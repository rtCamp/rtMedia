<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaBuddyPressActivity
 *
 * @author faishal
 */
class RTMediaBuddyPressActivity {

	function __construct() {
		global $rtmedia;
		if ( 0 !== intval( $rtmedia->options['buddypress_enableOnActivity'] ) ) {
			add_action( 'bp_after_activity_post_form', array( &$this, 'bp_after_activity_post_form' ) );
			add_action( 'bp_activity_posted_update', array( &$this, 'bp_activity_posted_update' ), 99, 3 );

			// manage user's last activity update.
			add_action( 'bp_activity_posted_update', array( &$this, 'manage_user_last_activity_update' ), 999, 3 );
			add_action( 'bp_groups_posted_update', array( &$this, 'bp_groups_posted_update' ), 99, 4 );
		}
		add_action( 'bp_init', array( $this, 'non_threaded_comments' ) );
		add_action( 'bp_activity_comment_posted', array( $this, 'comment_sync' ), 10, 2 );
		add_action( 'bp_activity_delete_comment', array( $this, 'delete_comment_sync' ), 10, 2 );
		add_filter( 'bp_activity_allowed_tags', array( &$this, 'override_allowed_tags' ) );
		add_filter( 'bp_get_activity_parent_content', array( &$this, 'bp_get_activity_parent_content' ) );
		add_action( 'bp_activity_deleted_activities', array( &$this, 'bp_activity_deleted_activities' ) );

		// Filter bp_activity_prefetch_object_data for translatable activity actions
		add_filter( 'bp_activity_prefetch_object_data', array( $this, 'bp_prefetch_activity_object_data' ), 10, 1 );

		// BuddyPress activity for media like action
		if ( isset( $rtmedia->options['buddypress_mediaLikeActivity'] ) && 0 !== intval( $rtmedia->options['buddypress_mediaLikeActivity'] ) ) {
			add_action( 'rtmedia_after_like_media', array( $this, 'activity_after_media_like' ) );
		}

		// BuddyPress activity for media comment action
		if ( isset( $rtmedia->options['buddypress_mediaCommentActivity'] ) && 0 !== intval( $rtmedia->options['buddypress_mediaCommentActivity'] ) ) {
			add_action( 'rtmedia_after_add_comment', array( $this, 'activity_after_media_comment' ) );
			add_action( 'rtmedia_before_remove_comment', array( $this, 'remove_activity_after_media_comment_delete' ) );
		}

		add_filter( 'bp_activity_user_can_delete', array( $this, 'rtm_bp_activity_user_can_delete' ), 10, 2 );

		add_filter( 'bp_activity_permalink_access', array( $this, 'rtm_bp_activity_permalink_access' ) );
		add_action( 'bp_activity_comment_posted', array( $this, 'rtm_check_privacy_for_comments' ), 10, 3 );
	}

	function bp_activity_deleted_activities( $activity_ids_deleted ) {
		//Allo delete activity othe media only of request from activity ajax
		if ( ( is_admin() && '1' == DOING_AJAX ) || ! is_admin() ) {
			$rt_model  = new RTMediaModel();
			$all_media = $rt_model->get( array( 'activity_id' => $activity_ids_deleted ) );
			if ( $all_media ) {
				$media = new RTMediaMedia();
				remove_action( 'bp_activity_deleted_activities', array( &$this, 'bp_activity_deleted_activities' ) );
				foreach ( $all_media as $single_media ) {
					$media->delete( $single_media->id, false, false );
				}
			}
		}
	}

	function bp_get_activity_parent_content( $content ) {
		global $activities_template;

		// Get the ID of the parent activity content
		if ( ! $parent_id = $activities_template->activity->item_id ) {
			return false;
		}

		// Bail if no parent content
		if ( empty( $activities_template->activity_parents[ $parent_id ] ) ) {
			return false;
		}

		// Bail if no action
		if ( empty( $activities_template->activity_parents[ $parent_id ]->action ) ) {
			return false;
		}

		// Content always includes action
		$content = $activities_template->activity_parents[ $parent_id ]->action;

		// Maybe append activity content, if it exists
		if ( ! empty( $activities_template->activity_parents[ $parent_id ]->content ) ) {
			$content .= ' ' . $activities_template->activity_parents[ $parent_id ]->content;
		}

		// Remove the time since content for backwards compatibility
		$content = str_replace( '<span class="time-since">%s</span>', '', $content );

		return $content;
	}

	function delete_comment_sync( $activity_id, $comment_id ) {
		global $wpdb;
		$comment_id = $wpdb->get_var( $wpdb->prepare( "select comment_id from {$wpdb->commentmeta} where meta_key = 'activity_id' and meta_value = %s", $comment_id ) );
		if ( $comment_id ) {
			wp_delete_comment( $comment_id, true );
		}
	}

	function comment_sync( $comment_id, $param ) {
		$default_args   = array( 'user_id' => '', 'comment_author' => '' );
		$param          = wp_parse_args( $param, $default_args );
		$user_id        = $param['user_id'];
		$comment_author = $param['comment_author'];
		if ( ! empty( $user_id ) ) {
			$user_data      = get_userdata( $user_id );
			$comment_author = $user_data->data->user_login;
		}
		$mediamodel = new RTMediaModel();
		$media      = $mediamodel->get( array( 'activity_id' => $param['activity_id'] ) );
		// if there is only single media in activity
		if ( 1 === count( $media ) && isset( $media[0]->media_id ) ) {

			/* has media in comment */
			$rtMedia_attached_files = filter_input( INPUT_POST, 'rtMedia_attached_files', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			/* if the media is not empty */
			if ( is_array( $rtMedia_attached_files ) && ! empty( $rtMedia_attached_files[0] ) && class_exists( 'RTMediaActivity' ) ) {
				/* create new html for comment content */
				$obj_comment = new RTMediaActivity( $rtMedia_attached_files[0], 0, $param['content'] );
				$param['content'] = $obj_comment->create_activity_html( 'comment-media' );
			}

			$media_id = $media[0]->media_id;
			$comment  = new RTMediaComment();
			$id       = $comment->add( array(
				'comment_content' => $param['content'],
				'comment_post_ID' => $media_id,
				'user_id'         => $user_id,
				'comment_author'  => $comment_author,
			) );
			update_comment_meta( $id, 'activity_id', $comment_id );
		}
	}

	function non_threaded_comments() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );
		if ( 'new_activity_comment' === $action ) {
			$activity_id = filter_input( INPUT_POST, 'form_id', FILTER_SANITIZE_NUMBER_INT );
			$act         = new BP_Activity_Activity( $activity_id );

			if ( 'rtmedia_update' === $act->type && isset( $_REQUEST['rtmedia_disable_media_in_commented_media'] ) &&  ! empty( $_REQUEST['rtmedia_disable_media_in_commented_media'] ) ) {
				$_POST['comment_id'] = $activity_id;
			}
		}
	}

	function bp_groups_posted_update( $content, $user_id, $group_id, $activity_id ) {
		$this->bp_activity_posted_update( $content, $user_id, $activity_id );
	}

	function bp_activity_posted_update( $content, $user_id, $activity_id ) {
		global $wpdb, $bp;
		$updated_content = '';

		// hook for rtmedia buddypress before activity posted
		do_action( 'rtmedia_bp_before_activity_posted', $content, $user_id, $activity_id );

		$rtmedia_attached_files = filter_input( INPUT_POST, 'rtMedia_attached_files', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( is_array( $rtmedia_attached_files ) ) {
			$updated_content = $wpdb->get_var( "select content from  {$bp->activity->table_name} where  id= $activity_id limit 1" ); // @codingStandardsIgnoreLine

			$obj_activity = new RTMediaActivity( $rtmedia_attached_files, 0, $updated_content );
			$html_content = $obj_activity->create_activity_html();
			bp_activity_update_meta( $activity_id, 'bp_old_activity_content', $html_content );
			bp_activity_update_meta( $activity_id, 'bp_activity_text', $updated_content );
			$wpdb->update( $bp->activity->table_name, array(
				'type'    => 'rtmedia_update',
				'content' => $html_content,
			), array( 'id' => $activity_id ) );
			$media_obj = new RTMediaModel();
			//Credit faisal : https://gist.github.com/faishal/c4306ae7267fff976465
			$in_str_arr = array_fill( 0, count( $rtmedia_attached_files ), '%d' );
			$in_str     = join( ',', $in_str_arr );
			$sql        = $wpdb->prepare( "update {$media_obj->table_name} set activity_id = %d where blog_id = %d and ", $activity_id, get_current_blog_id() ); // @codingStandardsIgnoreLine
			$form_id_where = $wpdb->prepare( "id IN ($in_str)", $rtmedia_attached_files );
			$sql .= $form_id_where;
			$wpdb->query( $sql );// @codingStandardsIgnoreLine
		}
		// hook for rtmedia buddypress after activity posted
		do_action( 'rtmedia_bp_activity_posted', $updated_content, $user_id, $activity_id );
		$rtmedia_privacy = filter_input( INPUT_POST, 'rtmedia-privacy', FILTER_SANITIZE_NUMBER_INT );
		if ( null !== $rtmedia_privacy ) {
			$privacy = - 1;
			if ( is_rtmedia_privacy_enable() ) {
				if ( is_rtmedia_privacy_user_overide() ) {
					$privacy = $rtmedia_privacy;
				} else {
					$privacy = get_rtmedia_default_privacy();
				}
			}
			bp_activity_update_meta( $activity_id, 'rtmedia_privacy', $privacy );
			// insert/update activity details in rtmedia activity table
			$rtmedia_activity_model = new RTMediaActivityModel();
			if ( ! $rtmedia_activity_model->check( $activity_id ) ) {
				$rtmedia_activity_model->insert( array(
					'activity_id' => $activity_id,
					'user_id'     => $user_id,
					'privacy'     => $privacy,
				) );
			} else {
				$rtmedia_activity_model->update( array(
					'activity_id' => $activity_id,
					'user_id'     => $user_id,
					'privacy'     => $privacy,
				), array( 'activity_id' => $activity_id ) );
			}
		}
	}

	/**
	 * Update `bp_latest_update` user meta with lasted public update.
	 *
	 * @param $content
	 * @param $user_id
	 * @param $activity_id
	 */
	function manage_user_last_activity_update( $content, $user_id, $activity_id ) {
		global $wpdb, $bp;

		// do not proceed if not allowed
		if ( ! apply_filters( 'rtm_manage_user_last_activity_update', true, $activity_id ) ) {
			return;
		}
		$rtm_activity_model = new RTMediaActivityModel();

		$rtm_activity_obj = $rtm_activity_model->get( array( 'activity_id' => $activity_id ) );

		if ( ! empty( $rtm_activity_obj ) ) {
			if ( isset( $rtm_activity_obj[0]->privacy ) && $rtm_activity_obj[0]->privacy > 0 ) {

				$get_columns = array(
					'activity_id' => array(
						'compare' => '<',
						'value'   => $activity_id,
					),
					'user_id'     => $user_id,
					'privacy'     => array(
						'compare' => '<=',
						'value'   => 0,
					),
				);

				// get user's latest public activity update
				$new_last_activity_obj = $rtm_activity_model->get( $get_columns, 0, 1 );

				if ( ! empty( $new_last_activity_obj ) ) {
					// latest public activity id
					$public_activity_id = $new_last_activity_obj[0]->activity_id;

					// latest public activity content
					$activity_content = bp_activity_get_meta( $public_activity_id, 'bp_activity_text' );
					if ( empty( $activity_content ) ) {
						$activity_content = $wpdb->get_var( $wpdb->prepare( "SELECT content FROM {$bp->activity->table_name} WHERE id = %d", $public_activity_id ) ); // @codingStandardsIgnoreLine
					}
					$activity_content = apply_filters( 'bp_activity_latest_update_content', $activity_content, $activity_content );

					// update user's latest update
					bp_update_user_meta( $user_id, 'bp_latest_update', array(
						'id'      => $public_activity_id,
						'content' => $activity_content,
					) );
				}
			}
		}
	}

	function bp_after_activity_post_form() {
		$request_uri = rtm_get_server_var( 'REQUEST_URI', 'FILTER_SANITIZE_URL' );
		$url         = rtmedia_get_upload_url( $request_uri );
		if ( rtmedia_is_uploader_view_allowed( true, 'activity' ) ) {
			$params = array(
				'url'                 => $url,
				'runtimes'            => 'html5,flash,html4',
				'browse_button'       => apply_filters( 'rtmedia_upload_button_id', 'rtmedia-add-media-button-post-update' ),
				// browse button assigned to "Attach Files" Button.
				'container'           => 'rtmedia-whts-new-upload-container',
				'drop_element'        => 'whats-new-textarea',
				// drag-drop area assigned to activity update textarea
				'filters'             => apply_filters( 'rtmedia_plupload_files_filter', array(
					array(
						'title'      => esc_html__( 'Media Files', 'buddypress-media' ),
						'extensions' => get_rtmedia_allowed_upload_type(),
					),
				) ),
				'max_file_size'       => ( wp_max_upload_size() ) / ( 1024 * 1024 ) . 'M',
				'multipart'           => true,
				'urlstream_upload'    => true,
				'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
				'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
				'file_data_name'      => 'rtmedia_file',
				// key passed to $_FILE.
				'multi_selection'     => true,
				'multipart_params'    => apply_filters( 'rtmedia-multi-params', array(
					'redirect'             => 'no',
					'rtmedia_update'       => 'true',
					'action'               => 'wp_handle_upload',
					'_wp_http_referer'     => $request_uri,
					'mode'                 => 'file_upload',
					'rtmedia_upload_nonce' => RTMediaUploadView::upload_nonce_generator( false, true ),
				) ),
				'max_file_size_msg'   => apply_filters( 'rtmedia_plupload_file_size_msg', min( array(
					ini_get( 'upload_max_filesize' ),
					ini_get( 'post_max_size' ),
				) ) ),
			);

			$params = apply_filters( 'rtmedia_modify_upload_params', $params );
			wp_enqueue_script( 'rtmedia-backbone', false, '', false, true );
			$is_album        = is_rtmedia_album() ? true : false;
			$is_edit_allowed = is_rtmedia_edit_allowed() ? true : false;
			wp_localize_script( 'rtmedia-backbone', 'is_album', $is_album );
			wp_localize_script( 'rtmedia-backbone', 'is_edit_allowed', $is_edit_allowed );
			wp_localize_script( 'rtmedia-backbone', 'rtMedia_update_plupload_config', $params );

			$upload_view = new RTMediaUploadView( array( 'activity' => true ) );
			$upload_view->render( 'uploader' );
		} else {
			echo "<div class='rtmedia-upload-not-allowed'>" . wp_kses( apply_filters( 'rtmedia_upload_not_allowed_message', esc_html__( 'You are not allowed to upload/attach media.', 'buddypress-media' ), 'activity' ), RTMediaUpload::$wp_kses_allowed_tags ) . '</div>';
		}
	}

	function override_allowed_tags( $activity_allowedtags ) {

		$activity_allowedtags['video']             	= array();
		$activity_allowedtags['video']['id']       	= array();
		$activity_allowedtags['video']['class']    	= array();
		$activity_allowedtags['video']['src']      	= array();
		$activity_allowedtags['video']['controls'] 	= array();
		$activity_allowedtags['video']['preload']  	= array();
		$activity_allowedtags['video']['alt']      	= array();
		$activity_allowedtags['video']['title']    	= array();
		$activity_allowedtags['video']['width']    	= array();
		$activity_allowedtags['video']['height']   	= array();
		$activity_allowedtags['video']['poster']   	= array();
		$activity_allowedtags['source'] 		   	= array();
		$activity_allowedtags['source']['type']	   	= array();
		$activity_allowedtags['source']['src'] 	   	= array();
		$activity_allowedtags['audio']             	= array();
		$activity_allowedtags['audio']['id']       	= array();
		$activity_allowedtags['audio']['class']    	= array();
		$activity_allowedtags['audio']['src']      	= array();
		$activity_allowedtags['audio']['controls'] 	= array();
		$activity_allowedtags['audio']['preload']  	= array();
		$activity_allowedtags['audio']['alt']      	= array();
		$activity_allowedtags['audio']['title']    	= array();
		$activity_allowedtags['audio']['width']    	= array();
		$activity_allowedtags['audio']['poster']   	= array();

		if ( ! isset( $activity_allowedtags['div'] ) ) {
			$activity_allowedtags['div'] = array();
		}

		$activity_allowedtags['div']['id']    = array();
		$activity_allowedtags['div']['class'] = array();

		if ( ! isset( $activity_allowedtags['a'] ) ) {
			$activity_allowedtags['a'] = array();
		}

		$activity_allowedtags['a']['title'] = array();
		$activity_allowedtags['a']['href']  = array();

		if ( ! isset( $activity_allowedtags['ul'] ) ) {
			$activity_allowedtags['ul'] = array();
		}

		$activity_allowedtags['ul']['class'] = array();

		if ( ! isset( $activity_allowedtags['li'] ) ) {
			$activity_allowedtags['li'] = array();
		}
		$activity_allowedtags['li']['class'] = array();

		return $activity_allowedtags;
	}

	/**
	 * To add dynamic activity actions for translation of activity items
	 *
	 * @param $activities
	 */
	function bp_prefetch_activity_object_data( $activities ) {
		// If activities array is empty then return
		if ( empty( $activities ) ) {
			return;
		}

		// To store activity_id
		$activity_ids         = array();
		$activity_index_array = array();

		foreach ( $activities as $i => $activity ) {
			// Checking if activity_type is of rtmedia and component must be profile
			if ( 'rtmedia_update' === $activity->type && 'profile' === $activity->component ) {
				// Storing activity_id
				$activity_ids[] = $activity->id;
				// Storing index of activity from activities array
				$activity_index_array[] = $i;
			}
		}

		// Checking if media is linked with any of activity
		if ( ! empty( $activity_ids ) ) {
			$rtmedia_model = new RTMediaModel();

			// Where condition array to get media using activity_id from rtm_media table
			$rtmedia_media_where_array                = array();
			$rtmedia_media_where_array['activity_id'] = array(
				'compare' => 'IN',
				'value'   => $activity_ids,
			);
			$rtmedia_media_query                      = $rtmedia_model->get( $rtmedia_media_where_array );

			// Array to store media_type in simplified manner with activity_id as key
			$rtmedia_media_type_array = array();
			for ( $i = 0; $i < count( $rtmedia_media_query ); $i ++ ) {
				// Storing media_type of uploaded media to check whether all media are of same type or different and key is activity_id
				// Making activity_id array because there might be more then 1 media linked with activity
				if ( ! isset( $rtmedia_media_type_array[ $rtmedia_media_query[ $i ]->activity_id ] ) || ! is_array( $rtmedia_media_type_array[ $rtmedia_media_query[ $i ]->activity_id ] ) ) {
					$rtmedia_media_type_array[ $rtmedia_media_query[ $i ]->activity_id ] = array();
				}

				array_push( $rtmedia_media_type_array[ $rtmedia_media_query[ $i ]->activity_id ], $rtmedia_media_query[ $i ]->media_type );
			}

			// Updating action
			for ( $a = 0; $a < count( $activity_ids ); $a ++ ) {
				// Getting index of activity which is being updated
				$index = $activity_index_array[ $a ];

				// Generating user_link with display name.
				$user_link = '<a href="' . esc_url( $activities[ $index ]->primary_link ) . '">' . esc_html( $activities[ $index ]->display_name ) . '</a>';

				if ( isset( $rtmedia_media_type_array[ $activities[ $index ]->id ] ) ) {
					// Counting media linked with activity
					$count = count( $rtmedia_media_type_array[ $activities[ $index ]->id ] );
					// Getting constant with single label or plural label
					$media_const = 'RTMEDIA_' . strtoupper( $rtmedia_media_type_array[ $activities[ $index ]->id ][0] );
					if ( $count > 1 ) {
						$media_const .= '_PLURAL';
					}
					$media_const .= '_LABEL';
					if ( defined( $media_const ) ) {
						$media_str = constant( $media_const );
					} else {
						$media_str = RTMEDIA_MEDIA_SLUG;
					}

					$action = '';
					$user   = get_userdata( $activities[ $index ]->user_id );
					// Updating activity based on count
					if ( 1 === $count ) {
						$action = sprintf( esc_html__( '%s added a %s', 'buddypress-media' ), $user_link, $media_str );
					} else {
						// Checking all the media linked with activity are of same type
						if ( isset( $rtmedia_media_type_array[ $activities[ $index ]->id ] )
							&& ! empty( $rtmedia_media_type_array[ $activities[ $index ]->id ] )
							&& count( array_unique( $rtmedia_media_type_array[ $activities[ $index ]->id ] ) ) === 1
						) {
							$action = sprintf( esc_html__( '%s added %d %s', 'buddypress-media' ), $user_link, $count, $media_str );
						} else {
							$action = sprintf( esc_html__( '%s added %d %s', 'buddypress-media' ), $user_link, $count, RTMEDIA_MEDIA_SLUG );
						}
					}

					$action                       = apply_filters( 'rtmedia_bp_activity_action_text', $action, $user_link, $count, $user, $rtmedia_media_type_array[ $activities[ $index ]->id ][0], $activities[ $index ]->id );
					$activities[ $index ]->action = $action;
				}
			}
		}

		return $activities;
	}

	/**
	 * Create BP activity when user like and delete associated activity when user remove like.
	 *
	 * @param $obj RTMediaLike
	 */
	function activity_after_media_like( $obj ) {
		if ( class_exists( 'BuddyPress' ) ) {
			global $rtmedia_points_media_id;
			if ( is_a( $obj, 'RTMediaLike' ) && isset( $obj->action_query->id ) ) {
				$media_id = $obj->action_query->id;
			} elseif ( ! empty( $rtmedia_points_media_id ) ) {
				$media_id = $rtmedia_points_media_id;
			} else {
				$media_id = false;
			}

			$media_obj = $obj->media;

			// Proceed only if we have media to process.
			if ( false !== $media_id && ( 'profile' === $media_obj->context || 'group' === $media_obj->context ) ) {

				$user_id = $obj->interactor;

				// If $obj->increase is true than request is to like the media.
				if ( $obj->increase ) {

					// Create activity on media like
					$user     = get_userdata( $user_id );
					$username = '<a href="' . esc_url( get_rtmedia_user_link( $user_id ) ) . '">' . esc_html( $user->display_name ) . '</a>';

					$media_author = $obj->owner;

					$primary_link = get_rtmedia_permalink( $media_id );

					$media_const = 'RTMEDIA_' . strtoupper( $obj->media->media_type ) . '_LABEL';
					$media_str   = '<a href="' . esc_url( $primary_link ) . '">' . esc_html( constant( $media_const ) ) . '</a>';

					if ( 'group' === $media_obj->context ) {
						$group_data = groups_get_group( array( 'group_id' => $media_obj->context_id ) );
						$group_name = '<a href="' . esc_url( bp_get_group_permalink( $group_data ) ) . '">' . esc_html( $group_data->name ) . '</a>';
						$action     = sprintf( esc_html__( '%1$s liked a %2$s in the group %3$s', 'buddypress-media' ), $username, $media_str, $group_name );
					} else {
						if ( $user_id === $media_author ) {
							$action = sprintf( esc_html__( '%1$s liked their %2$s', 'buddypress-media' ), $username, $media_str );
						} else {
							$media_author_data = get_userdata( $media_author );
							$media_author_name = '<a href="' . esc_url( get_rtmedia_user_link( $media_author ) ) . '">' . esc_html( $media_author_data->display_name ) . '</a>';
							$action            = sprintf( esc_html__( '%1$s liked %2$s\'s %3$s', 'buddypress-media' ), $username, $media_author_name, $media_str );
						}
					}

					$action       = apply_filters( 'rtm_bp_like_activity_action', $action, $media_id, $user_id );
					$primary_link = get_rtmedia_permalink( $media_id );

					// generate activity arguments.
					$activity_args = array(
							'user_id'      => $user_id,
							'action'       => $action,
							'type'         => 'rtmedia_like_activity',
							'primary_link' => $primary_link,
							'item_id'      => $media_id,
							'secondary_item_id'      => $media_id, // Used for when deleting media when it's enter in group not used when media is add in the main activity
					);

					// set activity component
					if ( 'group' === $media_obj->context || 'profile' === $media_obj->context ) {
						$activity_args['component'] = $media_obj->context;
						if ( 'group' === $media_obj->context ) {
							$activity_args['component'] = 'groups';
							$activity_args['item_id']   = $media_obj->context_id;
						}
					}

					// add BP activity
					$activity_id = bp_activity_add( $activity_args );

					// add privacy for like activity
					if( class_exists( 'RTMediaActivityModel' ) && is_rtmedia_privacy_enable() && isset( $media_obj->activity_id ) ){
						$rtmedia_activity_model = new RTMediaActivityModel();
						$rtmedia_activity_model->set_privacy_for_rtmedia_activity( $media_obj->activity_id, $activity_id , $user_id );
					}


					// Store activity id into user meta for reference
					//todo user_attribute
					update_user_meta( $user_id, 'rtm-bp-media-like-activity-' . $media_id, $activity_id );
				} else {

					$meta_key = 'rtm-bp-media-like-activity-' . $media_id;
					// Delete activity when user remove his like.
					//todo user_attribute
					$activity_id = get_user_meta( $user_id, $meta_key, true );

					if ( ! empty( $activity_id ) ) {
						if ( bp_activity_delete( array( 'id' => $activity_id ) ) ) {
							//todo user_attribute
							delete_user_meta( $user_id, $meta_key );
						}
					}
				}
			}
		}
	}

	/**
	 * Create BuddyPress activity when user comment on media
	 *
	 * @param $params array
	 */
	function activity_after_media_comment( $params ) {
		if ( class_exists( 'BuddyPress' ) ) {
			if ( isset( $params['comment_post_ID'] ) ) {

				// get media details
				$media_model = new RTMediaModel();
				$media_obj   = $media_model->get( array( 'media_id' => $params['comment_post_ID'] ) );
				$media_obj   = $media_obj[0];

				// only proceed if corresponding media is exist.
				if ( ! empty( $media_obj ) && ( 'profile' === $media_obj->context || 'group' === $media_obj->context ) ) {

					$media_id = $media_obj->id;

					$user_id  = $params['user_id'];
					$user     = get_userdata( $user_id );
					$username = '<a href="' . esc_url( get_rtmedia_user_link( $user_id ) ) . '">' . esc_html( $user->display_name ) . '</a>';

					$primary_link = get_rtmedia_permalink( $media_id );

					$media_const = 'RTMEDIA_' . strtoupper( $media_obj->media_type ) . '_LABEL';
					$media_str   = '<a href="' . esc_url( $primary_link ) . '">' . constant( $media_const ) . '</a>';

					$media_author = $media_obj->media_author;

					if ( 'group' === $media_obj->context ) {
						$group_data = groups_get_group( array( 'group_id' => $media_obj->context_id ) );
						$group_name = '<a href="' . esc_url( bp_get_group_permalink( $group_data ) ) . '">' . esc_html( $group_data->name ) . '</a>';
						$action     = sprintf( esc_html__( '%1$s commented on a %2$s in the group %3$s', 'buddypress-media' ), $username, $media_str, $group_name );
					} else {
						if ( $user_id === $media_author ) {
							$action = sprintf( esc_html__( '%1$s commented on their %2$s', 'buddypress-media' ), $username, $media_str );
						} else {
							$media_author_data = get_userdata( $media_author );
							$media_author_name = '<a href="' . esc_url( get_rtmedia_user_link( $media_author ) ) . '">' . esc_html( $media_author_data->display_name ) . '</a>';
							$action            = sprintf( esc_html__( '%1$s commented on %2$s\'s %3$s', 'buddypress-media' ), $username, $media_author_name, $media_str );
						}
					}

					$activity_content = $params['comment_content'];
					$comment_media = false;
					$comment_media_id = false;

					/* if activity is add from comment media  */
				    if( isset( $_REQUEST['comment_content'] ) || isset( $_REQUEST['action'] ) ){
				    	if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'new_activity_comment' ){

				    		remove_action( 'bp_activity_content_before_save', 'rtmedia_bp_activity_comment_content_callback', 1001, 1 );
				    		/* comment content */
					        $comment_content = $_REQUEST['content'];
				    	}elseif ( isset( $_REQUEST['comment_content'] ) ) {
					        /* comment content */
					        $comment_content = $_REQUEST['comment_content'];
				    	}

				        /* is comment is empty then add content content space */
			            if( strstr($comment_content, 'nbsp') ){
			                $comment_content = "&nbsp;";
			            }


				        /* if comment has comment media then create new html for it */
				        if ( isset( $_REQUEST['rtMedia_attached_files'] ) ) {
				            $rtMedia_attached_files = filter_input( INPUT_POST, 'rtMedia_attached_files', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

				            /* check media should be in array format and is not empty to */
				            if( class_exists( 'RTMediaActivity' )  && is_array( $rtMedia_attached_files ) && ! empty( $rtMedia_attached_files ) ){
				            	$comment_media = true;
				            	$comment_media_id = $rtMedia_attached_files[0];
			                    $obj_comment = new RTMediaActivity( $rtMedia_attached_files[0], 0, $comment_content );
			                	$comment_content = $obj_comment->create_activity_html();
				            }
				        }

				        /* add the new content to the activity */
				        $activity_content = $comment_content;
				    }


					$wp_comment_id   = $params['comment_id'];

					// prepare activity arguments
					$activity_args = array(
							'user_id'           => $user_id,
							'action'            => $action,
							'content'           => $activity_content,
							'type'              => 'rtmedia_comment_activity',
							'primary_link'      => $primary_link,
							'item_id'           => $media_id,
							'secondary_item_id' => $wp_comment_id,
					);

					// set activity component
					if ( 'group' === $media_obj->context || 'profile' === $media_obj->context ) {
						$activity_args['component'] = $media_obj->context;
						if ( 'group' === $media_obj->context ) {
							$activity_args['component'] = 'groups';
							$activity_args['item_id']   = $media_obj->context_id;
						}
					}

					// create BuddyPress activity
					$activity_id = bp_activity_add( $activity_args );

					/* save the profile activity id in the media meta */
					if( ! empty( $comment_media ) && ! empty( $comment_media_id ) && ! empty( $activity_id ) ){
						add_rtmedia_meta( $comment_media_id, 'rtmedia_comment_media_profile_id', $activity_id );
					}

					// add privacy for like activity
					if( class_exists( 'RTMediaActivityModel' ) && is_rtmedia_privacy_enable() && isset( $media_obj->activity_id ) ){
						$rtmedia_activity_model = new RTMediaActivityModel();
						$rtmedia_activity_model->set_privacy_for_rtmedia_activity( $media_obj->activity_id, $activity_id , $user_id );
					}

					// Store activity id into user meta for reference
					//todo user_attribute
					update_user_meta( $user_id, 'rtm-bp-media-comment-activity-' . $media_id . '-' . $wp_comment_id, $activity_id );

					if( function_exists( 'rtmedia_get_original_comment_media_content' ) ){
						/* get the original content of media */
						$original_content = rtmedia_get_original_comment_media_content();
						/* save the original content in the meta fields */
						bp_activity_update_meta( $activity_id, 'bp_activity_text', $original_content );
						// bp_activity_update_meta( $activity_id, 'bp_old_activity_content', $original_content );
					}
				}
			}
		}
	}

	/**
	 * Remove activity when comment on media is deleted
	 *
	 * @param $comment_id
	 */
	function remove_activity_after_media_comment_delete( $comment_id ) {
		if ( ! empty( $comment_id ) ) {

			// get comment details from comment id
			$comment = get_comment( $comment_id );
			$user_id = $comment->user_id;

			if ( isset( $comment->comment_post_ID ) && isset( $comment->user_id ) ) {
				$model     = new RTMediaModel();
				$media_obj = $model->get( array( 'media_id' => $comment->comment_post_ID ) );
				$media_obj = $media_obj[0];

				if ( ! empty( $media_obj ) ) {
					$meta_key = 'rtm-bp-media-comment-activity-' . $media_obj->id . '-' . $comment_id;

					// Delete activity when user remove his comment.
					//todo user_attribute
					$activity_id = get_user_meta( $user_id, $meta_key, true );

					if ( ! empty( $activity_id ) ) {
						if ( bp_activity_delete( array( 'id' => $activity_id ) ) ) {
							//todo user_attribute
							delete_user_meta( $user_id, $meta_key );
						}
					}
				}
			}
		}
	}

	/**
	 * To check whether user can delete the activity or not
	 *
	 * @access	public
	 *
	 * @since	4.0.2
	 *
	 * @param	bool	$can_delete	Whether the user can delete the item.
	 * @param	object	$activity	Current activity item object.
	 *
	 * @return	bool	$can_delete
	 */
	public function rtm_bp_activity_user_can_delete( $can_delete, $activity ) {

		if ( isset( $activity->user_id ) && ( intval( $activity->user_id ) === intval( bp_loggedin_user_id() ) ) ) {
			$can_delete = true;
		}

		return $can_delete;

	}

	/**
	 * To check user has access to view single activity
	 *
	 * @access	public
	 *
	 * @since	4.0.2
	 *
	 * @param	bool 	$args
	 *
	 * @return 	bool	$has_access
	 */
	public function rtm_bp_activity_permalink_access( $args ) {

		$bp = buddypress();

		// Get the activity details.
		$activity = bp_activity_get_specific( array( 'activity_ids' => bp_current_action(), 'show_hidden' => true, 'spam' => 'ham_only' ) );

		// 404 if activity does not exist
		if ( empty( $activity['activities'][0] ) || bp_action_variables() ) {
			bp_do_404();

			return;
		} else {
			$activity = $activity['activities'][0];
		}

		// Default access is true.
		$has_access = true;

		// If activity is from a group, do an extra cap check.
		if ( isset( $bp->groups->id ) && $activity->component == $bp->groups->id ) {
			// Activity is from a group, but groups is currently disabled.
			if ( ! bp_is_active( 'groups' ) ) {
				bp_do_404();

				return;
			}

			// Check to see if the group is not public, if so, check the
			// user has access to see this activity.
			if ( $group = groups_get_group( array( 'group_id' => $activity->item_id ) ) ) {
				// Group is not public.
				if ( 'public' != $group->status ) {
					// User is not a member of group.
					if ( ! groups_is_user_member( bp_loggedin_user_id(), $group->id ) ) {
						$has_access = false;
					}
				}
			}
		}

		// If activity author does not match displayed user, block access.
		if ( true === $has_access && intval( bp_displayed_user_id() ) !== intval( $activity->user_id ) ) {
			$has_access = false;
		}

		return $has_access;

	}

	/**
	 * Makes the comments hidden (private) if the parent comment's
	 * privacy is set to private.
	 *
	 * @param string $comment_id Activity id of the comment.
	 * @param array  $r          Array of arguments.
	 */
	public function rtm_check_privacy_for_comments( $comment_id, $r ) {
		global $wpdb;

		if ( empty( $r ) || empty( $comment_id ) || ( ! is_array( $r ) ) ) {
			return;
		}

		$db_prefix   = $wpdb->get_blog_prefix();
		$table_name  = 'rt_rtm_activity';
		$activity_id = $r['activity_id'];
		$user_id     = $r['user_id'];
		$privacy_id  = bp_activity_get_meta( $activity_id, 'rtmedia_privacy' );
		$blog_id     = get_current_blog_id();

		if ( '60' === $privacy_id ) {
			$row_values_rtm_media = array(
				'activity_id' => $comment_id,
				'user_id'     => $user_id,
				'privacy'     => $privacy_id,
				'blog_id'     => $blog_id,
			);

			$wpdb->insert(
				$db_prefix . $table_name,
				$row_values_rtm_media
			);

			$table_name = 'bp_activity_meta';
			$row_values_activity_meta = array(
				'id'          => '',
				'activity_id' => $comment_id,
				'meta_key'    => 'rtmedia_privacy',
				'meta_value'  => 60,
			);

			$wpdb->insert(
				$db_prefix . $table_name,
				$row_values_activity_meta
			);
		}
	}
}
