<?php

/**
 * Called on bp_init by screen functions
 *
 * @uses global $bp, $bp_media_query
 *
 * @since BP Media 2.0
 */
function bp_media_groups_set_query() {
	global $bp, $bp_media_query,$bp_media_posts_per_page;
	if(isset($bp->current_action)&&$bp->current_action==BP_MEDIA_SLUG){
		if(bp_action_variable(0)){
			switch (bp_action_variable(0)) {
				case BP_MEDIA_IMAGES_SLUG:
					$type = 'image';
					break;
				case BP_MEDIA_AUDIO_SLUG:
					$type = 'audio';
					break;
				case BP_MEDIA_VIDEOS_SLUG:
					$type = 'video';
					break;
				default :
					$type = null;
			}
			if (bp_action_variable(1)=='page'&&  is_numeric(bp_action_variable(2))) {
				$paged = bp_action_variable(2);
			} else {
				$paged = 1;
			}
			if ($type) {
				$args = array(
					'post_type' => 'attachment',
					'post_status'	=>	'any',
					'post_mime_type' =>	$type,
					'meta_key' => 'bp-media-key',
					'meta_value' => -bp_get_current_group_id(),
					'meta_compare' => '=',
					'paged' => $paged,
					'posts_per_page' => $bp_media_posts_per_page
				);
				$bp_media_query = new WP_Query($args);
			}
		}
	}
}


/**
 * Called on bp_init by screen functions
 * Initializes the albums query for groups
 *
 * @uses global $bp, $bp_media_albums_query
 *
 * @since BP Media 2.2
 */
function bp_media_groups_albums_set_query() {
	global $bp, $bp_media_albums_query;
	if (isset($bp->action_variables) && isset($bp->action_variables[1]) && $bp->action_variables[1] == 'page' && isset($bp->action_variables[2]) && is_numeric($bp->action_variables[2])) {
		$paged = $bp->action_variables[2];
	} else {
		$paged = 1;
	}

	if (isset($bp->action_variables[0])&&$bp->action_variables[0] == BP_MEDIA_ALBUMS_SLUG) {
		$args = array(
			'post_type' => 'bp_media_album',
			'paged' => $paged,
			'meta_key' => 'bp-media-key',
			'meta_value' => -bp_get_current_group_id(),
			'meta_compare' => '='
		);
		$bp_media_albums_query = new WP_Query($args);
	}
}

?>