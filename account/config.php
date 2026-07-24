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
        'account_db_include' => miq_account_env('ACCOUNT_DB_INCLUDE', ''),
        'db_host' => miq_account_env('ACCOUNT_DB_HOST', ''),
        'db_name' => miq_account_env('ACCOUNT_DB_NAME', ''),
        'db_user' => miq_account_env('ACCOUNT_DB_USER', ''),
        'db_password' => miq_account_env('ACCOUNT_DB_PASSWORD', ''),
        'db_port' => (int) miq_account_env('ACCOUNT_DB_PORT', 3306),
        'email_from' => miq_account_env('ACCOUNT_EMAIL_FROM', 'no-reply@360miq.com'),
        'email_from_name' => miq_account_env('ACCOUNT_EMAIL_FROM_NAME', '360MiQ'),
        'moderator_emails' => array_filter(array_map('trim', explode(',', miq_account_env('MIQ_MODERATOR_EMAILS', '')))),
        'debug' => filter_var(miq_account_env('MIQ_ACCOUNT_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
        'max_chart_bytes' => (int) miq_account_env('MIQ_MAX_CHART_BYTES', 1000000),
        'max_script_chars' => (int) miq_account_env('MIQ_MAX_SCRIPT_CHARS', 100000),
    );

    if ($config['table_prefix'] === '') {
        $config['table_prefix'] = 'miq_';
    }

    return $config;
}
