<?php
/**
 * Plugin compatibility.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.1
 */

/**
 * Compatibility class.
 *
 * @package CuePro
 * @since   1.0.1
 */
class CuePro_Provider_Compatibility extends CuePro_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 1.0.1
	 */
	public function register_hooks() {
		add_action( 'admin_notices', array( $this, 'display_cue_required_notice' ) );
	}

	/**
	 * Display a notice if Cue isn't installed or active.
	 *
	 * @since 1.0.1
	 */
	public function display_cue_required_notice() {
		global $pagenow;

		// Don't show the notice when Cue is being installed.
		if ( $this->is_cue_active() || 'update.php' == $pagenow) {
			return;
		}

		$notice = '';

		if ( $this->is_cue_installed() && current_user_can( 'activate_plugins' ) ) {
			$activate_url = wp_nonce_url(
				self_admin_url( 'plugins.php?action=activate&amp;plugin=cue/cue.php' ),
				'activate-plugin_cue/cue.php'
			);

			$notice = sprintf( '%s <a href="%s"><strong>%s</strong></a>',
				esc_html__( 'Cue Pro requires Cue to be activated.', 'cuepro' ),
				esc_url( $activate_url ),
				esc_html__( 'Activate Now', 'cuepro' )
			);
		} elseif ( current_user_can( 'install_plugins' ) ) {
			$install_url = wp_nonce_url(
				self_admin_url( 'update.php?action=install-plugin&amp;plugin=cue' ),
				'install-plugin_cue'
			);

			$notice = sprintf( '%s <a href="%s"><strong>%s</strong></a>',
				esc_html__( 'Cue Pro requires Cue to be installed and activated.', 'cuepro' ),
				esc_url( $install_url ),
				esc_html__( 'Install Now', 'cuepro' )
			);
		}

		$this->display_notice( $notice );
	}

	/**
	 * Whether Cue is installed.
	 *
	 * @since 1.0.1
	 *
	 * @return boolean
	 */
	protected function is_cue_installed() {
		return 0 === validate_plugin( 'cue/cue.php' );
	}

	/**
	 * Whether Cue is active.
	 *
	 * @since 1.0.1
	 *
	 * @return boolean
	 */
	protected function is_cue_active() {
		return function_exists( 'cue' );
	}

	/**
	 * Display an admin notice.
	 *
	 * @since 1.0.1
	 */
	protected function display_notice( $message, $type = 'error' ) {
		?>
		<div id="cuepro-compatibility-notice" class="notice notice-<?php echo esc_attr( $type ); ?>">
			<p>
				<?php echo $message; ?>
			</p>
		</div>
		<?php
	}
}
