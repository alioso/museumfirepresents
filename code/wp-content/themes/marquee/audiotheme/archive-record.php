<?php
/**
 * The template for displaying a record archives.
 *
 * @package Marquee
 * @since 1.0.0
 */

get_header();
?>

<main id="primary" <?php audiotheme_archive_class( array( 'content-area', 'archive-record' ) ); ?> role="main" itemprop="mainContentOfPage">

	<?php do_action( 'marquee_main_top' ); ?>

	<?php if ( have_posts() ) : ?>

		<header class="page-header">
			<?php the_audiotheme_archive_title( '<h1 class="page-title" itemprop="headline">', '</h1>' ); ?>
			<?php marquee_post_type_navigation( 'audiotheme_record' ); ?>
			<?php the_audiotheme_archive_description( '<div class="page-content" itemprop="text">', '</div>' ); ?>
		</header>

		<div class="<?php marquee_block_grid_classes(); ?>">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'audiotheme/record/content', 'archive' ); ?>
			<?php endwhile; ?>
		</div>

		<?php
		marquee_posts_navigation( array(
			'class'     => 'sort-natural',
			'prev_text' => esc_html__( 'Next', 'marquee' ),
			'next_text' => esc_html__( 'Previous', 'marquee' ),
		) );
		?>

	<?php else : ?>

		<?php get_template_part( 'audiotheme/record/content', 'none' ); ?>

	<?php endif; ?>

	<?php do_action( 'marquee_main_bottom' ); ?>

</main>

<?php
get_footer();
