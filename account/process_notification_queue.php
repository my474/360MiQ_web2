<?php
/**
 * Deliver queued browser and Android notifications.
 *
 * Run from cron once per minute. Multiple workers are safe: every delivery is
 * claimed under a short database lease before the network request begins.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/bootstrap.php';

$requested_limit = isset($argv[1]) ? (int) $argv[1] : null;

try {
    $stats = miq_account_process_notification_queue($requested_limit);
    fwrite(STDOUT, gmdate('c') . ' ' . json_encode($stats, JSON_UNESCAPED_SLASHES) . PHP_EOL);
    if (!$stats['configured']) {
        fwrite(STDERR, "FCM service-account credentials are not configured; queued deliveries were left pending.\n");
        exit(2);
    }
} catch (Throwable $error) {
    fwrite(STDERR, gmdate('c') . ' notification-worker failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
