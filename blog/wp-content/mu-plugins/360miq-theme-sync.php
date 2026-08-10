<?php
/**
 * Plugin Name: 360MiQ Blog Theme Sync
 * Description: Syncs the public WordPress blog theme and shared Recent Analyses behavior.
 * Author: 360MiQ
 * Version: 1.2.0
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

if ( ! function_exists( 'miq360_blog_theme_sync_main_site_user' ) ) {
	function miq360_blog_theme_sync_main_site_user() {
		static $resolved = false;
		static $user     = null;

		if ( $resolved ) {
			return $user;
		}

		$resolved          = true;
		$account_bootstrap = dirname( __DIR__, 3 ) . '/account/bootstrap.php';
		if ( ! is_file( $account_bootstrap ) ) {
			return null;
		}

		require_once $account_bootstrap;
		if ( function_exists( 'miq_account_current_user' ) ) {
			$user = miq_account_current_user();
		}

		return $user;
	}
}

if ( ! function_exists( 'miq360_blog_theme_sync_unread_notifications' ) ) {
	function miq360_blog_theme_sync_unread_notifications( $main_site_user = null, $wordpress_user = null ) {
		$user_id = is_array( $main_site_user ) ? absint( $main_site_user['id'] ?? 0 ) : 0;
		if ( $user_id <= 0 && $wordpress_user instanceof WP_User && function_exists( 'get_user_meta' ) ) {
			$user_id = absint( get_user_meta( $wordpress_user->ID, 'miq_main_user_id', true ) );
		}
		if ( $user_id <= 0 || ! function_exists( 'miq_account_unread_notification_count' ) ) {
			return 0;
		}

		try {
			return max( 0, (int) miq_account_unread_notification_count( $user_id ) );
		} catch ( Throwable $error ) {
			return 0;
		}
	}
}

if ( ! function_exists( 'miq360_blog_theme_sync_menu_toggle' ) ) {
	function miq360_blog_theme_sync_menu_toggle( $items, $args ) {
		if ( is_admin() || empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
			return $items;
		}

		$main_site_url = function_exists( 'miq_main_site_sso_main_site_url' )
			? miq_main_site_sso_main_site_url( 'production' )
			: 'https://360miq.com';
		$main_site_path = trailingslashit( $main_site_url );
		$wp_authenticated   = function_exists( 'is_user_logged_in' ) && is_user_logged_in();
		$current_user       = $wp_authenticated && function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
		$main_site_user     = $wp_authenticated ? null : miq360_blog_theme_sync_main_site_user();
		$is_authenticated   = $wp_authenticated || $main_site_user !== null;
		$main_site_identity = $current_user instanceof WP_User ? $current_user : $main_site_user;
		$needs_sso_handoff  = ! $wp_authenticated && $main_site_user !== null;
		$account_state = $is_authenticated ? 'is-authenticated' : 'is-guest';
		$unread_notifications = $is_authenticated
			? miq360_blog_theme_sync_unread_notifications( $main_site_user, $current_user )
			: 0;
		$unread_badge = $unread_notifications > 99 ? '99+' : (string) $unread_notifications;
		$account_aria_label = $unread_notifications > 0
			? sprintf( 'Account menu, %d unread notifications', $unread_notifications )
			: 'Account menu';
		$account_url = $is_authenticated
			? '#'
			: $main_site_path . 'account.php?view=login&amp;return_to=' . rawurlencode( '/blog/' );
		$account  = '<li class="menu-item miq360-account-item ' . esc_attr( $account_state ) . '">';
		$account .= '<a href="' . esc_url( $account_url ) . '" id="miq360-blog-account-toggle" class="miq360-account-link nav-link ' . esc_attr( $account_state ) . '" aria-label="' . esc_attr( $is_authenticated ? $account_aria_label : 'Sign in' ) . '" title="' . esc_attr( $is_authenticated ? 'Account' : 'Sign in' ) . '"';
		if ( $is_authenticated ) {
			$account .= ' aria-haspopup="true" aria-expanded="false" aria-controls="miq360-blog-account-menu"';
		}
		$account .= '><span class="miq360-account-avatar-wrap"><i class="fas fa-user-circle miq-account-avatar" aria-hidden="true"></i>';
		if ( $unread_notifications > 0 ) {
			$account .= '<span class="miq360-account-icon-badge" aria-hidden="true">' . esc_html( $unread_badge ) . '</span>';
		}
		$account .= '</span>';
		if ( $is_authenticated ) {
			$account .= '<span class="miq-account-chevron" aria-hidden="true"></span>';
		}
		$account .= '</a>';

		if ( $is_authenticated ) {
			if ( is_array( $main_site_identity ) && ! empty( $main_site_identity['display_name'] ) ) {
				$display_name = $main_site_identity['display_name'];
			} elseif ( $main_site_identity instanceof WP_User && $main_site_identity->display_name !== '' ) {
				$display_name = $main_site_identity->display_name;
			} elseif ( $main_site_identity instanceof WP_User ) {
				$display_name = $main_site_identity->user_login;
			} else {
				$display_name = 'Account';
			}
			$logout_url = function_exists( 'wp_logout_url' )
				? wp_logout_url( $main_site_path . 'account_logout' )
				: $main_site_path . 'account_logout';
			$account .= '<div id="miq360-blog-account-menu" class="miq360-account-menu" role="menu" aria-labelledby="miq360-blog-account-toggle">';
			$account .= '<div class="miq360-account-menu-header" role="presentation"><span>Signed in as</span><strong>' . esc_html( $display_name ) . '</strong></div>';
			$account .= '<a class="miq360-account-menu-item" role="menuitem" href="' . esc_url( $main_site_path . 'workspace' ) . '"><i class="fas fa-layer-group fa-fw" aria-hidden="true"></i> My Workspace</a>';
			$account .= '<a class="miq360-account-menu-item" role="menuitem" href="' . esc_url( $main_site_path . 'workspace?tab=watchlists' ) . '"><i class="fas fa-star fa-fw" aria-hidden="true"></i> Watchlists</a>';
			$account .= '<a class="miq360-account-menu-item" role="menuitem" href="' . esc_url( $main_site_path . 'workspace?tab=charts' ) . '"><i class="fas fa-chart-line fa-fw" aria-hidden="true"></i> Saved Charts</a>';
			$account .= '<a class="miq360-account-menu-item" role="menuitem" href="' . esc_url( $main_site_path . 'workspace?tab=scripts' ) . '"><i class="fas fa-code fa-fw" aria-hidden="true"></i> Pine Scripts</a>';
			$account .= '<a class="miq360-account-menu-item" role="menuitem" href="' . esc_url( $main_site_path . 'workspace?tab=notes' ) . '"><i class="fas fa-book-open fa-fw" aria-hidden="true"></i> Research Notes</a>';
			$account .= '<a class="miq360-account-menu-item" role="menuitem" href="' . esc_url( $main_site_path . 'workspace?tab=alerts' ) . '"><i class="fas fa-bell fa-fw" aria-hidden="true"></i> Price Alerts</a>';
			$account .= '<a class="miq360-account-menu-item" role="menuitem" href="' . esc_url( $main_site_path . 'workspace?tab=notifications' ) . '"><i class="fas fa-inbox fa-fw" aria-hidden="true"></i> Notifications';
			if ( $unread_notifications > 0 ) {
				$account .= '<span class="miq360-account-unread">' . esc_html( $unread_badge ) . '</span>';
			}
			$account .= '</a>';
			$article_editor_url = $needs_sso_handoff
				? $main_site_path . 'account_sso.php?target=new-post'
				: admin_url( 'post-new.php' );
            $account .= '<a class="miq360-account-menu-item" role="menuitem" href="' . esc_url( $article_editor_url ) . '"><i class="fas fa-pen-alt fa-fw" aria-hidden="true"></i> Write an Article</a>';
			$account .= '<a class="miq360-account-menu-item" role="menuitem" href="' . esc_url( $main_site_path . 'account_settings' ) . '"><i class="fas fa-cog fa-fw" aria-hidden="true"></i> Settings</a>';
			$account .= '<div class="miq360-account-menu-divider" role="separator"></div>';
			$account .= '<a class="miq360-account-menu-item" role="menuitem" href="' . esc_url( $logout_url ) . '"><i class="fas fa-sign-out-alt fa-fw" aria-hidden="true"></i> Sign out</a>';
			$account .= '</div>';
		}

		$account .= '</li>';

		$toggle  = '<li class="menu-item miq360-theme-toggle-item">';
		$toggle .= '<a href="#" id="theme-toggle" class="miq360-theme-toggle nav-link" aria-label="Switch theme" title="Switch theme"></a>';
		$toggle .= '</li>';

		return $items . $toggle . $account;
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
