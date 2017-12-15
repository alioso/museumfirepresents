<?php
/**
 * AudioTheme integration.
 *
 * @package Marquee
 * @since 1.0.0
 * @link https://audiotheme.com/
 */

/**
 * Set up theme defaults and register support for AudioTheme features.
 *
 * @since 1.0.0
 */
function marquee_audiotheme_setup() {
	// Add AudioTheme automatic updates support.
	add_theme_support( 'audiotheme-automatic-updates' );

	// Register nav menus.
	register_nav_menus( array(
		'audiotheme_gig'    => esc_html__( 'Gigs Menu',    'marquee' ),
		'audiotheme_record' => esc_html__( 'Records Menu', 'marquee' ),
		'audiotheme_video'  => esc_html__( 'Videos Menu',  'marquee' ),
	) );

	// Add support for AudioTheme widgets.
	add_theme_support( 'audiotheme-widgets', array(
		'record',
		'track',
		'upcoming-gigs',
		'video',
	) );

	// Remove AudiotTheme video wrappers.
	remove_filter( 'embed_oembed_html', 'audiotheme_oembed_html', 10 );
	remove_filter( 'embed_handler_html', 'audiotheme_oembed_html', 10 );
}
add_action( 'after_setup_theme', 'marquee_audiotheme_setup', 11 );

/**
 * Enqueue assets for AudioTheme features.
 *
 * @since 1.0.0
 */
function marquee_audiotheme_enqueue_assets() {
	wp_enqueue_style(
		'marquee-audiotheme',
		get_template_directory_uri() . '/assets/css/audiotheme.css',
		array( 'marquee-style' )
	);

	wp_style_add_data( 'marquee-audiotheme', 'rtl', 'replace' );

	if ( is_singular( array( 'audiotheme_record', 'audiotheme_track' ) ) ) {
		wp_enqueue_script( 'marquee-tracklists' );
	}
}
add_action( 'wp_enqueue_scripts', 'marquee_audiotheme_enqueue_assets', 15 );


/*
 * Plugin hooks.
 * -----------------------------------------------------------------------------
 */

/**
 * Activate default archive setting fields.
 *
 * @since 1.0.0
 *
 * @param array  $fields List of default fields to activate.
 * @param string $post_type Post type archive.
 * @return array
 */
function marquee_audiotheme_archive_settings_fields( $fields, $post_type ) {
	if ( ! in_array( $post_type, array( 'audiotheme_record', 'audiotheme_video' ) ) ) {
		return $fields;
	}

	$fields['columns'] = array(
		'choices' => array( 1, 2 ),
		'default' => 2,
	);

	$fields['posts_per_archive_page'] = true;

	return $fields;
}
add_filter( 'audiotheme_archive_settings_fields', 'marquee_audiotheme_archive_settings_fields', 10, 2 );

/**
 * Disable Jetpack Infinite Scroll on AudioTheme post types.
 *
 * @since 1.0.0
 *
 * @param bool $supported Whether Infinite Scroll is supported for the current request.
 * @return bool
 */
function marquee_audiotheme_infinite_scroll_archive_supported( $supported ) {
	$post_type = get_post_type() ? get_post_type() : get_query_var( 'post_type' );

	if ( $post_type && false !== strpos( $post_type, 'audiotheme_' ) ) {
		$supported = false;
	}

	return $supported;
}
add_filter( 'infinite_scroll_archive_supported', 'marquee_audiotheme_infinite_scroll_archive_supported' );


/*
 * Theme hooks.
 * -----------------------------------------------------------------------------
 */

/**
 * Add classes to archive block grids.
 *
 * @since 1.0.0
 *
 * @param array $classes List of HTML classes.
 * @return array
 */
function marquee_audiotheme_archive_block_grid_classes( $classes ) {
	if (
		is_post_type_archive( 'audiotheme_record' ) ||
		is_tax( 'audiotheme_record_type' )
	) {
		$classes[] = 'block-grid-' . get_audiotheme_archive_meta( 'columns', true, 2 );
	}

	if (
		is_post_type_archive( 'audiotheme_video' ) ||
		is_tax( 'audiotheme_video_category' )
	) {
		$classes[] = 'block-grid-' . get_audiotheme_archive_meta( 'columns', true, 2 );
		$classes[] = 'block-grid--16x9';
	}

	return $classes;
}
add_filter( 'marquee_block_grid_classes', 'marquee_audiotheme_archive_block_grid_classes' );

/**
 * Return a set of recent gigs.
 *
 * @since  1.0.0
 */
function marquee_audiotheme_recent_gigs_query() {
	$args = array(
		'order'          => 'desc',
		'posts_per_page' => 5,
		'meta_query'     => array(
			array(
				'key'     => '_audiotheme_gig_datetime',
				'value'   => current_time( 'mysql' ),
				'compare' => '<=',
				'type'    => 'DATETIME',
			),
		),
	);

	return new Audiotheme_Gig_Query( apply_filters( 'marquee_recent_gigs_query_args', $args ) );
}

/**
 * Display a track's duration.
 *
 * @since 1.0.0
 *
 * @param int $track_id Track ID.
 */
function marquee_audiotheme_track_length( $track_id = 0 ) {
	$track_id = empty( $track_id ) ? get_the_ID() : $track_id;
	$length   = get_audiotheme_track_length( $track_id );

	if ( empty( $length ) ) {
		$length = esc_html_x( '-:--', 'default track length', 'marquee' );
	}

	echo esc_html( $length );
}


/*
 * Template tags.
 * -----------------------------------------------------------------------------
 */

/**
 * Add HTML attributes to a track element.
 *
 * @since 1.0.0
 *
 * @param int $track_id Optional. The track ID. Defaults to the current track in the loop.
 */
function marquee_track_attributes( $track_id = 0 ) {
	$track = get_post( $track_id );

	$classes = 'track';
	if ( get_audiotheme_track_file_url( $track->ID ) ) {
		$classes .= ' is-playable js-play-record';
	}

	$attributes = array(
		'class'          => $classes,
		'itemprop'       => 'track',
		'itemtype'       => 'http://schema.org/MusicRecording',
		'data-record-id' => absint( $track->post_parent ),
		'data-track-id'  => absint( $track->ID ),
	);

	if ( is_singular( 'audiotheme_track' ) ) {
		$attributes['data-single'] = true;
	}

	foreach ( $attributes as $key => $value ) {
		printf(
			' %1$s="%2$s"',
			$key, // WPCS: XSS OK.
			esc_attr( $value )
		);
	}
}

/**
 * Display a track's title.
 *
 * This is for backward compatibility with versions of AudioTheme prior to
 * 2.1.0.
 *
 * @since 1.2.2
 *
 * @param int|WP_Post $post Optional. Post ID or object.
 * @param array
 */
function marquee_audiotheme_track_title( $post = 0, $args = array() ) {
	$post = get_post( $post );

	if ( function_exists( 'get_audiotheme_track_title' ) ) {
		echo get_audiotheme_track_title( $post, $args );
	} else {
		printf(
			'<a href="%s" class="track-title" itemprop="url"><span itemprop="name">%s</span></a>',
			esc_url( get_permalink( $post ) ),
			get_the_title( $post )
		);
	}
}



/*
 * AJAX callbacks.
 * -----------------------------------------------------------------------------
 */

/**
 * AJAX callback to retrieve data about a record's tracks.
 *
 * @since 1.0.0
 */
function marquee_audiotheme_ajax_get_record_data() {
	$record_id = absint( $_GET['record_id'] ); // WPCS: Input var OK.
	$tracks    = marquee_audiotheme_get_track_data( $record_id );

	if ( empty( $tracks ) ) {
		wp_send_json_error();
	}

	wp_send_json_success( array(
		'tracks' => $tracks,
	) );
}
add_action( 'wp_ajax_marquee_get_record_data',        'marquee_audiotheme_ajax_get_record_data' );
add_action( 'wp_ajax_nopriv_marquee_get_record_data', 'marquee_audiotheme_ajax_get_record_data' );

/**
 * Retrieve data about a record's tracks.
 *
 * @since 1.0.0
 *
 * @param int $record_id Record post ID.
 * @return array
 */
function marquee_audiotheme_get_track_data( $record_id ) {
	$tracks = array();
	$posts  = get_audiotheme_record_tracks( $record_id );

	if ( empty( $posts ) ) {
		return $tracks;
	}

	foreach ( $posts as $track ) {
		$data   = array();
		$track  = get_post( $track );
		$record = get_post( $track->post_parent );

		$data['track_id'] = $track->ID;
		$data['title']    = $track->post_title;
		$data['artist']   = get_audiotheme_track_artist( $track->ID );
		$data['album']    = $record->post_title;

		$data['src']      = get_audiotheme_track_file_url( $track->ID );
		$data['length']   = get_audiotheme_track_length( $track->ID );

		if ( $thumbnail_id = get_audiotheme_track_thumbnail_id( $track ) ) {
			$image = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail' );
			$data['artwork'] = $image[0];
		}

		$data['id']       = md5( $data['artist'] . $data['title'] . $data['src'] );
		$data['recordId'] = $record->ID;
		$data['trackId']  = $track->ID;

		$tracks[] = $data;
	}

	return $tracks;
}

/**
 * Add helpful nav menu classes.
 *
 * WordPress doesn't strip query strings when comparing custom menu item URLs
 * to the current URL, so PJAX requests never match menu item URLs. This method
 * strips the PJAX query argument from the current URL before comparing the
 * URLs to attempt to add the `.current-menu-item` class to appropriate menu
 * items.
 *
 * This is useful for the post type menus on AudioTheme archives to prevent them
 * from becoming unusuable in certain situations. See #57.
 *
 * @since 1.1.1
 *
 * @see _wp_menu_item_classes_by_context()
 *
 * @param array $items List of menu items.
 * @param array $args Menu display args.
 * @return array
 */
function marquee_audiotheme_nav_menu_classes( $items, $args ) {
	global $wp_rewrite;

	$_root_relative_current = untrailingslashit( $_SERVER['REQUEST_URI'] );
	$current_url            = remove_query_arg( '_pjax', set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_root_relative_current ) );
	$_indexless_current     = untrailingslashit( preg_replace( '/' . preg_quote( $wp_rewrite->index, '/' ) . '$/', '', $current_url ) );

	foreach ( $items as $key => $item ) {
		if ( 'custom' == $item->object && isset( $_SERVER['HTTP_HOST'] ) ) {
			$raw_item_url = strpos( $item->url, '#' ) ? substr( $item->url, 0, strpos( $item->url, '#' ) ) : $item->url;
			$item_url     = set_url_scheme( untrailingslashit( $raw_item_url ) );

			if ( $raw_item_url && in_array( $item_url, array( $current_url, $_indexless_current, $_root_relative_current ) ) ) {
				$items[ $key ]->classes[] = 'current-menu-item';
				$items[ $key ]->current = true;
			}
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'marquee_audiotheme_nav_menu_classes', 10, 3 );
