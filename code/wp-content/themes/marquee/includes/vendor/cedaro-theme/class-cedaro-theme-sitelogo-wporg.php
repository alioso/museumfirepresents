<?php
/**
 * Site logo support.
 *
 * Self-hosted/fallback support for site logos in environments where the
 * Jetpack isn't active.
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
class Cedaro_Theme_SiteLogo_WPorg extends Cedaro_Theme_SiteLogo {
	/*
	 * Public API methods.
	 */

	/**
	 * Attach theme hooks for the site logo feature.
	 *
	 * The Jetpack site logo doesn't load until init:10, so we can't check for
	 * jetpack_the_site_logo() until then and the check needs to occur before
	 * wp_loaded:10 to determine if our functionality should be attached instead.
	 *
	 * @since 3.0.0
	 *
	 * @see Cedaro_Theme_SiteLogo::wp_loaded()
	 */
	public function add_support() {
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ), 0 );
		return $this;
	}

	/**
	 * Retrieve the logo HTML.
	 *
	 * Returns early if the current theme doesn't support 'site-logo'.
	 *
	 * Priority is given to the Jetpack site logo, otherwise the output for
	 * self-hosted installations mimics that plugin.
	 *
	 * Either a logo or a placeholder will always be printed in the Customizer
	 * for instant feedback via the post message transport.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function html() {
		if ( ! current_theme_supports( 'site-logo' ) ) {
			return '';
		}

		$html     = parent::html();
		$logo_url = $this->get_logo_data( 'url' );

		if ( empty( $html ) && ( ! empty( $logo_url ) || $this->theme->is_customizer_preview() ) ) {
			$image_url = empty( $logo_url ) ? 'data:image/gif;base64,R0lGODlhAQABAAAAADs=' : esc_url( set_url_scheme( $logo_url ) );

			$html = sprintf(
				'<a href="%1$s" class="site-logo-link site-logo-anchor"><img src="%2$s" alt="" class="site-logo" data-size="full"%3$s></a>',
				esc_url( home_url( '/' ) ),
				$image_url,
				empty( $logo_url ) ? ' style="display: none"' : ''
			);
		}

		return $html;
	}

	/*
	 * Hook callbacks.
	 */

	/**
	 * Set up site logo support.
	 *
	 * If the current theme doesn't support site logos or a site logo plugin
	 * exists, don't worry about setting anything up.
	 *
	 * @since 2.0.0
	 */
	public function wp_loaded() {
		// Bail if the current theme doesn't support site logos
		// Or if a plugin that provides site logo support is available.
		if ( ! current_theme_supports( 'site-logo' ) || function_exists( 'jetpack_the_site_logo' ) ) {
			return;
		}

		add_action( 'wp_head', array( $this, 'header_text_styles' ) );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_action( 'customize_preview_init', array( $this, 'enqueue_customizer_preview_assets' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_customizer_controls_assets' ) );
		add_action( 'customize_controls_print_styles', array( $this, 'customize_controls_print_styles' ) );
	}

	/**
	 * Print CSS styles to respect the header text visibility setting.
	 *
	 * @since 2.0.0
	 */
	public function header_text_styles() {
		// Bail if our theme supports custom headers.
		if ( current_theme_supports( 'custom-header' ) ) {
			return;
		}

		// Is Display Header Text unchecked? If so, we need to hide our header text.
		if ( ! get_theme_mod( 'site_logo_header_text', 1 ) ) {
			?>
			<style type="text/css">
			.site-title,
			.site-description {
				clip: rect(1px, 1px, 1px, 1px);
				height: 1px;
				overflow: hidden;
				position: absolute;
				width: 1px;
			}
			</style>
			<?php
		}
	}

	/**
	 * Add a class to the body element if a site logo exists.
	 *
	 * @since 3.0.0
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public function body_class( $classes ) {
		$url = $this->get_logo_data( 'url' );
		if ( ! empty( $url ) ) {
			$classes[] = 'has-site-logo';
		}
		return $classes;
	}

	/**
	 * Register Customizer settings and controls.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function customize_register( $wp_customize ) {
		$wp_customize->get_control( 'blogname' )->priority  = 7;

		// Add a setting to hide header text if the theme doesn't support the feature itself.
		if ( ! current_theme_supports( 'custom-header' ) ) {
			$wp_customize->add_setting( 'site_logo_header_text', array(
				'default'           => 1,
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'transport'         => 'postMessage',
			) );

			$wp_customize->add_control( 'cedaro_site_logo_header_text', array(
				'label'    => esc_html__( 'Display Site Title and Tagline', 'marquee' ),
				'section'  => 'title_tagline',
				'settings' => 'site_logo_header_text',
				'type'     => 'checkbox',
				'priority' => 12,
			) );
		}

		// Add a setting to sync the logo attachment properties.
		$wp_customize->add_setting( 'site_logo', array(
			'capability'        => 'manage_options',
			'sanitize_callback' => array( $this, 'sanitize_logo' ),
			'transport'         => 'postMessage',
			'type'              => 'option',
			'default'           => array(
				'id'  => 0,
				'url' => '',
			),
		) );

		// Add the setting for the image control.
		$wp_customize->add_setting( 'cedaro_site_logo_url', array(
			'capability'        => 'manage_options',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'cedaro_site_logo', array(
			'label'    => esc_html__( 'Site Logo', 'marquee' ),
			'section'  => 'title_tagline',
			'settings' => 'cedaro_site_logo_url',
			'priority' => 4,
		) ) );
	}

	/**
	 * Enqueue assets when previewing the site in the Customizer.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_customizer_preview_assets() {
		wp_enqueue_script(
			'cedaro-theme-customize-preview',
			$this->theme->get_library_uri( 'assets/js/customize-preview.js' ),
			array( 'customize-preview' ),
			'1.0.0',
			true
		);
	}

	/**
	 * Enqueue assets for handling custom controls.
	 *
	 * Syncs an image control with a setting in the format that the Jetpack site
	 * logo plugin expects.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_customizer_controls_assets() {
		wp_enqueue_script(
			'cedaro-theme-customize-controls-site-logo',
			$this->theme->get_library_uri( 'assets/js/customize-controls-site-logo.js' ),
			array( 'customize-controls' ),
			'1.0.0',
			true
		);
	}

	/**
	 * Add a background color to logo previews to make white logos visible.
	 *
	 * @since 3.1.1
	 */
	public function customize_controls_print_styles() {
		?>
		<style type="text/css">
		#customize-control-cedaro_site_logo .container {
			background: none repeat scroll 0 0 #eee;
			border-width: 0;
			box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05) inset;
		}
		</style>
		<?php
	}

	/**
	 * Sanitize a Customizer checkbox setting.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value Value to sanitize.
	 * @return string 1 if checked, empty string if not checked.
	 */
	public function sanitize_checkbox( $value ) {
		return empty( $value ) || ! $value ? '' : '1';
	}

	/**
	 * Sanitize the logo setting.
	 *
	 * @sine 2.0.0
	 *
	 * @param array $value Value to sanitize.
	 * @return array
	 */
	public function sanitize_logo( $value ) {
		$defaults = array(
			'id'    => 0,
			'sizes' => array(),
			'url'   => '',
		);

		$value = wp_parse_args( (array) $value, $defaults );
		$value = array_intersect_key( $value, $defaults );

		$value['id']  = absint( $value['id'] );
		$value['url'] = esc_url_raw( $value['url'] );

		return $value;
	}
}
