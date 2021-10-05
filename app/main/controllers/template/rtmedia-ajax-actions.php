<?php
/**
 * Includes ajax actions functions.
 *
 * @package rtMedia
 */

/**
 * Delete uploaded media.
 * Modified 10-02-2019 by Adarsh Verma <adarsh.verma@rtcamp.com>
 */
function rtmedia_delete_uploaded_media() {

	$action   = sanitize_text_field( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) );
	$nonce    = sanitize_text_field( filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING ) );
	$media_id = filter_input( INPUT_POST, 'media_id', FILTER_SANITIZE_NUMBER_INT );

	if ( ! empty( $action ) && 'delete_uploaded_media' === $action && ! empty( $media_id ) ) {
		if ( wp_verify_nonce( $nonce, 'rtmedia_' . get_current_user_id() ) ) {
			$remaining_album     = 0;
			$remaining_photos    = 0;
			$remaining_music     = 0;
			$remaining_videos    = 0;
			$remaining_all_media = 0;
			$rtmedia_media       = new RTMediaMedia();
			$rtmedia_media->delete( $media_id );

			// Fetch the remaining media count.
			if ( class_exists( 'RTMediaNav' ) ) {
				global $bp;
				$rtmedia_nav_obj = new RTMediaNav();
				$model           = new RTMediaModel();
				$other_count     = 0;

				if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {

					if ( ! empty( $bp->groups->current_group->id ) ) {
						$counts      = $rtmedia_nav_obj->actual_counts( $bp->groups->current_group->id, 'group' );
						$other_count = $model->get_other_album_count( $bp->groups->current_group->id, 'group' );

					}
				} else {

					if ( function_exists( 'bp_displayed_user_id' ) ) {
						$counts      = $rtmedia_nav_obj->actual_counts( bp_displayed_user_id(), 'profile' );
						$other_count = $model->get_other_album_count( bp_displayed_user_id(), 'profile' );
					}
				}

				$remaining_all_media = ( ! empty( $counts['total']['all'] ) ) ? $counts['total']['all'] : 0;
				$remaining_album     = ( isset( $counts['total']['album'] ) ) ? $counts['total']['album'] + $other_count : 0;
				$remaining_photos    = ( ! empty( $counts['total']['photo'] ) ) ? $counts['total']['photo'] : 0;
				$remaining_videos    = ( ! empty( $counts['total']['video'] ) ) ? $counts['total']['video'] : 0;
				$remaining_music     = ( ! empty( $counts['total']['music'] ) ) ? $counts['total']['music'] : 0;
			}

			wp_send_json_success(
				array(
					'code'            => 'rtmedia-media-deleted',
					'all_media_count' => $remaining_all_media,
					'photos_count'    => $remaining_photos,
					'music_count'     => $remaining_music,
					'videos_count'    => $remaining_videos,
					'albums_count'    => $remaining_album,
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

if ( ! function_exists( 'rtmedia_transcoded_media_added_callback' ) ) {

	/**
	 * Update profile and comment activity content of comment media
	 *
	 * @param int $attachment_id Attachment id.
	 *
	 * @global object $wpdb
	 */
	function rtmedia_transcoded_media_added_callback( $attachment_id ) {
		if ( isset( $attachment_id ) && ! empty( $attachment_id ) ) {

			$job_for = get_post_meta( $attachment_id, '_rt_media_source', true );

			if ( 'rtmedia' === $job_for && class_exists( 'RTMediaModel' ) ) {

				$model = new RTMediaModel();
				$media = $model->get_media( array( 'media_id' => $attachment_id ), 0, 1 );

				if ( isset( $media[0] ) && isset( $media[0]->activity_id ) && ! empty( $media[0]->activity_id ) ) {

					$activity_id = $media[0]->activity_id;
					$media_id    = $media[0]->id;

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

/**
 * Actions before showing comments.
 */
function rtmedia_actions_before_comments_conversation_callback() {
	global $rtmedia_media;

	// check is comment media.
	$comment_media = rtmedia_is_comment_media( rtmedia_id() );

	if ( isset( $rtmedia_media->activity_id ) && ! empty( $rtmedia_media->activity_id ) && function_exists( 'rtmedia_view_conversation_of_media' ) && $comment_media ) {
		rtmedia_view_conversation_of_media( $rtmedia_media->activity_id );
	}
}
add_action( 'rtmedia_actions_before_comments', 'rtmedia_actions_before_comments_conversation_callback', 1000 );

/**
 * Add comment link.
 */
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

/**
 * Runs when activity is add in buddypress
 *
 * @param object $activity_data Activity data object.
 */
function rtmedia_bp_activity_after_save_callback( $activity_data ) {

	// check is  activity reply.
	if ( ! empty( $activity_data ) && 'activity_comment' === $activity_data->type ) {

		$rtmedia_attached_files = filter_input( INPUT_POST, 'rtMedia_attached_files', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		// check if class exits or not and it has any media in it.
		if ( ! empty( $rtmedia_attached_files ) && isset( $activity_data->id ) && is_array( $rtmedia_attached_files ) && ! empty( $rtmedia_attached_files[0] ) && class_exists( 'RTMediaModel' ) ) {

			if ( function_exists( 'rtmedia_get_original_comment_media_content' ) ) {
				// get the original content of media.
				$original_content = rtmedia_get_original_comment_media_content();
				// save the original content in the meta fields.
				bp_activity_update_meta( $activity_data->id, 'bp_activity_text', $original_content );
			}

			$rtmedia_model = new RTMediaModel();
			$rtmedia_model->update(
				array(
					'activity_id' => $activity_data->id,
				),
				array(
					'id' => $rtmedia_attached_files[0],
				)
			);

			$privacy = -1;
			$form_id = filter_input( INPUT_POST, 'form_id', FILTER_SANITIZE_NUMBER_INT );

			$rtm_activity_model = new RTMediaActivityModel();
			$columns            = array(
				'activity_id' => $form_id,
				'blog_id'     => get_current_blog_id(),
			);

			$is_ac_privacy_exist = $rtm_activity_model->get( $columns );

			// changing privacy according to it parent.
			if ( isset( $is_ac_privacy_exist[0] ) && ! empty( $is_ac_privacy_exist[0] ) && isset( $is_ac_privacy_exist[0]->privacy ) ) {
				$privacy = $is_ac_privacy_exist[0]->privacy;
			}

			// Very first privacy entry for this activity.
			if ( $rtm_activity_model->check( $activity_data->id ) ) {
				$status = $rtm_activity_model->update(
					array(
						'privacy'     => $privacy,
						'activity_id' => $activity_data->id,
						'user_id'     => get_current_user_id(),
					),
					array( 'activity_id' => $activity_data->id )
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
add_action( 'bp_activity_after_save', 'rtmedia_bp_activity_after_save_callback', 1000, 1 );

/**
 * Change the BuddyPress activity Comment reply content
 *
 * @param string $content Comment content.
 *
 * @return string
 */
function rtmedia_bp_activity_comment_content_callback( $content ) {

	$new_content = $content;

	$rtmedia_attached_files = filter_input( INPUT_POST, 'rtMedia_attached_files', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	$comment_id             = filter_input( INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT );
	$form_id                = filter_input( INPUT_POST, 'form_id', FILTER_SANITIZE_NUMBER_INT );

	if ( ! empty( $rtmedia_attached_files ) && ! empty( $comment_id ) && ! empty( $form_id ) ) {

		if ( ! empty( $rtmedia_attached_files[0] ) && is_array( $rtmedia_attached_files ) && class_exists( 'RTMediaActivity' ) ) {
			$obj_comment = new RTMediaActivity( $rtmedia_attached_files[0], 0, $content );
			$new_content = $obj_comment->create_activity_html();
		}
	}
	return $new_content;
}
add_action( 'bp_activity_content_before_save', 'rtmedia_bp_activity_comment_content_callback', 1001, 1 );
