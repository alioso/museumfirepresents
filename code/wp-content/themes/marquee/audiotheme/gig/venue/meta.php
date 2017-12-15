<?php
/**
 * The template used for displaying venue meta on single gig pages.
 *
 * @package Marquee
 * @since 1.0.0
 */

if ( audiotheme_gig_has_venue() ) :
	$venue = get_audiotheme_venue( get_audiotheme_gig()->venue->ID );
	?>

	<div class="venue-meta" itemprop="location" itemscope itemtype="http://schema.org/EventVenue">
		<h2 class="screen-reader-text"><?php esc_html_e( 'Venue Details', 'marquee' ); ?></h2>

		<?php get_template_part( 'audiotheme/gig/venue/map' ); ?>

		<dl class="venue-address">
			<dt class="screen-reader-text"><?php esc_html_e( 'Address', 'marquee' ); ?></dt>
			<dd>
				<?php
				the_audiotheme_venue_vcard( array(
					'container'         => '',
					'separator_address' => ', ',
					'separator_country' => ', ',
					'show_name'         => false,
					'show_phone'        => false,
				) );
				?>

				<?php if ( $venue->phone ) : ?>
					<span class="venue-phone"><?php echo esc_html( $venue->phone ); ?></span>
				<?php endif; ?>

				<?php if ( $venue->website ) : ?>
					<span class="venue-website"><a href="<?php echo esc_url( $venue->website ); ?>" itemprop="url"><?php echo esc_html( audiotheme_simplify_url( $venue->website ) ); ?></a></span>
				<?php endif; ?>
			</dd>
		</dl>
	</div>

	<?php
endif;
