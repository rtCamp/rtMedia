<?php
/*
Plugin Name: BuddyPress Media Component
Plugin URI: http://rtcamp.com/buddypress-media/
Description: This component adds missing media rich features like photos, videos and audios uploading to BuddyPress which are essential if you are building social network, seriously!
Version: 2.0.4
Author: rtCamp
Author URI: http://rtcamp.com
 */

/* A constant that can be checked to see if the BP Media is installed or not. */
define('BP_MEDIA_IS_INSTALLED', 1);

/* Constant to store the current version of the BP Media Plugin. */
define('BP_MEDIA_VERSION', '2.0.4');

/* A constant to be used as base for other URLs throughout the plugin */
define('BP_MEDIA_PLUGIN_DIR', dirname(__FILE__));

/* A constant to store the Database Version of the BP Media Plugin */
define('BP_MEDIA_DB_VERSION', '1');

/**
 * Function to initialize the BP Media Plugin
 * 
 * It checks for the version minimum required version of buddypress before initializing.
 * 
 * @uses BP_VERSION to check if the plugin supports the BuddyPress version.
 * 
 * @since BP Media 2.0
 */
function bp_media_init() {
	if (defined('BP_VERSION')&&version_compare(BP_VERSION, '1.6.0', '>')) {
		require( BP_MEDIA_PLUGIN_DIR . '/includes/bp-media-loader.php' );
	}
	else{
		add_action('admin_notices', 'bp_media_admin_notice');
	}
}
add_action('bp_include', 'bp_media_init');

/**
 * Function to do the tasks required to be done while activating the plugin
 */
function bp_media_activate() {
	//todo
}
register_activation_hook(__FILE__, 'bp_media_activate');

/**
 * Function to do the tasks during deactivation.
 * 
 * Will Make this function to do the db deletion and other things that might have been created with the plugin.
 */
function bp_media_deactivate() {
	//todo
}
register_deactivation_hook(__FILE__, 'bp_media_deactivate');

function bp_media_admin_notice() {
    global $current_user ;
        $user_id = $current_user->ID;
	if ( isset($_GET['bp_media_nag_ignore']) && '0' == $_GET['bp_media_nag_ignore'] ) {
			add_user_meta($user_id, 'bp_media_ignore_notice', 'true', true);
	}
        /* Check that the user hasn't already clicked to ignore the message */
    if ( ! get_user_meta($user_id, 'bp_media_ignore_notice') ) {
		if(defined('BP_VERSION')){
			echo '<div class="error"><p>';
			printf(__('The BuddyPress version installed is an older version and is not supported, please update BuddyPress to use BuddyPress Media Plugin.<a class="alignright" href="%1$s">X</a>'), '?bp_media_nag_ignore=0');
			echo "</p></div>";
		}
		else{
			echo '<div class="error"><p>';
			printf(__('You have not installed BuddyPress. Please install latest version of BuddyPress to use BuddyPress Media plugin.<a class="alignright" href="%1$s">X</a>'), '?bp_media_nag_ignore=0');
			echo "</p></div>";
		}
    }
}
?>