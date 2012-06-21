<?php

/**
 * 
 */
function bp_media_activity_permalink_filter($link,$activity_obj) {
	if ( 'media_upload' == $activity_obj->type )
		$link = $activity_obj->primary_link;
	return $link;
}
add_filter('bp_activity_get_permalink', 'bp_media_activity_permalink_filter',10,2);

function bp_media_upload_type_change_filter() {
	global $bp;
//	if(isset($bp->{BP_MEDIA_SLUG})) {
//		echo '<pre>';
//		var_dump(func_get_args());
//		echo '</pre>';
//	}
}
//add_filter('wp_handle_upload','bp_media_upload_type_change_filter',10,3);

?>