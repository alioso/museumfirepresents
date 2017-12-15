<?php
/**
 * Archive feature base.
 *
 * @since 3.2.0
 *
 * @package Cedaro\Theme
 * @copyright Copyright (c) 2015, Cedaro
 * @license GPL-2.0+
 */

/**
 * Class for choosing and enforcing archive settings.
 *
 * @package Cedaro\Theme
 * @since 3.2.0
 */
abstract class Cedaro_Theme_Archive_Feature {
	/**
	 * Whether `the_content` filter is active.
	 *
	 * @since 3.2.0
	 * @var bool
	 */
	protected $in_the_content = false;

	/**
	 * Available options for the feature.
	 *
	 * @since 3.2.0
	 * @var array
	 */
	protected $options = array();

	/**
	 * Post types affected by the feature.
	 *
	 * @since 3.2.0
	 * @var array
	 */
	protected $post_types = array( 'post' );

	/**
	 * The theme object.
	 *
	 * @since 3.2.0
	 * @var Cedaro_Theme
	 */
	protected $theme;

	/**
	 * Constructor method.
	 *
	 * @since 3.2.0
	 *
	 * @param Cedaro_Theme $theme Cedaro theme instance.
	 */
	public function __construct( Cedaro_Theme $theme ) {
		$this->theme = $theme;
	}

	/**
	 * Register post types that can be featured.
	 *
	 * @since 3.2.0
	 *
	 * @param array|string $post_types Post types.
	 */
	public function add_post_types( $post_types ) {
		$this->post_types = array_merge( $this->post_types, (array) $post_types );
		return $this;
	}

	/**
	 * Add options to the existing set.
	 *
	 * @since 3.2.0
	 *
	 * @param array $options Feature options.
	 * @return $this
	 */
	public function add_options( $options ) {
		$this->options = array_merge( $this->options, $options );
		return $this;
	}

	/**
	 * Whether the current post is in a supported loop.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public function in_supported_loop() {
		global $wp_query;

		return (
			! $this->in_the_content &&
			! is_feed() &&
			! is_singular() &&
			$wp_query->is_main_query() &&
			in_the_loop() &&
			in_array( get_post_type(), $this->post_types )
		);
	}

	/**
	 * Remove options from the existing set.
	 *
	 * @since 3.2.0
	 *
	 * @param array|string $options An option id or array of ids.
	 * @return $this
	 */
	public function remove_options( $options ) {
		foreach ( (array) $options as $id ) {
			unset( $this->options[ $id ] );
		}
		return $this;
	}

	/**
	 * Set the feature options.
	 *
	 * @since 3.2.0
	 *
	 * @param array $options Feature options.
	 * @return $this
	 */
	public function set_options( $options ) {
		$this->options = $options;
		return $this;
	}

	/*
	 * Hook callbacks.
	 */

	/**
	 * Sanitization callback for the archive content mode in the Customizer.
	 *
	 * @since 3.2.0
	 *
	 * @param string $value Setting value.
	 * @return string empty by default, value string otherwise.
	 */
	public function sanitize_choices( $value ) {
		if ( ! array_key_exists( $value, $this->get_choices() ) ) {
			$value = '';
		}

		return $value;
	}

	/*
	 * Protected methods.
	 */

	/**
	 * Retrieve the image mode options.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	protected function get_choices() {
		$choices = array();
		foreach ( $this->options as $key => $data ) {
			$choices[ $key ] = is_string( $data ) ? $data : $data['label'];
		}
		return $choices;
	}
}
