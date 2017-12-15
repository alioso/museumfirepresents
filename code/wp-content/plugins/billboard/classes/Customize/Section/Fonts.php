<?php
/**
 * Fonts section for the Customizer.
 *
 * @package   Billboard
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Fonts section class.
 *
 * @package Billboard
 * @since   1.0.0
 * @see     WP_Customize_Section
 */
class Billboard_Customize_Section_Fonts extends WP_Customize_Section {
	/**
	 * Customize section type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $type = 'billboard-fonts';

	/**
	 * An Underscore (JS) template for rendering this section.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @see WP_Customize_Section::print_template()
	 */
	protected function render_template() {
		$settings   = get_option( 'billboard_fonts' );
		$typekit_id = isset( $settings['typekit_id'] ) ? $settings['typekit_id'] : '';
		?>
		<li id="accordion-section-{{ data.id }}" class="accordion-section control-section control-section-{{ data.type }}">
			<h3 class="accordion-section-title" tabindex="0">
				{{ data.title }}
				<span class="screen-reader-text"><?php esc_html_e( 'Press return or enter to open', 'billboard' ); ?></span>
			</h3>
			<ul class="accordion-section-content">
				<li class="customize-section-description-container customize-info">
					<div class="customize-section-title">
						<button class="customize-section-back" tabindex="-1">
							<span class="screen-reader-text"><?php esc_html_e( 'Back', 'billboard' ); ?></span>
						</button>
						<h3>
							<span class="customize-action">
								{{{ data.customizeAction }}}
							</span>
							{{ data.title }}
						</h3>
						<button type="button" class="customize-help-toggle billboard-fonts-section-toggle dashicons dashicons-editor-help" aria-expanded="false" data-target="#billboard-fonts-section-description">
							<span class="screen-reader-text"><?php esc_html_e( 'Help', 'billboard' ); ?></span>
						</button>
						<button type="button" class="customize-screen-options-toggle billboard-fonts-section-toggle" aria-expanded="false" data-target="#billboard-fonts-section-options">
							<span class="screen-reader-text"><?php esc_html_e( 'Font Options', 'billboard' ); ?></span>
						</button>
					</div>
					<div id="billboard-fonts-section-description" class="description customize-section-description billboard-fonts-section-content">
						<?php esc_html_e( 'Easily customize your fonts. Try to re-use fonts where possible to keep your website snappy.', 'billboard' ); ?>
					</div>
					<div id="billboard-fonts-section-options" class="billboard-fonts-section-content">
						<p>
							<label for="billboard-fonts-option-typekit-id"><?php esc_html_e( 'Typekit Integration', 'billboard' ); ?></label>
						</p>
						<p>
							<?php
							$text = sprintf(
								__( 'Enter a Kit ID to make custom fonts from Typekit available in each dropdown. %s', 'billboard' ),
								sprintf( '<a href="https://audiotheme.com/support/kb/typekit/">%s</a>', esc_html__( 'Learn more.', 'billboard' ) )
							);

							echo wp_kses( $text, array( 'a' => array( 'href' => array() ) ) );
							?>
						</p>
						<p>
							<input type="text" id="billboard-fonts-option-typekit-id" value="<?php echo esc_attr( $typekit_id ); ?>">
							<span class="spinner"></span>
						</p>
					</div>
				</li>
			</ul>
		</li>
		<?php
	}
}
