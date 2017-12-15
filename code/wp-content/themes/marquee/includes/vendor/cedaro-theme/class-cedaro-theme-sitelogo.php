<?php
/**
 * Site logo support.
 *
 * @since 2.0.0
 *
 * @package Cedaro\Theme
 * @copyright Copyright (c) 2014, Cedaro
 * @license GPL-2.0+
 */

/**
 * Class for the site logo feature.
 *
 * @package Cedaro\Theme
 * @since 2.0.0
 */
class Cedaro_Theme_SiteLogo {
	/**
	 * The theme object.
	 *
	 * @since 2.0.0
	 * @var Cedaro_Theme
	 */
	protected $theme;

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 *
	 * @param Cedaro_Theme $theme Cedaro theme instance.
	 */
	public function __construct( Cedaro_Theme $theme ) {
		$this->theme = $theme;
	}

	/*
	 * Public API methods.
	 */

	/**
	 * Register theme hooks for the site logo feature.
	 *
	 * @since 3.0.0
	 *
	 * @see Cedaro_Theme_SiteLogo::wp_loaded()
	 */
	public function add_support() {
		return $this;
	}

	/**
	 * Retrieve the logo attachment ID.
	 *
	 * @since 2.0.0
	 *
	 * @return int Image attachment ID or 0.
	 */
	public function get_id() {
		return $this->get_logo_data( 'id' );
	}

	/**
	 * Retrieve the logo image URL.
	 *
	 * @since 2.0.0
	 *
	 * @return string Image URL or an empty string.
	 */
	public function get_url() {
		return $this->get_logo_data( 'url' );
	}

	/**
	 * Retrieve the logo HTML.
	 *
	 * Returns early if the current theme doesn't support 'site-logo'.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function html() {
		if ( ! current_theme_supports( 'site-logo' ) ) {
			return '';
		}

		$html = '';

		// Jetpack site logo support.
		if ( function_exists( 'jetpack_the_site_logo' ) ) {
			ob_start();
			jetpack_the_site_logo();
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Retrieve logo data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Optional. Logo attachment property to return.
	 *                    Defaults to all.
	 * @return mixed Defaults to an object, otherwise the property requested.
	 */
	protected function get_logo_data( $key = '' ) {
		$logo = get_option( 'site_logo', array(
			'id'  => 0,
			'url' => '',
		) );

		return empty( $key ) ? $logo : $logo[ $key ];
	}
}
