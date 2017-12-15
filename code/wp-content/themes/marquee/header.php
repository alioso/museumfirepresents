<?php
/**
 * The template for displaying the theme header.
 *
 * @package Marquee
 * @since 1.0.0
 */
?><!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?> <?php marquee_body_schema(); ?>>

	<?php do_action( 'marquee_before' ); ?>

	<div id="viewport-pane">
		<div id="page" class="hfeed site">
			<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'marquee' ); ?></a>

			<?php do_action( 'marquee_header_before' ); ?>

			<?php get_template_part( 'templates/parts/site-header' ); ?>

			<?php do_action( 'marquee_header_after' ); ?>

			<div id="content" class="site-content"><!--pjax-content-->

				<?php do_action( 'marquee_main_before' ); ?>
