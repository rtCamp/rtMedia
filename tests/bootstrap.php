<?php
/**
 * Bootstrap the plugin unit testing environment.
 *
 * Edit 'active_plugins' setting below to point to your main plugin file.
 *
 * @package wordpress-plugin-tests
 */

// Activates this plugin in WordPress so it can be tested.
$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array( 'rtMedia/index.php' ),
);

require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';

function _manually_load_plugin() {
        require dirname( __FILE__ ) . '/../index.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';
class RTMEDIA_TestCase extends WP_UnitTestCase {
        // Put convenience methods here
        // Here are two I use for faking things for save_post hooks, et al
        function set_post( $key, $value ) {
                $_POST[$key] = $_REQUEST[$key] = addslashes( $value );
        }

        function unset_post( $key ) {
                unset( $_POST[$key], $_REQUEST[$key] );
        }
}