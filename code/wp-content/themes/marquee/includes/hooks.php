<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * @package Marquee
 * @since 1.0.0
 */

/**
 * Register template parts to load throughout the theme.
 *
 * Take note of the priorities. Changing them will allow template parts to be
 * loaded in a different order.
 *
 * To remove any of these parts, use remove_action() in the
 * "marquee_register_template_parts" hook or later.
 *
 * @since 1.0.0
 */
function marquee_register_template_parts() {
	// Load the home widgets area on the front page.
	if ( is_front_page() ) {
		add_action( 'marquee_main_after', 'marquee_front_page_widgets' );
	}

	if ( ! get_theme_mod( 'disable_player' ) ) {
		add_action( 'marquee_after',  'marquee_player_template' );
	}

	do_action( 'marquee_register_template_parts' );
}
add_action( 'template_redirect', 'marquee_register_template_parts', 9 );

/**
 * Filter archive titles.
 *
 * @since 1.0.0
 *
 * @param string $title Archive title.
 * @return string
 */
function marquee_filter_archive_title( $title ) {
	if ( is_home() && ! is_front_page() ) {
		$title = get_the_title( get_option( 'page_for_posts' ) );
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'marquee_filter_archive_title' );

/**
 * Add an image itemprop attribute to image attachments.
 *
 * @since 1.0.0
 *
 * @param  array $attr Attributes for the image markup.
 * @return array
 */
function marquee_attachment_image_attributes( $attr ) {
	$attr['itemprop'] = 'image';
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'marquee_attachment_image_attributes' );
