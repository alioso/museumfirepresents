<?php
/**
 * Template functions and tags.
 *
 * @since 3.0.0
 *
 * @package Cedaro\Theme
 * @copyright Copyright (c) 2014, Cedaro
 * @license GPL-2.0+
 */

/**
 * Class with template loading methods and tags.
 *
 * @package Cedaro\Theme
 * @since 3.0.0
 */
class Cedaro_Theme_Template {
	/**
	 * The theme object.
	 *
	 * @since 3.0.0
	 * @var Cedaro_Theme
	 */
	protected $theme;

	/**
	 * Constructor method.
	 *
	 * @since 3.0.0
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
	 * Retrieve the section title for an archive.
	 *
	 * @since 3.1.0
	 *
	 * @param int|WP_Post $post Optional. Post to get the archive title for.
	 *                          Defaults to the current post.
	 * @return string
	 */
	public function get_archive_link( $post = null ) {
		$post = get_post( $post );

		if ( is_singular( 'post' ) ) {
			if ( 'page' === get_option( 'show_on_front' ) ) {
				$link = get_permalink( get_option( 'page_for_posts' ) );
			} else {
				$link = home_url( '/' );
			}
		} elseif ( $this->theme->page_types->is_type( '', $post ) ) {
			$link = get_permalink( $post->post_parent );
		} else {
			$link = get_post_type_archive_link( $post->post_type );
		}

		/**
		 * Filter archive permalinks.
		 *
		 * @since 1.0.0
		 *
		 * @param string $link Archive permalink.
		 */
		return apply_filters( $this->theme->prefix . '_archive_link', $link );
	}

	/**
	 * Retrieve the tracks for a playlist.
	 *
	 * @since 3.0.0
	 * @see wp_get_playlist()
	 *
	 * @param string       $key            Theme mod or option key.
	 * @param array|string $attachment_ids Optional. Array or comma-separated string of attachment ids.
	 * @param string       $type           Optional. Whether to look for a theme_mod or option if the
	 *                                     second parameter is empty. Defaults to theme_mod.
	 * @return array Array of tracks.
	 */
	public function get_tracks( $key, $attachment_ids = array(), $type = 'theme_mod' ) {
		$key = sanitize_key( $key );

		if ( empty( $attachment_ids ) && 'theme_mod' === $type ) {
			$attachment_ids = get_theme_mod( $key, array() );
		} elseif ( empty( $attachment_ids ) && 'option' === $type ) {
			$attachment_ids = get_option( $key, array() );
		}

		$attachment_ids = is_array( $attachment_ids ) ? $attachment_ids : explode( ',', $attachment_ids );
		$attachment_ids = array_filter( $attachment_ids );

		$tracks = apply_filters( 'pre_' . $key . '_tracks', array(), $attachment_ids );

		// Check the option from the Customizer.
		if ( empty( $tracks ) && ! empty( $attachment_ids ) ) {
			$query = new WP_Query();
			$attachments = $query->query( array(
				'post__in'       => $attachment_ids,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'audio',
				'order'          => 'asc',
				'orderby'        => 'post__in',
				'posts_per_page' => 20,
			) );
		}

		if ( ! empty( $attachments ) ) {
			$supports_thumbs = ( current_theme_supports( 'post-thumbnails', 'attachment:audio' ) && post_type_supports( 'attachment:audio', 'thumbnail' ) );

			foreach ( $attachments as $attachment ) {
				$url   = wp_get_attachment_url( $attachment->ID );
				$ftype = wp_check_filetype( $url, wp_get_mime_types() );
				$track = array(
					'id'          => $attachment->ID,
					'src'         => $url,
					'type'        => $ftype['type'],
					'title'       => get_the_title( $attachment->ID ),
					'caption'     => wptexturize( $attachment->post_excerpt ),
					'description' => wptexturize( $attachment->post_content ),
				);

				$track['meta'] = array();
				$meta = wp_get_attachment_metadata( $attachment->ID );
				if ( ! empty( $meta ) ) {
					$keys = array( 'title', 'artist', 'band', 'album', 'genre', 'year', 'length', 'length_formatted' );
					foreach ( $keys as $key ) {
						$track['meta'][ $key ] = empty( $meta[ $key ] ) ? '' : $meta[ $key ];
					}
				}

				if ( $supports_thumbs ) {
					$id = get_post_thumbnail_id( $attachment->ID );
					if ( ! empty( $id ) ) {
						list( $src, $width, $height ) = wp_get_attachment_image_src( $id, 'full' );
						$track['image'] = compact( 'src', 'width', 'height' );
						list( $src, $width, $height ) = wp_get_attachment_image_src( $id, 'thumbnail' );
						$track['thumb'] = compact( 'src', 'width', 'height' );
					}
				}

				$tracks[] = $track;
			}
		}

		return apply_filters( $key . '_tracks', $tracks );
	}

	/**
	 * Whether a site has more than one term for a taxonomy.
	 *
	 * Searches for at least two terms in the taxonomy that have attached
	 * objects. The result is stored in a transient.
	 *
	 * @since 3.1.0
	 *
	 * @param string|array $taxonomy The taxonomies to retrieve terms from.
	 * @return bool
	 */
	public function has_multiple_terms( $taxonomy = 'category' ) {
		$transient = sprintf( $this->theme->prefix . '_%s_terms', $taxonomy );

		if ( false === ( $terms = get_transient( $transient ) ) ) {
			$terms = get_terms( $taxonomy, array(
				'fields'     => 'ids',
				'hide_empty' => true,
				'number'     => 2,
			) );

			$terms = count( $terms );
			set_transient( $transient, $terms );
		}

		return ( $terms > 1 );
	}
}
