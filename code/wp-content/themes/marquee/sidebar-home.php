<?php
/**
 * The template containing the home widget area.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<aside id="secondary" class="home-widgets widget-area" role="complementary" itemscope itemtype="http://schema.org/WPSideBar">

	<?php do_action( 'marquee_home_widgets_top' ); ?>

	<?php dynamic_sidebar( 'home-widgets' ); ?>

	<?php do_action( 'marquee_home_widgets_bottom' ); ?>

</aside>
