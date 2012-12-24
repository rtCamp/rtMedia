<?php
/**
 * Description of BPMediaGroupLoader
 *
 * @author faishal
 */

class BPMediaGroupAction {
	/**
	 * Called on bp_init by screen functions
	 *
	 * @uses global $bp, $bp_media_query
	 *
	 * @since BP Media 2.0
	 */
	static function bp_media_groups_set_query() {
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
	static function bp_media_groups_albums_set_query() {
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

	static function bp_media_groups_activity_create_after_add_media($media,$hidden=false){
		if(function_exists('bp_activity_add')){
			if(!is_object($media)){
				try{
					$media = new BP_Media_Host_Wordpress($media);
				}catch(exception $e){
					return false;
				}
			}
			$args = array(
					'action' => apply_filters( 'bp_media_added_media', sprintf( __( '%1$s added a %2$s', 'bp-media'), bp_core_get_userlink( $media->get_author() ), '<a href="' . $media->get_url() . '">' . $media->get_media_activity_type() . '</a>' ) ),
					'content' => $media->get_media_activity_content(),
					'primary_link' => $media->get_url(),
					'item_id' => $media->get_id(),
					'type' => 'media_upload',
					'user_id' =>	$media->get_author()
				);
			$hidden = apply_filters('bp_media_force_hide_activity',$hidden);
			if($hidden){
				$args['secondary_item_id'] = -999;
				//do_action('bp_media_album_updated',$media->get_album_id());
			}
			$activity_id = bp_media_record_activity($args);
			add_post_meta($media->get_id(),'bp_media_child_activity',$activity_id);
		}
	}
	//add_action('bp_media_groups_after_add_media','bp_media_groups_activity_create_after_add_media',10,2);

	static function bp_media_groups_redirection_handler(){
		global $bp;
		echo '<pre>';
		var_dump($bp);
		echo '</pre>';
		die();
	}
	//add_action('bp_media_init','bp_media_groups_redirection_handler');

	static function bp_media_groups_force_hide_activity(){
		return true;
	}
}