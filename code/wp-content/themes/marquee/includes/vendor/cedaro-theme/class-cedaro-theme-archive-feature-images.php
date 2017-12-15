<?php
/**
 * Archive image settings.
 *
 * @since 3.2.0
 *
 * @package Cedaro\Theme
 * @copyright Copyright (c) 2015, Cedaro
 * @license GPL-2.0+
 */

/**
 * Class for managing featured images on archives.
 *
 * @package Cedaro\Theme
 * @since 3.2.0
 */
class Cedaro_Theme_Archive_Feature_Images extends Cedaro_Theme_Archive_Feature {
	/*
	 * Public API methods.
	 */

	/**
	 * Wire up the theme hooks.
	 *
	 * @since 3.2.0
	 *
	 * @param array $options Archive image options.
	 * @return $this
	 */
	public function add_support( $options = array() ) {
		if ( empty( $options ) ) {
			$options = array(
				''          => 'None',
				'header'    => array(
					'label' => esc_html__( 'Post header', 'marquee' ),
					'size'  => 'large',
					'class' => '',
				),
				'thumbnail' => array(
					'label' => esc_html__( 'Thumbnails', 'marquee' ),
					'size'  => 'thumbnail',
					'class' => 'alignleft',
				),
			);
		}

		$this->set_options( $options );

		// Prepend after the filter in Cedaro_Theme_Archive_Feature_Content.
		add_action( $this->theme->prefix . '_entry_content_top', array( $this, 'print_the_image_html' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		return $this;
	}

	/**
	 * Retrieve the image mode.
	 *
	 * @since 3.2.0
	 *
	 * @param int|WP_Post $post Optional. Post ID or object.
	 * @return string
	 */
	public function get_mode( $post = null ) {
		$mode = get_theme_mod( 'archive_images_mode', '' );
		return apply_filters( $this->theme->prefix . '_archive_image_mode', $mode, $post );
	}

	/**
	 * Retrieve the featured image HTML for the current post.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_the_image() {
		$prefix = $this->theme->prefix;
		$mode   = $this->get_mode();
		$class  = $this->options[ $mode ]['class'];
		$size   = $this->options[ $mode ]['size'];

		return get_the_post_thumbnail(
			get_the_ID(),
			apply_filters( $prefix . '_archive_image_size', $size, $mode ),
			array( 'class' => $prefix . '-archive-image ' . sanitize_html_class( $class ) )
		);
	}

	/*
	 * Hook callbacks.
	 */

	/**
	 * Print a post's featured image when in supported loops.
	 *
	 * @since 3.2.0
	 */
	public function print_the_image_html() {
		$html   = '';
		$prefix = $this->theme->prefix;
		$mode   = $this->get_mode();

		// Display featured images when activated and when the content
		// mode is not empty.
		if (
			$this->in_supported_loop() &&
			'' !== $mode &&
			( $image = $this->get_the_image() )
		) {
			$classes = array(
				'entry-content-asset',
				$prefix . '-archive-media',
				$prefix . '-archive-media--' . $mode,
			);

			$html = sprintf(
				'<figure class="%s"><a href="%s">%s</a></figure>',
				implode( ' ', array_map( 'sanitize_html_class', $classes ) ),
				esc_url( get_the_permalink() ),
				$image
			);

			$html = apply_filters( $prefix . '_archive_image_html', $html );
		}

		echo $html; // WPCS: XSS OK.
	}

	/**
	 * Register Customizer settings and controls.
	 *
	 * @since 3.2.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function customize_register( $wp_customize ) {
		// Set up archive images mode setting.
		$wp_customize->add_setting( 'archive_images_mode', array(
			'default'           => '',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => array( $this, 'sanitize_choices' ),
		) );

		// Set up archive images mode setting control.
		$wp_customize->add_control( 'cedaro_archive_images_mode', array(
			'label'    => esc_html__( 'Archive Images', 'marquee' ),
			'section'  => 'theme_options',
			'settings' => 'archive_images_mode',
			'type'     => 'select',
			'choices'  => $this->get_choices(),
		) );
	}
}
