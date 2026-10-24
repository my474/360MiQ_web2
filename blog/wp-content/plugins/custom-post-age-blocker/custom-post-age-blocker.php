<?php
/**
 * Plugin Name: Custom Post Age Blocker
 * Description: Hides old "market update" posts by author 1 from listings and blocks direct access
 */

if ( ! defined( 'CUSTOM_POST_AGE_BLOCKER_EXCEPTION_TITLE' ) ) {
    define( 'CUSTOM_POST_AGE_BLOCKER_EXCEPTION_TITLE', 'Market Breadth Can Make You a Better Trader (Proven by Backtesting)' );
}

// Shared condition check, used for direct-access blocking
function is_old_market_update_post( $post ) {
    if ( CUSTOM_POST_AGE_BLOCKER_EXCEPTION_TITLE === (string) $post->post_title ) {
        return false;
    }

    if ( (int) $post->post_author !== 1 ) {
        return false;
    }

    /* $matches_plain_pattern = preg_match( '/market update \d{4}-\d{2}-\d{2}$/i', $post->post_title );
    $matches_plain_pattern2  = preg_match( '/HK Stock Connect Southbound Fund Flow \d{4}-\d{2}-\d{2}$/i', $post->post_title );

    if ( ! $matches_plain_pattern && ! $matches_plain_pattern2 ) {
        return false;
    } */

    $post_date_ts   = strtotime( $post->post_date );
    $six_months_ago = strtotime( '-7 days' );
    return $post_date_ts < $six_months_ago;
}

// 1. Block direct access (permalink) to matching old posts
function block_old_market_update_posts() {
    if ( is_singular( 'post' ) ) {
        global $post;
        if ( is_old_market_update_post( $post ) ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            nocache_headers();
        }
    }
}
add_action( 'template_redirect', 'block_old_market_update_posts' );

// 2. Exclude matching old posts from all listing queries (home, archive, category, search, etc.)
function exclude_old_market_update_posts_from_queries( $where, $query ) {
    if ( is_admin() ) {
        return $where;
    }

    global $wpdb;

    $six_months_ago = date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );

    /* $where .= $wpdb->prepare(
        " AND NOT (
            {$wpdb->posts}.post_author = %d
            AND (
                {$wpdb->posts}.post_title REGEXP %s
                OR {$wpdb->posts}.post_title REGEXP %s
            )
            AND {$wpdb->posts}.post_date < %s
        )",
        1,
        'market update [0-9]{4}-[0-9]{2}-[0-9]{2}$',
        'HK Stock Connect Southbound Fund Flow [0-9]{4}-[0-9]{2}-[0-9]{2}$',
        $six_months_ago
    ); */

	$where .= $wpdb->prepare(
        " AND NOT (
            {$wpdb->posts}.post_author = %d
            AND {$wpdb->posts}.post_date < %s
            AND {$wpdb->posts}.post_title <> %s
        )",
        1,
        $six_months_ago,
        CUSTOM_POST_AGE_BLOCKER_EXCEPTION_TITLE
    );

    return $where;
}
add_filter( 'posts_where', 'exclude_old_market_update_posts_from_queries', 10, 2 );

// 3. Block single-post REST API access (bypasses WP_Query, so posts_where doesn't catch it)
function block_old_market_update_posts_rest( $response, $post, $request ) {
    if ( is_old_market_update_post( $post ) ) {
        return new WP_Error(
            'rest_post_invalid_id',
            __( 'Invalid post ID.' ),
            array( 'status' => 404 )
        );
    }
    return $response;
}
add_filter( 'rest_prepare_post', 'block_old_market_update_posts_rest', 10, 3 );
