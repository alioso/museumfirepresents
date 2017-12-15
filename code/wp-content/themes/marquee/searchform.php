<?php
/**
 * The template used for displaying the search form.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div class="input-group">
		<input type="search" class="search-field input-group-field" placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder', 'marquee' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'label', 'marquee' ); ?>">

		<span class="input-group-button">
			<button type="submit" class="search-submit"><?php echo esc_html_x( 'Search', 'submit button', 'marquee' ); ?></button>
		</span>
	</div>
</form>
