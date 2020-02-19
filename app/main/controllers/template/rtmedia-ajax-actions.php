<?php

/**
 * Delete uploaded media.
 * Modified 10-02-2019 by Adarsh Verma <adarsh.verma@rtcamp.com>
 */
function rtmedia_delete_uploaded_media() {

	$action   = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );
	$nonce    = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
	$media_id = filter_input( INPUT_POST, 'media_id', FILTER_SANITIZE_NUMBER_INT );

	if ( ! empty( $action ) && 'delete_uploaded_media' === $action && ! empty( $media_id ) ) {
		if ( wp_verify_nonce( $nonce, 'rtmedia_' . get_current_user_id() ) ) {

			$rtmedia_media  = new RTMediaMedia();
			$rtmedia_media->delete( $media_id );

			// Fetch the remaining media count.
			if( class_exists( 'RTMediaNav' ) ) {
				global $bp;
				$rtmedia_nav_obj = new RTMediaNav();
				if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {
					$counts = $rtmedia_nav_obj->actual_counts( $bp->groups->current_group->id, 'group' );
				} else {
					$counts = $rtmedia_nav_obj->actual_counts( bp_displayed_user_id(), 'profile' );
				}
				$remaining_all_media = ( isset( $counts['total']['all'] ) && ! empty( $counts['total']['all'] ) ) ? $counts['total']['all'] : 0;
				$remaining_photos    = ( isset( $counts['total']['photo'] ) && ! empty( $counts['total']['photo'] ) ) ? $counts['total']['photo'] : 0;
				$remaining_videos    = ( isset( $counts['total']['video'] ) && ! empty( $counts['total']['video'] ) ) ? $counts['total']['video'] : 0;
				$remaining_music     = ( isset( $counts['total']['music'] ) && ! empty( $counts['total']['music'] ) ) ? $counts['total']['music'] : 0;
			} else {
				$remaining_photos = $remaining_music = $remaining_videos = $remaining_all_media = 0;
			}

			wp_send_json_success(
				array(
					'code'            => 'rtmedia-media-deleted',
					'all_media_count' => $remaining_all_media,
					'photos_count'    => $remaining_photos,
					'music_count'     => $remaining_music,
					'videos_count'    => $remaining_videos,
				)
			);

			wp_die();
		}
	}

	wp_send_json_error(
		array(
			'code'    => 'rtmedia-media-not-deleted',
			'message' => esc_html__( 'Doing wrong, invalid AJAX request!', 'buddypress-media' ),
		)
	);

	wp_die();

}

add_action( 'wp_ajax_delete_uploaded_media', 'rtmedia_delete_uploaded_media' );



/**
 * Update profile and comment activity content of comment media
 *
 * @global      object    $wpdb
 *
 * @param       int           $attachment_id
 */
if ( ! function_exists( 'rtmedia_transcoded_media_added_callback' ) ) {
	function rtmedia_transcoded_media_added_callback( $attachment_id ) {
		if ( isset( $attachment_id ) && ! empty( $attachment_id ) ) {
			$job_for = get_post_meta( $attachment_id, '_rt_media_source', true );
			if ( 'rtmedia' == $job_for && class_exists( 'RTMediaModel' ) ) {
				$model 	= new RTMediaModel();
				$media 	= $model->get_media( array( 'media_id' => $attachment_id ), 0, 1 );
				if ( isset( $media[0] ) && isset( $media[0]->activity_id ) && ! empty( $media[0]->activity_id ) ) {
					$activity_id = $media[0]->activity_id;
					$media_id = $media[0]->id;
					if ( $activity_id && isset( $media_id ) && ! empty( $media_id ) && function_exists( 'rtmedia_is_comment_media' ) && rtmedia_is_comment_media( $media_id ) ) {
						global $wpdb;
						$activity_content = $wpdb->get_var( $wpdb->prepare( "SELECT content FROM {$wpdb->base_prefix}bp_activity WHERE id = %d", $activity_id ) );
						if ( function_exists( 'rtmedia_update_content_of_comment_media' ) ) {
							rtmedia_update_content_of_comment_media( $media[0]->id, $activity_content );
						}
					}
				}
			}
		}
	}
}
add_action( 'transcoded_media_added', 'rtmedia_transcoded_media_added_callback', 10, 1 );

function rtmedia_actions_before_comments_convesation_callback() {
	global $rtmedia_media;

	/* check is comment media */
	$comment_media = rtmedia_is_comment_media( rtmedia_id() );

	if ( isset( $rtmedia_media->activity_id )  && ! empty( $rtmedia_media->activity_id ) && function_exists( 'rtmedia_view_conversation_of_media' ) && $comment_media ) {
		rtmedia_view_conversation_of_media( $rtmedia_media->activity_id );
	}
}
add_action( 'rtmedia_actions_before_comments', 'rtmedia_actions_before_comments_convesation_callback', 1000 );

function rtmedia_actions_before_comments_links_callback() {
	// check is comment media.
	$comment_media = false;
	if ( function_exists( 'rtmedia_is_comment_media_single_page' ) ) {
		$comment_media = rtmedia_is_comment_media_single_page( rtmedia_id() );
	}

	// if user is login and is not comment media.
	if ( is_user_logged_in() && empty( $comment_media ) ) { ?>
		<span>
			<a href='#' class='rtmedia-comment-link rtmedia-comments-link'><?php esc_html_e( 'Comment', 'buddypress-media' ); ?></a>
		</span>
	<?php
	}
}
add_action( 'rtmedia_actions_before_comments', 'rtmedia_actions_before_comments_links_callback', 11 );



/*
 * Runes when activity is add in buddypress
*/
function rtmedia_bp_activity_after_save_callback( $activity_data ) {
	/* check is  activity reply*/
	if ( ! empty( $activity_data )  && 'activity_comment' == $activity_data->type ) {
		/* check  that it has any media in it */
		if ( isset( $_REQUEST['rtMedia_attached_files'] ) && isset( $activity_data->id ) ) {
	        $rtMedia_attached_files = filter_input( INPUT_POST, 'rtMedia_attached_files', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	        /*  check is class exits or not */
			if ( is_array( $rtMedia_attached_files )  && ! empty( $rtMedia_attached_files[0] )  && class_exists( 'RTMediaModel' ) ) {

				if ( function_exists( 'rtmedia_get_original_comment_media_content' ) ) {
					/* get the original content of media */
					$original_content = rtmedia_get_original_comment_media_content();
					/* save the original content in the meta fields */
					bp_activity_update_meta( $activity_data->id, 'bp_activity_text', $original_content );
					// bp_activity_update_meta( $activity_data->id, 'bp_old_activity_content', $original_content );
				}

				$rtmedia_model = new RTMediaModel();
				$rtmedia_model->update(
					array(
						'activity_id' => $activity_data->id,
					),
					array(
						'id' => $rtMedia_attached_files[0],
					)
				);

				$privacy     = -1;
				$form_id = filter_input( INPUT_POST, 'form_id', FILTER_SANITIZE_NUMBER_INT );

				$rtm_activity_model  = new RTMediaActivityModel();
				$columns = array(
					'activity_id' => $form_id,
					'blog_id'     => get_current_blog_id(),
				);

				$is_ac_privacy_exist = $rtm_activity_model->get( $columns );

				/*  changing privacy according to it parent */
				if ( isset( $is_ac_privacy_exist[0] )  && ! empty( $is_ac_privacy_exist[0] ) && isset( $is_ac_privacy_exist[0]->privacy ) ) {
					$privacy = $is_ac_privacy_exist[0]->privacy;
				}

				// Very first privacy entry for this activity
				if ( $rtm_activity_model->check( $activity_data->id ) ) {
					$status = $rtm_activity_model->update(
						array(
							'privacy'     => $privacy,
							'activity_id' => $activity_data->id,
							'user_id'     => get_current_user_id(),
						), array( 'activity_id' => $activity_data->id )
					);
				} else {
					$status = $rtm_activity_model->insert(
						array(
							'privacy'     => $privacy,
							'activity_id' => $activity_data->id,
							'user_id'     => get_current_user_id(),
						)
					);
				}
			}
	    }
	}
}
add_action( 'bp_activity_after_save', 'rtmedia_bp_activity_after_save_callback', 1000, 1 );


/*
 * Change the BuddyPress activity Comment reply content
*/
function rtmedia_bp_activity_comment_content_callback( $content ) {
	$new_content = $content;
	if ( isset( $_REQUEST['rtMedia_attached_files'] )  && isset( $_REQUEST['comment_id'] ) && isset( $_REQUEST['form_id'] ) ) {
		$rtMedia_attached_files = filter_input( INPUT_POST, 'rtMedia_attached_files', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( ! empty( $rtMedia_attached_files[0] )  && is_array( $rtMedia_attached_files ) && class_exists( 'RTMediaActivity' ) ) {
			$obj_comment = new RTMediaActivity( $rtMedia_attached_files[0], 0, $content );
			$new_content = $obj_comment->create_activity_html();
		}
	}
	return $new_content;
}
add_action( 'bp_activity_content_before_save', 'rtmedia_bp_activity_comment_content_callback', 1001, 1 );