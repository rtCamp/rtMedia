<?php
/**
Plugin Name: BuddyPress Media Component
Plugin URI: http://www.rtCamp.com
Description: This component adds missing media rich features like photos, videos and audios uploading to BuddyPress which are essential if you are building social network, seriously!
Version: 0.0.0
Requires at least: WP 3.3.2, BuddyPress 1.5.5
Tested up to: Wordpress 3.3.2, what BuddyPress 1.5.5
Author: rtCamp
Author URI: http://www.rtCamp.com
 */

// A constant that can be checked to see if the BP Media is installed or not.
define( 'BP_MEDIA_IS_INSTALLED', 1 );

// Constant to store the current version of the BP Media Plugin.
define( 'BP_MEDIA_VERSION', '0.0.0' );

// A constant to be used as base for other URLs throughout the plugin
define( 'BP_MEDIA_PLUGIN_DIR', dirname( __FILE__ ) );

// A constant to store the Database Version of the BP Media Plugin
define ( 'BP_MEDIA_DB_VERSION', '1' );

/**
 * Function to initialize the BP Media Plugin
 * 
 * It checks for the version minimum required version of buddypress before initializing.
 * 
 */
function bp_media_init() {
	if(version_compare( BP_VERSION , '1.5','>' ) ) {
		require( BP_MEDIA_PLUGIN_DIR . '/includes/bp-media-loader.php' );
	}
}
//Add the initialize function to the bp_include hook
add_action('bp_include', 'bp_media_init');

/**
 * Function to do the tasks required to be done while activating the plugin
 */
function bp_media_activate() {
	//todo Make this function to do the db creation and other required things for the plugin before activating.
}
register_activation_hook( __FILE__, 'bp_media_activate' );


/**
 * Function to do the tasks during deactivation.
 */
function bp_media_deactivate() {
	//todo Make this function to do the db deletion and other things that might have been created with the plugin.
}
register_deactivation_hook( __FILE__, 'bp_media_deactivate' );


?>