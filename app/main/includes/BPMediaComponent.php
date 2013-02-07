<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaComponent
 *
 * @author saurabh
 */
class BPMediaComponent extends BP_Component {

    /**
     * Hold the messages generated during initialization process and will be shown on the screen functions
     *
     * @since BuddyPress Media 2.0
     */
    var $messages = array(
        'error' => array(),
        'info' => array(),
        'updated' => array()
    );

    /**
     * Constructor for the BuddyPress Media
     *
     * @since BuddyPress Media 2.0
     */

    /**
     * 
     * @global type $bp
     */
    function __construct() {
        global $bp;
        parent::start(BP_MEDIA_SLUG, BP_MEDIA_LABEL, BP_MEDIA_PATH);
        $this->includes();
        $bp->active_components[$this->id] = '1';
        add_action('init', array(&$this, 'register_post_types'), 10);
    }

    /**
     * Includes the files required for the BuddyPress Media and calls the parent class' includes function
     *
     * @since BuddyPress Media 2.0
     */
    function includes() {
        $inc_path_prefix = 'app/main/includes/';
        $includes = array(
            $inc_path_prefix . 'bp-media-functions.php',
            $inc_path_prefix . 'bp-media-template-functions.php',
            $inc_path_prefix . 'bp-media-interface.php',
            $inc_path_prefix . 'bp-media-shortcodes.php',
            $inc_path_prefix . 'bp-media-widgets.php'
                //$inc_path_prefix . 'BPMediaFilter.php',
        );
        parent::includes($includes);
        do_action('bp_media_init');
    }

    /**
     * Initializes the global variables of the BuddyPress Media and its parent class.
     */

    /**
     * 
     * @global type $bp
     */
    function setup_globals() {
        global $bp;
        $globals = array(
            'slug' => BP_MEDIA_SLUG,
            'root_slug' => isset($bp->pages->{$this->id}->slug) ? $bp->pages->{$this->id}->slug : BP_MEDIA_SLUG,
            /* 'has_directory'         => true, /* Set to false if not required */
            'search_string' => __('Search Media...', BP_MEDIA_TXT_DOMAIN),
        );
        parent::setup_globals($globals);
    }

    /**
     * 
     * @global type $bp
     */
    function setup_nav() {
        /* Add 'Media' to the main navigation */
        global $bp;
        $bp_media_upload = new BPMediaUploadScreen('upload', BP_MEDIA_UPLOAD_SLUG);

        $bp_media_image = new BPMediaScreen('image', BP_MEDIA_IMAGES_SLUG);
        $bp_media_video = new BPMediaScreen('video', BP_MEDIA_VIDEOS_SLUG);
        $bp_media_audio = new BPMediaScreen('audio', BP_MEDIA_AUDIO_SLUG);

        $bp_media_album = new BPMediaAlbumScreen('album', BP_MEDIA_ALBUMS_SLUG);
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



        // -------------- Removed Upload as default tab ------------- //
//		if ( bp_is_my_profile() ) {
//			$main_nav = array(
//				'name' => BP_MEDIA_LABEL,
//				'slug' => BP_MEDIA_SLUG,
//				'position' => 80,
//				'screen_function' => array( $bp_media_upload, 'upload_screen' ),
//				'default_subnav_slug' => BP_MEDIA_UPLOAD_SLUG
//			);
//		} else {
        $main_nav = array(
            'name' => __(BP_MEDIA_LABEL, BP_MEDIA_TXT_DOMAIN),
            'slug' => BP_MEDIA_SLUG,
            'position' => 80,
            'screen_function' => array($bp_media_image, 'screen'),
            'default_subnav_slug' => BP_MEDIA_IMAGES_SLUG
        );
//		}
        $sub_nav[] = array();
        parent::setup_nav($main_nav, $sub_nav);
        bp_core_new_nav_item(array(
            'name' => __(BP_MEDIA_IMAGES_LABEL, BP_MEDIA_TXT_DOMAIN),
            'slug' => BP_MEDIA_IMAGES_SLUG,
            'screen_function' => array($bp_media_image, 'screen'),
        ));


        bp_core_new_subnav_item(array(
            'name' => 'View', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_IMAGES_ENTRY_SLUG, /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_IMAGES_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_image, 'screen'), /* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Edit', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_IMAGES_EDIT_SLUG, /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_IMAGES_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_image, 'edit_screen'), /* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Delete', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_DELETE_SLUG, /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_IMAGES_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_image, 'screen'), /* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Page', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => 'page', /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_IMAGES_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_image, 'screen'), /* The name of the function to run when clicked */
        ));


        bp_core_new_nav_item(array(
            'name' => __(BP_MEDIA_VIDEOS_LABEL, BP_MEDIA_TXT_DOMAIN),
            'slug' => BP_MEDIA_VIDEOS_SLUG,
            'screen_function' => array($bp_media_video, 'screen')
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Watch', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_VIDEOS_ENTRY_SLUG, /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_VIDEOS_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_VIDEOS_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_video, 'screen'), /* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Edit', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_VIDEOS_EDIT_SLUG, /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_VIDEOS_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_VIDEOS_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_video, 'edit_screen'), /* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Delete', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_DELETE_SLUG, /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_VIDEOS_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_VIDEOS_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_video, 'screen'), /* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Page', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => 'page', /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_VIDEOS_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_VIDEOS_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_video, 'screen'), /* The name of the function to run when clicked */
        ));


        bp_core_new_nav_item(array(
            'name' => __(BP_MEDIA_AUDIO_LABEL, BP_MEDIA_TXT_DOMAIN),
            'slug' => BP_MEDIA_AUDIO_SLUG,
            'screen_function' => array($bp_media_audio, 'screen')
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Listen', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_AUDIO_ENTRY_SLUG, /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_AUDIO_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_AUDIO_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_audio, 'screen'), /* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Edit', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_AUDIO_EDIT_SLUG, /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_AUDIO_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_AUDIO_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_audio, 'edit_screen'), /* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Delete', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_DELETE_SLUG, /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_AUDIO_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_AUDIO_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_audio, 'screen'), /* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Page', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => 'page', /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_AUDIO_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_AUDIO_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_audio, 'screen'), /* The name of the function to run when clicked */
        ));


        bp_core_new_nav_item(array(
            'name' => __(BP_MEDIA_ALBUMS_LABEL, BP_MEDIA_TXT_DOMAIN),
            'slug' => BP_MEDIA_ALBUMS_SLUG,
            'screen_function' => array($bp_media_album, 'screen'),
        ));

        bp_core_new_subnav_item(array(
            'name' => 'View', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_ALBUMS_ENTRY_SLUG, /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_ALBUMS_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_ALBUMS_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_album, 'screen'), /* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Edit', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_ALBUMS_EDIT_SLUG, /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_ALBUMS_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_ALBUMS_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_album, 'edit_screen'), /* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Delete', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => BP_MEDIA_DELETE_SLUG, /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_ALBUMS_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_ALBUMS_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_album, 'screen'), /* The name of the function to run when clicked */
        ));

        bp_core_new_subnav_item(array(
            'name' => 'Page', /* Display name for the nav item(It won't be shown anywhere) */
            'slug' => 'page', /* URL slug for the nav item */
            'parent_slug' => BP_MEDIA_ALBUMS_SLUG, /* URL slug of the parent nav item */
            'parent_url' => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_ALBUMS_SLUG), /* URL of the parent item */
            'position' => 90, /* Index of where this nav item should be positioned */
            'screen_function' => array($bp_media_album, 'screen'), /* The name of the function to run when clicked */
        ));
        bp_core_new_nav_item(array(
            'name' => __(BP_MEDIA_UPLOAD_LABEL, BP_MEDIA_TXT_DOMAIN),
            'slug' => BP_MEDIA_UPLOAD_SLUG,
            'screen_function' => array($bp_media_upload, 'upload_screen'),
            'user_has_access' => bp_is_my_profile()
        ));
    }

    /**
     * Creating a custom post type album for BuddyPress Media
     */
    function register_post_types() {
        $labels = array(
            'name' => __('Albums', BP_MEDIA_TXT_DOMAIN),
            'singular_name' => __('Album', BP_MEDIA_TXT_DOMAIN),
            'add_new' => __('Create', BP_MEDIA_TXT_DOMAIN),
            'add_new_item' => __('Create Album', BP_MEDIA_TXT_DOMAIN),
            'edit_item' => __('Edit Album', BP_MEDIA_TXT_DOMAIN),
            'new_item' => __('New Album', BP_MEDIA_TXT_DOMAIN),
            'all_items' => __('All Albums', BP_MEDIA_TXT_DOMAIN),
            'view_item' => __('View Album', BP_MEDIA_TXT_DOMAIN),
            'search_items' => __('Search Albums', BP_MEDIA_TXT_DOMAIN),
            'not_found' => __('No album found', BP_MEDIA_TXT_DOMAIN),
            'not_found_in_trash' => __('No album found in Trash', BP_MEDIA_TXT_DOMAIN),
            'parent_item_colon' => '',
            'menu_name' => __('Albums', BP_MEDIA_TXT_DOMAIN)
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
            'supports' => array('title', 'author', 'thumbnail', 'excerpt', 'comments')
        );
        register_post_type('bp_media_album', $args);
        global $bp_media;
        /* Set up labels for the post type */
        $labels = array(
            'name' => __('Media', BP_MEDIA_TXT_DOMAIN),
            'singular' => __('Media', BP_MEDIA_TXT_DOMAIN),
            'add_new' => __('Add New Media', BP_MEDIA_TXT_DOMAIN)
        );

        /* Set up the argument array for register_post_type() */
        $args = array(
            'label' => __('Media', BP_MEDIA_TXT_DOMAIN),
            'labels' => $labels,
            'description' => __('BuddyPress Media\'s Media Files', BP_MEDIA_TXT_DOMAIN),
            'public' => true,
            'show_ui' => false,
            'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'custom-fields')
        );
        register_post_type('bp_media', $args);
        parent::register_post_types();
    }

}

?>
