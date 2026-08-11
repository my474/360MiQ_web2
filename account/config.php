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
        // OAuth client IDs are public identifiers. This Web client is also the
        // Credential Manager serverClientId, so web and Android tokens share
        // the same server-side audience. GOOGLE_CLIENT_ID can still override
        // it for staging or a future production client rotation.
        'google_client_id' => miq_account_env('GOOGLE_CLIENT_ID', '735181786268-s7n2c9fdg268labp2estg8au267c3m0r.apps.googleusercontent.com'),
        'google_tokeninfo_url' => miq_account_env('GOOGLE_TOKENINFO_URL', 'https://oauth2.googleapis.com/tokeninfo'),
        'native_google_challenge_ttl' => max(60, min(600, (int) miq_account_env('MIQ_NATIVE_GOOGLE_CHALLENGE_TTL', 300))),
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
        'fcm_project_id' => miq_account_env('FCM_PROJECT_ID', ''),
        'fcm_client_email' => miq_account_env('FCM_CLIENT_EMAIL', ''),
        'fcm_private_key' => str_replace('\\n', "\n", miq_account_env('FCM_PRIVATE_KEY', '')),
        'fcm_service_account_json' => miq_account_env('FCM_SERVICE_ACCOUNT_JSON', ''),
        // Shared-hosting PHP and cron can read the same credential without
        // placing its private key in .htaccess or a command line.
        'fcm_service_account_file' => miq_account_env('FCM_SERVICE_ACCOUNT_FILE', '/home2/aamiqcom/php_script/firebase-service-account.json'),
        'fcm_web_api_key' => miq_account_env('FCM_WEB_API_KEY', ''),
        'fcm_web_auth_domain' => miq_account_env('FCM_WEB_AUTH_DOMAIN', ''),
        'fcm_web_storage_bucket' => miq_account_env('FCM_WEB_STORAGE_BUCKET', ''),
        'fcm_web_messaging_sender_id' => miq_account_env('FCM_WEB_MESSAGING_SENDER_ID', ''),
        'fcm_web_app_id' => miq_account_env('FCM_WEB_APP_ID', ''),
        'fcm_web_vapid_key' => miq_account_env('FCM_WEB_VAPID_KEY', ''),
        'fcm_web_sdk_version' => miq_account_env('FCM_WEB_SDK_VERSION', '12.16.0'),
        'fcm_max_devices_per_notification' => max(1, min(50, (int) miq_account_env('FCM_MAX_DEVICES_PER_NOTIFICATION', 10))),
        'fcm_max_devices_per_user' => max(1, min(100, (int) miq_account_env('FCM_MAX_DEVICES_PER_USER', 20))),
        'fcm_worker_batch_size' => max(1, min(200, (int) miq_account_env('FCM_WORKER_BATCH_SIZE', 50))),
        'fcm_delivery_max_attempts' => max(1, min(20, (int) miq_account_env('FCM_DELIVERY_MAX_ATTEMPTS', 8))),
        'fcm_retry_base_seconds' => max(5, min(3600, (int) miq_account_env('FCM_RETRY_BASE_SECONDS', 60))),
        'fcm_retry_max_seconds' => max(60, min(86400, (int) miq_account_env('FCM_RETRY_MAX_SECONDS', 21600))),
        // A cold OAuth exchange plus one authenticated retry can consume four
        // 15-second HTTP timeouts. Keep the lease above that worst-case path
        // so a second worker cannot reclaim and duplicate an in-flight push.
        'fcm_delivery_lease_seconds' => max(90, min(600, (int) miq_account_env('FCM_DELIVERY_LEASE_SECONDS', 120))),
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
            'verification_resend_ip' => array('limit' => (int) miq_account_env('MIQ_RATE_VERIFY_RESEND_IP_LIMIT', 10), 'window' => (int) miq_account_env('MIQ_RATE_VERIFY_RESEND_IP_WINDOW', 3600)),
            'verification_resend_email' => array('limit' => (int) miq_account_env('MIQ_RATE_VERIFY_RESEND_EMAIL_LIMIT', 3), 'window' => (int) miq_account_env('MIQ_RATE_VERIFY_RESEND_EMAIL_WINDOW', 3600)),
            'email_ip' => array('limit' => (int) miq_account_env('MIQ_RATE_EMAIL_IP_LIMIT', 12), 'window' => (int) miq_account_env('MIQ_RATE_EMAIL_IP_WINDOW', 3600)),
            'email_recipient' => array('limit' => (int) miq_account_env('MIQ_RATE_EMAIL_RECIPIENT_LIMIT', 3), 'window' => (int) miq_account_env('MIQ_RATE_EMAIL_RECIPIENT_WINDOW', 3600)),
            'email_cooldown' => array('limit' => 1, 'window' => (int) miq_account_env('MIQ_RATE_EMAIL_COOLDOWN', 60)),
            'community_vote_user' => array('limit' => (int) miq_account_env('MIQ_RATE_COMMUNITY_VOTE_LIMIT', 30), 'window' => (int) miq_account_env('MIQ_RATE_COMMUNITY_VOTE_WINDOW', 3600)),
            'community_report_user' => array('limit' => (int) miq_account_env('MIQ_RATE_COMMUNITY_REPORT_LIMIT', 10), 'window' => (int) miq_account_env('MIQ_RATE_COMMUNITY_REPORT_WINDOW', 3600)),
            'community_reply_user' => array('limit' => (int) miq_account_env('MIQ_RATE_COMMUNITY_REPLY_LIMIT', 20), 'window' => (int) miq_account_env('MIQ_RATE_COMMUNITY_REPLY_WINDOW', 3600)),
            'sso_user' => array('limit' => (int) miq_account_env('MIQ_RATE_SSO_USER_LIMIT', 60), 'window' => (int) miq_account_env('MIQ_RATE_SSO_USER_WINDOW', 3600)),
            'asset_write_user' => array('limit' => (int) miq_account_env('MIQ_RATE_ASSET_WRITE_LIMIT', 600), 'window' => (int) miq_account_env('MIQ_RATE_ASSET_WRITE_WINDOW', 3600)),
            'asset_version_user' => array('limit' => (int) miq_account_env('MIQ_RATE_ASSET_VERSION_LIMIT', 60), 'window' => (int) miq_account_env('MIQ_RATE_ASSET_VERSION_WINDOW', 3600)),
            'notification_device_user' => array('limit' => (int) miq_account_env('MIQ_RATE_NOTIFICATION_DEVICE_LIMIT', 300), 'window' => (int) miq_account_env('MIQ_RATE_NOTIFICATION_DEVICE_WINDOW', 3600)),
        ),
        'max_api_request_bytes' => (int) miq_account_env('MIQ_MAX_API_REQUEST_BYTES', 2000000),
        // Chat history is intentionally small because it contains rendered
        // message HTML and stock-analysis metadata, not a durable transcript.
        'max_chat_history_bytes' => max(32768, min(1048576, (int) miq_account_env('MIQ_MAX_CHAT_HISTORY_BYTES', 262144))),
        'max_chart_bytes' => (int) miq_account_env('MIQ_MAX_CHART_BYTES', 1000000),
        'max_script_chars' => (int) miq_account_env('MIQ_MAX_SCRIPT_CHARS', 100000),
        'max_chart_count' => (int) miq_account_env('MIQ_MAX_CHART_COUNT', 250),
        'max_named_chart_count' => (int) miq_account_env('MIQ_MAX_NAMED_CHART_COUNT', 100),
        'max_script_count' => (int) miq_account_env('MIQ_MAX_SCRIPT_COUNT', 200),
        'max_asset_storage_bytes' => (int) miq_account_env('MIQ_MAX_ASSET_STORAGE_BYTES', 50000000),
        'max_screener_preset_count' => (int) miq_account_env('MIQ_MAX_SCREENER_PRESET_COUNT', 50),
        'max_watchlist_count' => (int) miq_account_env('MIQ_MAX_WATCHLIST_COUNT', 20),
        'max_watchlist_items' => (int) miq_account_env('MIQ_MAX_WATCHLIST_ITEMS', 100),
        'max_note_count' => (int) miq_account_env('MIQ_MAX_NOTE_COUNT', 1000),
        'max_alert_count' => (int) miq_account_env('MIQ_MAX_ALERT_COUNT', 100),
        'max_asset_versions' => (int) miq_account_env('MIQ_MAX_ASSET_VERSIONS', 20),
    );

    if ($config['table_prefix'] === '') {
        $config['table_prefix'] = 'miq_';
    }

    return $config;
}
