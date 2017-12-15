<?php
/**
 * Post meta Customizer setting.
 *
 * @package Cedaro\Theme
 * @license GPL-2.0+
 * @since 3.3.0
 */

/**
 * Post meta Customizer setting class.
 *
 * @package Cedaro\Theme
 * @since 3.3.0
 * @see WP_Customize_Setting
 */
class Cedaro_Theme_Customize_Setting_PostMeta extends WP_Customize_Setting {
	/**
	 * Setting id pattern.
	 *
	 * Helps determine whether or not a given setting is post meta.
	 *
	 * @var string
	 * @since 3.3.0
	 * @link https://github.com/xwp/wp-customize-posts/blob/744354bb43bf45340c8d3b4e96a9fd4f2a41079d/php/class-wp-customize-postmeta-setting.php
	 */
	const SETTING_ID_PATTERN = '/^cedaro_theme_postmeta\[(?P<post_id>-?\d+)\]\[(?P<meta_key>.+)\]$/';

	/**
	 * Type of setting.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	public $type = 'cedaro_theme_postmeta';

	/**
	 * Post ID.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	public $post_id;

	/**
	 * Meta key.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	public $meta_key;

	/**
	 * Constructor method.
	 *
	 * @since 3.3.0
	 *
	 * @param WP_Customize_Manager $manager Manager.
	 * @param string $id Setting ID.
	 * @param array $args Setting args.
	 * @throws Exception If the ID is in an invalid format.
	 */
	public function __construct( WP_Customize_Manager $manager, $id, $args = array() ) {
		if ( ! preg_match( self::SETTING_ID_PATTERN, $id, $matches ) ) {
			throw new Exception( 'Illegal setting id: ' . $id );
		}

		$args['post_id']  = intval( $matches['post_id'] );
		$args['meta_key'] = $matches['meta_key'];

		// Determine the capability required for editing this.
		$post_type        = get_post_type( $args['post_id'] );
		$post_type_object = get_post_type_object( $post_type );

		$can_edit = current_user_can( $post_type_object->cap->edit_posts );
		if ( $can_edit ) {
			$can_edit = current_user_can( 'edit_post_meta', $args['post_id'], $args['meta_key'] );
		}

		if ( ! $can_edit ) {
			$args['capability'] = 'do_not_allow';
		} elseif ( ! isset( $args['capability'] ) ) {
			$args['capability'] = $post_type_object->cap->edit_posts;
		}

		add_action( 'customize_preview_cedaro_theme_postmeta', array( $this, 'register_preview_filter' ) );

		parent::__construct( $manager, $id, $args );
	}

	/**
	 * Retrieve the setting value.
	 *
	 * @since 3.3.0
	 *
	 * @return mixed
	 */
	public function value() {
		return get_post_meta( $this->post_id, $this->meta_key, true );
	}

	/**
	 * Save the value of the setting.
	 *
	 * @since 3.3.0
	 *
	 * @param mixed $value The value to update.
	 */
	public function update( $value ) {
		$result = update_post_meta( $this->post_id, $this->meta_key, $value );
		return false !== $result;
	}

	/**
	 * Sanitize the setting value.
	 *
	 * @since 3.3.0
	 *
	 * @param  mixed $value Setting value.
	 * @return mixed
	 */
	public function sanitize( $value ) {
		$meta_keys = get_registered_meta_keys( 'post' );

		// Blank out the value if a sanitize callback hasn't been registered for
		// the meta key.
		if ( isset( $meta_keys[ $this->meta_key ]['sanitize_callback'] ) ) {
			$value = sanitize_meta( $this->meta_key, $value, 'post' );
		} else {
			$value = null;
		}

		return $value;
	}

	/**
	 * Register a filter to update the setting value in the previewer.
	 *
	 * @since 3.3.0
	 */
	public function register_preview_filter() {
		add_filter( 'get_post_metadata', array( $this, 'preview_filter' ), 10, 3 );
	}

	/**
	 * Filter the setting value in the previewer.
	 *
	 * @since 3.3.0
	 *
	 * @param mixed $value Setting value.
	 * @param int $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @return mixed
	 */
	public function preview_filter( $value, $post_id, $meta_key ) {
		if ( $post_id !== $this->post_id || $meta_key !== $this->meta_key ) {
			return $value;
		}

		return $this->post_value();
	}
}
