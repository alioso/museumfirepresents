<?php
/**
 * Track purchase link provider.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Track purchase link provider class.
 *
 * @package CuePro
 * @since   1.0.0
 */
class CuePro_Provider_TrackPurchaseLink extends CuePro_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {
		add_action( 'cue_display_track_fields_right', array( $this, 'display_edit_fields' ) );
		add_filter( 'cue_default_track_properties',   array( $this, 'register_purchase_text' ) );
		add_filter( 'cue_default_track_properties',   array( $this, 'register_purchase_url' ) );
		add_filter( 'cue_sanitize_track',             array( $this, 'sanitize_purchase_text' ), 10, 2 );
		add_filter( 'cue_sanitize_track',             array( $this, 'sanitize_purchase_url' ), 10, 2 );
		add_filter( 'cue_playlist_tracks',            array( $this, 'add_purchase_text' ), 10, 3 );
		add_filter( 'cue_playlist_tracks',            array( $this, 'add_purchase_url' ), 10, 3 );
	}

	/**
	 * Display fields to edit the purchase text and URL.
	 *
	 * @since 1.0.0
	 */
	public function display_edit_fields() {
		?>
		<p>
			<label>
				<?php esc_html_e( 'Purchase Text:', 'cuepro' ); ?><br>
				<input type="text" name="tracks[][purchaseText]" placeholder="<?php esc_attr_e( 'Buy', 'cuepro' ); ?>" value="{{{ data.purchaseText }}}" data-setting="purchaseText" class="regular-text">
			</label>
		</p>
		<p>
			<label>
				<?php esc_html_e( 'Purchase URL:', 'cuepro' ); ?><br>
				<input type="text" name="tracks[][purchaseUrl]" placeholder="https://example.com/" value="{{{ data.purchaseUrl }}}" data-setting="purchaseUrl" class="regular-text">
			</label>
		</p>
		<?php
	}

	/**
	 * Register the purchase text field.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $args Track fields.
	 * @return array
	 */
	public function register_purchase_text( $args ) {
		$args['purchaseText'] = '';
		return $args;
	}

	/**
	 * Register the purchase URL field.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $args Track fields.
	 * @return array
	 */
	public function register_purchase_url( $args ) {
		$args['purchaseUrl'] = '';
		return $args;
	}

	/**
	 * Sanitize purchase text.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $track   Track fields.
	 * @param  array $context Filter context.
	 * @return array
	 */
	public function sanitize_purchase_text( $track, $context ) {
		if ( 'save' === $context && ! empty( $track['purchaseText'] ) ) {
			$track['purchaseText'] = sanitize_text_field( $track['purchaseText'] );
		}

		return $track;
	}

	/**
	 * Sanitize a purchase URL.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $track   Track fields.
	 * @param  array $context Filter context.
	 * @return array
	 */
	public function sanitize_purchase_url( $track, $context ) {
		if ( 'save' === $context && ! empty( $track['purchaseUrl'] ) ) {
			$track['purchaseUrl'] = esc_url_raw( $track['purchaseUrl'] );
		}

		return $track;
	}

	/**
	 * Add the purchase text property to tracks.
	 *
	 * @since 1.0.0
	 *
	 * @param  array   $tracks   Array of tracks.
	 * @param  WP_Post $playlist Playlist post object.
	 * @param  string  $context  Filter context.
	 * @return array
	 */
	public function add_purchase_text( $tracks, $playlist, $context ) {
		if ( ! in_array( $context, array( 'display', 'wp-playlist' ), true ) ) {
			return $tracks;
		}

		foreach ( $tracks as $key => $track ) {
			if ( empty( $track['purchaseText'] ) ) {
				$tracks[ $key ]['purchaseText'] = esc_html__( 'Buy', 'cuepro' );
			}
		}

		return $tracks;
	}

	/**
	 * Add the purchase URL to tracks.
	 *
	 * @since 1.0.0
	 *
	 * @param  array   $tracks   Array of tracks.
	 * @param  WP_Post $playlist Playlist post object.
	 * @param  string  $context  Filter context.
	 * @return array
	 */
	public function add_purchase_url( $tracks, $playlist, $context ) {
		if ( ! in_array( $context, array( 'display', 'wp-playlist' ), true ) ) {
			return $tracks;
		}

		foreach ( $tracks as $key => $track ) {
			if ( empty( $track['purchaseUrl'] ) ) {
				$tracks[ $key ]['purchaseUrl'] = '';
			}
		}

		return $tracks;
	}
}
