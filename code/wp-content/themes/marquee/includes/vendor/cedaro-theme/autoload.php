<?php
/**
 * Cedaro theme autoloader.
 *
 * @since 2.0.0
 *
 * @package Cedaro\Theme
 * @copyright Copyright (c) 2014, Cedaro
 * @license GPL-2.0+
 */

/**
 * Cedaro theme autoloader class.
 *
 * @since 3.1.0
 */
class Cedaro_Theme_Autoloader {
	/**
	 * Base directory.
	 *
	 * @since 3.1.0
	 * @var string
	 */
	private $base_directory;

	/**
	 * Autoloader constructor.
	 *
	 * @since 3.1.0
	 *
	 * @param string $base_directory Cedaro theme base directory.
	 */
	public function __construct( $base_directory = null ) {
		if ( null === $base_directory ) {
			$base_directory = dirname( __FILE__ );
		}

		$real_directory = realpath( $base_directory );
		if ( is_dir( $real_directory ) ) {
			$this->base_directory = $real_directory;
		} else {
			$this->base_directory = $base_directory;
		}
	}

	/**
	 * Register a new instance as an SPL autoloader.
	 *
	 * @since 3.1.0
	 *
	 * @param string $base_directory Cedaro theme base directory.
	 * @return Cedaro_Theme_Autoloader Registered Autoloader instance.
	 */
	public static function register( $base_directory = null ) {
		$loader = new self( $base_directory );
		spl_autoload_register( array( $loader, 'autoload' ) );
		return $loader;
	}

	/**
	 * Autoload Cedaro Theme classes.
	 *
	 * Converts a class name to a file path and requires it if it exists.
	 *
	 * @since 3.1.0
	 *
	 * @param string $class Class name.
	 */
	public static function autoload( $class ) {
		if ( 0 !== strpos( $class, 'Cedaro_Theme' ) ) {
			return;
		}

		$class = str_replace( 'Cedaro_', '', $class );
		$class = ( false === strpos( $class, 'Theme_' ) ) ? 'Theme' : $class;

		$file  = dirname( __FILE__ ) . '/class-cedaro-';
		$file .= str_replace( '_', '-', strtolower( $class ) ) . '.php';

		if ( file_exists( $file ) ) {
			require_once( $file );
		}
	}
}
