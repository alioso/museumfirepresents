<?php
/**
 * Mono theme.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.2.0
 */

/**
 * Mono theme class.
 *
 * @package CuePro
 * @since   1.2.0
 */
class CuePro_Theme_Mono extends CuePro_AbstractProvider {
	/**
	 * File suffix for minified assets.
	 *
	 * @since 1.2.0
	 * @var string
	 */
	protected $suffix = '.min';

	/**
	 * Constructor method.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$this->suffix = '';
		}
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.2.0
	 */
	public function register_hooks() {
		add_filter( 'cue_themes',                  array( $this, 'register_themes' ) );
		add_filter( 'cue_native_themes',           array( $this, 'register_native_themes' ) );
		add_filter( 'cue_playlist_thumbnail_size', array( $this, 'filter_thumbnail_size' ), 10, 3 );
		add_filter( 'shortcode_atts_audio',        array( $this, 'filter_audio_shortcode_attributes' ) );

		add_action( 'wp_enqueue_scripts',          array( $this, 'register_assets' ), 1 );
		add_action( 'cue_after_playlist',          array( $this, 'maybe_enqueue_assets' ), 10, 3 );
		add_filter( 'cue_parse_shortcode_head',    array( $this, 'filter_mce_view_styles' ) );
		add_action( 'cue_embed_enqueue_scripts',   array( $this, 'register_assets' ), 1 );
		add_action( 'cue_embed_head',              array( $this, 'print_embed_assets' ) );
		add_filter( 'shortcode_atts_audio',        array( $this, 'maybe_enqueue_assets_for_native_audio' ) );
	}

	/**
	 * Register new themes.
	 *
	 * @since 1.2.0
	 *
	 * @param  array $themes Array of themes.
	 * @return array
	 */
	public function register_themes( $themes ) {
		$themes['mono'] = esc_html( 'Mono' );
		$themes['mono-cover'] = esc_html( 'Mono Cover' );
		$themes['mono-banner'] = esc_html( 'Mono Banner' );

		$themes['mono-dark'] = esc_html( 'Mono (Dark)' );
		$themes['mono-cover-dark'] = esc_html( 'Mono Cover (Dark)' );
		$themes['mono-banner-dark'] = esc_html( 'Mono Banner (Dark)' );

		return $themes;
	}

	/**
	 * Register themes for the native audio shortcode.
	 *
	 * @since 1.2.0
	 *
	 * @param  array $themes Array of themes.
	 * @return array
	 */
	public function register_native_themes( $themes ) {
		$themes['mono'] = esc_html( 'Mono' );
		$themes['mono-dark'] = esc_html( 'Mono (Dark)' );
		return $themes;
	}

	/**
	 * Filter the size of the background image for the banner variation.
	 *
	 * @since 1.2.0
	 *
	 * @param  array   $size     Image size.
	 * @param  WP_Post $playlist Playlist post object.
	 * @param  array   $args     Playlist settings.
	 * @return array
	 */
	public function filter_thumbnail_size( $size, $playlist, $args ) {
		if ( 'mono-banner' === $args['theme'] ) {
			$size = array( 600, 800 );
		}

		return $size;
	}

	/**
	 * Filter the audio shortcode attributes when the default theme is set to
	 * a Cue Mono variation.
	 *
	 * @since 1.2.0
	 *
	 * @param  array $atts Shortcode attributes.
	 * @return array
	 */
	public function filter_audio_shortcode_attributes( $atts ) {
		$theme = get_cue_native_theme( $atts );

		if ( ! is_admin() && 0 === strpos( $theme, 'mono' ) ) {
			$atts['style'] = 'width: 100%; height: auto';
		}

		return $atts;
	}

	/**
	 * Register frontend scripts and styles.
	 *
	 * @since 1.2.0
	 */
	public function register_assets() {
		wp_register_style(
			'cuepro-theme-mono',
			$this->plugin->get_url( 'assets/css/themes/mono/style' . $this->suffix . '.css' ),
			array(),
			'1.2.0'
		);

		wp_style_add_data( 'cuepro-theme-mono', 'rtl', 'replace' );
		wp_style_add_data( 'cuepro-theme-mono', 'suffix', $this->suffix );
	}

	/**
	 * Enqueue front end assets.
	 *
	 * @since 1.2.0
	 */
	public function maybe_enqueue_assets( $post, $tracks, $args ) {
		if ( empty( $args['theme'] ) || 0 !== strpos( $args['theme'], 'mono' ) || ! $args['enqueue'] ) {
			return;
		}

		wp_enqueue_style( 'cuepro-theme-mono' );
	}

	/**
	 * Append Cue Pro styles to the MCE view.
	 *
	 * @since 1.2.0
	 *
	 * @param string $head Iframe head output.
	 * @return string
	 */
	public function filter_mce_view_styles( $head ) {
		$href = sprintf(
			$this->plugin->get_url( 'assets/css/themes/mono/style%s.min.css' ),
			is_rtl() ? '-rtl' : ''
		);

		$head .= '<link rel="stylesheet" href="' . $href . '">';

		return $head;
	}

	/**
	 * Print scripts and styles in the embed template.
	 *
	 * @since 1.2.0
	 */
	public function print_embed_assets() {
		wp_print_styles( array( 'cuepro-theme-mono' ) );
	}

	/**
	 * Enqueue assets when the default native player is set to use the Mono theme.
	 *
	 * @since 1.2.0
	 *
	 * @param  array $atts Shortcode attributes.
	 * @return array
	 */
	public function maybe_enqueue_assets_for_native_audio( $atts ) {
		$theme = get_cue_native_theme( $atts );

		if ( 0 === strpos( $theme, 'mono' ) ) {
			wp_enqueue_style( 'cuepro-theme-mono' );
		}

		return $atts;
	}
}
