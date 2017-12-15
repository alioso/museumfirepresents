<?php
/**
 * Playlist control for the Customizer.
 *
 * @package Cedaro\Theme
 * @license GPL-2.0+
 * @since 3.0.0
 */

/**
 * Playlist control.
 *
 * @package Cedaro\Theme
 * @since 3.0.0
 */
class Cedaro_Theme_Customize_Control_Playlist extends WP_Customize_Control {
	/**
	 * Control type.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $type = 'cedaro-theme-playlist';

	/**
	 * Enqueue control scripts.
	 *
	 * @since 3.0.0
	 */
	public function enqueue() {
		wp_enqueue_media();

		wp_enqueue_script(
			'cedaro-theme-customize-controls',
			get_template_directory_uri() . '/includes/vendor/cedaro-theme/assets/js/customize-controls.js',
			array( 'customize-controls' ),
			'1.0.0',
			true
		);

		add_action( 'customize_controls_print_footer_scripts', array( $this, 'print_scripts' ) );
	}

	/**
	 * Render the control's content.
	 *
	 * @since 3.0.0
	 */
	public function render_content() {
		?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description">
					<?php
					// @link https://core.trac.wordpress.org/ticket/27981#comment:9
					echo $this->description; // WPCS: XSS OK.
					?>
				</span>
			<?php endif; ?>

			<input type="text" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
		</label>

		<div class="actions">
			<button class="button js-choose-playlist"><?php esc_html_e( 'Edit Playlist', 'marquee' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Print scripts and styles for the control.
	 *
	 * @since 3.0.0
	 */
	public function print_scripts() {
		?>
		<style type="text/css">
		.customize-control-cedaro-theme-playlist button {
			float: right;
		}

		.customize-control-cedaro-theme-playlist input[type="text"] {
			-moz-box-sizing: border-box;
			-webkit-box-sizing: border-box;
			box-sizing: border-box;
			line-height: 18px;
			margin: 0 0 6px 0;
		    width: 100%;
		}
		</style>
		<?php
	}

	/**
	 * Sanitization callback for lists of IDs in the Customizer.
	 *
	 * @since 3.0.0
	 *
	 * @param string $value Setting value.
	 * @return string Comma-separated list of IDs.
	 */
	public static function sanitize_id_list( $value ) {
		$value = implode( ',', wp_parse_id_list( $value ) );
		return ( '0' === $value ) ? '' : $value;
	}
}
