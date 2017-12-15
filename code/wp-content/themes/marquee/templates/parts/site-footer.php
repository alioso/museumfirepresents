<?php
/**
 * The template used for displaying the site footer.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<footer id="footer" class="site-footer" role="contentinfo" itemscope itemtype="http://schema.org/WPFooter">

	<?php do_action( 'marquee_footer_top' ); ?>

	<div class="site-info">
		<?php if ( has_nav_menu( 'social' ) ) : ?>
			<nav class="social-navigation" role="navigation">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'social',
					'container'      => false,
					'depth'          => 1,
					'link_before'    => '<span class="screen-reader-text">',
					'link_after'     => '</span>',
				) );
				?>
			</nav>
		<?php endif; ?>

		<div class="credits">
			<?php marquee_credits(); ?>
		</div>
	</div>

	<?php do_action( 'marquee_footer_bottom' ); ?>

</footer>
