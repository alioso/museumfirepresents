<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Marquee
 * @since 1.0.0
 */

get_header();
?>

<main id="primary" class="content-area" role="main">

	<?php do_action( 'marquee_main_top' ); ?>

	<?php if ( have_posts() ) : ?>

		<header class="page-header">
			<?php the_archive_title( '<h1 class="page-title" itemprop="headline">', '</h1>' ); ?>
			<?php marquee_post_type_navigation( 'post' ); ?>
			<?php the_archive_description( '<div class="page-content lead" itemprop="text">', '</div>' ); ?>
		</header>

		<?php while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'templates/parts/content', get_post_format() ); ?>

		<?php endwhile; ?>

		<?php marquee_posts_navigation(); ?>

	<?php else : ?>

		<?php get_template_part( 'templates/parts/content', 'none' ); ?>

	<?php endif; ?>

	<?php do_action( 'marquee_main_bottom' ); ?>

</main>

<?php
get_footer();
