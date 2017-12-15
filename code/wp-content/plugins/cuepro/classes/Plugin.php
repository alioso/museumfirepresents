<?php
/**
 * Main plugin file.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Main plugin class.
 *
 * @package CuePro
 * @since   1.0.0
 */
class CuePro_Plugin extends CuePro_AbstractPlugin {
	/**
	 * Load the plugin.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin() {
		add_action( 'rest_api_init',      array( $this, 'register_rest_routes' ) );
		add_filter( 'wp_audio_shortcode', array( $this, 'add_audio_title_attribute' ), 15, 3 );
		add_filter( 'plugin_action_links_' . $this->get_basename(), array( $this, 'filter_action_links' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes() {
		$controller = new CuePro_REST_StatsController();
		$controller->register_routes();
	}

	/**
	 * Add a title attribute to audio elements rendered by the audio shortcode.
	 *
	 * The title attribute makes it possible for scripts to determine the title
	 * of the track.
	 *
	 * @since 1.0.0
	 *
	 * @param  string  $html  HTML.
	 * @param  array   $atts  Shortcode attributes.
	 * @param  WP_Post $audio Audio attachment.
	 * @return string
	 */
	public function add_audio_title_attribute( $html, $atts, $audio ) {
		if ( empty( $atts['title'] ) && ! empty( $audio ) ) {
			$atts['title'] = get_the_title( $audio );
		}

		if ( ! empty( $atts['title'] ) ) {
			$search  = '<audio';
			$replace = sprintf( '<audio title="%s"', esc_attr( $atts['title'] ) );
			$html    = str_replace( $search, $replace, $html );
		}

		return $html;
	}

	/**
	 * Filter plugin action links.
	 *
	 * Adds an 'Insights' link pointing to the admin screen.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $actions Array of action links.
	 * @return array
	 */
	public function filter_action_links( $actions ) {
		$actions['insights'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( self_admin_url( 'edit.php?post_type=cue_playlist&page=cuepro-insights' ) ),
			esc_html__( 'Insights', 'cuepro' )
		);

		return $actions;
	}
}
