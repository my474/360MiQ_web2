<?php
require_once __DIR__ . '/account/bootstrap.php';

$admin_user = miq_account_current_user();
if (!$admin_user) {
    header('Location: account.php?view=login&return_to=/account_user_admin');
    exit;
}
if (!miq_account_is_admin($admin_user)) {
    http_response_code(403);
    echo 'Administrator access required.';
    exit;
}

header('Cache-Control: private, no-store');
header('X-Robots-Tag: noindex, nofollow');

function miq_user_admin_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function miq_user_admin_utc($value, $fallback = 'Never')
{
    if (!$value) {
        return $fallback;
    }
    $timestamp = strtotime((string) $value . ' UTC');
    return $timestamp === false ? (string) $value : gmdate('Y-m-d H:i', $timestamp) . ' UTC';
}

function miq_user_admin_duration($duration, $custom_until)
{
    $duration = strtolower(trim((string) $duration));
    $seconds = array(
        '1d' => 86400,
        '7d' => 7 * 86400,
        '30d' => 30 * 86400,
        '90d' => 90 * 86400,
    );
    if ($duration === 'forever') {
        return array('action' => 'block', 'until' => null);
    }
    if (isset($seconds[$duration])) {
        return array('action' => 'suspend', 'until' => gmdate('Y-m-d H:i:s', time() + $seconds[$duration]));
    }
    if ($duration !== 'custom') {
        throw new InvalidArgumentException('Choose a valid suspension period.');
    }

    $custom_until = trim((string) $custom_until);
    $timezone = new DateTimeZone('UTC');
    $date = DateTime::createFromFormat('Y-m-d\TH:i', $custom_until, $timezone);
    if (!$date || $date->format('Y-m-d\TH:i') !== $custom_until || $date->getTimestamp() <= time()) {
        throw new InvalidArgumentException('Choose a future custom suspension time in UTC.');
    }
    return array('action' => 'suspend', 'until' => $date->format('Y-m-d H:i:s'));
}

function miq_user_admin_redirect($notice)
{
    header('Location: account_user_admin?notice=' . rawurlencode($notice), true, 303);
    exit;
}

$users_table = miq_account_table('users');
$sessions_table = miq_account_table('sessions');
$actions_table = miq_account_table('user_admin_actions');
$activity_table = miq_account_table('user_activity_daily');
$identities_table = miq_account_table('identities');
$charts_table = miq_account_table('saved_charts');
$scripts_table = miq_account_table('pine_scripts');
$messages = array();
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!miq_account_check_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session token expired. Refresh and try again.';
    } else {
        $db = miq_account_db();
        $in_transaction = false;
        try {
            $action = (string) ($_POST['action'] ?? '');
            $target_user_id = (int) ($_POST['target_user_id'] ?? 0);
            if ($target_user_id <= 0) {
                throw new InvalidArgumentException('Choose a valid user.');
            }
            if ($target_user_id === (int) $admin_user['id']) {
                throw new RuntimeException('You cannot suspend or block your own administrator account.');
            }

            $db->begin_transaction();
            $in_transaction = true;
            $target = miq_account_fetch_one(miq_account_query(
                "SELECT id, email, display_name, role, status FROM {$users_table} WHERE id = ? LIMIT 1 FOR UPDATE",
                'i',
                array($target_user_id)
            ));
            if (!$target || $target['status'] === 'deleted') {
                throw new RuntimeException('That user is no longer available.');
            }

            $reason = trim((string) ($_POST['reason'] ?? ''));
            if (function_exists('mb_substr')) {
                $reason = mb_substr($reason, 0, 500, 'UTF-8');
            } else {
                $reason = substr($reason, 0, 500);
            }

            if ($action === 'suspend') {
                if ($reason === '') {
                    throw new InvalidArgumentException('Add an internal reason for the suspension or block.');
                }
                $sanction = miq_user_admin_duration($_POST['duration'] ?? '', $_POST['custom_until'] ?? '');
                if ($sanction['until'] === null) {
                    miq_account_query(
                        "UPDATE {$users_table} SET status = 'suspended', suspended_at = UTC_TIMESTAMP(), suspended_until = NULL, suspension_reason = ?, suspended_by_user_id = ?, session_version = session_version + 1, updated_at = UTC_TIMESTAMP() WHERE id = ?",
                        'sii',
                        array($reason, (int) $admin_user['id'], $target_user_id)
                    )->close();
                } else {
                    miq_account_query(
                        "UPDATE {$users_table} SET status = 'suspended', suspended_at = UTC_TIMESTAMP(), suspended_until = ?, suspension_reason = ?, suspended_by_user_id = ?, session_version = session_version + 1, updated_at = UTC_TIMESTAMP() WHERE id = ?",
                        'ssii',
                        array($sanction['until'], $reason, (int) $admin_user['id'], $target_user_id)
                    )->close();
                }
                miq_account_query(
                    "INSERT INTO {$actions_table} (target_user_id, admin_user_id, target_email, target_display_name, action, reason, suspended_until, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP())",
                    'iisssss',
                    array($target_user_id, (int) $admin_user['id'], $target['email'], $target['display_name'], $sanction['action'], $reason, $sanction['until'])
                )->close();
                miq_account_query("DELETE FROM {$sessions_table} WHERE user_id = ?", 'i', array($target_user_id))->close();
                $db->commit();
                $in_transaction = false;
                miq_user_admin_redirect($sanction['action'] === 'block' ? 'blocked' : 'suspended');
            }

            if ($action === 'unsuspend') {
                if ($reason === '') {
                    $reason = 'Manual administrator restore.';
                }
                miq_account_query(
                    "UPDATE {$users_table} SET status = 'active', suspended_at = NULL, suspended_until = NULL, suspension_reason = NULL, suspended_by_user_id = NULL, session_version = session_version + 1, updated_at = UTC_TIMESTAMP() WHERE id = ?",
                    'i',
                    array($target_user_id)
                )->close();
                miq_account_query(
                    "INSERT INTO {$actions_table} (target_user_id, admin_user_id, target_email, target_display_name, action, reason, suspended_until, created_at) VALUES (?, ?, ?, ?, 'unsuspend', ?, NULL, UTC_TIMESTAMP())",
                    'iisss',
                    array($target_user_id, (int) $admin_user['id'], $target['email'], $target['display_name'], $reason)
                )->close();
                miq_account_query("DELETE FROM {$sessions_table} WHERE user_id = ?", 'i', array($target_user_id))->close();
                $db->commit();
                $in_transaction = false;
                miq_user_admin_redirect('restored');
            }

            throw new InvalidArgumentException('Choose a valid administrator action.');
        } catch (Throwable $error) {
            if ($in_transaction) {
                $db->rollback();
            }
            $errors[] = $error->getMessage();
        }
    }
}

$notice = isset($_GET['notice']) ? (string) $_GET['notice'] : '';
if ($notice === 'suspended') $messages[] = 'The user was suspended and all active sessions were revoked.';
if ($notice === 'blocked') $messages[] = 'The user was permanently blocked and all active sessions were revoked.';
if ($notice === 'restored') $messages[] = 'The user was restored. They can sign in again.';

miq_account_query(
    "UPDATE {$users_table} SET status = 'active', suspended_at = NULL, suspended_until = NULL, suspension_reason = NULL, suspended_by_user_id = NULL, updated_at = UTC_TIMESTAMP() WHERE status = 'suspended' AND suspended_until IS NOT NULL AND suspended_until <= UTC_TIMESTAMP()"
)->close();
miq_account_query("DELETE FROM {$sessions_table} WHERE expires_at < UTC_TIMESTAMP()")->close();

$stats = miq_account_fetch_one(miq_account_query(
    "SELECT
        COUNT(*) AS total_users,
        SUM(status = 'active') AS active_accounts,
        SUM(email_verified_at IS NOT NULL) AS verified_users,
        SUM(last_seen_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE)) AS active_15m,
        SUM(last_seen_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY)) AS active_24h,
        SUM(last_seen_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 7 DAY)) AS active_7d,
        SUM(last_seen_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY)) AS active_30d,
        SUM(status = 'suspended') AS suspended_users,
        SUM(created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY)) AS new_30d
     FROM {$users_table}
     WHERE status <> 'deleted'"
));

$search = trim((string) ($_GET['q'] ?? ''));
if (function_exists('mb_substr')) {
    $search = mb_substr($search, 0, 100, 'UTF-8');
} else {
    $search = substr($search, 0, 100);
}
$filter = strtolower((string) ($_GET['filter'] ?? 'all'));
$allowed_filters = array('all', 'active15', 'active24', 'active7', 'active30', 'inactive30', 'never', 'suspended', 'unverified', 'admins');
if (!in_array($filter, $allowed_filters, true)) {
    $filter = 'all';
}
$page_number = max(1, (int) ($_GET['page'] ?? 1));
$page_size = 25;

$where = array("u.status <> 'deleted'");
$types = '';
$params = array();
if ($search !== '') {
    $where[] = '(u.email LIKE ? OR u.display_name LIKE ?)';
    $term = '%' . $search . '%';
    $types .= 'ss';
    $params[] = $term;
    $params[] = $term;
}
if ($filter === 'active15') $where[] = 'u.last_seen_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE)';
if ($filter === 'active24') $where[] = 'u.last_seen_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY)';
if ($filter === 'active7') $where[] = 'u.last_seen_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 7 DAY)';
if ($filter === 'active30') $where[] = 'u.last_seen_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY)';
if ($filter === 'inactive30') $where[] = "(u.last_seen_at IS NULL OR u.last_seen_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY))";
if ($filter === 'never') $where[] = 'u.last_seen_at IS NULL';
if ($filter === 'suspended') $where[] = "u.status = 'suspended'";
if ($filter === 'unverified') $where[] = 'u.email_verified_at IS NULL';
if ($filter === 'admins') $where[] = "u.role = 'admin'";
$where_sql = implode(' AND ', $where);

$total_row = miq_account_fetch_one(miq_account_query(
    "SELECT COUNT(*) AS total FROM {$users_table} u WHERE {$where_sql}",
    $types,
    $params
));
$filtered_total = (int) ($total_row['total'] ?? 0);
$total_pages = max(1, (int) ceil($filtered_total / $page_size));
if ($page_number > $total_pages) $page_number = $total_pages;
$offset = ($page_number - 1) * $page_size;

$user_params = $params;
$user_params[] = $page_size;
$user_params[] = $offset;
$user_rows = miq_account_fetch_all(miq_account_query(
    "SELECT u.id, u.email, u.display_name, u.role, u.status, u.email_verified_at, u.created_at,
            u.last_login_at, u.last_seen_at, u.login_count, u.suspended_at, u.suspended_until,
            u.suspension_reason,
            EXISTS(SELECT 1 FROM {$identities_table} identity_row WHERE identity_row.user_id = u.id AND identity_row.provider = 'google') AS has_google,
            (SELECT COUNT(*) FROM {$sessions_table} session_row WHERE session_row.user_id = u.id AND session_row.expires_at >= UTC_TIMESTAMP()) AS active_sessions,
            (SELECT COUNT(*) FROM {$charts_table} chart_row WHERE chart_row.user_id = u.id) AS chart_count,
            (SELECT COUNT(*) FROM {$scripts_table} script_row WHERE script_row.user_id = u.id) AS script_count
     FROM {$users_table} u
     WHERE {$where_sql}
     ORDER BY COALESCE(u.last_seen_at, u.created_at) DESC, u.id DESC
     LIMIT ? OFFSET ?",
    $types . 'ii',
    $user_params
));

$activity_rows = miq_account_fetch_all(miq_account_query(
    "SELECT activity_date, COUNT(*) AS active_users, SUM(request_count) AS activity_writes
     FROM {$activity_table}
     WHERE activity_date >= DATE_SUB(UTC_DATE(), INTERVAL 29 DAY)
     GROUP BY activity_date
     ORDER BY activity_date ASC"
));
$activity_by_day = array();
foreach ($activity_rows as $row) {
    $activity_by_day[$row['activity_date']] = $row;
}
$activity_days = array();
$max_daily_users = 1;
for ($days_ago = 29; $days_ago >= 0; $days_ago--) {
    $day = gmdate('Y-m-d', time() - ($days_ago * 86400));
    $count = isset($activity_by_day[$day]) ? (int) $activity_by_day[$day]['active_users'] : 0;
    $writes = isset($activity_by_day[$day]) ? (int) $activity_by_day[$day]['activity_writes'] : 0;
    $max_daily_users = max($max_daily_users, $count);
    $activity_days[] = array('date' => $day, 'users' => $count, 'writes' => $writes);
}

$audit_rows = miq_account_fetch_all(miq_account_query(
    "SELECT action_row.id, action_row.target_email, action_row.target_display_name, action_row.action,
            action_row.reason, action_row.suspended_until, action_row.created_at,
            COALESCE(admin_user.display_name, 'Deleted administrator') AS admin_display_name
     FROM {$actions_table} action_row
     LEFT JOIN {$users_table} admin_user ON admin_user.id = action_row.admin_user_id
     ORDER BY action_row.created_at DESC, action_row.id DESC
     LIMIT 50"
));

$csrf_token = miq_account_csrf_token();
?>
<!DOCTYPE html>
<html>
<head>
    <?php include __DIR__ . '/meta.php'; ?>
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Administrator-only 360MiQ account activity and user controls.">
    <title>User Administration - 360MiQ.com</title>
    <link rel="stylesheet" href="assets/css/account.css?v=20260726.6">
    <link rel="stylesheet" href="assets/css/workspace.css?v=20260726.5">
</head>
<body class="miq-user-admin-body">
<?php $page = 'user-admin'; include __DIR__ . '/header.php'; ?>
<main class="miq-workspace-page miq-user-admin-page container">
    <div class="miq-workspace-heading">
        <div>
            <span class="miq-account-kicker">Administrator only</span>
            <h1>User activity and access</h1>
            <p>Activity timestamps use UTC. Tracking begins when the activity migration is deployed.</p>
        </div>
        <a class="btn btn-outline-primary" href="workspace">Back to Workspace</a>
    </div>

    <?php foreach ($messages as $message): ?><div class="alert alert-success"><?php echo miq_user_admin_h($message); ?></div><?php endforeach; ?>
    <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?php echo miq_user_admin_h($error); ?></div><?php endforeach; ?>

    <section class="miq-user-admin-stats" aria-label="User activity summary">
        <a href="account_user_admin" class="miq-user-admin-stat"><strong><?php echo (int) ($stats['total_users'] ?? 0); ?></strong><span>Total users</span></a>
        <a href="account_user_admin?filter=active15" class="miq-user-admin-stat"><strong><?php echo (int) ($stats['active_15m'] ?? 0); ?></strong><span>Active ~15 minutes</span></a>
        <a href="account_user_admin?filter=active24" class="miq-user-admin-stat"><strong><?php echo (int) ($stats['active_24h'] ?? 0); ?></strong><span>Active 24 hours</span></a>
        <a href="account_user_admin?filter=active7" class="miq-user-admin-stat"><strong><?php echo (int) ($stats['active_7d'] ?? 0); ?></strong><span>Active 7 days</span></a>
        <a href="account_user_admin?filter=active30" class="miq-user-admin-stat"><strong><?php echo (int) ($stats['active_30d'] ?? 0); ?></strong><span>Active 30 days</span></a>
        <a href="account_user_admin?filter=suspended" class="miq-user-admin-stat is-warning"><strong><?php echo (int) ($stats['suspended_users'] ?? 0); ?></strong><span>Suspended/blocked</span></a>
    </section>

    <section class="miq-workspace-panel miq-workspace-panel-wide miq-user-activity-panel">
        <div class="miq-user-admin-section-heading">
            <div><span class="miq-account-kicker">Last 30 days</span><h2>Daily active signed-in users</h2></div>
            <span><?php echo (int) ($stats['new_30d'] ?? 0); ?> new account(s)</span>
        </div>
        <div class="miq-user-activity-chart" role="img" aria-label="Daily active signed-in users for the last 30 days">
            <?php foreach ($activity_days as $day):
                $height = $day['users'] > 0 ? max(8, (int) round(($day['users'] / $max_daily_users) * 100)) : 2;
            ?>
                <div class="miq-user-activity-day" title="<?php echo miq_user_admin_h($day['date'] . ': ' . $day['users'] . ' active user(s)'); ?>">
                    <span style="height:<?php echo $height; ?>%"></span>
                    <small><?php echo substr($day['date'], 8, 2); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="miq-workspace-panel miq-workspace-panel-wide">
        <div class="miq-user-admin-section-heading">
            <div><span class="miq-account-kicker">Accounts</span><h2>User directory</h2></div>
            <span><?php echo $filtered_total; ?> matching user(s)</span>
        </div>
        <form class="miq-user-admin-filter" method="get">
            <label for="admin-user-search">Search users</label>
            <input id="admin-user-search" class="form-control" type="search" name="q" value="<?php echo miq_user_admin_h($search); ?>" placeholder="Email or display name">
            <label for="admin-user-filter">Activity/status</label>
            <select id="admin-user-filter" class="form-control" name="filter">
                <?php
                $filter_labels = array(
                    'all' => 'All users', 'active15' => 'Active in ~15 minutes', 'active24' => 'Active in 24 hours', 'active7' => 'Active in 7 days',
                    'active30' => 'Active in 30 days', 'inactive30' => 'Inactive for 30 days', 'never' => 'Never active',
                    'suspended' => 'Suspended or blocked', 'unverified' => 'Unverified', 'admins' => 'Administrators'
                );
                foreach ($filter_labels as $value => $label):
                ?>
                    <option value="<?php echo $value; ?>"<?php echo $filter === $value ? ' selected' : ''; ?>><?php echo miq_user_admin_h($label); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">Apply</button>
            <a class="btn btn-outline-secondary" href="account_user_admin">Reset</a>
        </form>

        <div class="miq-user-admin-table-wrap">
            <table class="miq-user-admin-table">
                <thead><tr><th>User</th><th>Access</th><th>Activity</th><th>Saved work</th><th>Control</th></tr></thead>
                <tbody>
                <?php if (!$user_rows): ?>
                    <tr><td colspan="5"><div class="miq-empty-state">No users match this filter.</div></td></tr>
                <?php endif; ?>
                <?php foreach ($user_rows as $row):
                    $is_current_admin = (int) $row['id'] === (int) $admin_user['id'];
                    $is_permanent = $row['status'] === 'suspended' && empty($row['suspended_until']);
                ?>
                    <tr>
                        <td>
                            <strong><?php echo miq_user_admin_h($row['display_name']); ?></strong>
                            <a href="mailto:<?php echo miq_user_admin_h($row['email']); ?>"><?php echo miq_user_admin_h($row['email']); ?></a>
                            <small>#<?php echo (int) $row['id']; ?> &middot; joined <?php echo miq_user_admin_h(miq_user_admin_utc($row['created_at'])); ?></small>
                            <small><?php echo $row['has_google'] ? 'Google linked' : 'Email login'; ?> &middot; <?php echo $row['email_verified_at'] ? 'verified' : 'unverified'; ?></small>
                        </td>
                        <td>
                            <span class="miq-user-status is-<?php echo miq_user_admin_h($row['status']); ?>"><?php echo $is_permanent ? 'Blocked' : miq_user_admin_h(ucfirst($row['status'])); ?></span>
                            <small><?php echo miq_user_admin_h($row['role']); ?></small>
                            <?php if ($row['status'] === 'suspended'): ?>
                                <small><?php echo $is_permanent ? 'No expiry' : 'Until ' . miq_user_admin_h(miq_user_admin_utc($row['suspended_until'])); ?></small>
                                <small class="miq-user-sanction-reason"><?php echo miq_user_admin_h($row['suspension_reason']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo miq_user_admin_h(miq_user_admin_utc($row['last_seen_at'])); ?></strong>
                            <small>Last login: <?php echo miq_user_admin_h(miq_user_admin_utc($row['last_login_at'])); ?></small>
                            <small><?php echo (int) $row['login_count']; ?> login(s) &middot; <?php echo (int) $row['active_sessions']; ?> session(s)</small>
                        </td>
                        <td>
                            <strong><?php echo (int) $row['chart_count']; ?> chart(s)</strong>
                            <small><?php echo (int) $row['script_count']; ?> Pine script(s)</small>
                        </td>
                        <td>
                            <?php if ($is_current_admin): ?>
                                <span class="miq-user-admin-self">Current administrator</span>
                            <?php else: ?>
                                <details class="miq-user-admin-controls">
                                    <summary>Manage access</summary>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?php echo miq_user_admin_h($csrf_token); ?>">
                                        <input type="hidden" name="action" value="suspend">
                                        <input type="hidden" name="target_user_id" value="<?php echo (int) $row['id']; ?>">
                                        <label>Period
                                            <select class="form-control" name="duration" required>
                                                <option value="1d">1 day</option>
                                                <option value="7d">7 days</option>
                                                <option value="30d">30 days</option>
                                                <option value="90d">90 days</option>
                                                <option value="custom">Custom UTC time</option>
                                                <option value="forever">Forever &mdash; permanent block</option>
                                            </select>
                                        </label>
                                        <label>Custom end (UTC)
                                            <input class="form-control" type="datetime-local" name="custom_until">
                                        </label>
                                        <label>Internal reason
                                            <textarea class="form-control" name="reason" maxlength="500" rows="2" required></textarea>
                                        </label>
                                        <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return window.confirm('Apply this restriction and sign the user out everywhere?');">Suspend or block</button>
                                    </form>
                                    <?php if ($row['status'] === 'suspended'): ?>
                                        <form method="post">
                                            <input type="hidden" name="csrf_token" value="<?php echo miq_user_admin_h($csrf_token); ?>">
                                            <input type="hidden" name="action" value="unsuspend">
                                            <input type="hidden" name="target_user_id" value="<?php echo (int) $row['id']; ?>">
                                            <label>Restore note
                                                <input class="form-control" name="reason" maxlength="500" placeholder="Optional">
                                            </label>
                                            <button class="btn btn-sm btn-outline-success" type="submit">Restore access</button>
                                        </form>
                                    <?php endif; ?>
                                </details>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav class="miq-user-admin-pagination" aria-label="User list pages">
                <?php for ($page_link = 1; $page_link <= $total_pages; $page_link++):
                    $page_query = http_build_query(array('q' => $search, 'filter' => $filter, 'page' => $page_link));
                ?>
                    <a class="<?php echo $page_link === $page_number ? 'is-active' : ''; ?>" href="account_user_admin?<?php echo miq_user_admin_h($page_query); ?>"><?php echo $page_link; ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>

    <section class="miq-workspace-panel miq-workspace-panel-wide">
        <div class="miq-user-admin-section-heading"><div><span class="miq-account-kicker">Accountability</span><h2>Latest access actions</h2></div></div>
        <div class="miq-user-admin-audit">
            <?php if (!$audit_rows): ?><div class="miq-empty-state">No suspension actions have been recorded.</div><?php endif; ?>
            <?php foreach ($audit_rows as $audit): ?>
                <article>
                    <div><span class="miq-user-status is-<?php echo $audit['action'] === 'unsuspend' ? 'active' : 'suspended'; ?>"><?php echo miq_user_admin_h(ucfirst($audit['action'])); ?></span><strong><?php echo miq_user_admin_h($audit['target_display_name']); ?></strong><small><?php echo miq_user_admin_h($audit['target_email']); ?></small></div>
                    <div><strong><?php echo miq_user_admin_h($audit['admin_display_name']); ?></strong><small><?php echo miq_user_admin_h(miq_user_admin_utc($audit['created_at'])); ?></small><?php if ($audit['suspended_until']): ?><small>Until <?php echo miq_user_admin_h(miq_user_admin_utc($audit['suspended_until'])); ?></small><?php endif; ?></div>
                    <p><?php echo miq_user_admin_h($audit['reason']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
