<?php
/**
 * Metric repository.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Metric repository class.
 *
 * @package CuePro
 * @since   1.0.0
 */
class CuePro_MetricRepository {
	/**
	 * MySQL datetime format.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

	/**
	 * WPDB instance.
	 *
	 * @since 1.0.0
	 * @var wpdb
	 */
	protected $db;

	/**
	 * Database name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $db_name;

	/**
	 * End MySQL datetime.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $end_date;

	/**
	 * Interval in days.
	 *
	 * @since 1.0.0
	 * @var integer
	 */
	protected $interval = 7;

	/**
	 * Time zone offset.
	 *
	 * @since 1.0.0
	 * @var double
	 */
	protected $offset;

	/**
	 * Start MySQL datetime.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $start_date;

	/**
	 * Constructor method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;

		$this->db         = $wpdb;
		$this->db_name    = $wpdb->prefix . 'cue_events';
		$this->offset     = $this->get_timezone_offset();
		$this->end_date   = date( self::MYSQL_DATETIME_FORMAT, time() );
		$this->start_date = date( self::MYSQL_DATETIME_FORMAT, strtotime( sprintf( '-%d days', $this->interval ) ) );
	}

	/**
	 * Set the interval.
	 *
	 * @since 1.0.0
	 *
	 * @param  integer $interval Interval in days.
	 * @return $this
	 */
	public function set_interval( $interval ) {
		$this->interval   = absint( $interval );
		$this->start_date = date( self::MYSQL_DATETIME_FORMAT, strtotime( sprintf( '-%d days', $this->interval ) ) );

		return $this;
	}

	/**
	 * Retrieve the total number of plays during the specified interval.
	 *
	 * @since 1.0.0
	 *
	 * @return integer
	 */
	public function get_play_count() {
		$sql = $this->db->prepare(
			"SELECT COUNT( * )
			FROM {$this->db_name}
			WHERE
				action = 'play' AND
				target_time = 0 AND
				created BETWEEN %s AND %s",
			$this->start_date,
			$this->end_date
		);

		return (int) $this->db->get_var( $sql );
	}

	/**
	 * Retrieve the total number of listeners during the specified interval.
	 *
	 * @since 1.0.0
	 *
	 * @return integer
	 */
	public function get_listener_count() {
		$sql = $this->db->prepare(
			"SELECT COUNT( DISTINCT client_uid )
			FROM {$this->db_name}
			WHERE
				action = 'play' AND
				target_time = 0 AND
				created BETWEEN %s AND %s",
			$this->start_date,
			$this->end_date
		);

		return (int) $this->db->get_var( $sql );
	}

	/**
	 * Retrieve the number of tracks played during the specified interval.
	 *
	 * @since 1.0.0
	 *
	 * @return integer
	 */
	public function get_track_count() {
		$sql = $this->db->prepare(
			"SELECT COUNT( DISTINCT target_url )
			FROM {$this->db_name}
			WHERE
				action = 'play' AND
				target_time = 0 AND
				created BETWEEN %s AND %s",
			$this->start_date,
			$this->end_date
		);

		return (int) $this->db->get_var( $sql );
	}

	/**
	 * Retrieve the total plays per day within the specified interval.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_play_count_by_day() {
		$sql = $this->db->prepare(
			"SELECT
				DATE_FORMAT( DATE_ADD( created, INTERVAL %d HOUR ), '%%c/%%e' ) AS date,
				COUNT( IF( action = 'play', 1, NULL ) ) AS play_count,
				COUNT( IF( action = 'complete', 1, NULL ) ) AS complete_count
			FROM {$this->db_name}
			WHERE
				(
					( action = 'play' AND target_time = 0 ) OR
					action = 'complete'
				) AND
				created BETWEEN %s AND %s
			GROUP BY DAYOFYEAR( DATE_ADD( created, INTERVAL %d HOUR ) )
			ORDER BY created ASC",
			$this->offset,
			$this->start_date,
			$this->end_date,
			$this->offset
		);

		$results = $this->db->get_results( $sql );
		$results = array_combine( wp_list_pluck( $results, 'date' ), $results );

		$current_time = strtotime( $this->end_date );
		$data         = array();

		// Create an array with an entry for every date in the period, including
		// days when there weren't any listens.
		for ( $i = $this->interval - 1; $i >= 0; $i-- ) {
			$date = date( 'n/j', $current_time - $i * DAY_IN_SECONDS );

			$data[ $date ] = (object) array(
				'date'           => $date,
				'complete_count' => 0,
				'play_count'     => 0,
			);

			if ( isset( $results[ $date ] ) ) {
				$data[ $date ] = $results[ $date ];
			}
		}

		return $data;
	}

	/**
	 * Retrieve the plays per page during the specified interval.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_play_count_per_page() {
		$sql = $this->db->prepare(
			"SELECT page_title, page_url, COUNT(*) AS play_count
			FROM {$this->db_name}
			WHERE
				action = 'play' AND
				target_time = 0 AND
				created BETWEEN %s AND %s
			GROUP BY page_url
			ORDER BY play_count DESC
			LIMIT 10",
			$this->start_date,
			$this->end_date
		);

		$results = $this->db->get_results( $sql );

		foreach ( $results as $key => $result ) {
			if ( empty( $result->page_title ) ) {
				$results[ $key ]->page_title = $result->page_url;
			}
		}

		return $results;
	}

	/**
	 * Retrieve the plays per day for each track within the specified interval.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_play_count_per_track() {
		$sql = $this->db->prepare(
			"SELECT
				target_title,
				target_url,
				COUNT( IF( action = 'play', 1, NULL ) ) AS play_count,
				COUNT( IF( action = 'complete', 1, NULL ) ) AS complete_count,
				COUNT( IF( action = 'skip', 1, NULL ) ) AS skip_count
			FROM {$this->db_name}
			WHERE
				(
					( action = 'play' AND target_time = 0 ) OR
					action = 'complete' OR
					action = 'skip'
				) AND
				created BETWEEN %s AND %s
			GROUP BY target_url
			ORDER BY play_count DESC
			LIMIT 10",
			$this->start_date,
			$this->end_date
		);

		$results = $this->db->get_results( $sql );

		$data = array();
		foreach ( $results as $key => $result ) {
			$complete_rate = 0;
			if ( $result->complete_count ) {
				$complete_rate = $result->complete_count / $result->play_count;
			}

			$partial_rate = 0;
			if ( $result->play_count && ( $result->complete_count || $result->skip_count ) ) {
				$partial_rate = ( $result->play_count - $result->complete_count - $result->skip_count ) / $result->play_count;
			} elseif ( ! empty( $result->play_count ) ) {
				$partial_rate = 1;
			}

			$skip_rate = 0;
			if ( $result->play_count && $result->skip_count ) {
				$skip_rate = $result->skip_count / $result->play_count;
			}

			$data[] = (object) wp_parse_args( array(
				'complete_rate' => $complete_rate,
				'partial_rate'  => $partial_rate,
				'skip_rate'     => $skip_rate,
			), (array) $result );
		}

		return $data;
	}

	/**
	 * Retrieve data to display the change in plays compared to the previous interval.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_chart_data() {
		$sql = $this->db->prepare(
			"SELECT
				target_title,
				target_url,
				COUNT( IF( DATE_ADD( created, INTERVAL %d HOUR ) >= DATE_SUB( %s, INTERVAL %d DAY ), 1, NULL ) ) AS plays_this_period,
				COUNT( IF( DATE_ADD( created, INTERVAL %d HOUR ) < DATE_SUB( %s, INTERVAL %d DAY ), 1, NULL ) ) AS plays_last_period
			FROM {$this->db_name}
			WHERE
				action = 'play' AND
				target_time = 0 AND
				created BETWEEN %s AND %s
			GROUP BY target_url
			ORDER BY plays_this_period DESC
			LIMIT 10",
			$this->offset,
			$this->end_date,
			$this->interval,
			$this->offset,
			$this->end_date,
			$this->interval,
			date( self::MYSQL_DATETIME_FORMAT, strtotime( sprintf( '-%d days', $this->interval * 2 ) ) ),
			$this->end_date
		);

		$results = $this->db->get_results( $sql );

		return $results;
	}

	/**
	 * Retrieve the time zone offset.
	 *
	 * @since 1.0.0
	 *
	 * @return double
	 */
	protected function get_timezone_offset() {
		$offset = wp_timezone_override_offset();
		return $offset ? $offset : get_option( 'gmt_offset', 0 );
	}
}
