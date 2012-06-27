<?php

/**
 * 
 */
function bp_media_activity_permalink_filter($link, $activity_obj) {
	if ('media_upload' == $activity_obj->type)
		$link = $activity_obj->primary_link;
	return $link;
}

add_filter('bp_activity_get_permalink', 'bp_media_activity_permalink_filter', 10, 2);
?>