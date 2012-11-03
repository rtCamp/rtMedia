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
		global $activities_template;
		remove_filter('bp_activity_get_user_join_filter','bp_media_activity_query_filter',10);
		$parent = $activity_obj->item_id;
		if ($parent) {
			try{
				if(isset($activities_template->activity_parents[$parent])){
					return $activities_template->activity_parents[$parent]->primary_link;
				}
				else{
					$activities = bp_activity_get(array('in' => $parent));
					if(isset($activities['activities'][0])){
						$activities_template->activity_parents[$parent] = $activities['activities'][0];
						return $activities['activities'][0]->primary_link;
					}
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

function bp_media_activity_parent_content_filter($activity_content) {
	global $activities_template;
	$defaults = array(
		'hide_user' => false
	);
	if ( !$parent_id = $activities_template->activity->item_id )
		return false;
	if(!isset($bp_media_hidden_activity_cache[$parent_id])){
		$activities = bp_activity_get(array('in' => $parent_id));
		if(isset($activities['activities'][0])){
			$bp_media_hidden_activity_cache[$parent_id] = $activities['activities'][0];
		}
	}
	if ( empty( $bp_media_hidden_activity_cache[$parent_id] ) )
		return false;

	if ( empty( $bp_media_hidden_activity_cache[$parent_id]->content ) )
		$content = $bp_media_hidden_activity_cache[$parent_id]->action;
	else
		$content = $bp_media_hidden_activity_cache[$parent_id]->action . ' ' . $bp_media_hidden_activity_cache[$parent_id]->content;

	// Remove the time since content for backwards compatibility
	$content = str_replace( '<span class="time-since">%s</span>', '', $content );

	// Remove images
	$content = preg_replace( '/<img[^>]*>/Ui', '', $content );

	return $content;
	return $activity_content;
}
//add_filter('bp_get_activity_parent_content', 'bp_media_activity_parent_content_filter', 1);

//function bp_media_activity_parent__content_filter($content) {
//	add_shortcode('bp_media_action', 'bp_media_shortcode_action');
//	add_shortcode('bp_media_content', 'bp_media_shortcode_content');
//	$content=do_shortcode($content);
//	remove_shortcode('bp_media_action');
//	remove_shortcode('bp_media_content');
//	return $content;
//}
//add_filter('bp_get_activity_parent_content', 'bp_media_activity_parent_content_filter');

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
function bp_media_activity_query_filter($query){
	global $wpdb;
	$query = preg_replace('/WHERE/i', 'WHERE a.secondary_item_id!=-999 AND ',$query);
	return $query;
}
add_filter('bp_activity_get_user_join_filter','bp_media_activity_query_filter',10);

?>