<?php
/**
 * The template used for displaying individual records.
 *
 * @package Marquee
 * @since 1.0.0
 */

$artist = get_audiotheme_record_artist();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title" itemprop="name">', '</h1>' ); ?>

		<?php if ( ! empty( $artist ) ) : ?>
			<h2 class="entry-subtitle" itemprop="byArtist"><?php echo esc_html( $artist ); ?></h2>
		<?php endif; ?>

		<p class="breadcrumbs">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'audiotheme_record' ) ); ?>"><?php esc_html_e( 'View All Music', 'marquee' ); ?></a>
		</p>
	</header>

	<div class="record-details">
		<?php get_template_part( 'audiotheme/record/artwork' ); ?>

		<div class="record-details-meta">
			<?php get_template_part( 'audiotheme/record/meta' ); ?>
			<?php get_template_part( 'audiotheme/record/meta', 'links' ); ?>
		</div>
	</div>

	<?php get_template_part( 'audiotheme/record/tracklist' ); ?>

	<div class="entry-content" itemprop="description">
		<?php do_action( 'marquee_entry_content_top' ); ?>
		<?php the_content(); ?>
		<?php do_action( 'marquee_entry_content_bottom' ); ?>
	</div>
</article>
