<?php
/**
 * General template tags.
 *
 * @package   Billboard
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Display the Billboard content.
 *
 * @since 1.0.0
 */
function billboard_content() {
	echo apply_filters( 'billboard_content', billboard()->get_setting( 'content' ) );
}

/**
 * Display the Billboard logo.
 *
 * @since 1.0.0
 */
function billboard_logo() {
	echo wp_get_attachment_image( billboard()->get_setting( 'logo' ), 'full', false, array(
		'class'     => 'billboard-logo',
		'data-size' => 'full',
		'itemprop'  => 'logo',
	) );
}

/**
 * Display a background video.
 *
 * @since 1.1.0
 */
function billboard_background_video() {
	$attachment_id = billboard()->get_setting( 'background_video' );

	if ( empty( $attachment_id ) ) {
		return;
	}

	printf(
		'<video src="%s" autoplay loop muted playsinline></video>',
		esc_url( wp_get_attachment_url( $attachment_id ) )
	);
}
