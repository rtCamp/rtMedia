<?php

/*
Plugin Name: BuddyPress Media Component with KalturaCE Support 
Plugin URI: http://mediabp.com/
Description: This BuddyPress media component adds features like photos, videos and audio upload to BuddyPress.
Version: 1.2.5
Revision Date: 27 July 2010
Author: rtCamp, Rahul Bansal, Kapil Gonge
Author URI: http://mediabp.com/about/
*/



/**
 *  Make sure BuddyPress is loaded
 */
if ( !function_exists( 'bp_core_install' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    if ( is_plugin_active( 'buddypress/bp-loader.php' ) )
        require_once ( WP_PLUGIN_DIR . '/buddypress/bp-loader.php' );
    else
        return;
}

/**
 *
 * This function adds a bp_init hook with the buddypress and loads the all respective files.
 */
function media_init() {
    /**
     * Extending Group API
     */

    require_once 'bp-media-group.php';


    /* Define the slug for the component */
    $this_plguin_dir = basename(dirname(__FILE__));
    if ( !defined( 'BP_MEDIA_SLUG' ) )
        define ( 'BP_MEDIA_SLUG', 'media' );

    if ( !defined( 'BP_PHOTO_SLUG' ) )
        define ( 'BP_PHOTO_SLUG', 'photo' );

    if ( !defined( 'BP_VIDEO_SLUG' ) )
        define ( 'BP_VIDEO_SLUG', 'video' );

    define ( 'BP_MEDIA_IS_INSTALLED', 1 );
    define ( 'BP_MEDIA_VERSION', '1.005-beta' );
    define ( 'BP_MEDIA_DB_VERSION', '1000' );
    define ( 'BP_MEDIA_PLUGIN_NAME', $this_plguin_dir );
    define ( 'BP_MEDIA_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . BP_MEDIA_PLUGIN_NAME );
    define ( 'BP_MEDIA_PLUGIN_URL', WP_PLUGIN_URL . '/' . BP_MEDIA_PLUGIN_NAME );
    define ( 'BP_MEDIA_ACTIVITY_ACTION_COMMENT', 'bp_media_comment' );
    if ( !defined( 'WP_PLUGIN_DIR' ) )
        define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

    if ( !defined( 'WP_PLUGIN_NAME' ) )
        define( 'WP_PLUGIN_NAME', basename(dirname(__FILE__)) );

    if ( !defined( 'BP_MEDIA_PERSONAL_ACTIVITY_HISTORY' ) )
        define( 'BP_MEDIA_PERSONAL_ACTIVITY_HISTORY', 100 );

    require ( BP_MEDIA_PLUGIN_DIR . '/bp-media.php' );

//    require_once 'bp-media.php';
}

if ( defined( 'BP_VERSION' ) )
    media_init();
else
    add_action( 'bp_init', 'media_init' );



?>