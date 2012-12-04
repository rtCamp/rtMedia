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
	switch ($bp->current_action) {
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
	if (isset($bp->action_variables)
			&& isset($bp->action_variables[0])
			&& $bp->action_variables[0] == 'page'
			&& isset($bp->action_variables[1])
			&& is_numeric($bp->action_variables[1])) {
		$paged = $bp->action_variables[1];
	} else {
		$paged = 1;
	}
	if ($type) {
		$args = array(
			'post_type' => 'attachment',
			'post_status'	=>	'any',
			'post_mime_type' =>	$type,
			'author' => $bp->displayed_user->id,
			'meta_key' => 'bp-media-key',
			'meta_value' => -bp_get_current_group_id(),
			'meta_compare' => 'LIKE',
			'paged' => $paged,
			'posts_per_page' => $bp_media_posts_per_page
		);
		$bp_media_query = new WP_Query($args);
	}
}

?>