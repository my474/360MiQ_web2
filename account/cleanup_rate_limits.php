<?php
/**
 * Remove stale account rate-limit keys. Run from a server cron job.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/lifecycle.php';

$table = miq_account_table('rate_limits');
$statement = miq_account_db()->prepare("DELETE FROM {$table} WHERE last_attempt_at < UTC_TIMESTAMP() - INTERVAL 2 DAY");
if (!$statement || !$statement->execute()) {
    if ($statement) {
        $statement->close();
    }
    throw new RuntimeException('Rate-limit cleanup failed.');
}

$deleted = $statement->affected_rows;
$statement->close();

$sessions = miq_account_table('sessions');
$statement = miq_account_query("DELETE FROM {$sessions} WHERE expires_at < UTC_TIMESTAMP()");
$expired_sessions = $statement->affected_rows;
$statement->close();

$expired_notification_devices = 0;
try {
    if (miq_account_table_exists('notification_devices')) {
        $notification_devices = miq_account_table('notification_devices');
        $statement = miq_account_query(
            "UPDATE {$notification_devices} device LEFT JOIN {$sessions} account_session ON account_session.user_id = device.user_id AND account_session.session_hash = device.session_hash AND account_session.expires_at > UTC_TIMESTAMP() SET device.device_token = '', device.session_hash = NULL, device.enabled = 0, device.updated_at = UTC_TIMESTAMP() WHERE device.enabled = 1 AND account_session.id IS NULL"
        );
        $expired_notification_devices = $statement->affected_rows;
        $statement->close();
    }
} catch (Throwable $notification_cleanup_error) {
    error_log('360MiQ notification device cleanup failed: ' . $notification_cleanup_error->getMessage());
}

$activity = miq_account_table('user_activity_daily');
$statement = miq_account_query("DELETE FROM {$activity} WHERE activity_date < DATE_SUB(UTC_DATE(), INTERVAL 400 DAY)");
$expired_activity = $statement->affected_rows;
$statement->close();

$users = miq_account_table('users');
$statement = miq_account_query(
    "UPDATE {$users} SET status = 'active', suspended_at = NULL, suspended_until = NULL, suspension_reason = NULL, suspended_by_user_id = NULL, updated_at = UTC_TIMESTAMP() WHERE status = 'suspended' AND suspended_until IS NOT NULL AND suspended_until <= UTC_TIMESTAMP()"
);
$released_users = $statement->affected_rows;
$statement->close();

$expired_notifications = 0;
$expired_replies = 0;
$expired_email_tokens = 0;
$expired_reset_tokens = 0;
$expired_sso_tokens = 0;
try {
    $notifications = miq_account_table('notifications');
    if (miq_account_table_exists('notification_deliveries')) {
        $deliveries = miq_account_table('notification_deliveries');
        miq_account_query(
            "DELETE delivery FROM {$deliveries} delivery INNER JOIN {$notifications} notification ON notification.id = delivery.notification_id WHERE notification.read_at IS NOT NULL AND notification.created_at < UTC_TIMESTAMP() - INTERVAL 180 DAY"
        )->close();
    }
    $statement = miq_account_query("DELETE FROM {$notifications} WHERE read_at IS NOT NULL AND created_at < UTC_TIMESTAMP() - INTERVAL 180 DAY");
    $expired_notifications = $statement->affected_rows;
    $statement->close();

    $replies = miq_account_table('community_replies');
    $statement = miq_account_query("DELETE FROM {$replies} WHERE status = 'deleted' AND updated_at < UTC_TIMESTAMP() - INTERVAL 30 DAY");
    $expired_replies = $statement->affected_rows;
    $statement->close();

} catch (Throwable $optional_cleanup_error) {
    error_log('360MiQ optional productivity cleanup failed: ' . $optional_cleanup_error->getMessage());
}

try {
    foreach (array(
        'email_tokens' => 'expired_email_tokens',
        'password_reset_tokens' => 'expired_reset_tokens',
    ) as $logical_name => $counter_name) {
        if (!miq_account_table_exists($logical_name)) continue;
        $token_table = miq_account_table($logical_name);
        $statement = miq_account_query("DELETE FROM {$token_table} WHERE expires_at < UTC_TIMESTAMP()");
        ${$counter_name} = $statement->affected_rows;
        $statement->close();
    }
    if (miq_account_table_exists('sso_tokens')) {
        $sso_tokens = miq_account_table('sso_tokens');
        $statement = miq_account_query("DELETE FROM {$sso_tokens} WHERE expires_at < UTC_TIMESTAMP() OR (consumed_at IS NOT NULL AND consumed_at < UTC_TIMESTAMP() - INTERVAL 7 DAY)");
        $expired_sso_tokens = $statement->affected_rows;
        $statement->close();
    }
} catch (Throwable $token_cleanup_error) {
    error_log('360MiQ token cleanup failed: ' . $token_cleanup_error->getMessage());
}

fwrite(
    STDOUT,
    "Deleted {$deleted} stale rate-limit row(s), {$expired_sessions} expired session(s), "
    . "disabled {$expired_notification_devices} expired notification device(s), "
    . "{$expired_activity} old activity row(s), {$expired_notifications} read notification(s), "
    . "{$expired_replies} deleted reply row(s), {$expired_email_tokens} verification token(s), "
    . "{$expired_reset_tokens} reset token(s), {$expired_sso_tokens} SSO token(s); "
    . "released {$released_users} expired suspension(s).\n"
);
