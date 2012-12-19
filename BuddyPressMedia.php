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

class BuddyPressMedia {

	public $text_domain = 'bp-media';

	public function __construct() {
		global $bpm_text_domain;
		$bpm_text_domain = $this->text_domain;
		$this->constants();
		add_action( 'bp_include', array( $this, 'init' ) );
	}

	public function constants() {

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

	function init() {
		if ( defined( 'BP_VERSION' ) && version_compare( BP_VERSION, BP_MEDIA_REQUIRED_BP, '>' ) ) {
			add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );
			require( BP_MEDIA_PATH . 'includes/bp-media-loader.php' );
			//require( BP_MEDIA_PLUGIN_DIR . '/includes/bp-media-groups-loader.php');
		}
	}

	function settings_link( $links, $file ) {
		/* create link */
		$plugin_name = plugin_basename( __FILE__ );
		$admin_link = bp_media_get_admin_url( add_query_arg( array( 'page' => 'bp-media-settings' ), 'admin.php' ) );
		if ( $file == $plugin_name ) {
			array_unshift(
					$links, sprintf( '<a href="%s">%s</a>', $admin_link, __( 'Settings' ) )
			);
		}
		return $links;
	}

	private function activate() {

	}

	private function deactivate() {

	}

	public function autoload_js_css() {

	}

}

?>
