<?php
/**
 * Front page theme feature.
 *
 * Overrides the front-page.php template with the list of posts if a page hasn't
 * been set for the front page. Also allows for registering additional page
 * templates that can be used instead of front-page.php.
 *
 * @since 2.0.0
 *
 * @package Cedaro\Theme
 * @copyright Copyright (c) 2014, Cedaro
 * @license GPL-2.0+
 */

/**
 * Class for front page related features.
 *
 * @package Cedaro\Theme
 * @since 2.0.0
 */
class Cedaro_Theme_FrontPage {
	/**
	 * The theme object.
	 *
	 * @since 2.0.0
	 * @var Cedaro_Theme
	 */
	protected $theme;

	/**
	 * List of front page templates.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $templates = array();

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 *
	 * @param Cedaro_Theme $theme Cedaro theme instance.
	 */
	public function __construct( Cedaro_Theme $theme ) {
		$this->theme = $theme;
	}

	/*
	 * Public API methods.
	 */

	/**
	 * Wire up the theme hooks.
	 *
	 * @since 3.0.0
	 */
	public function add_support() {
		add_filter( 'template_include', array( $this, 'template_overrides' ) );
		return $this;
	}

	/**
	 * Register page templates that can override front-page.php.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $templates One or more page templates.
	 */
	public function add_templates( $templates ) {
		$this->templates = array_filter( array_merge( $this->templates, (array) $templates ) );
		return $this;
	}

	/**
	 * Retrieve a list of allowed front page templates.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_templates() {
		/**
		 * Allowed front page templates.
		 *
		 * @since 1.0.0
		 *
		 * @param array $templates List of template files.
		 */
		return apply_filters( $this->theme->prefix . '_front_page_templates', $this->templates );
	}

	/*
	 * Hook callbacks.
	 */

	/**
	 * Load the correct front page template.
	 *
	 * If a page hasn't been selected for the front page, show the blog (home)
	 * template.
	 *
	 * Otherwise, allow certain templates in the page template dropdown to
	 * override the default front-page.php template.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Template file.
	 * @return string
	 */
	public function template_overrides( $template ) {
		if ( is_front_page() ) {
			if ( 'page' !== get_option( 'show_on_front' ) ) {
				$template = get_home_template();
			} elseif ( is_page_template() &&( $templates = $this->get_templates() ) ) {
				foreach ( (array) $templates as $key => $filename ) {
					if ( is_page_template( $filename ) ) {
						$template = get_page_template();
						break;
					}
				}
			}
		}

		return $template;
	}
}
