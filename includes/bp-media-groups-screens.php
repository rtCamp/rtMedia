<?php

/**
 * Displays the Video, Audio, Image, Album and Upload screens
 *
 * @uses global $bp_media_group_sub_nav, $bp
 *
 * @since BP Media 2.3
 */
function bp_media_groups_display_screen(){
	global $bp_media_group_sub_nav,$bp;
	bp_media_groups_set_query();
	bp_media_groups_display_navigation_menu();
	if(bp_action_variable(0)){
		switch(bp_action_variable(0)){
			case BP_MEDIA_IMAGES_SLUG:
				bp_media_groups_images_screen();
				break;
			case BP_MEDIA_VIDEOS_SLUG:
				if(isset($bp->action_variables[1])){
					switch($bp->action_variables[1]){
						case BP_MEDIA_VIDEOS_EDIT_SLUG:
							//Edit screen for image
							break;
						case BP_MEDIA_DELETE_SLUG:
							//Delete function for media file
							break;
						default:
							if(intval(bp_action_variable(1))>0){
								global $bp_media_current_entry;
								try {
									$bp_media_current_entry = new BP_Media_Host_Wordpress(bp_action_variable(1));
									if($bp_media_current_entry->get_group_id()!= bp_get_current_group_id())
										throw new Exception(__('Sorry, the requested media does not belong to the group'));
								} catch (Exception $e) {
									/** Error Handling when media not present or not belong to the group */
									echo '<div id="message" class="error">';
									echo '<p>'.$e->getMessage().'</p>';
									echo '</div>';
									return;
								}
								bp_media_videos_entry_screen_content();
								break;
							}
							else{
								/** @todo display 404 */
							}
					}
				}else{
					bp_media_videos_screen_content();
				}
				break;
			case BP_MEDIA_AUDIO_SLUG:
				if(isset($bp->action_variables[1])){
					switch($bp->action_variables[1]){
						case BP_MEDIA_AUDIO_EDIT_SLUG:
							//Edit screen for image
							break;
						case BP_MEDIA_DELETE_SLUG:
							//Delete function for media file
							break;
						default:
							if(intval(bp_action_variable(1))>0){
								global $bp_media_current_entry;
								try {
									$bp_media_current_entry = new BP_Media_Host_Wordpress(bp_action_variable(1));
									if($bp_media_current_entry->get_group_id()!= bp_get_current_group_id())
										throw new Exception(__('Sorry, the requested media does not belong to the group'));
								} catch (Exception $e) {
									/** Error Handling when media not present or not belong to the group */
									echo '<div id="message" class="error">';
									echo '<p>'.$e->getMessage().'</p>';
									echo '</div>';
									return;
								}
								bp_media_audio_entry_screen_content();
								break;
							}
							else{
								/** @todo display 404 */
							}
					}
				}else{
					bp_media_audio_screen_content();
				}
				break;
			case BP_MEDIA_ALBUMS_SLUG:
				if(isset($bp->action_variables[1])){
					switch($bp->action_variables[1]){
						case BP_MEDIA_ALBUMS_EDIT_SLUG:
							//Edit screen for image
							break;
						case BP_MEDIA_DELETE_SLUG:
							//Delete function for media file
							break;
						default:
							if(intval(bp_action_variable(1))>0){
								global $bp_media_current_album;
								try {
									$bp_media_current_album = new BP_Media_Host_Wordpress(bp_action_variable(1));
									if($bp_media_current_album->get_group_id()!= bp_get_current_group_id())
										throw new Exception(__('Sorry, the requested album does not belong to the group'));
								} catch (Exception $e) {
									/** Error Handling when media not present or not belong to the group */
									echo '<div id="message" class="error">';
									echo '<p>'.$e->getMessage().'</p>';
									echo '</div>';
									return;
								}
								bp_media_albums_entry_screen_content();
								break;
							}
							else{
								/** @todo display 404 */
							}
					}
				}else{
					bp_media_groups_albums_set_query();
					bp_media_albums_screen_content();
				}
				break;
			default:
				/** @todo Error is to be displayed for 404 */
		}
	}
	else{
		if(bp_media_groups_can_upload())
			bp_media_upload_screen_content();
		else{
			$bp->action_variables[0] = BP_MEDIA_IMAGES_SLUG;
			bp_media_groups_set_query();
			bp_media_groups_images_screen();
		}
	}
}

function bp_media_groups_images_screen(){
	global $bp_media_current_entry;
	if(bp_action_variable(1)){
		switch(bp_action_variable(1)){
			case BP_MEDIA_IMAGES_EDIT_SLUG:
				//Edit screen for image
				break;
			case BP_MEDIA_DELETE_SLUG:
				//Delete function for media file
				break;
			default:
				if(intval(bp_action_variable(1))>0){
					global $bp_media_current_entry;
					try {
						$bp_media_current_entry = new BP_Media_Host_Wordpress(bp_action_variable(1));
						if($bp_media_current_entry->get_group_id()!= bp_get_current_group_id())
							throw new Exception(__('Sorry, the requested media does not belong to the group'));
					} catch (Exception $e) {
						/** Error Handling when media not present or not belong to the group */
						echo '<div id="message" class="error">';
						echo '<p>'.$e->getMessage().'</p>';
						echo '</div>';
						return;
					}
					bp_media_images_entry_screen_content();
					break;
				}
				else{
					/** @todo display 404 */
				}
		}
	}else{
		bp_media_images_screen_content();
	}
}
?>