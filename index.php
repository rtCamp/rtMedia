<?php
/*
  Plugin Name: BuddyPress Media
  Plugin URI: http://rtcamp.com/buddypress-media/
  Description: This plugin adds missing media rich features like photos, videos and audios uploading to BuddyPress which are essential if you are building social network, seriously!
  Version: 2.4
  Author: rtCamp
  Text Domain: buddypress-media
  Author URI: http://rtcamp.com
  Text domain: buddypress-media
 */

/*
 * Base constants that provide the plugin's path and directory
 */
if ( ! defined( 'BP_MEDIA_PATH' ) )
	define( 'BP_MEDIA_PATH', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'BP_MEDIA_URL' ) )
	define( 'BP_MEDIA_URL', plugin_dir_url( __FILE__ ) );

/*
 * Autoloads classes on instantiation.
 */
function buddypress_media_autoloader( $class_name ) {
	$rtlibpath = array(
		'app/helper/' . $class_name . '.php',
		'app/admin/' . $class_name . '.php',
		'app/main/' . $class_name . '.php',
		'app/main/profile/' . $class_name . '.php',
		'app/main/group/' . $class_name . '.php',
		'app/main/group/dummy/' . $class_name . '.php',
		'app/main/includes/' . $class_name . '.php',
		'app/main/widgets/' . $class_name . '.php',
	);
	foreach ( $rtlibpath as $i => $path ) {
		$path = BP_MEDIA_PATH . $path;
		if ( file_exists( $path ) ) {
			include $path;
			break;
		}
	}
}

/*
 * Register the autoloader function into spl_autoload
 */
spl_autoload_register( 'buddypress_media_autoloader' );

/*
 * Instantiate the BuddyPressMedia class.
 */
global $bp_media;
$bp_media = new BuddyPressMedia();

/*
 * Activating the plugin!
 */
register_activation_hook( __FILE__, array( $bp_media, 'activate' ) );

/*
 * And hooking it to BuddyPress
 */
add_action( 'bp_include', array($bp_media, 'init') );

/*
 * Look Ma! Very few includes!
 */
?>
