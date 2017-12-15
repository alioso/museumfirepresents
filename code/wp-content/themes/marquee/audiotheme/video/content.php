<?php
/**
 * The template used for displaying individual videos.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemprop="video" itemscope itemtype="http://schema.org/VideoObject">
	<?php if ( $thumbnail = get_post_thumbnail_id() ) : ?>
		<meta itemprop="thumbnailUrl" content="<?php echo esc_url( wp_get_attachment_url( $thumbnail, 'full' ) ); ?>">
	<?php endif; ?>

	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title" itemprop="name">', '</h1>' ); ?>

		<p class="breadcrumbs">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'audiotheme_video' ) ); ?>"><?php esc_html_e( 'View All Videos', 'marquee' ); ?></a>
		</p>
	</header>

	<?php get_template_part( 'audiotheme/video/media' ); ?>

	<div class="entry-content" itemprop="description">
		<?php do_action( 'marquee_entry_content_top' ); ?>
		<?php the_content(); ?>
		<?php do_action( 'marquee_entry_content_bottom' ); ?>
	</div>
</article>
