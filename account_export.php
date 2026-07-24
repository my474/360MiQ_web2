<?php
require_once __DIR__ . '/account/bootstrap.php';
$user = miq_account_current_user();
if (!$user) {
    header('Location: /account?view=login&return_to=/account_settings');
    exit;
}

$user_id = (int) $user['id'];
$charts = miq_account_table('saved_charts');
$scripts = miq_account_table('pine_scripts');
$searches = miq_account_table('recent_searches');
$watchlists = miq_account_table('watchlists');
$ideas = miq_account_table('community_ideas');
$payload = array(
    'exported_at' => gmdate('c'),
    'profile' => array('email' => $user['email'], 'display_name' => $user['display_name'], 'created_role' => $user['role']),
    'recent_searches' => miq_account_fetch_all(miq_account_query("SELECT code, exchange, display_name, searched_at FROM {$searches} WHERE user_id = ? ORDER BY searched_at DESC", 'i', array($user_id))),
    'saved_charts' => miq_account_fetch_all(miq_account_query("SELECT name, code, layout_json, visibility, revision, created_at, updated_at FROM {$charts} WHERE user_id = ? ORDER BY updated_at DESC", 'i', array($user_id))),
    'pine_scripts' => miq_account_fetch_all(miq_account_query("SELECT name, code, source_code, visibility, revision, status, created_at, updated_at FROM {$scripts} WHERE user_id = ? ORDER BY updated_at DESC", 'i', array($user_id))),
    'watchlists' => miq_account_fetch_all(miq_account_query("SELECT id, name, created_at, updated_at FROM {$watchlists} WHERE user_id = ? ORDER BY updated_at DESC", 'i', array($user_id))),
    'community_ideas' => miq_account_fetch_all(miq_account_query("SELECT code, title, direction, timeframe, thesis, catalyst, risk, disclosure, status, visibility, created_at, updated_at, published_at FROM {$ideas} WHERE user_id = ? ORDER BY updated_at DESC", 'i', array($user_id))),
);
header('Content-Type: application/json; charset=UTF-8');
header('Content-Disposition: attachment; filename="360miq-account-export-' . gmdate('Ymd-His') . '.json"');
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
