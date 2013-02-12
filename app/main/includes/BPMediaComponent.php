<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Add BuddyPress Media as a component of BuddyPress
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 */
class BPMediaComponent extends BP_Component {

	/**
	 * Hold the messages that are generated during initialization process
	 * and will be shown on the screen functions
	 *
	 * @var array
	 */
    var $messages = array(
        'error' => array(),
        'info' => array(),
        'updated' => array()
    );

    /**
     * Initialise the component with appropriate parameters.
	 * Add hook for plugins, themes, extensions to hook on to
	 * Activates the component
	 * Registers necessary post types
	 *
     * @global object $bp The global BuddyPress object
     */
    function __construct() {
        global $bp;
        parent::start(BP_MEDIA_SLUG, BP_MEDIA_LABEL, BP_MEDIA_PATH);
        do_action('bp_media_init');
        $bp->active_components[$this->id] = '1';
        add_action('init', array(&$this, 'register_post_types'), 10);
    }

    /**
	 * Initialise the global variables of the BuddyPress Media
	 * and its parent class.
	 * Add necessary slugs and search functionality
     *
     * @global object $bp The global BuddyPress object
     */
    function setup_globals() {
        global $bp;
        $globals = array(
            'slug' => BP_MEDIA_SLUG,
            'root_slug' => isset(
					$bp->pages->{$this->id}->slug) ?
					$bp->pages->{$this->id}->slug
							: BP_MEDIA_SLUG,
            // 'has_directory'         => true, // Set to false if not required
            'search_string' => __('Search Media...', BP_MEDIA_TXT_DOMAIN),
        );
        parent::setup_globals($globals);
    }

    /**
	 * Sets up BuddyPress Media navigation and tabs on profile
     *
     * @global object $bp The global BuddyPress object
     */
    function setup_nav() {
        global $bp;

		/* Upload Screen */
        $bp_media_upload = new BPMediaUploadScreen(
				'upload',
				BP_MEDIA_UPLOAD_SLUG
				);

		/* Media Screens */
        $bp_media_image = new BPMediaScreen(
				'image',
				BP_MEDIA_IMAGES_SLUG
				);
        $bp_media_video = new BPMediaScreen(
				'video',
				BP_MEDIA_VIDEOS_SLUG
				);
        $bp_media_audio = new BPMediaScreen(
				'audio',
				BP_MEDIA_AUDIO_SLUG
				);

		/* Album Screen */
        $bp_media_album = new BPMediaAlbumScreen(
				'album',
				BP_MEDIA_ALBUMS_SLUG
				);

		/* Switch between different screens depending on context */
        switch ($bp->current_component) {
            case BP_MEDIA_IMAGES_SLUG:
                if (is_numeric($bp->current_action)) {
                    $bp->action_variables[0] = $bp->current_action;
                    $bp->current_action = BP_MEDIA_IMAGES_ENTRY_SLUG;
                }
                break;
            case BP_MEDIA_AUDIO_SLUG:
                if (is_numeric($bp->current_action)) {
                    $bp->action_variables[0] = $bp->current_action;
                    $bp->current_action = BP_MEDIA_AUDIO_ENTRY_SLUG;
                }
                break;
            case BP_MEDIA_VIDEOS_SLUG:
                if (is_numeric($bp->current_action)) {
                    $bp->action_variables[0] = $bp->current_action;
                    $bp->current_action = BP_MEDIA_VIDEOS_ENTRY_SLUG;
                }
                break;
            case BP_MEDIA_ALBUMS_SLUG:
                if (is_numeric($bp->current_action)) {
                    $bp->action_variables[0] = $bp->current_action;
                    $bp->current_action = BP_MEDIA_ALBUMS_ENTRY_SLUG;
                }
                break;
        }

		/* Create the main navigation on profile */
        $main_nav = array(
            'name' => __(BP_MEDIA_LABEL, BP_MEDIA_TXT_DOMAIN),
            'slug' => BP_MEDIA_SLUG,
            'position' => 80,
            'screen_function' => array($bp_media_image, 'screen'),
            'default_subnav_slug' => BP_MEDIA_IMAGES_SLUG
        );

		/* Create  an empty sub navigation */
        $sub_nav[] = array();

		/* Set up navigation */
        parent::setup_nav($main_nav, $sub_nav);

		/* Set up individual screens for each nav/sub nav */

        bp_core_new_nav_item(array(
            'name' => __(BP_MEDIA_IMAGES_LABEL, BP_MEDIA_TXT_DOMAIN),
            'slug' => BP_MEDIA_IMAGES_SLUG,
            'screen_function' => array($bp_media_image, 'screen'),
        ));


        bp_core_new_subnav_item(array(
            'name' => 'View',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_IMAGES_ENTRY_SLUG,
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_IMAGES_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_IMAGES_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_image, 'screen'),
			/* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Edit',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_IMAGES_EDIT_SLUG,
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_IMAGES_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_IMAGES_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_image, 'edit_screen'),
			/* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Delete',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_DELETE_SLUG,
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_IMAGES_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_IMAGES_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_image, 'screen'),
			/* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Page',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => 'page',
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_IMAGES_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_IMAGES_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_image, 'screen'),
			/* The name of the function to run when clicked */
        ));


        bp_core_new_nav_item(array(
            'name' => __(BP_MEDIA_VIDEOS_LABEL, BP_MEDIA_TXT_DOMAIN),
            'slug' => BP_MEDIA_VIDEOS_SLUG,
            'screen_function' => array($bp_media_video, 'screen')
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Watch',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_VIDEOS_ENTRY_SLUG,
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_VIDEOS_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_VIDEOS_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_video, 'screen'),
			/* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Edit',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_VIDEOS_EDIT_SLUG,
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_VIDEOS_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_VIDEOS_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_video, 'edit_screen'),
			/* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Delete',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_DELETE_SLUG,
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_VIDEOS_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_VIDEOS_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_video, 'screen'),
			/* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Page',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => 'page',
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_VIDEOS_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_VIDEOS_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_video, 'screen'),
			/* The name of the function to run when clicked */
        ));


        bp_core_new_nav_item(array(
            'name' => __(BP_MEDIA_AUDIO_LABEL, BP_MEDIA_TXT_DOMAIN),
            'slug' => BP_MEDIA_AUDIO_SLUG,
            'screen_function' => array($bp_media_audio, 'screen')
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Listen',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_AUDIO_ENTRY_SLUG,
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_AUDIO_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_AUDIO_SLUG), /* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_audio, 'screen'),
			/* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Edit',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_AUDIO_EDIT_SLUG,
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_AUDIO_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_AUDIO_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_audio, 'edit_screen'),
			/* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Delete',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_DELETE_SLUG,
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_AUDIO_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_AUDIO_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_audio, 'screen'),
			/* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Page',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => 'page',
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_AUDIO_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_AUDIO_SLUG), /* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_audio, 'screen'),
			/* The name of the function to run when clicked */
        ));


        bp_core_new_nav_item(array(
            'name' => __(BP_MEDIA_ALBUMS_LABEL, BP_MEDIA_TXT_DOMAIN),
            'slug' => BP_MEDIA_ALBUMS_SLUG,
            'screen_function' => array($bp_media_album, 'screen'),
        ));

        bp_core_new_subnav_item(array(
            'name' => 'View',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_ALBUMS_ENTRY_SLUG,
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_ALBUMS_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_ALBUMS_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_album, 'screen'),
			/* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Edit',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_ALBUMS_EDIT_SLUG,
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_ALBUMS_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_ALBUMS_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_album, 'edit_screen'),
			/* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Delete',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_DELETE_SLUG,
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_ALBUMS_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_ALBUMS_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_album, 'screen'),
			/* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Page',
			/* Display name for the nav item(It won't be shown anywhere) */
            'slug' => 'page',
			/* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_ALBUMS_SLUG,
			/* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain()
					. BP_MEDIA_ALBUMS_SLUG),
			/* URL of the parent item */
            'position' => 90,
			/* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_album, 'screen'),
			/* The name of the function to run when clicked */
        ));
        bp_core_new_nav_item(array(
            'name' => __(BP_MEDIA_UPLOAD_LABEL, BP_MEDIA_TXT_DOMAIN),
            'slug' => BP_MEDIA_UPLOAD_SLUG,
            'screen_function' => array($bp_media_upload, 'upload_screen'),
            'user_has_access' => bp_is_my_profile()
        ));
    }

    /**
     * Register Custom Post Types required by BuddyPress Media
     */
    function register_post_types() {

		/* Set up Album labels */
        $album_labels = array(
            'name'					=> __('Albums',
					BP_MEDIA_TXT_DOMAIN),
            'singular_name'			=> __('Album',
					BP_MEDIA_TXT_DOMAIN),
            'add_new'				=> __('Create',
					BP_MEDIA_TXT_DOMAIN),
            'add_new_item'			=> __('Create Album',
					BP_MEDIA_TXT_DOMAIN),
            'edit_item'				=> __('Edit Album',
					BP_MEDIA_TXT_DOMAIN),
            'new_item'				=> __('New Album',
					BP_MEDIA_TXT_DOMAIN),
            'all_items'				=> __('All Albums',
					BP_MEDIA_TXT_DOMAIN),
            'view_item'				=> __('View Album',
					BP_MEDIA_TXT_DOMAIN),
            'search_items'			=> __('Search Albums',
					BP_MEDIA_TXT_DOMAIN),
            'not_found'				=> __('No album found',
					BP_MEDIA_TXT_DOMAIN),
            'not_found_in_trash'	=> __('No album found in Trash',
					BP_MEDIA_TXT_DOMAIN),
            'parent_item_colon'		=> '',
            'menu_name'				=> __('Albums',
					BP_MEDIA_TXT_DOMAIN)
        );

		/* Set up Album post type arguments */
        $album_args = array(
            'labels'				=> $album_labels,
            'public'				=> true,
            'publicly_queryable'	=> true,
            'show_ui'				=> false,
            'show_in_menu'			=> false,
            'query_var'				=> true,
            'capability_type'		=> 'post',
            'has_archive'			=> true,
            'hierarchical'			=> false,
            'menu_position'			=> null,
            'supports'				=> array(
										'title',
										'author',
										'thumbnail',
										'excerpt',
										'comments'
									)
        );

		/* register Album post type */
        register_post_type('bp_media_album', $album_args);


        /* Set up labels for Media post type */
        $labels = array(
            'name' => __('Media', BP_MEDIA_TXT_DOMAIN),
            'singular' => __('Media', BP_MEDIA_TXT_DOMAIN),
            'add_new' => __('Add New Media', BP_MEDIA_TXT_DOMAIN)
        );

        /* Set up the arguments for Media post type */
        $args = array(
            'label' => __('Media', BP_MEDIA_TXT_DOMAIN),
            'labels' => $labels,
            'description' => __(
					'BuddyPress Media\'s Media Files',
					BP_MEDIA_TXT_DOMAIN
					),
            'public' => true,
            'show_ui' => false,
            'supports' => array(
				'title',
				'editor',
				'excerpt',
				'author',
				'thumbnail',
				'custom-fields'
				)
        );

		/* Register Media post type */
        register_post_type('bp_media', $args);

		/* Register parent's post types */
        parent::register_post_types();
    }

}

?>
