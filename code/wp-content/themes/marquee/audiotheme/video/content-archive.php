<?php
/**
 * The template used for displaying videos on archives.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<article id="block-grid-item-<?php the_ID(); ?>" <?php post_class( 'block-grid-item' ); ?>>
	<div class="block-grid-item-wrapper">
		<?php do_action( 'marquee_entry_content_top' ); ?>

		<a class="block-grid-item-thumbnail" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'marquee-block-grid-16x9' ); ?></a>

		<?php the_title( '<h2 class="block-grid-item-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>

		<?php do_action( 'marquee_entry_content_bottom' ); ?>
	</div>
</article>
