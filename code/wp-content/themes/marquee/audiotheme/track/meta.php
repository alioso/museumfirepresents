<?php
/**
 * The template used for displaying a meta on single track pages.
 *
 * @package Marquee
 * @since 1.0.0
 */

$year   = get_audiotheme_record_release_year( $post->post_parent );
$genre  = get_audiotheme_record_genre( $post->post_parent );

if ( $year || $genre ) :
?>

	<div class="record-meta record-meta--track">
		<h2 class="screen-reader-text"><?php esc_html_e( 'Record Details', 'marquee' ); ?></h2>

		<dl>
			<?php if ( $year ) : ?>
				<dt class="record-meta-label record-year"><?php esc_html_e( 'Release', 'marquee' ); ?></dt>
				<dd class="record-meta-value record-year" itemprop="dateCreated"><?php echo esc_html( $year ); ?></dd>
			<?php endif; ?>

			<?php if ( $genre ) : ?>
				<dt class="record-meta-label record-genre"><?php esc_html_e( 'Genre', 'marquee' ); ?></dt>
				<dd class="record-meta-value record-genre" itemprop="genre"><?php echo esc_html( $genre ); ?></dd>
			<?php endif; ?>
		</dl>
	</div>

<?php
endif;
