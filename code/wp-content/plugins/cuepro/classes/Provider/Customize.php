<?php
/**
 * Customizer provider.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.1.0
 */

/**
 * Customizer provider class.
 *
 * @package CuePro
 * @since   1.1.0
 */
class CuePro_Provider_Customize extends CuePro_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 1.1.0
	 */
	public function register_hooks() {
		add_action( 'customize_register', array( $this, 'customize_register' ) );
	}

	/**
	 * Add a Customizer section for selecting playlists for registered players.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	public function customize_register( $wp_customize ) {
		$wp_customize->add_setting( 'cuepro_force_downloads', array(
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'cuepro_force_downloads', array(
			'label'       => esc_html__( 'Force track downloads', 'cuepro' ),
			'description' => esc_html__( 'Encourage browsers to download linked files instead of playing them.', 'cuepro' ),
			'section'     => 'cue',
			'settings'    => 'cuepro_force_downloads',
			'type'        => 'checkbox',
			'priority'    => 105,
		) );

		$wp_customize->add_setting( 'cuepro_disable_embeds', array(
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'cuepro_disable_embeds', array(
			'label'       => esc_html__( "Don't allow playlists to be embedded", 'cuepro' ),
			'section'     => 'cue',
			'settings'    => 'cuepro_disable_embeds',
			'type'        => 'checkbox',
			'priority'    => 105,
		) );
	}

	/**
	 * Sanitization callback for checkbox controls in the Customizer.
	 *
	 * @since 1.1.0
	 *
	 * @param string $value Setting value.
	 * @return string 1 if checked, empty string otherwise.
	 */
	public function sanitize_checkbox( $value ) {
		return empty( $value ) || ! $value ? '' : '1';
	}
}
