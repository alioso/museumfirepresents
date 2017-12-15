<?php
/**
 * Template Name: Landing Page
 *
 * A page template for placing the emphasis on a call-to-action. Works well as
 * an alternative to the default page template on the homepage.
 *
 * @package Marquee
 * @since 1.0.0
 */

get_header();
?>

<main id="primary" class="content-area" role="main" itemprop="mainContentOfPage">

	<?php do_action( 'marquee_main_top' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'templates/parts/content', 'page' ); ?>

		<?php comments_template( '', true ); ?>

	<?php endwhile; ?>

	<?php do_action( 'marquee_main_bottom' ); ?>

</main>

<?php
get_footer();
