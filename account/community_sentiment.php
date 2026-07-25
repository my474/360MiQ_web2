<?php
require_once __DIR__ . '/db.php';

function miq_community_empty_counts()
{
    return array('bullish' => 0, 'bearish' => 0, 'neutral' => 0);
}

function miq_community_schema_ready()
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        $votes = miq_account_table('community_votes');
        $events = miq_account_table('community_vote_events');
        $snapshots = miq_account_table('community_sentiment_daily');
        foreach (array(
            "SELECT expires_at FROM {$votes} LIMIT 0",
            "SELECT expires_at FROM {$events} LIMIT 0",
            "SELECT snapshot_date FROM {$snapshots} LIMIT 0",
        ) as $sql) {
            $statement = miq_account_query($sql);
            $statement->close();
        }
        $ready = true;
    } catch (Throwable $error) {
        $ready = false;
    }

    return $ready;
}

function miq_community_active_counts($context_type, $context_key)
{
    $votes = miq_account_table('community_votes');
    $expiry_sql = miq_community_schema_ready() ? ' AND expires_at > UTC_TIMESTAMP()' : '';
    $rows = miq_account_fetch_all(miq_account_query(
        "SELECT direction, COUNT(*) AS total FROM {$votes} WHERE context_type = ? AND context_key = ?{$expiry_sql} GROUP BY direction",
        'ss',
        array($context_type, $context_key)
    ));
    $counts = miq_community_empty_counts();
    foreach ($rows as $row) {
        if (isset($counts[$row['direction']])) {
            $counts[$row['direction']] = (int) $row['total'];
        }
    }
    return $counts;
}

function miq_community_snapshot_score($counts)
{
    $total = array_sum($counts);
    return $total > 0 ? round((($counts['bullish'] - $counts['bearish']) / $total) * 100, 2) : 0.0;
}

function miq_community_snapshot_point($date, $counts)
{
    $total = array_sum($counts);
    return array(
        'date' => $date,
        'bullish' => (int) $counts['bullish'],
        'neutral' => (int) $counts['neutral'],
        'bearish' => (int) $counts['bearish'],
        'total' => $total,
        'bullish_pct' => $total > 0 ? round(($counts['bullish'] / $total) * 100, 1) : 0.0,
        'neutral_pct' => $total > 0 ? round(($counts['neutral'] / $total) * 100, 1) : 0.0,
        'bearish_pct' => $total > 0 ? round(($counts['bearish'] / $total) * 100, 1) : 0.0,
        'score' => miq_community_snapshot_score($counts),
    );
}

function miq_community_upsert_snapshot($context_type, $context_key, $date, $counts)
{
    if (!miq_community_schema_ready()) {
        return;
    }
    $snapshots = miq_account_table('community_sentiment_daily');
    $total = array_sum($counts);
    $score = miq_community_snapshot_score($counts);
    miq_account_query(
        "INSERT INTO {$snapshots} (snapshot_date, context_type, context_key, timeframe, bullish_count, neutral_count, bearish_count, total_count, sentiment_score, created_at, updated_at) VALUES (?, ?, ?, '30d', ?, ?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE bullish_count = VALUES(bullish_count), neutral_count = VALUES(neutral_count), bearish_count = VALUES(bearish_count), total_count = VALUES(total_count), sentiment_score = VALUES(sentiment_score), updated_at = UTC_TIMESTAMP()",
        'sssiiiid',
        array($date, $context_type, $context_key, (int) $counts['bullish'], (int) $counts['neutral'], (int) $counts['bearish'], $total, $score)
    )->close();
}

function miq_community_snapshot_today($context_type, $context_key, $force = false)
{
    if (!$force && miq_community_schema_ready()) {
        $snapshots = miq_account_table('community_sentiment_daily');
        $recent = miq_account_fetch_one(miq_account_query(
            "SELECT bullish_count, neutral_count, bearish_count FROM {$snapshots} WHERE snapshot_date = UTC_DATE() AND context_type = ? AND context_key = ? AND timeframe = '30d' AND updated_at >= UTC_TIMESTAMP() - INTERVAL 5 MINUTE LIMIT 1",
            'ss',
            array($context_type, $context_key)
        ));
        if ($recent) {
            return array(
                'bullish' => (int) $recent['bullish_count'],
                'neutral' => (int) $recent['neutral_count'],
                'bearish' => (int) $recent['bearish_count'],
            );
        }
    }

    $counts = miq_community_active_counts($context_type, $context_key);
    miq_community_upsert_snapshot($context_type, $context_key, gmdate('Y-m-d'), $counts);
    return $counts;
}

function miq_community_save_vote($user_id, $context_type, $context_key, $direction)
{
    $votes = miq_account_table('community_votes');
    $db = miq_account_db();
    $db->begin_transaction();
    try {
        if (!miq_community_schema_ready()) {
            miq_account_query(
                "INSERT INTO {$votes} (user_id, context_type, context_key, direction, created_at, updated_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE direction = VALUES(direction), updated_at = UTC_TIMESTAMP()",
                'isss',
                array($user_id, $context_type, $context_key, $direction)
            )->close();
            $db->commit();
            return miq_community_active_counts($context_type, $context_key);
        }

        $existing = miq_account_fetch_one(miq_account_query(
            "SELECT direction FROM {$votes} WHERE user_id = ? AND context_type = ? AND context_key = ? LIMIT 1 FOR UPDATE",
            'iss',
            array($user_id, $context_type, $context_key)
        ));
        $previous_direction = $existing ? $existing['direction'] : null;
        miq_account_query(
            "INSERT INTO {$votes} (user_id, context_type, context_key, direction, expires_at, created_at, updated_at) VALUES (?, ?, ?, ?, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 30 DAY), UTC_TIMESTAMP(), UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE direction = VALUES(direction), expires_at = VALUES(expires_at), updated_at = UTC_TIMESTAMP()",
            'isss',
            array($user_id, $context_type, $context_key, $direction)
        )->close();

        $events = miq_account_table('community_vote_events');
        miq_account_query(
            "INSERT INTO {$events} (user_id, context_type, context_key, direction, previous_direction, timeframe, period_end, expires_at, created_at) VALUES (?, ?, ?, ?, ?, '30d', DATE_ADD(UTC_DATE(), INTERVAL 30 DAY), DATE_ADD(UTC_TIMESTAMP(), INTERVAL 30 DAY), UTC_TIMESTAMP())",
            'issss',
            array($user_id, $context_type, $context_key, $direction, $previous_direction)
        )->close();
        $db->commit();
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }

    return miq_community_snapshot_today($context_type, $context_key, true);
}

function miq_community_trend_payload($points, $minimum_sample)
{
    $latest = !empty($points)
        ? $points[count($points) - 1]
        : miq_community_snapshot_point(gmdate('Y-m-d'), miq_community_empty_counts());
    return array(
        'available' => true,
        'migration_required' => false,
        'timeframe' => '30d',
        'period_end' => gmdate('Y-m-d', time() + (30 * 86400)),
        'minimum_sample' => $minimum_sample,
        'meets_minimum' => $latest['total'] >= $minimum_sample,
        'latest' => $latest,
        'points' => $points,
    );
}

function miq_community_rebuild_trend($context_type, $context_key, $days = 90, $minimum_sample = 10)
{
    $days = max(7, min(180, (int) $days));
    $minimum_sample = max(1, (int) $minimum_sample);
    if (!miq_community_schema_ready()) {
        return array(
            'available' => false,
            'migration_required' => true,
            'minimum_sample' => $minimum_sample,
            'meets_minimum' => false,
            'points' => array(),
        );
    }

    $timezone = new DateTimeZone('UTC');
    $today = new DateTimeImmutable('today', $timezone);
    $start = $today->modify('-' . ($days - 1) . ' days');
    $snapshots_table = miq_account_table('community_sentiment_daily');
    $stored_rows = miq_account_fetch_all(miq_account_query(
        "SELECT snapshot_date, bullish_count, neutral_count, bearish_count FROM {$snapshots_table} WHERE context_type = ? AND context_key = ? AND timeframe = '30d' AND snapshot_date >= ? AND snapshot_date <= ? ORDER BY snapshot_date ASC",
        'ssss',
        array($context_type, $context_key, $start->format('Y-m-d'), $today->format('Y-m-d'))
    ));
    if (count($stored_rows) >= $days) {
        $stored_points = array_map(function ($row) {
            return miq_community_snapshot_point($row['snapshot_date'], array(
                'bullish' => (int) $row['bullish_count'],
                'neutral' => (int) $row['neutral_count'],
                'bearish' => (int) $row['bearish_count'],
            ));
        }, $stored_rows);
        $stored_points[count($stored_points) - 1] = miq_community_snapshot_point(
            $today->format('Y-m-d'),
            miq_community_active_counts($context_type, $context_key)
        );
        return miq_community_trend_payload($stored_points, $minimum_sample);
    }

    $event_start = $start->modify('-30 days')->format('Y-m-d H:i:s');
    $event_end = $today->modify('+1 day')->format('Y-m-d H:i:s');
    $events_table = miq_account_table('community_vote_events');
    $events = miq_account_fetch_all(miq_account_query(
        "SELECT id, user_id, direction, expires_at, created_at FROM {$events_table} WHERE context_type = ? AND context_key = ? AND created_at >= ? AND created_at < ? ORDER BY created_at ASC, id ASC",
        'ssss',
        array($context_type, $context_key, $event_start, $event_end)
    ));

    $event_index = 0;
    $event_total = count($events);
    $latest_by_user = array();
    $points = array();
    $now = time();

    for ($offset = 0; $offset < $days; $offset++) {
        $date = $start->modify('+' . $offset . ' days');
        $is_today = $date->format('Y-m-d') === $today->format('Y-m-d');
        $cutoff = $is_today ? $now : $date->modify('+1 day')->getTimestamp() - 1;

        while ($event_index < $event_total) {
            $created_at = strtotime($events[$event_index]['created_at'] . ' UTC');
            if ($created_at === false || $created_at > $cutoff) {
                break;
            }
            $latest_by_user[(int) $events[$event_index]['user_id']] = $events[$event_index];
            $event_index++;
        }

        $counts = miq_community_empty_counts();
        foreach ($latest_by_user as $event) {
            $expires_at = strtotime($event['expires_at'] . ' UTC');
            if ($expires_at !== false && $expires_at > $cutoff && isset($counts[$event['direction']])) {
                $counts[$event['direction']]++;
            }
        }

        $date_string = $date->format('Y-m-d');
        if ($is_today) {
            $counts = miq_community_active_counts($context_type, $context_key);
        }
        $points[] = miq_community_snapshot_point($date_string, $counts);
    }

    return miq_community_trend_payload($points, $minimum_sample);
}
