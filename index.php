<?php

/*
  Plugin Name: rtMedia for WordPress, BuddyPress and bbPress
  Plugin URI: https://rtmedia.io/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media
  Description: This plugin adds missing media rich features like photos, videos and audio uploading to BuddyPress which are essential if you are building social network, seriously!
  Version: 4.1.8
  Author: rtCamp
  Text Domain: buddypress-media
  Author URI: http://rtcamp.com/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media
  Domain Path: /languages/
 */

/**
 * Main file, contains the plugin metadata and activation processes
 *
 * @package    BuddyPressMedia
 * @subpackage Main
 */
if ( ! defined( 'RTMEDIA_PATH' ) ) {

	/**
	 *  The server file system path to the plugin directory
	 *
	 */
	define( 'RTMEDIA_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'BP_MEDIA_PATH' ) ) {

	/**
	 *  Legacy support
	 *
	 */
	define( 'BP_MEDIA_PATH', RTMEDIA_PATH );
}


if ( ! defined( 'RTMEDIA_URL' ) ) {

	/**
	 * The url to the plugin directory
	 *
	 */
	define( 'RTMEDIA_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'RTMEDIA_BASE_NAME' ) ) {

	/**
	 * The url to the plugin directory
	 *
	 */
	define( 'RTMEDIA_BASE_NAME', plugin_basename( __FILE__ ) );
}

/**
 * Auto Loader Function
 *
 * Autoloads classes on instantiation. Used by spl_autoload_register.
 *
 * @param string $class_name The name of the class to autoload
 */
function rtmedia_autoloader( $class_name ) {
	$rtlibpath = array(
		'app/services/' . $class_name . '.php',
		'app/helper/' . $class_name . '.php',
		'app/helper/db/' . $class_name . '.php',
		'app/admin/' . $class_name . '.php',
		'app/main/interactions/' . $class_name . '.php',
		'app/main/routers/' . $class_name . '.php',
		'app/main/routers/query/' . $class_name . '.php',
		'app/main/controllers/upload/' . $class_name . '.php',
		'app/main/controllers/upload/processors/' . $class_name . '.php',
		'app/main/controllers/shortcodes/' . $class_name . '.php',
		'app/main/controllers/template/' . $class_name . '.php',
		'app/main/controllers/media/' . $class_name . '.php',
		'app/main/controllers/group/' . $class_name . '.php',
		'app/main/controllers/privacy/' . $class_name . '.php',
		'app/main/controllers/activity/' . $class_name . '.php',
		'app/main/deprecated/' . $class_name . '.php',
		'app/main/contexts/' . $class_name . '.php',
		'app/main/' . $class_name . '.php',
		'app/main/includes/' . $class_name . '.php',
		'app/main/widgets/' . $class_name . '.php',
		'app/main/upload/' . $class_name . '.php',
		'app/main/upload/processors/' . $class_name . '.php',
		'app/main/template/' . $class_name . '.php',
		'app/log/' . $class_name . '.php',
		'app/importers/' . $class_name . '.php',
		'app/main/controllers/api/' . $class_name . '.php',
	);
	foreach ( $rtlibpath as $path ) {
		$path = RTMEDIA_PATH . $path;
		if ( file_exists( $path ) ) {
			include $path;
			break;
		}
	}
}

/**
 * Register the autoloader function into spl_autoload
 */
spl_autoload_register( 'rtmedia_autoloader' );

/**
 * Instantiate the BuddyPressMedia class.
 */
global $rtmedia;
$rtmedia = new RTMedia();

function is_rtmedia_vip_plugin() {
	return ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV );
}
/*
 * Look Ma! Very few includes! Next File: /app/main/RTMedia.php
 */

function my_function( $data, $post_ID ) {

	$post = get_post( $post_ID );

	if ( $_REQUEST ) {

		if ( 'image-editor' == $_REQUEST['action'] && 'edit-attachment' == $_REQUEST['context'] ) {

			$media = new RTMediaModel();
			$media_available = $media->get_media( array(
				'media_id'	=> $_REQUEST['postid'],
			), 0, 1 );
			$media_id = $media_available[0]->id;
			if ( ! empty( $media_available ) ) {
				$rtmedia_filepath_old = rtmedia_image( 'rt_media_activity_image', $media_id, false );
				if ( isset( $rtmedia_filepath_old ) ) {
					$is_valid_url = preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $rtmedia_filepath_old );

					if ( $is_valid_url && function_exists( 'bp_is_active' ) && bp_is_active( 'activity' ) ) {
						$thumbnailinfo = wp_get_attachment_image_src( $post_ID, 'rt_media_activity_image' );
						$activity_id   = rtmedia_activity_id( $media_id );

						if ( $post_ID && ! empty( $activity_id ) ) {
							global $wpdb, $bp;

							if ( ! empty( $bp->activity ) ) {
								$media->model = new RTMediaModel();
								$related_media_data = $media->model->get( array( 'activity_id' => $activity_id ) );
								$related_media      = array();
								foreach ( $related_media_data as $activity_media ) {
									$related_media[] = $activity_media->id;
								}
								$activity_text = bp_activity_get_meta( $activity_id, 'bp_activity_text' );

								$activity = new RTMediaActivity( $related_media, 0, $activity_text );

								$activity_content_new = $activity->create_activity_html();

								$activity_content = str_replace( $rtmedia_filepath_old, wp_get_attachment_url( $post_ID ), $activity_content_new );

								$wpdb->update( $bp->activity->table_name, array( 'content' => $activity_content ), array( 'id' => $activity_id ) );
							}
						}
					}
				}
			}
		}
	}

	return $data;
}
add_filter( 'wp_update_attachment_metadata', 'my_function', 10, 2 );
