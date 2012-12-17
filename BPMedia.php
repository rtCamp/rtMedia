<?php

/**
 * Description of BPMedia
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
define( 'BPM_PATH', plugin_dir_path( __FILE__ ) );
define( 'BPM_URL', plugin_dir_url( __FILE__ ) );

class BPMedia {

	public function __construct() {

	}

	function __autoload( $class_name ) {
		$rtlibpath = array(
			BPM_PATH . 'rtlib/' . $class_name . '.php',
			BPM_PATH . 'admin/' . $class_name . '.php',
			BPM_PATH . 'includes/' . $class_name . '.php',
		);
		foreach ( $rtlibpath as $i => $path ) {
			if ( file_exists( $path ) ) {
				include $path;
				break;
			}
		}
	}

	private function init() {

	}

}

global $bp_media;
$bp_media = new BPMedia();
?>
