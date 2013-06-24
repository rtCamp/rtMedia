<?php

/**
 * Don't load this file directly!
 */
if (!defined('ABSPATH'))
	exit;

/**
 * BuddyPress Media
 *
 * The main BuddyPress Media Class. This is where everything starts.
 *
 * @package BuddyPressMedia
 * @subpackage Main
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class RTMedia {

	/**
	 *
	 * @var array RTMedia settings
	 */

	public $default_thumbnail;
	public $allowed_types;
	public $privacy;
	public $default_sizes;

	/**
	 *
	 * @var string Email address the admin support form should send to
	 */
	public $support_email = 'support@rtcamp.com';

	/**
	 *
	 * @var string Support forum url
	 */
	public $support_url = 'http://rtcamp.com/support/forum/buddypress-media/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media';

	/**
	 *
	 * @var object/array The query that fetches media (photos, video and audio)
	 */
	public $query;

	/**
	 *
	 * @var object/array The query that fetches albums
	 */
	public $albums_query;

	/**
	 *
	 * @var int Count
	 */
	public $count = null;

	/**
	 *
	 * @var int Number of media items to show in one view.
	 */
	public $posts_per_page = 10;

	/**
	 *
	 * @var array The types of activity BuddyPress Media creates
	 */
	public $activity_types = array(
		'media_upload',
		'album_updated',
		'album_created'
	);

	/**
	 *
	 * @var array A cache for activities that are hidden by BuddyPress Media
	 */
	public $hidden_activity_cache = array();

	/**
	 *
	 * @var type
	 */
	public $loader;

	public $options;

	/**
	 *
	 * @var type
	 */
	public $group_loader;

	/**
	 * Constructs the class
	 * Defines constants and excerpt lengths, initiates admin notices,
	 * loads and initiates the plugin, loads translations.
	 * Initialises media counter
	 *
	 * @global int $bp_media_counter Media counter
	 */
	public function __construct() {

		// Rewrite API flush before activating and after deactivating the plugin
		register_activation_hook(__FILE__, array($this, 'flush_rewrite'));
		register_deactivation_hook(__FILE__, array($this, 'flush_rewrite'));

		$this->default_thumbnail = apply_filters('rtmedia_default_thumbnail',RT_MEDIA_URL. 'assets/thumb_default.png');
		// Define allowed types
		$this->set_allowed_types();

		$this->set_allowed_types(); // Define allowed types

		$this->constants(); // Define constants

		// check for global album --- after wordpress is fully loaded
		add_action('init', array($this, 'check_global_album'));

		// Hook it to WordPress
		add_action('plugins_loaded', array($this, 'init'));

		// Load translations
		add_action('plugins_loaded', array($this, 'load_translation'));

		//Admin Panel
		add_action('init', array($this, 'admin_init'));

		$this->set_default_sizes(); // set default sizes

		$this->set_privacy(); // set privacy

		//  Enqueue Plugin Scripts and Styles
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'), 11);

		//AJAX Call for PL Upload
		//add_action('wp_ajax_rt_file_upload', array('RTMediaUploadHelper', 'file_upload'));
	}

	/**
	 *  Default allowed media types array
	 */
	function set_allowed_types(){
		$allowed_types = array(
			array(
					'name'	=> 'music',
					'plural' => 'music',
					'label' => __('Music','rt-media'),
					'plural_label' => __('Music','rt-media'),
					'extn' => array('mp3'),
					'thumbnail' => RT_MEDIA_URL.'/assets/img/audio_thumb.png'
				),
			array(
					'name'	=> 'video',
					'plural' => 'videos',
					'label' => __('Video','rt-media'),
					'plural_label' => __('Videos','rt-media'),
					'extn' => array('mp4'),
					'thumbnail' => RT_MEDIA_URL.'/assets/img/video_thumb.png'
				),
			array(
					'name'	=> 'photo',
					'plural' => 'photos',
					'label' => __('Photo','rt-media'),
					'plural_label' => __('Photos','rt-media'),
					'extn' => array('jpeg', 'png'),
					'thumbnail' => RT_MEDIA_URL.'/assets/img/image_thumb.png'
				)
		);

		// filter for hooking additional media types
		$allowed_types = apply_filters('rt_media_allowed_types', $allowed_types);

		// sanitize all the types
		$allowed_types = $this->sanitize_allowed_types($allowed_types);

		// set the allowed types property
		$this->allowed_types = $allowed_types;

	}

	/**
	 *
	 * @param array $allowed_types allowed media types after hooking custom types
	 * @return array $allowed_types sanitized media types
	 */
	function sanitize_allowed_types($allowed_types){
		if(!is_array($allowed_types)&& count($allowed_types)<1) return;
		foreach($allowed_types as $key=>&$type){
			if(!isset($type['name']) ||
					empty($type['name']) ||
					!isset($type['extn']) ||
					empty($type['extn'])){
				unset($allowed_types[$key]);
				continue;
			}
			if(!isset($type['thumbnail']) || empty($type['thumbnail'])){
				$type['thumbnail']= $this->default_thumbnail;
			}
		}
		return $allowed_types;
	}

	function set_default_sizes(){
		$this->default_sizes = array(
			'image' => array(
				'title' => __("Image","rt-media"),
				'thumbnail' => array(
					'title' => __("Thumbnail","rt-media"),
					'dimensions' => array('width' => 150, 'height' => 150, 'crop' => 1)
				),
				'medium' => array(
					'title' => __("Medium","rt-media"),
					'dimensions' => array('width' => 320, 'height' => 240, 'crop' => 1)
				),
				'large' => array(
					'title' => __("Large","rt-media"),
					'dimensions' => array('width' => 800, 'height' => 0, 'crop' => 1)
				)
			),
			'video' => array(
				'title' => __("Video","rt-media"),
				'activity_player' => array(
					'title' => __("Activity Player","rt-media"),
					'dimensions' => array('width' => 320, 'height' => 240)
				),
				'single_player' => array(
					'title' => __("Single Player","rt-media"),
					'dimensions' => array('width' => 640, 'height' => 480)
				)
			),
			'audio' => array(
				'title' => __("Audio","rt-media"),
				'activity_player' => array(
					'title' => __("Activity Player","rt-media"),
					'dimensions' => array('width' => 320)
				),
				'single_player' => array(
					'title' => __("Single Player","rt-media"),
					'dimensions' => array('width' => 640)
				)
			),
			'featured' => array(
				'title' => __("Featured Media","rt-media"),
				'default' => array(
					'title' => __("Default","rt-media"),
					'dimensions' => array('width' => 100, 'height' => 100, 'crop' => 1)
				)
			)
		);

		$this->default_sizes = apply_filters('rt_media_allowed_sizes', $this->allowed_sizes);

	}

	function set_privacy(){
		$this->privacy = array(
			'enable' => array(
				'title' => __("Enable Privacy","rt-media"),
				'callback' => array("RTMediaFormHandler", "checkbox"),
				'args' => array(
					'id' => 'rt-media-privacy-enable',
					'key' => 'rt-media-privacy][enable',
					'value' => 0
				)
			),
			'default' => array(
				'title' => __("Default Privacy","rt-media"),
				'callback' => array("RTMediaFormHandler","radio"),
				'args' => array(
					'key' => 'rt-media-privacy][default',
					'radios' => array(
						60 => __('<strong>Private</strong> - Visible only to the user', 'rt-media'),
						40 => __('<strong>Friends</strong> - Visible to user\'s friends', 'rt-media'),
						20 => __('<strong>Users</strong> - Visible to registered users', 'rt-media'),
						0 => __('<strong>Public</strong> - Visible to the world', 'rt-media')
					),
					'default' => 0
				),
			),
			'user_override' => array(
				'title' => __("User Override","rt-media"),
				'callback' => array("RTMediaFormHandler", "checkbox"),
				'args' => array(
					'key' => 'rt-media-privacy][user-override',
					'value' => 0
				)
			)
		);
		$this->privacy = apply_filters('rt_media_privacy_levels', $this->privacy);

		$this->privacy = array(
			'enable' => array(
				'title' => __("Enable Privacy","rt-media"),
				'callback' => array("RTMediaFormHandler", "checkbox"),
				'args' => array(
					'id' => 'rt-media-privacy-enable',
					'key' => 'rt-media-privacy][enable',
					'value' => 0
				)
			),
			'default' => array(
				'title' => __("Default Privacy","rt-media"),
				'callback' => array("RTMediaFormHandler","radio"),
				'args' => array(
					'key' => 'rt-media-privacy][default',
					'radios' => array(
						60 => __('<strong>Private</strong> - Visible only to the user', 'rt-media'),
						40 => __('<strong>Friends</strong> - Visible to user\'s friends', 'rt-media'),
						20 => __('<strong>Users</strong> - Visible to registered users', 'rt-media'),
						0 => __('<strong>Public</strong> - Visible to the world', 'rt-media')
					),
					'default' => 0
				),
			),
			'user_override' => array(
				'title' => __("User Override","rt-media"),
				'callback' => array("RTMediaFormHandler", "checkbox"),
				'args' => array(
					'key' => 'rt-media-privacy][user-override',
					'value' => 0
				)
			)
		);
		$this->privacy = apply_filters('rt_media_privacy_levels', $this->privacy);

		if (function_exists("bp_is_active") && !bp_is_active('friends')) {
			unset($this->privacy['levels'][40]);
		}

		/**
		 *  Enqueue Plugin Scripts and Styles
		 */
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'), 11);
                
                /* Includes db specific wrapper functions required to render the template */
                include(RT_MEDIA_PATH . 'app/main/controllers/template/rt-template-functions.php');
                
		/**
		 * AJAX Call for PL Upload
		 */
		//add_action('wp_ajax_rt_file_upload', array('RTMediaUploadHelper', 'file_upload'));
	}

	function dummy_function() {
		return;
	}

	function admin_init() {
		global $rt_media_admin;
		$rt_media_admin = new RTMediaAdmin();
	}

	function custom_media_nav_tab() {

		bp_core_new_nav_item( array(
			'name' => __( 'Media', 'rt-media' ),
			'slug' => 'media',
			'screen_function' => array($this,'dummy_function')
		) );

		if(bp_is_group()) {
			global $bp;
			$bp->bp_options_nav[bp_get_current_group_slug()]['media'] = array(
				'name' => 'Media',
				'link' => ( (is_multisite()) ? get_site_url(get_current_blog_id()) : get_site_url() ) . '/groups/' . bp_get_current_group_slug().'/media',
				'slug' => 'media',
				'user_has_access' => true,
				'css_id' => 'rt-media-media-nav',
				'position' => 99
			);
		}
	}

	public function init_site_options() {

		/**
		 * General Settings
		 */
		rt_media_get_site_option('rt-media-albums-enabled', 1);
		rt_media_get_site_option('rt-media-comments-enabled', 1);
		rt_media_get_site_option('rt-media-download-button', 1);
		rt_media_get_site_option('rt-media-enable-lightbox', 1);
		rt_media_get_site_option('rt-media-per-page-media', 10);
		rt_media_get_site_option('rt-media-media-end-point_enable', true);
		rt_media_get_site_option('rt-media-show-admin-menu', 1);

		/**
		 * Types Settings
		 */
		$allowed_types = $this->allowed_types;
		foreach($allowed_types as &$type){
			$type['enabled']= 1;
			$type['featured']= 0;
		}
		rt_media_get_site_option('rt-media-allowed-types', $allowed_types);

		/**
		 * Sizes Settings
		 */
		rt_media_get_site_option('rt-media-allowed-sizes', $this->allowed_sizes);

		/**
		 * Privacy
		 */
		rt_media_get_site_option('rt-media-privacy', $this->privacy);

		/**
		 * BuddyPress Settings
		 */
		if (function_exists("bp_is_active")) {
			rt_media_get_site_option('rt-media-enable-on-activity', bp_is_active('activity'));
			rt_media_get_site_option('rt-media-enable-on-profile', bp_is_active('profile'));
			rt_media_get_site_option('rt-media-enable-on-group', bp_is_active('groups'));
		} else {
			rt_media_get_site_option('rt-media-enable-on-activity', 0);
			rt_media_get_site_option('rt-media-enable-on-profile', 0);
			rt_media_get_site_option('rt-media-enable-on-group', 0);
		}

		$options = array(
			/* General */
			'rt-media-general' => array(
				'rt-media-albums-enabled' => array(
					'title' => __('Albums','rt-media'),
					'callback' => array('RTMediaFormHandler', 'checkbox'),
					'args' => array(
						'key' => 'rt-media-general][rt-media-albums-enabled]',
						'value' => rt_media_get_site_option('rt-media-albums-enabled'),
						'desc' => __('Enable Albums in rtMedia','rt-media')
					)
				),
				'rt-media-comments-enabled' => array(
					'title' => __('Comments','rt-media'),
					'callback' => array('RTMediaFormHandler', 'checkbox'),
					'args' => array(
						'key' => 'rt-media-general][rt-media-comments-enabled]',
						'value' => rt_media_get_site_option('rt-media-comments-enabled'),
						'desc' => __('Enable Comments in rtMedia','rt-media')
					)
				),
				'rt-media-download-button' => array(
					'title' => __('Download Button','rt-media'),
					'callback' => array('RTMediaFormHandler', 'checkbox'),
					'args' => array(
						'key' => 'rt-media-general][rt-media-download-button]',
						'value' => rt_media_get_site_option('rt-media-download-button'),
						'desc' => __('Display download button under media','rt-media')
					)
				),
				'rt-media-enable-lightbox' => array(
					'title' => __('Lightbox','rt-media'),
					'callback' => array('RTMediaFormHandler', 'checkbox'),
					'args' => array(
						'key' => 'rt-media-general][rt-media-enable-lightbox]',
						'value' => rt_media_get_site_option('rt-media-enable-lightbox'),
						'desc' => __('Enable Lighbox on Media','rt-media')
					)
				),
				'rt-media-per-page-media' => array(
					'title' => __('Number of Media Per Page','rt-media'),
					'callback' => array('RTMediaFormHandler', 'number'),
					'args' => array(
						'key' => 'rt-media-general][rt-media-per-page-media]',
						'value' => rt_media_get_site_option('rt-media-per-page-media'),
						'desc' => ''
					)
				),
//				'rt-media-media-end-point-enabled' => array(
//					'title' => __('Enable Media End Point for users','rt-media'),
//					'callback' => array('RTMediaFormHandler', 'checkbox'),
//					'args' => array(
//						'key' => 'rt-media-general][rt-media-media-end-point-enabled]',
//						'value' => rt_media_get_site_option('rt-media-media-end-pont-enabled'),
//						'desc' => __('Users can access their media on media end point','rt-media')
//					)
//				),
				'rt-media-show-admin-menu' => array(
					'title' => __('Admin Bar Menu','rt-media'),
					'callback' => array('RTMediaFormHandler', 'checkbox'),
					'args' => array(
						'key' => 'rt-media-general][rt-media-show-admin-menu]',
						'value' => rt_media_get_site_option('rt-media-show-admin-menu'),
						'desc' => __('Enable menu in WordPress admin bar','rt-media')
					)
				)
			),

			/* Types */
			'rt-media-allowed-types' => rt_media_get_site_option('rt-media-allowed-types'),

			/* Sizes */
			'rt-media-allowed-sizes' => rt_media_get_site_option('rt-media-allowed-sizes'),

			/* Privacy */
			'rt-media-privacy' => rt_media_get_site_option('rt-media-privacy'),

			/* BuddyPress */
			'rt-media-buddypress' => array(
				'rt-media-enable-on-profile' => array(
					'title' => __('Profile Media','rt-media'),
					'callback' => array('RTMediaFormHandler', 'checkbox'),
					'args' => array(
						'key' => 'rt-media-buddypress][rt-media-enable-on-profile]',
						'value' => rt_media_get_site_option('rt-media-enable-on-profile'),
						'desc' => __('Enable Media on BuddyPress Profile','rt-media')
					)
				),
				'rt-media-enable-on-groups' => array(
					'title' => __('Group Media','rt-media'),
					'callback' => array('RTMediaFormHandler', 'checkbox'),
					'args' => array(
						'key' => 'rt-media-buddypress][rt-media-enable-on-groups]',
						'value' => rt_media_get_site_option('rt-media-enable-on-groups'),
						'desc' => __('Enable Media on BuddyPress Groups','rt-media')
					)
				),
				'rt-media-enable-on-activity' => array(
					'title' => __('Activity Media','rt-media'),
					'callback' => array('RTMediaFormHandler', 'checkbox'),
					'args' => array(
						'key' => 'rt-media-buddypress][rt-media-enable-on-activity]',
						'value' => rt_media_get_site_option('rt-media-enable-on-activity'),
						'desc' => __('Enable Media on BuddyPress Activities','rt-media')
					)
				)
			)
		);

		$this->options = $options;
	}

	/**
	 * get Site Settings
	 */
	public function get_option($key) {

		$option = rt_media_get_site_option($key);
		return $option;
	}

	/**
	 * Defines all the constants if undefined. Can be overridden by
	 * defining them elsewhere, say wp-config.php
	 */
	public function constants() {

		/* If the plugin is installed. */
		if (!defined('BP_MEDIA_IS_INSTALLED'))
			define('BP_MEDIA_IS_INSTALLED', 1);

		/* Current Version. */
		if (!defined('RT_MEDIA_VERSION'))
			define('RT_MEDIA_VERSION', '3.0 Beta');

		/* Required Version  */
		if (!defined('RT_MEDIA_REQUIRED_BP'))
			define('RT_MEDIA_REQUIRED_BP', '1.7');


		/* Slug Constants for building urls */

		/* Media slugs */

		if (!defined('RT_MEDIA_MEDIA_SLUG'))
			define('RT_MEDIA_MEDIA_SLUG', 'media');

		if (!defined('RT_MEDIA_MEDIA_LABEL'))
			define('RT_MEDIA_MEDIA_LABEL', __('Media','rt-media'));

		if (!defined('RT_MEDIA_ALBUM_SLUG'))
			define('RT_MEDIA_ALBUM_SLUG', 'album');

		if (!defined('RT_MEDIA_ALBUM_PLURAL_SLUG'))
			define('RT_MEDIA_ALBUM_PLURAL_SLUG', 'albums');

		if (!defined('RT_MEDIA_ALBUM_LABEL'))
			define('RT_MEDIA_ALBUM_LABEL', __('Album','rt-media'));

		if (!defined('RT_MEDIA_ALBUM_PLURAL_LABEL'))
			define('RT_MEDIA_ALBUM_PLURAL_LABEL', __('Albums','rt-media'));

		/* Upload slug */
		if (!defined('RT_MEDIA_UPLOAD_SLUG'))
			define('RT_MEDIA_UPLOAD_SLUG', 'upload');

		/* Upload slug */
		if (!defined('RT_MEDIA_UPLOAD_LABEL'))
			define('RT_MEDIA_UPLOAD_LABEL', __('Upload','rt-media'));




		$this->define_type_constants();


	}

	function define_type_constants(){

		if(!isset($this->allowed_types)) return;
		foreach($this->allowed_types as $type){

			if(!isset($type['name'])|| $type['name']==='')
				continue;

			$name = $type['name'];

			if(isset($type['plural'])&& $type['plural']!=''){
				$plural = $type['plural'];
			}else{
				$plural = $name.'s';
			}

			if(isset($type['label'])&& $type['label']!=''){
				$label = $type['label'];
			}else{
				$label = ucfirst($name);
			}

			if(isset($type['label_plural'])&& $type['label_plural']!=''){
				$label_plural = $type['label_plural'];
			}else{
				$label_plural = ucfirst($plural);
			}

			$slug = strtoupper($name);

			if(!defined('RT_MEDIA_'.$slug.'_SLUG'))
					define('RT_MEDIA_'.$slug.'_SLUG',$name);
			if(!defined('RT_MEDIA_'.$slug.'_PLURAL_SLUG'))
					define('RT_MEDIA_'.$slug.'_PLURAL_SLUG',$plural);
			if(!defined('RT_MEDIA_'.$slug.'_LABEL'))
					define('RT_MEDIA_'.$slug.'_LABEL',$label);
			if(!defined('RT_MEDIA_'.$slug.'_PLURAL_LABEL'))
					define('RT_MEDIA_'.$slug.'_PLURAL_LABEL',$label_plural);

		}


	}

	/**
	 * Hooks the plugin into BuddyPress via 'bp_include' action.
	 * Initialises the plugin's functionalities, options,
	 * loads media for Profiles and Groups.
	 * Creates Admin panels
	 * Loads accessory functions
	 *
	 * @global BPMediaAdmin $bp_media_admin
	 */
	function init() {

		/**
		 *
		 * Buddypress Media Auto Upgradation
		 */
		$this->update_db();

		/**
		 * Load options/settings
		 */
		$this->init_site_options();

		/**
		 * Add a settings link to the Plugin list screen
		 */
//            add_filter('plugin_action_links', array($this, 'settings_link'), 10, 2);

		/**
		 * BuddyPress - Media Navigation Tab Inject
		 *
		 */
		if(class_exists('BuddyPress')) {
			add_action('bp_init', array($this,'custom_media_nav_tab'), 10,1);
		}

		/**
		 * Load accessory functions
		 */
//			new BPMediaActivity();
		$class_construct = array(
			'deprecated' => true,
			'interaction' => true,
			//'template'	=> false,
			'upload_shortcode' => false,
			'gallery_shortcode' => false,
			'upload_endpoint' => false,
				//'query'		=> false
		);
		$class_construct = apply_filters('bpmedia_class_construct', $class_construct);

		foreach ($class_construct as $key => $global_scope) {
			$classname = '';
			$ck = explode('_', $key);

			foreach ($ck as $cn) {
				$classname .= ucfirst($cn);
			}

			$class = 'RTMedia' . $classname;

			if (class_exists($class)) {
				if ($global_scope == true) {
					global ${'rt_media_' . $key};
					${'rt_media_' . $key} = new $class();
				} else {
					new $class();
				}
			}
		}

                $media = new RTMediaMedia();
                $media->delete_hook();
	}

	/**
	 * Loads translations
	 */
	static function load_translation() {
		load_plugin_textdomain('rt-media', false, basename(RT_MEDIA_PATH) . '/languages/');
	}

	function flush_rewrite() {
		error_log('flush');
		flush_rewrite_rules();
	}

	function check_global_album() {
		$album = new RTMediaAlbum();
		$global_album = $album->get_default();
                //**
                    if(isset($_POST["action"]) && isset($_POST["mode"]) && $_POST["mode"] == "file_upload"){
                        unset($_POST["name"]);
                    }
                
                //**
		if(!$global_album) {
			$global_album = $album->add_global(__("rtMedia Global Album","rt-media"));
		}
	}

	function default_count() {
		$count = $this->posts_per_page;
		if (array_key_exists('default_count', $this->options)) {
			$count = $this->options['default_count'];
		}
		$count = (!is_int($count)) ? 0 : $count;
		return (!$count) ? 10 : $count;
	}

	static function plugin_get_version($path = NULL) {
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		$path = ($path) ? $path : RT_MEDIA_PATH . 'index.php';
		$plugin_data = get_plugin_data($path);
		$plugin_version = $plugin_data['Version'];
		return $plugin_version;
	}

        function update_db() {
            $update = new RTDBUpdate();
            if ($update->check_upgrade()) {
                $update->do_upgrade();
            }
            new RTMediaMigration();
        }

	function enqueue_scripts_styles() {
		wp_enqueue_style('rt-media-main', RT_MEDIA_URL . 'app/assets/css/main.css', '', RT_MEDIA_VERSION);

		wp_enqueue_script('rt-media-helper', RT_MEDIA_URL . 'app/assets/js/rt.media.helper.js', array('jquery'), RT_MEDIA_VERSION);
	}

}

function rt_media_update_site_option($option_name,$option_value) {
	update_site_option($option_name, $option_value);
}

function rt_media_get_site_option($option_name,$default=false){
	$return_val	 = get_site_option($option_name);
	if($return_val === false){
		if(function_exists("bp_get_option")){
			$return_val = bp_get_option($option_name,$default);
			rt_media_update_site_option($option_name, $return_val);
		}
	}
	if($default!== false && $return_val === false){
		$return_val = $default;
	}
	return $return_val;
}


/**
 * This wraps up the main rtMedia class. Three important notes:
 *
 * 1. All the constants can be overridden.
 *    So, you could use, 'portfolio' instead of 'media'
 * 2. The default thumbnail and display sizes can be filtered
 *    using 'bpmedia_media_sizes' hook
 * 3. The excerpts and string sizes can be filtered
 *    using 'bpmedia_excerpt_lengths' hook
 *
 */

