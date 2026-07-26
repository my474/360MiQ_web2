<?php
require_once __DIR__ . '/account/bootstrap.php';
$user = miq_account_current_user();
if (!$user) {
    header('Location: account?view=login&return_to=account_settings');
    exit;
}

$user_id = (int) $user['id'];
$charts = miq_account_table('saved_charts');
$scripts = miq_account_table('pine_scripts');
$searches = miq_account_table('recent_searches');
$screener_presets = miq_account_table('screener_presets');
$watchlists = miq_account_table('watchlists');
$watchlist_items = miq_account_table('watchlist_items');
$preferences = miq_account_table('user_preferences');
$notes = miq_account_table('research_notes');
$alerts = miq_account_table('price_alerts');
$notifications = miq_account_table('notifications');
$bookmarks = miq_account_table('community_bookmarks');
$replies = miq_account_table('community_replies');
$ideas = miq_account_table('community_ideas');
$chart_versions = miq_account_table('chart_versions');
$script_versions = miq_account_table('pine_script_versions');
$idea_revisions = miq_account_table('community_idea_revisions');
$votes = miq_account_table('community_votes');
$vote_events = miq_account_table('community_vote_events');
$identities = miq_account_table('identities');
$sessions = miq_account_table('sessions');
$activity = miq_account_table('user_activity_daily');
$reports = miq_account_table('community_reports');
$admin_actions = miq_account_table('user_admin_actions');
$payload = array(
    'exported_at' => gmdate('c'),
    'profile' => array('email' => $user['email'], 'display_name' => $user['display_name'], 'created_role' => $user['role']),
    'recent_searches' => miq_account_fetch_all(miq_account_query("SELECT code, exchange, display_name, searched_at FROM {$searches} WHERE user_id = ? ORDER BY searched_at DESC", 'i', array($user_id))),
    'screener_presets' => miq_account_fetch_all(miq_account_query("SELECT client_key, name, config_json, is_default, revision, client_updated_at, created_at, updated_at FROM {$screener_presets} WHERE user_id = ? ORDER BY updated_at DESC", 'i', array($user_id))),
    'saved_charts' => miq_account_fetch_all(miq_account_query("SELECT id, asset_key, name, code, kind, layout_json, visibility, revision, last_client_updated_at, created_at, updated_at FROM {$charts} WHERE user_id = ? ORDER BY updated_at DESC", 'i', array($user_id))),
    'chart_versions' => miq_account_fetch_all(miq_account_query("SELECT chart_id, revision, layout_json, created_at FROM {$chart_versions} WHERE user_id = ? ORDER BY created_at DESC", 'i', array($user_id))),
    'pine_scripts' => miq_account_fetch_all(miq_account_query("SELECT id, asset_key, name, code, source_code, visibility, revision, status, last_client_updated_at, created_at, updated_at FROM {$scripts} WHERE user_id = ? ORDER BY updated_at DESC", 'i', array($user_id))),
    'pine_script_versions' => miq_account_fetch_all(miq_account_query("SELECT script_id, revision, source_code, created_at FROM {$script_versions} WHERE user_id = ? ORDER BY created_at DESC", 'i', array($user_id))),
    'preferences' => miq_account_user_preferences($user_id),
    'watchlists' => miq_account_fetch_all(miq_account_query("SELECT id, name, created_at, updated_at FROM {$watchlists} WHERE user_id = ? ORDER BY updated_at DESC", 'i', array($user_id))),
    'watchlist_items' => miq_account_fetch_all(miq_account_query("SELECT watchlist_id, code, sort_order, created_at FROM {$watchlist_items} WHERE user_id = ? ORDER BY watchlist_id, sort_order, id", 'i', array($user_id))),
    'research_notes' => miq_account_fetch_all(miq_account_query("SELECT stock_code, chart_id, script_id, title, body, created_at, updated_at FROM {$notes} WHERE user_id = ? ORDER BY updated_at DESC", 'i', array($user_id))),
    'price_alerts' => miq_account_fetch_all(miq_account_query("SELECT code, condition_type, target_price, status, last_price, triggered_at, created_at, updated_at FROM {$alerts} WHERE user_id = ? ORDER BY updated_at DESC", 'i', array($user_id))),
    'notifications' => miq_account_fetch_all(miq_account_query("SELECT notification_type, title, message, link_url, read_at, created_at FROM {$notifications} WHERE user_id = ? ORDER BY created_at DESC", 'i', array($user_id))),
    'community_bookmarks' => miq_account_fetch_all(miq_account_query("SELECT idea_id, created_at FROM {$bookmarks} WHERE user_id = ? ORDER BY created_at DESC", 'i', array($user_id))),
    'community_replies' => miq_account_fetch_all(miq_account_query("SELECT idea_id, parent_reply_id, body, status, created_at, updated_at FROM {$replies} WHERE user_id = ? ORDER BY created_at DESC", 'i', array($user_id))),
    'community_ideas' => miq_account_fetch_all(miq_account_query("SELECT id, code, title, direction, timeframe, thesis, catalyst, risk, disclosure, status, visibility, created_at, updated_at, published_at FROM {$ideas} WHERE user_id = ? ORDER BY updated_at DESC", 'i', array($user_id))),
    'community_idea_revisions' => miq_account_fetch_all(miq_account_query("SELECT idea_id, payload_json, created_at FROM {$idea_revisions} WHERE user_id = ? ORDER BY created_at DESC", 'i', array($user_id))),
    'community_votes' => miq_account_fetch_all(miq_account_query("SELECT context_type, context_key, direction, expires_at, created_at, updated_at FROM {$votes} WHERE user_id = ? ORDER BY updated_at DESC", 'i', array($user_id))),
    'community_vote_events' => miq_account_fetch_all(miq_account_query("SELECT context_type, context_key, direction, previous_direction, timeframe, period_end, expires_at, created_at FROM {$vote_events} WHERE user_id = ? ORDER BY created_at DESC", 'i', array($user_id))),
    'reports_submitted' => miq_account_fetch_all(miq_account_query("SELECT idea_id, reason, details, status, created_at FROM {$reports} WHERE reporter_user_id = ? ORDER BY created_at DESC", 'i', array($user_id))),
    'linked_identities' => miq_account_fetch_all(miq_account_query("SELECT provider, provider_email, created_at FROM {$identities} WHERE user_id = ? ORDER BY created_at DESC", 'i', array($user_id))),
    'sessions' => miq_account_fetch_all(miq_account_query("SELECT created_at, last_seen_at, expires_at FROM {$sessions} WHERE user_id = ? ORDER BY created_at DESC", 'i', array($user_id))),
    'activity' => miq_account_fetch_all(miq_account_query("SELECT activity_date, first_seen_at, last_seen_at, request_count FROM {$activity} WHERE user_id = ? ORDER BY activity_date DESC", 'i', array($user_id))),
    'administrative_actions' => miq_account_fetch_all(miq_account_query("SELECT action, reason, suspended_until, created_at FROM {$admin_actions} WHERE target_user_id = ? ORDER BY created_at DESC", 'i', array($user_id))),
);
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: private, no-store, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: attachment; filename="360miq-account-export-' . gmdate('Ymd-His') . '.json"');
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
