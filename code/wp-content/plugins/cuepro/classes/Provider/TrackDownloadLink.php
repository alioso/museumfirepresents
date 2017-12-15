<?php
/**
 * Track download link provider.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Track download link provider class.
 *
 * @package CuePro
 * @since   1.0.0
 */
class CuePro_Provider_TrackDownloadLink extends CuePro_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {
		add_action( 'cue_display_track_fields_left', array( $this, 'display_edit_fields' ) );
		add_filter( 'cue_default_track_properties',  array( $this, 'register_download_url' ) );
		add_filter( 'cue_sanitize_track',            array( $this, 'sanitize_download_url' ), 10, 2 );
		add_filter( 'cue_playlist_tracks',           array( $this, 'add_download_url' ), 10, 3 );
	}

	/**
	 * Display fields to edit the download URL.
	 *
	 * @since 1.0.0
	 */
	public function display_edit_fields() {
		?>
		<p>
			<label>
				<?php esc_html_e( 'Download URL:', 'cuepro' ); ?><br>
				<input type="text" name="tracks[][downloadUrl]" placeholder="https://example.com/" value="{{{ data.downloadUrl }}}" data-setting="downloadUrl" class="regular-text">
			</label>
		</p>
		<?php
	}

	/**
	 * Register the download URL field.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $args Track fields.
	 * @return array
	 */
	public function register_download_url( $args ) {
		$args['downloadUrl'] = '';
		return $args;
	}

	/**
	 * Sanitize a download URL.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $track   Track fields.
	 * @param  array $context Filter context.
	 * @return array
	 */
	public function sanitize_download_url( $track, $context ) {
		if ( 'save' === $context && ! empty( $track['downloadUrl'] ) ) {
			$track['downloadUrl'] = esc_url_raw( $track['downloadUrl'] );
		}

		return $track;
	}

	/**
	 * Add the download URL to tracks.
	 *
	 * @since 1.0.0
	 *
	 * @param  array   $tracks   Array of tracks.
	 * @param  WP_Post $playlist Playlist post object.
	 * @param  string  $context  Filter context.
	 * @return array
	 */
	public function add_download_url( $tracks, $playlist, $context ) {
		if ( ! in_array( $context, array( 'display', 'wp-playlist' ), true ) ) {
			return $tracks;
		}

		foreach ( $tracks as $key => $track ) {
			if ( empty( $track['downloadUrl'] ) ) {
				$tracks[ $key ]['downloadUrl'] = '';
			}
		}

		return $tracks;
	}
}
