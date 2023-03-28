<?php

class WP_CLI_SearchWP_Legacy_Data_Removal extends WP_CLI_SearchWP_Legacy_Data_Removal_Base {
	/**
	 * Cleanup database from legacy version SearchWP
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     wp searchwp remove-legacy-data
	 */
	public function __invoke( $args ) {
		if ( $this->has_legacy_version( '3' ) ) {
			$this->remove_3x();
			WP_CLI::success( 'SearchWP 3.x legacy data has been removed!' );
		}

		WP_CLI::warning( 'There is no legacy SearchWP data detected.' );
	}

	/**
	 * Checks for the submitted legacy version.
	 *
	 * @param mixed $version
	 * @return bool
	 */
	public function has_legacy_version( $version ) {
		$has_legacy = false;

		switch ( $version ) {
			case '3':
				$legacy_settings = get_option( 'searchwp_settings' );
				$has_legacy      = ! empty( $legacy_settings );
		}

		return $has_legacy;
	}

	/**
	 * Removal routine for SearchWP 3.x.
	 *
	 * @return void
	 */
	private function remove_3x() {
		global $wpdb;

		// Remove post meta.
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_searchwp_last_index' ) );
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_searchwp_attempts' ) );
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_searchwp_skip' ) );
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_searchwp_skip_doc_processing' ) );
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_searchwp_review' ) );

		$persisted_extra_metadata_keys = $wpdb->get_col( $wpdb->prepare(
			"SELECT meta_key FROM $wpdb->postmeta WHERE meta_key LIKE %s GROUP BY meta_key",
			'_searchwp_extra_metadata%'
		) );

		if ( ! empty( $persisted_extra_metadata_keys ) ) {
			foreach ( $persisted_extra_metadata_keys as $persisted_extra_metadata_key ) {
				$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => $persisted_extra_metadata_key ) );
			}
		}

		// Drop custom database tables.
		foreach ( array( 'cf', 'index', 'log', 'tax', 'terms' ) as $table ){
			$tableName = $wpdb->prefix . 'swp_' . $table;

			if ( $tableName == $wpdb->get_var( "SHOW TABLES LIKE '$tableName'" ) ) {
				$wpdb->query( "DROP TABLE $tableName" );
			}
		}

		delete_option( 'searchwp_settings' );
		delete_option( 'searchwp_settings_backup' );
		delete_option( 'searchwp_indexer' );
		delete_option( 'searchwp_purge_queue' );
		delete_option( 'searchwp_version' );
		delete_option( 'searchwp_progress' );
		delete_option( 'searchwp_license_key' );
		delete_option( 'searchwp_paused' );
		delete_option( 'searchwp_last_activity' );
		delete_option( 'searchwp_busy' );
		delete_option( 'searchwp_doing_delta' );
		delete_option( 'searchwp_utf8mb4' );
		delete_option( 'searchwp_advanced' );
		delete_option( 'searchwp_waiting' );
		delete_option( 'searchwp_delta_attempts' );
		delete_option( 'searchwp_processing_purge_queue' );
		delete_option( 'searchwp_transient' );
		delete_option( 'swppurge_transient' );
	}
}

WP_CLI::add_command( 'searchwp remove-legacy-data', 'WP_CLI_SearchWP_Legacy_Data_Removal' );
