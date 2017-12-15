<?php
/**
 * The template for displaying a record artwork.
 *
 * @package Marquee
 * @since 1.0.0
 */

if ( has_post_thumbnail() ) :
?>
	<figure class="entry-media record-artwork">
		<a class="post-thumbnail" href="<?php echo esc_url( wp_get_attachment_url( get_post_thumbnail_id() ) ); ?>"><?php the_post_thumbnail(); ?></a>
	</figure>
<?php
endif;
