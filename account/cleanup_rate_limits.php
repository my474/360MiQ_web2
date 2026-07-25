<?php
/**
 * Remove stale account rate-limit keys. Run from a server cron job.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/db.php';

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

fwrite(
    STDOUT,
    "Deleted {$deleted} stale rate-limit row(s), {$expired_sessions} expired session(s), "
    . "{$expired_activity} old activity row(s); released {$released_users} expired suspension(s).\n"
);
