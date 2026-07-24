<?php
require_once __DIR__ . '/account/bootstrap.php';

function miq_sso_json($payload, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function miq_sso_shared_secret()
{
    return (string) miq_account_env('MIQ_SSO_SHARED_SECRET', '');
}

function miq_sso_begin()
{
    $user = miq_account_current_user();
    $return_to = miq_account_safe_return_to($_GET['return_to'] ?? '/blog/wp-admin/edit.php', '/blog/wp-admin/edit.php');
    if (!$user) {
        $handoff = 'account_sso.php?return_to=' . rawurlencode($return_to);
        header('Location: account?view=login&return_to=' . rawurlencode($handoff));
        exit;
    }

    if (miq_sso_shared_secret() === '') {
        header('Location: blog/wp-login.php?redirect_to=' . rawurlencode($return_to));
        exit;
    }

    $token = miq_account_create_token();
    $tokens = miq_account_table('sso_tokens');
    $expires_at = gmdate('Y-m-d H:i:s', time() + 300);
    miq_account_query(
        "INSERT INTO {$tokens} (user_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, UTC_TIMESTAMP())",
        'iss',
        array((int) $user['id'], miq_account_hash_token($token), $expires_at)
    )->close();
    header('Location: /blog/?miq_sso=1&token=' . rawurlencode($token) . '&return_to=' . rawurlencode($return_to));
    exit;
}

if (isset($_GET['mode']) && $_GET['mode'] === 'consume') {
    $provided_secret = isset($_SERVER['HTTP_X_MIQ_SSO_SECRET']) ? $_SERVER['HTTP_X_MIQ_SSO_SECRET'] : ($_POST['secret'] ?? '');
    if (miq_sso_shared_secret() === '' || !hash_equals(miq_sso_shared_secret(), (string) $provided_secret)) {
        miq_sso_json(array('error' => 'SSO is not configured.'), 403);
    }

    $token = (string) ($_POST['token'] ?? '');
    if (!preg_match('/^[a-f0-9]{32,128}$/i', $token)) {
        miq_sso_json(array('error' => 'Invalid SSO token.'), 422);
    }

    try {
        $db = miq_account_db();
        $tokens = miq_account_table('sso_tokens');
        $users = miq_account_table('users');
        $db->begin_transaction();
        $row = miq_account_fetch_one(miq_account_query(
            "SELECT t.id AS token_id, u.id, u.email, u.display_name, u.avatar_url FROM {$tokens} t INNER JOIN {$users} u ON u.id = t.user_id WHERE t.token_hash = ? AND t.consumed_at IS NULL AND t.expires_at >= UTC_TIMESTAMP() AND u.status = 'active' LIMIT 1 FOR UPDATE",
            's',
            array(miq_account_hash_token($token))
        ));
        if (!$row) {
            $db->rollback();
            miq_sso_json(array('error' => 'SSO token is invalid or expired.'), 401);
        }
        miq_account_query("UPDATE {$tokens} SET consumed_at = UTC_TIMESTAMP() WHERE id = ?", 'i', array((int) $row['token_id']))->close();
        $db->commit();
        unset($row['token_id']);
        miq_sso_json(array('user' => $row));
    } catch (Throwable $error) {
        if (isset($db) && $db instanceof mysqli) $db->rollback();
        miq_sso_json(array('error' => 'SSO handoff failed.'), 500);
    }
}

miq_sso_begin();
