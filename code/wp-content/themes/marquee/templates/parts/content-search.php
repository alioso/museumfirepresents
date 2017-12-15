<?php
/**
 * The template used for displaying search content.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php marquee_entry_title(); ?>
	</header>

	<div class="entry-content" itemprop="text">
		<?php do_action( 'marquee_entry_content_top' ); ?>
		<?php the_excerpt(); ?>
		<?php do_action( 'marquee_entry_content_bottom' ); ?>
	</div>
</article>
