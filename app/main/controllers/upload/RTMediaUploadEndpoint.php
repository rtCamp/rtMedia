<?php

/**
 * Description of RTMediaUploadEndpoint
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class RTMediaUploadEndpoint {

	public $upload;

	/**
	 *
	 */
	public function __construct() {
		add_action( 'rtmedia_upload_redirect', array( $this, 'template_redirect' ) );
	}

	/**
	 *
	 */
	// todo refactor below function so it takes param also and use if passed else use POST request
	function template_redirect( $create_activity = true ) {
		ob_start();
		if ( ! count( $_POST ) ) { // @codingStandardsIgnoreLine
			include get_404_template();
		} else {

			$nonce        = $mode = '';
			$_activity_id = - 1;
			if ( isset( $_REQUEST['rtmedia_upload_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_REQUEST['rtmedia_upload_nonce'] ) );
			}
			if ( isset( $_REQUEST['mode'] ) ) {
				$mode = sanitize_text_field( wp_unslash( $_REQUEST['mode'] ) );
			}

			if ( isset( $_REQUEST['activity_id'] ) ) {
				$_activity_id = sanitize_text_field( $_REQUEST['activity_id'] );
			}

			if ( isset( $_REQUEST['redirection'] ) ) {
				$_redirection = sanitize_text_field( $_REQUEST['redirection'] );
			}

			if ( isset( $_REQUEST['redirect'] ) ) {
				$_redirect = sanitize_text_field( $_REQUEST['redirect'] );
			}

			$_redirect_url = filter_input( INPUT_POST, 'redirect', FILTER_SANITIZE_NUMBER_INT );
			$rtupload      = false;
			$activity_id   = - 1;
			$redirect_url  = '';
			if ( wp_verify_nonce( $nonce, 'rtmedia_upload_nonce' ) ) {
				$model = new RTMediaUploadModel();
				do_action( 'rtmedia_upload_set_post_object' );
				$this->upload = $model->set_post_object();
				if ( - 1 !== $_activity_id ) {
					$this->upload['activity_id'] = $_activity_id;
					$activity_id                 = $_activity_id;

				}

				//if media upload is being made for a group, identify the group privacy and set media privacy accordingly
				if ( isset( $this->upload['context'] ) && isset( $this->upload['context_id'] ) && 'group' === $this->upload['context'] && function_exists( 'groups_get_group' ) ) {

					$group = groups_get_group( array( 'group_id' => $this->upload['context_id'] ) );
					if ( isset( $group->status ) && 'public' !== $group->status ) {
						// if group is not public, then set media privacy as 20, so only the group members can see the images
						$this->upload['privacy'] = '20';
					} else {
						// if group is public, then set media privacy as 0
						$this->upload['privacy'] = '0';
					}
				}

				$comment_media = false;
				// if media is add in from the comment media section
				if ( isset( $this->upload['comment_media_activity_id'] ) && ! empty( $this->upload['comment_media_activity_id'] ) ) {
					// if group is public, then set media privacy as 0
					global $rtmedia;
					$privacy = '0';
					if ( isset( $rtmedia->options['privacy_enabled'] ) && isset( $rtmedia->options['privacy_default'] ) ) {
						$privacy = $rtmedia->options['privacy_default'];
					}

					if ( class_exists( 'RTMediaAlbum' ) ) {
						$album_id = RTMediaAlbum::get_default();
					} else {
						$album = rtmedia_get_site_option( 'rtmedia-global-albums' );
						$album_id = $album_id = $album[0];
					}

					$current_media_id = preg_replace( '/[^0-9]/', '', $this->upload['comment_media_activity_id'] );
					$current_media_type = preg_replace( '/[^a-z]/', '', $this->upload['comment_media_activity_id'] );

					if ( $current_media_id ) {
						$comment_media = true;
						$context = 'profile';
						$context_id = get_current_user_id();

						$media_obj    = new RTMediaMedia();

						if ( 'rtmedia' == $current_media_type ) {
							/* search from media id*/
							$media = $media_obj->model->get( array( 'id' => $current_media_id ) );
						} else {
							/* search from activity id*/
							$media = $media_obj->model->get( array( 'activity_id' => $current_media_id ) );
						}

						if ( ! empty( $media[0] ) && $media[0]->album_id ) {
							$album_id = $media[0]->album_id;
							$privacy = $media[0]->privacy;
							$context = $media[0]->context;
							$context_id = $media[0]->context_id;
						} else {
							/* search from the BuddyPress Table */
							$media = bp_activity_get_specific( array( 'activity_ids' => $current_media_id ) );

							if ( isset( $media['activities'][0]->component ) ) {
								if ( $media['activities'][0]->component != 'activity' ) {
									$context = $media['activities'][0]->component;
								}
							}

							if ( isset( $media['activities'][0]->item_id ) ) {
								$context_id = $media['activities'][0]->item_id;
							}
						}

						$this->upload['album_id'] = $album_id;
						$this->upload['privacy'] = $privacy;

						if ( 0 == strrpos( $context , 'comment-media' ) ) {
							$this->upload['context'] = 'comment-media';
						}

						$this->upload['context_id'] = $context_id;

					}// End if().

				}// End if().

				$this->upload = apply_filters( 'rtmedia_media_param_before_upload', $this->upload );
				$rtupload     = new RTMediaUpload( $this->upload );

				if ( $comment_media ) {
					add_rtmedia_meta( $rtupload->media_ids[0], 'rtmedia_comment_media', true );

					if ( isset( $media[0]->id ) ) {
						// add in comment media the parent media id
						add_rtmedia_meta( $rtupload->media_ids[0], 'rtmedia_comment_media_id', $media[0]->id );

						// add in parent media the comment media id
						$get_rtmedia_meta = array();
						$get_rtmedia_meta_old = get_rtmedia_meta( $media[0]->id, 'has_comment_media' );
						if ( is_array( $get_rtmedia_meta_old ) ) {
							foreach ( $get_rtmedia_meta_old as $value ) {
								$get_rtmedia_meta[] = $value;
							}
						}
						$get_rtmedia_meta[] = $rtupload->media_ids[0];
						update_rtmedia_meta( $media[0]->id, 'has_comment_media', $get_rtmedia_meta );
					}

					if ( ! empty( $current_media_id ) ) {
						add_rtmedia_meta( $rtupload->media_ids[0], 'rtmedia_comment_media_parent', $current_media_id );
					}

					if ( isset( $current_media_type ) ) {
						add_rtmedia_meta( $rtupload->media_ids[0], 'rtmedia_comment_media_type', $current_media_type );
					}
				}

				$media_obj    = new RTMediaMedia();
				$media        = $media_obj->model->get( array( 'id' => $rtupload->media_ids[0] ) );
				$rtmedia_nav  = new RTMediaNav();
				$perma_link   = '';
				if ( isset( $media ) && count( $media ) > 0 ) {
					$perma_link = get_rtmedia_permalink( $media[0]->id );
					if ( 'photo' === $media[0]->media_type ) {
						$thumb_image = rtmedia_image( 'rt_media_thumbnail', $rtupload->media_ids[0], false );
					} elseif ( 'music' === $media[0]->media_type ) {
						$thumb_image = $media[0]->cover_art;
					} else {
						$thumb_image = '';
					}

					if ( 'group' === $media[0]->context ) {
						$rtmedia_nav->refresh_counts( $media[0]->context_id, array(
							'context'    => $media[0]->context,
							'context_id' => $media[0]->context_id,
						) );
					} else {
						$rtmedia_nav->refresh_counts( $media[0]->media_author, array(
							'context'      => 'profile',
							'media_author' => $media[0]->media_author,
						) );
					}
					if ( false !== $create_activity && class_exists( 'BuddyPress' ) && bp_is_active( 'activity' ) ) {
						$allow_single_activity = apply_filters( 'rtmedia_media_single_activity', false );

						// Following will not apply to activity uploads. For first time activity won't be generated.
						// Create activity first and pass activity id in response.

						// todo fixme rtmedia_media_single_activity filter. It will create 2 activity with same media if uploaded from activity page.

						$_rtmedia_update = filter_input( INPUT_POST, 'rtmedia_update', FILTER_SANITIZE_STRING );
						if ( ( -1 === intval( $activity_id ) && ( ! ( isset( $_rtmedia_update ) && 'true' === $_rtmedia_update ) ) ) || $allow_single_activity ) {
							$activity_id = $media_obj->insert_activity( $media[0]->media_id, $media[0] );
						} else {
							$media_obj->model->update( array( 'activity_id' => $activity_id ), array( 'id' => $rtupload->media_ids[0] ) );
							//
							$same_medias = $media_obj->model->get( array( 'activity_id' => $activity_id ) );

							$update_activity_media = array();
							foreach ( $same_medias as $a_media ) {
								$update_activity_media[] = $a_media->id;
							}
							$privacy = filter_input( INPUT_POST, 'privacy', FILTER_SANITIZE_NUMBER_INT );
							if ( empty( $privacy ) ) {
								$privacy = 0;
							}
							$obj_activity = new RTMediaActivity( $update_activity_media, $privacy, false );
							global $wpdb, $bp;
							$user     = get_userdata( $same_medias[0]->media_author );
							$username = '<a href="' . esc_url( get_rtmedia_user_link( $same_medias[0]->media_author ) ) . '">' . esc_html( $user->display_name ) . '</a>';
							$action   = sprintf( esc_html__( '%s added %d %s', 'buddypress-media' ), $username, count( $same_medias ), RTMEDIA_MEDIA_SLUG );
							$action   = apply_filters( 'rtmedia_buddypress_action_text_fitler_multiple_media', $action, $username, count( $same_medias ), $user->display_name );
							$wpdb->update( $bp->activity->table_name, array(
								'type'    => 'rtmedia_update',
								'content' => $obj_activity->create_activity_html(),
								'action'  => $action,
							), array( 'id' => $activity_id ) );
						}

						// update group last active
						if ( 'group' === $media[0]->context ) {
							RTMediaGroup::update_last_active( $media[0]->context_id );
						}
					}
				}
				if ( isset( $this->upload['rtmedia_simple_file_upload'] ) && true == $this->upload['rtmedia_simple_file_upload'] ) {
					if ( isset( $media ) && count( $media ) > 0 ) {
						if ( ( ( isset( $_redirection ) && 'true' === $_redirection ) || ( isset( $_redirect ) && 'no' !== $_redirect ) ) &&
							isset( $_redirect_url ) )  {
							if ( intval( $_redirect_url ) > 1 ) {
								//bulkurl
								if ( 'group' === $media[0]->context ) {
									$redirect_url = trailingslashit( get_rtmedia_group_link( $media[0]->context_id ) ) . RTMEDIA_MEDIA_SLUG;
								} else {
									$redirect_url = trailingslashit( get_rtmedia_user_link( $media[0]->media_author ) ) . RTMEDIA_MEDIA_SLUG;
								}
							} else {
								$redirect_url = get_rtmedia_permalink( $media[0]->id );
							}
							$redirect_url = apply_filters( 'rtmedia_simple_file_upload_redirect_url_filter', $redirect_url );
							wp_safe_redirect( esc_url_raw( $redirect_url ) );
							die();
						}

						return $media;
					}

					return false;
				}
			}

			$redirect_url = '';
			if ( ( ( isset( $_redirection ) && 'true' === $_redirection ) || ( isset( $_redirect ) && 'no' !== $_redirect ) ) &&
				isset( $_redirect_url ) && is_numeric( $_redirect_url ) ) {

				if ( intval( $_redirect_url ) > 1 ) {
					//bulkurl
					if ( 'group' === $media[0]->context ) {
						$redirect_url = trailingslashit( get_rtmedia_group_link( $media[0]->context_id ) ) . RTMEDIA_MEDIA_SLUG;
					} else {
						$redirect_url = trailingslashit( get_rtmedia_user_link( $media[0]->media_author ) ) . RTMEDIA_MEDIA_SLUG;
					}
				} else {
					$redirect_url = get_rtmedia_permalink( $media[0]->id );
				}
			}
			// Ha ha ha
			ob_end_clean();
			//check for simpe
			$rtmedia_update = filter_input( INPUT_POST, 'rtmedia_update', FILTER_SANITIZE_STRING );
			$_user_agent    = rtm_get_server_var( 'HTTP_USER_AGENT', 'FILTER_SANITIZE_STRING' );
			/**
			 * if(redirect)
			 *
			 */
			if ( ! empty( $rtmedia_update ) && 'true' === $rtmedia_update ) {
				if ( preg_match( '/(?i)msie [1-9]/', $_user_agent ) ) {  // if IE(<=9) set content type = text/plain
					header( 'Content-type: text/plain' );
				} else {
					header( 'Content-type: application/json' );
				}
				echo wp_json_encode( $rtupload->media_ids );
			} else {
				// Media Upload Case - on album/post/profile/group
				if ( isset( $media[0] ) ) {
					$data = array(
						'media_id'     => $media[0]->id,
						'activity_id'  => $activity_id,
						'redirect_url' => $redirect_url,
						'permalink'    => $perma_link,
						'cover_art'    => $thumb_image,
					);
				} else {
					$data = array();
				}
				if ( preg_match( '/(?i)msie [1-9]/', $_user_agent ) ) {  // if IE(<=9) set content type = text/plain
					header( 'Content-type: text/plain' );
				} else {
					header( 'Content-type: application/json' );
				}
				echo wp_json_encode( apply_filters( 'rtmedia_upload_endpoint_response', $data ) );
			}

			die();
		}
	}
}
