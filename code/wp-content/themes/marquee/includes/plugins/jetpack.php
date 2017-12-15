<?php
/**
 * Jetpack integration.
 *
 * @package Marquee
 * @since 1.0.0
 * @link https://jetpack.com/
 */

/**
 * Set up Jetpack theme support.
 *
 * Adds support for Infinite Scroll.
 *
 * @since 1.0.0
 */
function marquee_jetpack_setup() {
	// Add support for Infinite Scroll.
	add_theme_support( 'infinite-scroll', apply_filters( 'marquee_infinite_scroll_args', array(
		'container' => 'primary',
		'footer'    => 'footer',
		'render'    => 'marquee_jetpack_infinite_scroll_render',
		'wrapper'   => false,
	) ) );
}
add_action( 'after_setup_theme', 'marquee_jetpack_setup' );

/**
 * Load required assets for Jetpack support.
 *
 * @since 1.2.0
 */
function marquee_jetpack_enqueue_assets() {
	wp_enqueue_style(
		'marquee-jetpack',
		get_template_directory_uri() . '/assets/css/jetpack.css',
		array( 'marquee-style' )
	);

	wp_style_add_data( 'marquee-jetpack', 'rtl', 'replace' );
}
add_action( 'wp_enqueue_scripts', 'marquee_jetpack_enqueue_assets' );

/**
 * Filter Infinite Scroll JavaScript settings.
 *
 * @since 1.0.0
 *
 * @param  array $settings Settings.
 * @return array
 */
function marquee_jetpack_infinite_scroll_js_settings( $settings ) {
	$settings['text'] = esc_html_x( 'Load More', 'load posts button text', 'marquee' );
	return $settings;
}
add_filter( 'infinite_scroll_js_settings', 'marquee_jetpack_infinite_scroll_js_settings' );

/**
 * Switch to the "click to load" type when the front page is showing and the
 * homepage widget area.
 *
 * Jetpack stores settings too early to use conditionals like is_front_page()
 * using the "infinite_scroll_has_footer_widgets" filter.
 *
 * @since 1.0.0
 */
function marquee_infinite_scroll_has_footer_widgets() {
	if ( class_exists( 'The_Neverending_Home_Page' ) && is_front_page() && is_active_sidebar( 'home-widgets' ) ) {
		The_Neverending_Home_Page::$settings['footer_widgets'] = true;
		The_Neverending_Home_Page::$settings['type'] = 'click';
	}
}
add_filter( 'template_redirect', 'marquee_infinite_scroll_has_footer_widgets', 9 );

if ( ! function_exists( 'marquee_jetpack_infinite_scroll_render' ) ) :
/**
 * Callback for the Infinite Scroll module in Jetpack to render additional posts.
 *
 * @since 1.0.0
 */
function marquee_jetpack_infinite_scroll_render() {
	while ( have_posts() ) {
		the_post();
		get_template_part( 'templates/parts/content', get_post_format() );
	}
}
endif;
