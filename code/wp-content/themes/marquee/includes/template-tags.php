<?php
/**
 * Custom template tags.
 *
 * @package Marquee
 * @since 1.0.0
 */

if ( ! function_exists( 'marquee_site_branding' ) ) :
/**
 * Display the site logo, title, and description.
 *
 * @since 1.0.0
 */
function marquee_site_branding() {
	$output = '';

	// Site logo.
	$output .= marquee_theme()->logo->html();

	// Site title.
	$output .= sprintf(
		'<h1 class="site-title"><a href="%1$s" rel="home">%2$s</a></h1>',
		esc_url( home_url( '/' ) ),
		get_bloginfo( 'name', 'display' )
	);

	// Site description.
	$output .= '<div class="site-description">' . get_bloginfo( 'description', 'display' ) . '</div>';

	echo '<div class="site-branding">' . $output . '</div>'; // WPCS: XSS OK.
}
endif;

if ( ! function_exists( 'marquee_comment_navigation' ) ) :
/**
 * Display navigation to next/previous comments when applicable.
 *
 * @since 1.0.0
 */
function marquee_comment_navigation() {
	// Are there comments to navigate through?
	if ( get_comment_pages_count() < 2 || ! get_option( 'page_comments' ) ) {
		return;
	}
	?>
	<nav class="navigation comment-navigation" role="navigation">
		<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'marquee' ); ?></h2>
		<div class="nav-links">
			<?php
			if ( $prev_link = get_previous_comments_link( esc_html__( 'Older Comments', 'marquee' ) ) ) :
				printf( '<div class="nav-previous">%s</div>', marquee_allowed_tags( $prev_link ) );
			endif;

			if ( $next_link = get_next_comments_link( esc_html__( 'Newer Comments', 'marquee' ) ) ) :
				printf( '<div class="nav-next">%s</div>', marquee_allowed_tags( $next_link ) );
			endif;
			?>
		</div>
	</nav>
	<?php
}
endif;

if ( ! function_exists( 'marquee_content_navigation' ) ) :
/**
 * Display navigation to next/previous posts when applicable.
 *
 * @since 1.0.0
 */
function marquee_content_navigation() {
	if ( is_singular() ) :
		the_post_navigation( array(
			'prev_text' => marquee_allowed_tags( _x( 'Prev <span class="screen-reader-text">Post: %title</span>', 'Previous post link', 'marquee' ) ),
			'next_text' => marquee_allowed_tags( _x( 'Next <span class="screen-reader-text">Post: %title</span>', 'Next post link', 'marquee' ) ),
		) );
	else :
		the_posts_navigation( array(
			'prev_text' => esc_html__( 'Older', 'marquee' ),
			'next_text' => esc_html__( 'Newer', 'marquee' ),
		) );
	endif;
}
endif;

if ( ! function_exists( 'marquee_posts_navigation' ) ) :
/**
 * Display navigation to next/previous posts when applicable.
 *
 * @since 1.1.1
 *
 * @param array $args {
 *     Optional. Default posts navigation arguments. Default empty array.
 *
 *     @type string $class              HTML class to append to the posts container.
 *     @type string $prev_text          Anchor text to display in the previous posts link.
 *                                      Default 'Older'.
 *     @type string $next_text          Anchor text to display in the next posts link.
 *                                      Default 'Newer'.
 *     @type string $screen_reader_text Screen reader text for nav element.
 *                                      Default 'Posts navigation'.
 * }
 * @return string Markup for posts links.
 */
function marquee_posts_navigation( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'class'              => '',
		'prev_text'          => esc_html__( 'Older', 'marquee' ),
		'next_text'          => esc_html__( 'Newer', 'marquee' ),
		'screen_reader_text' => esc_html__( 'Posts navigation', 'marquee' ),
	) );

	$navigation = get_the_posts_navigation( $args );

	if ( ! empty( $args['class'] ) ) {
		// Inject a custom class into the container.
		$navigation = preg_replace(
			'/class=(?P<quote>[\'"])(.*?posts-navigation.*?)(?(quote)(?P=quote))/',
			'class=$1$2 ' . $args['class'] . '$1',
			$navigation
		);
	}

	echo $navigation;
}
endif;

if ( ! function_exists( 'marquee_post_type_navigation' ) ) :
/**
 * Display navigation menu for a post type archive.
 *
 * Navigation menus need to be registered using register_nav_menus() with the
 * location name set as the post type name.
 *
 * @since 1.0.0
 *
 * @param string $post_type Optional. Post type string.
 */
function marquee_post_type_navigation( $post_type = '' ) {
	$post_type = empty( $post_type ) ? get_post_type() : $post_type;

	$args = apply_filters( 'marquee_post_type_navigation_args', array(
		'theme_location' => $post_type,
		'container'      => false,
		'menu_class'     => 'menu dropdown-group-items',
		'depth'          => 1,
		'fallback_cb'    => false,
	) );

	if ( ! $args['theme_location'] || ! has_nav_menu( $args['theme_location'] ) ) {
		return;
	}
	?>
	<nav class="navigation post-type-navigation dropdown-group" role="navigation">
		<h2 class="post-type-navigation-title dropdown-group-title"><?php echo esc_html( marquee_get_nav_menu_name( $args['theme_location'] ) ); ?></h2>
		<?php wp_nav_menu( $args );	?>
	</nav>
	<?php
}
endif;

if ( ! function_exists( 'marquee_get_nav_menu_name' ) ) :
/**
 * Get the name of a nav menu as set in the admin panel.
 *
 * @since 1.0.0
 *
 * @param string $theme_location Location of the corresponding menu.
 * @return string Name of the nav menu.
 */
function marquee_get_nav_menu_name( $theme_location ) {
	$locations = get_nav_menu_locations();

	if ( empty( $locations[ $theme_location ] ) ) {
		return '';
	}

	$menu = get_term( $locations[ $theme_location ], 'nav_menu' );

	return ( ! $menu || empty( $menu->name ) ) ? '' : $menu->name;
}
endif;

if ( ! function_exists( 'marquee_page_links' ) ) :
/**
 * Wrapper for wp_link_pages() to maintain consistent markup.
 *
 * @since 1.0.0
 *
 * @return string
 */
function marquee_page_links() {
	if ( ! is_singular() ) {
		return;
	}

	wp_link_pages( array(
		'before'      => '<nav class="page-links"><span class="page-links-title">' . esc_html__( 'Pages', 'marquee' ) . '</span>',
		'after'       => '</nav>',
		'link_before' => '<span class="page-links-number">',
		'link_after'  => '</span>',
		'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'marquee' ) . ' </span>%',
		'separator'   => '<span class="screen-reader-text">, </span>',
	) );
}
endif;

/**
 * Retrieve the URL for the blog.
 *
 * @since 1.0.0
 *
 * @return string
 */
function marquee_blog_url() {
	$show_on_front  = get_option( 'show_on_front' );
	$page_for_posts = get_option( 'page_for_posts' );

	if ( 'page' === $show_on_front && $page_for_posts ) {
		$url = get_the_permalink( $page_for_posts );
	} else {
		$url = home_url( '/' );
	}

	return apply_filters( 'marquee_blog_url', $url );
}

if ( ! function_exists( 'marquee_entry_title' ) ) :
/**
 * Display an entry title.
 *
 * Includes the link on archives.
 *
 * @since 1.0.0
 */
function marquee_entry_title() {
	$title  = get_the_title();

	if ( empty( $title ) ) {
		return;
	}

	if ( ! is_singular() ) {
		$title = sprintf(
			'<a class="permalink" href="%1$s" rel="bookmark" itemprop="url">%2$s</a>',
			esc_url( get_permalink() ),
			$title
		);
	}

	printf( '<h1 class="entry-title" itemprop="headline">%s</h1>', marquee_allowed_tags( $title ) );
}
endif;

if ( ! function_exists( 'marquee_get_entry_author' ) ) :
/**
 * Retrieve entry author.
 *
 * @since 1.0.0
 *
 * @return string
 */
function marquee_get_entry_author() {
	$html  = '<span class="entry-author author vcard" itemprop="author" itemscope itemtype="http://schema.org/Person">';
	$html .= sprintf(
		'<a class="url fn n" href="%1$s" rel="author" itemprop="url"><span itemprop="name">%2$s</span></a>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_html( get_the_author() )
	);
	$html .= '</span>';

	return $html;
}
endif;

if ( ! function_exists( 'marquee_get_entry_date' ) ) :
/**
 * Retrieve HTML with meta information for the current post-date/time.
 *
 * @since 1.0.0
 *
 * @param bool $updated Optional. Whether to print the updated time, too. Defaults to true.
 * @return string
 */
function marquee_get_entry_date( $updated = true ) {
	$time_string = '<time class="entry-time published" datetime="%1$s" itemprop="datePublished">%2$s</time>';

	// To appease rich snippets, an updated class needs to be defined.
	// Default to the published time if the post has not been updated.
	if ( $updated ) {
		if ( get_the_time( 'U' ) === get_the_modified_time( 'U' ) ) {
			$time_string .= '<time class="entry-time updated" datetime="%1$s">%2$s</time>';
		} else {
			$time_string .= '<time class="entry-time updated" datetime="%3$s" itemprop="dateModified">%4$s</time>';
		}
	}

	return sprintf(
		$time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);
}
endif;

if ( ! function_exists( 'marquee_posted_by' ) ) :
/**
 * Display post author byline.
 *
 * @since 1.0.0
 */
function marquee_posted_by() {
	?>
	<span class="posted-by byline">
		<?php
		/* translators: %s: Author name */
		echo marquee_allowed_tags( sprintf( __( '<span class="sep">By</span> %s', 'marquee' ), marquee_get_entry_author() ) );
		?>
	</span>
	<?php
}
endif;

if ( ! function_exists( 'marquee_posted_on' ) ) :
/**
 * Display post date/time with link.
 *
 * @since 1.0.0
 */
function marquee_posted_on() {
	?>
	<span class="posted-on">
		<?php
		$html = sprintf(
			'<span class="entry-date"><a href="%1$s" rel="bookmark">%2$s</a></span>',
			esc_url( get_permalink() ),
			marquee_get_entry_date()
		);

		/* translators: %s: Publish date */
		echo marquee_allowed_tags( sprintf( __( '<span class="sep">on</span> %s', 'marquee' ), $html ) );
		?>
	</span>
	<?php
}
endif;

if ( ! function_exists( 'marquee_entry_comments_link' ) ) :
/**
 * Display linked entry comment count.
 *
 * @since 1.0.0
 */
function marquee_entry_comments_link() {
	if ( is_singular() || post_password_required() || ! comments_open() || ! get_comments_number() ) {
		return;
	}

	echo '<span class="entry-comments-link">';
	comments_popup_link(
		esc_html__( 'Leave a comment', 'marquee' ),
		esc_html__( '1 Comment', 'marquee' ),
		esc_html__( '% Comments', 'marquee' )
	);
	echo '</span>';
}
endif;

if ( ! function_exists( 'marquee_entry_terms' ) ) :
/**
 * Display terms for a given taxonomy.
 *
 * @since 1.0.0
 *
 * @param array $taxonomies Optional. List of taxonomy objects with labels.
 */
function marquee_entry_terms( $taxonomies = array() ) {
	if ( ! is_singular() || post_password_required() ) {
		return;
	}

	echo wp_kses_post( marquee_get_entry_terms( $taxonomies ) );
}
endif;

if ( ! function_exists( 'marquee_get_entry_terms' ) ) :
/**
 * Retrieve terms for a given taxonomy.
 *
 * @since 1.0.0
 *
 * @param array       $taxonomies Optional. List of taxonomy objects with labels.
 * @param int|WP_Post $post Optional. Post ID or object. Defaults to the current post.
 */
function marquee_get_entry_terms( $taxonomies = array(), $post = null ) {
	$default = array(
		'category' => esc_html__( 'Posted In:', 'marquee' ),
		'post_tag' => esc_html__( 'Tagged:', 'marquee' ),
	);

	// Set default taxonomies if empty or not an array.
	if ( ! $taxonomies || ! is_array( $taxonomies ) ) {
		$taxonomies = $default;
	}

	// Allow plugins and themes to override taxonomies and labels.
	$taxonomies = apply_filters( 'marquee_entry_terms_taxonomies', $taxonomies );

	// Return early if the taxonomies are empty or not an array.
	if ( ! $taxonomies || ! is_array( $taxonomies ) ) {
		return;
	}

	$post   = get_post( $post );
	$output = '';

	// Get object taxonomy list to validate taxonomy later on.
	$object_taxonomies = get_object_taxonomies( get_post_type() );

	// Loop through each taxonomy and set up term list html.
	foreach ( (array) $taxonomies as $taxonomy => $label ) {
		// Continue if taxonomy is not in the object taxonomy list.
		if ( ! in_array( $taxonomy, $object_taxonomies ) ) {
			continue;
		}

		// Get term list.
		$term_list = get_the_term_list( $post->ID, $taxonomy, '', ', ', '' );

		// Continue if there is not one or more terms in the taxonomy.
		if ( ! $term_list || ! marquee_theme()->template->has_multiple_terms( $taxonomy ) ) {
			continue;
		}

		if ( $label ) {
			$label = sprintf( '<h3 class="term-title screen-reader-text">%s</h3>', $label );
		}

		// Set term list output html.
		$output .= sprintf(
			'<div class="term-group term-group--%1$s">%2$s%3$s</div>',
			esc_attr( $taxonomy ),
			$label,
			$term_list
		);
	}

	// Return if no term lists were created.
	if ( empty( $output ) ) {
		return;
	}

	return sprintf( '<div class="entry-terms">%s</div>', $output );
}
endif;

/**
 * Print classes needed to render a block grid.
 *
 * @since 1.0.0
 *
 * @param array $classes List of HTML classes.
 */
function marquee_block_grid_classes( $classes = array() ) {
	// Split a string.
	if ( ! empty( $classes ) && ! is_array( $classes ) ) {
		$classes = preg_split( '#\s+#', $classes );
	}

	array_unshift( $classes, 'block-grid', 'block-grid--gutters' );
	$classes = apply_filters( 'marquee_block_grid_classes', $classes );

	echo esc_attr( implode( ' ', $classes ) );
}

/**
 * Retrieve the tracks for the site-wide player.
 *
 * Uses values set by a filter, otherwise uses an option from the Customizer.
 *
 * @since 1.0.0
 * @see wp_get_playlist()
 *
 * @return array Array of tracks.
 */
function marquee_get_player_tracks() {
	return marquee_theme()->template->get_tracks( 'marquee_player', get_theme_mod( 'marquee_attachment_ids' ) );
}

/**
 * Whether the player is visible for the current request.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function marquee_is_player_active() {
	static $is_active;

	if ( null === $is_active ) {
		$tracks = marquee_get_player_tracks();

		// Only visible if tracks have been assigned.
		$is_active = (bool) apply_filters( 'marquee_is_player_active', ! empty( $tracks ) );
	}

	return $is_active;
}

/**
 * Display body schema markup.
 *
 * @since 1.0.0
 */
function marquee_body_schema() {
	$schema = 'http://schema.org/';
	$type   = 'WebPage';

	if ( is_home() || is_singular( 'post' ) || is_category() || is_tag() ) {
		$type = 'Blog';
	} elseif ( is_author() ) {
		$type = 'ProfilePage';
	} elseif ( is_search() ) {
		$type = 'SearchResultsPage';
	}

	$type = apply_filters( 'marquee_body_schema', $type );

	printf(
		'itemscope="itemscope" itemtype="%1$s%2$s"',
		esc_attr( $schema ),
		esc_attr( $type )
	);
}

if ( ! function_exists( 'marquee_allowed_tags' ) ) :
/**
 * Allow only the allowedtags array in a string.
 *
 * @since 1.0.0
 *
 * @link https://www.tollmanz.com/wp-kses-performance/
 *
 * @param  string $string The unsanitized string.
 * @return string         The sanitized string.
 */
function marquee_allowed_tags( $string ) {
	global $allowedtags;

	$theme_tags = array(
		'a'    => array(
			'href'     => true,
			'itemprop' => true,
			'rel'      => true,
			'title'    => true,
		),
		'span' => array(
			'class' => true,
		),
		'time' => array(
			'class'    => true,
			'datetime' => true,
			'itemprop' => true,
		),
	);

	return wp_kses( $string, array_merge( $allowedtags, $theme_tags ) );
}
endif;

/**
 * Theme credits text.
 *
 * @since 1.0.0
 */
function marquee_credits() {
	echo marquee_get_credits();
}

if ( ! function_exists( 'marquee_get_credits' ) ) :
/**
 * Retrieve theme credits text.
 *
 * @since 1.0.0
 *
 * @return string
 */
function marquee_get_credits() {
	$text = marquee_allowed_tags( sprintf(
		__( '<a href="%s">Marquee music theme</a> by AudioTheme.', 'marquee' ),
		'https://audiotheme.com/view/marquee/'
	) );

	return apply_filters( 'marquee_credits', $text );
}
endif;
