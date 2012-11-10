<?php

function bp_media_upgrade_from_1_0_to_2_1(){
	global $wpdb;
	remove_filter('bp_activity_get_user_join_filter','bp_media_activity_query_filter',10);
	/* @var $wpdb wpdb */
	$wall_posts_album_ids=array();
	do{
		$media_files = new WP_Query(array(
			'post_type'	=>	'bp_media',
			'posts_per_page' => 10
		));
		$media_files = isset($media_files->posts)?$media_files->posts:null;
		if(is_array($media_files)&&count($media_files)){
			foreach($media_files as $media_file){
				$attachment_id = get_post_meta($media_file->ID,'bp_media_child_attachment',true);
				$child_activity = get_post_meta($media_file->ID,'bp_media_child_activity',true);
				update_post_meta($attachment_id, 'bp_media_child_activity', $child_activity);
				$attachment = get_post($attachment_id , ARRAY_A);
				if(isset($wall_posts_album_ids[$media_file->post_author])){
					$wall_posts_id = $wall_posts_album_ids[$media_file->post_author];
				}
				else{
					$wall_posts_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = 'Wall Posts' AND post_author = '".  $media_file->post_author."' AND post_type='bp_media_album'");
					if($wall_posts_id==null){
						$album = new BP_Media_Album();
						$album->add_album('Wall Posts',$media_file->post_author);
						$wall_posts_id = $album->get_id();
					}
					if(!$wall_posts_id){
						continue; //This condition should never be encountered
					}
					$wall_posts_album_ids[$media_file->post_author] = $wall_posts_id;
				}
				$attachment['post_parent'] = $wall_posts_id;
				wp_update_post($attachment);
				update_post_meta($attachment_id,'bp-media-key',$media_file->post_author);
				$activity = bp_activity_get(array('in'=>intval($child_activity)));
				if(isset($activity['activities'][0]->id))
					$activity = $activity['activities'][0];
				try{
					$bp_media = new BP_Media_Host_Wordpress($attachment_id);
				}
				catch(exception $e){
					continue;
				}
				$args = array(
					'content'	=>	$bp_media->get_media_activity_content(),
					'id'	=>	$child_activity,
					'type' => 'media_upload',
					'action' => apply_filters( 'bp_media_added_media', sprintf( __( '%1$s added a %2$s', 'bp-media'), bp_core_get_userlink( $media_file->post_author ), '<a href="' . $bp_media->get_url() . '">' . $bp_media->get_media_activity_type() . '</a>' ) ),
					'primary_link' => $bp_media->get_url(),
					'item_id' => $attachment_id,
					'recorded_time' => $activity->date_recorded,
					'user_id' => $bp_media->get_author()
				);
				$act_id = bp_media_record_activity($args);
				bp_activity_delete_meta($child_activity, 'bp_media_parent_post');
				wp_delete_post($media_file->ID);
			}
		}
		else{
			break;
		}
	}while(1);
	update_option('bp_media_db_version',BP_MEDIA_DB_VERSION);
	add_action('admin_notices','bp_media_database_updated_notice');
	wp_cache_flush();
}
function bp_media_database_updated_notice(){echo '<div class="updated rt-success"><p>
		<b>BuddyPress Media</b> Database upgraded successfully.
	</p></div>';}
function bp_media_upgrade_from_2_0_to_2_1(){
	$page = 0;
	while($media_entries = bp_media_return_query_posts(array(
		'post_type' => 'attachment',
		'post_status'	=>	'any',
		'meta_key' => 'bp-media-key',
		'meta_value' => 0,
		'meta_compare' => '>',
		'paged' => ++$page,
		'postsperpage' => 10
	))){
		foreach($media_entries as $media){
			try{
				$bp_media = new BP_Media_Host_Wordpress($media->ID);
			} catch (exception $e){
				continue;
			}
			$child_activity = get_post_meta($media->ID,'bp_media_child_activity',true);
			if($child_activity){
				$activity = bp_activity_get(array('in'=>intval($child_activity)));
				if(isset($activity['activities'][0]->id))
					$activity = $activity['activities'][0];
				else
					continue;
				$args = array(
					'content'	=>	$bp_media->get_media_activity_content(),
					'id'	=>	$child_activity,
					'type' => 'media_upload',
					'action' => apply_filters( 'bp_media_added_media', sprintf( __( '%1$s added a %2$s', 'bp-media'), bp_core_get_userlink( $bp_media->get_author() ), '<a href="' . $bp_media->get_url() . '">' . $bp_media->get_media_activity_type() . '</a>' ) ),
					'primary_link' => $bp_media->get_url(),
					'item_id' => $activity->item_id,
					'recorded_time' => $activity->date_recorded,
					'user_id' => $bp_media->get_author()
				);
				bp_media_record_activity($args);
			}
		}
	}
	update_option('bp_media_db_version',BP_MEDIA_DB_VERSION);
	add_action('admin_notices','bp_media_database_updated_notice');
	wp_cache_flush();
}

function bp_media_return_query_posts($args){
	$bp_media_query = new WP_Query($args);
	return $bp_media_query->posts;
}
?>