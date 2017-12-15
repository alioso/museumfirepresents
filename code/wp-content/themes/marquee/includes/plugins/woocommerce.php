<?php
/**
 * WooCommerce integration.
 *
 * @package Marquee
 * @since 1.0.0
 * @link https://docs.woocommerce.com/document/third-party-custom-theme-compatibility/
 */

/**
 * Set up WooCommerce theme support.
 *
 * @since 1.0.0
 */
function marquee_woocommerce_setup() {
	add_theme_support( 'woocommerce' );

	// Disable the page title for the catalog and product archive pages.
	add_filter( 'woocommerce_show_page_title', '__return_false' );
}
add_action( 'after_setup_theme', 'marquee_woocommerce_setup', 11 );

/**
 * Remove the default WooCommerce content wrappers.
 *
 * @since 1.0.0
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper' );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end' );

/**
 * Print the default theme content open tag.
 *
 * Wraps WooCommerce content with the same elements used throughout the theme.
 *
 * @since 1.0.0
 */
function marquee_woocommerce_before_main_content() {
	echo '<main id="primary" class="content-area" role="main" itemprop="mainContentOfPage">';
}
add_action( 'woocommerce_before_main_content', 'marquee_woocommerce_before_main_content' );

/**
 * Print the default theme content wrapper close tag.
 *
 * @since 1.0.0
 */
function marquee_woocommerce_after_main_content() {
	echo '</main>';
}
add_action( 'woocommerce_after_main_content', 'marquee_woocommerce_after_main_content' );
