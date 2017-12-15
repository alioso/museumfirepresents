<?php
/**
 * Template for displaying the audio player.
 *
 * @package   Billboard
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */
?>

<div class="billboard-player is-loading" itemscope itemtype="http://schema.org/MusicPlaylist">
	<meta itemprop="numTracks" content="<?php echo count( $tracks ); ?>" />

	<audio src="<?php echo esc_url( $tracks[0]['audioUrl'] ); ?>" controls preload="none" class="cue-audio" style="width: 100%; height: auto"></audio>

	<div class="cue-tracks">
		<ol class="cue-tracks-list">

			<?php foreach ( $tracks as $track ) : ?>

				<li class="cue-track" itemprop="track" itemscope itemtype="http://schema.org/MusicRecording">
					<?php do_action( 'billboard_track_before', $track ); ?>

					<span class="cue-track-details cue-track-cell">
						<span class="cue-track-title" itemprop="name"><?php echo $track['title']; ?></span>
						<span class="cue-track-artist" itemprop="byArtist"><?php echo esc_html( $track['artist'] ); ?></span>

						<?php
						if ( function_exists( 'cue_track_action_links' ) ) :
							cue_track_action_links( $track );
						endif;
						?>
					</span>

					<span class="cue-track-length cue-track-cell"><?php echo esc_html( $track['length'] ); ?></span>

					<?php do_action( 'billboard_track_after', $track ); ?>
				</li>

			<?php endforeach; ?>

		</ol>
	</div>
</div>
