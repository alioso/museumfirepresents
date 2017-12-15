<?php
/**
 * The template for displaying a message when there aren't any records.
 *
 * @package Marquee
 * @since 1.0.0
 */
?>

<section class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'marquee' ); ?></h1>
	</header>

	<div class="page-content">
		<?php if ( current_user_can( 'publish_posts' ) ) : ?>
			<p>
				<?php
				$post_type_object = get_post_type_object( 'audiotheme_record' );
				echo marquee_allowed_tags( sprintf( _x( 'Ready to publish your first record? <a href="%1$s">Get started here</a>.', 'add post type link', 'marquee' ),
					esc_url( add_query_arg( 'post_type', $post_type_object->name, admin_url( 'post-new.php' ) ) )
				) );
				?>
			</p>
		<?php else : ?>
			<p>
				<?php esc_html_e( "There currently aren't any records available.", 'marquee' ); ?>
			</p>
		<?php endif; ?>
	</div>
</section>
