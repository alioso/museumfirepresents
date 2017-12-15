<?php
/**
 * Customizer integration.
 *
 * @package Marquee
 * @since 1.0.0
 */

/**
 * Register and update Customizer settings.
 *
 * @since 1.0.0
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function marquee_customize_register( $wp_customize ) {
	$color_scheme = marquee_get_color_scheme();

	$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	$wp_customize->add_section( 'theme_options', array(
		'title'    => esc_html__( 'Theme Options', 'marquee' ),
		'priority' => 120,
	) );

	$wp_customize->add_setting( 'marquee_background_color', array(
		'default'           => $color_scheme['background_color'],
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'marquee_background_color', array(
		'label'    => esc_html__( 'Background Color', 'marquee' ),
		'section'  => 'colors',
		'settings' => 'marquee_background_color',
	) ) );

	$wp_customize->add_setting( 'background_overlay_opacity', array(
		'capability'        => 'edit_theme_options',
		'default'           => 50,
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'marquee_background_overlay_opacity', array(
		'label'       => esc_html__( 'Background Overlay Opacity', 'marquee' ),
		'description' => esc_html__( 'Applies background color over the image.', 'marquee' ),
		'section'     => 'colors',
		'settings'    => 'background_overlay_opacity',
		'type'        => 'range',
		'priority'    => 15,
		'input_attrs' => array(
			'min'   => 0,
			'max'   => 100,
			'step'  => 1,
		),
	) );
}
add_action( 'customize_register', 'marquee_customize_register' );

/**
 * Bind JavaScript handlers to make the Customizer preview reload changes
 * asynchronously.
 *
 * @since 1.0.0
 */
function marquee_customize_preview_assets() {
	wp_enqueue_script(
		'marquee-customize-preview',
		get_template_directory_uri() . '/assets/js/customize-preview.js',
		array( 'customize-preview', 'underscore', 'wp-util' ),
		'20150213',
		true
	);
}
add_action( 'customize_preview_init', 'marquee_customize_preview_assets' );

/**
 * Register default color scheme values.
 *
 * @since 1.0.0
 */
function marquee_get_color_scheme() {
	return array(
		'background_color'           => '#000000',
		'background_overlay_opacity' => 50,
		'header_text_color'          => '#ffffff',
	);
}

/**
 * Print an Underscore template with CSS to generate based on options
 * selected in the Customizer.
 *
 * @since 1.0.0
 */
function marquee_customize_styles_template() {
	if ( ! is_customize_preview() ) {
		return;
	}

	$colors = array(
		'background_color'           => '{{ data.backgroundColor }}',
		'background_overlay_opacity' => '{{ data.backgroundOverlayOpacity }}',
		'header_text_color'          => '{{ data.headerTextColor }}',
	);

	printf(
		'<script type="text/html" id="tmpl-marquee-customizer-styles">%s</script>',
		marquee_get_custom_css( $colors )
	);
}
add_action( 'wp_footer', 'marquee_customize_styles_template' );

/**
 * Enqueue front-end CSS for custom colors.
 *
 * @since 1.0.0
 *
 * @see WP_Styles::print_inline_style()
 */
function marquee_customize_add_inline_css() {
	$css = preg_replace( '/[\s]{2,}/', '', marquee_get_custom_css() );
	printf( "<style id='marquee-custom-css' type='text/css'>\n%s\n</style>\n", $css ); // WPCS: XSS OK.
}
add_action( 'wp_head', 'marquee_customize_add_inline_css', 11 );

/**
 * Retrieve CSS rules for implementing custom colors.
 *
 * @since 1.0.0
 *
 * @param array $colors Optional. An array of colors.
 * @return string
 */
function marquee_get_custom_css( $colors = array() ) {
	$css      = '';
	$defaults = marquee_get_color_scheme();

	$colors = wp_parse_args( $colors, array(
		'background_color'           => get_theme_mod( 'marquee_background_color', $defaults['background_color'] ),
		'background_overlay_opacity' => get_theme_mod( 'background_overlay_opacity', $defaults['background_overlay_opacity'] ),
		'header_text_color'          => '#' . get_header_textcolor(),
	) );

	if ( $colors['background_color'] !== $defaults['background_color'] || is_customize_preview() ) {
		$css .= marquee_get_background_color_css( $colors['background_color'] );
	}

	$opacity = $colors['background_overlay_opacity'];
	if ( $opacity !== $defaults['background_overlay_opacity'] || is_customize_preview() ) {
		$opacity = is_numeric( $opacity ) ? $opacity / 100 : $opacity;
		$css .= marquee_get_background_overlay_opacity_css( $opacity );
	}

	if ( $colors['header_text_color'] !== $defaults['header_text_color'] || is_customize_preview() ) {
		$css .= marquee_get_header_text_color_css( $colors['header_text_color'] );
	}

	if ( ! display_header_text() && ! is_customize_preview() ) {
		$css .= marquee_get_hide_header_text_css();
	}

	$image_url = get_header_image();
	if ( ! empty( $image_url ) || is_customize_preview() ) {
		$css .= sprintf(
			'.site-header { background-image: url("%s");}',
			esc_url( $image_url )
		);
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
function marquee_get_background_color_css( $color ) {
	$css = <<<CSS
	.site-header:before {
		background-color: {$color};
	}
CSS;

	return $css;
}

/**
 * Get background overlay opacity CSS.
 *
 * @since 1.0.0
 *
 * @param  string $opacity
 * @return string
 */
function marquee_get_background_overlay_opacity_css( $opacity ) {
	$css = <<<CSS
	.site-header:before {
		opacity: {$opacity};
	}
CSS;

	return $css;
}

/**
 * Get header text color CSS
 *
 * @since 1.0.0
 *
 * @param  string $color Hex color.
 * @return string
 */
function marquee_get_header_text_color_css( $color ) {
	$css = <<<CSS
	.site-current-track-details,
	.site-description,
	.site-navigation-toggle,
	.site-navigation-toggle:focus,
	.site-navigation-toggle:hover,
	.site-navigation-toggle.is-open,
	.site-play-pause-button,
	.site-play-pause-button:focus,
	.site-play-pause-button:hover,
	.site-player-toggle,
	.site-player-toggle:focus,
	.site-player-toggle:hover,
	.site-title,
	.site-title a {
		color: {$color};
	}

	@media (min-width: 1024px) {
		.site-navigation-toggle.is-open,
		.site-navigation-toggle.is-open:focus,
		.site-navigation-toggle.is-open:hover {
			color: #fff;
		}
	}
CSS;

	return $css;
}

/**
 * Get header text display CSS.
 *
 * @since 1.0.0
 *
 * @return string
 */
function marquee_get_hide_header_text_css() {
	$css = <<<CSS
	.site-title,
	.site-description {
		clip: rect(1px 1px 1px 1px);
		height: 1px;
		position: absolute !important;
		overflow: hidden;
		width: 1px;
	}
CSS;

	return $css;
}

/**
 * Sanitization callback for checkbox controls in the Customizer.
 *
 * @since 1.0.0
 *
 * @param string $value Setting value.
 * @return string 1 if checked, empty string otherwise.
 */
function marquee_customize_sanitize_checkbox( $value ) {
	return empty( $value ) || ! $value ? '' : '1';
}
