<?php
/**
 * Track action links provider.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Track action links provider class.
 *
 * @package CuePro
 * @since   1.0.0
 */
class CuePro_Provider_TrackActionLinks extends CuePro_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {
		add_action( 'cue_playlist_track_details_after', array( $this, 'display_track_action_links' ) );
	}

	/**
	 * Display the purchase link on the front end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $track Track data.
	 */
	public function display_track_action_links( $track ) {
		cue_track_action_links( $track, array(
			'container_class' => 'cue-track-actions cue-track-cell',
		) );
	}
}
