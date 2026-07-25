<?php
/**
 * Main-site account configuration.
 *
 * Keep secrets outside the web root when possible. Every setting can be
 * provided as an environment variable so local, staging, and production
 * deployments do not share credentials.
 */

if (!defined('MIQ_ACCOUNT_CONFIG_LOADED')) {
    define('MIQ_ACCOUNT_CONFIG_LOADED', true);
}

function miq_account_env($name, $default = '')
{
    $value = getenv($name);
    return ($value === false || $value === '') ? $default : $value;
}

function miq_community_enabled()
{
    $enabled = filter_var(
        miq_account_env('MIQ_COMMUNITY_ENABLED', 'true'),
        FILTER_VALIDATE_BOOLEAN,
        FILTER_NULL_ON_FAILURE
    );
    return $enabled === null ? true : $enabled;
}

function miq_account_config()
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $config = array(
        'table_prefix' => preg_replace('/[^a-zA-Z0-9_]/', '', miq_account_env('MIQ_ACCOUNT_TABLE_PREFIX', 'miq_')),
        'cookie_name' => miq_account_env('MIQ_ACCOUNT_COOKIE_NAME', 'miq_account'),
        'session_lifetime' => (int) miq_account_env('MIQ_ACCOUNT_SESSION_LIFETIME', 1209600),
        'base_url' => rtrim(miq_account_env('MIQ_SITE_URL', 'https://360miq.com'), '/'),
        'google_client_id' => miq_account_env('GOOGLE_CLIENT_ID', ''),
        'google_tokeninfo_url' => miq_account_env('GOOGLE_TOKENINFO_URL', 'https://oauth2.googleapis.com/tokeninfo'),
        // The production account connection lives outside the public web root.
        // ACCOUNT_DB_INCLUDE can still override this path for local/staging.
        'account_db_include' => miq_account_env('ACCOUNT_DB_INCLUDE', '/home2/aamiqcom/php_script/mysql_vars_account.php'),
        'db_host' => miq_account_env('ACCOUNT_DB_HOST', ''),
        'db_name' => miq_account_env('ACCOUNT_DB_NAME', ''),
        'db_user' => miq_account_env('ACCOUNT_DB_USER', ''),
        'db_password' => miq_account_env('ACCOUNT_DB_PASSWORD', ''),
        'db_port' => (int) miq_account_env('ACCOUNT_DB_PORT', 3306),
        'email_from' => miq_account_env('ACCOUNT_EMAIL_FROM', 'no-reply@360miq.com'),
        'email_from_name' => miq_account_env('ACCOUNT_EMAIL_FROM_NAME', '360MiQ'),
        'mailer_include' => miq_account_env('ACCOUNT_MAILER_INCLUDE', '/home2/aamiqcom/cronjobs/email.php'),
        'moderator_emails' => array_filter(array_map('trim', explode(',', miq_account_env('MIQ_MODERATOR_EMAILS', '')))),
        'admin_emails' => array_filter(array_map('trim', explode(',', miq_account_env('MIQ_ADMIN_EMAILS', '')))),
        'activity_write_interval' => max(60, (int) miq_account_env('MIQ_ACTIVITY_WRITE_INTERVAL', 900)),
        'debug' => filter_var(miq_account_env('MIQ_ACCOUNT_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
        'rate_limits' => array(
            'login_ip' => array('limit' => (int) miq_account_env('MIQ_RATE_LOGIN_IP_LIMIT', 20), 'window' => (int) miq_account_env('MIQ_RATE_LOGIN_IP_WINDOW', 900)),
            'login_email' => array('limit' => (int) miq_account_env('MIQ_RATE_LOGIN_EMAIL_LIMIT', 8), 'window' => (int) miq_account_env('MIQ_RATE_LOGIN_EMAIL_WINDOW', 900)),
            'register_ip' => array('limit' => (int) miq_account_env('MIQ_RATE_REGISTER_IP_LIMIT', 5), 'window' => (int) miq_account_env('MIQ_RATE_REGISTER_IP_WINDOW', 3600)),
            'register_email' => array('limit' => (int) miq_account_env('MIQ_RATE_REGISTER_EMAIL_LIMIT', 3), 'window' => (int) miq_account_env('MIQ_RATE_REGISTER_EMAIL_WINDOW', 3600)),
            'reset_ip' => array('limit' => (int) miq_account_env('MIQ_RATE_RESET_IP_LIMIT', 10), 'window' => (int) miq_account_env('MIQ_RATE_RESET_IP_WINDOW', 3600)),
            'reset_email' => array('limit' => (int) miq_account_env('MIQ_RATE_RESET_EMAIL_LIMIT', 3), 'window' => (int) miq_account_env('MIQ_RATE_RESET_EMAIL_WINDOW', 3600)),
            'email_ip' => array('limit' => (int) miq_account_env('MIQ_RATE_EMAIL_IP_LIMIT', 12), 'window' => (int) miq_account_env('MIQ_RATE_EMAIL_IP_WINDOW', 3600)),
            'email_recipient' => array('limit' => (int) miq_account_env('MIQ_RATE_EMAIL_RECIPIENT_LIMIT', 3), 'window' => (int) miq_account_env('MIQ_RATE_EMAIL_RECIPIENT_WINDOW', 3600)),
            'email_cooldown' => array('limit' => 1, 'window' => (int) miq_account_env('MIQ_RATE_EMAIL_COOLDOWN', 60)),
            'community_vote_user' => array('limit' => (int) miq_account_env('MIQ_RATE_COMMUNITY_VOTE_LIMIT', 30), 'window' => (int) miq_account_env('MIQ_RATE_COMMUNITY_VOTE_WINDOW', 3600)),
            'community_report_user' => array('limit' => (int) miq_account_env('MIQ_RATE_COMMUNITY_REPORT_LIMIT', 10), 'window' => (int) miq_account_env('MIQ_RATE_COMMUNITY_REPORT_WINDOW', 3600)),
        ),
        'max_chart_bytes' => (int) miq_account_env('MIQ_MAX_CHART_BYTES', 1000000),
        'max_script_chars' => (int) miq_account_env('MIQ_MAX_SCRIPT_CHARS', 100000),
        'max_chart_count' => (int) miq_account_env('MIQ_MAX_CHART_COUNT', 250),
        'max_named_chart_count' => (int) miq_account_env('MIQ_MAX_NAMED_CHART_COUNT', 100),
        'max_script_count' => (int) miq_account_env('MIQ_MAX_SCRIPT_COUNT', 200),
        'max_asset_versions' => (int) miq_account_env('MIQ_MAX_ASSET_VERSIONS', 20),
    );

    if ($config['table_prefix'] === '') {
        $config['table_prefix'] = 'miq_';
    }

    return $config;
}
