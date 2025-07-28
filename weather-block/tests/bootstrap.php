<?php
/**
 * PHPUnit bootstrap file
 */

// First, check if we're being loaded by an existing WordPress test suite.
if ( defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
	$_tests_dir = dirname( WP_TESTS_CONFIG_FILE_PATH );
} else {
	$_tests_dir = getenv( 'WP_TESTS_DIR' );
}

// If the WordPress test suite is not found, let's try to find it.
if ( ! $_tests_dir ) {
	$try_paths = array(
		// In a Composer project, it's in the vendor directory.
		__DIR__ . '/../vendor/wordpress/wordpress-develop/tests/phpunit',
		// The WP-CLI scaffold command puts it in a different location.
		'/tmp/wordpress-tests-lib',
	);

	foreach ( $try_paths as $try_path ) {
		if ( file_exists( $try_path . '/includes/functions.php' ) ) {
			$_tests_dir = $try_path;
			break;
		}
	}
}

// If we still can't find it, then we'll give up.
if ( ! $_tests_dir ) {
	die( "Could not find the WordPress test suite. Please set the WP_TESTS_DIR environment variable.\n" );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/weather-block.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
