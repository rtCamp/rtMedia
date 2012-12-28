<?php
/*
  Plugin Name: BuddyPress Media
  Plugin URI: http://rtcamp.com/buddypress-media/
  Description: This plugin adds missing media rich features like photos, videos and audios uploading to BuddyPress which are essential if you are building social network, seriously!
  Version: 2.4
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
		$path = BP_MEDIA_PATH . $path;
		if ( file_exists( $path ) ) {
			include $path;
			break;
		}
	}
}

spl_autoload_register( 'buddypress_media_autoloader' );
global $bp_media;
$bp_media = new BuddyPressMedia();
register_activation_hook( __FILE__, array( $bp_media, 'activate' ) );
add_action( 'bp_include', array($bp_media, 'init') );

/*

add_action('activated_plugin','save_error');
function save_error(){
    delete_option('plugin_error');
    update_option('plugin_error',  ob_get_contents());
}
echo get_option('plugin_error');
 *
 */
?>
