<?php
require_once __DIR__ . '/db.php';

function miq_account_table_exists($logical_name)
{
    static $cache = array();
    $table = miq_account_table($logical_name);
    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }
    $row = miq_account_fetch_one(miq_account_query(
        'SELECT 1 AS present FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1',
        's',
        array($table)
    ));
    $cache[$table] = (bool) $row;
    return $cache[$table];
}

function miq_account_delete_for_user($logical_name, $column, $user_id)
{
    if (!miq_account_table_exists($logical_name)) {
        return;
    }
    $table = miq_account_table($logical_name);
    miq_account_query("DELETE FROM {$table} WHERE {$column} = ?", 'i', array((int) $user_id))->close();
}

/**
 * Permanently removes an account and all data owned by or directly identifying
 * that user. The explicit order works on Bluehost accounts that cannot create
 * foreign keys and also respects foreign keys on databases that already have them.
 */
function miq_account_delete_user_data($user_id)
{
    $user_id = (int) $user_id;
    $ideas = miq_account_table('community_ideas');
    $charts = miq_account_table('saved_charts');
    $scripts = miq_account_table('pine_scripts');
    $watchlists = miq_account_table('watchlists');

    if (miq_account_table_exists('community_replies') && miq_account_table_exists('community_ideas')) {
        $replies = miq_account_table('community_replies');
        miq_account_query("UPDATE {$replies} SET parent_reply_id = NULL WHERE parent_reply_id IN (SELECT id FROM (SELECT id FROM {$replies} WHERE user_id = ? OR idea_id IN (SELECT id FROM {$ideas} WHERE user_id = ?)) owned_replies)", 'ii', array($user_id, $user_id))->close();
        miq_account_query("DELETE FROM {$replies} WHERE user_id = ? OR idea_id IN (SELECT id FROM {$ideas} WHERE user_id = ?)", 'ii', array($user_id, $user_id))->close();
    }
    foreach (array(
        array('community_bookmarks', 'user_id'),
        array('community_reports', 'reporter_user_id'),
        array('community_idea_revisions', 'user_id'),
        array('community_votes', 'user_id'),
        array('community_vote_events', 'user_id'),
    ) as $target) {
        miq_account_delete_for_user($target[0], $target[1], $user_id);
    }
    if (miq_account_table_exists('moderation_actions')) {
        $actions = miq_account_table('moderation_actions');
        miq_account_query("DELETE FROM {$actions} WHERE moderator_user_id = ? OR idea_id IN (SELECT id FROM {$ideas} WHERE user_id = ?)", 'ii', array($user_id, $user_id))->close();
    }
    if (miq_account_table_exists('community_reports') && miq_account_table_exists('community_ideas')) {
        $reports = miq_account_table('community_reports');
        miq_account_query("DELETE FROM {$reports} WHERE idea_id IN (SELECT id FROM {$ideas} WHERE user_id = ?)", 'i', array($user_id))->close();
    }
    if (miq_account_table_exists('community_bookmarks') && miq_account_table_exists('community_ideas')) {
        $bookmarks = miq_account_table('community_bookmarks');
        miq_account_query("DELETE FROM {$bookmarks} WHERE idea_id IN (SELECT id FROM {$ideas} WHERE user_id = ?)", 'i', array($user_id))->close();
    }
    if (miq_account_table_exists('community_idea_revisions') && miq_account_table_exists('community_ideas')) {
        $revisions = miq_account_table('community_idea_revisions');
        miq_account_query("DELETE FROM {$revisions} WHERE idea_id IN (SELECT id FROM {$ideas} WHERE user_id = ?)", 'i', array($user_id))->close();
    }
    miq_account_delete_for_user('community_ideas', 'user_id', $user_id);

    miq_account_delete_for_user('watchlist_items', 'user_id', $user_id);
    miq_account_delete_for_user('watchlists', 'user_id', $user_id);
    miq_account_delete_for_user('research_notes', 'user_id', $user_id);
    if (miq_account_table_exists('chart_versions') && miq_account_table_exists('saved_charts')) {
        $versions = miq_account_table('chart_versions');
        miq_account_query("DELETE FROM {$versions} WHERE user_id = ? OR chart_id IN (SELECT id FROM {$charts} WHERE user_id = ?)", 'ii', array($user_id, $user_id))->close();
    }
    miq_account_delete_for_user('saved_charts', 'user_id', $user_id);
    if (miq_account_table_exists('pine_script_versions') && miq_account_table_exists('pine_scripts')) {
        $versions = miq_account_table('pine_script_versions');
        miq_account_query("DELETE FROM {$versions} WHERE user_id = ? OR script_id IN (SELECT id FROM {$scripts} WHERE user_id = ?)", 'ii', array($user_id, $user_id))->close();
    }
    miq_account_delete_for_user('pine_scripts', 'user_id', $user_id);

    foreach (array(
        'recent_searches', 'screener_presets', 'user_preferences', 'chat_histories',
        'price_alerts', 'notifications', 'notification_preferences', 'sessions',
        'user_activity_daily', 'sso_tokens', 'email_tokens', 'password_reset_tokens', 'identities'
    ) as $logical_name) {
        miq_account_delete_for_user($logical_name, 'user_id', $user_id);
    }
    if (miq_account_table_exists('notification_deliveries') && miq_account_table_exists('notification_devices')) {
        $deliveries = miq_account_table('notification_deliveries');
        $devices = miq_account_table('notification_devices');
        miq_account_query("DELETE FROM {$deliveries} WHERE device_id IN (SELECT id FROM {$devices} WHERE user_id = ?)", 'i', array($user_id))->close();
    }
    miq_account_delete_for_user('notification_devices', 'user_id', $user_id);
    if (miq_account_table_exists('user_admin_actions')) {
        $admin_actions = miq_account_table('user_admin_actions');
        miq_account_query("DELETE FROM {$admin_actions} WHERE admin_user_id = ? OR target_user_id = ?", 'ii', array($user_id, $user_id))->close();
    }
    if (miq_account_table_exists('users')) {
        $users = miq_account_table('users');
        miq_account_query("UPDATE {$users} SET suspended_by_user_id = NULL WHERE suspended_by_user_id = ?", 'i', array($user_id))->close();
        miq_account_query("DELETE FROM {$users} WHERE id = ?", 'i', array($user_id))->close();
    }
}
