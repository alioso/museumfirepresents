<?php
/**
 * Template to display a video widget.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<?php
if ( ! empty( $title ) ) :
	echo $before_title . $title . $after_title;
endif;
?>

<?php echo get_audiotheme_video( $post->ID ); ?>

<?php
if ( ! empty( $text ) ) :
	echo wpautop( $text );
endif;
?>

<?php if ( ! empty( $link_text ) ) : ?>
	<p class="more">
		<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>"><?php echo $link_text; ?></a>
	</p>
<?php endif; ?>
