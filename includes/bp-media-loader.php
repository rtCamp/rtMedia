<?php
/**
 * The Main loader file of BuddyPress Media Plugin
 */
/* Exit if accessed directlly. */

if (!defined('ABSPATH'))
	exit;

/* Slug Constants */
define('BP_MEDIA_SLUG', 'media');
define('BP_MEDIA_UPLOAD_SLUG', 'upload');
define('BP_MEDIA_DELETE_SLUG', 'delete');

define('BP_MEDIA_IMAGES_SLUG', 'photos');
define('BP_MEDIA_IMAGES_ENTRY_SLUG', 'view');
define('BP_MEDIA_IMAGES_EDIT_SLUG', 'edit');

define('BP_MEDIA_VIDEOS_SLUG', 'videos');
define('BP_MEDIA_VIDEOS_ENTRY_SLUG', 'watch');
define('BP_MEDIA_VIDEOS_EDIT_SLUG', 'edit');

define('BP_MEDIA_AUDIO_SLUG', 'music');
define('BP_MEDIA_AUDIO_ENTRY_SLUG', 'listen');
define('BP_MEDIA_AUDIO_EDIT_SLUG', 'edit');

define('BP_MEDIA_ALBUMS_SLUG', 'albums');
define('BP_MEDIA_ALBUMS_ENTRY_SLUG', 'list');
define('BP_MEDIA_ALBUMS_EDIT_SLUG', 'edit');

/* Label Constants(need to be translatable) */
define('BP_MEDIA_LABEL', __('Media', 'bp-media'));
define('BP_MEDIA_LABEL_SINGULAR', __('Media', 'bp-media'));
define('BP_MEDIA_IMAGES_LABEL', __('Photos', 'bp-media'));
define('BP_MEDIA_IMAGES_LABEL_SINGULAR', __('Photo', 'bp-media'));
define('BP_MEDIA_VIDEOS_LABEL', __('Videos', 'bp-media'));
define('BP_MEDIA_VIDEOS_LABEL_SINGULAR', __('Video', 'bp-media'));
define('BP_MEDIA_AUDIO_LABEL', __('Music', 'bp-media'));
define('BP_MEDIA_AUDIO_LABEL_SINGULAR', __('Music', 'bp-media'));
define('BP_MEDIA_ALBUMS_LABEL', __('Albums', 'bp-media'));
define('BP_MEDIA_ALBUMS_LABEL_SINGULAR', __('Album', 'bp-media'));
define('BP_MEDIA_UPLOAD_LABEL', __('Upload', 'bp-media'));

/* Global variable to store the query */
global $bp_media_query;

/* Global variable to store the albums query */
global $bp_media_albums_query;

/* Global variable for making distinct ids for different media objects in activity stream */
global $bp_media_counter;
$bp_media_counter = 0;

/* Global variable storing the count of the media files displayed user has */
global $bp_media_count;
$bp_media_count=null;

/* Global variable for various display sizes */
global $bp_media_default_sizes;
$bp_media_default_sizes = array(
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

/* Global variable to store various excerpt sizes */
global $bp_media_default_excerpts;
$bp_media_default_excerpts=array(
	'single_entry_title'	=>	100,
	'single_entry_description'	=>	500,
	'activity_entry_title'	=> 50,
	'activity_entry_description'=>	500
);

global $bp_media_options;
$bp_media_options = get_option('bp_media_options',array(
	'videos_enabled'	=>	true,
	'audio_enabled'		=>	true,
	'images_enabled'	=>	true,
	'download_enabled'	=>	true
));

global $bp_media_posts_per_page;
$bp_media_posts_per_page = 10;

global $bp_media_activity_types;
$bp_media_activity_types = array('media_upload','album_updated','album_created');

global $bp_media_hidden_activity_cache;
$bp_media_hidden_activity_cache = array();

/* To set the language according to the locale selected and availability of the language file. */
if (file_exists(BP_MEDIA_PLUGIN_DIR . '/languages/' . get_locale() . '.mo'))
	load_textdomain('bp-media', BP_MEDIA_PLUGIN_DIR . '/languages/' . get_locale() . '.mo');

/**
 * BP Media Class, extends BP_Component
 *
 * @see BP_Component
 *
 * @since BP Media 2.0
 */
class BP_Media_Component extends BP_Component {

	/**
	 * Hold the messages generated during initialization process and will be shown on the screen functions
	 *
	 * @since BP Media 2.0
	 */
	var $messages = array(
		'error' => array(),
		'info' => array(),
		'updated' => array()
	);


	/**
	 * Constructor for the BuddyPress Media
	 *
	 * @since BP Media 2.0
	 */
	function __construct() {
		global $bp;
		parent::start(BP_MEDIA_SLUG, BP_MEDIA_LABEL, BP_MEDIA_PLUGIN_DIR);
		$this->includes();
		$bp->active_components[$this->id] = '1';
		add_action('init', array(&$this, 'register_post_types'));
	}

	/**
	 * Includes the files required for the BuddyPress Media and calls the parent class' includes function
	 *
	 * @since BP Media 2.0
	 */
	function includes() {
		$includes = array(
			'includes/bp-media-screens.php',
			'includes/bp-media-functions.php',
			'includes/bp-media-filters.php',
			'includes/bp-media-template-functions.php',
			'includes/bp-media-actions.php',
			'includes/bp-media-interface.php',
			'includes/bp-media-class-wordpress.php',
			'includes/bp-media-shortcodes.php',
			'includes/bp-media-widgets.php',
			'includes/bp-media-class-album.php',
		);
		if (is_admin() || is_network_admin()) {
			$includes[] = 'includes/bp-media-admin.php';
		}
		parent::includes($includes);
		do_action('bp_media_init');
	}

	/**
	 * Initializes the global variables of the BuddyPress Media and its parent class.
	 */
	function setup_globals() {
		global $bp;
		$globals = array(
			'slug' => BP_MEDIA_SLUG,
			'root_slug' => isset($bp->pages->{$this->id}->slug) ? $bp->pages->{$this->id}->slug : BP_MEDIA_SLUG,
			/*'has_directory'         => true, /* Set to false if not required */
			'search_string' => __('Search Media...', 'bp-media'),
		);
		parent::setup_globals($globals);
	}

	function setup_nav() {
		/* Add 'Media' to the main navigation */
		global $bp;
		switch($bp->current_component){
			case BP_MEDIA_IMAGES_SLUG:
				if(is_numeric($bp->current_action)){
					$bp->action_variables[0]=$bp->current_action;
					$bp->current_action=BP_MEDIA_IMAGES_ENTRY_SLUG;
				}
				break;
			case BP_MEDIA_AUDIO_SLUG:
				if(is_numeric($bp->current_action)){
					$bp->action_variables[0]=$bp->current_action;
					$bp->current_action=BP_MEDIA_AUDIO_ENTRY_SLUG;
				}
				break;
			case BP_MEDIA_VIDEOS_SLUG:
				if(is_numeric($bp->current_action)){
					$bp->action_variables[0]=$bp->current_action;
					$bp->current_action=BP_MEDIA_VIDEOS_ENTRY_SLUG;
				}
				break;
			case BP_MEDIA_ALBUMS_SLUG:
				if(is_numeric($bp->current_action)){
					$bp->action_variables[0]=$bp->current_action;
					$bp->current_action=BP_MEDIA_ALBUMS_ENTRY_SLUG;
				}
				break;
		}

		if (bp_is_my_profile()) {
			$main_nav = array(
				'name' => BP_MEDIA_LABEL,
				'slug' => BP_MEDIA_SLUG,
				'position' => 80,
				'screen_function' => 'bp_media_upload_screen',
				'default_subnav_slug' => BP_MEDIA_UPLOAD_SLUG
			);
		} else {
			$main_nav = array(
				'name' => BP_MEDIA_LABEL,
				'slug' => BP_MEDIA_SLUG,
				'position' => 80,
				'screen_function' => 'bp_media_images_screen',
				'default_subnav_slug' => BP_MEDIA_IMAGES_SLUG
			);
		}
		$sub_nav[] = array(
			'name' => BP_MEDIA_UPLOAD_LABEL,
			'slug' => BP_MEDIA_UPLOAD_SLUG,
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_SLUG),
			'parent_slug' => BP_MEDIA_SLUG,
			'screen_function' => 'bp_media_upload_screen',
			'position' => 10,
			'user_has_access' => bp_is_my_profile()
		);
		parent::setup_nav($main_nav, $sub_nav);

		bp_core_new_nav_item(array(
			'name' => BP_MEDIA_IMAGES_LABEL,
			'slug' => BP_MEDIA_IMAGES_SLUG,
			'screen_function' => 'bp_media_images_screen'
		));

		bp_core_new_subnav_item(array(
			'name' => 'View', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => BP_MEDIA_IMAGES_ENTRY_SLUG, /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_IMAGES_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_images_screen', /* The name of the function to run when clicked */
		));

		bp_core_new_subnav_item(array(
			'name' => 'Edit', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => BP_MEDIA_IMAGES_EDIT_SLUG, /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_IMAGES_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_images_screen', /* The name of the function to run when clicked */
		));

		bp_core_new_subnav_item(array(
			'name' => 'Delete', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => BP_MEDIA_DELETE_SLUG, /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_IMAGES_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_images_screen', /* The name of the function to run when clicked */
		));

		bp_core_new_subnav_item(array(
			'name' => 'Page', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => 'page', /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_IMAGES_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_images_screen', /* The name of the function to run when clicked */
		));


		bp_core_new_nav_item(array(
			'name' => BP_MEDIA_VIDEOS_LABEL,
			'slug' => BP_MEDIA_VIDEOS_SLUG,
			'screen_function' => 'bp_media_videos_screen'
		));

		bp_core_new_subnav_item(array(
			'name' => 'Watch', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => BP_MEDIA_VIDEOS_ENTRY_SLUG, /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_VIDEOS_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_VIDEOS_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_videos_screen', /* The name of the function to run when clicked */
		));

		bp_core_new_subnav_item(array(
			'name' => 'Edit', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => BP_MEDIA_VIDEOS_EDIT_SLUG, /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_VIDEOS_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_VIDEOS_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_videos_edit_screen', /* The name of the function to run when clicked */
		));

		bp_core_new_subnav_item(array(
			'name' => 'Delete', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => BP_MEDIA_DELETE_SLUG, /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_VIDEOS_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_VIDEOS_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_videos_screen', /* The name of the function to run when clicked */
		));

		bp_core_new_subnav_item(array(
			'name' => 'Page', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => 'page', /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_VIDEOS_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_VIDEOS_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_videos_screen', /* The name of the function to run when clicked */
		));


		bp_core_new_nav_item(array(
			'name' => BP_MEDIA_AUDIO_LABEL,
			'slug' => BP_MEDIA_AUDIO_SLUG,
			'screen_function' => 'bp_media_audio_screen'
		));

		bp_core_new_subnav_item(array(
			'name' => 'Listen', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => BP_MEDIA_AUDIO_ENTRY_SLUG, /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_AUDIO_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_AUDIO_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_audio_screen', /* The name of the function to run when clicked */
		));

		bp_core_new_subnav_item(array(
			'name' => 'Edit', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => BP_MEDIA_AUDIO_EDIT_SLUG, /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_AUDIO_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_AUDIO_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_audio_edit_screen', /* The name of the function to run when clicked */
		));

		bp_core_new_subnav_item(array(
			'name' => 'Delete', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => BP_MEDIA_DELETE_SLUG, /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_AUDIO_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_AUDIO_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_audio_screen', /* The name of the function to run when clicked */
		));

		bp_core_new_subnav_item(array(
			'name' => 'Page', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => 'page', /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_AUDIO_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_AUDIO_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_audio_screen', /* The name of the function to run when clicked */
		));


		bp_core_new_nav_item(array(
			'name' => BP_MEDIA_ALBUMS_LABEL,
			'slug' => BP_MEDIA_ALBUMS_SLUG,
			'screen_function' => 'bp_media_albums_screen'
		));

		bp_core_new_subnav_item(array(
			'name' => 'View', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => BP_MEDIA_ALBUMS_ENTRY_SLUG, /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_ALBUMS_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_ALBUMS_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_albums_screen', /* The name of the function to run when clicked */
		));

		bp_core_new_subnav_item(array(
			'name' => 'Edit', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => BP_MEDIA_ALBUMS_EDIT_SLUG, /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_ALBUMS_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_ALBUMS_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_albums_edit_screen', /* The name of the function to run when clicked */
		));

		bp_core_new_subnav_item(array(
			'name' => 'Delete', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => BP_MEDIA_DELETE_SLUG, /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_ALBUMS_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_ALBUMS_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_albums_screen', /* The name of the function to run when clicked */
		));

		bp_core_new_subnav_item(array(
			'name' => 'Page', /* Display name for the nav item(It won't be shown anywhere) */
			'slug' => 'page', /* URL slug for the nav item */
			'parent_slug' => BP_MEDIA_ALBUMS_SLUG, /* URL slug of the parent nav item */
			'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_ALBUMS_SLUG), /* URL of the parent item */
			'position' => 90, /* Index of where this nav item should be positioned */
			'screen_function' => 'bp_media_albums_screen', /* The name of the function to run when clicked */
		));
	}

	/**
	* Creating a custom post type album for BuddyPress Media
	*/
	function register_post_types() {
		$labels = array(
			'name' => __('Albums', 'bp-media'),
			'singular_name' => __('Album', 'bp-media'),
			'add_new' => __('Create', 'bp-media'),
			'add_new_item' => __('Create Album', 'bp-media'),
			'edit_item' => __('Edit Album', 'bp-media'),
			'new_item' => __('New Album', 'bp-media'),
			'all_items' => __('All Albums', 'bp-media'),
			'view_item' => __('View Album', 'bp-media'),
			'search_items' => __('Search Albums', 'bp-media'),
			'not_found' =>  __('No album found', 'bp-media'),
			'not_found_in_trash' => __('No album found in Trash', 'bp-media'),
			'parent_item_colon' => '',
			'menu_name' => __('Albums', 'bp-media')
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => false,
			'show_in_menu' => false,
			'query_var' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title', 'author', 'thumbnail', 'excerpt', 'comments' )
		);
		register_post_type('bp_media_album', $args);

		/* Set up labels for the post type */
		$labels = array(
			'name' => __('Media', 'bp-media'),
			'singular' => __('Media', 'bp-media'),
			'add_new' => __('Add New Media', 'bp-media')
		);

		/* Set up the argument array for register_post_type() */
		$args = array(
			'label' => __('Media', 'bp-media'),
			'labels' => $labels,
			'description' => 'BuddyPress Media\'s Media Files',
			'public' => true,
			'show_ui' => false,
			'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'custom-fields')
		);
		register_post_type('bp_media', $args);
		parent::register_post_types();
	}
}

function bp_media_load_core_component() {
	global $bp;
	$bp->{BP_MEDIA_SLUG} = new BP_Media_Component();
}
add_action('bp_loaded', 'bp_media_load_core_component');

/**
 * Function to set the custom navigation system in effect.
 */
function bp_media_custom_nav() {
	global $bp;
	foreach ($bp->bp_nav as $key => $nav_item) {
		switch($nav_item['slug']){
			case BP_MEDIA_IMAGES_SLUG:
			case BP_MEDIA_VIDEOS_SLUG:
			case BP_MEDIA_AUDIO_SLUG:
			case BP_MEDIA_ALBUMS_SLUG:
				$bp->bp_options_nav[BP_MEDIA_SLUG][] = array(
					'name' => $nav_item['name'],
					'link' => (isset($bp->displayed_user->domain) ? $bp->displayed_user->domain : (isset($bp->loggedin_user->domain) ? $bp->loggedin_user->domain : '')) . $nav_item['slug'] . '/',
					'slug' => $nav_item['slug'],
					'css_id' => $nav_item['css_id'],
					'position' => $nav_item['position'],
					'screen_function' => $nav_item['screen_function'],
					'user_has_access' => true,
					'parent_url' => trailingslashit(bp_displayed_user_domain())
				);
				unset($bp->bp_nav[$key]);
		}
		switch($bp->current_component){
			case BP_MEDIA_IMAGES_SLUG:
			case BP_MEDIA_VIDEOS_SLUG:
			case BP_MEDIA_AUDIO_SLUG:
			case BP_MEDIA_ALBUMS_SLUG:
				$count = count($bp->action_variables);
				for ($i = $count; $i > 0; $i--) {
					$bp->action_variables[$i] = $bp->action_variables[$i - 1];
				}
				$bp->action_variables[0] = $bp->current_action;
				$bp->current_action = $bp->current_component;
				$bp->current_component = BP_MEDIA_SLUG;
		}
	}
}
add_action('bp_setup_nav', 'bp_media_custom_nav', 999);

function bp_media_thumbnail() {
	global $bp_media_default_sizes;
	add_image_size('bp_media_activity_image', $bp_media_default_sizes['activity_image']['width'], $bp_media_default_sizes['activity_image']['height'], true);
	add_image_size('bp_media_single_image', $bp_media_default_sizes['single_image']['width'], $bp_media_default_sizes['single_image']['height'], true);
}
add_action('after_setup_theme', 'bp_media_thumbnail');

function bp_media_fetch_feeds() {
	if(isset($_GET['bp_media_get_feeds'])&&$_GET['bp_media_get_feeds']=='1'){
		bp_media_get_feeds();
		die();
	}
}
add_action('init','bp_media_fetch_feeds');
?>