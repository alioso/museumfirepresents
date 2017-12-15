<?php
/**
 * The template used for displaying individual gigs.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'vevent' ); ?> itemscope itemtype="http://schema.org/MusicEvent">
	<header class="entry-header">
		<h1 class="entry-title" itemprop="name"><?php echo marquee_allowed_tags( get_audiotheme_gig_title() ); ?></h1>

		<h2 class="entry-subtitle">
			<meta content="<?php echo esc_attr( get_audiotheme_gig_time( 'c' ) ); ?>" itemprop="startDate">
			<time datetime="<?php echo esc_attr( get_audiotheme_gig_time( 'c' ) ); ?>">
				<?php echo esc_html( get_audiotheme_gig_time( get_option( 'date_format', 'F d, Y' ) ) ); ?>
			</time>

			<?php if ( audiotheme_gig_has_venue() ) : ?>
				<?php $venue = get_audiotheme_venue( get_audiotheme_gig()->venue->ID ); ?><br>

				<span class="gig-location">
					<?php echo marquee_allowed_tags( get_audiotheme_venue_location( $venue->ID ) ); ?>
				</span>

				<span class="gig-venue-name"><?php echo esc_html( $venue->name ); ?></span>
			<?php endif; ?>
		</h2>

		<p class="breadcrumbs">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'audiotheme_gig' ) ); ?>"><?php esc_html_e( 'View All Gigs', 'marquee' ); ?></a>
		</p>
	</header>

	<?php get_template_part( 'audiotheme/gig/meta' ); ?>

	<?php if ( audiotheme_gig_has_venue() ) : ?>
		<?php get_template_part( 'audiotheme/gig/venue/meta' ); ?>
	<?php endif; ?>

	<div class="entry-content" itemprop="text">
		<?php do_action( 'marquee_entry_content_top' ); ?>
		<?php the_content(); ?>
		<?php do_action( 'marquee_entry_content_bottom' ); ?>
	</div>
</article>
