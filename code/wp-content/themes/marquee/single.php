<?php
/**
 * The template for displaying individual posts.
 *
 * @package Marquee
 * @since 1.0.0
 */

get_header();
?>

<main id="primary" class="content-area" role="main">

	<?php do_action( 'marquee_main_top' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'templates/parts/content', get_post_format() ); ?>

		<?php marquee_content_navigation(); ?>

		<?php comments_template( '', true ); ?>

	<?php endwhile; ?>

	<?php do_action( 'marquee_main_bottom' ); ?>

</main>

<?php
get_footer();
