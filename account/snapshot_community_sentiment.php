<?php
/**
 * Persist today's active 30-day community sentiment for every context.
 * Run once daily from a server cron job after applying the sentiment migration.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/community_sentiment.php';

if (!miq_community_schema_ready()) {
    throw new RuntimeException('Community sentiment migration has not been applied.');
}

$votes = miq_account_table('community_votes');
$contexts = miq_account_fetch_all(miq_account_query(
    "SELECT DISTINCT context_type, context_key FROM {$votes} WHERE expires_at > UTC_TIMESTAMP() ORDER BY context_type, context_key"
));

foreach ($contexts as $context) {
    miq_community_snapshot_today($context['context_type'], $context['context_key'], true);
}

$events = miq_account_table('community_vote_events');
$snapshots = miq_account_table('community_sentiment_daily');
miq_account_query("DELETE FROM {$events} WHERE created_at < UTC_TIMESTAMP() - INTERVAL 211 DAY")->close();
$deleted_events = miq_account_db()->affected_rows;
miq_account_query("DELETE FROM {$snapshots} WHERE snapshot_date < UTC_DATE() - INTERVAL 400 DAY")->close();
$deleted_snapshots = miq_account_db()->affected_rows;

fwrite(
    STDOUT,
    'Saved ' . count($contexts) . ' community sentiment snapshot(s); removed '
    . $deleted_events . ' old vote event(s) and ' . $deleted_snapshots . " old snapshot(s).\n"
);
