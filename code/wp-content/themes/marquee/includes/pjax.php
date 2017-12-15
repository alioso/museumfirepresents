<?php
/**
 * Marquee PJAX integration.
 *
 * @package Marquee
 * @since 1.0.0
 */

/**
 * Whether the current request is PJAX.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function marquee_is_pjax_request() {
	return isset( $_SERVER['HTTP_X_PJAX'] ) && 'true' === $_SERVER['HTTP_X_PJAX']; // WPCS: Input var OK.
}

/**
 * Handle PJAX requests.
 *
 * @since 1.0.0
 */
function marquee_pjax_handle_request() {
	if ( marquee_is_pjax_request() ) {
		ob_start( 'marquee_pjax_response' );
	}
}
add_action( 'template_redirect', 'marquee_pjax_handle_request', 0 );

/**
 * Modify the response body for PJAX requests.
 *
 * Sends back the page title, main content, and sets the page state in an
 * embedded script tag.
 *
 * Page state data includes:
 * - Body classes.
 * - Scripts and styles enqueued for the current request.
 * - Customize URL.
 *
 * @since 1.0.0
 *
 * @global $wp_scripts
 * @global $wp_styles
 *
 * @param string $output Output buffer contents.
 * @return string
 */
function marquee_pjax_response( $output ) {
	global $wp_scripts, $wp_styles;

	preg_match( '#<title>[\s\S]*?</title>#', $output, $title );
	preg_match( '#<body[^>]+class=[\'"](?P<classes>[^\'"]+)#', $output, $body_classes );
	preg_match( '#<!--pjax-content-->([\s\S]+?)<!--pjax-content-->#', $output, $main );
	preg_match( '#<li id="wp-admin-bar-edit">([\s\S]+?)</li>#', $output, $edit_link );

	$scripts = new Marquee_Dependencies_Scripts( $wp_scripts );
	$styles  = new Marquee_Dependencies_Styles( $wp_styles );

	$state = array(
		'bodyClasses'  => $body_classes['classes'],
		'customizeUrl' => esc_url( marquee_pjax_get_customizer_url() ),
		'editLink'     => empty( $edit_link[1] ) ? null : $edit_link[1],
		'scripts'      => $scripts->get_loaded_data(),
		'styles'       => $styles->get_loaded_data(),
	);

	return sprintf(
		'<html><head>%1$s</head><body>%2$s%3$s</body></html>',
		$title[0],
		$main[1],
		sprintf( '<script type="application/json" id="pjax-data">%s</script>', wp_json_encode( $state ) )
	);
}

/**
 * Retrieve the Customizer URL for the current request.
 *
 * @since 1.0.0
 * @see wp_admin_bar_customize_menu()
 *
 * @return string
 */
function marquee_pjax_get_customizer_url() {
	// Don't show for users who can't access the customizer or when in the admin.
	if ( ! current_user_can( 'customize' ) || is_admin() ) {
		return;
	}

	$current_url   = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // WPCS: Input var OK.
	$current_url   = remove_query_arg( '_pjax', $current_url );
	$customize_url = add_query_arg( 'url', urlencode( $current_url ), wp_customize_url() );

	return $customize_url;
}

/**
 * Keep track of scripts and styles that are loaded.
 *
 * New scripts and styles are loaded dynamicically during partial page
 * refreshes.
 *
 * WordPress core prints footer scripts at priority 20.
 *
 * @since 1.0.0
 *
 * @see wp_print_footer_scripts()
 * @global $wp_scripts
 * @global $wp_scripts
 */
function marquee_pjax_dependencies() {
	global $wp_scripts, $wp_styles;

	$scripts = new Marquee_Dependencies_Scripts( $wp_scripts );
	$styles  = new Marquee_Dependencies_Styles( $wp_styles );
	?>
	<script type="text/javascript">
	marquee.scripts.markAsDone( <?php echo wp_json_encode( $scripts->get_loaded_handles() ); ?> );
	marquee.styles.markAsDone( <?php echo wp_json_encode( $styles->get_loaded_handles() ); ?> );
	</script>
	<?php
}
add_action( 'wp_footer', 'marquee_pjax_dependencies', 21 );
