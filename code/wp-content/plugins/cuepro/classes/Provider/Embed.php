<?php
/**
 * Embed provider.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.1.0
 */

/**
 * Embed provider class.
 *
 * @package CuePro
 * @since   1.1.0
 */
class CuePro_Provider_Embed extends CuePro_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 1.1.0
	 */
	public function register_hooks() {
		add_filter( 'query_vars',        array( $this, 'register_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'load_embed_template' ) );
		add_action( 'cue_embed_head',    'wp_no_robots' );
		add_action( 'cue_embed_footer',  array( $this, 'print_embed_template_script' ) );

		if ( ! get_option( 'cuepro_disable_embeds', false ) ) {
			add_action( 'cue_playlist_bottom', array( $this, 'print_playlist_share_dialog' ), 10, 3 );
		}
	}

	/**
	 * Register query variables.
	 *
	 * @since 1.1.0
	 *
	 * @param  array $vars Array of query variables.
	 * @return array
	 */
	public function register_query_vars( $vars ) {
		$vars[] = 'cue_embed';
		return $vars;
	}

	/**
	 * Load the embedded playlist template.
	 *
	 * @since 1.1.0
	 */
	public function load_embed_template() {
		global $post, $wp_query;

		$post_name = $wp_query->get( 'cue_embed' );
		if ( empty( $post_name ) ) {
			return;
		}

		$post = get_page_by_path( $post_name, OBJECT, 'cue_playlist' );
		setup_postdata( $post );
		show_admin_bar( false );
		do_action( 'cue_embed_enqueue_scripts' );

		$args = array();
		if ( isset( $_GET['cue_theme'] ) ) {
			$args['theme'] = sanitize_key( $_GET['cue_theme'] );
		}

		include( $this->plugin->get_path( 'templates/embed.php' ) );
		exit;
	}

	/**
	 * Print the share dialog HTML after a playlist.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function print_playlist_share_dialog( $post, $tracks, $args ) {
		?>
		<div class="cue-share-dialog">
			<h2 class="cue-share-dialog-title"><?php esc_html_e( 'Embed', 'cuepro' ); ?></h2>
			<p>
				<?php esc_html_e( 'Copy and paste this code to your site to embed.', 'cuepro' ); ?>
			</p>
			<p>
				<textarea rows="3" onclick="this.select();" readonly><?php
					echo esc_textarea( sprintf(
						'<iframe class="cue-embed" src="%s" frameborder="0" marginwidth="0" marginheight="0" width="100%%" height="400"></iframe><script src="%s"></script>',
						esc_url( get_cue_embed_link( $post->ID, $args ) ),
						esc_url( $this->plugin->get_url( 'assets/js/embed.min.js' ) )
					) );
				?></textarea>
			</p>
			<button class="cue-share-dialog-close js-close" title="<?php esc_html_e( 'Close', 'cuepro' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 24 24">
					<path d="M21,4.41L19.59,3L12,10.59L4.41,3L3,4.41L10.59,12L3,19.59L4.41,21L12,13.41L19.59,21L21,19.59L13.41,12L21,4.41z"/>
				</svg>
			</button>
		</div>
		<?php
	}

	/**
	 * Print a script to resize iframes in the embed template.
	 *
	 * @since 1.1.0
	 */
	public function print_embed_template_script() {
		?>
		<script>
		(function( window, undefined ) {
			window.addEventListener( 'message', function( e ) {
				if ( 'height' === e.data.message ) {
					e.source.postMessage({
						message: 'height',
						value: Math.ceil( document.body.getBoundingClientRect().height ),
						index: e.data.index
					}, e.origin );
				}
			});
		})( this );
		</script>
		<?php
	}
}
