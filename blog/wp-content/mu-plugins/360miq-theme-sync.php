<?php
/**
 * Plugin Name: 360MiQ Blog Theme Sync
 * Description: Syncs the public WordPress blog with the 360MiQ light/dark mode preference.
 * Author: 360MiQ
 * Version: 1.0.0
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

		$toggle  = '<li class="menu-item miq360-theme-toggle-item">';
		$toggle .= '<button type="button" id="miq360-blog-theme-toggle" class="miq360-theme-toggle" aria-label="Switch theme" title="Switch theme">';
		$toggle .= '<span class="miq360-theme-toggle-icon" aria-hidden="true">&#x2600;&#xfe0f;</span>';
		$toggle .= '<span class="screen-reader-text">Switch theme</span>';
		$toggle .= '</button>';
		$toggle .= '</li>';

		return $items . $toggle;
	}
}
add_filter( 'wp_nav_menu_items', 'miq360_blog_theme_sync_menu_toggle', 10, 2 );
