<?php
require_once __DIR__ . '/account/bootstrap.php';
require_once __DIR__ . '/account/community_sentiment.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: private, no-store, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Vary: Cookie', false);

function miq_api_json($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function miq_api_body()
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return array();
    }

    $maximum = max(1024, (int) miq_account_config()['max_api_request_bytes']);
    $content_length = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
    if ($content_length > $maximum) {
        miq_api_json(array('error' => 'The request is too large.'), 413);
    }

    $content_type = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
    if (strpos($content_type, 'application/json') === false) {
        return $_POST;
    }

    $raw = file_get_contents('php://input', false, null, 0, $maximum + 1);
    if ($raw === false || strlen($raw) > $maximum) {
        miq_api_json(array('error' => 'The request is too large.'), 413);
    }
    $body = json_decode($raw, true);
    if (!is_array($body)) {
        miq_api_json(array('error' => 'The JSON request body is invalid.'), 400);
    }
    return $body;
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

function miq_api_chat_history_payload($value)
{
    if (!is_array($value)) {
        return null;
    }

    $now_ms = (int) round(microtime(true) * 1000);
    $saved_at = isset($value['savedAt']) && is_numeric($value['savedAt'])
        ? (int) $value['savedAt']
        : $now_ms;
    if ($saved_at <= 0 || $saved_at > $now_ms + 86400000) {
        $saved_at = $now_ms;
    }

    $messages = array();
    if (isset($value['messages']) && is_array($value['messages'])) {
        // Keep one overflow candidate so an oversized newest message can be
        // rejected before applying the 40-message count limit.
        $raw_messages = array_values(array_slice($value['messages'], -41));
        $raw_message_count = count($raw_messages);
        $seen_message_ids = array();
        foreach ($raw_messages as $index => $message) {
            $message_data = is_array($message) ? $message : array();
            $html = is_string($message) ? $message : ($message_data['html'] ?? '');
            if (!is_string($html) || trim($html) === '') {
                continue;
            }

            $created_at = isset($message_data['createdAt']) && is_numeric($message_data['createdAt'])
                ? (int) $message_data['createdAt']
                : 0;
            if ($created_at <= 0 || $created_at > $now_ms + 86400000) {
                $created_at = max(1, $saved_at - (($raw_message_count - $index - 1) * 1000));
            }

            $message_id = isset($message_data['id']) && is_string($message_data['id'])
                ? preg_replace('/[^A-Za-z0-9._:-]/', '', substr($message_data['id'], 0, 96))
                : '';
            if ($message_id === '') {
                $message_id = 'legacy-' . substr(hash('sha256', $html . '|' . $created_at . '|' . $index), 0, 32);
            }
            if (isset($seen_message_ids[$message_id])) {
                $message_id = substr($message_id, 0, 80) . '-' . substr(hash('sha256', $html . '|' . $index), 0, 12);
            }
            $seen_message_ids[$message_id] = true;

            $role = isset($message_data['role']) && in_array($message_data['role'], array('user', 'assistant'), true)
                ? $message_data['role']
                : (strpos($html, 'is-user') !== false ? 'user' : 'assistant');
            $messages[] = array(
                'id' => $message_id,
                'role' => $role,
                'html' => $html,
                // Unix milliseconds are UTC and are formatted in the viewer's
                // local timezone only when the message is rendered.
                'createdAt' => $created_at,
            );
        }
    }

    $stockchat_dict = array();
    if (isset($value['stockchatDict']) && is_array($value['stockchatDict'])) {
        $keys = array_slice(array_keys($value['stockchatDict']), -40);
        foreach ($keys as $key) {
            $stockchat_dict[(string) $key] = $value['stockchatDict'][$key];
        }
    }

    $checkbox_states = array();
    if (isset($value['checkboxStates']) && is_array($value['checkboxStates'])) {
        foreach (array_slice($value['checkboxStates'], -400, 400, true) as $key => $checked) {
            $checkbox_states[(string) $key] = (bool) $checked;
        }
    }

    $state = array(
        'messages' => $messages,
        'stockchatDict' => $stockchat_dict,
        'checkboxStates' => $checkbox_states,
        'count' => isset($value['count']) && is_numeric($value['count']) ? max(0, (int) $value['count']) : 0,
        'savedAt' => $saved_at,
    );
    $max_bytes = (int) miq_account_config()['max_chat_history_bytes'];

    // Treat messages as atomic records. If one message cannot fit inside an
    // otherwise empty history, omit it before aggregate trimming so it cannot
    // evict every previously saved message.
    $state['messages'] = array_values(array_filter($state['messages'], function ($message) use ($saved_at, $max_bytes) {
        $single_message_json = json_encode(array(
            'messages' => array($message),
            'stockchatDict' => array(),
            'checkboxStates' => array(),
            'count' => 0,
            'savedAt' => $saved_at,
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $single_message_json !== false && strlen($single_message_json) <= $max_bytes;
    }));
    $state['messages'] = array_values(array_slice($state['messages'], -40));

    while (strlen((string) json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) > $max_bytes) {
        if (!empty($state['messages'])) {
            array_shift($state['messages']);
            continue;
        }
        if (!empty($state['stockchatDict'])) {
            array_shift($state['stockchatDict']);
            continue;
        }
        if (!empty($state['checkboxStates'])) {
            array_shift($state['checkboxStates']);
            continue;
        }
        break;
    }

    $encoded = json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($encoded === false || strlen($encoded) > $max_bytes) {
        return null;
    }

    return array('state' => $state, 'json' => $encoded, 'bytes' => strlen($encoded));
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

function miq_api_workspace_asset_key($user_id, $code)
{
    $hex = hash('sha256', 'workspace' . "\0" . (int) $user_id . "\0" . strtoupper((string) $code));
    $hex[12] = '5';
    $variant = hexdec($hex[16]);
    $hex[16] = dechex(($variant & 0x3) | 0x8);
    return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
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

function miq_api_screener_config($config)
{
    if (is_string($config)) {
        $config = json_decode($config, true);
    }
    if (!is_array($config)) {
        return null;
    }

    $allowed_filters = array(
        'market', 'sector', 'industry', 'marketcap', 'polar_ta', 'polar_va',
        'polar_fa', 'polar_trendgauge', 'channel_pos', 'channel_trend',
        'pe_stdev', 'pe_trend', 'pb_stdev', 'pb_trend', 'fscore', 'zscore',
        'mscore', 'ma10', 'ma20', 'ma50', 'ma100', 'ma200', 'ma250',
        'rsi14d', 'rsi14w', 'macdd', 'macdw', 'highlow', 'volume'
    );
    $allowed_markets = array(
        'NYSE + NASDAQ', 'NYSE', 'NASDAQ', 'NYSEARCA', 'LSE', 'ASX',
        'TSX', 'NSE', 'TYO', 'HKEX', 'SHSE', 'SZSE'
    );
    $source_filters = isset($config['filters']) && is_array($config['filters']) ? $config['filters'] : array();
    $filters = array();
    foreach ($allowed_filters as $filter_name) {
        if (!array_key_exists($filter_name, $source_filters) || !is_scalar($source_filters[$filter_name])) {
            continue;
        }
        $filter_value = preg_replace('/[\x00-\x1F\x7F]/u', '', trim((string) $source_filters[$filter_name]));
        $filter_value = miq_api_clean_text($filter_value, 160);
        if ($filter_value !== '') {
            $filters[$filter_name] = $filter_value;
        }
    }
    if (!isset($filters['market']) || !in_array($filters['market'], $allowed_markets, true)) {
        return null;
    }

    $source_table = isset($config['table']) && is_array($config['table']) ? $config['table'] : array();
    $order = array();
    if (isset($source_table['order']) && is_array($source_table['order'])) {
        foreach (array_slice($source_table['order'], 0, 3) as $sort) {
            if (!is_array($sort) || count($sort) < 2) continue;
            $column = (int) $sort[0];
            $direction = strtolower((string) $sort[1]);
            if ($column < 0 || $column > 39 || !in_array($direction, array('asc', 'desc'), true)) continue;
            $order[] = array($column, $direction);
        }
    }
    if (!$order) {
        $order = array(array(3, 'desc'));
    }

    $page_length = (int) ($source_table['pageLength'] ?? 30);
    if (!in_array($page_length, array(30, 60, 100, 200), true)) {
        $page_length = 30;
    }
    $columns = array();
    if (isset($source_table['columns']) && is_array($source_table['columns'])) {
        foreach (array_slice($source_table['columns'], 0, 40) as $visible) {
            $columns[] = (bool) $visible;
        }
    }

    $normalized = array(
        'version' => 1,
        'filters' => $filters,
        'table' => array(
            'order' => $order,
            'pageLength' => $page_length,
            'columns' => $columns,
        ),
    );
    $encoded = json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return $encoded && strlen($encoded) <= 32768 ? $normalized : null;
}

function miq_api_screener_preset_payload($preset)
{
    if (!$preset) return null;
    return array(
        'id' => (int) $preset['id'],
        'client_key' => $preset['client_key'],
        'name' => $preset['name'],
        'config' => json_decode($preset['config_json'], true),
        'is_default' => (bool) $preset['is_default'],
        'revision' => (int) $preset['revision'],
        'client_updated_at' => $preset['client_updated_at'],
        'created_at' => $preset['created_at'],
        'updated_at' => $preset['updated_at'],
    );
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

function miq_api_require_asset_write($user_id, $creates_version = false)
{
    $limits = miq_account_config()['rate_limits'];
    $write = $limits['asset_write_user'];
    if (!miq_account_rate_limit('asset_write_user', (string) $user_id, $write['limit'], $write['window'])) {
        miq_api_json(array('error' => 'Too many chart or Pine saves. Please wait and try again.'), 429);
    }
    if ($creates_version) {
        $version = $limits['asset_version_user'];
        if (!miq_account_rate_limit('asset_version_user', (string) $user_id, $version['limit'], $version['window'])) {
            miq_api_json(array('error' => 'Too many explicit versions were created. Please wait and try again.'), 429);
        }
    }
}

function miq_api_asset_storage_bytes($user_id)
{
    $sources = array(
        array(miq_account_table('saved_charts'), 'layout_json'),
        array(miq_account_table('chart_versions'), 'layout_json'),
        array(miq_account_table('pine_scripts'), 'source_code'),
        array(miq_account_table('pine_script_versions'), 'source_code'),
    );
    $total = 0;
    foreach ($sources as $source) {
        $row = miq_account_fetch_one(miq_account_query(
            "SELECT COALESCE(SUM(OCTET_LENGTH({$source[1]})), 0) AS total FROM {$source[0]} WHERE user_id = ?",
            'i',
            array($user_id)
        ));
        $total += (int) ($row['total'] ?? 0);
    }
    return $total;
}

function miq_api_version_storage_delta($table, $asset_column, $asset_id, $content_column, $new_bytes)
{
    if ($asset_id <= 0) return max(0, (int) $new_bytes);
    $limit = max(1, (int) miq_account_config()['max_asset_versions']);
    $row = miq_account_fetch_one(miq_account_query(
        "SELECT COUNT(*) AS total FROM {$table} WHERE {$asset_column} = ?",
        'i',
        array($asset_id)
    ));
    $delete_count = max(0, ((int) ($row['total'] ?? 0)) + 1 - $limit);
    if ($delete_count === 0) return max(0, (int) $new_bytes);
    $oldest = miq_account_fetch_one(miq_account_query(
        "SELECT COALESCE(SUM(content_bytes), 0) AS total FROM (SELECT OCTET_LENGTH({$content_column}) AS content_bytes FROM {$table} WHERE {$asset_column} = ? ORDER BY revision ASC, id ASC LIMIT {$delete_count}) AS oldest_versions",
        'i',
        array($asset_id)
    ));
    return (int) $new_bytes - (int) ($oldest['total'] ?? 0);
}

function miq_api_enforce_asset_storage($user_id, $delta_bytes)
{
    if ($delta_bytes <= 0) return;
    $maximum = max(1000000, (int) miq_account_config()['max_asset_storage_bytes']);
    $current = miq_api_asset_storage_bytes($user_id);
    if ($current + $delta_bytes > $maximum) {
        miq_api_json(array(
            'error' => 'Your chart and Pine storage limit has been reached.',
            'storage_bytes' => $current,
            'storage_limit_bytes' => $maximum,
        ), 422);
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
    $replies = miq_account_table('community_replies');

    $pending = miq_account_fetch_all(miq_account_query(
        "SELECT i.id, i.user_id, i.code, i.title, i.direction, i.timeframe, i.thesis, i.catalyst, i.risk, i.disclosure, i.status, i.visibility, i.created_at, i.updated_at, i.published_at, author.display_name AS author_display_name, author.email AS author_email, (SELECT COUNT(*) FROM {$reports} report_count WHERE report_count.idea_id = i.id AND report_count.status = 'open') AS open_report_count FROM {$ideas} i INNER JOIN {$users} author ON author.id = i.user_id WHERE i.status = 'pending' ORDER BY i.updated_at ASC LIMIT 100"
    ));
    $open_reports = miq_account_fetch_all(miq_account_query(
        "SELECT r.id AS report_id, r.reason AS report_reason, r.details AS report_details, r.status AS report_status, r.created_at AS report_created_at, reporter.display_name AS reporter_display_name, reporter.email AS reporter_email, i.id AS idea_id, i.user_id AS author_user_id, i.code, i.title, i.direction, i.timeframe, i.thesis, i.catalyst, i.risk, i.disclosure, i.status AS idea_status, i.visibility AS idea_visibility, i.created_at AS idea_created_at, i.updated_at AS idea_updated_at, i.published_at, author.display_name AS author_display_name, author.email AS author_email FROM {$reports} r INNER JOIN {$ideas} i ON i.id = r.idea_id INNER JOIN {$users} reporter ON reporter.id = r.reporter_user_id INNER JOIN {$users} author ON author.id = i.user_id WHERE r.status = 'open' ORDER BY r.created_at ASC LIMIT 100"
    ));
    $pending_replies = miq_account_fetch_all(miq_account_query(
        "SELECT reply.id, reply.idea_id, reply.user_id, reply.parent_reply_id, reply.body, reply.status, reply.created_at, reply.updated_at, author.display_name AS author_display_name, author.email AS author_email, idea.title AS idea_title, idea.code AS idea_code FROM {$replies} reply INNER JOIN {$users} author ON author.id = reply.user_id INNER JOIN {$ideas} idea ON idea.id = reply.idea_id WHERE reply.status = 'pending' ORDER BY reply.created_at ASC LIMIT 100"
    ));
    $history = miq_account_fetch_all(miq_account_query(
        "SELECT action_log.id, action_log.idea_id, action_log.action, action_log.note, action_log.created_at, moderator.display_name AS moderator_display_name, moderator.email AS moderator_email, i.title AS idea_title, i.code AS idea_code, i.status AS idea_status FROM {$actions} action_log INNER JOIN {$users} moderator ON moderator.id = action_log.moderator_user_id INNER JOIN {$ideas} i ON i.id = action_log.idea_id ORDER BY action_log.created_at DESC, action_log.id DESC LIMIT 100"
    ));
    $pending_count = miq_account_fetch_one(miq_account_query("SELECT COUNT(*) AS total FROM {$ideas} WHERE status = 'pending'"));
    $report_count = miq_account_fetch_one(miq_account_query("SELECT COUNT(*) AS total FROM {$reports} WHERE status = 'open'"));
    $reply_count = miq_account_fetch_one(miq_account_query("SELECT COUNT(*) AS total FROM {$replies} WHERE status = 'pending'"));
    $action_count = miq_account_fetch_one(miq_account_query("SELECT COUNT(*) AS total FROM {$actions}"));

    return array(
        'ideas' => $pending,
        'reports' => $open_reports,
        'replies' => $pending_replies,
        'history' => $history,
        'counts' => array(
            'pending' => (int) ($pending_count['total'] ?? 0),
            'reports' => (int) ($report_count['total'] ?? 0),
            'replies' => (int) ($reply_count['total'] ?? 0),
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

function miq_api_watchlists($user_id)
{
    $watchlists = miq_account_table('watchlists');
    $watchlist_items = miq_account_table('watchlist_items');
    $lists = miq_account_fetch_all(miq_account_query(
        "SELECT id, name, created_at, updated_at FROM {$watchlists} WHERE user_id = ? ORDER BY updated_at DESC, id DESC LIMIT 20",
        'i',
        array((int) $user_id)
    ));
    foreach ($lists as $index => $list) {
        $lists[$index]['items'] = miq_account_fetch_all(miq_account_query(
            "SELECT id, code, sort_order, created_at FROM {$watchlist_items} WHERE watchlist_id = ? AND user_id = ? ORDER BY sort_order, id",
            'ii',
            array((int) $list['id'], (int) $user_id)
        ));
    }
    return $lists;
}

function miq_api_workspace_optional($callback, $fallback)
{
    try {
        return $callback();
    } catch (Throwable $error) {
        error_log('360MiQ optional workspace feature unavailable: ' . $error->getMessage());
        return $fallback;
    }
}

function miq_api_annotate_public_ideas($rows, $viewer_user_id)
{
    if (!$rows) {
        return array();
    }
    $ids = array_map(function ($row) {
        return (int) $row['id'];
    }, $rows);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $reply_counts = array();
    $bookmarked = array();
    try {
        $replies = miq_account_table('community_replies');
        $count_rows = miq_account_fetch_all(miq_account_query(
            "SELECT idea_id, COUNT(*) AS total FROM {$replies} WHERE status = 'published' AND idea_id IN ({$placeholders}) GROUP BY idea_id",
            $types,
            $ids
        ));
        foreach ($count_rows as $count_row) {
            $reply_counts[(int) $count_row['idea_id']] = (int) $count_row['total'];
        }
        if ((int) $viewer_user_id > 0) {
            $bookmarks = miq_account_table('community_bookmarks');
            $bookmark_params = array_merge(array((int) $viewer_user_id), $ids);
            $bookmark_rows = miq_account_fetch_all(miq_account_query(
                "SELECT idea_id FROM {$bookmarks} WHERE user_id = ? AND idea_id IN ({$placeholders})",
                'i' . $types,
                $bookmark_params
            ));
            foreach ($bookmark_rows as $bookmark_row) {
                $bookmarked[(int) $bookmark_row['idea_id']] = true;
            }
        }
    } catch (Throwable $error) {
        error_log('360MiQ community annotation unavailable: ' . $error->getMessage());
    }
    foreach ($rows as $index => $row) {
        $idea_id = (int) $row['id'];
        $rows[$index]['reply_count'] = $reply_counts[$idea_id] ?? 0;
        $rows[$index]['bookmarked'] = !empty($bookmarked[$idea_id]);
    }
    return $rows;
}

function miq_api_workspace_quote_payload($user_id, $lists, $alerts)
{
    $watchlist_codes = array();
    $watchlist_code_set = array();
    foreach ($lists as $list) {
        foreach ($list['items'] as $item) {
            $code = strtoupper(trim((string) ($item['code'] ?? '')));
            if ($code === '') {
                continue;
            }
            $watchlist_codes[] = $code;
            $watchlist_code_set[$code] = true;
        }
    }
    $quote_codes = $watchlist_codes;
    foreach ($alerts as $alert) {
        if ($alert['status'] === 'active') {
            $code = strtoupper(trim((string) ($alert['code'] ?? '')));
            if ($code !== '') {
                $quote_codes[] = $code;
            }
        }
    }
    $quote_codes = array_values(array_unique($quote_codes));
    $quotes = array();
    $watchlist_quotes = array();
    try {
        $quotes = miq_stock_quotes($quote_codes);
        miq_account_evaluate_price_alerts($quotes, $user_id);
        $watchlist_quotes = array_values(array_filter($quotes, function ($quote) use ($watchlist_code_set) {
            $code = strtoupper(trim((string) ($quote['code'] ?? '')));
            return $code !== '' && isset($watchlist_code_set[$code]);
        }));
        if ($alerts) {
            $alerts_table = miq_account_table('price_alerts');
            $alerts = miq_account_fetch_all(miq_account_query(
                "SELECT id, code, condition_type, target_price, status, last_price, triggered_at, created_at, updated_at FROM {$alerts_table} WHERE user_id = ? ORDER BY FIELD(status, 'triggered', 'active', 'disabled'), updated_at DESC LIMIT 100",
                'i',
                array($user_id)
            ));
        }
    } catch (Throwable $error) {
        error_log('360MiQ workspace quote failure: ' . $error->getMessage());
    }

    return array(
        'watchlist_quotes' => $watchlist_quotes,
        'alerts' => $alerts,
        'quotes_loaded' => true,
    );
}

function miq_api_workspace($user, $include_quotes = true)
{
    $user_id = (int) $user['id'];
    $charts = miq_account_table('saved_charts');
    $scripts = miq_account_table('pine_scripts');
    $searches = miq_account_table('recent_searches');
    $screener_presets = miq_account_table('screener_presets');
    $idea_rows = array();
    $idea_count = 0;
    if (miq_community_enabled()) {
        $ideas = miq_account_table('community_ideas');
        $idea_rows = miq_account_fetch_all(miq_account_query("SELECT id, code, title, direction, timeframe, status, visibility, updated_at FROM {$ideas} WHERE user_id = ? ORDER BY updated_at DESC LIMIT 30", 'i', array($user_id)));
        $idea_count = miq_api_count_rows($ideas, $user_id);
    }
    $lists = miq_api_watchlists($user_id);
    $alerts = miq_api_workspace_optional(function () use ($user_id) {
        $table = miq_account_table('price_alerts');
        return miq_account_fetch_all(miq_account_query(
            "SELECT id, code, condition_type, target_price, status, last_price, triggered_at, created_at, updated_at FROM {$table} WHERE user_id = ? ORDER BY FIELD(status, 'triggered', 'active', 'disabled'), updated_at DESC LIMIT 100",
            'i',
            array($user_id)
        ));
    }, array());
    $quote_payload = $include_quotes
        ? miq_api_workspace_quote_payload($user_id, $lists, $alerts)
        : array('watchlist_quotes' => array(), 'alerts' => $alerts, 'quotes_loaded' => false);
    $alerts = $quote_payload['alerts'];
    $notes = miq_api_workspace_optional(function () use ($user_id, $charts, $scripts) {
        $table = miq_account_table('research_notes');
        return miq_account_fetch_all(miq_account_query(
            "SELECT n.id, n.stock_code, n.chart_id, n.script_id, n.title, n.body, n.created_at, n.updated_at, c.name AS chart_name, s.name AS script_name FROM {$table} n LEFT JOIN {$charts} c ON c.id = n.chart_id AND c.user_id = n.user_id LEFT JOIN {$scripts} s ON s.id = n.script_id AND s.user_id = n.user_id WHERE n.user_id = ? ORDER BY n.updated_at DESC LIMIT 100",
            'i',
            array($user_id)
        ));
    }, array());
    $notifications = miq_api_workspace_optional(function () use ($user_id) {
        $table = miq_account_table('notifications');
        $community_filter = miq_community_enabled() ? '' : " AND notification_type NOT LIKE 'community_%'";
        return miq_account_fetch_all(miq_account_query(
            "SELECT id, notification_type, title, message, link_url, read_at, created_at FROM {$table} WHERE user_id = ?{$community_filter} ORDER BY created_at DESC LIMIT 100",
            'i',
            array($user_id)
        ));
    }, array());
    foreach ($notifications as $index => $notification) {
        if (($notification['notification_type'] ?? '') === 'price_alert') {
            $notifications[$index]['message'] = miq_account_format_alert_message($notification['message'] ?? '');
        }
    }
    $bookmarks = array();
    if (miq_community_enabled()) {
        $bookmarks = miq_api_workspace_optional(function () use ($user_id) {
            $table = miq_account_table('community_bookmarks');
            $ideas = miq_account_table('community_ideas');
            return miq_account_fetch_all(miq_account_query(
                "SELECT b.idea_id, b.created_at AS bookmarked_at, i.code, i.title, i.direction, i.timeframe, i.updated_at FROM {$table} b INNER JOIN {$ideas} i ON i.id = b.idea_id WHERE b.user_id = ? AND i.status = 'published' AND i.visibility = 'public' ORDER BY b.created_at DESC LIMIT 100",
                'i',
                array($user_id)
            ));
        }, array());
    }
    $watchlist_item_count = 0;
    foreach ($lists as $list) {
        $watchlist_item_count += count($list['items']);
    }
    $active_alert_count = 0;
    foreach ($alerts as $alert) {
        if ($alert['status'] === 'active') {
            $active_alert_count += 1;
        }
    }
    return array(
        'searches' => miq_account_fetch_all(miq_account_query("SELECT id, code, exchange, display_name, searched_at FROM {$searches} WHERE user_id = ? ORDER BY searched_at DESC, id DESC LIMIT 20", 'i', array($user_id))),
        'charts' => miq_account_fetch_all(miq_account_query("SELECT id, asset_key, name, code, kind, visibility, revision, last_client_updated_at, updated_at FROM {$charts} WHERE user_id = ? ORDER BY updated_at DESC LIMIT 50", 'i', array($user_id))),
        'scripts' => miq_account_fetch_all(miq_account_query("SELECT id, asset_key, name, code, visibility, revision, status, last_client_updated_at, updated_at FROM {$scripts} WHERE user_id = ? ORDER BY updated_at DESC LIMIT 50", 'i', array($user_id))),
        'screener_presets' => miq_account_fetch_all(miq_account_query("SELECT client_key, name, is_default, revision, updated_at FROM {$screener_presets} WHERE user_id = ? ORDER BY is_default DESC, updated_at DESC LIMIT 50", 'i', array($user_id))),
        'ideas' => $idea_rows,
        'watchlists' => $lists,
        'watchlist_quotes' => $quote_payload['watchlist_quotes'],
        'quotes_loaded' => $quote_payload['quotes_loaded'],
        'notes' => $notes,
        'alerts' => $alerts,
        'preferences' => miq_account_user_preferences($user_id),
        'notifications' => $notifications,
        'bookmarks' => $bookmarks,
        'counts' => array(
            'charts' => miq_api_count_rows($charts, $user_id),
            'scripts' => miq_api_count_rows($scripts, $user_id),
            'searches' => miq_api_count_rows($searches, $user_id),
            'screener_presets' => miq_api_count_rows($screener_presets, $user_id),
            'ideas' => $idea_count,
            'watchlists' => count($lists),
            'watchlist_items' => $watchlist_item_count,
            'notes' => count($notes),
            'alerts' => count($alerts),
            'active_alerts' => $active_alert_count,
            'notifications_unread' => miq_account_unread_notification_count($user_id),
            'bookmarks' => count($bookmarks),
        ),
    );
}

$body = miq_api_body();
$action = isset($_GET['action']) ? (string) $_GET['action'] : (isset($body['action']) ? (string) $body['action'] : '');
$request_method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$read_actions = array(
    'pulse', 'pulse_trend', 'public_ideas', 'list_idea_replies', 'workspace', 'workspace_quotes',
    'account_bootstrap', 'get_preferences', 'get_notification_settings', 'list_notes', 'list_alerts', 'list_screener_presets',
    'list_watchlists', 'watchlist_state', 'list_charts', 'get_chart',
    'list_chart_versions', 'list_scripts', 'get_script', 'list_script_versions',
    'moderation_dashboard', 'moderation_queue', 'get_chat_history'
);
if ($request_method === 'GET') {
    if (!in_array($action, $read_actions, true)) {
        header('Allow: POST');
        miq_api_json(array('error' => 'This action requires POST.'), 405);
    }
} elseif ($request_method === 'POST') {
    miq_api_require_post_csrf($body);
} else {
    header('Allow: GET, POST');
    miq_api_json(array('error' => 'Method not allowed.'), 405);
}
$community_actions = array(
    'pulse', 'pulse_trend', 'public_ideas', 'list_idea_replies', 'save_idea', 'vote',
    'report_idea', 'bookmark_idea', 'save_idea_reply', 'delete_idea_reply',
    'moderation_dashboard', 'moderation_queue', 'moderate_idea', 'moderate_reply', 'moderate_report'
);
if (!miq_community_enabled() && in_array($action, $community_actions, true)) {
    miq_api_json(array('error' => 'Community features are currently unavailable.'), 404);
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
        $viewer = miq_account_current_user();
        if ($idea_id > 0) {
            $rows = miq_account_fetch_all(miq_account_query("SELECT i.id, i.code, i.title, i.direction, i.timeframe, i.thesis, i.catalyst, i.risk, i.disclosure, i.published_at, u.display_name FROM {$ideas} i INNER JOIN " . miq_account_table('users') . " u ON u.id = i.user_id WHERE i.id = ? AND i.status = 'published' AND i.visibility = 'public' LIMIT 1", 'i', array($idea_id)));
        } else {
            $rows = miq_account_fetch_all(miq_account_query("SELECT i.id, i.code, i.title, i.direction, i.timeframe, i.thesis, i.catalyst, i.risk, i.disclosure, i.published_at, u.display_name FROM {$ideas} i INNER JOIN " . miq_account_table('users') . " u ON u.id = i.user_id WHERE i.status = 'published' AND i.visibility = 'public' AND (? = '' OR i.code = ?) ORDER BY i.published_at DESC LIMIT 40", 'ss', array($context_key, $context_key)));
        }
        miq_api_json(array('ideas' => miq_api_annotate_public_ideas($rows, $viewer ? (int) $viewer['id'] : 0)));
    }

    if ($action === 'list_idea_replies') {
        $idea_id = (int) ($_GET['idea_id'] ?? 0);
        $ideas = miq_account_table('community_ideas');
        $idea = miq_account_fetch_one(miq_account_query(
            "SELECT id FROM {$ideas} WHERE id = ? AND status = 'published' AND visibility = 'public' LIMIT 1",
            'i',
            array($idea_id)
        ));
        if (!$idea) {
            miq_api_json(array('error' => 'Published idea not found.'), 404);
        }
        $replies = miq_account_table('community_replies');
        $users = miq_account_table('users');
        $rows = miq_account_fetch_all(miq_account_query(
            "SELECT r.id, r.idea_id, r.user_id, r.parent_reply_id, r.body, r.created_at, r.updated_at, u.display_name FROM {$replies} r INNER JOIN {$users} u ON u.id = r.user_id WHERE r.idea_id = ? AND r.status = 'published' ORDER BY r.created_at, r.id LIMIT 200",
            'i',
            array($idea_id)
        ));
        $viewer = miq_account_current_user();
        foreach ($rows as $index => $row) {
            $rows[$index]['can_delete'] = $viewer && ((int) $viewer['id'] === (int) $row['user_id'] || miq_account_is_moderator($viewer));
            unset($rows[$index]['user_id']);
        }
        miq_api_json(array('replies' => $rows));
    }

    if ($action === 'account_bootstrap') {
        $viewer = miq_account_current_user();
        $config = miq_account_config();
        $base_url = rtrim((string) $config['base_url'], '/');
        $payload = array(
            'loggedIn' => (bool) $viewer,
            'userId' => $viewer ? (int) $viewer['id'] : null,
            'displayName' => $viewer ? (string) $viewer['display_name'] : '',
            'csrfToken' => $viewer ? miq_account_csrf_token() : '',
            'apiUrl' => $base_url . '/account_api.php',
            'workspaceUrl' => $base_url . '/workspace',
            'assetBaseUrl' => $base_url . '/assets',
            'chatHistoryMaxBytes' => (int) $config['max_chat_history_bytes'],
            'preferences' => $viewer ? miq_account_user_preferences((int) $viewer['id']) : miq_account_preference_defaults(),
            'unreadNotifications' => $viewer ? miq_account_unread_notification_count((int) $viewer['id']) : 0,
            'notificationConfig' => miq_account_notification_web_config(),
        );
        miq_api_json($payload);
    }

    $user = miq_api_user();
    $user_id = (int) $user['id'];

    // The workspace quote lookup can be slow when the market-data database is
    // unavailable. Do not let that optional work hold the PHP session lock and
    // block a full-page refresh in the same browser.
    if (($action === 'workspace' || $action === 'workspace_quotes') && session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    if ($action === 'get_chat_history') {
        $history = null;
        $updated_at = null;
        $history_bytes = 0;
        $table = miq_account_table('chat_histories');
        if (miq_account_table_exists('chat_histories')) {
            $row = miq_account_fetch_one(miq_account_query(
                "SELECT history_json, history_bytes, updated_at FROM {$table} WHERE user_id = ? LIMIT 1",
                'i',
                array($user_id)
            ));
            if ($row) {
                $history = json_decode((string) $row['history_json'], true);
                $updated_at = $row['updated_at'];
                $history_bytes = (int) $row['history_bytes'];
            }
        }
        miq_api_json(array(
            'history' => is_array($history) ? $history : null,
            'bytes' => $history_bytes,
            'updated_at' => $updated_at,
            'csrf_token' => miq_account_csrf_token(),
            'max_bytes' => (int) miq_account_config()['max_chat_history_bytes'],
            'sync_available' => miq_account_table_exists('chat_histories'),
        ));
    }

    if ($action === 'save_chat_history') {
        $history = miq_api_chat_history_payload($body['history'] ?? null);
        if (!$history) {
            miq_api_json(array('error' => 'Chat history is invalid or exceeds the storage limit.'), 413);
        }
        $table = miq_account_table('chat_histories');
        if (!miq_account_table_exists('chat_histories')) {
            miq_api_json(array('error' => 'Chat history sync is not available yet.'), 503);
        }
        miq_account_query(
            "INSERT INTO {$table} (user_id, history_json, history_bytes, updated_at) VALUES (?, ?, ?, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE history_json = VALUES(history_json), history_bytes = VALUES(history_bytes), updated_at = UTC_TIMESTAMP()",
            'isi',
            array($user_id, $history['json'], $history['bytes'])
        )->close();
        miq_api_json(array(
            'saved' => true,
            'bytes' => $history['bytes'],
            'max_bytes' => (int) miq_account_config()['max_chat_history_bytes'],
        ));
    }

    if ($action === 'clear_chat_history') {
        if (miq_account_table_exists('chat_histories')) {
            $table = miq_account_table('chat_histories');
            miq_account_query("DELETE FROM {$table} WHERE user_id = ?", 'i', array($user_id))->close();
        }
        miq_api_json(array('cleared' => true));
    }

    if ($action === 'save_search') {
        $code = miq_api_clean_code($body['code'] ?? '');
        if ($code === '') miq_api_json(array('error' => 'A stock code is required.'), 422);
        $searches = miq_account_table('recent_searches');
        $exchange = miq_api_clean_text($body['exchange'] ?? '', 32);
        $display_name = miq_api_clean_text($body['display_name'] ?? '', 160);
        $preserve_searched_at = !empty($body['preserve_searched_at']);
        $preserved_searched_at = $preserve_searched_at
            ? miq_api_client_datetime($body['searched_at'] ?? '')
            : null;
        if ($preserved_searched_at && strtotime($preserved_searched_at) > time() + 60) {
            $preserved_searched_at = null;
        }

        if ($preserve_searched_at && $preserved_searched_at) {
            // Local history is replayed on page load. Keep its original time,
            // while retaining the newest timestamp if another device is ahead.
            miq_account_query(
                "INSERT INTO {$searches} (user_id, code, exchange, display_name, searched_at) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE exchange = VALUES(exchange), display_name = VALUES(display_name), searched_at = IF(searched_at >= VALUES(searched_at), searched_at, VALUES(searched_at))",
                'issss',
                array($user_id, $code, $exchange, $display_name, $preserved_searched_at)
            )->close();
        } else {
            // Replace the touched symbol so the auto-increment id provides a
            // stable tie-breaker when several searches share the same second.
            miq_account_query(
                "REPLACE INTO {$searches} (user_id, code, exchange, display_name, searched_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())",
                'isss',
                array($user_id, $code, $exchange, $display_name)
            )->close();
        }
        miq_account_query("DELETE FROM {$searches} WHERE user_id = ? AND id NOT IN (SELECT id FROM (SELECT id FROM {$searches} WHERE user_id = ? ORDER BY searched_at DESC, id DESC LIMIT 50) recent_ids)", 'ii', array($user_id, $user_id))->close();
        miq_api_json(array('saved' => true));
    }

    if ($action === 'workspace') {
        $defer_quotes = filter_var($_GET['defer_quotes'] ?? false, FILTER_VALIDATE_BOOLEAN);
        miq_api_json(array('workspace' => miq_api_workspace($user, !$defer_quotes)));
    }

    if ($action === 'workspace_quotes') {
        $lists = miq_api_watchlists($user_id);
        $alerts_table = miq_account_table('price_alerts');
        $alerts = miq_account_fetch_all(miq_account_query(
            "SELECT id, code, condition_type, target_price, status, last_price, triggered_at, created_at, updated_at FROM {$alerts_table} WHERE user_id = ? ORDER BY FIELD(status, 'triggered', 'active', 'disabled'), updated_at DESC LIMIT 100",
            'i',
            array($user_id)
        ));
        miq_api_json(array('workspace_quotes' => miq_api_workspace_quote_payload($user_id, $lists, $alerts)));
    }

    if ($action === 'get_preferences') {
        miq_api_json(array('preferences' => miq_account_user_preferences($user_id)));
    }

    if ($action === 'get_notification_settings') {
        miq_api_json(array_merge(
            miq_account_notification_settings_payload($user_id),
            array('csrf_token' => miq_account_csrf_token())
        ));
    }

    if ($action === 'save_notification_settings') {
        $values = is_array($body['preferences'] ?? null) ? $body['preferences'] : $body;
        $preferences = miq_account_save_notification_preferences($user_id, $values);
        $settings = miq_account_notification_settings_payload($user_id);
        $settings['preferences'] = $preferences;
        miq_api_json(array_merge(array('saved' => true, 'csrf_token' => miq_account_csrf_token()), $settings));
    }

    if ($action === 'register_notification_device') {
        $device_limit = miq_account_config()['rate_limits']['notification_device_user'];
        if (!miq_account_rate_limit('notification_device_user', (string) $user_id, $device_limit['limit'], $device_limit['window'])) {
            miq_api_json(array('error' => 'Too many notification-device updates. Please wait and try again.'), 429);
        }
        $channel = miq_account_notification_clean_channel($body['channel'] ?? '');
        $registration_target = miq_account_notification_target_payload($body);
        $device_target = $registration_target ? $registration_target['target'] : '';
        if ($channel === '' || !$registration_target || strlen($device_target) < 20 || strlen($device_target) > 4096) {
            miq_api_json(array('error' => 'A valid, unambiguous notification channel and delivery target are required.'), 422);
        }
        $device = miq_account_register_notification_device($user_id, $channel, $device_target, array(
            'target_type' => $registration_target['target_type'],
            'label' => $body['label'] ?? '',
            'app_version' => $body['app_version'] ?? '',
            'installation_id' => $body['installation_id'] ?? '',
        ));
        $settings = miq_account_notification_settings_payload($user_id);
        miq_api_json(array_merge(array('saved' => true, 'device' => $device, 'csrf_token' => miq_account_csrf_token()), $settings));
    }

    if ($action === 'unregister_notification_device') {
        $registration_target = miq_account_notification_target_payload($body);
        if (!$registration_target) {
            miq_api_json(array('error' => 'The notification delivery target is ambiguous.'), 422);
        }
        $removed = miq_account_unregister_notification_device(
            $user_id,
            (int) ($body['device_id'] ?? 0),
            $body['channel'] ?? '',
            $registration_target['target'],
            $body['installation_id'] ?? ''
        );
        $settings = miq_account_notification_settings_payload($user_id);
        miq_api_json(array_merge(array('saved' => true, 'removed' => (bool) $removed, 'csrf_token' => miq_account_csrf_token()), $settings));
    }

    if ($action === 'save_preferences') {
        $preferences = miq_account_save_preferences($user_id, $body);
        miq_api_json(array('saved' => true, 'preferences' => $preferences));
    }

    if ($action === 'list_notes') {
        $notes = miq_account_table('research_notes');
        $charts = miq_account_table('saved_charts');
        $scripts = miq_account_table('pine_scripts');
        $code = miq_api_clean_code($_GET['code'] ?? '');
        $chart_id = (int) ($_GET['chart_id'] ?? 0);
        $script_id = (int) ($_GET['script_id'] ?? 0);
        $where = 'n.user_id = ?';
        $types = 'i';
        $params = array($user_id);
        if ($code !== '') {
            $where .= ' AND n.stock_code = ?';
            $types .= 's';
            $params[] = $code;
        }
        if ($chart_id > 0) {
            $where .= ' AND n.chart_id = ?';
            $types .= 'i';
            $params[] = $chart_id;
        }
        if ($script_id > 0) {
            $where .= ' AND n.script_id = ?';
            $types .= 'i';
            $params[] = $script_id;
        }
        $rows = miq_account_fetch_all(miq_account_query(
            "SELECT n.id, n.stock_code, n.chart_id, n.script_id, n.title, n.body, n.created_at, n.updated_at, c.name AS chart_name, s.name AS script_name FROM {$notes} n LEFT JOIN {$charts} c ON c.id = n.chart_id AND c.user_id = n.user_id LEFT JOIN {$scripts} s ON s.id = n.script_id AND s.user_id = n.user_id WHERE {$where} ORDER BY n.updated_at DESC LIMIT 200",
            $types,
            $params
        ));
        miq_api_json(array('notes' => $rows));
    }

    if ($action === 'save_note') {
        $notes = miq_account_table('research_notes');
        $charts = miq_account_table('saved_charts');
        $scripts = miq_account_table('pine_scripts');
        $note_id = (int) ($body['id'] ?? 0);
        $stock_code = miq_api_clean_code($body['stock_code'] ?? '');
        $chart_id = (int) ($body['chart_id'] ?? 0);
        $script_id = (int) ($body['script_id'] ?? 0);
        $title = miq_api_clean_text($body['title'] ?? '', 160);
        $note_body = miq_api_clean_text($body['body'] ?? '', 20000);
        if ($title === '' || $note_body === '') {
            miq_api_json(array('error' => 'A note title and note text are required.'), 422);
        }
        if ($stock_code === '' && $chart_id <= 0 && $script_id <= 0) {
            miq_api_json(array('error' => 'Link the note to a stock, saved chart, or Pine script.'), 422);
        }
        if ($chart_id > 0) {
            $owned_chart = miq_account_fetch_one(miq_account_query("SELECT id FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
            if (!$owned_chart) miq_api_json(array('error' => 'Saved chart not found.'), 404);
        }
        if ($script_id > 0) {
            $owned_script = miq_account_fetch_one(miq_account_query("SELECT id FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
            if (!$owned_script) miq_api_json(array('error' => 'Pine script not found.'), 404);
        }
        if ($note_id > 0) {
            $owned_note = miq_account_fetch_one(miq_account_query(
                "SELECT id FROM {$notes} WHERE id = ? AND user_id = ? LIMIT 1",
                'ii',
                array($note_id, $user_id)
            ));
            if (!$owned_note) miq_api_json(array('error' => 'Research note not found.'), 404);
            $statement = miq_account_query(
                "UPDATE {$notes} SET stock_code = ?, chart_id = ?, script_id = ?, title = ?, body = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?",
                'siissii',
                array($stock_code !== '' ? $stock_code : null, $chart_id ?: null, $script_id ?: null, $title, $note_body, $note_id, $user_id)
            );
            $statement->close();
        } else {
            if (miq_api_count_rows($notes, $user_id) >= miq_account_config()['max_note_count']) {
                miq_api_json(array('error' => 'Your research note limit has been reached.'), 422);
            }
            $statement = miq_account_query(
                "INSERT INTO {$notes} (user_id, stock_code, chart_id, script_id, title, body, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())",
                'isiiss',
                array($user_id, $stock_code !== '' ? $stock_code : null, $chart_id ?: null, $script_id ?: null, $title, $note_body)
            );
            $note_id = (int) miq_account_db()->insert_id;
            $statement->close();
        }
        $row = miq_account_fetch_one(miq_account_query(
            "SELECT id, stock_code, chart_id, script_id, title, body, created_at, updated_at FROM {$notes} WHERE id = ? AND user_id = ? LIMIT 1",
            'ii',
            array($note_id, $user_id)
        ));
        miq_api_json(array('saved' => true, 'note' => $row));
    }

    if ($action === 'delete_note') {
        $note_id = (int) ($body['id'] ?? 0);
        $notes = miq_account_table('research_notes');
        $statement = miq_account_query("DELETE FROM {$notes} WHERE id = ? AND user_id = ?", 'ii', array($note_id, $user_id));
        $deleted = $statement->affected_rows === 1;
        $statement->close();
        if (!$deleted) miq_api_json(array('error' => 'Research note not found.'), 404);
        miq_api_json(array('deleted' => true, 'id' => $note_id));
    }

    if ($action === 'list_alerts') {
        $alerts = miq_account_table('price_alerts');
        $rows = miq_account_fetch_all(miq_account_query(
            "SELECT id, code, condition_type, target_price, status, last_price, triggered_at, created_at, updated_at FROM {$alerts} WHERE user_id = ? ORDER BY FIELD(status, 'triggered', 'active', 'disabled'), updated_at DESC LIMIT 200",
            'i',
            array($user_id)
        ));
        miq_api_json(array('alerts' => $rows));
    }

    if ($action === 'save_alert') {
        $alerts = miq_account_table('price_alerts');
        $alert_id = (int) ($body['id'] ?? 0);
        $code = miq_api_clean_code($body['code'] ?? '');
        $condition = in_array(($body['condition_type'] ?? ''), array('above', 'below'), true) ? $body['condition_type'] : '';
        $target = (float) ($body['target_price'] ?? 0);
        if ($code === '' || $condition === '' || !is_finite($target) || $target <= 0 || $target > 1000000000000) {
            miq_api_json(array('error' => 'Choose a stock, an above/below condition, and a valid target price.'), 422);
        }
        $alert_quote = miq_stock_quotes(array($code));
        if (!$alert_quote) {
            miq_api_json(array('error' => 'That stock code does not have a current quote.'), 422);
        }
        if ($alert_id > 0) {
            $owned_alert = miq_account_fetch_one(miq_account_query(
                "SELECT id FROM {$alerts} WHERE id = ? AND user_id = ? LIMIT 1",
                'ii',
                array($alert_id, $user_id)
            ));
            if (!$owned_alert) miq_api_json(array('error' => 'Price alert not found.'), 404);
            $statement = miq_account_query(
                "UPDATE {$alerts} SET code = ?, condition_type = ?, target_price = ?, status = 'active', triggered_at = NULL, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?",
                'ssdii',
                array($code, $condition, $target, $alert_id, $user_id)
            );
            $statement->close();
        } else {
            if (miq_api_count_rows($alerts, $user_id) >= miq_account_config()['max_alert_count']) {
                miq_api_json(array('error' => 'Your price alert limit has been reached.'), 422);
            }
            $statement = miq_account_query(
                "INSERT INTO {$alerts} (user_id, code, condition_type, target_price, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP())",
                'issd',
                array($user_id, $code, $condition, $target)
            );
            $alert_id = (int) miq_account_db()->insert_id;
            $statement->close();
        }
        miq_account_evaluate_price_alerts($alert_quote, $user_id);
        $row = miq_account_fetch_one(miq_account_query(
            "SELECT id, code, condition_type, target_price, status, last_price, triggered_at, created_at, updated_at FROM {$alerts} WHERE id = ? AND user_id = ? LIMIT 1",
            'ii',
            array($alert_id, $user_id)
        ));
        miq_api_json(array('saved' => true, 'alert' => $row));
    }

    if ($action === 'set_alert_status') {
        $alert_id = (int) ($body['id'] ?? 0);
        $status = ($body['status'] ?? '') === 'active' ? 'active' : 'disabled';
        $alerts = miq_account_table('price_alerts');
        $owned_alert = miq_account_fetch_one(miq_account_query(
            "SELECT id FROM {$alerts} WHERE id = ? AND user_id = ? LIMIT 1",
            'ii',
            array($alert_id, $user_id)
        ));
        if (!$owned_alert) miq_api_json(array('error' => 'Price alert not found.'), 404);
        $statement = miq_account_query(
            "UPDATE {$alerts} SET status = ?, triggered_at = CASE WHEN ? = 'active' THEN NULL ELSE triggered_at END, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?",
            'ssii',
            array($status, $status, $alert_id, $user_id)
        );
        $statement->close();
        miq_api_json(array('saved' => true, 'id' => $alert_id, 'status' => $status));
    }

    if ($action === 'delete_alert') {
        $alert_id = (int) ($body['id'] ?? 0);
        $alerts = miq_account_table('price_alerts');
        $statement = miq_account_query("DELETE FROM {$alerts} WHERE id = ? AND user_id = ?", 'ii', array($alert_id, $user_id));
        $deleted = $statement->affected_rows === 1;
        $statement->close();
        if (!$deleted) miq_api_json(array('error' => 'Price alert not found.'), 404);
        miq_api_json(array('deleted' => true, 'id' => $alert_id));
    }

    if ($action === 'mark_notification_read') {
        $notification_id = (int) ($body['id'] ?? 0);
        $notifications = miq_account_table('notifications');
        if ($notification_id > 0) {
            miq_account_query("UPDATE {$notifications} SET read_at = COALESCE(read_at, UTC_TIMESTAMP()) WHERE id = ? AND user_id = ?", 'ii', array($notification_id, $user_id))->close();
        } else {
            miq_account_query("UPDATE {$notifications} SET read_at = UTC_TIMESTAMP() WHERE user_id = ? AND read_at IS NULL", 'i', array($user_id))->close();
        }
        miq_api_json(array('saved' => true, 'unread' => miq_account_unread_notification_count($user_id)));
    }

    if ($action === 'list_screener_presets') {
        $presets = miq_account_table('screener_presets');
        $rows = miq_account_fetch_all(miq_account_query(
            "SELECT id, client_key, name, config_json, is_default, revision, client_updated_at, created_at, updated_at FROM {$presets} WHERE user_id = ? ORDER BY is_default DESC, updated_at DESC, id DESC",
            'i',
            array($user_id)
        ));
        miq_api_json(array(
            'presets' => array_map('miq_api_screener_preset_payload', $rows),
            'limit' => max(1, (int) miq_account_config()['max_screener_preset_count']),
        ));
    }

    if ($action === 'save_screener_preset') {
        $presets = miq_account_table('screener_presets');
        $name = miq_api_clean_text($body['name'] ?? '', 120);
        $config = miq_api_screener_config($body['config'] ?? null);
        if ($name === '' || !$config) {
            miq_api_json(array('error' => 'A preset name and a valid completed screen are required.'), 422);
        }
        $config_json = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $client_key = miq_api_existing_asset_key($body['client_key'] ?? '');
        if ($client_key === '') {
            $client_key = miq_api_asset_key();
        }
        $client_updated_at = miq_api_client_datetime($body['client_updated_at'] ?? '') ?: gmdate('Y-m-d H:i:s');
        $existing = miq_account_fetch_one(miq_account_query(
            "SELECT * FROM {$presets} WHERE user_id = ? AND client_key = ? LIMIT 1",
            'is',
            array($user_id, $client_key)
        ));
        $duplicate = miq_account_fetch_one(miq_account_query(
            "SELECT id FROM {$presets} WHERE user_id = ? AND name = ? LIMIT 1",
            'is',
            array($user_id, $name)
        ));
        if ($duplicate && (!$existing || (int) $duplicate['id'] !== (int) $existing['id'])) {
            miq_api_json(array('error' => 'A screener preset with that name already exists.'), 409);
        }
        if (!$existing && miq_api_count_rows($presets, $user_id) >= max(1, (int) miq_account_config()['max_screener_preset_count'])) {
            miq_api_json(array('error' => 'You have reached the screener preset limit.'), 422);
        }

        $make_default = !empty($body['make_default']) || (!$existing && miq_api_count_rows($presets, $user_id) === 0);
        $is_default = ($make_default || ($existing && !empty($existing['is_default']))) ? 1 : 0;
        $revision = $existing ? ((int) $existing['revision'] + 1) : 1;
        $db = miq_account_db();
        $db->begin_transaction();
        try {
            if ($make_default) {
                miq_account_query("UPDATE {$presets} SET is_default = 0 WHERE user_id = ?", 'i', array($user_id))->close();
            }
            if ($existing) {
                miq_account_query(
                    "UPDATE {$presets} SET name = ?, config_json = ?, is_default = ?, revision = ?, client_updated_at = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?",
                    'ssiisii',
                    array($name, $config_json, $is_default, $revision, $client_updated_at, (int) $existing['id'], $user_id)
                )->close();
                $preset_id = (int) $existing['id'];
            } else {
                $statement = miq_account_query(
                    "INSERT INTO {$presets} (user_id, client_key, name, config_json, is_default, revision, client_updated_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())",
                    'isssiis',
                    array($user_id, $client_key, $name, $config_json, $is_default, $revision, $client_updated_at)
                );
                $preset_id = (int) $db->insert_id;
                $statement->close();
            }
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        $saved = miq_account_fetch_one(miq_account_query(
            "SELECT id, client_key, name, config_json, is_default, revision, client_updated_at, created_at, updated_at FROM {$presets} WHERE id = ? AND user_id = ? LIMIT 1",
            'ii',
            array($preset_id, $user_id)
        ));
        miq_api_json(array('saved' => true, 'preset' => miq_api_screener_preset_payload($saved)));
    }

    if ($action === 'set_default_screener_preset') {
        $presets = miq_account_table('screener_presets');
        $client_key = miq_api_existing_asset_key($body['client_key'] ?? '');
        if ($client_key === '') miq_api_json(array('error' => 'Choose a screener preset.'), 422);
        $db = miq_account_db();
        $db->begin_transaction();
        try {
            $owned = miq_account_fetch_one(miq_account_query(
                "SELECT id FROM {$presets} WHERE user_id = ? AND client_key = ? LIMIT 1 FOR UPDATE",
                'is',
                array($user_id, $client_key)
            ));
            if (!$owned) {
                $db->rollback();
                miq_api_json(array('error' => 'Screener preset not found.'), 404);
            }
            miq_account_query("UPDATE {$presets} SET is_default = 0 WHERE user_id = ?", 'i', array($user_id))->close();
            miq_account_query("UPDATE {$presets} SET is_default = 1, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'ii', array((int) $owned['id'], $user_id))->close();
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        miq_api_json(array('saved' => true, 'client_key' => $client_key));
    }

    if ($action === 'delete_screener_preset') {
        $presets = miq_account_table('screener_presets');
        $client_key = miq_api_existing_asset_key($body['client_key'] ?? '');
        if ($client_key === '') miq_api_json(array('error' => 'Choose a screener preset.'), 422);
        $db = miq_account_db();
        $db->begin_transaction();
        try {
            $existing = miq_account_fetch_one(miq_account_query(
                "SELECT id, is_default FROM {$presets} WHERE user_id = ? AND client_key = ? LIMIT 1 FOR UPDATE",
                'is',
                array($user_id, $client_key)
            ));
            if (!$existing) {
                $db->rollback();
                miq_api_json(array('error' => 'Screener preset not found.'), 404);
            }
            miq_account_query("DELETE FROM {$presets} WHERE id = ? AND user_id = ?", 'ii', array((int) $existing['id'], $user_id))->close();
            if (!empty($existing['is_default'])) {
                miq_account_query("UPDATE {$presets} SET is_default = 1 WHERE user_id = ? ORDER BY updated_at DESC, id DESC LIMIT 1", 'i', array($user_id))->close();
            }
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        miq_api_json(array('deleted' => true, 'client_key' => $client_key));
    }

    if ($action === 'list_watchlists' || $action === 'watchlist_state') {
        $lists = miq_api_watchlists($user_id);
        $codes = array();
        foreach ($lists as $list) {
            foreach ($list['items'] as $item) {
                $codes[] = $item['code'];
            }
        }
        $requested_code = miq_api_clean_code($_GET['code'] ?? '');
        if ($requested_code !== '') {
            $codes[] = $requested_code;
        }
        $quotes = array();
        try {
            $quotes = miq_stock_quotes($codes);
            miq_account_evaluate_price_alerts($quotes, $user_id);
        } catch (Throwable $error) {
            error_log('360MiQ watchlist quote failure: ' . $error->getMessage());
        }
        $payload = array('watchlists' => $lists, 'quotes' => $quotes);
        if ($action === 'watchlist_state') {
            $notes = miq_account_table('research_notes');
            $alerts = miq_account_table('price_alerts');
            $payload['code'] = $requested_code;
            $payload['notes'] = $requested_code === '' ? array() : miq_account_fetch_all(miq_account_query(
                "SELECT id, stock_code, chart_id, script_id, title, body, created_at, updated_at FROM {$notes} WHERE user_id = ? AND stock_code = ? ORDER BY updated_at DESC LIMIT 20",
                'is',
                array($user_id, $requested_code)
            ));
            $payload['alerts'] = $requested_code === '' ? array() : miq_account_fetch_all(miq_account_query(
                "SELECT id, code, condition_type, target_price, status, last_price, triggered_at, created_at, updated_at FROM {$alerts} WHERE user_id = ? AND code = ? ORDER BY updated_at DESC LIMIT 20",
                'is',
                array($user_id, $requested_code)
            ));
        }
        miq_api_json($payload);
    }

    if ($action === 'create_watchlist') {
        $name = miq_api_clean_text($body['name'] ?? 'My Watchlist', 120);
        if ($name === '') miq_api_json(array('error' => 'A watchlist name is required.'), 422);
        $watchlists = miq_account_table('watchlists');
        if (miq_api_count_rows($watchlists, $user_id) >= miq_account_config()['max_watchlist_count']) {
            miq_api_json(array('error' => 'Your watchlist limit has been reached.'), 422);
        }
        $duplicate = miq_account_fetch_one(miq_account_query("SELECT id FROM {$watchlists} WHERE user_id = ? AND name = ? LIMIT 1", 'is', array($user_id, $name)));
        if ($duplicate) miq_api_json(array('error' => 'A watchlist with that name already exists.'), 409);
        $statement = miq_account_query("INSERT INTO {$watchlists} (user_id, name, created_at, updated_at) VALUES (?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())", 'is', array($user_id, $name));
        $id = (int) miq_account_db()->insert_id;
        $statement->close();
        miq_api_json(array('saved' => true, 'id' => $id, 'watchlists' => miq_api_watchlists($user_id)));
    }

    if ($action === 'rename_watchlist') {
        $watchlist_id = (int) ($body['watchlist_id'] ?? 0);
        $name = miq_api_clean_text($body['name'] ?? '', 120);
        $watchlists = miq_account_table('watchlists');
        if (!$watchlist_id || $name === '') miq_api_json(array('error' => 'Choose a watchlist and enter a name.'), 422);
        $duplicate = miq_account_fetch_one(miq_account_query("SELECT id FROM {$watchlists} WHERE user_id = ? AND name = ? AND id <> ? LIMIT 1", 'isi', array($user_id, $name, $watchlist_id)));
        if ($duplicate) miq_api_json(array('error' => 'A watchlist with that name already exists.'), 409);
        $owned = miq_account_fetch_one(miq_account_query("SELECT id FROM {$watchlists} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($watchlist_id, $user_id)));
        if (!$owned) miq_api_json(array('error' => 'Watchlist not found.'), 404);
        miq_account_query("UPDATE {$watchlists} SET name = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'sii', array($name, $watchlist_id, $user_id))->close();
        miq_api_json(array('saved' => true, 'watchlists' => miq_api_watchlists($user_id)));
    }

    if ($action === 'delete_watchlist') {
        $watchlist_id = (int) ($body['watchlist_id'] ?? 0);
        $watchlists = miq_account_table('watchlists');
        $items = miq_account_table('watchlist_items');
        $owned = miq_account_fetch_one(miq_account_query("SELECT id FROM {$watchlists} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($watchlist_id, $user_id)));
        if (!$owned) miq_api_json(array('error' => 'Watchlist not found.'), 404);
        $db = miq_account_db();
        $db->begin_transaction();
        try {
            miq_account_query("DELETE FROM {$items} WHERE watchlist_id = ? AND user_id = ?", 'ii', array($watchlist_id, $user_id))->close();
            miq_account_query("DELETE FROM {$watchlists} WHERE id = ? AND user_id = ?", 'ii', array($watchlist_id, $user_id))->close();
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        miq_api_json(array('deleted' => true, 'watchlists' => miq_api_watchlists($user_id)));
    }

    if ($action === 'add_watchlist_item') {
        $watchlist_id = (int) ($body['watchlist_id'] ?? 0);
        $code = miq_api_clean_code($body['code'] ?? '');
        if (!$watchlist_id || $code === '') miq_api_json(array('error' => 'A watchlist and stock code are required.'), 422);
        $watchlists = miq_account_table('watchlists');
        $items = miq_account_table('watchlist_items');
        $owned = miq_account_fetch_one(miq_account_query("SELECT id FROM {$watchlists} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($watchlist_id, $user_id)));
        if (!$owned) miq_api_json(array('error' => 'Watchlist not found.'), 404);
        if (!miq_stock_quotes(array($code))) {
            miq_api_json(array('error' => 'That stock code does not have a current quote.'), 422);
        }
        $item_count = miq_account_fetch_one(miq_account_query("SELECT COUNT(*) AS total, COALESCE(MAX(sort_order), -1) AS max_order FROM {$items} WHERE watchlist_id = ? AND user_id = ?", 'ii', array($watchlist_id, $user_id)));
        $existing = miq_account_fetch_one(miq_account_query("SELECT id FROM {$items} WHERE watchlist_id = ? AND user_id = ? AND code = ? LIMIT 1", 'iis', array($watchlist_id, $user_id, $code)));
        if (!$existing && (int) ($item_count['total'] ?? 0) >= miq_account_config()['max_watchlist_items']) {
            miq_api_json(array('error' => 'This watchlist has reached its stock limit.'), 422);
        }
        miq_account_query(
            "INSERT INTO {$items} (watchlist_id, user_id, code, sort_order, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE sort_order = sort_order",
            'iisi',
            array($watchlist_id, $user_id, $code, (int) ($item_count['max_order'] ?? -1) + 1)
        )->close();
        miq_account_query("UPDATE {$watchlists} SET updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'ii', array($watchlist_id, $user_id))->close();
        miq_api_json(array('saved' => true, 'watchlists' => miq_api_watchlists($user_id)));
    }

    if ($action === 'remove_watchlist_item') {
        $watchlist_id = (int) ($body['watchlist_id'] ?? 0);
        $code = miq_api_clean_code($body['code'] ?? '');
        $watchlists = miq_account_table('watchlists');
        $items = miq_account_table('watchlist_items');
        miq_account_query("DELETE FROM {$items} WHERE watchlist_id = ? AND user_id = ? AND code = ?", 'iis', array($watchlist_id, $user_id, $code))->close();
        miq_account_query("UPDATE {$watchlists} SET updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'ii', array($watchlist_id, $user_id))->close();
        miq_api_json(array('saved' => true, 'watchlists' => miq_api_watchlists($user_id)));
    }

    if ($action === 'reorder_watchlist_items') {
        $watchlist_id = (int) ($body['watchlist_id'] ?? 0);
        $codes = miq_stock_clean_codes($body['codes'] ?? array(), miq_account_config()['max_watchlist_items']);
        $watchlists = miq_account_table('watchlists');
        $items = miq_account_table('watchlist_items');
        $owned = miq_account_fetch_one(miq_account_query("SELECT id FROM {$watchlists} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($watchlist_id, $user_id)));
        if (!$owned) miq_api_json(array('error' => 'Watchlist not found.'), 404);
        $db = miq_account_db();
        $db->begin_transaction();
        try {
            foreach ($codes as $index => $code) {
                miq_account_query(
                    "UPDATE {$items} SET sort_order = ? WHERE watchlist_id = ? AND user_id = ? AND code = ?",
                    'iiis',
                    array($index, $watchlist_id, $user_id, $code)
                )->close();
            }
            miq_account_query("UPDATE {$watchlists} SET updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'ii', array($watchlist_id, $user_id))->close();
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        miq_api_json(array('saved' => true, 'watchlists' => miq_api_watchlists($user_id)));
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
        }
        if (!$existing && $kind === 'workspace') {
            $existing = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE user_id = ? AND code = ? AND (kind = 'workspace' OR name LIKE 'Auto:%') ORDER BY updated_at DESC LIMIT 1", 'is', array($user_id, $code)));
        } elseif (!$existing && $asset_key === '') {
            // Backward compatibility for named clients that predate stable asset keys.
            $existing = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE user_id = ? AND code = ? AND name = ? LIMIT 1", 'iss', array($user_id, $code, $name)));
        }
        if (!$existing && miq_api_count_rows($charts, $user_id) >= miq_account_config()['max_chart_count']) {
            miq_api_json(array('error' => 'Your chart storage limit has been reached.'), 422);
        }
        if (!$existing && $kind === 'named' && miq_api_count_rows($charts, $user_id, "AND kind = 'named'") >= miq_account_config()['max_named_chart_count']) {
            miq_api_json(array('error' => 'Your named chart limit has been reached.'), 422);
        }
        $will_create_version = $existing
            ? (!empty($body['create_version']) || empty($body['autosave']))
            : ($kind === 'named' || !empty($body['create_version']));
        miq_api_require_asset_write($user_id, $will_create_version);
        $storage_delta = strlen($layout_json) - ($existing ? strlen((string) $existing['layout_json']) : 0);
        if ($will_create_version) {
            $storage_delta += miq_api_version_storage_delta($versions, 'chart_id', $existing ? (int) $existing['id'] : 0, 'layout_json', strlen($layout_json));
        }
        miq_api_enforce_asset_storage($user_id, $storage_delta);
        $db = miq_account_db();
        $db->begin_transaction();
        try {
        if ($existing) {
            $chart_id = (int) $existing['id'];
            if ($expected_revision > 0 && $expected_revision !== (int) $existing['revision']) {
                $db->rollback();
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
                    $db->rollback();
                    miq_api_json(array('error' => 'This chart changed on another device.', 'conflict' => true, 'chart' => miq_api_chart_payload($current)), 409);
                }
            } else {
                miq_account_query(
                    "UPDATE {$charts} SET name = ?, code = ?, kind = ?, layout_json = ?, visibility = ?, revision = ?, last_client_updated_at = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?",
                    'sssssisii',
                    array($name, $code, $kind, $layout_json, $visibility, $revision, $client_updated_at, $chart_id, $user_id)
                )->close();
            }
            if ($will_create_version) {
                miq_account_query("INSERT INTO {$versions} (chart_id, user_id, revision, layout_json, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiis', array($chart_id, $user_id, $revision, $layout_json))->close();
                miq_api_trim_versions($versions, 'chart_id', $chart_id);
            }
        } else {
            $asset_key = $kind === 'workspace'
                ? miq_api_workspace_asset_key($user_id, $code)
                : ($asset_key !== '' ? $asset_key : miq_api_asset_key());
            if ($kind === 'workspace') {
                $statement = miq_account_query(
                    "INSERT INTO {$charts} (user_id, asset_key, name, code, kind, layout_json, visibility, revision, last_client_updated_at, created_at, updated_at) VALUES (?, ?, ?, ?, 'workspace', ?, ?, 1, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), name = VALUES(name), code = VALUES(code), kind = 'workspace', layout_json = VALUES(layout_json), visibility = VALUES(visibility), revision = revision + 1, last_client_updated_at = VALUES(last_client_updated_at), updated_at = UTC_TIMESTAMP()",
                    'issssss',
                    array($user_id, $asset_key, $name, $code, $layout_json, $visibility, $client_updated_at)
                );
                $chart_id = (int) miq_account_db()->insert_id;
                $statement->close();
                $saved_workspace = miq_account_fetch_one(miq_account_query("SELECT revision FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
                $revision = (int) ($saved_workspace['revision'] ?? 1);
            } else {
                $statement = miq_account_query(
                    "INSERT INTO {$charts} (user_id, asset_key, name, code, kind, layout_json, visibility, revision, last_client_updated_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())",
                    'isssssss',
                    array($user_id, $asset_key, $name, $code, $kind, $layout_json, $visibility, $client_updated_at)
                );
                $chart_id = (int) miq_account_db()->insert_id;
                $statement->close();
                $revision = 1;
            }
            if ($will_create_version) {
                miq_account_query("INSERT INTO {$versions} (chart_id, user_id, revision, layout_json, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiis', array($chart_id, $user_id, $revision, $layout_json))->close();
            }
        }
        $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
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
        miq_api_require_asset_write($user_id, true);
        miq_api_enforce_asset_storage($user_id, strlen((string) $chart['layout_json']) * 2);
        $db = miq_account_db();
        $db->begin_transaction();
        try {
        $statement = miq_account_query(
            "INSERT INTO {$charts} (user_id, asset_key, name, code, kind, layout_json, visibility, revision, last_client_updated_at, created_at, updated_at) VALUES (?, ?, ?, ?, 'named', ?, 'private', 1, NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP())",
            'issss',
            array($user_id, $asset_key, $name, $chart['code'], $chart['layout_json'])
        );
        $new_id = (int) miq_account_db()->insert_id;
        $statement->close();
        miq_account_query("INSERT INTO {$versions} (chart_id, user_id, revision, layout_json, created_at) VALUES (?, ?, 1, ?, UTC_TIMESTAMP())", 'iis', array($new_id, $user_id, $chart['layout_json']))->close();
        $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
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
        miq_api_require_asset_write($user_id, true);
        $storage_delta = strlen((string) $version['layout_json']) - strlen((string) $chart['layout_json']);
        $storage_delta += miq_api_version_storage_delta($versions, 'chart_id', $chart_id, 'layout_json', strlen((string) $version['layout_json']));
        miq_api_enforce_asset_storage($user_id, $storage_delta);
        $revision = (int) $chart['revision'] + 1;
        $db = miq_account_db();
        $db->begin_transaction();
        try {
        $statement = $expected_revision > 0
            ? miq_account_query("UPDATE {$charts} SET layout_json = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ? AND revision = ?", 'siiii', array($version['layout_json'], $revision, $chart_id, $user_id, $expected_revision))
            : miq_account_query("UPDATE {$charts} SET layout_json = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'siii', array($version['layout_json'], $revision, $chart_id, $user_id));
        $restored = $statement->affected_rows === 1;
        $statement->close();
        if (!$restored) {
            $current = miq_account_fetch_one(miq_account_query("SELECT * FROM {$charts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($chart_id, $user_id)));
            $db->rollback();
            miq_api_json(array('error' => 'This chart changed on another device.', 'conflict' => true, 'chart' => miq_api_chart_payload($current)), 409);
        }
        miq_account_query("INSERT INTO {$versions} (chart_id, user_id, revision, layout_json, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiis', array($chart_id, $user_id, $revision, $version['layout_json']))->close();
        miq_api_trim_versions($versions, 'chart_id', $chart_id);
        $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
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
        if (!$existing && miq_api_count_rows($scripts, $user_id) >= miq_account_config()['max_script_count']) {
            miq_api_json(array('error' => 'Your Pine script storage limit has been reached.'), 422);
        }
        $will_create_version = !empty($body['create_version']) || !empty($body['publish']);
        miq_api_require_asset_write($user_id, $will_create_version);
        $storage_delta = strlen($source) - ($existing ? strlen((string) $existing['source_code']) : 0);
        if ($will_create_version) {
            $storage_delta += miq_api_version_storage_delta($versions, 'script_id', $existing ? (int) $existing['id'] : 0, 'source_code', strlen($source));
        }
        miq_api_enforce_asset_storage($user_id, $storage_delta);
        $db = miq_account_db();
        $db->begin_transaction();
        try {
        if ($existing) {
            $script_id = (int) $existing['id'];
            if ($expected_revision > 0 && $expected_revision !== (int) $existing['revision']) {
                $db->rollback();
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
                    $db->rollback();
                    miq_api_json(array('error' => 'This script changed on another device.', 'conflict' => true, 'script' => miq_api_script_payload($current)), 409);
                }
            } else {
                miq_account_query(
                    "UPDATE {$scripts} SET name = ?, code = ?, source_code = ?, visibility = ?, status = ?, revision = ?, last_client_updated_at = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?",
                    'sssssisii',
                    array($name, $code, $source, $visibility, $status, $revision, $client_updated_at, $script_id, $user_id)
                )->close();
            }
            if ($will_create_version) {
                miq_account_query("INSERT INTO {$versions} (script_id, user_id, revision, source_code, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiis', array($script_id, $user_id, $revision, $source))->close();
                miq_api_trim_versions($versions, 'script_id', $script_id);
            }
        } else {
            $asset_key = $asset_key !== '' ? $asset_key : miq_api_asset_key();
            $statement = miq_account_query(
                "INSERT INTO {$scripts} (user_id, asset_key, name, code, source_code, visibility, revision, status, last_client_updated_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())",
                'isssssss',
                array($user_id, $asset_key, $name, $code, $source, $visibility, $status, $client_updated_at)
            );
            $script_id = (int) miq_account_db()->insert_id;
            $statement->close();
            $revision = 1;
            if ($will_create_version) {
                miq_account_query("INSERT INTO {$versions} (script_id, user_id, revision, source_code, created_at) VALUES (?, ?, 1, ?, UTC_TIMESTAMP())", 'iis', array($script_id, $user_id, $source))->close();
            }
        }
        $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
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
        miq_api_require_asset_write($user_id, true);
        miq_api_enforce_asset_storage($user_id, strlen((string) $script['source_code']) * 2);
        $db = miq_account_db();
        $db->begin_transaction();
        try {
        $statement = miq_account_query(
            "INSERT INTO {$scripts} (user_id, asset_key, name, code, source_code, visibility, revision, status, last_client_updated_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'private', 1, 'draft', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP())",
            'issss',
            array($user_id, $asset_key, $name, $script['code'], $script['source_code'])
        );
        $new_id = (int) miq_account_db()->insert_id;
        $statement->close();
        miq_account_query("INSERT INTO {$versions} (script_id, user_id, revision, source_code, created_at) VALUES (?, ?, 1, ?, UTC_TIMESTAMP())", 'iis', array($new_id, $user_id, $script['source_code']))->close();
        $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        miq_api_json(array('saved' => true, 'id' => $new_id, 'asset_key' => $asset_key, 'revision' => 1));
    }

    if ($action === 'archive_script' || $action === 'unarchive_script' || $action === 'delete_script') {
        $script_id = (int) ($body['id'] ?? 0);
        $scripts = miq_account_table('pine_scripts');
        if ($action === 'archive_script' || $action === 'unarchive_script') {
            $status = $action === 'archive_script' ? 'archived' : 'draft';
            $expected_revision = max(0, (int) ($body['expected_revision'] ?? 0));
            $script = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
            if (!$script) miq_api_json(array('error' => 'Pine script not found.'), 404);
            if ($expected_revision > 0 && $expected_revision !== (int) $script['revision']) {
                miq_api_json(array('error' => 'This script changed on another device.', 'conflict' => true, 'script' => miq_api_script_payload($script)), 409);
            }
            $revision = (int) $script['revision'] + 1;
            $statement = $expected_revision > 0
                ? miq_account_query("UPDATE {$scripts} SET status = ?, visibility = 'private', revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ? AND revision = ?", 'siiii', array($status, $revision, $script_id, $user_id, $expected_revision))
                : miq_account_query("UPDATE {$scripts} SET status = ?, visibility = 'private', revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'siii', array($status, $revision, $script_id, $user_id));
            $changed = $statement->affected_rows === 1;
            $statement->close();
            if (!$changed) {
                $current = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
                miq_api_json(array('error' => 'This script changed on another device.', 'conflict' => true, 'script' => miq_api_script_payload($current)), 409);
            }
            miq_api_json(array('archived' => $status === 'archived', 'id' => $script_id, 'status' => $status, 'revision' => $revision));
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
        miq_api_require_asset_write($user_id, true);
        $storage_delta = strlen((string) $version['source_code']) - strlen((string) $script['source_code']);
        $storage_delta += miq_api_version_storage_delta($versions, 'script_id', $script_id, 'source_code', strlen((string) $version['source_code']));
        miq_api_enforce_asset_storage($user_id, $storage_delta);
        $revision = (int) $script['revision'] + 1;
        $db = miq_account_db();
        $db->begin_transaction();
        try {
        $statement = $expected_revision > 0
            ? miq_account_query("UPDATE {$scripts} SET source_code = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ? AND revision = ?", 'siiii', array($version['source_code'], $revision, $script_id, $user_id, $expected_revision))
            : miq_account_query("UPDATE {$scripts} SET source_code = ?, revision = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?", 'siii', array($version['source_code'], $revision, $script_id, $user_id));
        $restored = $statement->affected_rows === 1;
        $statement->close();
        if (!$restored) {
            $current = miq_account_fetch_one(miq_account_query("SELECT * FROM {$scripts} WHERE id = ? AND user_id = ? LIMIT 1", 'ii', array($script_id, $user_id)));
            $db->rollback();
            miq_api_json(array('error' => 'This script changed on another device.', 'conflict' => true, 'script' => miq_api_script_payload($current)), 409);
        }
        miq_account_query("INSERT INTO {$versions} (script_id, user_id, revision, source_code, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())", 'iiis', array($script_id, $user_id, $revision, $version['source_code']))->close();
        miq_api_trim_versions($versions, 'script_id', $script_id);
        $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
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

    if ($action === 'bookmark_idea') {
        $idea_id = (int) ($body['idea_id'] ?? 0);
        $bookmarked = filter_var($body['bookmarked'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $ideas = miq_account_table('community_ideas');
        $idea = miq_account_fetch_one(miq_account_query(
            "SELECT id FROM {$ideas} WHERE id = ? AND status = 'published' AND visibility = 'public' LIMIT 1",
            'i',
            array($idea_id)
        ));
        if (!$idea) miq_api_json(array('error' => 'Published idea not found.'), 404);
        $bookmarks = miq_account_table('community_bookmarks');
        if ($bookmarked) {
            miq_account_query(
                "INSERT INTO {$bookmarks} (user_id, idea_id, created_at) VALUES (?, ?, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE created_at = created_at",
                'ii',
                array($user_id, $idea_id)
            )->close();
        } else {
            miq_account_query("DELETE FROM {$bookmarks} WHERE user_id = ? AND idea_id = ?", 'ii', array($user_id, $idea_id))->close();
        }
        miq_api_json(array('saved' => true, 'idea_id' => $idea_id, 'bookmarked' => $bookmarked));
    }

    if ($action === 'save_idea_reply') {
        $idea_id = (int) ($body['idea_id'] ?? 0);
        $parent_reply_id = (int) ($body['parent_reply_id'] ?? 0);
        $reply_body = miq_api_clean_text($body['body'] ?? '', 2000);
        if (!$idea_id || $reply_body === '') miq_api_json(array('error' => 'Write a reply before posting.'), 422);
        $reply_limit = miq_account_config()['rate_limits']['community_reply_user'];
        if (!miq_account_rate_limit('community_reply_user', (string) $user_id, $reply_limit['limit'], $reply_limit['window'])) {
            miq_api_json(array('error' => 'Too many replies. Try again later.'), 429);
        }
        $ideas = miq_account_table('community_ideas');
        $idea = miq_account_fetch_one(miq_account_query(
            "SELECT id, user_id, title FROM {$ideas} WHERE id = ? AND status = 'published' AND visibility = 'public' LIMIT 1",
            'i',
            array($idea_id)
        ));
        if (!$idea) miq_api_json(array('error' => 'Published idea not found.'), 404);
        $replies = miq_account_table('community_replies');
        $parent = null;
        if ($parent_reply_id > 0) {
            $parent = miq_account_fetch_one(miq_account_query(
                "SELECT id, user_id FROM {$replies} WHERE id = ? AND idea_id = ? AND status = 'published' LIMIT 1",
                'ii',
                array($parent_reply_id, $idea_id)
            ));
            if (!$parent) miq_api_json(array('error' => 'The reply you selected is no longer available.'), 404);
        }
        $statement = miq_account_query(
            "INSERT INTO {$replies} (idea_id, user_id, parent_reply_id, body, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'pending', UTC_TIMESTAMP(), UTC_TIMESTAMP())",
            'iiis',
            array($idea_id, $user_id, $parent_reply_id ?: null, $reply_body)
        );
        $reply_id = (int) miq_account_db()->insert_id;
        $statement->close();
        miq_api_json(array('saved' => true, 'id' => $reply_id, 'status' => 'pending'));
        $link = 'community?idea=' . $idea_id . '#reply-' . $reply_id;
        if ((int) $idea['user_id'] !== $user_id) {
            miq_account_notify(
                (int) $idea['user_id'],
                'community_reply',
                'New reply to your idea',
                $user['display_name'] . ' replied to “' . $idea['title'] . '”.',
                $link,
                'community-reply:' . $reply_id . ':idea-owner'
            );
        }
        if ($parent && (int) $parent['user_id'] !== $user_id && (int) $parent['user_id'] !== (int) $idea['user_id']) {
            miq_account_notify(
                (int) $parent['user_id'],
                'community_reply',
                'New reply to your comment',
                $user['display_name'] . ' replied in “' . $idea['title'] . '”.',
                $link,
                'community-reply:' . $reply_id . ':parent'
            );
        }
        $bookmarks = miq_account_table('community_bookmarks');
        $bookmark_users = miq_account_fetch_all(miq_account_query(
            "SELECT user_id FROM {$bookmarks} WHERE idea_id = ? ORDER BY created_at LIMIT 100",
            'i',
            array($idea_id)
        ));
        foreach ($bookmark_users as $bookmark_user) {
            $bookmark_user_id = (int) $bookmark_user['user_id'];
            if (
                $bookmark_user_id === $user_id
                || $bookmark_user_id === (int) $idea['user_id']
                || ($parent && $bookmark_user_id === (int) $parent['user_id'])
            ) {
                continue;
            }
            miq_account_notify(
                $bookmark_user_id,
                'community_reply',
                'New reply on a bookmarked idea',
                $user['display_name'] . ' replied in “' . $idea['title'] . '”.',
                $link,
                'community-reply:' . $reply_id . ':bookmark:' . $bookmark_user_id
            );
        }
        miq_api_json(array('saved' => true, 'id' => $reply_id));
    }

    if ($action === 'delete_idea_reply') {
        $reply_id = (int) ($body['reply_id'] ?? ($body['id'] ?? 0));
        $replies = miq_account_table('community_replies');
        $reply = miq_account_fetch_one(miq_account_query(
            "SELECT id, user_id FROM {$replies} WHERE id = ? AND status = 'published' LIMIT 1",
            'i',
            array($reply_id)
        ));
        if (!$reply) miq_api_json(array('error' => 'Reply not found.'), 404);
        if ((int) $reply['user_id'] !== $user_id && !miq_account_is_moderator($user)) {
            miq_api_json(array('error' => 'You cannot remove this reply.'), 403);
        }
        miq_account_query(
            "UPDATE {$replies} SET status = 'deleted', updated_at = UTC_TIMESTAMP() WHERE id = ?",
            'i',
            array($reply_id)
        )->close();
        miq_api_json(array('deleted' => true, 'id' => $reply_id));
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
                "SELECT id, user_id, title, status FROM {$ideas} WHERE id = ? LIMIT 1 FOR UPDATE",
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
            $notification_title = $decision === 'publish'
                ? 'Your community idea was published'
                : ($decision === 'reject' ? 'Your community idea needs changes' : 'Your community idea was hidden');
            $notification_message = $decision === 'publish'
                ? 'Your idea “' . $idea['title'] . '” is now visible in Community Ideas.'
                : $note;
            $notification_link = $decision === 'publish' ? 'community?idea=' . $idea_id : 'workspace?tab=ideas';
            miq_account_notify(
                (int) $idea['user_id'],
                'community_moderation',
                $notification_title,
                $notification_message,
                $notification_link,
                'idea-moderation:' . $idea_id . ':' . $status
            );
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        miq_api_json(array('saved' => true, 'status' => $status));
    }

    if ($action === 'moderate_reply') {
        miq_api_require_moderator($user);
        $reply_id = (int) ($body['reply_id'] ?? 0);
        $decision = in_array(($body['decision'] ?? ''), array('publish', 'reject', 'hide'), true) ? $body['decision'] : '';
        $note = miq_api_clean_text($body['note'] ?? '', 500);
        if (!$reply_id || $decision === '') miq_api_json(array('error' => 'Invalid reply moderation action.'), 422);
        if ($decision !== 'publish' && $note === '') miq_api_json(array('error' => 'Add a moderator note before rejecting or hiding a reply.'), 422);
        $status = $decision === 'publish' ? 'published' : ($decision === 'reject' ? 'rejected' : 'hidden');
        $replies = miq_account_table('community_replies');
        $ideas = miq_account_table('community_ideas');
        $users = miq_account_table('users');
        $db = miq_account_db();
        $db->begin_transaction();
        try {
            $reply = miq_account_fetch_one(miq_account_query(
                "SELECT reply.id, reply.idea_id, reply.user_id, reply.parent_reply_id, reply.status, idea.title AS idea_title, idea.user_id AS idea_user_id, author.display_name AS reply_author_name FROM {$replies} reply INNER JOIN {$ideas} idea ON idea.id = reply.idea_id INNER JOIN {$users} author ON author.id = reply.user_id WHERE reply.id = ? LIMIT 1 FOR UPDATE",
                'i',
                array($reply_id)
            ));
            if (!$reply) throw new RuntimeException('Reply not found.');
            miq_account_query("UPDATE {$replies} SET status = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?", 'si', array($status, $reply_id))->close();
            miq_api_record_moderation_action($user_id, (int) $reply['idea_id'], 'reply_' . $decision, 'Reply #' . $reply_id . ($note !== '' ? ': ' . $note : ''));
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        miq_account_notify(
            (int) $reply['user_id'],
            'community_moderation',
            $decision === 'publish' ? 'Your reply was published' : 'Your reply was reviewed',
            $decision === 'publish' ? 'Your reply on “' . $reply['idea_title'] . '” is now public.' : 'Your reply on “' . $reply['idea_title'] . '” was ' . $status . '.',
            'community?idea=' . (int) $reply['idea_id'] . '#reply-' . $reply_id,
            'community-reply-moderation:' . $reply_id . ':' . $status
        );
        if ($decision === 'publish') {
            $link = 'community?idea=' . (int) $reply['idea_id'] . '#reply-' . $reply_id;
            $targets = array((int) $reply['idea_user_id']);
            if ((int) $reply['parent_reply_id'] > 0) {
                $parent = miq_account_fetch_one(miq_account_query(
                    "SELECT user_id FROM {$replies} WHERE id = ? LIMIT 1",
                    'i',
                    array((int) $reply['parent_reply_id'])
                ));
                if ($parent) $targets[] = (int) $parent['user_id'];
            }
            $bookmarks = miq_account_table('community_bookmarks');
            $bookmark_users = miq_account_fetch_all(miq_account_query(
                "SELECT user_id FROM {$bookmarks} WHERE idea_id = ? ORDER BY created_at LIMIT 100",
                'i',
                array((int) $reply['idea_id'])
            ));
            foreach ($bookmark_users as $bookmark_user) $targets[] = (int) $bookmark_user['user_id'];
            foreach (array_unique($targets) as $target_user_id) {
                if ($target_user_id <= 0 || $target_user_id === (int) $reply['user_id']) continue;
                miq_account_notify(
                    $target_user_id,
                    'community_reply',
                    'New reply on a community idea',
                    $reply['reply_author_name'] . ' replied in “' . $reply['idea_title'] . '”.',
                    $link,
                    'community-reply:' . $reply_id . ':published:' . $target_user_id
                );
            }
        }
        miq_api_json(array('saved' => true, 'id' => $reply_id, 'status' => $status));
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
                $reported_idea = miq_account_fetch_one(miq_account_query(
                    "SELECT user_id, title FROM {$ideas} WHERE id = ? LIMIT 1",
                    'i',
                    array($idea_id)
                ));
                miq_account_query("UPDATE {$ideas} SET status = 'hidden', visibility = 'private', updated_at = UTC_TIMESTAMP() WHERE id = ?", 'i', array($idea_id))->close();
                miq_account_query("UPDATE {$reports} SET status = 'reviewed' WHERE idea_id = ? AND status = 'open'", 'i', array($idea_id))->close();
                miq_api_record_moderation_action($user_id, $idea_id, 'hide', 'Report #' . $report_id . ': ' . $note);
                if ($reported_idea) {
                    miq_account_notify(
                        (int) $reported_idea['user_id'],
                        'community_moderation',
                        'Your community idea was hidden',
                        $note,
                        'workspace?tab=ideas',
                        'idea-moderation:' . $idea_id . ':hidden'
                    );
                }
            }
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        miq_api_json(array('saved' => true, 'status' => $decision === 'dismiss' ? 'dismissed' : 'hidden'));
    }

    miq_api_json(array('error' => 'Unknown account action.'), 404);
} catch (MiqAccountNotificationDeviceLimitException $error) {
    miq_api_json(array('error' => $error->getMessage()), 409);
} catch (Throwable $error) {
    error_log('360MiQ account API error: ' . $error->getMessage());
    miq_api_json(array('error' => miq_account_config()['debug'] ? $error->getMessage() : 'The account service is temporarily unavailable.'), 500);
}
