<?php
/*
Plugin Name: BuddyPress Media
Plugin URI: http://rtcamp.com/buddypress-media/
Description: This plugin adds missing media rich features like photos, videos and audios uploading to BuddyPress which are essential if you are building social network, seriously!
Version: 2.2.8
Author: rtCamp
Author URI: http://rtcamp.com
*/

/* A constant that can be checked to see if the BP Media is installed or not. */
define('BP_MEDIA_IS_INSTALLED', 1);

/* Constant to store the current version of the BP Media Plugin. */
define('BP_MEDIA_VERSION', '2.2.8');

/* A constant to be used as base for other URLs throughout the plugin */
define('BP_MEDIA_PLUGIN_DIR', dirname(__FILE__));

/* A constant to store the required  */
define('BP_MEDIA_REQUIRED_BP','1.6');

/* A constatnt to store database version */
define('BP_MEDIA_DB_VERSION','2.1');

/* A constant to Active Collab API Assignee ID */
define('BP_MEDIA_AC_API_ASSIGNEE_ID','5');

/* A constant to Active Collab API Assignee ID */
define('BP_MEDIA_AC_API_LABEL_ID','1');

/* A constant to Active Collab API priority */
define('BP_MEDIA_AC_API_PRIORITY','2');

/* A constant to Active Collab API priority */
define('BP_MEDIA_AC_API_CATEGORY_ID','224');

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
	if (defined('BP_VERSION')&&version_compare(BP_VERSION, BP_MEDIA_REQUIRED_BP, '>')) {
		add_filter( 'plugin_action_links', 'bp_media_settings_link', 10, 2 );
		require( BP_MEDIA_PLUGIN_DIR . '/includes/bp-media-loader.php' );
		require( BP_MEDIA_PLUGIN_DIR . '/includes/bp-media-groups-loader.php');
	}
}
add_action('bp_include', 'bp_media_init');

/**
 * Function to do the tasks required to be done while activating the plugin
 */
function bp_media_activate() {
	$bp_media_options = get_site_option('bp_media_options',array(
		'videos_enabled'	=>	true,
		'audio_enabled'		=>	true,
		'images_enabled'	=>	true,
		'remove_linkback'	=>	'1',
		'download_enabled'	=>	true,
	));
	$previous_linkback_status = get_site_option('bp_media_remove_linkback');
	if($previous_linkback_status===false)
		$bp_media_options['remove_linkback'] = '1';
	else{
		$bp_media_options['remove_linkback'] = $previous_linkback_status;
		delete_option('bp_media_remove_linkback');
	}
	update_site_option('bp_media_options',$bp_media_options);


	$bpmquery = new WP_Query(array('post_type'=>'bp_media','posts_per_page'=>1));
	if($bpmquery->found_posts > 0){
		update_site_option('bp_media_db_version', '1.0');
	}else{
		switch(get_site_option('bp_media_db_version',false,false)){
			case '2.0':
				break;
			default:
				update_site_option('bp_media_db_version',BP_MEDIA_DB_VERSION);
		}
	}
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
			if (version_compare(BP_VERSION, BP_MEDIA_REQUIRED_BP, '<')) {
				echo '<div class="error"><p>';
				printf(__('The BuddyPress version installed is an older version and is not supported, please update BuddyPress to use BuddyPress Media Plugin.<a class="alignright" href="%1$s">X</a>'), '?bp_media_nag_ignore=0');
				echo "</p></div>";
			}
		}
		else {
			echo '<div class="error"><p>';
			printf(__('You have not installed BuddyPress. Please install latest version of BuddyPress to use BuddyPress Media plugin.<a class="alignright" href="%1$s">X</a>'), '?bp_media_nag_ignore=0');
			echo "</p></div>";
		}
    }
}
add_action('admin_notices', 'bp_media_admin_notice');

/**
 * Shows the settings link adjacent to the plugin in the plugins list
 * @since BP Media 2.0.4
 */
function bp_media_settings_link($links, $file) {
	/* create link */
	$plugin_name  = plugin_basename( __FILE__ );
	$admin_link = bp_media_get_admin_url( add_query_arg( array( 'page' => 'bp-media-settings'  ), 'admin.php' ) );
	if ( $file == $plugin_name ) {
		array_unshift(
			$links,
			sprintf( '<a href="%s">%s</a>', $admin_link, __('Settings') )
		);
	}
	return $links;
}

function bp_media_get_admin_url( $path = '', $scheme = 'admin' ) {

	// Links belong in network admin
	if (is_multisite() )
		$url = network_admin_url( $path, $scheme );

	// Links belong in site admin
	else
		$url = admin_url( $path, $scheme );

	return $url;
}
?>
