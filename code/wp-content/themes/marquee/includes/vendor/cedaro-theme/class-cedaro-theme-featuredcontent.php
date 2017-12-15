<?php
/**
 * Featured content feature.
 *
 * @since 3.1.0
 *
 * @package Cedaro\Theme
 * @copyright Copyright (c) 2014, Cedaro
 * @license GPL-2.0+
 */

/**
 * Class for the featured content feature.
 *
 * @package Cedaro\Theme
 * @since 3.1.0
 */
class Cedaro_Theme_FeaturedContent {
	/**
	 * Theme mod key for featured post IDs.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	const FEATURED_POSTS_THEME_MOD_KEY = 'featured_content_ids';

	/**
	 * Maximum number of posts to feature.
	 *
	 * @since 3.1.0
	 * @var int
	 */
	protected $max_posts = 15;

	/**
	 * Meta fields to register.
	 *
	 * @since 3.4.0
	 * @var array
	 */
	protected $meta_keys = array();

	/**
	 * Post types that can be featured.
	 *
	 * @since 3.1.0
	 * @var array
	 */
	protected $post_types = array( 'post', 'page' );

	/**
	 * The theme object.
	 *
	 * @since 3.1.0
	 * @var Cedaro_Theme
	 */
	protected $theme;

	/**
	 * Constructor method.
	 *
	 * @since 3.1.0
	 *
	 * @param Cedaro_Theme $theme Cedaro theme instance.
	 */
	public function __construct( Cedaro_Theme $theme ) {
		$this->theme = $theme;
	}

	/*
	 * Public API methods.
	 */

	/**
	 * Wire up theme hooks for supporting featured content.
	 *
	 * @since 3.1.0
	 */
	public function add_support() {
		$this->theme->post_collections->add_support();

		add_action( 'pre_get_posts', array( $this, 'exclude_featured_posts' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_filter( 'cedaro_theme_prepare_post_for_js', array( $this, 'filter_post_for_js' ), 10, 3 );
		add_action( 'post_submitbox_misc_actions', array( $this, 'display_feature_post_checkbox' ) );
		add_action( 'save_post', array( $this, 'on_save_post' ) );

		if ( in_array( '_ctfc_image_id', $this->meta_keys, true ) ) {
			register_meta( 'post', '_ctfc_image_id', array(
				'auth_callback'     => '__return_true',
				'sanitize_callback' => 'absint',
				'single'            => true,
				'type'              => 'integer',
			) );
		}

		if ( in_array( '_ctfc_cta', $this->meta_keys, true ) ) {
			register_meta( 'post', '_ctfc_cta', array(
				'auth_callback'     => '__return_true',
				'sanitize_callback' => 'sanitize_text_field',
				'single'            => true,
				'type'              => 'string',
			) );
		}

		return $this;
	}

	/**
	 * Register post types that can be featured.
	 *
	 * @since 3.1.0
	 *
	 * @param array|string $post_types Post types.
	 * @return $this
	 */
	public function add_post_types( $post_types ) {
		$this->post_types = array_merge( $this->post_types, (array) $post_types );
		return $this;
	}

	/**
	 * Maxmium number of posts to feature.
	 *
	 * @since 3.1.0
	 *
	 * @param int $count Number of posts to feature.
	 * @return $this
	 */
	public function set_max_posts( $count ) {
		$this->max_posts = absint( $count );
		return $this;
	}

	/**
	 * Register the meta fields to display for each featured item.
	 *
	 * @since 3.4.0
	 *
	 * @param array|string $fields Meta field.
	 * @return $this
	 */
	public function add_post_meta_fields( $fields ) {
		if ( ! is_array( $fields ) ) {
			$fields = array( $fields );
		}

		foreach ( $fields as $key ) {
			$this->meta_keys[] = sprintf( '_ctfc_%s', $key );
		}

		return $this;
	}

	/**
	 * Retrieve featured posts.
	 *
	 * @since 3.1.0
	 *
	 * @todo Cache this.
	 *
	 * @return array An array of WP_Post objects.
	 */
	public function get_posts() {
		$featured_posts = array();
		$post_ids       = $this->get_post_ids();

		$args = apply_filters( $this->theme->prefix . '_featured_content_args', array(
			'posts_per_page'      => $this->max_posts,
			'post_type'           => $this->post_types,
			'orderby'             => 'post__in',
			'ignore_sticky_posts' => true,
		) );

		if ( ! empty( $post_ids ) ) {
			$args['post__in'] = $post_ids;

			$query = new WP_Query();
			$featured_posts = $query->query( $args );
		}

		return apply_filters( $this->theme->prefix . '_featured_posts', $featured_posts, $args );
	}

	/**
	 * Retrieve an array of featured post IDs.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function get_post_ids() {
		$ids = get_theme_mod( self::FEATURED_POSTS_THEME_MOD_KEY, '' );
		return array_map( 'intval', explode( ',', $ids ) );
	}

	/**
	 * Whether a post is featured.
	 *
	 * @since 3.3.0
	 *
	 * @param int $post_id Post ID.
	 * @return boolean
	 */
	public function is_post_featured( $post_id ) {
		$featured_ids = $this->get_post_ids();
		return in_array( (int) $post_id, $featured_ids, true );
	}

	/**
	 * Feature a post.
	 *
	 * @since 3.3.0
	 *
	 * @param int $post_id Post ID.
	 * @return $this
	 */
	public function add_featured_post( $post_id ) {
		if ( ! $this->is_post_featured( $post_id ) ) {
			$ids = $this->get_post_ids();
			array_unshift( $ids, $post_id );
			set_theme_mod( self::FEATURED_POSTS_THEME_MOD_KEY, Cedaro_Theme_Customize_PostCollections::sanitize_id_list( $ids ) );
		}

		return $this;
	}

	/**
	 * Remove a featured post.
	 *
	 * @since 3.3.0
	 *
	 * @param int $post_id Post ID.
	 * @return $this
	 */
	public function remove_featured_post( $post_id ) {
		if ( $this->is_post_featured( $post_id ) ) {
			$ids = $this->get_post_ids();
			$ids = array_diff( $ids, array( $post_id ) );
			set_theme_mod( self::FEATURED_POSTS_THEME_MOD_KEY, Cedaro_Theme_Customize_PostCollections::sanitize_id_list( $ids ) );
		}

		return $this;
	}

	/**
	 * Retrieve the featured image ID for a post.
	 *
	 * @since 3.3.0
	 *
	 * @param int $post_id Post ID.
	 * @return int
	 */
	public static function get_post_image_id( $post_id ) {
		$image_id = get_post_meta( $post_id, '_ctfc_image_id', true );
		if ( empty( $image_id ) ) {
			$image_id = 0;
		}
		return $image_id;
	}

	/**
	 * Retrieve the featured image URL for a post.
	 *
	 * @since 3.3.0
	 *
	 * @param int $post_id Post ID.
	 * @param string $size Image size.
	 * @return string
	 */
	public static function get_post_image_url( $post_id, $size = 'thumbnail' ) {
		$image_url = '';
		$image_id  = self::get_post_image_id( $post_id );

		if ( ! empty( $image_id ) ) {
			$image_url = wp_get_attachment_image_url( $image_id, $size );
		}

		return $image_url;
	}

	/*
	 * Hook callbacks.
	 */

	/**
	 * Prepare a post for use in the Customizer JavaScript.
	 *
	 * @since 3.4.0
	 *
	 * @param array   $data Post data.
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	public function filter_post_for_js( $data, $post, $collection_id ) {
		if ( 'cedaro_featured_content' !== $collection_id ) {
			return $data;
		}

		$data['meta']['_ctfc_cta']      = get_post_meta( $post->ID, '_ctfc_cta', true );
		$data['meta']['_ctfc_image_id'] = self::get_post_image_id( $post->ID );

		if ( in_array( '_ctfc_image_id', $this->meta_keys, true ) ) {
			$data['fields'][] = array(
				'imageUrl' => self::get_post_image_url( $post->ID ),
				'key'      => '_ctfc_image_id',
				'type'     => 'image',
			);
		}

		if ( in_array( '_ctfc_cta', $this->meta_keys, true ) ) {
			$data['fields'][] = array(
				'label' => esc_html__( 'Call to Action', 'marquee' ),
				'key'   => '_ctfc_cta',
				'type'  => 'text',
			);
		}

		return $data;
	}

	/**
	 * Exclude featured posts from the blog query when the blog is the front-page.
	 *
	 * Filter the home page posts, and remove any featured post ID's from it.
	 * Hooked onto the 'pre_get_posts' action, this changes the parameters of the
	 * query before it retrieves any posts.
	 *
	 * @since 3.1.0
	 *
	 * @param WP_Query $wp_query WordPress query object.
	 * @return WP_Query Possibly modified WP_Query
	 */
	public function exclude_featured_posts( $wp_query ) {
		// Bail if not home or not main query.
		if ( ! $wp_query->is_home() || ! $wp_query->is_main_query() ) {
			return;
		}

		$page_on_front = get_option( 'page_on_front' );

		// Bail if the blog page is not the front page.
		if ( ! empty( $page_on_front ) ) {
			return;
		}

		$featured = $this->get_posts();

		// Bail if no featured posts.
		if ( empty( $featured ) ) {
			return;
		}

		$featured = wp_list_pluck( (array) $featured, 'ID' );
		$featured = array_map( 'absint', $featured );

		// We need to respect post ids already in the blacklist.
		$post__not_in = $wp_query->get( 'post__not_in' );

		if ( ! empty( $post__not_in ) ) {
			$featured = array_merge( (array) $post__not_in, $featured );
			$featured = array_unique( $featured );
		}

		$wp_query->set( 'post__not_in', $featured );
	}

	/**
	 * Register Customizer settings and controls.
	 *
	 * @since 3.1.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function customize_register( $wp_customize ) {
		$wp_customize->add_section( 'featured_content', array(
			'title'       => esc_html__( 'Featured Content', 'marquee' ),
			'description' => sprintf( esc_html__( 'Your theme supports up to %d posts in its featured content area.', 'marquee' ), $this->max_posts ),
			'priority'    => 130,
		) );

		$wp_customize->add_setting( self::FEATURED_POSTS_THEME_MOD_KEY, array(
			'sanitize_callback' => array( 'Cedaro_Theme_PostCollections', 'sanitize_id_list' ),
		) );

		$wp_customize->add_control( new Cedaro_Theme_Customize_Control_PostCollection( $wp_customize, 'cedaro_featured_content', array(
			'label'      => esc_html__( 'Featured Content', 'marquee' ),
			'section'    => 'featured_content',
			'settings'   => self::FEATURED_POSTS_THEME_MOD_KEY,
			'post_types' => $this->post_types,
		) ) );
	}

	/**
	 * Display a checkbox in the post submit meta box to feature the post.
	 *
	 * @since 3.3.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_feature_post_checkbox( $post = null ) {
		$post = get_post( $post );

		if ( ! in_array( $post->post_type, $this->post_types, true ) ) {
			return;
		}

		wp_nonce_field( 'feature-post_' . $post->ID, 'cedaro_theme_feature_post_nonce' );
		?>
		<div class="misc-pub-section">
			<label for="cedaro-theme-feature-post">
				<input type="checkbox" id="cedaro-theme-feature-post" name="cedaro_theme_feature_post" value="yes"<?php checked( $this->is_post_featured( $post->ID ) ); ?>>
				<?php esc_html_e( 'Add to featured content?', 'marquee' ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Add or remove a post from featured content when saved.
	 *
	 * @since 3.3.0
	 *
	 * @param int $post_id Post ID.
	 */
	public function on_save_post( $post_id ) {
		$is_autosave        = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_revision        = wp_is_post_revision( $post_id );
		$is_valid_nonce     = isset( $_POST['cedaro_theme_feature_post_nonce'] ) && wp_verify_nonce( $_POST['cedaro_theme_feature_post_nonce'], 'feature-post_' . $post_id );
		$is_valid_post_type = in_array( get_post_type( $post_id ), $this->post_types, true );

		// Bail if the data shouldn't be saved or intention can't be verified.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce || ! $is_valid_post_type ) {
			return;
		}

		if ( isset( $_POST['cedaro_theme_feature_post'] ) ) {
			$this->add_featured_post( $post_id );
		} else {
			$this->remove_featured_post( $post_id );
		}
	}
}
