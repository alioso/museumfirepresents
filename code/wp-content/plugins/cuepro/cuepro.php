<?php
/**
 * Cue Pro
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Cue Pro
 * Plugin URI:  https://audiotheme.com/view/cuepro/
 * Description: Share, sell, and gain insight into how visitors interact with your playlist tracks.
 * Version:     1.2.2
 * Author:      AudioTheme
 * Author URI:  https://audiotheme.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cuepro
 * Domain Path: /languages
 * Package Source: https://audiotheme.com/
 */

/**
 * Autoloader callback.
 *
 * Converts a class name to a file path and requires it if it exists.
 *
 * @since 1.0.0
 *
 * @param string $class Class name.
 */
function cuepro_autoloader( $class ) {
	if ( 0 !== strpos( $class, 'CuePro_' ) ) {
		return;
	}

	$file  = dirname( __FILE__ ) . '/classes/';
	$file .= str_replace( array( 'CuePro_', '_' ), array( '', '/' ), $class );
	$file .= '.php';

	if ( file_exists( $file ) ) {
		require_once( $file );
	}
}
spl_autoload_register( 'cuepro_autoloader' );

/**
 * Retrieve the main plugin instance.
 *
 * @since 1.0.0
 *
 * @return CuePro_Plugin
 */
function cuepro() {
	static $instance;

	if ( null === $instance ) {
		$instance = new CuePro_Plugin();
	}

	return $instance;
}

$cuepro = cuepro()
	->set_basename( plugin_basename( __FILE__ ) )
	->set_directory( plugin_dir_path( __FILE__ ) )
	->set_file( __FILE__ )
	->set_slug( 'cuepro' )
	->set_url( plugin_dir_url( __FILE__ ) )
	->register_hooks( new CuePro_Provider_I18n() )
	->register_hooks( new CuePro_Provider_Install() )
	->register_hooks( new CuePro_Provider_Embed() )
	->register_hooks( new CuePro_Provider_Assets() )
	->register_hooks( new CuePro_Provider_Customize() )
	->register_hooks( new CuePro_Provider_TrackActionLinks() )
	->register_hooks( new CuePro_Provider_TrackDownloadLink() )
	->register_hooks( new CuePro_Provider_TrackPurchaseLink() )
	->register_hooks( new CuePro_Theme_Mono() );

if ( is_admin() ) {
	$cuepro
		->register_hooks( new CuePro_Provider_Compatibility() )
		->register_hooks( new CuePro_Screen_Insights() );
}

/**
 * Include template tags.
 */
include( $cuepro->get_path( 'includes/general-template.php' ) );

/**
 * Load the plugin.
 */
add_action( 'plugins_loaded', array( $cuepro, 'load_plugin' ) );
