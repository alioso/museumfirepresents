<?php
/**
 * Underscore.js templates for displaying the audio player bar across the top of
 * the site when it's enabled.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<button id="site-player-toggle" class="site-player-toggle"><?php esc_html_e( 'Toggle Player', 'marquee' ); ?></button>

<div id="site-player-panel" class="site-player-panel"></div>

<script type="text/html" id="tmpl-site-player">
	<div class="controls">
		<button class="previous"><?php esc_html_e( 'Previous Track', 'marquee' ); ?></button>
		<button class="play-pause"><?php esc_html_e( 'Play', 'marquee' ); ?></button>
		<button class="next"><?php esc_html_e( 'Next Track', 'marquee' ); ?></button>
		<button class="repeat" title="<?php esc_attr_e( 'Repeat', 'marquee' ); ?>"><?php esc_html_e( 'Repeat', 'marquee' ); ?></button>
		<button class="shuffle" title="<?php esc_attr_e( 'Shuffle', 'marquee' ); ?>"><?php esc_html_e( 'Shuffle', 'marquee' ); ?></button>

		<div class="volume-bar">
			<div class="volume-bar-current"></div>
		</div>

		<div class="progress-bar">
			<div class="play-bar"></div>
		</div>

		<div class="times">
			<span class="current-time">-:--</span>
			/
			<span class="duration">-:--</span>
		</div>
	</div>
</script>

<script type="text/html" id="tmpl-site-player-track">
	<span class="track-status track-cell"></span>

	<span class="track-details track-cell">
		<span class="track-title">{{{ data.title }}}</span>
		<span class="track-artist">{{{ data.artist }}}</span>
	</span>

	<?php do_action( 'marquee_player_track_details_after' ); ?>

	<span class="track-length track-cell">{{ data.length }}</span>
</script>

<script type="text/html" id="tmpl-site-current-track-details">
	<span class="title">{{ data.title }}</span>
	-
	<span class="artist">{{ data.artist }}</span>

	<div class="times">
		<span class="current-time">-:--</span>
		/
		<span class="duration">-:--</span>
	</div>
</script>
