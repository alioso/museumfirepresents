<?php
/**
 * The template used for displaying a meta links on single track pages.
 *
 * @package Marquee
 * @since 1.0.0
 */

$purchase_url = get_audiotheme_track_purchase_url();
$download_url = is_audiotheme_track_downloadable();

if ( $purchase_url || $download_url ) :
?>

	<div class="meta-links dropdown-group">
		<h2 class="dropdown-group-title"><?php esc_html_e( 'Links', 'marquee' ); ?></h2>

		<ul class="dropdown-group-items">
			<?php if ( $purchase_url ) : ?>
				<li><a class="js-maybe-exernal" href="<?php echo esc_url( $purchase_url ); ?>" itemprop="url"><?php esc_html_e( 'Purchase', 'marquee' ); ?></a></li>
			<?php endif; ?>

			<?php if ( $download_url ) : ?>
				<li><a href="<?php echo esc_url( $download_url ); ?>" itemprop="url" download="<?php esc_attr( basename( $download_url ) ); ?>"><?php esc_html_e( 'Download', 'marquee' ); ?></a></li>
			<?php endif; ?>
		</ul>
	</div>

<?php
endif;
