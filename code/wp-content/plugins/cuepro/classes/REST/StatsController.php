<?php
/**
 * Stats REST controller.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Stats REST controller class.
 *
 * @package CuePro
 * @since   1.0.0
 */
class CuePro_REST_StatsController {
	/**
	 * Register REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route( 'cue', '/v1/stats', array(
			array(
				'methods'       => WP_REST_Server::CREATABLE,
				'callback'      => array( $this, 'log' ),
				'show_in_index' => false,
				'args'          => array(
					'action'       => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_key',
					),
					'client_uid'   => array(
						'required'          => true,
						'sanitize_callback' => array( $this, 'sanitize_client_uid' ),
					),
					'page_title'   => array(
						'default'           => '',
						'required'          => false,
						'sanitize_callback' => array( $this, 'sanitize_page_title' ),
					),
					'page_url'     => array(
						'required'          => true,
						'sanitize_callback' => array( $this, 'sanitize_page_url' ),
					),
					'target_time'  => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'target_title' => array(
						'default'           => '',
						'required'          => false,
						'sanitize_callback' => array( $this, 'sanitize_target_title' ),
					),
					'target_url'   => array(
						'required'          => true,
						'sanitize_callback' => array( $this, 'sanitize_target_url' ),
					),
				),
			),
		) );
	}

	/**
	 * Log an action.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_REST_Request $request Request instance.
	 * @return boolean
	 */
	public function log( $request ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'cue_events',
			array(
				'action'       => $request['action'],
				'client_ip'    => $this->get_ip_address(),
				'client_uid'   => $request['client_uid'],
				'page_title'   => $request['page_title'],
				'page_url'     => $request['page_url'],
				'target_time'  => $request['target_time'],
				'target_title' => $request['target_title'],
				'target_url'   => $request['target_url'],
				'created'      => date( 'Y-m-d H:i:s', time() ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s' )
		);

		return true;
	}

	/**
	 * Sanitize the client id.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $value Unique client id.
	 * @return string
	 */
	public function sanitize_client_uid( $value ) {
		return preg_replace( '/[^1-9.]/', '', $value );
	}

	/**
	 * Sanitize the page title.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $value Page title.
	 * @return string
	 */
	public function sanitize_page_title( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Sanitize the page URL.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $value Page URL.
	 * @return string
	 */
	public function sanitize_page_url( $value ) {
		return esc_url_raw( strtok( $value, '#' ) );
	}

	/**
	 * Sanitize the target title.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $value Target resource title.
	 * @return string
	 */
	public function sanitize_target_title( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Sanitize the target URL.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $value Target resource URL.
	 * @return string
	 */
	public function sanitize_target_url( $value ) {
		return esc_url_raw( remove_query_arg( '_', strtok( $value, '#' ) ) );
	}

	/**
	 * Retrieve the current client's IP address.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_ip_address() {
		return $_SERVER['REMOTE_ADDR'];
	}
}
