<?php
/**
 * Post collections Customizer controller.
 *
 * @since 3.4.0
 *
 * @package Cedaro\Theme
 * @copyright Copyright (c) 2014, Cedaro
 * @license GPL-2.0+
 */

/**
 * Class for post collection support in the Customizer.
 *
 * @package Cedaro\Theme
 * @since 3.4.0
 */
class Cedaro_Theme_PostCollections {
	/**
	 * The theme object.
	 *
	 * @since 3.4.0
	 * @var Cedaro_Theme
	 */
	protected $theme;

	/**
	 * Constructor method.
	 *
	 * @since 3.4.0
	 *
	 * @param Cedaro_Theme $theme Cedaro theme instance.
	 */
	public function __construct( Cedaro_Theme $theme ) {
		$this->theme = $theme;
	}

	/**
	 * Wire up theme hooks for supporting featured content.
	 *
	 * @since 3.4.0
	 */
	public function add_support() {
		add_action( 'wp_ajax_ctpc_find_posts', array( $this, 'ajax_find_posts' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ), 1 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_customizer_controls_assets' ) );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'print_templates' ) );
		add_filter( 'customize_dynamic_setting_args', array( $this, 'filter_dynamic_setting_args' ), 10, 2 );
		add_filter( 'customize_dynamic_setting_class', array( $this, 'filter_customize_dynamic_setting_class' ), 5, 3 );

		return $this;
	}

	/**
	 * Retrieve posts in a collection.
	 *
	 * @since 3.4.0
	 *
	 * @todo Cache this.
	 *
	 * @param string $name Theme mod name.
	 * @return array An array of WP_Post objects.
	 */
	public function get_posts( $name ) {
		$posts    = array();
		$post_ids = $this->get_post_ids( $name );

		$args = array(
			'post_type'           => 'any',
			'post_status'         => 'publish',
			'orderby'             => 'post__in',
			'ignore_sticky_posts' => true,
			'posts_per_page'      => 50,
		);

		$args = apply_filters( $this->theme->prefix . '_post_collection_args', $args, $name );

		if ( ! empty( $post_ids ) ) {
			$args['post__in'] = $post_ids;

			$query = new WP_Query();
			$posts = $query->query( $args );
		}

		return apply_filters( $this->theme->prefix . '_post_collection_posts', $posts, $name, $args );
	}

	/**
	 * Retrieve an array of post IDs in a collection.
	 *
	 * @since 3.4.0
	 *
	 * @param string $name Theme mod name.
	 * @return array
	 */
	public function get_post_ids( $name ) {
		$ids = get_theme_mod( $name, '' );
		return array_map( 'intval', explode( ',', $ids ) );
	}

	/**
	 * Prepare a post for use in the Customizer JavaScript.
	 *
	 * @since 3.4.0
	 *
	 * @param WP_Post $post Post object.
	 * @param string $collection_id Collection identifier.
	 * @return array
	 */
	public static function prepare_post_for_js( $post, $collection_id = '' ) {
		$data = array(
			'id'     => $post->ID,
			'fields' => array(),
			'meta'   => array(),
			'title'  => $post->post_title,
			'type'   => get_post_type_object( $post->post_type )->labels->singular_name,
		);

		$data = apply_filters( 'cedaro_theme_prepare_post_for_js', $data, $post, $collection_id );

		// Create meta entries for each field.
		foreach ( $data['fields'] as $key => $field ) {
			if ( isset( $data['meta'][ $field['key'] ] ) ) {
				continue;
			}

			$value = get_post_meta( $post->ID, $field['key'], true );
			if ( empty( $value ) && isset( $field['default'] ) ) {
				$value = $field['default'];
			}

			if ( 'image' === $field['type'] ) {
				$data['fields'][ $key ]['imageUrl'] = empty( $value ) ? '' : wp_get_attachment_image_url( $value, 'thumbnail' );
			}

			// Set the meta value to the post title if the key doesn't exist yet.
			if ( 'title' === $field['type'] && ! metadata_exists( 'post', $post->ID, $field['key'] ) ) {
				$value = $post->post_title;
			}

			$data['meta'][ $field['key'] ] = $value;
		}

		return $data;
	}

	/**
	 * Register Customizer settings and controls.
	 *
	 * @since 3.4.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function customize_register( $wp_customize ) {
		$wp_customize->register_control_type( 'Cedaro_Theme_Customize_Control_PostCollection' );
	}

	/**
	 * Determine the arguments for a dynamically-created setting.
	 *
	 * @since 3.4.0
	 * @link https://github.com/xwp/wp-customize-posts/blob/744354bb43bf45340c8d3b4e96a9fd4f2a41079d/php/class-wp-customize-posts.php#L329
	 *
	 * @param false|array $args The arguments to the WP_Customize_Setting constructor.
	 * @param string $setting_id ID for dynamic setting, usually coming from `$_POST['customized']`.
	 * @return false|array Setting arguments, false otherwise.
	 */
	public function filter_dynamic_setting_args( $args, $setting_id ) {
		if ( preg_match( Cedaro_Theme_Customize_Setting_PostMeta::SETTING_ID_PATTERN, $setting_id, $matches ) ) {
			if ( false === $args ) {
				$args = array();
			}

			$args['type'] = 'cedaro_theme_postmeta';
		}

		return $args;
	}

	/**
	 * Filters customize_dynamic_setting_class.
	 *
	 * @since 3.4.0
	 * @link https://github.com/xwp/wp-customize-posts/blob/744354bb43bf45340c8d3b4e96a9fd4f2a41079d/php/class-wp-customize-posts.php#L375
	 *
	 * @param string $class Setting class.
	 * @param string $setting_id Setting ID.
	 * @param array $args Setting arguments.
	 * @return string
	 */
	public function filter_customize_dynamic_setting_class( $class, $setting_id, $args ) {
		if ( isset( $args['type'] ) && 'cedaro_theme_postmeta' === $args['type'] ) {
			$class = 'Cedaro_Theme_Customize_Setting_PostMeta';
		}

		return $class;
	}

	/**
	 * Enqueue scripts to display in the Customizer preview.
	 *
	 * @since 3.4.0
	 */
	public function enqueue_customizer_controls_assets() {
		wp_enqueue_script(
			'cedaro-theme-customize-controls-post-collection',
			$this->theme->get_library_uri( 'assets/js/customize-controls-post-collection.js' ),
			array( 'customize-controls', 'jquery', 'jquery-ui-sortable', 'jquery-ui-droppable', 'wp-backbone', 'wp-util' ),
			'1.0.0',
			true
		);

		wp_enqueue_style(
			'cedaro-theme-customize-controls-post-collection',
			$this->theme->get_library_uri( 'assets/css/customize-controls-post-collection.css' ),
			array( 'dashicons' )
		);
	}

	/**
	 * Print JavaScript templates in the Customizer footer.
	 *
	 * @since 3.4.0
	 */
	public static function print_templates() {
		?>
		<script type="text/html" id="tmpl-ctpc-item">
			<div class="ctpc-item-header">
				<h4 class="ctpc-item-title"><span>{{ data.title }}</span></h4>

				<button type="button" class="ctpc-item-toggle js-toggle">
					<span class="screen-reader-text">{{ data.labels.togglePost }}</span>
				</button>

				<button type="button" class="ctpc-item-delete js-remove">
					<span class="screen-reader-text">{{ data.labels.removePost }}</span>
				</button>
			</div>

			<div class="ctpc-item-body"></div>

			<div class="ctpc-item-footer">
				<div class="ctpc-item-actions">
					<a class="ctpc-delete js-remove">{{ data.labels.remove }}</a>
				</div>
			</div>
		</script>

		<script type="text/html" id="tmpl-ctpc-item-field">
			<label>
				{{ data.label }}<br>
				<input type="text" value="{{ data.value }}">
			</label>
		</script>

		<script type="text/html" id="tmpl-ctpc-item-select-field">
			<label>
				{{ data.label }}<br>
				<select>
					<# _.each( data.choices, function( label, value ) { #>
						<option value="{{ value }}"<# if ( data.value === value ) { #> selected="selected"<# } #>>{{ label }}</option>
					<# } ); #>
				</select>
			</label>
		</script>

		<script type="text/html" id="tmpl-ctpc-drawer-title">
			<button type="button" class="customize-section-back" tabindex="-1">
				<span class="screen-reader-text"><?php esc_html_e( 'Back', 'marquee' ); ?></span>
			</button>
			<h3>
				<span class="customize-action">
					<?php
					/* translators: &#9656; is the unicode right-pointing triangle, and %s is the control label in the Customizer */
					printf( __( 'Customizing &#9656; %s', 'marquee' ), '{{ data.customizeAction }}' );
					?>
				</span>
				{{ data.title }}
			</h3>
		</script>

		<script type="text/html" id="tmpl-ctpc-search-group">
			<label class="screen-reader-text" for="ctpc-search-group-field">{{ data.labels.searchPosts }}</label>
			<input type="text" id="ctpc-search-group-field" placeholder="{{{ data.labels.searchPostsPlaceholder }}}" class="ctpc-search-group-field">
			<div class="search-icon" aria-hidden="true"></div>
			<button type="button" class="clear-results"><span class="screen-reader-text">{{ data.labels.clearResults }}</span></button>
		</script>

		<script type="text/html" id="tmpl-ctpc-search-result">
			<span class="ctpc-search-results-item-type">{{ data.type }}</span>
			<span class="ctpc-search-results-item-title">{{ data.title }}</span>

			<button type="button" class="ctpc-search-results-item-add button-link">
				<span class="screen-reader-text">{{ data.labels.addPost }}</span>
			</button>
		</script>
		<?php
	}

	/**
	 * Ajax handler for finding posts.
	 *
	 * @since 3.4.0
	 *
	 * @see wp_ajax_find_posts()
	 */
	public function ajax_find_posts() {
		check_ajax_referer( 'find-posts' );

		$post_types = array();

		if ( ! empty( $_POST['post_types'] ) ) { // WPCS: Input var OK.
			$post_type_names = array_map( 'sanitize_text_field', wp_unslash( $_POST['post_types'] ) ); // WPCS: Input var OK.
			foreach ( $post_type_names as $post_type ) {
				$post_types[ $post_type ] = get_post_type_object( $post_type );
			}
		}

		if ( empty( $post_types ) ) {
			$post_types['post'] = get_post_type_object( 'post' );
		}

		$args = array(
			'post_type'      => array_keys( $post_types ),
			'post_status'    => 'publish',
			'post__not_in'   => isset( $_POST['not_in'] ) ? wp_parse_id_list( $_POST['not_in'] ) : null, // WPCS: Input var OK.
			'posts_per_page' => 50,
		);

		if ( ! empty( $_POST['s'] ) ) { // WPCS: Input var OK.
			$args['s'] = sanitize_text_field( wp_unslash( $_POST['s'] ) ); // WPCS: Input var OK.
		}

		$query = new WP_Query();
		$posts = $query->query( $args );

		if ( count( $posts ) < 1 ) {
			wp_send_json_error( esc_html__( 'No results found.', 'marquee' ) );
		}

		$collection_id = preg_replace( '/[^a-z0-9_]/', '', $_POST['collection_id'] );

		foreach ( $posts as $post ) {
			$data[] = self::prepare_post_for_js( $post, $collection_id );
		}

		wp_send_json_success( $data );
	}

	/**
	 * Sanitization callback for lists of IDs in the Customizer.
	 *
	 * @since 3.4.0
	 *
	 * @param string $value Setting value.
	 * @return string Comma-separated list of IDs.
	 */
	public static function sanitize_id_list( $value ) {
		$value = implode( ',', array_unique( wp_parse_id_list( $value ) ) );
		return ( '0' === $value ) ? '' : $value;
	}
}
