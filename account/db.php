<?php
require_once __DIR__ . '/config.php';

function miq_account_db()
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $config = miq_account_config();
    $include_candidates = array();
    if ($config['account_db_include'] !== '') {
        $include_candidates[] = $config['account_db_include'];
    }

    // This is the existing production connection convention used by the
    // public data endpoints. A dedicated account DB include is preferred.
    $include_candidates[] = '/home2/aamiqcom/php_script/mysql_vars_stock.php';

    foreach ($include_candidates as $include_file) {
        if (!is_file($include_file)) {
            continue;
        }

        $connection = null;
        include $include_file;
        if ($connection instanceof mysqli) {
            $connection->set_charset('utf8mb4');
            return $connection;
        }
    }

    if ($config['db_host'] === '' || $config['db_name'] === '' || $config['db_user'] === '') {
        throw new RuntimeException('Account database is not configured. Set ACCOUNT_DB_INCLUDE or ACCOUNT_DB_HOST/NAME/USER/PASSWORD.');
    }

    $connection = mysqli_init();
    if (!$connection || !$connection->real_connect(
        $config['db_host'],
        $config['db_user'],
        $config['db_password'],
        $config['db_name'],
        $config['db_port']
    )) {
        throw new RuntimeException('Unable to connect to the account database.');
    }

    $connection->set_charset('utf8mb4');
    return $connection;
}

function miq_account_table($name)
{
    $allowed = array(
        'users', 'identities', 'email_tokens', 'password_reset_tokens', 'sessions',
        'recent_searches', 'saved_charts', 'chart_versions', 'pine_scripts',
        'pine_script_versions', 'watchlists', 'watchlist_items', 'community_ideas',
        'community_idea_revisions', 'community_votes', 'community_reports',
        'moderation_actions', 'sso_tokens'
    );

    if (!in_array($name, $allowed, true)) {
        throw new InvalidArgumentException('Unknown account table.');
    }

    return miq_account_config()['table_prefix'] . $name;
}

function miq_account_query($sql, $types = '', $params = array())
{
    $db = miq_account_db();
    $statement = $db->prepare($sql);
    if (!$statement) {
        throw new RuntimeException('Database statement preparation failed.');
    }

    if ($types !== '' && !empty($params)) {
        $bind = array($types);
        foreach ($params as $index => $value) {
            $bind[] = &$params[$index];
        }
        call_user_func_array(array($statement, 'bind_param'), $bind);
    }

    if (!$statement->execute()) {
        $statement->close();
        throw new RuntimeException('Database statement execution failed.');
    }

    return $statement;
}

function miq_account_fetch_all($statement)
{
    $rows = array();
    $result = $statement->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
    }
    $statement->close();
    return $rows;
}

function miq_account_fetch_one($statement)
{
    $result = $statement->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    if ($result) {
        $result->free();
    }
    $statement->close();
    return $row ?: null;
}
