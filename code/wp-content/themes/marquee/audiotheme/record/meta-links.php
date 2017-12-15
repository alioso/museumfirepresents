<?php
/**
 * The template used for displaying a meta links on single record pages.
 *
 * @package Marquee
 * @since 1.0.0
 */

if ( $links = get_audiotheme_record_links() ) :
?>

	<div class="meta-links dropdown-group">
		<h2 class="dropdown-group-title"><?php esc_html_e( 'Purchase', 'marquee' ); ?></h2>

		<ul class="dropdown-group-items">
			<?php foreach ( $links as $link ) : ?>
				<li>
					<a class="js-maybe-external" href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['name'] ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

<?php
endif;
