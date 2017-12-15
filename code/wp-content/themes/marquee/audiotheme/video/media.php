<?php
/**
 * The template for displaying a video media.
 *
 * @package Marquee
 * @since 1.0.0
 */

if ( $video_url = get_audiotheme_video_url() ) :
?>

	<meta itemprop="embedUrl" content="<?php echo esc_url( $video_url ); ?>">

	<figure class="entry-media entry-video">
		<?php the_audiotheme_video(); ?>
	</figure>

<?php
endif;
