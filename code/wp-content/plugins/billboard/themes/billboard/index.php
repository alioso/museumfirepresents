<?php
/**
 * Billboard theme template.
 *
 * @package   Billboard
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */
?><!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>
	<script>
	(function( html ) {
		var iOSversion, isMobileSafari,
			userAgent = window.navigator.userAgent;

		isMobileSafari = /(iPhone|iPod|iPad).+AppleWebKit/i.test( userAgent ) && (function() {
			iOSversion = userAgent.match(/OS (\d)/);
			return iOSversion && iOSversion.length > 1;
		})();

		if ( isMobileSafari ) {
			html.className = html.className + ' ios' + parseInt( iOSversion[1], 10 );
			html.className = html.className + ' ' + userAgent.match( /(iPhone|iPod|iPad)/i )[1].toLowerCase();
		}
	})( document.documentElement );
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> itemscope itemtype="http://schema.org/WebPage">
	<div class="billboard-background"><?php billboard_background_video(); ?></div>

	<div class="billboard-page">
		<?php do_action( 'billboard_page_top' ); ?>

		<header class="billboard-header">
			<div class="billboard-branding">
				<?php billboard_logo(); ?>
				<h1 class="billboard-title"><?php echo esc_html( billboard()->get_setting( 'title' ) ); ?></h1>
				<p class="billboard-tagline"><?php echo esc_html( billboard()->get_setting( 'tagline' ) ); ?></p>
			</div>

			<?php if ( billboard()->get_setting( 'social_menu' ) ) : ?>

				<nav class="billboard-social-navigation" role="navigation">
					<h2 class="screen-reader-text"><?php esc_html_e( 'Social Media Links', 'billboard' ); ?></h2>

					<?php
					wp_nav_menu( array(
						'menu'        => billboard()->get_setting( 'social_menu' ),
						'container'   => false,
						'depth'       => 1,
						'link_before' => '<span class="screen-reader-text">',
						'link_after'  => '</span>',
						'fallback_cb' => false,
					) );
					?>
				</nav>

			<?php endif; ?>
		</header>

		<div class="billboard-content">
			<?php do_action( 'billboard_content_top' ); ?>

			<main class="billboard-content-area">
				<?php billboard_content(); ?>
			</main>

			<?php do_action( 'billboard_content_bottom' ); ?>
		</div>

		<?php do_action( 'billboard_page_bottom' ); ?>
	</div>

	<?php wp_footer(); ?>
</body>
</html>
