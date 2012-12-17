<?php

/**
 * Description of BPMedia
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

class BPMedia {

	public $text_domain = 'bp-media';

	public function __construct() {
		global $bp_text_domain;
		$bp_text_domain = $this->text_domain;
	}

	public function constants() {

		if ( ! defined( 'BP_MEDIA_PATH' ) )
			define( 'BP_MEDIA_PATH', plugin_dir_path( __FILE__ ) );

		if ( ! defined( 'BP_MEDIA_URL' ) )
			define( 'BP_MEDIA_URL', plugin_dir_url( __FILE__ ) );

		/* A constant that can be checked to see if the BP Media is installed or not. */
		if ( ! defined( 'BP_MEDIA_IS_INSTALLED' ) )
			define( 'BP_MEDIA_IS_INSTALLED', 1 );

		/* Constant to store the current version of the BP Media Plugin. */
		if ( ! defined( 'BP_MEDIA_VERSION' ) )
			define( 'BP_MEDIA_VERSION', '2.3.2' );

		/* A constant to be used as base for other URLs throughout the plugin */
		if ( ! defined( 'BP_MEDIA_IS_INSTALLED' ) )
			define( 'BP_MEDIA_PLUGIN_DIR', dirname( __FILE__ ) );

		/* A constant to store the required  */
		if ( ! defined( 'BP_MEDIA_REQUIRED_BP' ) )
			define( 'BP_MEDIA_REQUIRED_BP', '1.6.2' );

		/* A constatnt to store database version */
		if ( ! defined( 'BP_MEDIA_DB_VERSION' ) )
			define( 'BP_MEDIA_DB_VERSION', '2.1' );

		/* A constant to Active Collab API Assignee ID */
		if ( ! defined( 'BP_MEDIA_AC_API_ASSIGNEE_ID' ) )
			define( 'BP_MEDIA_AC_API_ASSIGNEE_ID', '5' );

		/* A constant to Active Collab API Assignee ID */
		if ( ! defined( 'BP_MEDIA_AC_API_LABEL_ID' ) )
			define( 'BP_MEDIA_AC_API_LABEL_ID', '1' );

		/* A constant to Active Collab API priority */
		if ( ! defined( 'BP_MEDIA_AC_API_PRIORITY' ) )
			define( 'BP_MEDIA_AC_API_PRIORITY', '2' );

		/* A constant to Active Collab API priority */
		if ( ! defined( 'BP_MEDIA_AC_API_CATEGORY_ID' ) )
			define( 'BP_MEDIA_AC_API_CATEGORY_ID', '224' );
	}

	function __autoload( $class_name ) {
		$rtlibpath = array(
			BP_MEDIA_PATH . 'rtlib/' . $class_name . '.php',
			BP_MEDIA_PATH . 'admin/' . $class_name . '.php',
			BP_MEDIA_PATH . 'includes/' . $class_name . '.php',
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

	private function activate() {

	}

	private function deactivate() {

	}

}

global $bp_media;
$bp_media = new BPMedia();
?>
