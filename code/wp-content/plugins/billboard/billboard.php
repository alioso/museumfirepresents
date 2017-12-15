<?php
/**
 * Billboard
 *
 * @package   Billboard
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:    Billboard
 * Plugin URI:     https://audiotheme.com/view/billboard/
 * Description:    From idea to website, Billboard is a micro-landing page designed to be the quickest way to introduce your concept to the world.
 * Version:        1.1.0
 * Author:         AudioTheme
 * Author URI:     https://audiotheme.com/
 * License:        GPL-2.0+
 * License URI:    http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:    billboard
 * Domain Path:    /languages
 * Package Source: https://audiotheme.com/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoloader callback.
 *
 * Converts a class name to a file path and requires it if it exists.
 *
 * @since 1.0.0
 *
 * @param string $class Class name.
 */
function billboard_autoloader( $class ) {
	if ( 0 !== strpos( $class, 'Billboard_' ) ) {
		return;
	}

	$file  = dirname( __FILE__ ) . '/classes/';
	$file .= str_replace( array( 'Billboard_', '_' ), array( '', '/' ), $class );
	$file .= '.php';

	if ( file_exists( $file ) ) {
		require_once( $file );
	}
}
spl_autoload_register( 'billboard_autoloader' );

/**
 * Retrieve the main plugin instance.
 *
 * @since 1.0.0
 *
 * @return Billboard_Plugin
 */
function billboard() {
	static $instance;

	if ( null === $instance ) {
		$instance = new Billboard_Plugin();
	}

	return $instance;
}

billboard()
	->set_basename( plugin_basename( __FILE__ ) )
	->set_directory( plugin_dir_path( __FILE__ ) )
	->set_file( __FILE__ )
	->set_slug( 'billboard' )
	->set_url( plugin_dir_url( __FILE__ ) );

/**
 * Include template tags.
 */
include( billboard()->get_path( 'includes/general-template.php' ) );

/**
 * Load the plugin.
 *
 * @since 1.0.0
 */
function billboard_load() {
	billboard()->register_hooks( new Billboard_Provider_Customize() );

	// @todo Display a message if Cue isn't enabled?
	if ( function_exists( 'get_cue_playlist_tracks' ) ) {
		billboard()->register_hooks( new Billboard_Provider_Player() );
	}

	$fonts = new Billboard_Provider_Fonts();

	billboard()
		->register_hooks( $fonts )
		->load_plugin();

	$fonts->register_text_groups( array(
		array(
			'id'          => 'billboard-title',
			'label'       => esc_html__( 'Title', 'billboard' ),
			'selector'    => '.billboard-title',
			'family'      => 'Roboto',
			'variations'  => '300,400',
			'tags'        => array( 'title', 'heading' ),
		),
		array(
			'id'          => 'billboard-headings',
			'label'       => esc_html__( 'Headings', 'billboard' ),
			'selector'    => 'h1, h2, h3, h4, h5, h6',
			'family'      => 'Roboto',
			'variations'  => '300,400',
			'tags'        => array( 'heading' ),
		),
		array(
			'id'          => 'billboard-content',
			'label'       => esc_html__( 'Content', 'billboard' ),
			'selector'    => 'body, button, input, select, textarea, .button',
			'family'      => 'Roboto',
			'variations'  => '300,300italic,400,400italic,700',
			'tags'        => array( 'content' ),
		),
		array(
			'id'          => 'billboard-player',
			'label'       => esc_html__( 'Player', 'billboard' ),
			'selector'    => '.billboard-player, .cue-skin-billboard.mejs-container, .cue-skin-billboard.mejs-container .mejs-controls div',
			'family'      => 'Roboto',
			'variations'  => '400',
			'tags'        => array( 'content' ),
		),
	) );
}
add_action( 'plugins_loaded', 'billboard_load' );
