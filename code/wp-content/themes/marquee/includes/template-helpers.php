<?php
/**
 * Helper methods for loading or displaying template partials.
 *
 * These are typically miscellaneous template parts used outside the loop.
 * Although if the partial requires any sort of set up or tearddown, moving that
 * logic into a helper keeps the parent template a little more lean, clean,
 * reusable and easier to override in child themes.
 *
 * Loading these partials within an action hook will allow them to be easily
 * added, removed, or reordered without changing the parent template file.
 *
 * Take a look at marquee_register_template_parts() to see where most of these
 * are inserted.
 *
 * This approach tries to blend the two common approaches to theme development
 * (hooks or partials).
 *
 * @package Marquee
 * @since 1.0.0
 */

/**
 * Load the site-wide player template part.
 *
 * @since 1.0.0
 */
function marquee_player_template() {
	if ( ! marquee_is_player_active() ) {
		return;
	}

	wp_enqueue_script( 'marquee-player' );

	$tracks   = marquee_get_player_tracks();
	$settings = array(
		'signature' => md5( implode( ',', wp_list_pluck( $tracks, 'src' ) ) ),
		'tracks'    => $tracks,
	);

	include( locate_template( 'templates/parts/player.php' ) );
	echo '<script type="application/json" id="site-player-settings">' . wp_json_encode( $settings ) . '</script>';
}

/**
 * Display the home widgets sidebar area.
 *
 * @since 1.0.0
 */
function marquee_front_page_widgets() {
	if ( ! is_active_sidebar( 'home-widgets' ) ) {
		return;
	}

	get_sidebar( 'home' );
}
