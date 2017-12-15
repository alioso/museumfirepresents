<?php
/**
 * Cue integration.
 *
 * @package Marquee
 * @since 1.0.0
 * @link https://audiotheme.com/view/cue/
 */

/**
 * Register a Cue player.
 *
 * @since 1.0.0
 *
 * @param array $players List of players.
 */
function marquee_register_cue_players( $players ) {
	$players['marquee_player'] = esc_html__( 'Site-wide Player', 'marquee' );
	return $players;
}
add_filter( 'cue_players', 'marquee_register_cue_players' );

/**
 * Filter tracks for the site-wide player.
 *
 * Returns tracks from the Cue playlist associated with the player location.
 *
 * @since 1.0.0
 *
 * @param array $tracks List of tracks.
 */
function marquee_cue_player_tracks( $tracks ) {
	$tracks = get_cue_player_tracks( 'marquee_player', array( 'context' => 'wp-playlist' ) );

	foreach ( $tracks as $key => $track ) {
		$tracks[ $key ]['id'] = md5( $track['artist'] . $track['title'] . $track['src'] );
	}

	return $tracks;
}
add_filter( 'pre_marquee_player_tracks', 'marquee_cue_player_tracks' );

/**
 * Display a purchase link in the player tracklist.
 *
 * @since 1.0.0
 *
 * @param array $track Track.
 */
function marquee_player_track_actions( $track ) {
	if ( ! class_exists( 'CuePro_Plugin' ) ) {
		return;
	}

	// Make there's no whitespace in the enclosing span so it can be targeted
	// in CSS using `.track-actions:empty`.
	?>
	<span class="track-actions track-cell"><# if ( 'downloadUrl' in data && data.downloadUrl.length ) { #>
		<a href="{{ data.downloadUrl }}" class="cue-track-download track-button cue-button cue-button-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 12 12"><path d="M0,3h3V0h6v3h3L6,9L0,3z M0,10v2h12v-2H0z"/></svg></a>
	<# } #><# if ( 'purchaseUrl' in data && data.purchaseUrl.length ) { #>
		<a href="{{ data.purchaseUrl }}" class="track-purchase track-button cue-button">{{ data.purchaseText }}</a>
	<# } #></span>
	<?php
}
add_action( 'marquee_player_track_details_after', 'marquee_player_track_actions' );
