<?php
/**
 * General template tags.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Display track action links.
 *
 * @since 1.0.0
 *
 * @param  array $track Track data.
 * @param  array $args  Arguments to modify the output.
 */
function cue_track_action_links( $track, $args = array() ) {
	$args = wp_parse_args( $args, array(
		'container_class' => 'cue-track-actions',
		'force-download'  => (bool) get_option( 'cuepro_force_downloads', false ),
	) );

	$args = apply_filters( 'cue_track_action_links_args', $args, $track );

	echo '<span class="' . esc_attr( $args['container_class'] ) . '">';

		if ( ! empty( $track['downloadUrl'] ) ) {
			printf(
				' <a href="%s" class="cue-track-download cue-button cue-button-icon"%s>%s</a>',
				esc_url( $track['downloadUrl'] ),
				$args['force-download'] ? ' download' : '',
				'<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 12 12"><path d="M0,3h3V0h6v3h3L6,9L0,3z M0,10v2h12v-2H0z"/></svg>'
			);
		}

		if ( ! empty( $track['purchaseUrl'] ) ) {
			printf(
				' <a href="%s" class="cue-track-purchase cue-button">%s</a>',
				esc_url( $track['purchaseUrl'] ),
				esc_html( $track['purchaseText'] )
			);
		}

	echo '</span>';
}
