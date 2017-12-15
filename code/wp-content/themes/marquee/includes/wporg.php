<?php
/**
 * Functionality specific to self-hosted installations of WordPress, including
 * support for plugins.
 *
 * @package Marquee
 * @since 1.0.0
 */

/**
 * Load PJAX functionality.
 */
require( get_template_directory() . '/includes/dependencies.php' );
require( get_template_directory() . '/includes/pjax.php' );

/**
 * Set up self-hosted functionality.
 *
 * @since 1.0.0
 */
function marquee_wporg_setup() {
	$meta = array(
		'button',
		'input',
		'select',
		'textarea',
		'.button',
		'.site .skip-link',
		'.site-description',
		'.site-footer',
		'.entry-header .entry-meta',
		'.entry-subtitle',
		'.entry-terms',
		'.comment-reply-title small',
		'.comment-list .reply',
		'.comment-metadata',
		'.pingback .edit-link',
		'.trackback .edit-link',
		'.breadcrumbs',
		'.dropdown-group li',
		'.dropdown-group-title',
		'.block-grid-item-title',
		'.secondary-navigation',
		'.site-navigation-toggle',
		'.navigation .nav-previous',
		'.navigation .nav-next',
		'.page-links',
		'.widget',
		'.widget-title',
		'.site-player',
		'.site-current-track-details',
		'.gig-meta',
		'.gig-meta .gig-venue-name',
		'.venue-meta',
		'.gig-list',
		'.gig-list-header',
		'.gig-card',
		'.record-details',
		'.record-details',
		'.tracklist-title',
		'.record-tracklist',
		'.widget_audiotheme_record .more a',
		'.widget_audiotheme_track .more a',
		'.credits',
		'#infinite-handle span',
		'#infinite-footer',
	);

	marquee_theme()->fonts
		->add_support()
		->add_fonts( array(
			array( 'family' => 'Codystar', 'stack' => 'Codystar, cursive', 'tags' => array( 'site-title' ) ),
			array( 'family' => 'Geostar', 'stack' => 'Geostar, cursive', 'tags' => array( 'site-title' ) ),
			array( 'family' => 'Passion One', 'stack' => '"Passion One", cursive', 'tags' => array( 'site-title' ) ),
			array( 'family' => 'Plaster', 'stack' => 'Plaster, cursive', 'tags' => array( 'site-title' ) ),
		) )
		->register_text_groups( array(
			array(
				'id'          => 'site-title',
				'label'       => esc_html__( 'Site Title', 'marquee' ),
				'selector'    => '.site-title',
				'family'      => 'Roboto',
				'variations'  => '300,400',
				'tags'        => array( 'heading', 'site-title' ),
			),
			array(
				'id'          => 'site-navigation',
				'label'       => esc_html__( 'Site Navigation', 'marquee' ),
				'selector'    => '.site-navigation',
				'family'      => 'Roboto',
				'variations'  => '300',
				'tags'        => array( 'content', 'heading' ),
			),
			array(
				'id'          => 'headings',
				'label'       => esc_html__( 'Headings', 'marquee' ),
				'selector'    => 'h1, h2, h3, h4, h5, h6, .entry-title, .home.page .entry-title, .page-title',
				'family'      => 'Noto Serif',
				'variations'  => '700',
				'tags'        => array( 'content', 'heading' ),
			),
			array(
				'id'          => 'content',
				'label'       => esc_html__( 'Content', 'marquee' ),
				'selector'    => 'body',
				'family'      => 'Noto Serif',
				'variations'  => '400,400italic,700,700italic',
				'tags'        => array( 'content' ),
			),
			array(
				'id'          => 'meta',
				'label'       => esc_html__( 'Meta', 'marquee' ),
				'selector'    => implode( ', ', $meta ),
				'family'      => 'Roboto',
				'variations'  => '400,700',
				'tags'        => array( 'content', 'heading' ),
			),
		) );
}
add_action( 'after_setup_theme', 'marquee_wporg_setup' );

/**
 * Filter the style sheet URI to point to the parent theme when a child theme is
 * being used.
 *
 * @since 1.2.0
 *
 * @param  string $uri Style sheet URI.
 * @return string
 */
function marquee_stylesheet_uri( $uri ) {
	return get_template_directory_uri() . '/style.css';
}
add_filter( 'stylesheet_uri', 'marquee_stylesheet_uri' );

/**
 * Enqueue the child theme styles.
 *
 * The action priority must be set to load after any stylesheet that need to be
 * overridden in the child theme stylesheet.
 *
 * @since 1.2.0
 */
function marquee_enqueue_child_assets() {
	if ( is_child_theme() ) {
		wp_enqueue_style( 'marquee-child-style', get_stylesheet_directory_uri() . '/style.css' );
	}

	// Deregister old handle recommended in sample child theme.
	if ( wp_style_is( 'marquee-parent-style', 'enqueued' ) ) {
		wp_dequeue_style( 'marquee-parent-style' );
		wp_deregister_style( 'marquee-parent-style' );
	}
}
add_action( 'wp_enqueue_scripts', 'marquee_enqueue_child_assets', 20 );

/**
 * Enqueue scripts and styles for the front-end.
 *
 * @since 1.0.0
 */
function marquee_wporg_enqueue_assets() {
	wp_enqueue_script(
		'jquery-fitvids',
		get_template_directory_uri() . '/assets/js/vendor/jquery.fitvids.js',
		array( 'jquery' ),
		'1.1',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'marquee_wporg_enqueue_assets' );

/**
 * Register and update Customizer settings.
 *
 * @since 1.0.0
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function marquee_wporg_customize_register( $wp_customize ) {
	$wp_customize->add_setting( 'marquee_enable_pjax', array(
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'marquee_customize_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'marquee_enable_pjax', array(
		'label'    => esc_html__( 'Enable fast page loading?', 'marquee' ),
		'section'  => 'theme_options',
		'settings' => 'marquee_enable_pjax',
		'type'     => 'checkbox',
	) );
}
add_action( 'customize_register', 'marquee_wporg_customize_register' );

/**
 * Add PJAX as a dependency for the main script when enabled.
 *
 * @since 1.0.0
 *
 * @param array $dependencies Script handles.
 * @return array
 */
function marquee_wporg_maybe_enable_pjax( $dependencies ) {
	wp_register_script(
		'jquery-pjax',
		get_template_directory_uri() . '/assets/js/vendor/jquery.pjax.js',
		array( 'jquery' ),
		'1.9.6',
		true
	);

	if ( get_theme_mod( 'marquee_enable_pjax' ) && ! is_customize_preview() ) {
		$dependencies[] = 'jquery-pjax';
		$dependencies[] = 'mediaelement';
	}

	return $dependencies;
}
add_filter( 'marquee_script_dependencies', 'marquee_wporg_maybe_enable_pjax' );

/**
 * Filter Jetpack's Infinite Scroll args.
 *
 * Sets the type to 'click' when PJAX is enabled to prevent pushState conflicts.
 *
 * @since 1.0.0
 *
 * @param  array $args Infinite Scroll theme support arguments.
 * @return array
 */
function marquee_wporg_infinite_scroll_args( $args ) {
	if ( get_theme_mod( 'marquee_enable_pjax' ) ) {
		$args['type'] = 'click';
	}

	return $args;
}
add_action( 'marquee_infinite_scroll_args', 'marquee_wporg_infinite_scroll_args' );


/*
 * Plugin support.
 * -----------------------------------------------------------------------------
 */

/**
 * Load AudioTheme support or display a notice that it's needed.
 */
if ( function_exists( 'audiotheme_load' ) ) {
	include( get_template_directory() . '/includes/plugins/audiotheme.php' );
} else {
	include( get_template_directory() . '/includes/vendor/class-audiotheme-themenotice.php' );
	new Audiotheme_ThemeNotice();
}

/**
 * Load Cue support.
 */
if ( class_exists( 'Cue' ) ) {
	include( get_template_directory() . '/includes/plugins/cue.php' );
}

/**
 * Load Jetpack support.
 */
if ( class_exists( 'Jetpack' ) ) {
	include( get_template_directory() . '/includes/plugins/jetpack.php' );
}

/**
 * Load WooCommerce support.
 */
if ( class_exists( 'WooCommerce' ) ) {
	include( get_template_directory() . '/includes/plugins/woocommerce.php' );
}
