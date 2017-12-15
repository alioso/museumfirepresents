<?php
/**
 * Insights screen.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Insights screen class.
 *
 * @package CuePro
 * @since   1.0.0
 */
class CuePro_Screen_Insights extends CuePro_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {
		add_filter( 'map_meta_cap',          array( $this, 'map_meta_cap' ), 10, 3 );
		add_action( 'admin_menu',            array( $this, 'add_menu_item' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ), 0 );
	}

	/**
	 * Allow users with the 'manage_options' capability to view reports.
	 *
	 * @since 1.0.1
	 *
	 * @param  array  $caps    Returns the user's actual capabilities.
	 * @param  string $cap     Capability name.
	 * @param  int    $user_id The user ID.
	 * @return array
	 */
	public function map_meta_cap( $caps, $cap, $user_id ) {
		$required_cap = 'manage_options';

		if ( 'view_cuepro_reports' === $cap && user_can( $user_id, $required_cap ) ) {
				$caps = array( $required_cap );
		}

		return $caps;
	}

	/**
	 * Add the settings menu item.
	 *
	 * @since 1.0.0
	 */
	public function add_menu_item() {
		$page_hook = add_submenu_page(
			'edit.php?post_type=cue_playlist',
			esc_html__( 'Insights', 'cuepro' ),
			esc_html__( 'Insights', 'cuepro' ),
			'view_cuepro_reports',
			'cuepro-insights',
			array( $this, 'display_screen' )
		);

		add_action( 'load-' . $page_hook, array( $this, 'load_screen' ) );
	}

	/**
	 * Set up the screen.
	 *
	 * @since 1.0.0
	 */
	public function load_screen() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register assets for the screen.
	 *
	 * @since 1.0.0
	 */
	public function register_assets() {
		wp_register_style( 'cuepro-admin', $this->plugin->get_url( 'admin/assets/css/admin.css' ) );
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'chartjs' );
		wp_enqueue_style( 'cuepro-admin' );
	}

	/**
	 * Display the screen header.
	 *
	 * @since 1.0.0
	 */
	public function display_screen_header() {
		include( $this->plugin->get_path( 'admin/views/screen/header.php' ) );
	}

	/**
	 * Display the screen.
	 *
	 * @since 1.0.0
	 */
	public function display_screen() {
		global $wpdb;

		$interval = 7;
		if ( isset( $_GET['range'] ) ) {
			$interval = absint( $_GET['range'] );
		}

		$metrics = new CuePro_MetricRepository();
		$metrics->set_interval( $interval );

		$screen_url = admin_url( 'edit.php?post_type=cue_playlist&page=cuepro-insights' );

		$this->display_screen_header();
		include( $this->plugin->get_path( 'admin/views/screen/insights.php' ) );
		$this->display_screen_footer();
	}

	/**
	 * Display the screen footer.
	 *
	 * @since 1.0.0
	 */
	public function display_screen_footer() {
		include( $this->plugin->get_path( 'admin/views/screen/footer.php' ) );
	}

	/**
	 * Print the target resource title.
	 *
	 * @since 1.0.0
	 *
	 * @param object $item Database result object.
	 */
	protected function print_target_title( $item ) {
		$title = $item->target_title;
		if ( empty( $title ) ) {
			$title = str_replace(
				array( home_url(), network_site_url() ),
				'',
				$item->target_url
			);
		}

		echo esc_html( $title );
	}
}
