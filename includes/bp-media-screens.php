<?php

/**
 * 
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
		_e('Test Content');
	}
	
	
function bp_media_videos_screen() {
	
	add_action( 'bp_template_title', 'bp_media_videos_screen_title' );
	add_action( 'bp_template_content', 'bp_media_videos_screen_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
	function bp_media_videos_screen_title() {
		_e('Videos');
	}
	function bp_media_videos_screen_content() {
		global $bp;
		_e('Test Content');

	}
	
function bp_media_audio_screen() {
	
	add_action( 'bp_template_title', 'bp_media_audio_screen_title' );
	add_action( 'bp_template_content', 'bp_media_audio_screen_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
	function bp_media_audio_screen_title() {
		_e('Audio');
	}
	function bp_media_audio_screen_content() {
		global $bp;
		_e('Test Content');

	}
	
function bp_media_upload_screen() {
	
	add_action( 'bp_template_title', 'bp_media_upload_screen_title' );
	add_action( 'bp_template_content', 'bp_media_upload_screen_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
	function bp_media_upload_screen_title() {
		_e('Upload');
	}
	function bp_media_upload_screen_content() {
		global $bp;
		_e('Test Content');
	}
?>