<?php
/**
 * The template for displaying the site footer.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>


				<?php do_action( 'marquee_main_after' ); ?>

			<!--pjax-content--></div><!-- #content -->

			<?php do_action( 'marquee_footer_before' ); ?>

			<?php get_template_part( 'templates/parts/site-footer' ); ?>

			<?php do_action( 'marquee_footer_after' ); ?>

		</div><!-- #page -->
	</div><!-- #viewport-pane -->

	<?php do_action( 'marquee_after' ); ?>

	<?php wp_footer(); ?>

</body>
</html>
