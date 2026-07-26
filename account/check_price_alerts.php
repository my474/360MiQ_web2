<?php
require_once __DIR__ . '/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

try {
    $alerts = miq_account_table('price_alerts');
    $rows = miq_account_fetch_all(miq_account_query(
        "SELECT DISTINCT code FROM {$alerts} WHERE status = 'active' ORDER BY code LIMIT 500"
    ));
    $codes = array_map(function ($row) {
        return $row['code'];
    }, $rows);
    $checked = 0;
    $triggered = 0;
    foreach (array_chunk($codes, 100) as $code_batch) {
        $quotes = miq_stock_quotes($code_batch);
        $checked += count($quotes);
        $triggered += miq_account_evaluate_price_alerts($quotes);
    }
    echo gmdate('c') . ' checked=' . $checked . ' triggered=' . $triggered . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, gmdate('c') . ' price-alert-check failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
