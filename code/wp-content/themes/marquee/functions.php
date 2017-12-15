<?php
/**
 * Marquee functions and definitions.
 *
 * Sets up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme (see https://codex.wordpress.org/Theme_Development
 * and https://codex.wordpress.org/Child_Themes), you can override certain
 * functions (those wrapped in a function_exists() call) by defining them first
 * in your child theme's functions.php file. The child theme's functions.php
 * file is included before the parent theme's file, so the child theme
 * functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * see https://codex.wordpress.org/Plugin_API
 *
 * @package Marquee
 * @since 1.0.0
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 800;
}

/**
 * Adjust the content width for full width pages.
 *
 * @since 1.0.0
 */
function marquee_content_width() {
	global $content_width;

	if ( is_front_page() && is_page() ) {
		$content_width = 880;
	}
}
add_action( 'template_redirect', 'marquee_content_width' );

/**
 * Load helper functions and libraries.
 */
require( get_template_directory() . '/includes/customizer.php' );
require( get_template_directory() . '/includes/hooks.php' );
require( get_template_directory() . '/includes/template-helpers.php' );
require( get_template_directory() . '/includes/template-tags.php' );
require( get_template_directory() . '/includes/vendor/cedaro-theme/autoload.php' );
marquee_theme()->load();

/**
 * Set up theme defaults and register support for various WordPress features.
 *
 * @since 1.0.0
 */
function marquee_setup() {
	// Add support for translating strings in this theme.
	// @link https://codex.wordpress.org/Function_Reference/load_theme_textdomain
	load_theme_textdomain( 'marquee', get_template_directory() . '/languages' );

	// This theme styles the visual editor to resemble the theme style.
	add_editor_style( array(
		is_rtl() ? 'assets/css/editor-style-rtl.css' : 'assets/css/editor-style.css',
		marquee_fonts_icon_url(),
	) );

	// Add support for default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	// Add support for the title tag.
	// @link https://make.wordpress.org/core/2014/10/29/title-tags-in-4-1/
	add_theme_support( 'title-tag' );

	// Add support for post thumbnails.
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'marquee-block-grid-16x9', 425, 241, array( 'center', 'top' ) );
	add_image_size( 'marquee-featured-image',  880, 500, true );
	set_post_thumbnail_size( 425, 425, array( 'center', 'top' ) );

	// Add support for a logo.
	add_theme_support( 'site-logo', array(
		'size' => 'full',
	) );

	// Add HTML5 markup for the comment forms, search forms and comment lists.
	add_theme_support( 'html5', array(
		'caption',
		'comment-form',
		'comment-list',
		'gallery',
		'search-form',
	) );

	// Register default nav menus.
	register_nav_menus( array(
		'primary'   => esc_html__( 'Primary Menu', 'marquee' ),
		'secondary' => esc_html__( 'Secondary Menu', 'marquee' ),
		'social'    => esc_html__( 'Social Links Menu', 'marquee' ),
		'post'      => esc_html__( 'Blog Menu', 'marquee' ),
	) );

	// Add support for custom header functionality.
	add_theme_support( 'custom-header',  array(
		'default-image'      => get_template_directory_uri() . '/assets/images/default-header.jpg',
		'height'             => 800,
		'width'              => 720,
		'flex-height'        => true,
		'flex-width'         => true,
		'random-default'     => false,
		'header-text'        => true,
		'default-text-color' => 'ffffff',
		'uploads'            => true,
	) );

	// Register support for archive content settings.
	marquee_theme()->archive_content->add_support();

	// Register support for archive image settings.
	marquee_theme()->archive_images->add_support();
}
add_action( 'after_setup_theme', 'marquee_setup' );

/**
 * Register widget area.
 *
 * @since 1.0.0
 */
function marquee_register_sidebars() {
	register_sidebar( array(
		'id'              => 'home-widgets',
		'name'            => esc_html__( 'Home', 'marquee' ),
		'description'     => esc_html__( 'Widgets that appear on the homepage.', 'marquee' ),
		'before_widget'   => '<div id="%1$s" class="widget %2$s">',
		'after_widget'    => '</div>',
		'before_title'    => '<h2 class="widget-title">',
		'after_title'     => '</h2>',
	) );
}
add_action( 'widgets_init', 'marquee_register_sidebars' );

/**
 * Enqueue scripts and styles.
 *
 * @since 1.0.0
 */
function marquee_enqueue_assets() {
	// Add Themicons font, used in the main stylesheet.
	wp_enqueue_style( 'themicons', marquee_fonts_icon_url(), array(), '2.3.1' );

	// Load main style sheet.
	wp_enqueue_style( 'marquee-style', get_stylesheet_uri() );

	// Load RTL style sheet.
	wp_style_add_data( 'marquee-style', 'rtl', 'replace' );

	// Load theme scripts.
	$script_dependencies = array( 'backbone', 'jquery', 'underscore' );
	if ( is_front_page() && is_active_sidebar( 'home-widgets' ) ) {
		$script_dependencies[] = 'masonry';
	}

	wp_enqueue_script(
		'marquee-script',
		get_template_directory_uri() . '/assets/js/theme.bundle.js',
		apply_filters( 'marquee_script_dependencies', $script_dependencies ),
		'20150401',
		true
	);

	// Localize the main theme script.
	wp_localize_script( 'marquee-script', '_marqueeSettings', array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'customizeUrl' => esc_url( admin_url( 'customize.php' ) ),
		'isRTL'        => is_rtl(),
		'mejs'         => array(
			'pluginPath' => includes_url( 'js/mediaelement/', 'relative' ),
		),
	) );

	wp_enqueue_script(
		'mq4-hover-shim',
		get_template_directory_uri() . '/assets/js/vendor/mq4-hover-shim.js',
		array( 'marquee-script' ),
		'0.1.0',
		true
	);

	// Load script to support comment threading when it's enabled.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	wp_register_script(
		'marquee-player',
		get_template_directory_uri() . '/assets/js/player.bundle.js',
		array( 'backbone', 'jquery', 'marquee-script', 'mediaelement', 'underscore' ),
		'20150706',
		true
	);

	wp_localize_script( 'marquee-player', '_marqueePlayerSettings', array(
		'l10n' => array(
			'disableRepeat'  => esc_html__( 'Disable Repeat', 'marquee' ),
			'disableShuffle' => esc_html__( 'Disable Shuffle', 'marquee' ),
			'mute'           => esc_html__( 'Mute', 'marquee' ),
			'pause'          => esc_html__( 'Pause', 'marquee' ),
			'play'           => esc_html__( 'Play', 'marquee' ),
			'repeat'         => esc_html__( 'Repeat', 'marquee' ),
			'shuffle'        => esc_html__( 'Shuffle', 'marquee' ),
			'togglePlaylist' => esc_html__( 'Toggle Playlist', 'marquee' ),
			'unmute'         => esc_html__( 'Unmute', 'marquee' ),
		),
	) );

	wp_register_script(
		'marquee-tracklists',
		get_template_directory_uri() . '/assets/js/tracklists.js',
		array( 'marquee-script', 'mediaelement' ),
		'20150706',
		true
	);

	wp_localize_script( 'marquee-tracklists', '_marqueeTracklistSettings', array(
		'templateUrl' => get_template_directory_uri(),
	) );
}
add_action( 'wp_enqueue_scripts', 'marquee_enqueue_assets' );

/**
 * JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @since 1.0.0
 */
function marquee_javascript_detection() {
	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action( 'wp_head', 'marquee_javascript_detection', 0 );

/**
 * Add an HTML class to MediaElement.js container elements to aid styling.
 *
 * Extends the core _wpmejsSettings object to add a new feature via the
 * MediaElement.js plugin API.
 *
 * @since 1.0.0
 */
function marquee_mejs_add_container_class() {
	if ( ! wp_script_is( 'mediaelement', 'done' ) ) {
		return;
	}
	?>
	<script>
	(function() {
		var settings = window._wpmejsSettings || {};
		settings.features = settings.features || mejs.MepDefaults.features;
		settings.features.push( 'marqueeclass' );

		MediaElementPlayer.prototype.buildmarqueeclass = function( player ) {
			player.container.addClass( 'marquee-mejs-container' );
		};
	})();
	</script>
	<?php
}
add_action( 'wp_print_footer_scripts', 'marquee_mejs_add_container_class' );

/**
 * Retrieve the icon font style sheet URL.
 *
 * @since 1.0.0
 *
 * @return string Font stylesheet.
 */
function marquee_fonts_icon_url() {
	return get_template_directory_uri() . '/assets/css/themicons.css';
}

/**
 * Wrapper for accessing the Cedaro_Theme instance.
 *
 * @since 1.0.0
 *
 * @return Cedaro_Theme
 */
function marquee_theme() {
	static $instance;

	if ( null === $instance ) {
		Cedaro_Theme_Autoloader::register();
		$instance = new Cedaro_Theme( array( 'prefix' => 'marquee' ) );
	}

	return $instance;
}
