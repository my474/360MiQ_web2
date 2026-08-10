<?php
require_once __DIR__ . '/account/bootstrap.php';

header('Cache-Control: no-store, private');
header('X-Robots-Tag: noindex, nofollow, nosnippet');
header('Referrer-Policy: no-referrer');

function miq_android_google_redirect($path)
{
    header('Location: ' . $path, true, 302);
    exit;
}

$request_method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($request_method !== 'POST') {
    miq_account_flash('warning', 'Secure Google sign-in requires the latest 360MiQ Android app. You can still sign in with email and password.');
    miq_android_google_redirect('account.php?view=login');
}

$content_length = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
if ($content_length > 20000) {
    miq_account_flash('danger', 'The Android Google sign-in response was too large. Please try again.');
    miq_android_google_redirect('account.php?view=login');
}

$return_to = 'workspace';
try {
    miq_account_require_rate_limit('login_ip', miq_account_client_ip(), 'Too many login attempts. Please try again later.');
    $challenge = miq_account_consume_native_google_challenge((string) ($_POST['state'] ?? ''));
    $return_to = $challenge['return_to'];
    miq_account_process_google_login((string) ($_POST['credential'] ?? ''), $challenge['nonce']);
    miq_android_google_redirect($return_to);
} catch (Throwable $error) {
    miq_account_flash('danger', $error->getMessage());
    miq_android_google_redirect('account.php?view=login&return_to=' . rawurlencode($return_to));
}
