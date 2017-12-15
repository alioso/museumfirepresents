<?php
/**
 * The template used for displaying content in page.php.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemscope itemtype="http://schema.org/CreativeWork">
	<header class="entry-header">
		<?php marquee_entry_title(); ?>
	</header>

	<div class="entry-content" itemprop="text">
		<?php do_action( 'marquee_entry_content_top' ); ?>
		<?php the_content(); ?>
		<?php marquee_page_links(); ?>
		<?php do_action( 'marquee_entry_content_bottom' ); ?>
	</div>
</article>
