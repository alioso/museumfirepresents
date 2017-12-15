<?php
/**
 * Player.
 *
 * @package   Billboard
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Player class.
 *
 * @package Billboard
 * @since   1.0.0
 */
class Billboard_Provider_Player extends Billboard_AbstractProvider {
	/**
	 * Playlist tracks.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $tracks;

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {
		add_action( 'customize_register', array( $this, 'customize_register' ) );

		if ( ! $this->plugin->is_active() ) {
			return;
		}

		add_action( 'body_class', array( $this, 'add_body_class' ) );
		add_action( 'wp_footer',  array( $this, 'load_template' ) );
		add_action( 'init',       array( $this, 'disable_cuebar' ), 11 );
	}

	/**
	 * Add a body class when the player is active.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $classes HTML classes.
	 * @return array
	 */
	public function add_body_class( $classes ) {
		if ( $this->is_active() ) {
			$classes[] = 'has-billboard-player';
		}

		return $classes;
	}

	/**
	 * Load the player template part.
	 *
	 * @since 1.0.0
	 */
	public function load_template() {
		if ( ! $this->is_active() ) {
			return;
		}

		$tracks = $this->get_tracks();

		wp_enqueue_script(
			'billboard-player',
			$this->plugin->get_url( 'assets/js/player.js' ),
			array( 'jquery-cue' ),
			'',
			true
		);

		$settings = array(
			'tracks' => $tracks,
		);

		echo '<div class="cue-playlist-container">';
			include( $this->plugin->get_path( 'templates/player.php' ) );
			echo '<script type="application/json" id="billboard-player-settings">' . wp_json_encode( $settings ) . '</script>';
		echo '</div>';
	}

	/**
	 * Register Customizer settings and controls.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_Customize $wp_customize Cutomizer manager.
	 */
	public function customize_register( $wp_customize ) {
		$description = '';

		$playlists = get_posts( array(
			'post_type'      => 'cue_playlist',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'asc',
		) );

		if ( empty( $playlists ) ) {
			$playlists = array();

			$description = sprintf(
				__( '<a href="%s">Create a playlist</a> for this player.', 'billboard' ),
				admin_url( 'post-new.php?post_type=cue_playlist' )
			);
		} else {
			// Create an array: ID => post_title.
			$playlists = wp_list_pluck( $playlists, 'post_title', 'ID' );
		}

		$playlists = array( 0 => '' ) + $playlists;

		$wp_customize->add_setting( 'billboard[playlist]', array(
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'absint',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'billboard_playlist', array(
			'choices'     => $playlists,
			'description' => $description,
			'label'       => esc_html__( 'Playlist', 'billboard' ),
			'priority'    => 50,
			'section'     => 'billboard_content',
			'settings'    => 'billboard[playlist]',
			'type'        => 'select',
		) );
	}

	/**
	 * Disable CueBar.
	 *
	 * @since 1.0.2
	 */
	public function disable_cuebar() {
		if ( ! function_exists( 'cuebar' ) ) {
			return;
		}

		remove_action( 'wp_footer', array( cuebar(), 'display_player' ) );
	}

	/**
	 * Whether the player is active.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	protected function is_active() {
		$tracks = $this->get_tracks();
		return ! empty( $tracks );
	}

	/**
	 * Retrieve tracks.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_tracks() {
		if ( ! is_null( $this->tracks ) ) {
			return $this->tracks;
		}

		$playlist_id = $this->plugin->get_setting( 'playlist' );
		if ( empty( $playlist_id ) ) {
			return array();
		}

		$this->tracks = get_cue_playlist_tracks( $playlist_id, 'wp-playlist' );

		return $this->tracks;
	}
}
