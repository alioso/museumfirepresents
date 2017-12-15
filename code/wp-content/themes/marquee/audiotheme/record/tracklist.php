<?php
/**
 * The template used for displaying a tracklist for individual records.
 *
 * @package Marquee
 * @since 1.0.0
 */

if ( $tracks = get_audiotheme_record_tracks() ) :
?>

	<div class="tracklist-area">
		<h2 class="tracklist-title"><?php esc_html_e( 'Tracklist', 'marquee' ); ?></h2>
		<meta itemprop="numTracks" content="<?php echo esc_attr( count( $tracks ) ); ?>" />

		<ol class="tracklist record-tracklist">
			<?php foreach ( $tracks as $track ) : ?>
				<li id="track-<?php echo absint( $track->ID ); ?>" <?php marquee_track_attributes( $track->ID ); ?>>
					<span class="track-title">
						<?php marquee_audiotheme_track_title( $track->ID, array( 'before_link' => '<span itemprop="name">', 'after_link' => '</span>', 'link_class' => '' ) ); ?>
					</span>

					<span class="track-meta">
						<span class="track-current-time js-current-time">-:--</span>
						<span class="track-sep-duration"> / </span>
						<span class="track-duration js-duration"><?php marquee_audiotheme_track_length( $track->ID ); ?></span>
					</span>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>

<?php
endif;
