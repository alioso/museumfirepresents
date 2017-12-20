<?php
/**
 * Font control for the Customizer.
 *
 * @package Cedaro\Theme
 * @since 3.2.0
 * @copyright Copyright (c) 2015, Cedaro
 * @license GPL-2.0+
 */

/**
 * Font control class.
 *
 * @package Cedaro\Theme
 * @since 3.2.0
 */
class Cedaro_Theme_Customize_Control_Font extends WP_Customize_Control {
	/**
	 * Control type.
	 *
	 * @since 3.2.0
	 * @var string
	 */
	public $type = 'cedaro-theme-font';

	/**
	 * Default font.
	 *
	 * @since 3.2.0
	 * @var string
	 */
	public $default_font = '';

	/**
	 * Fonts to exclude from the dropdown.
	 *
	 * @since 3.2.0
	 * @var array
	 */
	public $exclude_fonts = array();

	/**
	 * Font tags.
	 *
	 * @since 3.2.0
	 * @var array
	 */
	public $tags = array();

	/**
	 * Refresh the parameters passed to JavaScript via JSON.
	 *
	 * @since 3.2.0
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
	 * @since 3.2.0
	 */
	public function render_content() {}
}
