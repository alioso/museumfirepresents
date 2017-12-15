<?php
/**
 * Cedaro Theme library.
 *
 * Wires up common hooks and provides features and APIs for other common tasks.
 *
 * @since 1.0.0
 *
 * @package Cedaro\Theme
 * @copyright Copyright (c) 2014, Cedaro
 * @license GPL-2.0+
 */

/**
 * Class for common theme functionality.
 *
 * @license GPL-2.0+
 * @since 1.0.0
 */
class Cedaro_Theme {
	/**
	 * Registry of instantiated theme feature objects.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $registry = array();

	/**
	 * Prefix to prevent conflicts.
	 *
	 * Used to prefix filters to make them unique.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $prefix;

	/**
	 * List of internationalized strings.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $strings = array();

	/**
	 * Constructor method to initialize the class.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     Configuration options. Optional.
	 *
	 *    @var string $prefix  Optional. Theme prefix. Defaults to the template name.
	 *    @var array  $strings List of internationalized strings.
	 * }
	 */
	public function __construct( $args = array() ) {
		$this->prefix = empty( $args['prefix'] ) ? get_template() : sanitize_key( $args['prefix'] );
	}

	/**
	 * Load the theme.
	 *
	 * This should be called immediately in functions.php and none of the
	 * included features or methods should be used before after_setup_theme
	 * fires.
	 *
	 * @since 3.0.0
	 */
	public function load() {
		add_action( 'after_setup_theme', array( $this, 'maybe_load_wporg_support' ), 1 );
		add_action( 'after_setup_theme', array( $this, 'register_hooks' ) );
		add_action( 'after_setup_theme', array( $this, 'setup_theme' ), 100 );
	}

	/**
	 * Magic get method.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Property name.
	 * @return mixed
	 */
	public function __get( $key ) {
		switch ( $key ) {
			case 'archive_content' :
				return $this->build( 'Archive_Feature_Content' );
			case 'archive_images' :
				return $this->build( 'Archive_Feature_Images' );
			case 'featured_content' :
				return $this->build( 'FeaturedContent' );
			case 'fonts' :
				return $this->build( 'Fonts' );
			case 'front_page' :
				return $this->build( 'FrontPage' );
			case 'logo' :
				return $this->build( 'SiteLogo' );
			case 'page_types' :
				return $this->build( 'PageTypes' );
			case 'post_collections' :
				return $this->build( 'PostCollections' );
			case 'post_media' :
				return $this->build( 'PostMedia' );
			case 'prefix' :
				return $this->prefix;
			case 'template' :
				return $this->build( 'Template' );
		}
	}

	/**
	 * Wire up the theme hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {
		add_filter( 'wp_title',  array( $this, 'wp_title' ), 10, 2 );
		add_action( 'edit_term', array( $this, 'flush_theme_taxonomy_terms_transient' ), 10, 3 );
		add_action( 'save_post', array( $this, 'flush_theme_taxonomy_terms_transient' ), 10, 2 );
	}

	/**
	 * Retrieve a uri to the library.
	 *
	 * @since 2.0.0
	 *
	 * @param string $path Optional. Path to append to the base library URI.
	 * @return string
	 */
	public function get_library_uri( $path = '' ) {
		return get_template_directory_uri() . '/includes/vendor/cedaro-theme/' . ltrim( $path, '/' );
	}

	/**
	 * Whether the current request is a Customizer preview.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_customizer_preview() {
		global $wp_customize;
		return $wp_customize instanceof WP_Customize_Manager && $wp_customize->is_preview();
	}

	/**
	 * Whether the current environment is WordPress.com.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_wpcom() {
		return apply_filters( 'cedaro_theme_is_wpcom', false );
	}

	/*
	 * Hook callbacks.
	 */

	/**
	 * Load self-hosted functionality and plugin supoport.
	 *
	 * This should be called after wpcom.php has been loaded at
	 * after_setup_theme:0.
	 *
	 * @since 3.0.0
	 */
	public function maybe_load_wporg_support() {
		// Only load if not on WordPress.com and the wporg.php file exists.
		if ( ! $this->is_wpcom() ) {
			$wporg_file = '/includes/wporg.php';

			// Load from the child theme first.
			if ( is_child_theme() && file_exists( get_stylesheet_directory() . $wporg_file ) ) {
				require_once( get_stylesheet_directory() . $wporg_file );
			}

			if ( file_exists( get_template_directory() . $wporg_file ) ) {
				require_once( get_template_directory() . $wporg_file );
			}
		}
	}

	/**
	 * Set up the theme features.
	 *
	 * @since 2.0.0
	 */
	public function setup_theme() {
		// Load logo support.
		if ( current_theme_supports( 'site-logo' ) ) {
			$this->logo->add_support();
		}
	}

	/**
	 * Filter wp_title to print a neat <title> tag based on the current view.
	 *
	 * @since 1.0.0
	 *
	 * @param string $title Default title text for current view.
	 * @param string $sep Optional separator.
	 * @return string The filtered title.
	 */
	public function wp_title( $title, $sep ) {
		global $page, $paged;

		if ( current_theme_supports( 'title-tag' ) || is_feed() ) {
			return $title;
		}

		// Add the blog name.
		$title .= get_bloginfo( 'name' );

		// Add the site description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) ) {
			$title .= " $sep $site_description";
		}

		// Add a page number if necessary.
		if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
			$title .= " $sep " . sprintf( esc_html_x( 'Page %s', 'suffix for paged archive titles', 'marquee' ), max( $paged, $page ) );
		}

		return $title;
	}

	/**
	 * Flush out the transients used in
	 * Cedaro_Theme_Template::has_multiple_terms().
	 *
	 * @since 3.1.0
	 */
	public function flush_theme_taxonomy_terms_transient() {
		$taxonomies = array();

		if ( 'edit_term' === current_filter() ) {
			$taxonomies[] = func_get_arg( 2 );
		} elseif ( 'save_post' === current_filter() ) {
			$post = func_get_arg( 1 );
			$taxonomies = get_object_taxonomies( $post->post_type );
		}

		if ( empty( $taxonomies ) ) {
			return;
		}

		foreach ( (array) $taxonomies as $taxonomy ) {
			$transient = sprintf( $this->prefix . '_%s_terms', $taxonomy );
			delete_transient( $transient );
		}
	}

	/*
	 * Protected methods.
	 */

	/**
	 * Build an object or retrieve an existing instance.
	 *
	 * @since 2.0.0
	 *
	 * @link https://ttmm.io/tech/multi-instance-factories-php/
	 *
	 * @param string $object Object identifier.
	 * @param string $name   Object instance name.
	 * @return object
	 * @throws InvalidArgumentException Thrown when corresponding class doesn't exist.
	 */
	protected function build( $object, $name = 'canonical' ) {
		if ( ! isset( $this->registry[ $object ] ) ) {
			$this->registry[ $object ] = array();
		}

		$class_name = 'Cedaro_Theme_' . str_replace( array( ' ', '-' ), '_', $object );

		if ( ! $this->is_wpcom() && class_exists( $class_name . '_WPorg' ) ) {
			$class_name = $class_name . '_WPorg';
		} elseif ( ! class_exists( $class_name ) ) {
			throw new InvalidArgumentException( 'No class exists for the "' . $object . '" object.' );
		}

		if ( ! isset( $this->registry[ $object ][ $name ] ) ) {
			$this->registry[ $object ][ $name ] = new $class_name( $this );
		}

		return $this->registry[ $object ][ $name ];
	}

	/*
	 * Deprecated methods.
	 */

	/**
	 * Retrieve the section title for an archive.
	 *
	 * @since 1.0.0
	 * @deprecated 3.1.0
	 * @deprecated Use Cedaro_Theme_Template::get_archive_link()
	 * @see Cedaro_Theme_Template::get_archive_link()
	 *
	 * @param int|WP_Post $post Optional. Post ID or object.
	 */
	public function get_archive_link( $post = null ) {
		return $this->template->get_archive_link( $post );
	}
}
