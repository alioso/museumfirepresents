<?php
/**
 * AudioTheme required notice.
 *
 * @package   AudioTheme\ThemeNotice
 * @version   1.0.1
 * @since     1.0.0
 * @link      https://audiotheme.com/
 * @copyright Copyright (c) 2015 AudioTheme, LLC
 * @license   GPL-2.0+
 */

/**
 * Class to display a dismissable notice if AudioTheme isn't active.
 *
 * @package AudioTheme\ThemeNotice
 * @since 1.0.0
 */
class AudioTheme_ThemeNotice {
	/**
	 * Array of configurable strings.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $strings = array();

	/**
	 * Load AudioTheme or display a notice if it's not active.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Notice customization arguments.
	 */
	public function __construct( $args = array() ) {
		if ( ! $this->is_audiotheme_active() && is_admin() && current_user_can( 'activate_plugins' ) ) {
			$this->strings = array(
				'notice'     => esc_html__( 'The AudioTheme plugin should be installed and activated for this theme to display properly.', 'marquee' ),
				'activate'   => esc_html__( 'Activate now', 'marquee' ),
				'learn_more' => esc_html__( 'Find out more', 'marquee' ),
				'dismiss'    => esc_html__( 'Dismiss', 'marquee' ),
			);

			if ( isset( $args['strings'] ) ) {
				$this->strings = $args['strings'];
			}

			add_action( 'admin_notices', array( $this, 'display_notice' ) );
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'wp_ajax_' . $this->dismiss_notice_action(), array( $this, 'ajax_dismiss_notice' ) );
		}
	}

	/**
	 * Check if AudioTheme is active.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_audiotheme_active() {
		return function_exists( 'audiotheme_load' );
	}

	/**
	 * Dismiss the plugin required notice.
	 *
	 * This is a fallback in case the AJAX method doesn't work.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$slug = $this->theme();

		$is_dismiss_request = isset( $_GET[ $slug ] ) && 'dismiss-notice' === $_GET[ $slug ]; // WPCS: Input var OK.
		$is_valid_nonce     = isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], $this->dismiss_notice_action() ); // WPCS: Input var OK. Sanitization OK.

		if ( ! $is_dismiss_request || ! $is_valid_nonce ) {
			return;
		}

		$this->dismiss_notice();
		$redirect = remove_query_arg( array( $this->theme(), '_wpnonce' ) );
		wp_safe_redirect( esc_url_raw( $redirect ) );
	}

	/**
	 * Display a notice if AudioTheme isn't active.
	 *
	 * @since 1.0.0
	 */
	public function display_notice() {
		$user_id = get_current_user_id();

		// Return early if user already dismissed the notice.
		if ( 'dismissed' === get_user_meta( $user_id, $this->notice_key(), true ) ) {
			return;
		}
		?>
		<div id="audiotheme-required-notice" class="error">
			<p>
				<?php
				echo esc_html( $this->strings['notice'] );

				if ( 0 === validate_plugin( 'audiotheme/audiotheme.php' ) ) {
					$activate_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=audiotheme/audiotheme.php', 'activate-plugin_audiotheme/audiotheme.php' );
					printf( ' <a href="%s"><strong>%s</strong></a>',
						esc_url( $activate_url ),
						esc_html( $this->strings['activate'] )
					);
				} else {
					printf( ' <a href="https://audiotheme.com/view/audiotheme/"><strong>%s</strong></a>',
						esc_html( $this->strings['learn_more'] )
					);
				}

				$dismiss_url = wp_nonce_url( add_query_arg( get_template(), 'dismiss-notice' ), $this->dismiss_notice_action() );
				printf( ' <a href="%s" class="dismiss" style="float: right">%s</a>',
					esc_url( $dismiss_url ),
					esc_html( $this->strings['dismiss'] )
				);
				?>
			</p>
		</div>
		<script type="text/javascript">
		jQuery( '#audiotheme-required-notice' ).on( 'click', '.dismiss', function( e ) {
			var $notice = jQuery( this ).closest( '.error' );

			e.preventDefault();

			jQuery.get( ajaxurl, {
				action: '<?php echo $this->dismiss_notice_action(); // WPCS: XSS OK. ?>',
				_wpnonce: '<?php echo wp_create_nonce( $this->dismiss_notice_action() ); // WPCS: XSS OK. ?>'
			}, function() {
				$notice.slideUp();
			});
		});
		</script>
		<?php
	}

	/**
	 * AJAX callback to dismiss the plugin required notice.
	 *
	 * @since 1.0.0
	 */
	public function ajax_dismiss_notice() {
		check_admin_referer( $this->dismiss_notice_action() );
		$this->dismiss_notice();
		wp_die( 1 );
	}

	/**
	 * Add the notice status to the current user's meta.
	 *
	 * @since 1.0.0
	 */
	protected function dismiss_notice() {
		$user_id = get_current_user_id();
		update_user_meta( $user_id, $this->notice_key(), 'dismissed', true );
	}

	/**
	 * User meta key for the notice status.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function notice_key() {
		return $this->theme() . '_audiotheme_required_notice';
	}

	/**
	 * Name of the dismiss action.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function dismiss_notice_action() {
		return $this->theme() . '-dismiss-audiotheme-required-notice';
	}

	/**
	 * Get the name of the current parent theme.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function theme() {
		return get_template();
	}
}
