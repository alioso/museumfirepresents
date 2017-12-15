<?php
/**
 * Marquee script and style dependencies.
 *
 * @package Marquee
 * @since 1.0.0
 */

/**
 * Marquee dependencies class.
 *
 * @package Marquee
 * @since 1.0.0
 */
abstract class Marquee_Dependencies {
	/**
	 * WordPress dependencies object.
	 *
	 * Either WP_Styles or WP_Scripts.
	 *
	 * @since 1.0.0
	 * @var WP_Dependencies
	 */
	protected $dependencies;

	/**
	 * Constructor method.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Dependencies $dependencies WordPress dependencies object.
	 */
	public function __construct( WP_Dependencies $dependencies ) {
		$this->dependencies = $dependencies;
	}

	/**
	 * Retrieve handles of dependencies that have been loaded.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_loaded_handles() {
		return isset( $this->dependencies->done ) ? $this->dependencies->done : array();
	}

	/**
	 * Retrieve a list of loaded dependency objects.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_loaded_data() {
		$dependencies = array();

		foreach ( $this->get_loaded_handles() as $handle ) {
			if ( isset( $this->dependencies->registered[ $handle ] ) ) {
				$dependencies[] = $this->get_data( $handle );
			}
		}

		return $dependencies;
	}

	/**
	 * Retrieve data about a dependency.
	 *
	 * @since 1.0.0
	 *
	 * @param string $handle Dependency identifier.
	 * @return array
	 */
	abstract protected function get_data( $handle );

	/**
	 * Retrieve the URL for a dependency.
	 *
	 * @since 1.0.0
	 *
	 * @param string $handle Dependency identifier.
	 * @return string
	 */
	protected function get_source( $handle ) {
		$source = $this->dependencies->registered[ $handle ]->src;
		if ( strpos( $source, 'http' ) !== 0 ) {
			$source = $this->dependencies->base_url . $source;
		}

		// Version and additional arguments.
		$version = $this->get_version_string( $handle );
		if ( ! empty( $version ) ) {
			$source = add_query_arg( 'ver', $version, $source );
		}

		return $source;
	}

	/**
	 * Retrieve the version query string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $handle Dependency identifier.
	 * @return string
	 */
	protected function get_version_string( $handle ) {
		$item = $this->dependencies->registered[ $handle ];

		if ( null === $item->ver ) {
			$version = '';
		} elseif ( $item->ver ) {
			$version = $item->ver;
		} else {
			$version = $this->dependencies->default_version;
		}

		if ( isset( $this->dependencies->args[ $handle ] ) ) {
			$version = $version ? $version . '&amp;' . $this->dependencies->args[ $handle ] : $this->dependencies->args[ $handle ];
		}

		return $version;
	}
}

/**
 * Marquee script dependencies class.
 *
 * @package Marquee
 * @since 1.0.0
 */
class Marquee_Dependencies_Scripts extends Marquee_Dependencies {
	/**
	 * Retrieve data about a script.
	 *
	 * @since 1.0.0
	 *
	 * @param string $handle Script identifier.
	 * @return array
	 */
	protected function get_data( $handle ) {
		return array(
			'handle'     => $handle,
			'footer'     => is_array( $this->dependencies->in_footer ) && in_array( $handle, $this->dependencies->in_footer ),
			'extra_data' => $this->dependencies->print_extra_script( $handle, false ),
			'src'        => $this->get_source( $handle ),
		);
	}
}

/**
 * Marquee style sheet dependencies class.
 *
 * @package Marquee
 * @since 1.0.0
 */
class Marquee_Dependencies_Styles extends Marquee_Dependencies  {
	/**
	 * Retrieve data about a style.
	 *
	 * @since 1.0.0
	 *
	 * @param string $handle Style identifier.
	 * @return array
	 */
	protected function get_data( $handle ) {
		$data = array(
			'handle' => $handle,
			'media'  => 'all',
			'src'    => $this->get_source( $handle ),
		);

		return $this->add_extra_data( $handle, $data );
	}

	/**
	 * Add additional data about the style sheet.
	 *
	 * @since 1.0.0
	 *
	 * @param string $handle Style identifier.
	 * @param array  $data Style data.
	 * @return array
	 */
	protected function add_extra_data( $handle, $data ) {
		$item = $this->dependencies->registered[ $handle ];

		// Parse style sheet's conditional comments if present,
		// converting to logic executable in JavaScript.
		if ( isset( $item->extra['conditional'] ) && $item->extra['conditional'] ) {
			// First, convert conditional comment operators to standard logical
			// operators. %ver is replaced in JS with the IE version.
			$data['conditional'] = str_replace(
				array(
					'lte',
					'lt',
					'gte',
					'gt',
				),
				array(
					'%ver <=',
					'%ver <',
					'%ver >=',
					'%ver >',
				),
				$item->extra['conditional']
			);

			// Next, replace any !IE checks. These shouldn't be present since
			// WP's conditional style sheet implementation doesn't support them,
			// but someone could be _doing_it_wrong().
			$data['conditional'] = preg_replace( '#!\s*IE(\s*\d+){0}#i', '1==2', $data['conditional'] );

			// Lastly, remove the IE strings.
			$data['conditional'] = str_replace( 'IE', '', $data['conditional'] );
		}

		// Parse requested media context for stylesheet.
		if ( isset( $item->args ) ) {
			$data['media'] = esc_attr( $item->args );
		}

		return $data;
	}
}
