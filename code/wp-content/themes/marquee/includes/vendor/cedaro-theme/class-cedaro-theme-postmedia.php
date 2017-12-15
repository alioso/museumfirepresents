<?php
/**
 * Post media functionality.
 *
 * @since 3.1.0
 *
 * @package Cedaro\Theme
 * @copyright Copyright (c) 2014, Cedaro
 * @license GPL-2.0+
 */

/**
 * Class for working with post formats and media.
 *
 * @package Cedaro\Theme
 * @since 3.1.0
 */
class Cedaro_Theme_PostMedia {
	/**
	 * Cached media lookups.
	 *
	 * @since 3.1.0
	 * @var array
	 */
	protected $cache = array();

	/**
	 * The theme object.
	 *
	 * @since 3.1.0
	 * @var Cedaro_Theme
	 */
	protected $theme;

	/**
	 * Constructor method.
	 *
	 * @since 3.1.0
	 *
	 * @param Cedaro_Theme $theme Cedaro theme instance.
	 */
	public function __construct( Cedaro_Theme $theme ) {
		$this->theme = $theme;
	}

	/**
	 * Return the post URL.
	 *
	 * @uses get_url_in_content() to get the URL in the post meta (if it exists) or
	 *     the first link found in the post content.
	 *
	 * Falls back to the post permalink if no URL is found in the post.
	 *
	 * @since 3.1.0
	 * @todo wp_extract_urls()
	 *
	 * @param int|WP_Post $post Optional. Post ID or object. Defaults to the current post.
	 * @return string     The Link format URL.
	 */
	public function get_link_url( $post = 0 ) {
		$post    = get_post( $post );
		$has_url = get_url_in_content( $post->post_content );
		return $has_url ? $has_url : apply_filters( 'the_permalink', get_permalink( $post->ID ) );
	}

	/**
	 * Retrieve an image for a post.
	 *
	 * @since 3.1.0
	 *
	 * @param int|WP_Post  $post Optional. Post ID or object. Defaults to the current post.
	 * @param string|array $size Optional. The size of the image to return. Defaults to large.
	 * @param array        $attr Optional. Attributes for the image tag.
	 * @return string      Image tag.
	 */
	public function get_image( $post = 0, $size = 'large', $attr = array() ) {
		$post = get_post( $post );
		$html = '';
		$url  = $this->get_image_url( $post, $size );

		if ( ! empty( $url ) ) {
			$attr = wp_parse_args( $attr, array(
				'src' => $url,
				'alt' => '',
			) );

			$attr = array_map( 'esc_attr', $attr );

			$html = '<img';
			foreach ( $attr as $name => $value ) {
				$html .= ' ' . $name . '="' . $value . '"';
			}
			$html .= '>';
		}

		return apply_filters( $this->theme->prefix . '_post_image_html', $html, $post, $url, $attr );
	}

	/**
	 * Extract and return the first image from passed content.
	 *
	 * @since 3.1.0
	 * @link https://core.trac.wordpress.org/browser/trunk/wp-includes/media.php?rev=24240#L2223
	 *
	 * @param string $content A string which might contain a URL.
	 * @return string The found URL.
	 */
	public function get_image_in_content( $content ) {
		$image = '';
		if ( ! empty( $content ) && preg_match( '#' . get_tag_regex( 'img' ) . '#i', $content, $matches ) && ! empty( $matches ) ) {
			// @todo Sanitize this.
			$image = $matches[0];
		}

		return $image;
	}

	/**
	 * Retrieve the image URL for a post.
	 *
	 * @since 3.1.0
	 *
	 * @see Cedaro_Theme_PostMedia::get_image()
	 * @todo Refactor to remove dependence on Cedaro_Theme_PostMedia::get_image()
	 *
	 * @param int|WP_Post  $post Optional. Post ID or object. Defaults to the current post.
	 * @param string|array $size Optional. The size of the image to return. Defaults to large.
	 * @return string      Image URL.
	 */
	public function get_image_url( $post = 0, $size = 'large' ) {
		$post = get_post( $post );
		$url  = $this->get_image_url_from_cache( $post, $size );

		if ( null !== $url ) {
			return apply_filters( $this->theme->prefix . '_post_image_url', $url, $post );
		}

		/*
		 * - Check for a featured image first.
 		 * - See if Jetpack has anything to say.
 		 * - Otherwise:
 		 *     - Check the post content for an image.
 		 *     - Check the post's attachments.
		 */
		if ( has_post_thumbnail( $post->ID ) ) {
			$data = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size );
			$url  = $data[0];
		} elseif ( class_exists( 'Jetpack_PostImages' ) && ( $data = Jetpack_PostImages::get_image( $post->ID ) ) ) {
			$url = $data['src'];
		} elseif ( ! class_exists( 'Jetpack_PostImages' ) ) {
			// Check the post content for an image.
			$html = $this->get_image_in_content( $post->post_content );
			if ( ! empty( $html ) && preg_match( '/src=[\'"]([^\'"]+)/', $html, $matches ) ) {
				$url = $matches[1];
			} else {
				// Check the post's attachments.
				$query = new WP_Query();
				$images = $query->query( array(
					'post_type'      => 'attachment',
					'post_parent'    => $post->ID,
					'post_mime_type' => 'image',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'orderby'        => 'menu_order',
					'order'          => 'asc',
				) );

				if ( count( $images ) ) {
					$data = wp_get_attachment_image_src( $images[0], $size );
					$url  = $data[0];
				}
			}
		}

		$this->cache_image_url( $post, $size, $url );

		return apply_filters( $this->theme->prefix . '_post_image_url', $url, $post );
	}

	/**
	 * Retrieve video related to a post.
	 *
	 * @since 3.1.0
	 * @uses $wp_embed
	 * @todo Consider using get_enclosed(), too?
	 * @todo Add support for the Crowd Favorite dealio.
	 *
	 * @param  int|WP_Post $post Optional. Post ID or object. Defaults to the current post in the loop.
	 * @return string      HTML <audio> tag or empty string.
	 */
	public function get_video( $post = 0 ) {
		global $wp_embed;

		$return_false_on_fail = $wp_embed->return_false_on_fail;
		$wp_embed->return_false_on_fail = true;

		$html = '';
		$post = get_post( $post );

		/*
		// Ask Jetpack first.
		// @todo Jetpack_Media_Meta_Extractor::extract( 1, $post->ID )
		if ( class_exists( 'Jetpack_Media_Summary' ) ) {
			$media = Jetpack_Media_Summary::get( $post->ID );

			if ( ! empty( $media['video'] ) ) {
				// @todo Handle VideoPress separately.
				$html = $wp_embed->shortcode( array(), $media['video'] );
			}
		}
		*/

		// Check for shortcodes.
		if ( empty( $html ) && preg_match_all( '/' . get_shortcode_regex() . '/s', $post->post_content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $shortcode ) {
				if ( in_array( $shortcode[2], array( 'video', 'embed', 'youtube', 'vimeo', 'hulu', 'ted', 'wpvideo' ) ) ) {
					$html = do_shortcode( $shortcode[0] );
				}
			}
		}

		// Check for autoembeds (links on their own lines) in the post content.
		if ( empty( $html ) && preg_match_all( '|^\s*(https?://[^\s"]+)\s*$|im', $post->post_content, $matches ) ) {
			foreach ( $matches[1] as $url ) {
				if ( $embed = $wp_embed->shortcode( array(), $url ) ) {
					$html = $embed;
					break;
				}
			}
		}

		// Check for video attachments.
		if ( empty( $html ) && ( $attached = get_attached_media( 'video' ) ) ) {
			$query = new WP_Query();
			$attachments = $query->query( array(
				'post_parent'    => $post->ID,
				'post_type'      => 'attachment',
				'post_mime_type' => 'video',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'orderby'        => 'menu_order',
				'order'          => 'asc',
			) );

			if ( ! empty( $attachments ) ) {
				$src = wp_get_attachment_url( $attachments[0] );
				$html = wp_video_shortcode( array( 'src' => $src ) );
			}
		}

		// Extract <video> tags from the post content.
		if ( empty( $html ) ) {
			$embedded = get_media_embedded_in_content( $post->post_content, array( 'video' ) );
			$html = empty( $embedded[0] ) ? '' : $embedded[0];
		}

		// Reset the WP_Embed::return_false_on_fail setting.
		$wp_embed->return_false_on_fail = $return_false_on_fail;

		return $html;
	}

	/**
	 * Retrieve audio related to a post.
	 *
	 * @since 3.1.0
	 * @uses $wp_embed
	 * @todo Consider using get_enclosed(), too?
	 * @todo Add support for the Crowd Favorite dealio.
	 *
	 * @param  int|WP_Post $post Optional. Post ID or object. Defaults to the current post in the loop.
	 * @return string HTML <audio> tag or empty string.
	 */
	public function get_audio( $post = 0 ) {
		global $wp_embed;

		$return_false_on_fail = $wp_embed->return_false_on_fail;
		$wp_embed->return_false_on_fail = true;

		$html = '';
		$post = get_post( $post );

		// Extract an [audio] shortcode from the post content.
		if ( has_shortcode( $post->post_content, 'audio' ) ) {
			if ( preg_match_all( '/' . get_shortcode_regex() . '/s', $post->post_content, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $shortcode ) {
					if ( 'audio' === $shortcode[2] ) {
						$html = do_shortcode( $shortcode );
						break;
					}
				}
			}
		}

		// Check for autoembeds (links on their own lines) in the post content.
		if ( empty( $html ) && preg_match_all( '|^\s*(https?://[^\s"]+)\s*$|im', $post->post_content, $matches ) ) {
			foreach ( $matches[1] as $url ) {
				$result = $wp_embed->shortcode( array(), $url );
				if ( 0 === strpos( $result, '[audio' ) ) {
					$html = do_shortcode( $result );
					break;
				}
			}
		}

		// Check for audio attachments.
		if ( empty( $html ) ) {
			$query = new WP_Query();
			$attachments = $query->query( array(
				'post_parent'    => $post->ID,
				'post_type'      => 'attachment',
				'post_mime_type' => 'audio',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'orderby'        => 'menu_order',
				'order'          => 'asc',
			) );

			if ( ! empty( $attachments ) ) {
				$src = wp_get_attachment_url( $attachments[0] );
				$html = wp_audio_shortcode( array( 'src' => $src ) );
			}
		}

		// Extract <audio> tags from the post content.
		if ( empty( $html ) ) {
			$embedded = get_media_embedded_in_content( $post->post_content, array( 'audio' ) );
			$html = empty( $embedded[0] ) ? '' : $embedded[0];
		}

		// Reset the WP_Embed::return_false_on_fail setting.
		$wp_embed->return_false_on_fail = $return_false_on_fail;

		return apply_filters( $this->theme->prefix . '_post_audio', $html, $post );
	}

	/**
	 * Cache a post's image URL.
	 *
	 * @since 3.1.0
	 *
	 * @param WP_Post      $post Post object.
	 * @param string|array $size Image size.
	 * @param string       $url  Image URL.
	 */
	protected function cache_image_url( $post, $size, $url ) {
		$size = is_array( $size ) ? implode( 'x', $size ) : $size;

		if ( ! isset( $this->cache[ $post->ID ]['image'][ $size ]['url'] ) ) {
			$this->cache[ $post->ID ]['image'][ $size ]['url'] = $url;
		}
	}

	/**
	 * Retrieve the image URL for a post from the cache.
	 *
	 * @since 3.1.0
	 *
	 * @param WP_Post      $post Post object.
	 * @param string|array $size Image size.
	 * @return string|null The image URL or null if it hasn't been previously cached.
	 */
	protected function get_image_url_from_cache( $post, $size ) {
		$url  = null;
		$size = is_array( $size ) ? implode( 'x', $size ) : $size;

		if ( isset( $this->cache[ $post->ID ]['image'][ $size ]['url'] ) ) {
			$url = $this->cache[ $post->ID ]['image'][ $size ]['url'];
		}

		return $url;
	}
}
