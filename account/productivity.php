<?php

function miq_account_preference_defaults()
{
    return array(
        'default_market' => 'NYSE',
        'preferred_timeframe' => '6m',
        'theme_mode' => 'system',
        'chart_type' => 'candlestick',
        'chart_period' => 'daily',
        'auto_save_charts' => true,
    );
}

function miq_account_clean_preferences($values, $existing = array())
{
    $defaults = array_merge(miq_account_preference_defaults(), is_array($existing) ? $existing : array());
    $markets = array('NYSE', 'NASDAQ', 'LSE', 'TSX', 'ASX', 'NSE', 'TYO', 'HKEX', 'SHSE', 'SZSE');
    $timeframes = array('1m', '3m', '6m', 'ytd', '1y', '2y', '3y', '5y', '8y', '10y', 'all');
    $themes = array('system', 'light', 'dark');
    $chart_types = array('candlestick', 'heikin_ashi', 'bar', 'line', 'area', 'baseline');
    $chart_periods = array('daily', 'weekly', 'monthly', 'quarterly', 'yearly');
    $values = is_array($values) ? $values : array();

    $market = strtoupper(trim((string) ($values['default_market'] ?? $defaults['default_market'])));
    $timeframe = strtolower(trim((string) ($values['preferred_timeframe'] ?? $defaults['preferred_timeframe'])));
    $theme = strtolower(trim((string) ($values['theme_mode'] ?? $defaults['theme_mode'])));
    $chart_type = strtolower(trim((string) ($values['chart_type'] ?? $defaults['chart_type'])));
    $chart_period = strtolower(trim((string) ($values['chart_period'] ?? $defaults['chart_period'])));
    $auto_save = array_key_exists('auto_save_charts', $values)
        ? filter_var($values['auto_save_charts'], FILTER_VALIDATE_BOOLEAN)
        : !empty($defaults['auto_save_charts']);

    return array(
        'default_market' => in_array($market, $markets, true) ? $market : $defaults['default_market'],
        'preferred_timeframe' => in_array($timeframe, $timeframes, true) ? $timeframe : $defaults['preferred_timeframe'],
        'theme_mode' => in_array($theme, $themes, true) ? $theme : $defaults['theme_mode'],
        'chart_type' => in_array($chart_type, $chart_types, true) ? $chart_type : $defaults['chart_type'],
        'chart_period' => in_array($chart_period, $chart_periods, true) ? $chart_period : $defaults['chart_period'],
        'auto_save_charts' => (bool) $auto_save,
    );
}

function miq_account_user_preferences($user_id)
{
    $user_id = (int) $user_id;
    if ($user_id <= 0) {
        return miq_account_preference_defaults();
    }
    try {
        $table = miq_account_table('user_preferences');
        $row = miq_account_fetch_one(miq_account_query(
            "SELECT default_market, preferred_timeframe, theme_mode, chart_type, chart_period, auto_save_charts FROM {$table} WHERE user_id = ? LIMIT 1",
            'i',
            array($user_id)
        ));
        return miq_account_clean_preferences($row ?: array());
    } catch (Throwable $error) {
        error_log('360MiQ preference read failure: ' . $error->getMessage());
        return miq_account_preference_defaults();
    }
}

function miq_account_save_preferences($user_id, $values)
{
    $user_id = (int) $user_id;
    $current = miq_account_user_preferences($user_id);
    $preferences = miq_account_clean_preferences($values, $current);
    $table = miq_account_table('user_preferences');
    miq_account_query(
        "INSERT INTO {$table} (user_id, default_market, preferred_timeframe, theme_mode, chart_type, chart_period, auto_save_charts, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE default_market = VALUES(default_market), preferred_timeframe = VALUES(preferred_timeframe), theme_mode = VALUES(theme_mode), chart_type = VALUES(chart_type), chart_period = VALUES(chart_period), auto_save_charts = VALUES(auto_save_charts), updated_at = UTC_TIMESTAMP()",
        'isssssi',
        array(
            $user_id,
            $preferences['default_market'],
            $preferences['preferred_timeframe'],
            $preferences['theme_mode'],
            $preferences['chart_type'],
            $preferences['chart_period'],
            $preferences['auto_save_charts'] ? 1 : 0,
        )
    )->close();
    return $preferences;
}

function miq_account_unread_notification_count($user_id)
{
    try {
        $notifications = miq_account_table('notifications');
        $community_filter = function_exists('miq_community_enabled') && !miq_community_enabled()
            ? " AND notification_type NOT LIKE 'community_%'"
            : '';
        $row = miq_account_fetch_one(miq_account_query(
            "SELECT COUNT(*) AS total FROM {$notifications} WHERE user_id = ? AND read_at IS NULL{$community_filter}",
            'i',
            array((int) $user_id)
        ));
        return (int) ($row['total'] ?? 0);
    } catch (Throwable $error) {
        return 0;
    }
}

if (!function_exists('miq_account_notification_text_limit')) {
    function miq_account_notification_text_limit($value, $maximum)
    {
        $value = trim((string) $value);
        $maximum = max(0, (int) $maximum);
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $maximum, 'UTF-8');
        }
        $characters = array();
        if (preg_match_all('/./us', $value, $characters) !== false) {
            return implode('', array_slice($characters[0], 0, $maximum));
        }
        $clean = function_exists('iconv') ? @iconv('UTF-8', 'UTF-8//IGNORE', $value) : false;
        $clean = is_string($clean) ? $clean : preg_replace('/[^\x00-\x7F]/', '', $value);
        return substr((string) $clean, 0, $maximum);
    }
}

function miq_account_notify($user_id, $type, $title, $message, $link_url = '', $dedupe_key = '')
{
    $user_id = (int) $user_id;
    if ($user_id <= 0) {
        return;
    }
    $type = substr(trim((string) $type), 0, 40);
    $title = miq_account_notification_text_limit($title, 160);
    $message = miq_account_notification_text_limit($message, 500);
    $link_url = miq_account_notification_text_limit($link_url, 500);
    $dedupe_key = substr(trim((string) $dedupe_key), 0, 190);
    $notifications = miq_account_table('notifications');
    $notification_id = 0;
    if ($dedupe_key !== '') {
        miq_account_query(
            "INSERT INTO {$notifications} (user_id, notification_type, title, message, link_url, dedupe_key, read_at, created_at) VALUES (?, ?, ?, ?, ?, ?, NULL, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), notification_type = VALUES(notification_type), title = VALUES(title), message = VALUES(message), link_url = VALUES(link_url), read_at = NULL, created_at = UTC_TIMESTAMP()",
            'isssss',
            array($user_id, $type, $title, $message, $link_url, $dedupe_key)
        )->close();
        $notification_id = (int) miq_account_db()->insert_id;
    } else {
        $statement = miq_account_query(
            "INSERT INTO {$notifications} (user_id, notification_type, title, message, link_url, dedupe_key, read_at, created_at) VALUES (?, ?, ?, ?, ?, NULL, NULL, UTC_TIMESTAMP())",
            'issss',
            array($user_id, $type, $title, $message, $link_url)
        );
        $notification_id = (int) miq_account_db()->insert_id;
        $statement->close();
    }
    if ($notification_id > 0 && function_exists('miq_account_enqueue_notification')) {
        try {
            // This only writes durable delivery rows. Network delivery is
            // performed by process_notification_queue.php after commit.
            miq_account_enqueue_notification($notification_id, $user_id, $type);
        } catch (Throwable $error) {
            error_log('360MiQ notification enqueue failure: ' . $error->getMessage());
        }
    }
}

function miq_stock_db()
{
    static $stock_db = null;
    if ($stock_db instanceof mysqli) {
        return $stock_db;
    }
    $candidates = array_filter(array_unique(array(
        getenv('STOCK_DB_INCLUDE') ?: '',
        '/home2/aamiqcom/php_script/mysql_vars_stock.php',
        dirname(__DIR__, 3) . '/php_script/mysql_vars_stock.php',
        dirname(__DIR__, 2) . '/php_script/mysql_vars_stock.php',
    )));
    foreach ($candidates as $include_file) {
        if (!is_file($include_file)) {
            continue;
        }
        if (!defined('A')) {
            define('A', true);
        }
        $connection = null;
        include $include_file;
        if ($connection instanceof mysqli) {
            $connection->set_charset('utf8mb4');
            $stock_db = $connection;
            return $stock_db;
        }
    }
    throw new RuntimeException('Stock market database is not configured.');
}

function miq_stock_clean_codes($codes, $limit = 500)
{
    $clean = array();
    $limit = max(1, min(500, (int) $limit));
    foreach ((array) $codes as $code) {
        $code = strtoupper(trim((string) $code));
        if ($code !== '' && strlen($code) <= 40 && preg_match('/^[A-Z0-9&.-]+$/', $code) && substr_count($code, '.') <= 1) {
            $clean[$code] = true;
        }
        if (count($clean) >= $limit) {
            break;
        }
    }
    return array_keys($clean);
}

function miq_stock_quotes($codes)
{
    $codes = miq_stock_clean_codes($codes, 500);
    if (!$codes) {
        return array();
    }
    $db = miq_stock_db();
    $placeholders = implode(',', array_fill(0, count($codes), '?'));
    $sql = "SELECT p.code, p.close, p.change_pct, p.tradedate, n.name_en, n.name_tc, n.exchange FROM price_current p LEFT JOIN stock_code_name_exchange n ON n.code = p.code WHERE p.code IN ({$placeholders})";
    $statement = $db->prepare($sql);
    if (!$statement) {
        throw new RuntimeException('Unable to prepare watchlist market-data query.');
    }
    $params = $codes;
    $bind = array(str_repeat('s', count($params)));
    foreach ($params as $index => $value) {
        $bind[] = &$params[$index];
    }
    call_user_func_array(array($statement, 'bind_param'), $bind);
    if (!$statement->execute()) {
        $statement->close();
        throw new RuntimeException('Unable to load watchlist market data.');
    }
    $result = $statement->get_result();
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $row['close'] = $row['close'] === null ? null : (float) $row['close'];
        $row['change_pct'] = $row['change_pct'] === null ? null : (float) $row['change_pct'];
        $rows[] = $row;
    }
    $statement->close();
    return $rows;
}

function miq_account_format_alert_price($value)
{
    if (is_string($value)) {
        $value = str_replace(',', '', $value);
    }
    $formatted = number_format((float) $value, 4, '.', '');
    return rtrim(rtrim($formatted, '0'), '.');
}

function miq_account_format_alert_message($message)
{
    $pattern = '/(\breached\s+)([-+]?(?:\d[\d,]*)(?:\.\d+)?)(\s+\((?:above|below)\s+)([-+]?(?:\d[\d,]*)(?:\.\d+)?)(?=\))/i';
    $formatted = preg_replace_callback($pattern, function ($matches) {
        return $matches[1]
            . miq_account_format_alert_price($matches[2])
            . $matches[3]
            . miq_account_format_alert_price($matches[4]);
    }, (string) $message);
    return $formatted === null ? (string) $message : $formatted;
}

function miq_account_evaluate_price_alerts($quotes, $user_id = 0)
{
    if (!$quotes) {
        return 0;
    }
    $quote_map = array();
    foreach ($quotes as $quote) {
        if (isset($quote['code']) && isset($quote['close']) && is_numeric($quote['close'])) {
            $quote_map[strtoupper($quote['code'])] = (float) $quote['close'];
        }
    }
    if (!$quote_map) {
        return 0;
    }
    $alerts = miq_account_table('price_alerts');
    $codes = array_keys($quote_map);
    $placeholders = implode(',', array_fill(0, count($codes), '?'));
    $types = str_repeat('s', count($codes));
    $params = $codes;
    $where = "status = 'active' AND code IN ({$placeholders})";
    if ((int) $user_id > 0) {
        $where .= ' AND user_id = ?';
        $types .= 'i';
        $params[] = (int) $user_id;
    }
    $rows = miq_account_fetch_all(miq_account_query(
        "SELECT id, user_id, code, condition_type, target_price FROM {$alerts} WHERE {$where}",
        $types,
        $params
    ));
    $triggered = 0;
    foreach ($rows as $alert) {
        $price = $quote_map[strtoupper($alert['code'])];
        $target = (float) $alert['target_price'];
        $matches = $alert['condition_type'] === 'above' ? $price >= $target : $price <= $target;
        if ($matches) {
            $statement = miq_account_query(
                "UPDATE {$alerts} SET status = 'triggered', last_price = ?, triggered_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP() WHERE id = ? AND status = 'active'",
                'di',
                array($price, (int) $alert['id'])
            );
            $did_trigger = $statement->affected_rows === 1;
            $statement->close();
            if (!$did_trigger) {
                continue;
            }
            $formatted_price = miq_account_format_alert_price($price);
            $formatted_target = miq_account_format_alert_price($target);
            miq_account_notify(
                (int) $alert['user_id'],
                'price_alert',
                $alert['code'] . ' price alert triggered',
                $alert['code'] . ' reached ' . $formatted_price . ' (' . $alert['condition_type'] . ' ' . $formatted_target . ').',
                'stockinfo?code=' . rawurlencode($alert['code']),
                'price-alert:' . (int) $alert['id']
            );
            $triggered += 1;
        } else {
            miq_account_query(
                "UPDATE {$alerts} SET last_price = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND status = 'active'",
                'di',
                array($price, (int) $alert['id'])
            )->close();
        }
    }
    return $triggered;
}
