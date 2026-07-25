<?php
require_once __DIR__ . '/account/bootstrap.php';
require_once __DIR__ . '/account/community_sentiment.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

function miq_api_json($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function miq_api_body()
{
    $raw = file_get_contents('php://input');
    $body = json_decode((string) $raw, true);
    return is_array($body) ? $body : $_POST;
}

function miq_api_user()
{
    $user = miq_account_current_user();
    if (!$user) {
        miq_api_json(array('error' => 'Sign in is required.'), 401);
    }
    return $user;
}

function miq_api_require_post_csrf($body)
{
    if (!miq_account_check_csrf(isset($body['csrf_token']) ? $body['csrf_token'] : '')) {
        miq_api_json(array('error' => 'Your session token expired. Refresh and try again.'), 419);
    }
}

function miq_api_clean_code($value)
{
    $value = strtoupper(trim((string) $value));
    return substr(preg_replace('/[^A-Z0-9._:-]/', '', $value), 0, 40);
}

function miq_api_clean_text($value, $max)
{
    $value = trim((string) $value);
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max, 'UTF-8');
    }
    return substr($value, 0, $max);
}

function miq_api_pulse_timeframe($value)
{
    $timeframe = strtolower(trim((string) $value));
    if ($timeframe === '') {
        $timeframe = '30d';
    }
    if ($timeframe === '1m') {
        $timeframe = '30d';
    }
    if ($timeframe !== '30d') {
        miq_api_json(array('error' => 'Invalid community pulse timeframe.'), 422);
    }
    return $timeframe;
}

function miq_api_pulse_period_end()
{
    return gmdate('Y-m-d', time() + (30 * 86400));
}

function miq_api_pulse_context($context_type, $context_key)
{
    $context_type = strtolower(miq_api_clean_text($context_type, 32));
    if (!in_array($context_type, array('site', 'stock', 'market'), true)) {
        miq_api_json(array('error' => 'Invalid community pulse context.'), 422);
    }
    if ($context_type === 'site') {
        return array('site', 'site');
    }
    $context_key = strtoupper(miq_api_clean_text($context_key, 80));
    if ($context_key === '') {
        miq_api_json(array('error' => 'A community pulse subject is required.'), 422);
    }
    return array($context_type, $context_key);
}

function miq_api_asset_key($value = '')
{
    $value = strtolower(trim((string) $value));
    if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/', $value)) {
        return $value;
    }
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    $hex = bin2hex($bytes);
    return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
}

function miq_api_existing_asset_key($value)
{
    $value = strtolower(trim((string) $value));
    return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/', $value) ? $value : '';
}

function miq_api_client_datetime($value)
{
    $timestamp = strtotime((string) $value);
    return $timestamp ? gmdate('Y-m-d H:i:s', $timestamp) : null;
}

function miq_api_page()
{
    return max(1, (int) ($_GET['page'] ?? 1));
}

function miq_api_limit($default = 50, $maximum = 100)
{
    $limit = (int) ($_GET['limit'] ?? $default);
    return max(1, min($maximum, $limit));
}

function miq_api_layout_json($layout)
{
    if (is_string($layout)) {
        $decoded = json_decode($layout, true);
        if (!is_array($decoded)) {
            return null;
        }
        $layout_json = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } elseif (is_array($layout) || is_object($layout)) {
        $layout_json = json_encode($layout, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } else {
        return null;
    }
    if (!$layout_json || strlen($layout_json) > miq_account_config()['max_chart_bytes']) {
        return null;
    }
    return $layout_json;
}

function miq_api_chart_payload($chart, $include_layout = false)
{
    if (!$chart) return null;
    $chart['id'] = (int) $chart['id'];
    $chart['revision'] = (int) $chart['revision'];
    if ($include_layout && array_key_exists('layout_json', $chart)) {
        $chart['layout'] = json_decode($chart['layout_json'], true);
    }
    unset($chart['layout_json']);
    return $chart;
}

function miq_api_script_payload($script, $include_source = false)
{
    if (!$script) return null;
    $script['id'] = (int) $script['id'];
    $script['revision'] = (int) $script['revision'];
    if (!$include_source) unset($script['source_code']);
    return $script;
}

function miq_api_count_rows($table, $user_id, $extra_sql = '', $types = '', $params = array())
{
    $query_types = 'i' . $types;
    $query_params = array_merge(array($user_id), $params);
    $row = miq_account_fetch_one(miq_account_query("SELECT COUNT(*) AS total FROM {$table} WHERE user_id = ? {$extra_sql}", $query_types, $query_params));
    return (int) ($row['total'] ?? 0);
}

function miq_api_trim_versions($table, $asset_column, $asset_id)
{
    $limit = max(1, (int) miq_account_config()['max_asset_versions']);
    $rows = miq_account_fetch_all(miq_account_query(
        "SELECT id FROM {$table} WHERE {$asset_column} = ? ORDER BY revision DESC, id DESC LIMIT 1000",
        'i',
        array($asset_id)
    ));
    if (count($rows) <= $limit) return;
    $delete_ids = array_slice(array_map(function ($row) { return (int) $row['id']; }, $rows), $limit);
    foreach (array_chunk($delete_ids, 50) as $chunk) {
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        miq_account_query("DELETE FROM {$table} WHERE id IN ({$placeholders})", str_repeat('i', count($chunk)), $chunk)->close();
    }
}

function miq_api_counts($context_type, $context_key)
{
    return miq_community_active_counts($context_type, $context_key);
}

function miq_api_require_moderator($user)
{
    if (!miq_account_is_moderator($user)) {
        miq_api_json(array('error' => 'Moderator access is required.'), 403);
    }
}

function miq_api_moderation_dashboard()
{
    $ideas = miq_account_table('community_ideas');
    $users = miq_account_table('users');
    $reports = miq_account_table('community_reports');
    $actions = miq_account_table('moderation_actions');

    $pending = miq_account_fetch_all(miq_account_query(
        "SELECT i.id, i.user_id, i.code, i.title, i.direction, i.timeframe, i.thesis, i.catalyst, i.risk, i.disclosure, i.status, i.visibility, i.created_at, i.updated_at, i.published_at, author.display_name AS author_display_name, author.email AS author_email, (SELECT COUNT(*) FROM {$reports} report_count WHERE report_count.idea_id = i.id AND report_count.status = 'open') AS open_report_count FROM {$ideas} i INNER JOIN {$users} author ON author.id = i.user_id WHERE i.status = 'pending' ORDER BY i.updated_at ASC LIMIT 100"
    ));
    $open_reports = miq_account_fetch_all(miq_account_query(
        "SELECT r.id AS report_id, r.reason AS report_reason, r.details AS report_details, r.status AS report_status, r.created_at AS report_created_at, reporter.display_name AS reporter_display_name, reporter.email AS reporter_email, i.id AS idea_id, i.user_id AS author_user_id, i.code, i.title, i.direction, i.timeframe, i.thesis, i.catalyst, i.risk, i.disclosure, i.status AS idea_status, i.visibility AS idea_visibility, i.created_at AS idea_created_at, i.updated_at AS idea_updated_at, i.published_at, author.display_name AS author_display_name, author.email AS author_email FROM {$reports} r INNER JOIN {$ideas} i ON i.id = r.idea_id INNER JOIN {$users} reporter ON reporter.id = r.reporter_user_id INNER JOIN {$users} author ON author.id = i.user_id WHERE r.status = 'open' ORDER BY r.created_at ASC LIMIT 100"
    ));
    $history = miq_account_fetch_all(miq_account_query(
        "SELECT action_log.id, action_log.idea_id, action_log.action, action_log.note, action_log.created_at, moderator.display_name AS moderator_display_name, moderator.email AS moderator_email, i.title AS idea_title, i.code AS idea_code, i.status AS idea_status FROM {$actions} action_log INNER JOIN {$users} moderator ON moderator.id = action_log.moderator_user_id INNER JOIN {$ideas} i ON i.id = action_log.idea_id ORDER BY action_log.created_at DESC, action_log.id DESC LIMIT 100"
    ));
    $pending_count = miq_account_fetch_one(miq_account_query("SELECT COUNT(*) AS total FROM {$ideas} WHERE status = 'pending'"));
    $report_count = miq_account_fetch_one(miq_account_query("SELECT COUNT(*) AS total FROM {$reports} WHERE status = 'open'"));
    $action_count = miq_account_fetch_one(miq_account_query("SELECT COUNT(*) AS total FROM {$actions}"));

    return array(
        'ideas' => $pending,
        'reports' => $open_reports,
        'history' => $history,
        'counts' => array(
            'pending' => (int) ($pending_count['total'] ?? 0),
            'reports' => (int) ($report_count['total'] ?? 0),
            'actions' => (int) ($action_count['total'] ?? 0),
        ),
    );
}

function miq_api_record_moderation_action($moderator_user_id, $idea_id, $action, $note)
{
    miq_account_query(
        "INSERT INTO " . miq_account_table('moderation_actions') . " (moderator_user_id, idea_id, action, note, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())",
        'iiss',
        array((int) $moderator_user_id, (int) $idea_id, miq_api_clean_text($action, 32), miq_api_clean_text($note, 500))
    )->close();
}

function miq_api_workspace($user)
{
    $user_id = (int) $user['id'];
    $charts = miq_account_table('saved_charts');
    $scripts = miq_account_table('pine_scripts');
    $ideas = miq_account_table('community_ideas');
    $searches = miq_account_table('recent_searches');
    $watchlists = miq_account_table('watchlists');
    $watchlist_items = miq_account_table('watchlist_items');
    $lists = miq_account_fetch_all(miq_account_query("SELECT id, name, created_at, updated_at FROM {$watchlists} WHERE user_id = ? ORDER BY updated_at DESC LIMIT 20", 'i', array($user_id)));
    foreach ($lists as $index => $list) {
        $lists[$index]['items'] = miq_account_fetch_all(miq_account_query("SELECT code, sort_order FROM {$watchlist_items} WHERE watchlist_id = ? AND user_id = ? ORDER BY sort_order, code", 'ii', array((int) $list['id'], $user_id)));
    }
    return array(
        'searches' => miq_account_fetch_all(miq_account_query("SELECT code, exchange, display_name, searched_at FROM {$searches} WHERE user_id = ? ORDER BY searched_at DESC LIMIT 20", 'i', array($user_id))),
        'charts' => miq_account_fetch_all(miq_account_query("SELECT id, asset_key, name, code, kind, visibility, revision, last_client_updated_at, updated_at FROM {$charts} WHERE user_id = ? ORDER BY updated_at DESC LIMIT 50", 'i', array($user_id))),
        'scripts' => miq_account_fetch_all(miq_account_query("SELECT id, asset_key, name, code, visibility, revision, status, last_client_updated_at, updated_at FROM {$scripts} WHERE user_id = ? ORDER BY updated_at DESC LIMIT 50", 'i', array($user_id))),
        'ideas' => miq_account_fetch_all(miq_account_query("SELECT id, code, title, direction, timeframe, status, visibility, updated_at FROM {$ideas} WHERE user_id = ? ORDER BY updated_at DESC LIMIT 30", 'i', array($user_id))),
        'watchlists' => $lists,
        'counts' => array(
            'charts' => miq_api_count_rows($charts, $user_id),
            'scripts' => miq_api_count_rows($scripts, $user_id),
            'searches' => miq_api_count_rows($searches, $user_id),
            'ideas' => miq_api_count_rows($ideas, $user_id),
            'watchlists' => count($lists),
        ),
    );
}

$body = miq_api_body();
$action = isset($_GET['action']) ? (string) $_GET['action'] : (isset($body['action']) ? (string) $body['action'] : '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    miq_api_require_post_csrf($body);
}

try {
    if ($action === 'pulse') {
        list($context_type, $context_key) = miq_api_pulse_context($_GET['context_type'] ?? 'site', $_GET['context_key'] ?? 'site');
        $timeframe = miq_api_pulse_timeframe($_GET['timeframe'] ?? '30d');
        miq_api_json(array(
            'counts' => miq_api_counts($context_type, $context_key),
            'timeframe' => $timeframe,
            'period_end' => miq_api_pulse_period_end(),
            'trend_available' => miq_community_schema_ready(),
        ));
    }

    if ($action === 'pulse_trend') {
        list($context_type, $context_key) = miq_api_pulse_context($_GET['context_type'] ?? 'site', $_GET['context_key'] ?? 'site');
        miq_api_pulse_timeframe($_GET['timeframe'] ?? '30d');
        $days = max(7, min(180, (int) ($_GET['days'] ?? 90)));
        miq_api_json(array('trend' => miq_community_rebuild_trend($context_type, $context_key, $days, 10)));
    }

    if ($action === 'public_ideas') {
        $context_key = miq_api_clean_code($_GET['context_key'] ?? '');
        $ideas = miq_account_table('community_ideas');
        $idea_id = (int) ($_GET['idea_id'] ?? 0);
        if ($idea_id > 0) {
            $rows = miq_account_fetch_all(miq_account_query("SELECT i.id, i.code, i.title, i.direction, i.timeframe, i.thesis, i.catalyst, i.risk, i.disclosure, i.published_at, u.display_name FROM {$ideas} i INNER JOIN " . miq_account_table('users') . " u ON u.id = i.user_id WHERE i.id = ? AND i.status = 'published' AND i.visibility = 'public' LIMIT 1", 'i', array($idea_id)));
        } else {
            $rows = miq_account_fetch_all(miq_account_query("SELECT i.id, i.code, i.title, i.direction, i.timeframe, i.thesis, i.catalyst, i.risk, i.disclosure, i.published_at, u.display_name FROM {$ideas} i INNER JOIN " . miq_account_table('users') . " u ON u.id = i.user_id WHERE i.status = 'published' AND i.visibility = 'public' AND (? = '' OR i.code = ?) ORDER BY i.published_at DESC LIMIT 40", 'ss', array($context_key, $context_key)));
        }
        miq_api_json(array('ideas' => $rows));
    }

    $user = miq_api_user();
    $user_id = (int) $user['id'];

    if ($action === 'save_search') {
        $code = miq_api_clean_code($body['code'] ?? '');
        if ($code === '') miq_api_json(array('error' => 'A stock code is required.'), 422);
        $searches = miq_account_table('recent_searches');
        miq_account_query(
            "INSERT INTO {$searches} (user_id, code, exchange, display_name, searched_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE exchange = VALUES(exchange), display_name = VALUES(display_name), searched_at = UTC_TIMESTAMP()",
            'isss',
            array($user_id, $code, miq_api_clean_text($body['exchange'] ?? '', 32), miq_api_clean_text($body['display_name'] ?? '', 160))
        )->close();
        miq_account_query("DELETE FROM {$searches} WHERE user_id = ? AND id NOT IN (SELECT id FROM (SELECT id FROM {$searches} WHERE user_id = ? ORDER BY searched_at DESC LIMIT 50) recent_ids)", 'ii', array($user_id, $user_id))->close();
        miq_api_json(array('saved' => true));
    }

    if ($action === 'workspace') {
        miq_api_json(array('workspace' => miq_api_workspace($user)));
    }

    if ($action === 'create_watchlist') {
        $name = miq_api_clean_text($body['name'] ?? 'My Watchlist', 120);
        if ($name === '') miq_api_json(array('error' => 'A watchlist name is required.'), 422);
        $watchlists = miq_account_table('watchlists');
        $statement = miq_account_query("INSERT INTO {$watchlists} (user_id, name, created_at, updated_at) VALUES (?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())", 'is', array($user_id, $name));
        $id = (int) miq_account_db()->insert_id;
        $statement->close();
        miq_api_json(array('saved' => true, 'id' => $id));
    }

    if ($action === 'add_watchlist_item') {
        $watchlist_id = (int) ($body['watchlist_id'] ?? 0);
        $code = miq_api_clean_code($body['code'] ?? '');
        if (!$watchlist_id || $code === '') miq_api_json(array('error' => 'A watchlist and stock code are required.'), 422);
        $watchlists = miq_account_table('watchlists');
        $items = miq_account_table('watchlist_items');
        $owned = miq_account_fetch_one(miq_account_query("SELECT id FROM {$watchlists} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($watchlist_id, $user_id)));
        if (!$owned) miq_api_json(array('error' => 'Watchlist not found.'), 404);
        miq_account_query("INSERT INTO {$items} (watchlist_id, user_id, code, sort_order, created_at) VALUES (?, ?, ?, 0, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE sort_order = sort_order", 'iis', array($watchlist_id, $user_id, $code))->close();
        miq_account_query("UPDATE {$watchlists} SET updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'ii', array($watchlist_id, $user_id))->close();
        miq_api_json(array('saved' => true));
    }

    if ($action === 'remove_watchlist_item') {
        $watchlist_id = (int) ($body['watchlist_id'] ?? 0);
        $code = miq_api_clean_code($body['code'] ?? '');
        $items = miq_account_table('watchlist_items');
        miq_account_query("DELETE FROM {$items} WHERE watchlist_id = ? AND user_id = ? AND code = ?", 'iis', array($watchlist_id, $user_id, $code))->close();
        miq_api_json(array('saved' => true));
    }

    if ($action === 'list_charts') {
        $charts = miq_account_table('saved_charts');
        $page = miq_api_page();
        $limit = miq_api_limit();
        $offset = ($page - 1) * $limit;
        $kind = in_array(($_GET['kind'] ?? ''), array('workspace', 'named'), true) ? $_GET['kind'] : '';
        $search = miq_api_clean_text($_GET['search'] ?? '', 120);
        $where = "user_id = ?";
        $types = 'i';
        $params = array($user_id);
        if ($kind !== '') {
            $where .= " AND kind = ?";
            $types .= 's';
            $params[] = $kind;
        }
        if ($search !== '') {
            $where .= " AND (name LIKE ? OR code LIKE ?)";
            $types .= 'ss';
            $params[] = '%' . $search . '%';
            $params[] = '%' . strtoupper($search) . '%';
        }
        $total_row = miq_account_fetch_one(miq_account_query("SELECT COUNT(*) AS total FROM {$charts} WHERE {$where}", $types, $params));
        $rows = miq_account_fetch_all(miq_account_query(
            "SELECT id, asset_key, name, code, kind, visibility, revision, last_client_updated_at, created_at, updated_at FROM {$charts} WHERE {$where} ORDER BY updated_at DESC LIMIT {$limit} OFFSET {$offset}",
            $types,
            $params
        ));
        miq_api_json(array('charts' => array_map(function ($row) { return miq_api_chart_payload($row); }, $rows), 'page' => $page, 'limit' => $limit, 'total' => (int) ($total_row['total'] ?? 0)));
    }

    if ($action === 'get_chart') {
        $charts = miq_account_table('saved_charts');
        $chart_id = (int) ($_GET['id'] ?? 0);
        $asset_key = miq_api_existing_asset_key($_GET['asset_key'] ?? '');
        $code = miq_api_clean_code($_GET['code'] ?? '');
        if ($chart_id > 0) {
            $chart = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
        } elseif ($asset_key !== '') {
            $chart = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE asset_key = ? AND user_id = ? LIMIT 1", 'si', array($asset_key, $user_id)));
        } elseif ($code !== '') {
            $chart = miq_account_fetch_one(miq_account_query(
                "SELECT * FROM {$charts} WHERE user_id = ? AND code = ? ORDER BY CASE WHEN kind = 'workspace' OR name LIKE 'Auto:%' THEN 0 ELSE 1 END, updated_at DESC LIMIT 1",
                'is',
                array($user_id, $code)
            ));
        } else {
            $chart = null;
        }
        miq_api_json(array('chart' => miq_api_chart_payload($chart, true)));
    }

    if ($action === 'save_chart') {
        $charts = miq_account_table('saved_charts');
        $versions = miq_account_table('chart_versions');
        $code = miq_api_clean_code($body['code'] ?? '');
        $kind = in_array(($body['kind'] ?? ''), array('workspace', 'named'), true)
            ? $body['kind']
            : (!empty($body['autosave']) ? 'workspace' : 'named');
        $name = miq_api_clean_text($body['name'] ?? ($kind === 'workspace' ? ('Auto: ' . $code) : $code . ' chart'), 120);
        $layout_json = miq_api_layout_json($body['layout'] ?? null);
        if ($code === '' || $name === '' || !$layout_json) {
            miq_api_json(array('error' => 'This chart layout is invalid or too large.'), 422);
        }
        $chart_id = (int) ($body['id'] ?? 0);
        $asset_key = miq_api_existing_asset_key($body['asset_key'] ?? '');
        $expected_revision = max(0, (int) ($body['expected_revision'] ?? 0));
        $client_updated_at = miq_api_client_datetime($body['client_updated_at'] ?? '');
        $visibility = in_array(($body['visibility'] ?? ''), array('private', 'unlisted', 'public'), true) ? $body['visibility'] : 'private';
        $existing = null;
        if ($chart_id > 0) {
            $existing = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
        } elseif ($asset_key !== '') {
            $existing = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE asset_key = ? AND user_id = ? LIMIT 1", 'si', array($asset_key, $user_id)));
        } elseif ($kind === 'workspace') {
            $existing = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE user_id = ? AND code = ? AND (kind = 'workspace' OR name LIKE 'Auto:%') ORDER BY updated_at DESC LIMIT 1", 'is', array($user_id, $code)));
        } else {
            // Backward compatibility for named clients that predate stable asset keys.
            $existing = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE user_id = ? AND code = ? AND name = ? LIMIT 1", 'iss', array($user_id, $code, $name)));
        }
        if ($existing) {
            $chart_id = (int) $existing['id'];
            if ($expected_revision > 0 && $expected_revision !== (int) $existing['revision']) {
                miq_api_json(array('error' => 'This chart changed on another device.', 'conflict' => true, 'chart' => miq_api_chart_payload($existing)), 409);
            }
            $revision = (int) $existing['revision'] + 1;
            if ($expected_revision > 0) {
                $statement = miq_account_query(
                    "UPDATE {$charts} SET name = ?, code = ?, kind = ?, layout_json = ?, visibility = ?, revision = ?, last_client_updated_at = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ? AND revision = ?",
                    'sssssisiii',
                    array($name, $code, $kind, $layout_json, $visibility, $revision, $client_updated_at, $chart_id, $user_id, $expected_revision)
                );
                $affected = $statement->affected_rows;
                $statement->close();
                if ($affected !== 1) {
                    $current = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
                    miq_api_json(array('error' => 'This chart changed on another device.', 'conflict' => true, 'chart' => miq_api_chart_payload($current)), 409);
                }
            } else {
                miq_account_query(
                    "UPDATE {$charts} SET name = ?, code = ?, kind = ?, layout_json = ?, visibility = ?, revision = ?, last_client_updated_at = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?",
                    'sssssisii',
                    array($name, $code, $kind, $layout_json, $visibility, $revision, $client_updated_at, $chart_id, $user_id)
                )->close();
            }
            if (!empty($body['create_version']) || empty($body['autosave'])) {
                miq_account_query("INSERT INTO {$versions} (chart_id, user_id, revision, layout_json, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiis', array($chart_id, $user_id, $revision, $layout_json))->close();
                miq_api_trim_versions($versions, 'chart_id', $chart_id);
            }
        } else {
            if (miq_api_count_rows($charts, $user_id) >= miq_account_config()['max_chart_count']) {
                miq_api_json(array('error' => 'Your chart storage limit has been reached.'), 422);
            }
            if ($kind === 'named' && miq_api_count_rows($charts, $user_id, "AND kind = 'named'") >= miq_account_config()['max_named_chart_count']) {
                miq_api_json(array('error' => 'Your named chart limit has been reached.'), 422);
            }
            $asset_key = $asset_key !== '' ? $asset_key : miq_api_asset_key();
            $statement = miq_account_query(
                "INSERT INTO {$charts} (user_id, asset_key, name, code, kind, layout_json, visibility, revision, last_client_updated_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())",
                'isssssss',
                array($user_id, $asset_key, $name, $code, $kind, $layout_json, $visibility, $client_updated_at)
            );
            $chart_id = (int) miq_account_db()->insert_id;
            $statement->close();
            $revision = 1;
            if ($kind === 'named' || !empty($body['create_version'])) {
                miq_account_query("INSERT INTO {$versions} (chart_id, user_id, revision, layout_json, created_at) VALUES (?, ?, 1, ?, UTC_TIMESTAMP())", 'iis', array($chart_id, $user_id, $layout_json))->close();
            }
        }
        $saved_chart = miq_account_fetch_one(miq_account_query("SELECT id, asset_key, name, code, kind, visibility, revision, last_client_updated_at, created_at, updated_at FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
        miq_api_json(array('saved' => true, 'chart' => miq_api_chart_payload($saved_chart), 'id' => $chart_id, 'revision' => $revision));
    }

    if ($action === 'rename_chart') {
        $charts = miq_account_table('saved_charts');
        $chart_id = (int) ($body['id'] ?? 0);
        $name = miq_api_clean_text($body['name'] ?? '', 120);
        $expected_revision = max(0, (int) ($body['expected_revision'] ?? 0));
        $chart = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
        if (!$chart) miq_api_json(array('error' => 'Chart not found.'), 404);
        if ($name === '') miq_api_json(array('error' => 'A chart name is required.'), 422);
        if ($expected_revision > 0 && $expected_revision !== (int) $chart['revision']) miq_api_json(array('error' => 'This chart changed on another device.', 'conflict' => true, 'chart' => miq_api_chart_payload($chart)), 409);
        $revision = (int) $chart['revision'] + 1;
        $statement = $expected_revision > 0
            ? miq_account_query("UPDATE {$charts} SET name = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ? AND revision = ?", 'siiii', array($name, $revision, $chart_id, $user_id, $expected_revision))
            : miq_account_query("UPDATE {$charts} SET name = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'siii', array($name, $revision, $chart_id, $user_id));
        $renamed = $statement->affected_rows === 1;
        $statement->close();
        if (!$renamed) {
            $current = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
            miq_api_json(array('error' => 'This chart changed on another device.', 'conflict' => true, 'chart' => miq_api_chart_payload($current)), 409);
        }
        miq_api_json(array('saved' => true, 'id' => $chart_id, 'name' => $name, 'revision' => $revision));
    }

    if ($action === 'duplicate_chart') {
        $charts = miq_account_table('saved_charts');
        $versions = miq_account_table('chart_versions');
        $chart_id = (int) ($body['id'] ?? 0);
        $chart = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
        if (!$chart) miq_api_json(array('error' => 'Chart not found.'), 404);
        if (miq_api_count_rows($charts, $user_id) >= miq_account_config()['max_chart_count'] || miq_api_count_rows($charts, $user_id, "AND kind = 'named'") >= miq_account_config()['max_named_chart_count']) {
            miq_api_json(array('error' => 'Your named chart limit has been reached.'), 422);
        }
        $name = miq_api_clean_text($body['name'] ?? ('Copy of ' . $chart['name']), 120);
        $asset_key = miq_api_asset_key();
        $statement = miq_account_query(
            "INSERT INTO {$charts} (user_id, asset_key, name, code, kind, layout_json, visibility, revision, last_client_updated_at, created_at, updated_at) VALUES (?, ?, ?, ?, 'named', ?, 'private', 1, NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP())",
            'issss',
            array($user_id, $asset_key, $name, $chart['code'], $chart['layout_json'])
        );
        $new_id = (int) miq_account_db()->insert_id;
        $statement->close();
        miq_account_query("INSERT INTO {$versions} (chart_id, user_id, revision, layout_json, created_at) VALUES (?, ?, 1, ?, UTC_TIMESTAMP())", 'iis', array($new_id, $user_id, $chart['layout_json']))->close();
        miq_api_json(array('saved' => true, 'id' => $new_id, 'asset_key' => $asset_key, 'revision' => 1));
    }

    if ($action === 'delete_chart') {
        $chart_id = (int) ($body['id'] ?? 0);
        $charts = miq_account_table('saved_charts');
        $statement = miq_account_query("DELETE FROM {$charts} WHERE id = ? AND user_id = ?", 'ii', array($chart_id, $user_id));
        $deleted = $statement->affected_rows === 1;
        $statement->close();
        if (!$deleted) miq_api_json(array('error' => 'Chart not found.'), 404);
        miq_api_json(array('deleted' => true, 'id' => $chart_id));
    }

    if ($action === 'list_chart_versions') {
        $chart_id = (int) ($_GET['id'] ?? 0);
        $charts = miq_account_table('saved_charts');
        $versions = miq_account_table('chart_versions');
        $owned = miq_account_fetch_one(miq_account_query("SELECT id FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
        if (!$owned) miq_api_json(array('error' => 'Chart not found.'), 404);
        $rows = miq_account_fetch_all(miq_account_query("SELECT id, revision, created_at FROM {$versions} WHERE chart_id = ? AND user_id = ? ORDER BY revision DESC LIMIT 100", 'ii', array($chart_id, $user_id)));
        miq_api_json(array('versions' => $rows));
    }

    if ($action === 'restore_chart_version') {
        $chart_id = (int) ($body['id'] ?? 0);
        $version_id = (int) ($body['version_id'] ?? 0);
        $expected_revision = max(0, (int) ($body['expected_revision'] ?? 0));
        $charts = miq_account_table('saved_charts');
        $versions = miq_account_table('chart_versions');
        $chart = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
        $version = miq_account_fetch_one(miq_account_query("SELECT * FROM {$versions} WHERE id = ? AND chart_id = ? AND user_id = ? LIMIT 1", 'iii', array($version_id, $chart_id, $user_id)));
        if (!$chart || !$version) miq_api_json(array('error' => 'Chart version not found.'), 404);
        if ($expected_revision > 0 && $expected_revision !== (int) $chart['revision']) miq_api_json(array('error' => 'This chart changed on another device.', 'conflict' => true, 'chart' => miq_api_chart_payload($chart)), 409);
        $revision = (int) $chart['revision'] + 1;
        $statement = $expected_revision > 0
            ? miq_account_query("UPDATE {$charts} SET layout_json = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ? AND revision = ?", 'siiii', array($version['layout_json'], $revision, $chart_id, $user_id, $expected_revision))
            : miq_account_query("UPDATE {$charts} SET layout_json = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'siii', array($version['layout_json'], $revision, $chart_id, $user_id));
        $restored = $statement->affected_rows === 1;
        $statement->close();
        if (!$restored) {
            $current = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
            miq_api_json(array('error' => 'This chart changed on another device.', 'conflict' => true, 'chart' => miq_api_chart_payload($current)), 409);
        }
        miq_account_query("INSERT INTO {$versions} (chart_id, user_id, revision, layout_json, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiis', array($chart_id, $user_id, $revision, $version['layout_json']))->close();
        miq_api_trim_versions($versions, 'chart_id', $chart_id);
        miq_api_json(array('saved' => true, 'id' => $chart_id, 'revision' => $revision, 'layout' => json_decode($version['layout_json'], true)));
    }

    if ($action === 'list_scripts') {
        $scripts = miq_account_table('pine_scripts');
        $page = miq_api_page();
        $limit = miq_api_limit();
        $offset = ($page - 1) * $limit;
        $status = in_array(($_GET['status'] ?? ''), array('draft', 'published', 'archived'), true) ? $_GET['status'] : '';
        $search = miq_api_clean_text($_GET['search'] ?? '', 120);
        $where = "user_id = ?";
        $types = 'i';
        $params = array($user_id);
        if ($status !== '') {
            $where .= " AND status = ?";
            $types .= 's';
            $params[] = $status;
        }
        if ($search !== '') {
            $where .= " AND (name LIKE ? OR code LIKE ?)";
            $types .= 'ss';
            $params[] = '%' . $search . '%';
            $params[] = '%' . strtoupper($search) . '%';
        }
        $total_row = miq_account_fetch_one(miq_account_query("SELECT COUNT(*) AS total FROM {$scripts} WHERE {$where}", $types, $params));
        $rows = miq_account_fetch_all(miq_account_query(
            "SELECT id, asset_key, name, code, visibility, revision, status, last_client_updated_at, created_at, updated_at FROM {$scripts} WHERE {$where} ORDER BY updated_at DESC LIMIT {$limit} OFFSET {$offset}",
            $types,
            $params
        ));
        miq_api_json(array('scripts' => array_map(function ($row) { return miq_api_script_payload($row); }, $rows), 'page' => $page, 'limit' => $limit, 'total' => (int) ($total_row['total'] ?? 0)));
    }

    if ($action === 'get_script') {
        $scripts = miq_account_table('pine_scripts');
        $script_id = (int) ($_GET['id'] ?? 0);
        $asset_key = miq_api_existing_asset_key($_GET['asset_key'] ?? '');
        if ($script_id > 0) {
            $script = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
        } elseif ($asset_key !== '') {
            $script = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE asset_key = ? AND user_id = ? LIMIT 1", 'si', array($asset_key, $user_id)));
        } else {
            $script = null;
        }
        miq_api_json(array('script' => miq_api_script_payload($script, true)));
    }

    if ($action === 'save_script') {
        $scripts = miq_account_table('pine_scripts');
        $versions = miq_account_table('pine_script_versions');
        $name = miq_api_clean_text($body['name'] ?? 'Untitled script', 120);
        $source = (string) ($body['source_code'] ?? '');
        $code = miq_api_clean_code($body['code'] ?? '');
        $status = in_array(($body['status'] ?? ''), array('draft', 'published', 'archived'), true) ? $body['status'] : (!empty($body['publish']) ? 'published' : 'draft');
        $visibility = in_array(($body['visibility'] ?? ''), array('private', 'unlisted', 'public'), true) ? $body['visibility'] : ($status === 'published' ? 'public' : 'private');
        if ($name === '' || trim($source) === '' || strlen($source) > miq_account_config()['max_script_chars']) {
            miq_api_json(array('error' => 'This Pine script is empty or too large.'), 422);
        }
        $script_id = (int) ($body['id'] ?? 0);
        $asset_key = miq_api_existing_asset_key($body['asset_key'] ?? '');
        $expected_revision = max(0, (int) ($body['expected_revision'] ?? 0));
        $client_updated_at = miq_api_client_datetime($body['client_updated_at'] ?? '');
        $existing = null;
        if ($script_id > 0) {
            $existing = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
        } elseif ($asset_key !== '') {
            $existing = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE asset_key = ? AND user_id = ? LIMIT 1", 'si', array($asset_key, $user_id)));
        } elseif ($asset_key === '' || !empty($body['legacy_match'])) {
            // Preserve the pre-asset-key API contract for cached/older clients.
            $existing = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE user_id = ? AND name = ? AND code = ? LIMIT 1", 'iss', array($user_id, $name, $code)));
        }
        if ($existing) {
            $script_id = (int) $existing['id'];
            if ($expected_revision > 0 && $expected_revision !== (int) $existing['revision']) {
                miq_api_json(array('error' => 'This script changed on another device.', 'conflict' => true, 'script' => miq_api_script_payload($existing)), 409);
            }
            $revision = (int) $existing['revision'] + 1;
            if ($expected_revision > 0) {
                $statement = miq_account_query(
                    "UPDATE {$scripts} SET name = ?, code = ?, source_code = ?, visibility = ?, status = ?, revision = ?, last_client_updated_at = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ? AND revision = ?",
                    'sssssisiii',
                    array($name, $code, $source, $visibility, $status, $revision, $client_updated_at, $script_id, $user_id, $expected_revision)
                );
                $affected = $statement->affected_rows;
                $statement->close();
                if ($affected !== 1) {
                    $current = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
                    miq_api_json(array('error' => 'This script changed on another device.', 'conflict' => true, 'script' => miq_api_script_payload($current)), 409);
                }
            } else {
                miq_account_query(
                    "UPDATE {$scripts} SET name = ?, code = ?, source_code = ?, visibility = ?, status = ?, revision = ?, last_client_updated_at = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?",
                    'sssssisii',
                    array($name, $code, $source, $visibility, $status, $revision, $client_updated_at, $script_id, $user_id)
                )->close();
            }
            if (!empty($body['create_version']) || !empty($body['publish'])) {
                miq_account_query("INSERT INTO {$versions} (script_id, user_id, revision, source_code, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiis', array($script_id, $user_id, $revision, $source))->close();
                miq_api_trim_versions($versions, 'script_id', $script_id);
            }
        } else {
            if (miq_api_count_rows($scripts, $user_id) >= miq_account_config()['max_script_count']) {
                miq_api_json(array('error' => 'Your Pine script storage limit has been reached.'), 422);
            }
            $asset_key = $asset_key !== '' ? $asset_key : miq_api_asset_key();
            $statement = miq_account_query(
                "INSERT INTO {$scripts} (user_id, asset_key, name, code, source_code, visibility, revision, status, last_client_updated_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())",
                'isssssss',
                array($user_id, $asset_key, $name, $code, $source, $visibility, $status, $client_updated_at)
            );
            $script_id = (int) miq_account_db()->insert_id;
            $statement->close();
            $revision = 1;
            if (!empty($body['create_version']) || !empty($body['publish'])) {
                miq_account_query("INSERT INTO {$versions} (script_id, user_id, revision, source_code, created_at) VALUES (?, ?, 1, ?, UTC_TIMESTAMP())", 'iis', array($script_id, $user_id, $source))->close();
            }
        }
        $saved_script = miq_account_fetch_one(miq_account_query("SELECT id, asset_key, name, code, visibility, revision, status, last_client_updated_at, created_at, updated_at FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
        miq_api_json(array('saved' => true, 'script' => miq_api_script_payload($saved_script), 'id' => $script_id, 'revision' => $revision));
    }

    if ($action === 'rename_script') {
        $scripts = miq_account_table('pine_scripts');
        $script_id = (int) ($body['id'] ?? 0);
        $name = miq_api_clean_text($body['name'] ?? '', 120);
        $expected_revision = max(0, (int) ($body['expected_revision'] ?? 0));
        $script = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
        if (!$script) miq_api_json(array('error' => 'Pine script not found.'), 404);
        if ($name === '') miq_api_json(array('error' => 'A script name is required.'), 422);
        if ($expected_revision > 0 && $expected_revision !== (int) $script['revision']) miq_api_json(array('error' => 'This script changed on another device.', 'conflict' => true, 'script' => miq_api_script_payload($script)), 409);
        $revision = (int) $script['revision'] + 1;
        $statement = $expected_revision > 0
            ? miq_account_query("UPDATE {$scripts} SET name = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ? AND revision = ?", 'siiii', array($name, $revision, $script_id, $user_id, $expected_revision))
            : miq_account_query("UPDATE {$scripts} SET name = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'siii', array($name, $revision, $script_id, $user_id));
        $renamed = $statement->affected_rows === 1;
        $statement->close();
        if (!$renamed) {
            $current = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
            miq_api_json(array('error' => 'This script changed on another device.', 'conflict' => true, 'script' => miq_api_script_payload($current)), 409);
        }
        miq_api_json(array('saved' => true, 'id' => $script_id, 'name' => $name, 'revision' => $revision));
    }

    if ($action === 'duplicate_script') {
        $scripts = miq_account_table('pine_scripts');
        $versions = miq_account_table('pine_script_versions');
        $script_id = (int) ($body['id'] ?? 0);
        $script = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
        if (!$script) miq_api_json(array('error' => 'Pine script not found.'), 404);
        if (miq_api_count_rows($scripts, $user_id) >= miq_account_config()['max_script_count']) miq_api_json(array('error' => 'Your Pine script storage limit has been reached.'), 422);
        $name = miq_api_clean_text($body['name'] ?? ('Copy of ' . $script['name']), 120);
        $asset_key = miq_api_asset_key();
        $statement = miq_account_query(
            "INSERT INTO {$scripts} (user_id, asset_key, name, code, source_code, visibility, revision, status, last_client_updated_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'private', 1, 'draft', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP())",
            'issss',
            array($user_id, $asset_key, $name, $script['code'], $script['source_code'])
        );
        $new_id = (int) miq_account_db()->insert_id;
        $statement->close();
        miq_account_query("INSERT INTO {$versions} (script_id, user_id, revision, source_code, created_at) VALUES (?, ?, 1, ?, UTC_TIMESTAMP())", 'iis', array($new_id, $user_id, $script['source_code']))->close();
        miq_api_json(array('saved' => true, 'id' => $new_id, 'asset_key' => $asset_key, 'revision' => 1));
    }

    if ($action === 'archive_script' || $action === 'unarchive_script' || $action === 'delete_script') {
        $script_id = (int) ($body['id'] ?? 0);
        $scripts = miq_account_table('pine_scripts');
        if ($action === 'archive_script' || $action === 'unarchive_script') {
            $status = $action === 'archive_script' ? 'archived' : 'draft';
            $statement = miq_account_query("UPDATE {$scripts} SET status = ?, visibility = 'private', updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'sii', array($status, $script_id, $user_id));
            $changed = $statement->affected_rows === 1;
            $statement->close();
            if (!$changed) miq_api_json(array('error' => 'Pine script not found.'), 404);
            miq_api_json(array('archived' => $status === 'archived', 'id' => $script_id, 'status' => $status));
        }
        $statement = miq_account_query("DELETE FROM {$scripts} WHERE id = ? AND user_id = ?", 'ii', array($script_id, $user_id));
        $deleted = $statement->affected_rows === 1;
        $statement->close();
        if (!$deleted) miq_api_json(array('error' => 'Pine script not found.'), 404);
        miq_api_json(array('deleted' => true, 'id' => $script_id));
    }

    if ($action === 'list_script_versions') {
        $script_id = (int) ($_GET['id'] ?? 0);
        $scripts = miq_account_table('pine_scripts');
        $versions = miq_account_table('pine_script_versions');
        $owned = miq_account_fetch_one(miq_account_query("SELECT id FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
        if (!$owned) miq_api_json(array('error' => 'Pine script not found.'), 404);
        $rows = miq_account_fetch_all(miq_account_query("SELECT id, revision, created_at FROM {$versions} WHERE script_id = ? AND user_id = ? ORDER BY revision DESC LIMIT 100", 'ii', array($script_id, $user_id)));
        miq_api_json(array('versions' => $rows));
    }

    if ($action === 'restore_script_version') {
        $script_id = (int) ($body['id'] ?? 0);
        $version_id = (int) ($body['version_id'] ?? 0);
        $expected_revision = max(0, (int) ($body['expected_revision'] ?? 0));
        $scripts = miq_account_table('pine_scripts');
        $versions = miq_account_table('pine_script_versions');
        $script = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
        $version = miq_account_fetch_one(miq_account_query("SELECT * FROM {$versions} WHERE id = ? AND script_id = ? AND user_id = ? LIMIT 1", 'iii', array($version_id, $script_id, $user_id)));
        if (!$script || !$version) miq_api_json(array('error' => 'Pine script version not found.'), 404);
        if ($expected_revision > 0 && $expected_revision !== (int) $script['revision']) miq_api_json(array('error' => 'This script changed on another device.', 'conflict' => true, 'script' => miq_api_script_payload($script)), 409);
        $revision = (int) $script['revision'] + 1;
        $statement = $expected_revision > 0
            ? miq_account_query("UPDATE {$scripts} SET source_code = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ? AND revision = ?", 'siiii', array($version['source_code'], $revision, $script_id, $user_id, $expected_revision))
            : miq_account_query("UPDATE {$scripts} SET source_code = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'siii', array($version['source_code'], $revision, $script_id, $user_id));
        $restored = $statement->affected_rows === 1;
        $statement->close();
        if (!$restored) {
            $current = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
            miq_api_json(array('error' => 'This script changed on another device.', 'conflict' => true, 'script' => miq_api_script_payload($current)), 409);
        }
        miq_account_query("INSERT INTO {$versions} (script_id, user_id, revision, source_code, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiis', array($script_id, $user_id, $revision, $version['source_code']))->close();
        miq_api_trim_versions($versions, 'script_id', $script_id);
        miq_api_json(array('saved' => true, 'id' => $script_id, 'revision' => $revision, 'source_code' => $version['source_code']));
    }

    if ($action === 'save_idea') {
        $title = miq_api_clean_text($body['title'] ?? '', 160);
        $direction = in_array(($body['direction'] ?? ''), array('bullish', 'bearish', 'neutral'), true) ? $body['direction'] : '';
        $thesis = miq_api_clean_text($body['thesis'] ?? '', 8000);
        if ($title === '' || $direction === '' || $thesis === '') miq_api_json(array('error' => 'Title, direction, and thesis are required.'), 422);
        $ideas = miq_account_table('community_ideas');
        $revisions = miq_account_table('community_idea_revisions');
        $idea_id = (int) ($body['id'] ?? 0);
        $status = !empty($body['submit']) ? 'pending' : 'draft';
        $visibility = !empty($body['submit']) ? 'public' : 'private';
        $fields = array(
            miq_api_clean_code($body['code'] ?? ''), $title, $direction, miq_api_clean_text($body['timeframe'] ?? '', 40),
            $thesis, miq_api_clean_text($body['catalyst'] ?? '', 4000), miq_api_clean_text($body['risk'] ?? '', 4000),
            miq_api_clean_text($body['disclosure'] ?? '', 500), $status, $visibility
        );
        if ($idea_id > 0) {
            $owner = miq_account_fetch_one(miq_account_query("SELECT id FROM {$ideas} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($idea_id, $user_id)));
        } else {
            $owner = null;
        }
        if ($owner) {
            miq_account_query("UPDATE {$ideas} SET code = ?, title = ?, direction = ?, timeframe = ?, thesis = ?, catalyst = ?, risk = ?, disclosure = ?, status = ?, visibility = ?, updated_at = UTC_TIMESTAMP(), published_at = CASE WHEN ? = 'pending' THEN NULL ELSE published_at END WHERE id = ? AND user_id = ?", 'sssssssssssii', array_merge($fields, array($status, $idea_id, $user_id)))->close();
        } else {
            $statement = miq_account_query("INSERT INTO {$ideas} (user_id, code, title, direction, timeframe, thesis, catalyst, risk, disclosure, status, visibility, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())", 'issssssssss', array_merge(array($user_id), $fields));
            $idea_id = (int) miq_account_db()->insert_id;
            $statement->close();
        }
        miq_account_query("INSERT INTO {$revisions} (idea_id, user_id, payload_json, created_at) VALUES (?, ?, ?, UTC_TIMESTAMP())", 'iis', array($idea_id, $user_id, json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)))->close();
        miq_api_json(array('saved' => true, 'id' => $idea_id, 'status' => $status));
    }

    if ($action === 'vote') {
        list($context_type, $context_key) = miq_api_pulse_context($body['context_type'] ?? 'site', $body['context_key'] ?? 'site');
        $timeframe = miq_api_pulse_timeframe($body['timeframe'] ?? '30d');
        $direction = $body['direction'] ?? '';
        if (!in_array($direction, array('bullish', 'bearish', 'neutral'), true)) miq_api_json(array('error' => 'Invalid community vote.'), 422);
        $vote_limit = miq_account_config()['rate_limits']['community_vote_user'];
        if (!miq_account_rate_limit('community_vote_user', (string) $user_id, $vote_limit['limit'], $vote_limit['window'])) {
            miq_api_json(array('error' => 'Too many community vote changes. Try again later.'), 429);
        }
        $counts = miq_community_save_vote($user_id, $context_type, $context_key, $direction);
        miq_api_json(array(
            'saved' => true,
            'counts' => $counts,
            'timeframe' => $timeframe,
            'period_end' => miq_api_pulse_period_end(),
            'trend_available' => miq_community_schema_ready(),
        ));
    }

    if ($action === 'report_idea') {
        $idea_id = (int) ($body['idea_id'] ?? 0);
        $reason = strtolower(miq_api_clean_text($body['reason'] ?? 'other', 80));
        $details = miq_api_clean_text($body['details'] ?? '', 500);
        $allowed_reasons = array('spam', 'misleading', 'harassment', 'undisclosed_conflict', 'other');
        if (!$idea_id || !in_array($reason, $allowed_reasons, true)) {
            miq_api_json(array('error' => 'Choose a valid report reason.'), 422);
        }
        if ($reason === 'other' && $details === '') {
            miq_api_json(array('error' => 'Add a short explanation for this report.'), 422);
        }
        $ideas = miq_account_table('community_ideas');
        $idea = miq_account_fetch_one(miq_account_query(
            "SELECT id, user_id FROM {$ideas} WHERE id = ? AND status = 'published' AND visibility = 'public' LIMIT 1",
            'i',
            array($idea_id)
        ));
        if (!$idea) miq_api_json(array('error' => 'That published idea is no longer available.'), 404);
        if ((int) $idea['user_id'] === $user_id) miq_api_json(array('error' => 'You cannot report your own idea.'), 422);
        $reports = miq_account_table('community_reports');
        $existing_report = miq_account_fetch_one(miq_account_query(
            "SELECT id FROM {$reports} WHERE idea_id = ? AND reporter_user_id = ? AND status = 'open' LIMIT 1",
            'ii',
            array($idea_id, $user_id)
        ));
        if ($existing_report) miq_api_json(array('error' => 'You already have an open report for this idea.'), 409);
        $report_limit = miq_account_config()['rate_limits']['community_report_user'];
        if (!miq_account_rate_limit('community_report_user', (string) $user_id, $report_limit['limit'], $report_limit['window'])) {
            miq_api_json(array('error' => 'Too many reports. Try again later.'), 429);
        }
        miq_account_query(
            "INSERT INTO {$reports} (idea_id, reporter_user_id, reason, details, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())",
            'iiss',
            array($idea_id, $user_id, $reason, $details)
        )->close();
        miq_api_json(array('saved' => true));
    }

    if ($action === 'moderation_dashboard' || $action === 'moderation_queue') {
        miq_api_require_moderator($user);
        miq_api_json(miq_api_moderation_dashboard());
    }

    if ($action === 'moderate_idea') {
        miq_api_require_moderator($user);
        $idea_id = (int) ($body['idea_id'] ?? 0);
        $decision = in_array(($body['decision'] ?? ''), array('publish', 'reject', 'hide'), true) ? $body['decision'] : '';
        $note = miq_api_clean_text($body['note'] ?? '', 500);
        if (!$idea_id || $decision === '') miq_api_json(array('error' => 'Invalid moderation action.'), 422);
        if (in_array($decision, array('reject', 'hide'), true) && $note === '') {
            miq_api_json(array('error' => 'Add a moderator note before rejecting or hiding content.'), 422);
        }
        $status = $decision === 'publish' ? 'published' : ($decision === 'reject' ? 'rejected' : 'hidden');
        $ideas = miq_account_table('community_ideas');
        $db = miq_account_db();
        $db->begin_transaction();
        try {
            $idea = miq_account_fetch_one(miq_account_query(
                "SELECT id, status FROM {$ideas} WHERE id = ? LIMIT 1 FOR UPDATE",
                'i',
                array($idea_id)
            ));
            if (!$idea) {
                $db->rollback();
                miq_api_json(array('error' => 'Idea not found.'), 404);
            }
            if (in_array($idea['status'], array('draft', 'archived'), true)) {
                $db->rollback();
                miq_api_json(array('error' => 'That idea is not available for moderation.'), 422);
            }
            miq_account_query(
                "UPDATE {$ideas} SET status = ?, visibility = CASE WHEN ? = 'published' THEN 'public' ELSE 'private' END, published_at = CASE WHEN ? = 'published' THEN COALESCE(published_at, UTC_TIMESTAMP()) ELSE published_at END, updated_at = UTC_TIMESTAMP() WHERE id = ?",
                'sssi',
                array($status, $status, $status, $idea_id)
            )->close();
            if ($decision === 'hide') {
                $reports = miq_account_table('community_reports');
                miq_account_query("UPDATE {$reports} SET status = 'reviewed' WHERE idea_id = ? AND status = 'open'", 'i', array($idea_id))->close();
            }
            miq_api_record_moderation_action($user_id, $idea_id, $decision, $note);
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        miq_api_json(array('saved' => true, 'status' => $status));
    }

    if ($action === 'moderate_report') {
        miq_api_require_moderator($user);
        $report_id = (int) ($body['report_id'] ?? 0);
        $decision = in_array(($body['decision'] ?? ''), array('dismiss', 'hide'), true) ? $body['decision'] : '';
        $note = miq_api_clean_text($body['note'] ?? '', 500);
        if (!$report_id || $decision === '') miq_api_json(array('error' => 'Invalid report action.'), 422);
        if ($note === '') miq_api_json(array('error' => 'Add a moderator note before resolving a report.'), 422);

        $reports = miq_account_table('community_reports');
        $ideas = miq_account_table('community_ideas');
        $db = miq_account_db();
        $db->begin_transaction();
        try {
            $report = miq_account_fetch_one(miq_account_query(
                "SELECT id, idea_id, status FROM {$reports} WHERE id = ? LIMIT 1 FOR UPDATE",
                'i',
                array($report_id)
            ));
            if (!$report) {
                $db->rollback();
                miq_api_json(array('error' => 'Report not found.'), 404);
            }
            if ($report['status'] !== 'open') {
                $db->rollback();
                miq_api_json(array('error' => 'That report has already been resolved.'), 409);
            }
            $idea_id = (int) $report['idea_id'];
            if ($decision === 'dismiss') {
                miq_account_query("UPDATE {$reports} SET status = 'dismissed' WHERE id = ?", 'i', array($report_id))->close();
                miq_api_record_moderation_action($user_id, $idea_id, 'report_dismissed', 'Report #' . $report_id . ': ' . $note);
            } else {
                miq_account_query("UPDATE {$ideas} SET status = 'hidden', visibility = 'private', updated_at = UTC_TIMESTAMP() WHERE id = ?", 'i', array($idea_id))->close();
                miq_account_query("UPDATE {$reports} SET status = 'reviewed' WHERE idea_id = ? AND status = 'open'", 'i', array($idea_id))->close();
                miq_api_record_moderation_action($user_id, $idea_id, 'hide', 'Report #' . $report_id . ': ' . $note);
            }
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        miq_api_json(array('saved' => true, 'status' => $decision === 'dismiss' ? 'dismissed' : 'hidden'));
    }

    miq_api_json(array('error' => 'Unknown account action.'), 404);
} catch (Throwable $error) {
    error_log('360MiQ account API error: ' . $error->getMessage());
    miq_api_json(array('error' => miq_account_config()['debug'] ? $error->getMessage() : 'The account service is temporarily unavailable.'), 500);
}
