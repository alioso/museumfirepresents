<?php
/**
 * Fonts section for the Customizer.
 *
 * @package Cedaro\Theme
 * @since 3.2.0
 * @copyright Copyright (c) 2015, Cedaro
 * @license GPL-2.0+
 */

/**
 * Fonts section class.
 *
 * @since 3.2.0
 *
 * @see WP_Customize_Section
 */
class Cedaro_Theme_Customize_Section_Fonts extends WP_Customize_Section {
	/**
	 * Customize section type.
	 *
	 * @since 3.2.0
	 * @var string
	 */
	public $type = 'cedaro-theme-fonts';

	/**
	 * An Underscore (JS) template for rendering this section.
	 *
	 * @since 3.2.0
	 * @access protected
	 *
	 * @see WP_Customize_Section::print_template()
	 */
	protected function render_template() {
		$typekit_id = get_theme_mod( 'cedaro_fonts_typekit_id', '' );
		?>
		<li id="accordion-section-{{ data.id }}" class="accordion-section control-section control-section-{{ data.type }}">
			<h3 class="accordion-section-title" tabindex="0">
				{{ data.title }}
				<span class="screen-reader-text"><?php esc_html_e( 'Press return or enter to open', 'marquee' ); ?></span>
			</h3>
			<ul class="accordion-section-content">
				<li class="customize-section-description-container customize-info">
					<div class="customize-section-title">
						<button class="customize-section-back" tabindex="-1">
							<span class="screen-reader-text"><?php esc_html_e( 'Back', 'marquee' ); ?></span>
						</button>
						<h3>
							<span class="customize-action">
								{{{ data.customizeAction }}}
							</span>
							{{ data.title }}
						</h3>
						<button type="button" class="customize-help-toggle cedaro-theme-fonts-section-toggle dashicons dashicons-editor-help" aria-expanded="false" data-target="#cedaro-theme-fonts-section-description">
							<span class="screen-reader-text"><?php esc_html_e( 'Help', 'marquee' ); ?></span>
						</button>
						<button type="button" class="customize-screen-options-toggle cedaro-theme-fonts-section-toggle" aria-expanded="false" data-target="#cedaro-theme-fonts-section-options">
							<span class="screen-reader-text"><?php esc_html_e( 'Font Options', 'marquee' ); ?></span>
						</button>
					</div>
					<div id="cedaro-theme-fonts-section-description" class="description customize-section-description cedaro-theme-fonts-section-content">
						<?php esc_html_e( 'Easily customize your fonts. Try to re-use fonts where possible to keep your website snappy.', 'marquee' ); ?>
					</div>
					<div id="cedaro-theme-fonts-section-options" class="cedaro-theme-fonts-section-content">
						<p>
							<label for="cedaro-theme-fonts-option-typekit-id"><?php esc_html_e( 'Typekit Integration', 'marquee' ); ?></label>
						</p>
						<p>
							<?php
							$text = sprintf(
								__( 'Enter a Kit ID to make custom fonts from Typekit available in each dropdown. %s', 'marquee' ),
								sprintf( '<a href="https://audiotheme.com/support/kb/typekit/">%s</a>', esc_html__( 'Learn more.', 'marquee' ) )
							);

							echo wp_kses( $text, array( 'a' => array( 'href' => array() ) ) );
							?>
						</p>
						<p>
							<input type="text" id="cedaro-theme-fonts-option-typekit-id" value="<?php echo esc_attr( $typekit_id ); ?>">
							<span class="spinner"></span>
						</p>
					</div>
				</li>
			</ul>
		</li>
		<?php
	}
}
