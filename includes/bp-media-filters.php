<?php
function bp_media_activity_permalink_filter($link, $activity_obj = null) {
	if ($activity_obj != null && 'media_upload' == $activity_obj->type) {
		if(preg_match('/bp_media_urlid=(\d+)/i',$activity_obj->primary_link, $result)&&isset($result[1])){
			$media=new BP_Media_Host_Wordpress($result[1]);
			return $media->get_media_activity_url();
		}
	}
	if ($activity_obj != null && 'activity_comment' == $activity_obj->type) {
		$parent = bp_activity_get_meta($activity_obj->item_id, 'bp_media_parent_post');
		if ($parent) {
			$parent = new BP_Media_Host_Wordpress($parent);
			return $parent->get_url();
		}
	}
	return $link;
}
add_filter('bp_activity_get_permalink', 'bp_media_activity_permalink_filter', 10, 2);

function bp_media_activity_action_filter($activity_action, $activity_obj = null) {
	if ($activity_obj != null && 'media_upload' == $activity_obj->type) {
		add_shortcode('bp_media_action', 'bp_media_shortcode_action');
		$activity_action = do_shortcode($activity_action);
		remove_shortcode('bp_media_action');
	}
	return $activity_action;
}
add_filter('bp_get_activity_action', 'bp_media_activity_action_filter', 10, 2);

function bp_media_activity_content_filter($activity_content, $activity_obj = null ) {
	if ($activity_obj != null && 'media_upload' == $activity_obj->type) {
		add_shortcode('bp_media_content', 'bp_media_shortcode_content');
		$activity_content = do_shortcode($activity_content);
		remove_shortcode('bp_media_content');
	}
	return $activity_content;
}
add_filter('bp_get_activity_content_body', 'bp_media_activity_content_filter', 10, 2);

function bp_media_activity_parent_content_filter($content) {
	add_shortcode('bp_media_action', 'bp_media_shortcode_action');
	add_shortcode('bp_media_content', 'bp_media_shortcode_content');
	$content=do_shortcode($content);
	remove_shortcode('bp_media_action');
	remove_shortcode('bp_media_content');
	return $content;
}
add_filter('bp_get_activity_parent_content', 'bp_media_activity_parent_content_filter');

function bp_media_delete_button_handler($link) {
	if(bp_current_component()=='media')
		$link=str_replace('delete-activity ', 'delete-activity-single ', $link);
	return $link;
}
add_filter('bp_get_activity_delete_link','bp_media_delete_button_handler');

function bp_media_items_count_filter ($title,$nav_item) {
	global $bp_media_count;
	switch($nav_item['slug']){
		case BP_MEDIA_SLUG	:
			$count=  intval($bp_media_count['images'])+intval($bp_media_count['videos'])+intval($bp_media_count['audio']);
			break;
		case BP_MEDIA_IMAGES_SLUG:
			$count=  intval($bp_media_count['images']);
			break;
		case BP_MEDIA_VIDEOS_SLUG:
			$count=  intval($bp_media_count['videos']);
			break;
		case BP_MEDIA_AUDIO_SLUG:
			$count=  intval($bp_media_count['audio']);
			break;
	}
	$count_html=' <span>'. $count.'</span>';
	return str_replace('</a>', $count_html.'</a>', $title);
}
?>