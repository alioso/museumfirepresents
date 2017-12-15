<?php
/**
 * Customizer manager.
 *
 * @package   Billboard
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Customizer manager class.
 *
 * @package Billboard
 * @since   1.0.0
 */
class Billboard_Provider_Customize extends Billboard_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {
		add_action( 'customize_register',                 array( $this, 'register_panel' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_customizer_controls_assets' ) );

		if ( is_customize_preview() || $this->plugin->is_active() ) {
			add_action( 'wp_footer', array( $this, 'print_styles_template' ) );
			add_action( 'wp_head',   array( $this, 'print_css' ), 11 );
		}
	}

	/**
	 * Register the Billboard panel.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customize manager.
	 */
	public function register_panel( $wp_customize ) {
		$wp_customize->add_panel( 'billboard', array(
			'title'    => esc_html__( 'Billboard', 'billboard' ),
			'priority' => 1,
		) );

		$this->register_identity_section( $wp_customize );
		$this->register_content_section( $wp_customize );
		$this->register_design_section( $wp_customize );
		$this->register_settings_section( $wp_customize );
	}

	/**
	 * Register the Identity section.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customize manager.
	 */
	protected function register_identity_section( $wp_customize ) {
		$wp_customize->add_section( 'billboard_identity', array(
			'title'    => esc_html__( 'Identity', 'billboard' ),
			'panel'    => 'billboard',
			'priority' => 10,
		) );

		$wp_customize->add_setting( 'billboard[logo]', array(
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'absint',
			'transport'         => isset( $wp_customize->selective_refresh ) ? 'postMessage' : 'refresh',
			'type'              => 'option',
		) );

		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'billboard_logo', array(
			'label'         => esc_html__( 'Logo', 'billboard' ),
			'section'       => 'billboard_identity',
			'settings'      => 'billboard[logo]',
			'priority'      => 4,
			'mime_type'     => 'image',
			'button_labels' => array(
				'select'       => __( 'Select logo', 'billboard' ),
				'change'       => __( 'Change logo', 'billboard' ),
				'remove'       => __( 'Remove', 'billboard' ),
				'default'      => __( 'Default', 'billboard' ),
				'placeholder'  => __( 'No logo selected', 'billboard' ),
				'frame_title'  => __( 'Select logo', 'billboard' ),
				'frame_button' => __( 'Choose logo', 'billboard' ),
			),
		) ) );

		$wp_customize->add_setting( 'billboard[title]', array(
			'default'           => get_bloginfo( 'name' ),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'billboard_title', array(
			'label'    => esc_html__( 'Title', 'billboard' ),
			'section'  => 'billboard_identity',
			'settings' => 'billboard[title]',
		) );

		$wp_customize->add_setting( 'billboard[tagline]', array(
			'default'           => get_bloginfo( 'description' ),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'billboard_tagline', array(
			'label'    => esc_html__( 'Tagline', 'billboard' ),
			'section'  => 'billboard_identity',
			'settings' => 'billboard[tagline]',
		) );

		$wp_customize->add_setting( 'billboard[display_header_text]', array(
			'default'           => 1,
			'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'billboard_display_header_text', array(
			'label'    => esc_html__( 'Display the title and tagline', 'billboard' ),
			'section'  => 'billboard_identity',
			'settings' => 'billboard[display_header_text]',
			'type'     => 'checkbox',
		) );

		if ( ! isset( $wp_customize->selective_refresh ) ) {
			return;
		}

		$wp_customize->selective_refresh->add_partial( 'billboard_logo', array(
			'selector'            => '.billboard-logo',
			'settings'            => array( 'billboard[logo]' ),
			'render_callback'     => 'billboard_logo',
			'container_inclusive' => true,
		) );
	}

	/**
	 * Register the Content section.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customize manager.
	 */
	protected function register_content_section( $wp_customize ) {
		$wp_customize->add_section( 'billboard_content', array(
			'title'    => esc_html__( 'Content', 'billboard' ),
			'panel'    => 'billboard',
			'priority' => 20,
		) );

		$wp_customize->add_setting( 'billboard[layout]', array(
			'sanitize_callback' => 'sanitize_key',
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'billboard_layout', array(
			'label'       => esc_html__( 'Layout', 'billboard' ),
			'description' => esc_html__( '', 'billboard' ),
			'priority'    => 10,
			'section'     => 'billboard_content',
			'settings'    => 'billboard[layout]',
			'type'        => 'select',
			'choices'     => array(
				''             => esc_html__( 'Signature', 'billboard' ),
				'focus'        => esc_html__( 'Focus', 'billboard' ),
				'left-top'     => esc_html__( 'Left Top', 'billboard' ),
				'left-middle'  => esc_html__( 'Left Middle', 'billboard' ),
				'left-bottom'  => esc_html__( 'Left Bottom', 'billboard' ),
				'right-middle' => esc_html__( 'Right Middle', 'billboard' ),
				'right-bottom' => esc_html__( 'Right Bottom', 'billboard' ),
			),
		) );

		// Shortcodes in the content may load additional styles and scripts, so
		// the transport needs to be refresh.
		$wp_customize->add_setting( 'billboard[content]', array(
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'wp_kses_post',
			'transport'         => 'refresh',
			'type'              => 'option',
		) );

		$wp_customize->add_control( new Billboard_Customize_Control_Editor( $wp_customize, 'billboard_content', array(
			'label'         => esc_html__( 'Content', 'billboard' ),
			'priority'      => 15,
			'section'       => 'billboard_content',
			'settings'      => 'billboard[content]',
			'stylesheets'   => array(
				$this->plugin->get_url( 'themes/billboard/editor-style.css' ),
				// @todo Add a fonts URL?
			),
		) ) );

		$menus = wp_list_pluck( wp_get_nav_menus(), 'name', 'term_id' );
		$menus = array( 0 => '' ) + $menus;

		$wp_customize->add_setting( 'billboard[social_menu]', array(
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'absint',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'billboard_social_menu', array(
			'label'       => esc_html__( 'Social Links Menu', 'billboard' ),
			'priority'    => 30,
			'section'     => 'billboard_content',
			'settings'    => 'billboard[social_menu]',
			'type'        => 'select',
			'choices'     => $menus,
		) );
	}

	/**
	 * Register the Design section.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customize manager.
	 */
	protected function register_design_section( $wp_customize ) {
		$wp_customize->add_section( 'billboard_design', array(
			'title'    => esc_html__( 'Design', 'billboard' ),
			'panel'    => 'billboard',
			'priority' => 30,
		) );

		$wp_customize->add_setting( 'billboard[logo_width]', array(
			'capability'        => 'edit_theme_options',
			'default'           => '',
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'billboard_logo_width', array(
			'label'       => esc_html__( 'Logo Width', 'billboard' ),
			'description' => esc_html( 'Set the logo width in pixels. It will automatically scale down on smaller devices.' ),
			'section'     => 'billboard_design',
			'settings'    => 'billboard[logo_width]',
		) );

		$wp_customize->add_setting( 'billboard[text_scheme]', array(
			'default'           => 'light',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_key',
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_setting( 'billboard[background_color]', array(
			'default'           => '#000000',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'billboard_background_color', array(
			'label'    => esc_html__( 'Background Color', 'billboard' ),
			'section'  => 'billboard_design',
			'settings' => 'billboard[background_color]',
		) ) );

		$wp_customize->add_setting( 'billboard[background_image]', array(
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'billboard_background_image', array(
			'label'    => esc_html__( 'Background Image', 'billboard' ),
			'section'  => 'billboard_design',
			'settings' => 'billboard[background_image]',
		) ) );

		$wp_customize->add_setting( 'billboard[background_overlay_opacity]', array(
			'capability'        => 'edit_theme_options',
			'default'           => 50,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'billboard_background_overlay_opacity', array(
			'label'       => esc_html__( 'Background Overlay Opacity', 'billboard' ),
			'description' => esc_html__( 'Applies background color over the image.', 'billboard' ),
			'section'     => 'billboard_design',
			'settings'    => 'billboard[background_overlay_opacity]',
			'type'        => 'range',
			'input_attrs' => array(
				'min'   => 0,
				'max'   => 100,
				'step'  => 1,
			),
		) );

		$wp_customize->add_setting( 'billboard[background_video]', array(
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'absint',
			'type'              => 'option',
			'validate_callback' => array( $this, 'validate_background_video' ),
		) );

		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'billboard_background_video', array(
			'label'     => esc_html__( 'Background Video', 'billboard' ),
			'mime_type' => 'video',
			'section'   => 'billboard_design',
			'settings'  => 'billboard[background_video]',
			'button_labels'  => array(
				'select'       => esc_html__( 'Select Video', 'billboard' ),
				'change'       => esc_html__( 'Change Video', 'billboard' ),
				'placeholder'  => esc_html__( 'No video selected', 'billboard' ),
				'frame_title'  => esc_html__( 'Select Video', 'billboard' ),
				'frame_button' => esc_html__( 'Choose Video', 'billboard' ),
			),
		) ) );

		if ( ! isset( $wp_customize->selective_refresh ) ) {
			return;
		}

		$wp_customize->get_setting( 'billboard[background_video]' )->transport = 'postMessage';

		$wp_customize->selective_refresh->add_partial( 'billboard_background_video', array(
			'selector'        => '.billboard-background',
			'settings'        => array( 'billboard[background_video]' ),
			'render_callback' => 'billboard_background_video',
		) );
	}

	/**
	 * Register the Settings section.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customize manager.
	 */
	protected function register_settings_section( $wp_customize ) {
		$wp_customize->add_section( 'billboard_settings', array(
			'title'    => esc_html__( 'Settings', 'billboard' ),
			'panel'    => 'billboard',
			'priority' => 100,
		) );

		$wp_customize->add_setting( 'billboard[mode]', array(
			'sanitize_callback' => 'sanitize_key',
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'billboard_mode', array(
			'label'    => esc_html__( 'Who should see the billboard?', 'billboard' ),
			'section'  => 'billboard_settings',
			'settings' => 'billboard[mode]',
			'type'     => 'select',
			'choices'  => array(
				''         => esc_html__( 'No one', 'billboard' ),
				'visitors' => esc_html__( 'Visitors only', 'billboard' ),
				'everyone' => esc_html__( 'Everyone', 'billboard' ),
			),
		) );

		$wp_customize->add_setting( 'billboard[enable_maintenance_mode]', array(
			'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'billboard_maintenance_mode', array(
			'label'       => esc_html__( 'Enable maintenance mode', 'billboard' ),
			'description' => esc_html__( 'Sends a 503 HTTP header to prevent search engines from indexing temporary content during brief maintenance periods.', 'billboard' ),
			'section'     => 'billboard_settings',
			'settings'    => 'billboard[enable_maintenance_mode]',
			'type'        => 'checkbox',
		) );

		$wp_customize->add_setting( 'billboard[restrict_feeds]', array(
			'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'billboard_restrict_feeds', array(
			'label'    => esc_html__( 'Limit feed access to logged-in users', 'billboard' ),
			'section'  => 'billboard_settings',
			'settings' => 'billboard[restrict_feeds]',
			'type'     => 'checkbox',
		) );

		$wp_customize->add_setting( 'billboard[allowed_ips]', array(
			'sanitize_callback' => array( $this, 'sanitize_ip_list' ),
			'transport'         => 'postMessage',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'billboard_allowed_ips', array(
			'label'       => esc_html__( 'IP Addresses', 'billboard' ),
			'description' => esc_html__( 'Enter IP addresses that should skip the coming soon or maintenance page (one per line).', 'billboard' ),
			'section'     => 'billboard_settings',
			'settings'    => 'billboard[allowed_ips]',
			'type'        => 'textarea',
		) );
	}

	/**
	 * Enqueue assets for the Customizer.
	 *
	 * @since 1.0.0
	 */
	function enqueue_customizer_controls_assets() {
		wp_enqueue_script(
			'billboard-customize-controls',
			$this->plugin->get_url( 'assets/js/customize-controls.js' ),
			array( 'customize-controls', 'underscore' ),
			'20150325',
			true
		);

		wp_localize_script( 'billboard-customize-controls', '_billboardControlsSettings', array(
			'previewUrl' => esc_url_raw( add_query_arg( 'billboard', 'preview', home_url( '/' ) ) ),
		) );

		wp_enqueue_style(
			'billboard-customize-controls-editor',
			$this->plugin->get_url( 'assets/css/customize-controls-editor.css' )
		);
	}

	/**
	 * Enqueue front-end CSS for custom styles.
	 *
	 * @since 1.0.0
	 *
	 * @see WP_Styles::print_inline_style()
	 */
	public function print_css() {
		$css = preg_replace( '/[\s]{2,}/', '', $this->get_custom_css() );
		printf( "<style id='billboard-custom-css' type='text/css'>\n%s\n</style>\n", $css );
	}

	/**
	 * Print an Underscore template with CSS to generate based on options
	 * selected in the Customizer.
	 *
	 * @since 1.0.0
	 */
	public function print_styles_template() {
		if ( ! is_customize_preview() ) {
			return;
		}

		$values = array(
			'background_color'           => '{{ data.backgroundColor }}',
			'background_image'           => '{{ data.backgroundImage }}',
			'background_overlay_opacity' => '{{ data.backgroundOverlayOpacity }}',
			'logo_width'                 => '{{ data.logoWidth }}',
		);

		printf(
			'<script type="text/html" id="tmpl-billboard-customizer-styles">%s</script>',
			$this->get_custom_css( $values )
		);
	}

	/**
	 * Retrieve CSS rules for implementing custom colors.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $values Optional. An array of CSS settings.
	 * @return string
	 */
	protected function get_custom_css( $values = array() ) {
		$values = wp_parse_args( $values, $this->plugin->get_settings() );

		$css  = '';
		$css .= $this->get_background_color_css( $values['background_color'] );

		if ( ! empty( $values['background_image'] ) ) {
			$css .= $this->get_background_image_css( $values['background_image'] );
		}

		$opacity = $values['background_overlay_opacity'];
		$opacity = is_numeric( $opacity ) ? $opacity / 100 : $opacity;
		$css .= $this->get_background_overlay_opacity_css( $opacity );

		if ( ! $this->plugin->get_setting( 'display_header_text' ) ) {
			$css .= $this->get_header_text_css();
		}

		if ( ! empty( $values['logo_width'] ) ) {
			$css .= $this->get_logo_css( $values['logo_width'] );
		}

		return $css;
	}

	/**
	 * Get background color CSS.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $color Hex color.
	 * @return string
	 */
	protected function get_background_color_css( $color ) {
		$css = <<<CSS
body,
body.custom-background,
.billboard-background:before {
	background-color: {$color};
}
CSS;

		return $css;
	}

	/**
	 * Get background image CSS.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $url Background image URL.
	 * @return string
	 */
	protected function get_background_image_css( $url ) {
		$css = <<<CSS
.billboard-background {
	background-image: url("{$url}");
}
CSS;

		return $css;
	}

	/**
	 * Get background overlay opacity CSS.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $opacity Background color opacity.
	 * @return string
	 */
	protected function get_background_overlay_opacity_css( $opacity ) {
		$css = <<<CSS
.billboard-background:before {
	opacity: {$opacity};
}
CSS;

		return $css;
	}

	/**
	 * Get CSS to hide the header text.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_header_text_css() {
		$css = <<<CSS
.billboard-title,
.billboard-tagline {
	clip: rect(1px, 1px, 1px, 1px);
	height: 1px;
	overflow: hidden;
	position: absolute;
	width: 1px;
}
CSS;

		return $css;
	}

	/**
	 * Get CSS for the logo.
	 *
	 * @since 1.0.0
	 *
	 * @param  int $width Logo width in pixels.
	 * @return string
	 */
	protected function get_logo_css( $width ) {
		$css = <<<CSS
.billboard-logo {
	width: {$width}px;
}
CSS;

		return $css;
	}

	/**
	 * Sanitize a Customizer checkbox setting.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $value Value to sanitize.
	 * @return string 1 if checked, empty string if not checked.
	 */
	public function sanitize_checkbox( $value ) {
		return empty( $value ) || ! $value ? '' : '1';
	}

	/**
	 * Sanitize a newline-separated list of IP addresses.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $value Newline-separated list of IP addresses.
	 * @return string
	 */
	public function sanitize_ip_list( $value ) {
		$ips = preg_split( '#\n|\r#', $value, -1, PREG_SPLIT_NO_EMPTY );

		$results = array();
		foreach ( $ips as $ip ) {
			$ip = preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip );

			if ( WP_Http::is_ip_address( $ip ) ) {
				$results[] = $ip;
			}
		}

		return implode( "\n", $results );
	}

	/**
	 * Callback for validating the background video value..
	 *
	 * Ensures that the selected video is less than 8MB.
	 *
	 * @since 1.1.0
	 *
	 * @param  WP_Error $validity Setting validity.
	 * @param  mixed    $value    Setting value.
	 * @return mixed
	 */
	public function validate_background_video( $validity, $value ) {
		$video = get_attached_file( absint( $value ) );

		if ( $video ) {
			$size = filesize( $video );

			// Check whether the size is larger than 8MB.
			if ( 8 < $size / pow( 1024, 2 ) ) {
				$validity->add(
					'size_too_large',
					esc_html__( 'This video file is too large to use as a background video. Try a shorter video or optimize the compression settings and re-upload a file that is less than 8MB.', 'billboard' )
				);
			}

			// Check for .mp4 format, which (assuming h.264 encoding) is the only cross-browser-supported format.
			if ( '.mp4' !== substr( $video, -4 ) ) {
				$message = wp_kses(
					sprintf(
						/* translators: 1: .mp4 */
						__( 'Only %1$s files may be used for background videos. Please convert your video file and try again.', 'billboard' ),
						'<code>.mp4</code>'
					),
					array( 'code' => array() )
				);

				$validity->add( 'invalid_file_type', $message );
			}
		}

		return $validity;
	}
}
