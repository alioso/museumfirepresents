<?php
/**
 * Main plugin.
 *
 * @package   Billboard
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Main plugin class.
 *
 * @package Billboard
 * @since   1.0.0
 */
class Billboard_Plugin extends Billboard_AbstractPlugin {
	/**
	 * Load the plugin.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin() {
		add_filter( 'plugin_action_links_' . $this->get_basename(), array( $this, 'filter_action_links' ) );
		add_action( 'admin_bar_menu',                               array( $this, 'setup_toolbar' ) );
		add_action( 'wp',                                           array( $this, 'restrict_feed_access' ) );

		if ( ! $this->is_active() ) {
			return;
		}

		add_action( 'setup_theme',        array( $this, 'setup_theme' ) );
		add_action( 'send_headers',       array( $this, 'maybe_send_maintenance_headers' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 1 );
		add_filter( 'body_class',         array( $this, 'add_body_classes' ) );

		add_filter( 'billboard_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
		add_filter( 'billboard_content', 'wptexturize' );
		add_filter( 'billboard_content', 'convert_smilies' );
		add_filter( 'billboard_content', 'wpautop' );
		add_filter( 'billboard_content', 'shortcode_unautop' );
		add_filter( 'billboard_content', 'wp_make_content_images_responsive' );
		add_filter( 'billboard_content', 'do_shortcode', 11 ); // AFTER wpautop().
	}

	/**
	 * Whether the Billboard theme is active for the current request.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function is_active() {
		$is_active = true;
		$mode      = $this->get_setting( 'mode' );

		if ( is_admin() ) {
			$is_active = false;
		}

		// Don't display for anyone.
		if ( $is_active && empty( $mode ) && ! $this->is_preview() ) {
			$is_active = false;
		}

		// Don't display for logged in users if the mode isn't set to 'everyone'.
		if ( $is_active && ! $this->is_preview() && is_user_logged_in() && 'everyone' !== $mode ) {
			$is_active = false;
		}

		// Don't display for whitelisted IPs.
		if ( $is_active && ! $this->is_preview() && $this->is_ip_allowed() ) {
			$is_active = false;
		}

		return apply_filters( 'billboard_is_active', $is_active );
	}

	/**
	 * Retrieve a setting.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $name Setting name.
	 * @return mixed
	 */
	public function get_setting( $name ) {
		$settings = $this->get_settings();

		$value = null;
		if ( isset( $settings[ $name ] ) ) {
			$value = $settings[ $name ];
		}

		return $value;
	}

	/**
	 * Retrieve all settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_settings() {
		return wp_parse_args( get_option( 'billboard', array() ), array(
			'allowed_ips'                => array(),
			'background_color'           => '#000000',
			'background_image'           => '',
			'background_overlay_opacity' => 50,
			'background_video'           => '',
			'display_header_text'        => true,
			'enable_maintenance_mode'    => false,
			'logo'                       => '',
			'logo_width'                 => 0,
			'mode'                       => '',
			'restrict_feeds'             => false,
			'tagline'                    => get_bloginfo( 'description' ),
			'text_scheme'                => 'light',
			'title'                      => get_bloginfo( 'name' ),
		) );
	}

	/**
	 * Set up the theme.
	 *
	 * Registers the theme directory in the plugin and filters various options
	 * to make it the active theme for the current request.
	 *
	 * @since 1.0.0
	 */
	public function setup_theme() {
		register_theme_directory( $this->get_path( 'themes' ) );

		$this->template = 'billboard';
		add_filter( 'template',                 array( $this, 'filter_template' ) );
		add_filter( 'stylesheet',               array( $this, 'filter_template' ) );
		add_filter( 'pre_option_template',      array( $this, 'filter_template' ) );
		add_filter( 'pre_option_template_root', array( $this, 'filter_template_root' ) );
	}

	/**
	 * Send maintenance headers.
	 *
	 * @since 1.0.0
	 *
	 * @link https://webmasters.googleblog.com/2011/01/how-to-deal-with-planned-site-downtime.html
	 * @link https://yoast.com/http-503-site-maintenance-seo/
	 */
	public function maybe_send_maintenance_headers() {
		if ( is_customize_preview() || ! $this->get_setting( 'enable_maintenance_mode' ) ) {
			return;
		}

		status_header( 503 );
		header( 'Status: 503 Service Unavailable' );
		header( 'Retry After: ' . DAY_IN_SECONDS );
	}

	/**
	 * Restrict feed access to logged-in users.
	 *
	 * @sine 1.0.0
	 */
	public function restrict_feed_access() {
		if ( $this->get_setting( 'restrict_feeds' ) && is_feed() && ! is_user_logged_in() ) {
			auth_redirect();
		}
	}

	/**
	 * Register theme assets.
	 *
	 * @since 1.0.0
	 */
	public function register_assets() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_style(
			'themicons',
			$this->get_url( '/assets/css/themicons.css' ),
			array(),
			'2.2.0'
		);

		wp_register_script(
			'jquery-cue',
			$this->get_url( 'assets/js/vendor/jquery.cue' . $suffix . '.js' ),
			array( 'mediaelement' ),
			'1.1.6',
			true
		);
	}

	/**
	 * Add body classes based on Billboard settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes Array of classes.
	 */
	public function add_body_classes( $classes ) {
		if ( $this->get_setting( 'social_menu' ) ) {
			$classes[] = 'has-billboard-social-menu';
		}

		$layout = $this->get_setting( 'layout' );
		$layout = empty( $layout ) ? 'signature' : $layout;
		$classes[] = 'billboard-layout-' . $layout;

		$classes[] = 'billboard-' . $this->get_setting( 'text_scheme' ) . '-text-scheme';

		return $classes;
	}

	/**
	 * Filter the template/stylesheet value.
	 *
	 * @since 1.0.0
	 */
	public function filter_template() {
		return $this->template;
	}

	/**
	 * Filter the template root.
	 *
	 * @since 1.0.0
	 */
	public function filter_template_root() {
		return '/plugins/billboard/themes';
	}

	/**
	 * Filter plugin action links.
	 *
	 * Adds a 'Manage' link pointing to the Customizer panel.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $actions Array of action links.
	 * @return array
	 */
	public function filter_action_links( $actions ) {
		$actions['manage'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $this->get_customizer_url() ),
			esc_html__( 'Manage', 'billboard' )
		);

		return $actions;
	}

	/**
	 * Set up the toolbar with a link to customize the Billboard.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_Admin_Bar $toolbar Tool bar.
	 */
	public function setup_toolbar( $toolbar ) {
		$class = $title = '';

		if ( $this->get_setting( 'enable_maintenance_mode' ) ) {
			$class = 'is-maintenance-mode';
			$title = esc_html( 'Maintenance mode is active.' );
		} elseif ( $this->is_active() ) {
			$class = 'is-active';
		}

		$toolbar->add_node( array(
			'parent' => 'top-secondary',
			'id'     => 'billboard',
			'title'  => esc_html__( 'Billboard', 'billboard' ),
			'href'   => $this->get_customizer_url(),
			'meta'  => array(
				'class' => $class,
				'title' => $title,
			),
		) );

		?>
		<style type="text/css" scoped>
		#wp-admin-bar-billboard.is-maintenance-mode a.ab-item {
			background-color: rgba(255, 187, 51, 0.75);
		}
		</style>
		<?php
	}

	/**
	 * Retrieve a deep link to the Billboard panel in the Customizer.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_customizer_url() {
		return admin_url( 'customize.php?autofocus[panel]=billboard' );
	}

	/**
	 * Whether preview mode is active.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	protected function is_preview() {
		return isset( $_GET['billboard'] ) && 'preview' === $_GET['billboard'];
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

	/**
	 * Whether the IP address is allowed to bypass the Billboard.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	protected function is_ip_allowed() {
		$allowed = $this->get_setting( 'allowed_ips' );

		if ( is_string( $allowed ) ) {
			$allowed = preg_split( '#\n|\r#', $allowed, -1, PREG_SPLIT_NO_EMPTY );
		}

		return in_array( $this->get_ip_address(), $allowed, true );
	}
}
