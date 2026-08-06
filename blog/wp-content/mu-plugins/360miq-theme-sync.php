<?php
/**
 * Plugin Name: 360MiQ Blog Theme Sync
 * Description: Syncs the public WordPress blog theme and shared Recent Analyses behavior.
 * Author: 360MiQ
 * Version: 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'miq360_blog_theme_sync_bootstrap' ) ) {
	function miq360_blog_theme_sync_bootstrap() {
		if ( is_admin() ) {
			return;
		}
		?>
<script id="miq360-blog-theme-bootstrap">
(function(){try{var key='360miq-dark-mode';var saved=localStorage.getItem(key);var dark=saved==='true'||(saved===null&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches);document.documentElement.setAttribute('data-theme',dark?'dark':'light');}catch(e){}})();
</script>
		<?php
	}
}
add_action( 'wp_head', 'miq360_blog_theme_sync_bootstrap', 0 );

if ( ! function_exists( 'miq360_blog_theme_sync_assets' ) ) {
	function miq360_blog_theme_sync_assets() {
		if ( is_admin() ) {
			return;
		}

		$base_path = plugin_dir_path( __FILE__ );
		$base_url  = plugin_dir_url( __FILE__ );
		$css_path  = $base_path . '360miq-theme-sync.css';
		$js_path   = $base_path . '360miq-theme-sync.js';

		wp_enqueue_style(
			'miq360-blog-theme-sync',
			$base_url . '360miq-theme-sync.css',
			array(),
			file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0'
		);

		wp_enqueue_script(
			'miq360-blog-theme-sync',
			$base_url . '360miq-theme-sync.js',
			array(),
			file_exists( $js_path ) ? filemtime( $js_path ) : '1.0.0',
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'miq360_blog_theme_sync_assets', 99 );

if ( ! function_exists( 'miq360_blog_theme_sync_menu_toggle' ) ) {
	function miq360_blog_theme_sync_menu_toggle( $items, $args ) {
		if ( is_admin() || empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
			return $items;
		}

		$main_site_url = function_exists( 'miq_main_site_sso_main_site_url' )
			? miq_main_site_sso_main_site_url( 'production' )
			: 'https://360miq.com';
		$account_url = trailingslashit( $main_site_url ) . 'workspace';
		$account  = '<li class="menu-item miq360-account-item">';
		$account .= '<a href="' . esc_url( $account_url ) . '" class="miq360-account-link nav-link" aria-label="Account" title="Account">';
		$account .= '<i class="fas fa-user-circle" aria-hidden="true"></i>';
		$account .= '</a>';
		$account .= '</li>';

		$toggle  = '<li class="menu-item miq360-theme-toggle-item">';
		$toggle .= '<a href="#" id="theme-toggle" class="miq360-theme-toggle nav-link" aria-label="Switch theme" title="Switch theme"></a>';
		$toggle .= '</li>';

		return $items . $account . $toggle;
	}
}
add_filter( 'wp_nav_menu_items', 'miq360_blog_theme_sync_menu_toggle', 10, 2 );

if ( ! function_exists( 'miq360_blog_is_recent_analyses_widget' ) ) {
	function miq360_blog_is_recent_analyses_widget( $instance ) {
		if ( empty( $instance['title'] ) ) {
			return false;
		}

		return 0 === strcasecmp(
			trim( wp_strip_all_tags( $instance['title'] ) ),
			'Recent Analyses'
		);
	}
}

if ( ! function_exists( 'miq360_blog_recent_analyses_orderby' ) ) {
	function miq360_blog_recent_analyses_orderby( $orderby, $query ) {
		if ( ! $query->get( 'miq360_recent_analyses_widget' ) ) {
			return $orderby;
		}

		global $wpdb;

		return "CASE
					WHEN {$wpdb->posts}.post_author NOT IN (0, 1, 2, 116)
					 AND {$wpdb->posts}.post_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 7 DAY)
					THEN 0
					ELSE 1
				END ASC,
				{$wpdb->posts}.post_date_gmt DESC,
				{$wpdb->posts}.ID DESC";
	}
}
add_filter( 'posts_orderby', 'miq360_blog_recent_analyses_orderby', 10, 2 );

if ( ! function_exists( 'miq360_blog_is_pinned_analysis' ) ) {
	function miq360_blog_is_pinned_analysis( $post ) {
		$excluded_authors = array( 0, 1, 2, 116 );

		if ( in_array( (int) $post->post_author, $excluded_authors, true ) ) {
			return false;
		}

		$published_at = (int) get_post_time( 'U', true, $post );

		return $published_at >= ( time() - ( 7 * DAY_IN_SECONDS ) );
	}
}

if ( ! function_exists( 'miq360_blog_render_recent_analyses_widget' ) ) {
	function miq360_blog_render_recent_analyses_widget( $instance, $args, $widget ) {
		$default_title = __( 'Recent Posts' );
		$title         = ! empty( $instance['title'] ) ? $instance['title'] : $default_title;
		$title         = apply_filters( 'widget_title', $title, $instance, $widget->id_base );
		$number        = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$show_date     = ! empty( $instance['show_date'] );

		if ( ! $number ) {
			$number = 5;
		}

		$query_args = apply_filters(
			'widget_posts_args',
			array(
				'posts_per_page'      => $number,
				'no_found_rows'       => true,
				'post_status'         => 'publish',
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
			),
			$instance
		);
		$query_args['miq360_recent_analyses_widget'] = true;

		$recent_posts = new WP_Query( $query_args );

		if ( ! $recent_posts->have_posts() ) {
			return;
		}

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$format = current_theme_supports( 'html5', 'navigation-widgets' ) ? 'html5' : 'xhtml';
		$format = apply_filters( 'navigation_widgets_format', $format );

		if ( 'html5' === $format ) {
			$aria_label = trim( wp_strip_all_tags( $title ) );
			$aria_label = $aria_label ? $aria_label : $default_title;
			echo '<nav aria-label="' . esc_attr( $aria_label ) . '">';
		}

		echo '<ul>';

		foreach ( $recent_posts->posts as $recent_post ) {
			$post_title   = get_the_title( $recent_post->ID );
			$link_title   = $post_title ? $post_title : __( '(no title)' );
			$aria_current = get_queried_object_id() === $recent_post->ID
				? ' aria-current="page"'
				: '';

			echo '<li>';
			echo '<a href="' . esc_url( get_permalink( $recent_post->ID ) ) . '"' . $aria_current . '>';
			echo esc_html( $link_title );
			echo '</a>';

			if ( miq360_blog_is_pinned_analysis( $recent_post ) ) {
				echo '<span class="miq360-recent-analysis-badge" title="Pinned for 7 days">Pinned</span>';
			}

			if ( $show_date ) {
				echo '<span class="post-date">' . esc_html( get_the_date( '', $recent_post->ID ) ) . '</span>';
			}

			echo '</li>';
		}

		echo '</ul>';

		if ( 'html5' === $format ) {
			echo '</nav>';
		}

		echo $args['after_widget'];
	}
}

if ( ! function_exists( 'miq360_blog_recent_analyses_widget' ) ) {
	function miq360_blog_recent_analyses_widget( $instance, $widget, $args ) {
		if ( is_admin() ||
			'recent-posts' !== $widget->id_base ||
			! miq360_blog_is_recent_analyses_widget( $instance ) )
		{
			return $instance;
		}

		miq360_blog_render_recent_analyses_widget( $instance, $args, $widget );

		return false;
	}
}
add_filter( 'widget_display_callback', 'miq360_blog_recent_analyses_widget', 10, 3 );
