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
fwrite(STDOUT, "Deleted {$deleted} stale account rate-limit rows.\n");
