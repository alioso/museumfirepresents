<?php
/**
 * Post Collection control for the Customizer.
 *
 * @package Cedaro\Theme
 * @license GPL-2.0+
 * @since 3.4.0
 */

/**
 * Post Collection Customizer control.
 *
 * @package Cedaro\Theme
 * @since 3.4.0
 */
class Cedaro_Theme_Customize_Control_PostCollection extends WP_Customize_Control {
	/**
	 * Control type.
	 *
	 * @since 3.4.0
	 * @var string
	 */
	public $type = 'cedaro-theme-post-collection';

	/**
	 * Post types.
	 *
	 * @since 3.4.0
	 * @var array
	 */
	public $post_types = array( 'page', 'post' );

	/**
	 * Labels.
	 *
	 * @since 3.4.0
	 * @access public
	 * @var array
	 */
	public $labels = array();

	/**
	 * Constructor.
	 *
	 * @since 3.4.0
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Control ID.
	 * @param array                $args    Optional. Arguments to override class property defaults.
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );

		$this->labels = wp_parse_args( $this->labels, array(
			'addPost'                => esc_html__( 'Add Post', 'marquee' ),
			'addPosts'               => esc_html__( 'Add Posts', 'marquee' ),
			'clearResults'           => esc_html__( 'Clear Results', 'marquee' ),
			'featuredImage'          => esc_html__( 'Featured Image', 'marquee' ),
			'remove'                 => esc_html__( 'Remove', 'marquee' ),
			'removePost'             => esc_html__( 'Remove Post', 'marquee' ),
			'setFeaturedImage'       => esc_html__( 'Set Featured Image', 'marquee' ),
			'togglePost'             => esc_html__( 'Toggle Post', 'marquee' ),
			'searchPosts'            => esc_html__( 'Search Posts', 'marquee' ),
			'searchPostsPlaceholder' => esc_html__( 'Search posts&hellip;', 'marquee' ),
		) );
	}

	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @since 3.4.0
	 */
	public function enqueue() {
		wp_enqueue_style( 'cedaro-theme-customize-controls-post-collection' );
		wp_enqueue_script( 'cedaro-theme-customize-controls-collection' );
	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @since 3.4.0
	 * @uses WP_Customize_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();

		$this->json['labels']      = $this->labels;
		$this->json['posts']       = $this->get_posts();
		$this->json['postTypes']   = $this->post_types;
		$this->json['searchNonce'] = wp_create_nonce( 'find-posts' );
	}

	/**
	 * Don't render any content for this control from PHP.
	 *
	 * @since 3.4.0
	 *
	 * @see WP_Customize_Post_Collection_Control::content_template()
	 */
	public function render_content() {}

	/**
	 * An Underscore (JS) template for this control's content (but not its container).
	 *
	 * @see WP_Customize_Control::print_template()
	 *
	 * @since 3.4.0
	 */
	protected function content_template() {
		?>
		<label>
			<# if ( data.label ) { #>
				<span class="customize-control-title">{{ data.label }}</span>
			<# } #>
			<# if ( data.description ) { #>
				<span class="description customize-control-description">{{{ data.description }}}</span>
			<# } #>
		</label>
		<?php
	}

	/**
	 * Retrieve posts.
	 *
	 * @since 3.4.0
	 *
	 * @return array
	 */
	protected function get_posts() {
		$data  = array();
		$value = $this->value();

		if ( ! empty( $value ) ) {
			$query = new WP_Query();
			$posts = $query->query( array(
				'post_type'      => $this->post_types,
				'post_status'    => 'any',
				'post__in'       => array_filter( array_map( 'absint', explode( ',', $value ) ) ),
				'orderby'        => 'post__in',
				'posts_per_page' => 20,
			) );
		}

		if ( ! empty( $posts ) ) {
			$i = 0;
			foreach ( $posts as $post ) {
				$data[] = Cedaro_Theme_PostCollections::prepare_post_for_js( $post, $this->id );
			}
		}

		return $data;
	}
}
