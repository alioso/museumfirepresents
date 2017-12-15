<?php
/**
 * The template used for displaying a tracklist for individual tracks.
 *
 * @package Marquee
 * @since 1.0.0
 */

if ( get_audiotheme_track_file_url() ) :
?>

<div class="tracklist-area">
	<h2 class="screen-reader-text"><?php esc_html_e( 'Record Tracklist', 'marquee' ); ?></h2>

	<ol class="tracklist record-tracklist">
		<li id="track-<?php the_ID(); ?>" <?php marquee_track_attributes( get_the_ID() ); ?>>
			<?php the_title( '<span class="track-title" itemprop="name">', '</span>' ); ?>
			<meta content="<?php the_permalink(); ?>" itemprop="url" />
			<span class="track-meta">
				<span class="track-current-time js-current-time">-:--</span>
				<span class="track-sep-duration"> / </span>
				<span class="track-duration js-duration"><?php marquee_audiotheme_track_length(); ?></span>
			</span>
		</li>
	</ol>
</div>

<?php
endif;
