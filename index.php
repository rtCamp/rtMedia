<?php
/*
Plugin Name: BuddyPress Media
Plugin URI: http://rtcamp.com/buddypress-media/
Description: This plugin adds missing media rich features like photos, videos and audios uploading to BuddyPress which are essential if you are building social network, seriously!
Version: 2.3.2
Author: rtCamp
Author URI: http://rtcamp.com
*/

if ( ! defined( 'BP_MEDIA_PATH' ) )
	define( 'BP_MEDIA_PATH', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'BP_MEDIA_URL' ) )
	define( 'BP_MEDIA_URL', plugin_dir_url( __FILE__ ) );

function buddypress_media_autoloader( $class_name ) {
	$rtlibpath = array(
		'app/helper/' . $class_name . '.php',
		'app/admin/' . $class_name . '.php',
		'app/main/' . $class_name . '.php',
		'lib/rtlib/' . $class_name . '.php',
	);
	foreach ( $rtlibpath as $i => $path ) {
		if ( file_exists( $path ) ) {
			include BP_MEDIA_PATH.$path;
			break;
		}
	}
}

spl_autoload_register('buddypress_media_autoloader');

global $bp_media, $bp_admin;
$bp_admin = new BPMAdmin();
$bp_media = new BuddyPressMedia();
?>
