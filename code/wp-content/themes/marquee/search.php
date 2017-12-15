<?php
/**
 * The template used for displaying search results.
 *
 * @package Marquee
 * @since 1.0.0
 */

get_header();
?>

<main id="primary" class="content-area" role="main" itemprop="mainContentOfPage">

	<?php do_action( 'marquee_main_top' ); ?>

	<?php if ( have_posts() ) : ?>

		<header class="page-header">
			<h1 class="page-title"><?php esc_html_e( 'Search Results', 'marquee' ); ?></h1>

			<?php get_search_form(); ?>
		</header>



		<?php while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'templates/parts/content', 'search' ); ?>

		<?php endwhile; ?>

		<?php marquee_posts_navigation(); ?>

	<?php else : ?>

		<?php get_template_part( 'templates/parts/content-none', 'index' ); ?>

	<?php endif; ?>

	<?php do_action( 'marquee_main_bottom' ); ?>

</main>

<?php
get_footer();
