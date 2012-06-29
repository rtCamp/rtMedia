<?php

/**
 * 
 */
function bp_media_activity_permalink_filter($link, $activity_obj) {
	if ('media_upload' == $activity_obj->type) {
		add_shortcode('bp_media_url', 'bp_media_shortcode_url');
		$link = do_shortcode($activity_obj->primary_link);
		remove_shortcode('bp_media_url');
	}
	return $link;
}

add_filter('bp_activity_get_permalink', 'bp_media_activity_permalink_filter', 10, 2);

function bp_media_activity_action_filter($activity_action, $activity_obj) {

	if ('media_upload' == $activity_obj->type) {
		add_shortcode('bp_media_action', 'bp_media_shortcode_action');
		$activity_action = do_shortcode($activity_action);
		remove_shortcode('bp_media_action');
	}
	return $activity_action;
}

add_filter('bp_get_activity_action', 'bp_media_activity_action_filter', 10, 2);

function bp_media_activity_content_filter($activity_content, $activity_obj) {
	if ('media_upload' == $activity_obj->type) {
		add_shortcode('bp_media_content', 'bp_media_shortcode_content');
		$activity_content = do_shortcode($activity_content);
		remove_shortcode('bp_media_content');
	}
	return $activity_content;
}

add_filter('bp_get_activity_content_body', 'bp_media_activity_content_filter', 10, 2);
?>