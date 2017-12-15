<?php
/**
 * Assets provider.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Assets provider class.
 *
 * @package CuePro
 * @since   1.0.0
 */
class CuePro_Provider_Assets extends CuePro_AbstractProvider {
	/**
	 * File suffix for minified assets.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $suffix = '.min';

	/**
	 * Constructor method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$this->suffix = '';
		}
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {
		add_action( 'wp_enqueue_scripts',       array( $this, 'register_assets' ), 1 );
		add_action( 'admin_enqueue_scripts',    array( $this, 'register_assets' ), 1 );
		add_action( 'wp_enqueue_scripts',       array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer',                array( $this, 'maybe_enqueue_assets' ), 1 );
		add_filter( 'cue_parse_shortcode_head', array( $this, 'filter_mce_view_styles' ) );

		add_action( 'cue_embed_enqueue_scripts', array( $this, 'register_assets' ), 1 );
		add_action( 'cue_embed_head',            array( $this, 'print_embed_assets' ) );
		add_action( 'cue_embed_footer',          array( $this, 'maybe_enqueue_assets' ), 1 );
		add_action( 'cue_embed_footer',          array( $this, 'print_late_embed_assets' ), 20 );
	}

	/**
	 * Register frontend scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public function register_assets() {
		wp_register_script(
			'chartjs',
			$this->plugin->get_url( 'assets/js/vendor/chart.min.js' ),
			array(),
			'1.0.2',
			true
		);

		wp_register_script(
			'cuepro',
			$this->plugin->get_url( 'assets/js/cuepro' . $this->suffix . '.js' ),
			array( 'cue' ),
			'1.1.0',
			true
		);

		wp_localize_script( 'cuepro', '_cueproSettings', array(
			'disableEmbeds' => (bool) get_option( 'cuepro_disable_embeds', false ),
			'l10n'          => array(
				'popup' => esc_html__( 'Popup', 'cuepro' ),
				'share' => esc_html__( 'Share', 'cuepro' ),
			),
		) );

		wp_register_style(
			'cuepro',
			$this->plugin->get_url( 'assets/css/cuepro.css' ),
			array(),
			'1.1.0'
		);
	}

	/**
	 * Enqueue front end assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		wp_enqueue_style( 'cuepro' );
	}

	/**
	 * Enqueue conditional assets.
	 *
	 * @since 1.0.0
	 */
	public function maybe_enqueue_assets() {
		// Enqueue Cue Pro script when the Cue script is enqueued.
		if ( wp_script_is( 'cue', 'enqueued' ) ) {
			wp_enqueue_script( 'cuepro' );
		}

		// Enqueue the insights script when MediaElement.js is enqueued.
		if ( ! wp_script_is( 'mediaelement', 'enqueued' ) ) {
			return;
		}

		$dependencies = array( 'mediaelement', 'underscore' );

		if ( wp_script_is( 'jquery-cue', 'enqueued' ) ) {
			$dependencies[] = 'jquery-cue';
		}

		if ( wp_script_is( 'wp-playlist', 'enqueued' ) ) {
			$dependencies[] = 'wp-playlist';
		}

		wp_enqueue_script(
			'cuepro-insights',
			$this->plugin->get_url( 'assets/js/insights' . $this->suffix . '.js' ),
			$dependencies,
			'1.0.0',
			true
		);

		wp_localize_script( 'cuepro-insights', '_cueproInsights', array(
			'restUrl'  => esc_url_raw( get_rest_url() ),
			'routeUrl' => esc_url( rest_url( '/cue/v1/stats' ) ),
		) );
	}

	/**
	 * Append Cue Pro styles to the MCE view.
	 *
	 * @since 1.1.0
	 *
	 * @param string $head Iframe head output.
	 * @return string
	 */
	public function filter_mce_view_styles( $head ) {
		$head .= '<link rel="stylesheet" href="' . $this->plugin->get_url( 'assets/css/cuepro.css' ) . '">';
		return $head;
	}

	/**
	 * Print scripts and styles in the embed template.
	 *
	 * @since 1.1.0
	 */
	public function print_embed_assets() {
		wp_print_styles( array( 'cue', 'cuepro' ) );
	}

	/**
	 * Print scripts and styles in the footer of the embed template.
	 *
	 * @since 1.1.0
	 */
	public function print_late_embed_assets() {
		wp_print_scripts( array( 'cue', 'cuepro', 'cue-insights' ) );
	}
}
