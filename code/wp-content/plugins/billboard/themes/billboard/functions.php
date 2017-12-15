<?php
/**
 * Billboard theme functions.
 *
 * @package   Billboard
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Set up theme defaults and register support for WordPress features.
 *
 * @since 1.0.0
 */
function billboard_setup() {
	// Add support for the title tag.
	add_theme_support( 'title-tag' );
}
add_action( 'after_setup_theme', 'billboard_setup' );

/**
 * Enqueue theme assets.
 *
 * @since 1.0.0
 */
function billboard_enqueue_assets() {
	wp_enqueue_script(
		'fitie',
		billboard()->get_url( 'assets/js/vendor/fitie.js' ),
		array(),
		'1.0.0'
	);

	wp_enqueue_style(
		'billboard',
		get_stylesheet_uri(),
		array( 'mediaelement', 'themicons' )
	);
}
add_action( 'wp_enqueue_scripts', 'billboard_enqueue_assets' );

/**
 * Enqueue assets for the Customizer preview.
 *
 * @since 1.1.0
 */
function billboard_enqueue_customizer_preview_assets() {
	wp_enqueue_script(
		'billboard-customize-preview',
		billboard()->get_url( 'assets/js/customize-preview.js' ),
		array( 'customize-preview', 'customize-preview-nav-menus', 'underscore', 'wp-util' ),
		'20161116',
		true
	);
}
add_action( 'customize_preview_init', 'billboard_enqueue_customizer_preview_assets' );

/**
 * Disable selective refresh for nav menus when previewing Billboard.
 *
 * The social menu in Billboard doesn't work like core menus, so the selective
 * refresh functionality needs to be disabled. It may receive a custom partial
 * in the future to handle selective refresh separately.
 *
 * @since 1.0.0
 *
 * @param WP_Customize_Manager $wp_customize Customize manager.
 */
function billboard_customize_disable_nav_menus_selective_refresh( $wp_customize ) {
	remove_filter( 'wp_nav_menu', array( $wp_customize->nav_menus, 'filter_wp_nav_menu' ), 10, 2 );
}
add_action( 'customize_preview_init', 'billboard_customize_disable_nav_menus_selective_refresh' );
