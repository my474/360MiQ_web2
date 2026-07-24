<?php
/**
 * Plugin Name: 360MiQ Main Site SSO
 * Description: Maps authenticated 360MiQ main-site accounts to WordPress contributor accounts.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function miq_main_site_sso_secret()
{
    return defined('MIQ_SSO_SHARED_SECRET') ? (string) MIQ_SSO_SHARED_SECRET : '';
}

function miq_main_site_sso_return_to($value)
{
    $value = wp_unslash((string) $value);
    if ($value === '' || strpos($value, '/') !== 0 || strpos($value, '//') === 0 || preg_match('/^[a-z][a-z0-9+.-]*:/i', $value)) {
        return admin_url('edit.php');
    }
    return $value;
}

function miq_main_site_sso_bootstrap()
{
    if (empty($_GET['miq_sso']) || $_GET['miq_sso'] !== '1' || empty($_GET['token'])) {
        return;
    }

    if (miq_main_site_sso_secret() === '') {
        wp_die(esc_html__('Main-site SSO is not configured.', 'miq-main-site-sso'));
    }

    $main_site = defined('MIQ_MAIN_SITE_URL') ? MIQ_MAIN_SITE_URL : 'https://360miq.com';
    $endpoint = untrailingslashit($main_site) . '/account_sso.php?mode=consume';
    $response = wp_remote_post($endpoint, array(
        'timeout' => 8,
        'headers' => array('X-MIQ-SSO-Secret' => miq_main_site_sso_secret()),
        'body' => array('token' => sanitize_text_field(wp_unslash($_GET['token'])))
    ));
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        wp_die(esc_html__('The main-site login could not be verified.', 'miq-main-site-sso'));
    }

    $payload = json_decode(wp_remote_retrieve_body($response), true);
    $account = isset($payload['user']) && is_array($payload['user']) ? $payload['user'] : null;
    if (!$account || empty($account['email'])) {
        wp_die(esc_html__('The main-site login response was invalid.', 'miq-main-site-sso'));
    }

    $email = sanitize_email($account['email']);
    $user = get_user_by('email', $email);
    if (!$user) {
        $username_base = sanitize_user($account['display_name'], true);
        if ($username_base === '') $username_base = 'miq-contributor';
        $username = $username_base;
        $suffix = 1;
        while (username_exists($username)) {
            $username = $username_base . '-' . $suffix;
            $suffix++;
        }
        $user_id = wp_insert_user(array(
            'user_login' => $username,
            'user_pass' => wp_generate_password(32, true, true),
            'user_email' => $email,
            'display_name' => sanitize_text_field($account['display_name'] ?? $username),
            'role' => 'contributor'
        ));
        if (is_wp_error($user_id)) {
            wp_die(esc_html__('The WordPress contributor account could not be created.', 'miq-main-site-sso'));
        }
        $user = get_user_by('id', $user_id);
    }

    update_user_meta($user->ID, 'miq_main_user_id', absint($account['id'] ?? 0));
    if (!$user->has_cap('manage_options') && !$user->has_cap('edit_others_posts')) {
        $user->set_role('contributor');
    }
    wp_set_current_user($user->ID, $user->user_login);
    wp_set_auth_cookie($user->ID, true);
    do_action('wp_login', $user->user_login, $user);
    wp_safe_redirect(miq_main_site_sso_return_to($_GET['return_to'] ?? admin_url('edit.php')));
    exit;
}
add_action('init', 'miq_main_site_sso_bootstrap', 1);
