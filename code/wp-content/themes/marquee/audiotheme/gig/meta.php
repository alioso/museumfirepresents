<?php
/**
 * The template used for displaying a meta single gig pages.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<div class="gig-meta">
	<?php
	the_audiotheme_gig_description(
		'<div class="gig-description" itemprop="description">',
		'</div>'
	);
	?>

	<dl>
		<dt><?php esc_html_e( 'Time', 'marquee' ); ?></dt>
		<dd>
			<?php
			echo esc_html( get_audiotheme_gig_time(
				'',
				'l \@ g:i A',
				false,
				array(
					'empty_time' => esc_html__( 'TBD', 'marquee' ),
				)
			) );
			?>
		</dd>

		<?php if ( audiotheme_gig_has_ticket_meta() ) : ?>
			<dt><?php esc_html_e( 'Admission', 'marquee' ); ?></dt>
			<dd itemprop="offers" itemscope itemtype="http://schema.org/Offer">
				<?php if ( $gig_tickets_price = get_audiotheme_gig_tickets_price() ) : ?>
					<span class="gig-ticktes-price" itemprop="price"><?php echo esc_html( $gig_tickets_price ); ?></span>
				<?php endif; ?>
			</dd>
		<?php endif; ?>
	</dl>

	<?php if ( audiotheme_gig_has_ticket_meta() && ( $gig_tickets_url = get_audiotheme_gig_tickets_url() ) ) : ?>
		<p class="gig-tickets-button">
			<a class="gig-tickets-url button js-maybe-external" href="<?php echo esc_url( $gig_tickets_url ); ?>" itemprop="url">
				<?php esc_html_e( 'Buy Tickets', 'marquee' ); ?>
			</a>
		</p>
	<?php endif; ?>
</div>
