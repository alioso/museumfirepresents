<?php
/**
 * The template used for displaying content.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemscope itemtype="http://schema.org/BlogPosting" itemprop="blogPost">
	<header class="entry-header">
		<?php marquee_entry_title(); ?>

		<p class="entry-meta entry-meta--header">
			<?php marquee_posted_by(); ?>
			<?php marquee_posted_on(); ?>
			<?php marquee_entry_comments_link(); ?>
			<?php edit_post_link( esc_html__( 'Edit', 'marquee' ), ' &bull; ' ); ?>
		</p>

		<?php if ( is_singular( 'post' ) ) : ?>
			<p class="breadcrumbs">
				<a href="<?php echo esc_url( marquee_blog_url() ); ?>">
					<?php esc_html_e( 'View All Posts', 'marquee' ); ?>
				</a>
			</p>
		<?php endif; ?>
	</header>

	<div class="entry-content" itemprop="articleBody">
		<?php do_action( 'marquee_entry_content_top' ); ?>
		<?php the_content(); ?>
		<?php marquee_page_links(); ?>
		<?php do_action( 'marquee_entry_content_bottom' ); ?>
	</div>

	<?php if ( is_singular() ) : ?>
		<footer class="entry-footer">
			<?php marquee_entry_terms(); ?>
		</footer>
	<?php endif; ?>
</article>
