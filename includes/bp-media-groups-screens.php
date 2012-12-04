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
	echo '<br/>current component:'.$bp->current_component;
	echo '<br/>current action: '. $bp->current_action;
	echo '<br/>action variables:';
	echo '<pre>';
	var_dump($bp->action_variables);
	echo '</pre>';
	if(isset($bp->action_variables[0])){
		switch($bp->action_variables[0]){
			case BP_MEDIA_IMAGES_SLUG:
				if(isset($bp->action_variables[1])){
					switch($bp->action_variables[1]){
						case BP_MEDIA_IMAGES_EDIT_SLUG:
							//Edit screen for image
							break;
						case BP_MEDIA_DELETE_SLUG:
							//Delete function for media file
							break;
						default:
							if(intval($bp->action_variables[1])>0){
								//Single entry page
							}
							else{
								/** @todo display 404 */
							}
					}
				}else{
					bp_media_images_screen_content();
				}
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
							if(intval($bp->action_variables[1])>0){
								//Single entry page
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
							if(intval($bp->action_variables[1])>0){
								//Single entry page
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
							if(intval($bp->action_variables[1])>0){
								//Single entry page
							}
							else{
								/** @todo display 404 */
							}
					}
				}else{
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

		}
	}
}
?>