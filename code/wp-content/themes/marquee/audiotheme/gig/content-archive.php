<?php
/**
 * The template used for displaying gig archives.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<div id="gigs" class="gig-list vcalendar">
	<h2 class="gig-list-header">
		<span class="gig-list-header-date"><?php esc_html_e( 'Date', 'marquee' ); ?></span>
		<span class="gig-list-header-location-sep">,&nbsp;</span>
		<span class="gig-list-header-location"><?php esc_html_e( 'Location', 'marquee' ); ?></span>
	</h2>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php get_template_part( 'audiotheme/gig/card' ); ?>
	<?php endwhile; ?>
</div>

<?php marquee_posts_navigation(); ?>
