<?php

/**
 * Shortcode for generating the action of the activity
 */
function bp_media_shortcode_action($atts) {
	extract(shortcode_atts(array(
			'id' => '0'
			), $atts)
	);
	$media=new BP_Media_Host_Wordpress($id);
	return $media->get_media_activity_action();
}
//add_shortcode('bp_media_action', 'bp_media_shortcode_action');

/**
 * Shortcode for generationg the content of the activity
 */
function bp_media_shortcode_content($atts) {
	extract(shortcode_atts(array(
			'id' => '0'
			), $atts)
	);
	$media=new BP_Media_Host_Wordpress($id);
	return $media->get_media_activity_content();
}
//add_shortcode('bp_media_content', 'bp_media_shortcode_content');

function bp_media_shortcode_url($atts) {
	extract(shortcode_atts(array(
			'id' => '0'
			), $atts)
	);
	$media=new BP_Media_Host_Wordpress($id);
	return $media->get_media_activity_url();
}
//add_shortcode('bp_media_url','bp_media_shortcode_url');
?>