<?php
/**
 * The template for displaying a single record.
 *
 * @package Marquee
 * @since 1.0.0
 */

get_header();
?>

<main id="primary" class="content-area single-record" role="main" itemprop="mainContentOfPage" itemscope itemtype="http://schema.org/MusicAlbum">

	<?php do_action( 'marquee_main_top' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'audiotheme/record/content', str_replace( 'record-type-', '', get_audiotheme_record_type( $post ) ) ); ?>

		<?php comments_template( '', true ); ?>

	<?php endwhile; ?>

	<?php do_action( 'marquee_main_bottom' ); ?>

</main>

<?php
get_footer();
