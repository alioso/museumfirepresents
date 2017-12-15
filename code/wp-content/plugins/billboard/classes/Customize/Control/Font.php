<?php
/**
 * Font control for the Customizer.
 *
 * @package   Billboard
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Font control class.
 *
 * @package Billboard
 * @since   1.0.0
 */
class Billboard_Customize_Control_Font extends WP_Customize_Control {
	/**
	 * Control type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $type = 'billboard-font';

	/**
	 * Default font.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $default_font = '';

	/**
	 * Fonts to exclude from the dropdown.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $exclude_fonts = array();

	/**
	 * Font tags.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $tags = array();

	/**
	 * Refresh the parameters passed to JavaScript via JSON.
	 *
	 * @since 1.0.0
	 * @uses WP_Customize_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();

		$this->json['defaultFont'] = $this->default_font;
		$this->json['excludeFonts'] = $this->exclude_fonts;
		$this->json['tags'] = $this->tags;
		$this->json['value'] = $this->value();
	}

	/**
	 * Render the control's content.
	 *
	 * @since 1.0.0
	 */
	public function render_content() {}
}
