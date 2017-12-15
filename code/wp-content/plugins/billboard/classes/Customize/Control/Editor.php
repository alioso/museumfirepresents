<?php
/**
 * TinyMCE editor Customizer control.
 *
 * @package   Billboard
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * TinyMCE editor Customizer control class.
 *
 * @package Billboard
 * @since   1.0.0
 */
class Billboard_Customize_Control_Editor extends WP_Customize_Control {
	/**
	 * Control type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $type = 'billboard-editor';

	/**
	 * Style sheets.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $stylesheets = array();

	/**
	 * Enqueue control scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue() {
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'render_editor' ), 0 );

		if ( false === has_action( 'customize_controls_print_footer_scripts', array( '_WP_Editors', 'enqueue_scripts' ) ) ) {
			add_action( 'customize_controls_print_footer_scripts', array( '_WP_Editors', 'enqueue_scripts' ) );
		}
	}

	/**
	 * Render the control's content.
	 *
	 * @since 1.0.0
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

			<div class="actions">
				<button class="button js-toggle-editor"><?php esc_html_e( 'Toggle Editor', 'billboard' ); ?></button>
			</div>
		</label>
		<?php
	}

	/**
	 * Render the editor.
	 *
	 * @link https://github.com/xwp/wp-customize-posts/blob/28fede1896f27d6f3ac323e4c3e4b66afb0879be/php/class-wp-customize-posts.php#L284
	 */
	public function render_editor() {
		echo '<div id="customize-billboard-editor-pane_' . $this->id . '" class="customize-billboard-editor-pane">';

		$settings = array(
			'_content_editor_dfw' => false,
			'drag_drop_upload'    => true,
			'editor_height'       => 150,
			'default_editor'      => 'tinymce',
			'teeny'               => true,
			'tinymce'             => array(
				'resize'             => false,
				'wp_autoresize_on'   => false,
				'add_unload_trigger' => false,
			),
		);

		add_filter( 'teeny_mce_buttons',  array( $this, 'mce_buttons' ), 10, 2 );
		add_filter( 'editor_stylesheets', array( $this, 'get_stylesheets' ) );
		wp_editor( $this->value(), $this->id, $settings );
		remove_filter( 'editor_stylesheets', array( $this, 'get_stylesheets' ) );

		echo '</div>';
	}

	/**
	 * Enable the formats dropdown.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $buttons   An array of teenyMCE buttons.
	 * @param  string $editor_id Unique editor identifier, e.g. 'content'.
	 * @return array
	 */
	public function mce_buttons( $buttons, $editor_id ) {
		if ( $this->id === $editor_id ) {
			array_unshift( $buttons, 'formatselect' );
			$buttons[] = 'styleselect';
		}

		return $buttons;
	}

	/**
	 * Retrieve style sheets for the editor.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $stylesheets Array of editor style sheets.
	 * @return array
	 */
	public function get_stylesheets( $stylesheets ) {
		return $this->stylesheets;
	}
}
