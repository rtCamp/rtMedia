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
	 * @var string The text domain for loading translations
	 */
	public $text_domain = 'rt-media';

	/**
	 *
	 * @var array RTMedia settings
	 */
	public $allowed_types = array(
		0 => array('name' => 'audio', 'extn' => array('mp3'), 'thumbnail' => '../assets/img/audio_thumb.png'),
		1 => array('name' => 'video', 'extn' => array('mp4'), 'thumbnail' => '../assets/img/video_thumb.png'),
		2 => array('name' => 'image', 'extn' => array('jpeg', 'png'), 'thumbnail' => '../assets/img/image_thumb.png')
	);

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

		/**
		 * Rewrite API flush before activating and after deactivating the plugin
		 */
		register_activation_hook(__FILE__, array($this, 'flush_rewrite'));
		register_deactivation_hook(__FILE__, array($this, 'flush_rewrite'));

		/**
		 *
		 * check for global album --- after wordpress is fully loaded
		 */
		add_action('wp', array($this, 'check_global_album'));

		/**
		 * Define constants
		 */
		$this->constants();
		/**
		 * Define excerpt lengths
		 */
		/**
		 * Hook it to BuddyPress
		 */
		add_action('plugins_loaded', array($this, 'init'));

		/**
		 * Load translations
		 */
		add_action('plugins_loaded', array($this, 'load_translation'));

		/**
		 * Admin Panel
		 */
		add_action('init', array($this, 'admin_init'));

		/**
		 * Initialise media counter
		 */
		global $bp_media_counter;
		$bp_media_counter = 0;
		$this->allowed_types = apply_filters('rt_media_allowed_types', $this->allowed_types);
		/**
		 *  Enqueue Plugin Scripts and Styles
		 */
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'), 11);
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
		$options = array(
			'enable_on_group' => 1,
			'enable_lightbox' => 1,
			'sizes' => array(
				'image' => array(
					'thumbnail' => array('width' => 150, 'height' => 150, 'crop' => 1),
					'medium' => array('width' => 320, 'height' => 240, 'crop' => 1),
					'large' => array('width' => 800, 'height' => 0, 'crop' => 1)
				),
				'video' => array(
					'medium' => array('width' => 320, 'height' => 240),
					'large' => array('width' => 640, 'height' => 480)
				),
				'audio' => array(
					'medium' => array('width' => 320),
					'large' => array('width' => 640)
				),
				'media' => array(
					'featured' => array('width' => 100, 'height' => 100, 'crop' => 1)
				)
			),
			'featured_image' => 0,
			'featured_video' => 0,
			'featured_audio' => 0,
			'videos_enabled' => 1,
			'audio_enabled' => 1,
			'images_enabled' => 1,
			'download_enabled' => 1,
			'show_admin_menu' => 1,
			'per_page_media' => 10,
			'media_end_point_enable' => true
		);
		add_site_option('enable_on_group', 1);
		add_site_option('enable_lightbox', 1);
		add_site_option('sizes', array(
			'image' => array(
				'thumbnail' => array('width' => 150, 'height' => 150, 'crop' => 1),
				'medium' => array('width' => 320, 'height' => 240, 'crop' => 1),
				'large' => array('width' => 800, 'height' => 0, 'crop' => 1)
			),
			'video' => array(
				'medium' => array('width' => 320, 'height' => 240),
				'large' => array('width' => 640, 'height' => 480)
			),
			'audio' => array(
				'medium' => array('width' => 320),
				'large' => array('width' => 640)
			),
			'media' => array(
				'featured' => array('width' => 100, 'height' => 100, 'crop' => 1)
			)
		));
		add_site_option('featured_image', 0);
		add_site_option('featured_video', 0);
		add_site_option('featured_audio', 0);
		add_site_option('video_enabled', 1);
		add_site_option('image_enabled', 1);
		add_site_option('videos_enabled', 1);
		add_site_option('download_enabled', 1);
		add_site_option('show_admin_menu', 1);
		add_site_option('per_page_media', 10);
		add_site_option('media_end_point_enable', true);
		add_site_option('comments_enabled', 1);
	}

	/**
	 * get Site Settings
	 */
	public function get_option($key) {

		$options = get_site_option($key);
		return $options;
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

		/* Media slug */
		if (!defined('RT_MEDIA_MEDIA_SLUG'))
			define('RT_MEDIA_MEDIA_SLUG', 'media');

		/* Upload slug */
		if (!defined('RT_MEDIA_UPLOAD_SLUG'))
			define('RT_MEDIA_UPLOAD_SLUG', 'upload');

		/* Upload slug */
		if (!defined('RT_MEDIA_UPLOAD_LABEL'))
			define('RT_MEDIA_UPLOAD_LABEL', 'Upload');
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
            new BuddyPressMigration();
            $update = new RTDBUpdate();
            if ($update->check_upgrade()) {
                $update->do_upgrade();
            }
        }

	function enqueue_scripts_styles() {
		wp_enqueue_style('rt-media-main', RT_MEDIA_URL . 'app/assets/css/main.css', '', RT_MEDIA_VERSION);

		wp_enqueue_script('rt-media-helper', RT_MEDIA_URL . 'app/assets/js/rt.media.helper.js', array('jquery'), RT_MEDIA_VERSION);
	}

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

