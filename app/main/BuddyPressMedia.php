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
	public $options;
	public $support_email = 'support@rtcamp.com';
	public $query;
	public $albums_query;
	public $counter = 0;
	public $count = null;
	public $posts_per_page = 10;
	public $activity_types = array(
		'media_upload',
		'album_updated',
		'album_created'
	);
	public $hidden_activity_cache = array( );
	public $loader;
        public $group_loader;
        
	public function __construct() {

		$this->constants();
	}

	public function get_option() {
		$this->options = bp_get_option( 'bp_media_options' );
	}

	public function constants() {

		/* If the plugin is installed. */
		if ( ! defined( 'BP_MEDIA_IS_INSTALLED' ) )
			define( 'BP_MEDIA_IS_INSTALLED', 1 );

		/* Current Version. */
		if ( ! defined( 'BP_MEDIA_VERSION' ) )
			define( 'BP_MEDIA_VERSION', '2.4' );

		/* Required Version  */
		if ( ! defined( 'BP_MEDIA_REQUIRED_BP' ) )
			define( 'BP_MEDIA_REQUIRED_BP', '1.6.2' );

		/* Database Version */
		if ( ! defined( 'BP_MEDIA_DB_VERSION' ) )
			define( 'BP_MEDIA_DB_VERSION', '2.1' );

		/**
		  /* A constant to Active Collab API Assignee ID
		  if ( ! defined( 'BP_MEDIA_AC_API_ASSIGNEE_ID' ) )
		  define( 'BP_MEDIA_AC_API_ASSIGNEE_ID', '5' );

		  /* A constant to Active Collab API Assignee ID
		  if ( ! defined( 'BP_MEDIA_AC_API_LABEL_ID' ) )
		  define( 'BP_MEDIA_AC_API_LABEL_ID', '1' );

		  /* A constant to Active Collab API priority
		  if ( ! defined( 'BP_MEDIA_AC_API_PRIORITY' ) )
		  define( 'BP_MEDIA_AC_API_PRIORITY', '2' );

		  /* A constant to Active Collab API priority
		  if ( ! defined( 'BP_MEDIA_AC_API_CATEGORY_ID' ) )
		  define( 'BP_MEDIA_AC_API_CATEGORY_ID', '224' );
		 */
		/* Slug Constants */
		if ( ! defined( 'BP_MEDIA_SLUG' ) )
			define( 'BP_MEDIA_SLUG', 'media' );

		if ( ! defined( 'BP_MEDIA_UPLOAD_SLUG' ) )
			define( 'BP_MEDIA_UPLOAD_SLUG', 'upload' );

		if ( ! defined( 'BP_MEDIA_DELETE_SLUG' ) )
			define( 'BP_MEDIA_DELETE_SLUG', 'delete' );

		if ( ! defined( 'BP_MEDIA_IMAGES_SLUG' ) )
			define( 'BP_MEDIA_IMAGES_SLUG', 'photos' );

		if ( ! defined( 'BP_MEDIA_IMAGES_ENTRY_SLUG' ) )
			define( 'BP_MEDIA_IMAGES_ENTRY_SLUG', 'view' );

		if ( ! defined( 'BP_MEDIA_IMAGES_EDIT_SLUG' ) )
			define( 'BP_MEDIA_IMAGES_EDIT_SLUG', 'edit' );

		if ( ! defined( 'BP_MEDIA_VIDEOS_SLUG' ) )
			define( 'BP_MEDIA_VIDEOS_SLUG', 'videos' );

		if ( ! defined( 'BP_MEDIA_VIDEOS_ENTRY_SLUG' ) )
			define( 'BP_MEDIA_VIDEOS_ENTRY_SLUG', 'watch' );

		if ( ! defined( 'BP_MEDIA_VIDEOS_EDIT_SLUG' ) )
			define( 'BP_MEDIA_VIDEOS_EDIT_SLUG', 'edit' );

		if ( ! defined( 'BP_MEDIA_AUDIO_SLUG' ) )
			define( 'BP_MEDIA_AUDIO_SLUG', 'music' );

		if ( ! defined( 'BP_MEDIA_AUDIO_ENTRY_SLUG' ) )
			define( 'BP_MEDIA_AUDIO_ENTRY_SLUG', 'listen' );

		if ( ! defined( 'BP_MEDIA_AUDIO_EDIT_SLUG' ) )
			define( 'BP_MEDIA_AUDIO_EDIT_SLUG', 'edit' );

		if ( ! defined( 'BP_MEDIA_ALBUMS_SLUG' ) )
			define( 'BP_MEDIA_ALBUMS_SLUG', 'albums' );

		if ( ! defined( 'BP_MEDIA_ALBUMS_ENTRY_SLUG' ) )
			define( 'BP_MEDIA_ALBUMS_ENTRY_SLUG', 'list' );

		if ( ! defined( 'BP_MEDIA_ALBUMS_EDIT_SLUG' ) )
			define( 'BP_MEDIA_ALBUMS_EDIT_SLUG', 'edit' );

		/* Labels loaded via text domain, can be translated */
		if ( ! defined( 'BP_MEDIA_LABEL' ) )
			define( 'BP_MEDIA_LABEL', __( 'Media', $this->text_domain ) );

		if ( ! defined( 'BP_MEDIA_LABEL_SINGULAR' ) )
			define( 'BP_MEDIA_LABEL_SINGULAR', __( 'Media', $this->text_domain ) );

		if ( ! defined( 'BP_MEDIA_IMAGES_LABEL' ) )
			define( 'BP_MEDIA_IMAGES_LABEL', __( 'Photos', $this->text_domain ) );

		if ( ! defined( 'BP_MEDIA_IMAGES_LABEL_SINGULAR' ) )
			define( 'BP_MEDIA_IMAGES_LABEL_SINGULAR', __( 'Photo', $this->text_domain ) );

		if ( ! defined( 'BP_MEDIA_VIDEOS_LABEL' ) )
			define( 'BP_MEDIA_VIDEOS_LABEL', __( 'Videos', $this->text_domain ) );

		if ( ! defined( 'BP_MEDIA_VIDEOS_LABEL_SINGULAR' ) )
			define( 'BP_MEDIA_VIDEOS_LABEL_SINGULAR', __( 'Video', $this->text_domain ) );

		if ( ! defined( 'BP_MEDIA_AUDIO_LABEL' ) )
			define( 'BP_MEDIA_AUDIO_LABEL', __( 'Music', $this->text_domain ) );

		if ( ! defined( 'BP_MEDIA_AUDIO_LABEL_SINGULAR' ) )
			define( 'BP_MEDIA_AUDIO_LABEL_SINGULAR', __( 'Music', $this->text_domain ) );

		if ( ! defined( 'BP_MEDIA_ALBUMS_LABEL' ) )
			define( 'BP_MEDIA_ALBUMS_LABEL', __( 'Albums', $this->text_domain ) );

		if ( ! defined( 'BP_MEDIA_ALBUMS_LABEL_SINGULAR' ) )
			define( 'BP_MEDIA_ALBUMS_LABEL_SINGULAR', __( 'Album', $this->text_domain ) );

		if ( ! defined( 'BP_MEDIAUPLOAD_LABEL' ) )
			define( 'BP_MEDIA_UPLOAD_LABEL', __( 'Upload', $this->text_domain ) );

		if ( ! defined( 'BP_MEDIA_TMP_DIR' ) )
			define( 'BP_MEDIA_TMP_DIR', WP_CONTENT_DIR . '/bp-media-temp' );

		if ( ! defined( 'BP_MEDIA_SUPPORT_EMAIL' ) )
			define( 'BP_MEDIA_SUPPORT_EMAIL', $this->support_email );
	}

	function init() {
		$this->get_option();

		if ( defined( 'BP_VERSION' ) && version_compare( BP_VERSION, BP_MEDIA_REQUIRED_BP, '>=' ) ) {
			add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );
			$this->loader = new BPMediaLoader();
//require( BP_MEDIA_PATH . '/includes/bp-media-groups-loader.php');
                       $this->group_loader= new BPMediaGroup();
                        
                        
		}

		if ( file_exists( BP_MEDIA_PATH . '/languages/' . get_locale() . '.mo' ) )
			load_textdomain( 'bp-media', BP_MEDIA_PATH . '/languages/' . get_locale() . '.mo' );

		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		global $bp_admin;
		$bp_admin = new BPMediaAdmin();
	}

	function settings_link( $links, $file ) {
		/* create link */
		$plugin_name = plugin_basename( __FILE__ );
		$admin_link = $this->get_admin_url( add_query_arg( array( 'page' => 'bp-media-settings' ), 'admin.php' ) );
		if ( $file == $plugin_name ) {
			array_unshift(
					$links, sprintf( '<a href="%s">%s</a>', $admin_link, __( 'Settings', $this->text_domain ) )
			);
		}
		return $links;
	}

	function media_sizes() {
		$def_sizes = array(
			'activity_image' => array(
				'width' => 320,
				'height' => 240
			),
			'activity_video' => array(
				'width' => 320,
				'height' => 240
			),
			'activity_audio' => array(
				'width' => 320,
			),
			'single_image' => array(
				'width' => 800,
				'height' => 0
			),
			'single_video' => array(
				'width' => 640,
				'height' => 480
			),
			'single_audio' => array(
				'width' => 640,
			),
		);

		return apply_filters( 'bpm_media_sizes', $def_sizes );
	}

	function excerpt_lengths() {
		$def_excerpt = array(
			'single_entry_title' => 100,
			'single_entry_description' => 500,
			'activity_entry_title' => 50,
			'activity_entry_description' => 500
		);

		return apply_filters( 'bpm_excerpt_lengths', $def_excerpt );
	}

	public function admin_notice() {
		global $current_user;
		$user_id = $current_user->ID;
		if ( isset( $_GET[ 'bp_media_nag_ignore' ] ) && '0' == $_GET[ 'bp_media_nag_ignore' ] ) {
			add_user_meta( $user_id, 'bp_media_ignore_notice', 'true', true );
		}
		/* Check that the user hasn't already clicked to ignore the message */
		if ( ! get_user_meta( $user_id, 'bp_media_ignore_notice' ) ) {
			if ( defined( 'BP_VERSION' ) ) {
				if ( version_compare( BP_VERSION, BP_MEDIA_REQUIRED_BP, '<' ) ) {
					echo '<div class="error"><p>';
					printf( __( 'The BuddyPress version installed is an older version and is not supported, please update BuddyPress to use BuddyPress Media Plugin.<a class="alignright" href="%1$s">X</a>', $this->text_domain ), '?bp_media_nag_ignore=0' );
					echo "</p></div>";
				}
			} else {
				echo '<div class="error"><p>';
				printf( __( 'You have not installed BuddyPress. Please install latest version of BuddyPress to use BuddyPress Media plugin.<a class="alignright" href="%1$s">X</a>', $this->text_domain ), '?bp_media_nag_ignore=0' );
				echo "</p></div>";
			}
		}
	}

	public function activate() {
		$bpmquery = new WP_Query( array( 'post_type' => 'bp_media', 'posts_per_page' => 1 ) );
		if ( $bpmquery->found_posts > 0 ) {
			update_site_option( 'bp_media_db_version', '1.0' );
		} else {
			switch ( get_site_option( 'bp_media_db_version', false, false ) ) {
				case '2.0':
					break;
				default:
					update_site_option( 'bp_media_db_version', BP_MEDIA_DB_VERSION );
			}
		}
	}

	function get_admin_url( $path = '', $scheme = 'admin' ) {

		// Links belong in network admin
		if ( is_multisite() )
			$url = network_admin_url( $path, $scheme );

		// Links belong in site admin
		else
			$url = admin_url( $path, $scheme );

		return $url;
	}

	private function deactivate() {

	}

	public function autoload_js_css() {

	}

}

?>
