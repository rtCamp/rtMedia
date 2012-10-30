<?php
function bp_media_activity_permalink_filter($link, $activity_obj = null) {
	global $bp_media_activity_types;
	if ($activity_obj != null && in_array($activity_obj->type,$bp_media_activity_types)) {
		if($activity_obj->primary_link!=''){
			try{
				return $activity_obj->primary_link;
			}
			catch(Exception $e){
				return $link;
			}
		}
	}
	if ($activity_obj != null && 'activity_comment' == $activity_obj->type) {
		$parent = $activity_obj->item_id;
		if ($parent) {
			try{
				$activities = bp_activity_get(array('in' => $parent));
				if(isset($activities['activities'][0])){
					return $activities['activities'][0]->primary_link;
				}
			}
			catch(Exception $e){
				return $link;
			}
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
		case BP_MEDIA_ALBUMS_SLUG:
			$count=  intval($bp_media_count['albums']);
			break;
	}
	$count_html=' <span>'. $count.'</span>';
	return str_replace('</a>', $count_html.'</a>', $title);
}

/**
 * To hide some activities of multiple uploads
 */
add_filter('bp_activity_get_user_join_filter',function($query){
	global $wpdb;
	$query = preg_replace('/WHERE/i', 'WHERE a.secondary_item_id!=-999 AND ',$query);
		return $query;
},10);
?>