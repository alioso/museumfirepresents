<?php
/**
 * Page type theme features.
 *
 * Register "page types" for use on WPCOM, which consist of an archive and
 * singular templates. For instance, can be used for discography or listing
 * videos by simply setting the archive page template and adding albums or
 * videos as child pages.
 *
 * @since 2.0.0
 *
 * @package Cedaro\Theme
 * @copyright Copyright (c) 2014, Cedaro
 * @license GPL-2.0+
 */

/**
 * Class for the page type feature.
 *
 * @package Cedaro\Theme
 * @since 2.0.0
 */
class Cedaro_Theme_PageTypes {
	/**
	 * The theme object.
	 *
	 * @since 2.0.0
	 * @var Cedaro_Theme
	 */
	protected $theme;

	/**
	 * List of page types and their args.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $types = array();

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
	 *
	 * @return $this
	 */
	public function add_support() {
		add_filter( 'template_include', array( $this, 'template_overrides' ) );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
		add_filter( $this->theme->prefix . '_archive_title', array( $this, 'archive_title' ), 10, 2 );
		return $this;
	}

	/**
	 * Register a page type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Page type name.
	 * @param array  $args Page type arguments.
	 * @return $this
	 */
	public function register( $type, $args = array() ) {
		$type = sanitize_key( $type );

		$args = wp_parse_args( $args, array(
			'archive_body_class' => '',
			'archive_template'   => "templates/wpcom-archive-{$type}.php",
			'single_body_class'  => '',
			'single_template'    => "templates/wpcom-single-{$type}.php",
		) );

		$this->types[ $type ] = $args;
		return $this;
	}

	/**
	 * Determine the current page type.
	 *
	 * Works on archive pages or single pages.
	 *
	 * @since 1.0.0
	 *
	 * @param int|WP_Post $post Optional. Post ID or object. Defaults to the
	 *                          current global post.
	 * @return string
	 */
	public function get_type( $post = null ) {
		$page_type = '';

		foreach ( $this->types as $type => $args ) {
			if ( $this->is_archive( $type, $post ) || $this->is_type( $type, $post ) ) {
				$page_type = $type;
				break;
			}
		}

		return $page_type;
	}

	/**
	 * Determine if a page is the singular version of a registered type.
	 *
	 * - Ensure it's a page first.
	 * - See if the parent page is using a registered archive template (single
	 *   templates aren't registered as page templates).
	 * - Verify the type if it is passed.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $type Page type name.
	 * @param int|WP_Post $post Optional. Post ID or object. Defaults to the
	 *                          current global post.
	 * @return bool
	 */
	public function is_type( $type = '', $post = null ) {
		if ( ! is_page() ) {
			return false;
		}

		$post            = empty( $post ) ? get_queried_object() : get_post( $post );
		$is_page_type    = false;
		$parent_template = $post->post_parent ? get_post_meta( $post->post_parent, '_wp_page_template', true ) : '';

		if (
			! empty( $parent_template ) &&
			in_array( $parent_template, $this->get_archive_templates() ) &&
			( empty( $type ) || $this->types[ $type ]['archive_template'] === $parent_template )
		) {
			$is_page_type = true;
		}

		return $is_page_type;
	}

	/**
	 * Determine if a page is the archive of a registered type.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $type Page type name.
	 * @param int|WP_Post $post Optional. Post ID or object. Defaults to the
	 *                          current global post.
	 * @return bool
	 */
	public function is_archive( $type = '', $post = null ) {
		$post                 = empty( $post ) ? get_queried_object() : get_post( $post );
		$is_page_type_archive = false;
		$templates            = $this->get_archive_templates();

		if ( empty( $type ) ) {
			$is_page_type_archive = ( ! empty( $post->page_template ) && in_array( $post->page_template, $templates ) );
		} elseif ( ! empty( $post ) ) {
			$page_type_archive = $this->get_type_arg( 'archive_template', $type );
			$is_page_type_archive = ( ! empty( $page_type_archive ) && ! empty( $post->page_template ) && $page_type_archive === $post->page_template );
		}

		return $is_page_type_archive;
	}

	/**
	 * Retrieve all registered page type archive templates.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_archive_templates() {
		$templates = array();

		if ( ! empty( $this->types ) ) {
			$templates = array_filter( wp_list_pluck( $this->types, 'archive_template' ) );
		}

		/**
		 * List of page type archive templates.
		 *
		 * @since 1.0.0
		 *
		 * @param array $templates List of template files.
		 */
		return apply_filters( $this->theme->prefix . '_archive_page_templates', $templates );
	}

	/**
	 * Retrieve the page type archive template for a specific page.
	 *
	 * @since 1.0.0
	 *
	 * @param int|WP_Post $post Optional. Post ID or object. Defaults to the
	 *                          current global post.
	 * @return string
	 */
	public function get_archive_template( $post = null ) {
		$post     = get_post( $post );
		$template = '';

		if ( $this->is_type() ) {
			$page_type = $this->get_type( $post );
			$template = $this->get_type_arg( 'archive_template', $page_type );
		}

		return $template;
	}

	/*
	 * Hook callbacks.
	 */

	/**
	 * Load the singular template for a registered page type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Template file.
	 * @return string
	 */
	public function template_overrides( $template ) {
		$post = get_post();

		if ( ! $this->is_type() ) {
			return $template;
		}

		$new_template    = '';
		$single_template = $this->get_type_arg( 'single_template' );

		if ( ! empty( $single_template ) ) {
			$new_template = locate_template( (array) $single_template );
		}

		// Filter adjacent post links to only return adjacent child pages.
		add_filter( 'get_next_post_where',     array( $this, 'adjacent_post_where_clause' ) );
		add_filter( 'get_previous_post_where', array( $this, 'adjacent_post_where_clause' ) );
		add_filter( 'get_next_post_sort',      array( $this, 'adjacent_post_sort_clause' ) );
		add_filter( 'get_previous_post_sort',  array( $this, 'adjacent_post_sort_clause' ) );

		return empty( $new_template ) ? $template : $new_template;
	}

	/**
	 * Add registered page type classes for the body element.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes List of HTML classes.
	 * @return array
	 */
	public function body_classes( $classes ) {
		if ( $type = $this->get_type() ) {
			$args = $this->types[ $type ];

			if ( $this->is_archive() && ! empty( $args['archive_body_class'] ) ) {
				$classes = array_merge( $classes, (array) $args['archive_body_class'] );
			} elseif ( $this->is_type() && ! empty( $args['single_body_class'] ) ) {
				$classes = array_merge( $classes, (array) $args['single_body_class'] );
			}

			$classes = array_unique( array_filter( $classes ) );
		}

		return $classes;
	}

	/**
	 * Filter the adjacent posts WHERE clause.
	 *
	 * Adjacent pages are determined within the context of a parent page.
	 *
	 * @since 3.1.0
	 *
	 * @see get_adjacent_post()
	 *
	 * @param string $where WHERE clause.
	 * @return string
	 */
	public function adjacent_post_where_clause( $where ) {
		global $wpdb;

		$post      = get_post();
		$previous  = ( 0 === strpos( current_filter(), 'get_previous_post_' ) );
		$adjacent  = $previous ? 'previous' : 'next';
		$operation = $previous ? '<' : '>';
		$order     = $previous ? 'DESC' : 'ASC';

		$where = $wpdb->prepare(
			"WHERE p.menu_order $operation %s AND p.post_type = %s AND p.post_parent = %d AND p.post_status = 'publish'",
			$post->menu_order,
			$post->post_type,
			$post->post_parent
		);

		return $where;
	}

	/**
	 * Filter the adjacent posts ORDER BY clause.
	 *
	 * @since 3.1.0
	 *
	 * @see get_adjacent_post()
	 *
	 * @param string $sort ORDER BY clause.
	 * @return string
	 */
	public function adjacent_post_sort_clause( $sort ) {
		$post     = get_post();
		$previous = ( 0 === strpos( current_filter(), 'get_previous_post_' ) );
		$order    = $previous ? 'DESC' : 'ASC';

		$sort = "ORDER BY p.menu_order $order LIMIT 1";

		return $sort;
	}

	/**
	 * Filter page type archive titles for WPCOM archive pages.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $title Archive title.
	 * @param int|WP_Post $post Post ID or object.
	 * @return string
	 */
	public function archive_title( $title, $post = null ) {
		$post = get_post( $post );

		if ( $this->is_type( '', $post ) ) {
			$title = get_the_title( $post->post_parent );
		}

		return $title;
	}

	/*
	 * Protected methods.
	 */

	/**
	 * Retrieve a page type argument.
	 *
	 * @since 1.0.0
	 *
	 * @param string $arg Argument key.
	 * @param string $type Optional. Page type name. Defaults to the page type
	 *                     for the current global post object.
	 * @return mixed
	 */
	protected function get_type_arg( $arg, $type = '' ) {
		$type  = empty( $type ) ? $this->get_type() : $type;
		$value = null;

		if ( isset( $this->types[ $type ][ $arg ] ) ) {
			$value = $this->types[ $type ][ $arg ];
		}

		return $value;
	}
}
