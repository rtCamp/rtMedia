<?php

/**
 * Screens for all the slugs defined in the BuddyPress Media Component
 */

//Exit if accessed directlly.
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Screen function for Images
 */
function bp_media_images_screen() {
	add_action( 'bp_template_title', 'bp_media_images_screen_title' );
	add_action( 'bp_template_content', 'bp_media_images_screen_content' );
	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
	function bp_media_images_screen_title() {
		_e('Images');
	}
	function bp_media_images_screen_content() {
		_e('Test Images Content');
	}
	
/**
 * Screen function for Videos
 */
function bp_media_videos_screen() {
	//test_activity();
	add_action( 'bp_template_title', 'bp_media_videos_screen_title' );
	add_action( 'bp_template_content', 'bp_media_videos_screen_content' );
	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
	function bp_media_videos_screen_title() {
		_e('Videos');
	}
	function bp_media_videos_screen_content() {
		global $bp;
		_e('Test Videos Content');
	}
	
/**
 * Screen function for Audio
 */
function bp_media_audio_screen() {
	//test_activity();
	add_action( 'bp_template_title', 'bp_media_audio_screen_title' );
	add_action( 'bp_template_content', 'bp_media_audio_screen_content' );
	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
	function bp_media_audio_screen_title() {
		_e('Audio');
	}
	function bp_media_audio_screen_content() {
		global $bp;
		_e('Test Audo Content');

	}
	
/**
 * Screen function for Upload
 */
function bp_media_upload_screen() {
	
	add_action( 'bp_template_title', 'bp_media_upload_screen_title' );
	
	add_action( 'bp_template_content', 'bp_media_upload_screen_content' );
	
	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
	function bp_media_upload_screen_title() {
		_e('Upload');
	}
	function bp_media_upload_screen_content() {
		do_action('bp_media_before_content');
		bp_media_show_upload_form();
		do_action('bp_media_after_content');
	}
?>