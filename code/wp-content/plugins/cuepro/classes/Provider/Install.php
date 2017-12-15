<?php
/**
 * Installation routines.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.0.0
 */

/**
 * Installation provider class.
 *
 * @package CuePro
 * @since   1.0.0
 */
class CuePro_Provider_Install extends CuePro_AbstractProvider {
	/**
	 * Database schema version.
	 *
	 * @since 1.0.0
	 * @var integer
	 */
	const DB_VERSION = 6;

	/**
	 * Database version option name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const DB_VERSION_OPTION_NAME = 'cuepro_db_version';

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_init',                                array( $this, 'maybe_upgrade' ) );
		add_action( 'activate_' . $this->plugin->get_basename(), array( $this, 'create_tables' ) );
	}

	/**
	 * Upgrade when the database version is outdated.
	 *
	 * @since 1.0.0
	 */
	public function maybe_upgrade() {
		global $wpdb;

		$saved_version = get_option( self::DB_VERSION_OPTION_NAME, 0 );
		$table_name    = $wpdb->prefix . 'cue_events';

		if ( version_compare( $saved_version, self::DB_VERSION, '<' ) ) {
			$this->create_tables();
		}

		if ( $saved_version && version_compare( $saved_version, 3, '<' ) ) {
			$wpdb->query( "UPDATE {$table_name} SET action = 'complete' WHERE action = 'end'" );
			$wpdb->query( "DELETE FROM {$table_name} WHERE action = 'play' AND target_time != 0" );
			$wpdb->query( "DELETE FROM {$table_name} WHERE action NOT IN ( 'play', 'skip', 'listen', 'complete' )" );
		}

		if ( $saved_version && version_compare( $saved_version, 5, '<' ) ) {
			if ( 'utf8mb4' === $wpdb->charset ) {
				$this->convert_table_to_utf8mb4( $table_name );
			}
		}

		if ( $saved_version && version_compare( $saved_version, 6, '<' ) ) {
			add_option( 'cuepro_force_downloads', '1' );
		}

		if ( version_compare( $saved_version, self::DB_VERSION, '<' ) ) {
			update_option( self::DB_VERSION_OPTION_NAME, self::DB_VERSION );
		}
	}

	/**
	 * Create database tables.
	 *
	 * @since 1.0.0
	 */
	public function create_tables() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $this->get_schema() );
	}

	/**
	 * Retrieve the database schema.
	 *
	 * @since 1.0.0
	 *
	 * @todo Add some indexes.
	 * @todo Consider blog_id/site_id for multisite?
	 * @todo Prevent notices when updating tables due to duplicate indexes.
	 *
	 * @return string SQL to create the database table(s).
	 */
	protected function get_schema() {
		global $wpdb;

		$charset_collate = $this->get_charset_collate();

		/*
		 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
		 * As of WordPress 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
		 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
		 *
		 * This may cause duplicate index notices in logs due to https://core.trac.wordpress.org/ticket/34870 but dropping
		 * indexes first causes too much load on some servers/larger DB.
		 */
		$max_index_length = 191;

		$tables = "
CREATE TABLE {$wpdb->prefix}cue_events (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  action varchar(32) NOT NULL,
  target_url varchar(255) NOT NULL,
  target_title text NOT NULL,
  target_time int(6) NOT NULL DEFAULT 0,
  page_title text NOT NULL,
  page_url varchar(255) NOT NULL,
  client_uid varchar(22) NOT NULL,
  client_ip varchar(39) NOT NULL,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY target_action (action,target_time,created,target_url($max_index_length))
) $charset_collate;";

		return $tables;
	}

	/**
	 * Retrieve the character set and collation.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_charset_collate() {
		global $wpdb;

		if ( $wpdb->has_cap( 'collation' ) ) {
			return $wpdb->get_charset_collate();
		}

		return '';
	}

	/**
	 * Convert a table to utf8mb4.
	 *
	 * @since 1.2.0
	 *
	 * @see maybe_convert_table_to_utf8mb4()
	 *
	 * @param  string $table The table to convert.
	 * @return bool true if the table was converted, false if it wasn't.
	 */
	protected function convert_table_to_utf8mb4( $table ) {
		global $wpdb;

		$results = $wpdb->get_results( "SHOW FULL COLUMNS FROM `$table`" );
		if ( ! $results ) {
			return false;
		}

		$table_details = $wpdb->get_row( "SHOW TABLE STATUS LIKE '$table'" );
		if ( ! $table_details ) {
			return false;
		}

		list( $table_charset ) = explode( '_', $table_details->Collation );
		$table_charset = strtolower( $table_charset );
		if ( 'utf8mb4' === $table_charset ) {
			return true;
		}

		return $wpdb->query( "ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" );
	}
}
