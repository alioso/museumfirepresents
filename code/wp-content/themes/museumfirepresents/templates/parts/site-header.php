<?php
/**
 * The template used for displaying the site header.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<header id="masthead" class="site-header" role="banner" itemscope itemtype="http://schema.org/WPHeader">
    <a href="/" class="h-t">HOME</a>
    <div class="site-header__mask"></div>

	<?php do_action( 'marquee_header_top' ); ?>

	<?php marquee_site_branding(); ?>

	<?php do_action( 'marquee_branding_after' ); ?>

    <div class="h-m-wrapper">

<!--        <button id="site-navigation-toggle" class="site-navigation-toggle">--><?php //esc_html_e( 'Menu', 'marquee' ); ?><!--</button>-->
    </div>
	<div id="site-navigation-panel" class="site-navigation-panel is-open">
		<div class="site-navigation-panel-body">
			<nav id="site-navigation" class="site-navigation" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'fallback_cb'    => false,
					'depth'          => 1,
				) );
				?>
			</nav>

			<nav id="secondary-navigation" class="secondary-navigation" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'secondary',
					'container'      => false,
					'fallback_cb'    => false,
					'depth'          => 1,
				) );
				?>
			</nav>

			<div class="panel-body-footer"></div>
		</div>
	</div>

	<?php do_action( 'marquee_header_bottom' ); ?>

</header>
