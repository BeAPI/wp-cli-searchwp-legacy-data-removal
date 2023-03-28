<?php
if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

define( 'WP_CLI_SWP_LDR', dirname( __FILE__ ) );

require_once WP_CLI_SWP_LDR . '/src/wp-cli-searchwp-legacy-data-removal-base.php';
require_once WP_CLI_SWP_LDR . '/src/wp-cli-searchwp-legacy-data-removal.php';