<?php
/**
 * Plugin Name: 360MiQ Main Site SSO
 * Description: Maps authenticated 360MiQ main-site accounts to WordPress contributor accounts.
 * Version: 1.4.0
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
    $fallback = admin_url('edit.php');
    $admin_path = (string) wp_parse_url(admin_url(), PHP_URL_PATH);
    if (
        $value === ''
        || strpos($value, '/') !== 0
        || strpos($value, '//') === 0
        || preg_match('/[\x00-\x1F\x7F\\\\]/', $value)
        || preg_match('/^[a-z][a-z0-9+.-]*:/i', $value)
        || $admin_path === ''
        || strpos($value, $admin_path) !== 0
    ) {
        return $fallback;
    }
    return $value;
}

function miq_main_site_sso_issuer()
{
    $issuer = sanitize_key(wp_unslash((string) ($_GET['issuer'] ?? '')));
    if (!in_array($issuer, array('production', 'full'), true)) {
        wp_die(esc_html__('The main-site login source is invalid.', 'miq-main-site-sso'));
    }
    return $issuer;
}

function miq_main_site_sso_verify_issuer($issuer, $token)
{
    $provided = sanitize_text_field(wp_unslash((string) ($_GET['issuer_sig'] ?? '')));
    $expected = hash_hmac('sha256', (string) $issuer . "\n" . (string) $token, miq_main_site_sso_secret());
    return preg_match('/^[a-f0-9]{64}$/i', $provided) && hash_equals($expected, $provided);
}

function miq_main_site_sso_main_site_url($issuer)
{
    $url = defined('MIQ_MAIN_SITE_URL') ? (string) MIQ_MAIN_SITE_URL : 'https://360miq.com';
    $url = untrailingslashit(esc_url_raw($url));
    if ($issuer === 'full') {
        $url .= '/full';
    }
    $scheme = strtolower((string) wp_parse_url($url, PHP_URL_SCHEME));
    if (!wp_http_validate_url($url) || ($scheme !== 'https' && !(defined('WP_DEBUG') && WP_DEBUG && $scheme === 'http'))) {
        wp_die(esc_html__('The configured main-site SSO URL is invalid.', 'miq-main-site-sso'));
    }
    return $url;
}

function miq_main_site_sso_linked_user($main_user_id)
{
    if ($main_user_id <= 0) {
        return null;
    }
    $users = get_users(array(
        'meta_key' => 'miq_main_user_id',
        'meta_value' => (string) $main_user_id,
        'number' => 2,
        'count_total' => false,
    ));
    if (count($users) > 1) {
        wp_die(esc_html__('This 360MiQ account is linked to more than one WordPress profile. Contact an administrator.', 'miq-main-site-sso'));
    }
    return !empty($users) && $users[0] instanceof WP_User ? $users[0] : null;
}

function miq_main_site_sso_public_name($account, $fallback)
{
    $name = sanitize_text_field((string) ($account['display_name'] ?? ''));
    return $name !== '' ? $name : $fallback;
}

function miq_main_site_sso_resolve_user($account)
{
    $main_user_id = absint($account['id'] ?? 0);
    $email = sanitize_email((string) ($account['email'] ?? ''));
    if ($main_user_id <= 0 || !is_email($email)) {
        wp_die(esc_html__('The main-site login response was invalid.', 'miq-main-site-sso'));
    }

    $linked_user = miq_main_site_sso_linked_user($main_user_id);
    $email_user = get_user_by('email', $email);
    if ($linked_user && $email_user && (int) $linked_user->ID !== (int) $email_user->ID) {
        wp_die(esc_html__('Your 360MiQ account and email point to different WordPress profiles. Contact an administrator.', 'miq-main-site-sso'));
    }

    $user = $linked_user ?: $email_user;
    $created_by_sso = false;
    if (!$user) {
        $username_base = sanitize_user(miq_main_site_sso_public_name($account, 'miq-contributor'), true);
        if ($username_base === '') {
            $username_base = 'miq-contributor';
        }
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
            'display_name' => miq_main_site_sso_public_name($account, $username),
            'role' => 'contributor',
        ));
        if (is_wp_error($user_id)) {
            wp_die(esc_html__('The WordPress contributor account could not be created.', 'miq-main-site-sso'));
        }
        $user = get_user_by('id', $user_id);
        if (!$user instanceof WP_User) {
            wp_die(esc_html__('The WordPress contributor account could not be loaded.', 'miq-main-site-sso'));
        }
        $created_by_sso = true;
        update_user_meta($user->ID, 'miq_sso_managed_profile', '1');
    }

    $linked_main_user_id = absint(get_user_meta($user->ID, 'miq_main_user_id', true));
    if ($linked_main_user_id > 0 && $linked_main_user_id !== $main_user_id) {
        wp_die(esc_html__('This WordPress profile is already linked to another 360MiQ account.', 'miq-main-site-sso'));
    }
    update_user_meta($user->ID, 'miq_main_user_id', $main_user_id);

    $managed_profile = $created_by_sso || get_user_meta($user->ID, 'miq_sso_managed_profile', true) === '1';
    if ($managed_profile) {
        $public_name = miq_main_site_sso_public_name($account, $user->display_name);
        if ($public_name !== $user->display_name) {
            $updated = wp_update_user(array('ID' => $user->ID, 'display_name' => $public_name));
            if (is_wp_error($updated)) {
                wp_die(esc_html__('The WordPress author profile could not be updated.', 'miq-main-site-sso'));
            }
            $user = get_user_by('id', $user->ID);
        }

        $avatar_url = esc_url_raw((string) ($account['avatar_url'] ?? ''));
        if ($avatar_url !== '' && strtolower((string) wp_parse_url($avatar_url, PHP_URL_SCHEME)) === 'https') {
            update_user_meta($user->ID, 'miq_main_avatar_url', $avatar_url);
        } else {
            delete_user_meta($user->ID, 'miq_main_avatar_url');
        }
    }

    // Promote subscribers to Contributor, but never downgrade existing
    // Contributors, Authors, Editors, Administrators, or stronger custom roles.
    if (!$user->has_cap('edit_posts')) {
        $user->set_role('contributor');
        $user = get_user_by('id', $user->ID);
    }

    return $user;
}

function miq_main_site_sso_avatar_url($url, $id_or_email, $args)
{
    $user = null;
    if ($id_or_email instanceof WP_User) {
        $user = $id_or_email;
    } elseif (is_numeric($id_or_email)) {
        $user = get_user_by('id', absint($id_or_email));
    } elseif (is_string($id_or_email) && is_email($id_or_email)) {
        $user = get_user_by('email', $id_or_email);
    } elseif (is_object($id_or_email) && !empty($id_or_email->user_id)) {
        $user = get_user_by('id', absint($id_or_email->user_id));
    }
    if (!$user) {
        return $url;
    }

    $avatar_url = esc_url_raw((string) get_user_meta($user->ID, 'miq_main_avatar_url', true));
    if ($avatar_url !== '' && strtolower((string) wp_parse_url($avatar_url, PHP_URL_SCHEME)) === 'https') {
        return $avatar_url;
    }
    return $url;
}
add_filter('get_avatar_url', 'miq_main_site_sso_avatar_url', 10, 3);

/**
 * Keep contributors focused on their own submissions in the admin post list.
 *
 * The role's capability checks still protect editing and publishing. This
 * additionally scopes the list query, including requests that try to open
 * the core "All" view manually.
 */
function miq_main_site_sso_limit_post_list($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    global $pagenow;
    if ($pagenow !== 'edit.php') {
        return;
    }

    $post_type = $query->get('post_type') ?: 'post';
    $post_type_object = get_post_type_object($post_type);
    if (!$post_type_object) {
        return;
    }

    if (!current_user_can($post_type_object->cap->edit_others_posts)) {
        $query->set('author', get_current_user_id());
    }
}
add_action('pre_get_posts', 'miq_main_site_sso_limit_post_list');

function miq_main_site_sso_scope_post_counts($counts, $post_type, $perm)
{
    if (!is_admin()) {
        return $counts;
    }

    global $pagenow, $wpdb;
    if ($pagenow !== 'edit.php') {
        return $counts;
    }

    $post_type_object = get_post_type_object($post_type);
    if (!$post_type_object || current_user_can($post_type_object->cap->edit_others_posts)) {
        return $counts;
    }

    $user_id = get_current_user_id();
    if ($user_id <= 0) {
        return $counts;
    }

    $scoped_counts = array_fill_keys(get_post_stati(), 0);
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT post_status, COUNT(*) AS num_posts
            FROM {$wpdb->posts}
            WHERE post_type = %s
            AND post_author = %d
            GROUP BY post_status",
            $post_type,
            $user_id
        ),
        ARRAY_A
    );

    foreach ((array) $rows as $row) {
        $status = (string) ($row['post_status'] ?? '');
        if (array_key_exists($status, $scoped_counts)) {
            $scoped_counts[$status] = (int) $row['num_posts'];
        }
    }

    return (object) $scoped_counts;
}
add_filter('wp_count_posts', 'miq_main_site_sso_scope_post_counts', 10, 3);

function miq_main_site_sso_hide_all_post_views($views)
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->base !== 'edit') {
        return $views;
    }

    $post_type_object = get_post_type_object($screen->post_type ?: 'post');
    if (!$post_type_object || current_user_can($post_type_object->cap->edit_others_posts)) {
        return $views;
    }

    unset($views['all']);
    return $views;
}

function miq_main_site_sso_register_post_view_filters()
{
    foreach (get_post_types(array('show_ui' => true), 'names') as $post_type) {
        add_filter('views_edit-' . $post_type, 'miq_main_site_sso_hide_all_post_views', 20);
    }
}
add_action('admin_init', 'miq_main_site_sso_register_post_view_filters');

function miq_main_site_sso_bootstrap()
{
    if (empty($_GET['miq_sso']) || $_GET['miq_sso'] !== '1' || empty($_GET['token'])) {
        return;
    }

    nocache_headers();
    header('Referrer-Policy: no-referrer');

    if (miq_main_site_sso_secret() === '') {
        wp_die(esc_html__('Main-site SSO is not configured.', 'miq-main-site-sso'));
    }

    $token = sanitize_text_field(wp_unslash($_GET['token']));
    if (!preg_match('/^[a-f0-9]{32,128}$/i', $token)) {
        wp_die(esc_html__('The main-site login token is invalid.', 'miq-main-site-sso'));
    }

    $issuer = miq_main_site_sso_issuer();
    if (!miq_main_site_sso_verify_issuer($issuer, $token)) {
        wp_die(esc_html__('The main-site login source could not be verified.', 'miq-main-site-sso'));
    }

    $endpoint_issuers = array($issuer);
    if ($issuer === 'full') {
        // Some deployments expose the full site under /full but keep the
        // account database and SSO consumer at the production path.
        $endpoint_issuers[] = 'production';
    }

    $response = null;
    foreach ($endpoint_issuers as $endpoint_issuer) {
        $endpoint = miq_main_site_sso_main_site_url($endpoint_issuer) . '/account_sso.php?mode=consume';
        $response = wp_remote_post($endpoint, array(
            'timeout' => 8,
            'redirection' => 0,
            'sslverify' => true,
            'headers' => array(
                'X-MIQ-SSO-Secret' => miq_main_site_sso_secret(),
                'Authorization' => 'Bearer ' . miq_main_site_sso_secret(),
            ),
            'body' => array('token' => $token),
        ));
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            break;
        }
    }
    if (is_wp_error($response)) {
        error_log('[360MiQ SSO] token consume request failed: ' . $response->get_error_code());
        wp_die(esc_html__('WordPress could not reach the main-site login service. Please try again later.', 'miq-main-site-sso'));
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $response_payload = json_decode(wp_remote_retrieve_body($response), true);
        $response_error = isset($response_payload['error']) ? sanitize_text_field((string) $response_payload['error']) : '';
        $known_errors = array(
            'SSO is not configured.',
            'Invalid SSO token.',
            'SSO token is invalid or expired.',
            'SSO handoff failed.',
        );
        if (!in_array($response_error, $known_errors, true)) {
            $response_error = '';
        }
        error_log(
            '[360MiQ SSO] token consume request returned HTTP '
            . (int) $response_code
            . ' for issuer '
            . $issuer
            . ($response_error !== '' ? ': ' . $response_error : '')
        );
        wp_die(esc_html(sprintf(
            __('The main-site login service rejected this handoff (HTTP %d). Please start again from the Article Editor link.', 'miq-main-site-sso'),
            (int) $response_code
        )));
    }

    $payload = json_decode(wp_remote_retrieve_body($response), true);
    $account = isset($payload['user']) && is_array($payload['user']) ? $payload['user'] : null;
    if (!$account) {
        wp_die(esc_html__('The main-site login response was invalid.', 'miq-main-site-sso'));
    }

    $user = miq_main_site_sso_resolve_user($account);
    if (!$user instanceof WP_User) {
        wp_die(esc_html__('The WordPress contributor account could not be loaded.', 'miq-main-site-sso'));
    }

    wp_set_current_user($user->ID, $user->user_login);
    wp_set_auth_cookie($user->ID, true);
    do_action('wp_login', $user->user_login, $user);
    wp_safe_redirect(miq_main_site_sso_return_to($_GET['return_to'] ?? admin_url('edit.php')));
    exit;
}
add_action('init', 'miq_main_site_sso_bootstrap', 1);
