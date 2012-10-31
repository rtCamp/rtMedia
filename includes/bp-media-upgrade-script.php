<?php

function bp_media_upgrade_to_2_2(){
	global $wpdb;
	remove_filter('bp_activity_get_user_join_filter','bp_media_activity_query_filter',10);
	/* @var $wpdb wpdb */
	$users = get_users();
	foreach($users as $user){
		$wall_posts_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = 'Wall Posts' AND post_author = '".  $user->ID."' AND post_type='bp_media_album'");
		if($wall_posts_id==null){
			$album = new BP_Media_Album();
			$album->add_album('Wall Posts',$user->ID);
			$wall_posts_id = $album->get_id();
		}
		if(!$wall_posts_id){
			continue;
		}

		$query = "SELECT * FROM $wpdb->posts WHERE post_type = 'bp_media' AND post_author=$user->ID";
		$media_files = $wpdb->get_results($query);
		foreach($media_files as $media_file){
			$attachment_id = get_post_meta($media_file->ID,'bp_media_child_attachment',true);
			$child_activity = get_post_meta($media_file->ID,'bp_media_child_activity',true);
			$status = update_post_meta($attachment_id, 'bp_media_child_activity', $child_activity);
			$attachment = get_post($attachment_id , ARRAY_A);
			$attachment['post_parent'] = $wall_posts_id;
			$result =  wp_update_post($attachment);
			$status = update_post_meta($attachment_id,'bp-media-key',$user->ID);
			$activity = bp_activity_get(array('in'=>intval($child_activity)));
			if(isset($activity['activities'][0]->id))
				$activity = $activity['activities'][0];
			$bp_media = new BP_Media_Host_Wordpress($attachment_id);
			$args = array(
				'content'	=>	$bp_media->get_media_activity_content(),
				'id'	=>	$child_activity,
				'type' => 'media_upload',
				'action' => apply_filters( 'bp_media_added_media', sprintf( __( '%1$s added a %2$s', 'bp-media'), bp_core_get_userlink( $user->ID ), '<a href="' . $bp_media->get_url() . '">' . $bp_media->get_media_activity_type() . '</a>' ) ),
				'primary_link' => $bp_media->get_url(),
				'item_id' => $attachment_id,
			);
			$act_id = bp_media_record_activity($args);
			bp_activity_delete_meta($child_activity, 'bp_media_parent_post');
			wp_delete_post($media_file->ID);
		}
	}
	$bp_media_options = get_option('bp_media_options',array(
		'videos_enabled'	=>	true,
		'audio_enabled'		=>	true,
		'images_enabled'	=>	true,
		'require_upgrade'	=>	false
	));
	$bp_media_options['require_upgrade'] = false;
	update_option('bp_media_options',$bp_media_options);
}

?>