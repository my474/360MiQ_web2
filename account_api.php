<?php
require_once __DIR__ . '/account/bootstrap.php';

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

function miq_api_counts($context_type, $context_key)
{
    $votes = miq_account_table('community_votes');
    $rows = miq_account_fetch_all(miq_account_query(
        "SELECT direction, COUNT(*) AS total FROM {$votes} WHERE context_type = ? AND context_key = ? GROUP BY direction",
        'ss',
        array($context_type, $context_key)
    ));
    $counts = array('bullish' => 0, 'bearish' => 0, 'neutral' => 0);
    foreach ($rows as $row) {
        if (isset($counts[$row['direction']])) {
            $counts[$row['direction']] = (int) $row['total'];
        }
    }
    return $counts;
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
        'charts' => miq_account_fetch_all(miq_account_query("SELECT id, name, code, visibility, revision, updated_at FROM {$charts} WHERE user_id = ? ORDER BY updated_at DESC LIMIT 30", 'i', array($user_id))),
        'scripts' => miq_account_fetch_all(miq_account_query("SELECT id, name, code, visibility, revision, status, updated_at FROM {$scripts} WHERE user_id = ? ORDER BY updated_at DESC LIMIT 30", 'i', array($user_id))),
        'ideas' => miq_account_fetch_all(miq_account_query("SELECT id, code, title, direction, timeframe, status, visibility, updated_at FROM {$ideas} WHERE user_id = ? ORDER BY updated_at DESC LIMIT 30", 'i', array($user_id))),
        'watchlists' => $lists,
    );
}

$body = miq_api_body();
$action = isset($_GET['action']) ? (string) $_GET['action'] : (isset($body['action']) ? (string) $body['action'] : '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    miq_api_require_post_csrf($body);
}

try {
    if ($action === 'pulse') {
        $context_type = miq_api_clean_text($_GET['context_type'] ?? 'site', 32);
        $context_key = miq_api_clean_text($_GET['context_key'] ?? 'site', 80);
        miq_api_json(array('counts' => miq_api_counts($context_type, $context_key)));
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

    if ($action === 'get_chart') {
        $code = miq_api_clean_code($_GET['code'] ?? '');
        $charts = miq_account_table('saved_charts');
        $chart = miq_account_fetch_one(miq_account_query(
            "SELECT id, name, code, layout_json, visibility, revision, updated_at FROM {$charts} WHERE user_id = ? AND code = ? ORDER BY CASE WHEN name LIKE 'Auto:%' THEN 0 ELSE 1 END, updated_at DESC LIMIT 1",
            'is',
            array($user_id, $code)
        ));
        if ($chart) {
            $chart['layout'] = json_decode($chart['layout_json'], true);
            unset($chart['layout_json']);
        }
        miq_api_json(array('chart' => $chart ?: null));
    }

    if ($action === 'save_chart') {
        $code = miq_api_clean_code($body['code'] ?? '');
        $name = miq_api_clean_text($body['name'] ?? ('Auto: ' . $code), 120);
        $layout = isset($body['layout']) ? $body['layout'] : null;
        $layout_json = is_string($layout) ? $layout : json_encode($layout, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($code === '' || !$layout_json || strlen($layout_json) > miq_account_config()['max_chart_bytes']) {
            miq_api_json(array('error' => 'This chart layout is invalid or too large.'), 422);
        }
        $charts = miq_account_table('saved_charts');
        $versions = miq_account_table('chart_versions');
        $existing = miq_account_fetch_one(miq_account_query(
            "SELECT id, revision FROM {$charts} WHERE user_id = ? AND code = ? AND name = ? LIMIT 1",
            'iss',
            array($user_id, $code, $name)
        ));
        if ($existing) {
            $revision = (int) $existing['revision'] + 1;
            miq_account_query("UPDATE {$charts} SET layout_json = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'siii', array($layout_json, $revision, (int) $existing['id'], $user_id))->close();
            if (empty($body['autosave'])) {
                miq_account_query("INSERT INTO {$versions} (chart_id, user_id, revision, layout_json, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiis', array((int) $existing['id'], $user_id, $revision, $layout_json))->close();
            }
            miq_api_json(array('saved' => true, 'id' => (int) $existing['id'], 'revision' => $revision));
        }
        $statement = miq_account_query("INSERT INTO {$charts} (user_id, name, code, layout_json, visibility, revision, created_at, updated_at) VALUES (?, ?, ?, ?, 'private', 1, UTC_TIMESTAMP(), UTC_TIMESTAMP())", 'isss', array($user_id, $name, $code, $layout_json));
        $id = (int) miq_account_db()->insert_id;
        $statement->close();
        miq_api_json(array('saved' => true, 'id' => $id, 'revision' => 1));
    }

    if ($action === 'save_script') {
        $name = miq_api_clean_text($body['name'] ?? 'Untitled script', 120);
        $source = (string) ($body['source_code'] ?? '');
        if ($source === '' || strlen($source) > miq_account_config()['max_script_chars']) {
            miq_api_json(array('error' => 'This Pine script is empty or too large.'), 422);
        }
        $scripts = miq_account_table('pine_scripts');
        $versions = miq_account_table('pine_script_versions');
        $script_id = (int) ($body['id'] ?? 0);
        if ($script_id > 0) {
            $existing = miq_account_fetch_one(miq_account_query("SELECT id, revision FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
        } else {
            $existing = miq_account_fetch_one(miq_account_query("SELECT id, revision FROM {$scripts} WHERE user_id = ? AND name = ? AND code = ? LIMIT 1", 'iss', array($user_id, $name, miq_api_clean_code($body['code'] ?? ''))));
            if ($existing) $script_id = (int) $existing['id'];
        }
        if ($existing) {
            $revision = (int) $existing['revision'] + 1;
            miq_account_query("UPDATE {$scripts} SET name = ?, code = ?, source_code = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'sssiii', array($name, miq_api_clean_code($body['code'] ?? ''), $source, $revision, $script_id, $user_id))->close();
            if (!empty($body['create_version']) || !empty($body['publish'])) {
                miq_account_query("INSERT INTO {$versions} (script_id, user_id, revision, source_code, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiis', array($script_id, $user_id, $revision, $source))->close();
            }
        } else {
            $statement = miq_account_query("INSERT INTO {$scripts} (user_id, name, code, source_code, visibility, revision, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'private', 1, 'draft', UTC_TIMESTAMP(), UTC_TIMESTAMP())", 'isss', array($user_id, $name, miq_api_clean_code($body['code'] ?? ''), $source));
            $script_id = (int) miq_account_db()->insert_id;
            $statement->close();
            $revision = 1;
        }
        miq_api_json(array('saved' => true, 'id' => $script_id, 'revision' => $revision));
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
        $context_type = miq_api_clean_text($body['context_type'] ?? 'site', 32);
        $context_key = miq_api_clean_text($body['context_key'] ?? 'site', 80);
        $direction = $body['direction'] ?? '';
        if (!in_array($direction, array('bullish', 'bearish', 'neutral'), true)) miq_api_json(array('error' => 'Invalid community vote.'), 422);
        $votes = miq_account_table('community_votes');
        miq_account_query("INSERT INTO {$votes} (user_id, context_type, context_key, direction, created_at, updated_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE direction = VALUES(direction), updated_at = UTC_TIMESTAMP()", 'isss', array($user_id, $context_type, $context_key, $direction))->close();
        miq_api_json(array('saved' => true, 'counts' => miq_api_counts($context_type, $context_key)));
    }

    if ($action === 'report_idea') {
        $idea_id = (int) ($body['idea_id'] ?? 0);
        $reports = miq_account_table('community_reports');
        miq_account_query("INSERT INTO {$reports} (idea_id, reporter_user_id, reason, details, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiss', array($idea_id, $user_id, miq_api_clean_text($body['reason'] ?? 'other', 80), miq_api_clean_text($body['details'] ?? '', 500)))->close();
        miq_api_json(array('saved' => true));
    }

    if ($action === 'moderation_queue') {
        if (!miq_account_is_moderator($user)) miq_api_json(array('error' => 'Moderator access is required.'), 403);
        $ideas = miq_account_table('community_ideas');
        $rows = miq_account_fetch_all(miq_account_query("SELECT i.*, u.display_name, u.email FROM {$ideas} i INNER JOIN " . miq_account_table('users') . " u ON u.id = i.user_id WHERE i.status = 'pending' ORDER BY i.updated_at ASC LIMIT 100"));
        miq_api_json(array('ideas' => $rows));
    }

    if ($action === 'moderate_idea') {
        if (!miq_account_is_moderator($user)) miq_api_json(array('error' => 'Moderator access is required.'), 403);
        $idea_id = (int) ($body['idea_id'] ?? 0);
        $decision = in_array(($body['decision'] ?? ''), array('publish', 'reject', 'hide'), true) ? $body['decision'] : '';
        if (!$idea_id || $decision === '') miq_api_json(array('error' => 'Invalid moderation action.'), 422);
        $status = $decision === 'publish' ? 'published' : ($decision === 'reject' ? 'rejected' : 'hidden');
        $ideas = miq_account_table('community_ideas');
        miq_account_query("UPDATE {$ideas} SET status = ?, visibility = CASE WHEN ? = 'published' THEN 'public' ELSE 'private' END, published_at = CASE WHEN ? = 'published' THEN UTC_TIMESTAMP() ELSE published_at END, updated_at = UTC_TIMESTAMP() WHERE id = ?", 'sssi', array($status, $status, $status, $idea_id))->close();
        miq_account_query("INSERT INTO " . miq_account_table('moderation_actions') . " (moderator_user_id, idea_id, action, note, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiss', array($user_id, $idea_id, $decision, miq_api_clean_text($body['note'] ?? '', 500)))->close();
        miq_api_json(array('saved' => true, 'status' => $status));
    }

    miq_api_json(array('error' => 'Unknown account action.'), 404);
} catch (Throwable $error) {
    error_log('360MiQ account API error: ' . $error->getMessage());
    miq_api_json(array('error' => miq_account_config()['debug'] ? $error->getMessage() : 'The account service is temporarily unavailable.'), 500);
}
