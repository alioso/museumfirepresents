<?php
/**
 * The template for displaying a list of gigs.
 *
 * @package Marquee
 * @since 1.0.0
 */

get_header();
?>

<main id="primary" <?php audiotheme_archive_class( array( 'content-area', 'archive-gig' ) ); ?> role="main" itemprop="mainContentOfPage">

	<?php do_action( 'marquee_main_top' ); ?>

	<?php if ( have_posts() ) : ?>

		<header class="page-header">
			<?php the_audiotheme_archive_title( '<h1 class="page-title" Hitemprop="headline">', '</h1>' ); ?>
			<?php marquee_post_type_navigation( 'audiotheme_gig' ); ?>
			<?php the_audiotheme_archive_description( '<div class="page-content" itemprop="text">', '</div>' ); ?>
		</header>

		<?php get_template_part( 'audiotheme/gig/content', 'archive' ); ?>

	<?php else : ?>

		<?php get_template_part( 'audiotheme/gig/content', 'none' ); ?>

	<?php endif; ?>

	<?php do_action( 'marquee_main_bottom' ); ?>

</main>

<?php
get_footer();
