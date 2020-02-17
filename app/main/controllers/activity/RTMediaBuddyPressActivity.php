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

			/**
			 * Filter to disable bp_activity_truncate_entry override function.
			 * 
			 * @param boolean By default its enabled.
			 */
			if ( apply_filters( 'rtmedia_disable_truncate_entry_override', true ) ) {
				// Code to show media with read more option.
				add_filter( 'bp_activity_truncate_entry', array( $this, 'bp_activity_truncate_entry' ), 10, 3 );
			}
		}
		add_action( 'bp_init', array( $this, 'non_threaded_comments' ) );
		add_action( 'bp_activity_comment_posted', array( $this, 'comment_sync' ), 10, 2 );
		add_action( 'bp_activity_delete_comment', array( $this, 'delete_comment_sync' ), 10, 2 );
		add_filter( 'bp_activity_allowed_tags', array( &$this, 'override_allowed_tags' ) );
		add_filter( 'bp_get_activity_parent_content', array( &$this, 'bp_get_activity_parent_content' ) );
		add_filter( 'bp_activity_content_before_save', array( $this, 'bp_activity_content_before_save' ) );
		add_filter( 'bp_activity_type_before_save', array( $this, 'bp_activity_type_before_save' ) );
		add_action( 'bp_activity_deleted_activities', array( &$this, 'bp_activity_deleted_activities' ) );

		// Filter bp_activity_prefetch_object_data for translatable activity actions
		add_filter( 'bp_activity_prefetch_object_data', array( $this, 'bp_prefetch_activity_object_data' ), 10, 1 );

		add_filter( 'bp_get_activity_action_pre_meta', array( $this, 'bp_get_activity_action_pre_meta' ), 11, 2 );

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

		// Apply these hooks only on multisite.
		if ( is_multisite() ) {
			// Filter activities in ajax and page reload.
			add_filter( 'bp_ajax_querystring', array( $this, 'filter_activity_with_blog' ) );
			add_filter( 'bp_after_has_activities_parse_args', array( $this, 'filter_activity_with_blog' ) );

			// Maintain activity list in rtm_activity table, reset transients.
			add_action( 'bp_activity_after_save', array( $this, 'bp_activity_after_save' ) );
			add_action( 'bp_activity_after_delete', array( $this, 'bp_activity_after_delete' ) );
		}
	}

	/**
	 * Show media even if the text is long with read more option.
	 *
	 * @param string $excerpt  Excerpt of the activity text.
	 * @param string $text     Actual text of activity.
	 * @param string $readmore Read more text.
	 *
	 * @return string Custom excerpt if conditions are match.
	 */
	public function bp_activity_truncate_entry( $excerpt, $text, $readmore ) {
		// Return if class doesn't exist.
		if ( ! class_exists( 'DOMDocument' ) ) {
			return $excerpt;
		}

		global $activities_template;

		$excerpt_length = bp_activity_get_excerpt_length();
		// Run the text through the excerpt function. If it's too short, the original text will be returned.
		$temp_excerpt = bp_create_excerpt( $text, $excerpt_length, array() );
		if ( strlen( $temp_excerpt ) >= strlen( strip_shortcodes( $text ) ) ) {
			return $excerpt;
		}

		// Get current activity id.
		$activity_id = bp_get_activity_id();

		// We need to separate text and rtMedia images, for this we need DOM manipulation.
		$dom = new DOMDocument();
		// DOMDocument gives error on html5 tags, so we need to disable errors.
		libxml_use_internal_errors( true );
		$dom->loadHTML( $text );
		// DOMDocument gives error on html5 tags, so we need to disable errors.
		libxml_clear_errors();
		// We need to find div having rtmedia-activity-text class, but no direct method for it.
		// So we need to iterate.
		$div_list = $dom->getElementsByTagName( 'div' );

		// Return if no divs found.
		if ( empty( $div_list ) ) {
			return $excerpt;
		}

		// We're storing first div to create final markup.
		// If we create markup from dom object, it'll create whole HTML which we don't want.
		$first_div = '';

		foreach ( $div_list as $div ) {
			// Set first div.
			if ( empty( $first_div ) ) {
				$first_div = $div;
			}

			// We need div with class attribute.
			if ( empty( $div->attributes ) ) {
				continue;
			}

			$atts = $div->attributes;
			// Check attributes by iterating them.
			foreach ( $atts as $att ) {
				if ( empty( $att->name ) || empty( $att->value ) ) {
					continue;
				}

				// Condition to find text div.
				if ( 'class' === $att->name && strpos( $att->value, 'rtmedia-activity-text' ) !== false ) {
					// Create excerpt only on text and then set it to div text.
					// We're using actual length / 2 to make space for image.
					$custom_excerpt   = bp_create_excerpt( $div->textContent, (int) $excerpt_length / 2, array( 'ending' => '...' ) ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Can't change property name.
					$div->textContent = trim( $custom_excerpt ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Can't change property name.

					// Show 4 images if text is less, else show 2 images.
					$images_to_show = 4;
					if ( strlen( $div->textContent ) > 20 ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Can't change property name.
						$images_to_show = 2;
					}

					// Set number of images to show in excerpt.
					$dom = $this->get_bp_activity_media_html( $dom, $images_to_show );

					// Code copied from buddypress.
					$id = ( ! empty( $activities_template->activity->current_comment->id ) ? 'acomment-read-more-' . $activities_template->activity->current_comment->id : 'activity-read-more-' . $activity_id );

					// Get final HTML.
					$content = $first_div->ownerDocument->saveHTML( $first_div ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Can't change property name.

					// Append read more link and text.
					$return = sprintf( '%1$s<span class="activity-read-more" id="%2$s"><a href="%3$s" rel="nofollow">%4$s</a></span>', $content, $id, bp_get_activity_thread_permalink(), $readmore );

					return $return;
				}
			}
		}

		return $excerpt;
	}

	/**
	 * Set number of images to show in activity excerpt.
	 *
	 * @param object $dom            DOMDocument object for DOM manipulation.
	 * @param int    $images_to_show Number of images to show.
	 *
	 * @return object Modified DOMDocument object.
	 */
	private function get_bp_activity_media_html( $dom, $images_to_show ) {
		// Get media list which is inside <ul>.
		$ul_list = $dom->getElementsByTagName( 'ul' );

		// Return if no ul element.
		if ( empty( $ul_list ) ) {
			return $dom;
		}

		// Iterate to find out media-list ul.
		foreach ( $ul_list as $ul ) {
			// We need ul having class 'rtm-activity-media-list'.
			if ( empty( $ul->attributes ) ) {
				continue;
			}

			// Iterate attributes.
			foreach ( $ul->attributes as $att ) {
				if ( empty( $att->name ) || empty( $att->value ) ) {
					continue;
				}

				// Conditions to match required class.
				if ( 'class' === $att->name && strpos( $att->value, 'rtm-activity-media-list' ) !== false && count( $ul->childNodes ) > 0 ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Can't change property name.

					// Number of li (images) allowed to show.
					$count = 1;
					// Array where items to remove will be stored.
					$items_to_remove = array();
					// Iterate all children of ul which are images (li).
					foreach ( $ul->childNodes as $li ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Can't change property name.

						// If max number of images reached, add li to items_to_remove array.
						if ( $count > $images_to_show ) {
							$items_to_remove[] = $li;
						}

						$count++;
					}

					// Remove images.
					foreach ( $items_to_remove as $item ) {
						$ul->removeChild( $item );
					}

					return $dom;
				}
			}
		}

		return $dom;
	}


	/**
	 * For adding secondary avatar in the activity header.
	 *
	 * @param String $action   Has the markup for activity header.
	 * @param array  $activity Contains values realated to the activity.
	 *
	 * @return String $action.
	 */
	function bp_get_activity_action_pre_meta( $action, $activity ) {

		if ( 'rtmedia_update' === $activity->type && 'groups' === $activity->component ) {

			switch ( $activity->component ) {
				case 'groups':
				case 'friends':
					// Only insert avatar if one exists.
					$secondary_avatar = bp_get_activity_secondary_avatar();
					if ( ! empty( $secondary_avatar ) && false === strpos( $activity->action, $secondary_avatar ) ) {

						$reverse_content = strrev( $activity->action );
						$position        = strpos( $reverse_content, 'a<' );
						$action          = substr_replace( $activity->action, $secondary_avatar, -$position - 2, 0 );
					}
					break;
			}

			return $action;

		} else {

			switch ( $activity->component ) {
				case 'groups':
				case 'friends':
					$secondary_avatar = bp_get_activity_secondary_avatar( array( 'linked' => false ) );

					// Only insert avatar if one exists.
					if ( ! empty( $secondary_avatar ) && false === strpos( $activity->action, $secondary_avatar ) ) {

						$link_close  = '">';
						$first_link  = strpos( $activity->action, $link_close );
						$second_link = strpos( $activity->action, $link_close, $first_link + strlen( $link_close ) );
						$action      = substr_replace( $activity->action, $secondary_avatar, $second_link + 2, 0 );
					}
					break;
			}

			return $action;
		}
	}

	/**
	 * To save all activities in rtm_activity table, reset transient.
	 *
	 * @param object $activity Saved activity object.
	 */
	public function bp_activity_after_save( $activity ) {
		$activity_model = new RTMediaActivityModel();
		if ( ! $activity_model->check( $activity->id ) ) {
			$activity_model->insert(
				array(
					'activity_id' => $activity->id,
					'user_id'     => get_current_user_id(),
					'blog_id'     => get_current_blog_id(),
				)
			);
		}

		self::reset_multisite_transient();
	}

	/**
	 * Reset transients for multisite.
	 */
	private static function reset_multisite_transient() {
		$sites = get_sites( array( 'fields' => 'ids' ) );
		foreach ( $sites as $site ) {
			if ( $site === get_current_blog_id() ) {
				continue;
			}

			delete_site_transient( 'rtm_filter_blog_activity_' . $site );
		}
	}

	/**
	 * To delete activities from rtm_activity table, reset transient.
	 *
	 * @param object $activity Deleted activity object.
	 */
	public function bp_activity_after_delete( $activity ) {
		if ( empty( $activity ) ) {
			return;
		}

		if ( is_array( $activity ) ) {
			$activity = $activity[0];
		}

		$activity_model = new RTMediaActivityModel();
		$activity_model->delete( array( 'activity_id' => $activity->id ) );

		self::reset_multisite_transient();
	}


	/**
	 * To handle multisite media.
	 * Exclude activities which has media uploaded from different sites.
	 *
	 * @param string|array $query_string Parameters to filter list of activities.
	 *
	 * @return string
	 */
	public function filter_activity_with_blog( $query_string ) {
		global $wpdb;
		$prefix  = $wpdb->base_prefix;
		$blog_id = get_current_blog_id();

		$transient_name = 'rtm_filter_blog_activity_' . $blog_id;
		$activity_ids   = get_site_transient( $transient_name );
		if ( empty( $activity_ids ) ) {
			$activities   = $wpdb->get_col( $wpdb->prepare( 'SELECT DISTINCT activity_id FROM ' . $prefix . 'rt_rtm_activity WHERE blog_id!=%d', $blog_id ) );
			$activity_ids = implode( ',', $activities );

			set_site_transient( $transient_name, $activity_ids );
		}

		if ( ! empty( $activity_ids ) ) {
			if ( current_filter() === 'bp_ajax_querystring' ) {
				$query_string .= '&exclude=' . $activity_ids;
			} else {
				$query_string['exclude'] = $activity_ids;
			}
		}

		return $query_string;
	}

	/**
	 * Fires after the activity item has been deleted for media cleanup.
	 *
	 * @param array $activity_ids_deleted Array of affected activity item IDs.
	 */
	function bp_activity_deleted_activities( $activity_ids_deleted ) {
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

	/**
	 * This function will check for the media file attached to the activity and accordingly will set content.
	 *
	 * @param string $content Content of the Activity.
	 *
	 * @return string Filtered value of the activity content.
	 */
	public function bp_activity_content_before_save( $content ) {

		$rtmedia_attached_files = filter_input( INPUT_POST, 'rtMedia_attached_files', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( ( ! empty( $rtmedia_attached_files ) ) && is_array( $rtmedia_attached_files ) ) {
			$obj_activity = new RTMediaActivity( $rtmedia_attached_files, 0, $content );

			// Remove action to fix duplication issue of comment content.
			remove_action( 'bp_activity_content_before_save', 'rtmedia_bp_activity_comment_content_callback', 1001, 1 );

			$content = $obj_activity->create_activity_html();
		}
		return $content;
	}

	/**
	 * This function will check for the media file attached to the actitvity and accordingly will set type.
	 *
	 * @param string $type Type of the Activity.
	 *
	 * @return string Filtered value of the activity type.
	 */
	public function bp_activity_type_before_save( $type ) {
		$rtmedia_attached_files = filter_input( INPUT_POST, 'rtMedia_attached_files', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( ( ! empty( $rtmedia_attached_files ) ) && is_array( $rtmedia_attached_files ) && 'activity_update' === $type ) {
			$type = 'rtmedia_update';
		}
		return $type;
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
			bp_activity_update_meta( $activity_id, 'bp_activity_text', bp_activity_filter_kses( $content ) );
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

		/**
		 * Filter to enable/disable media upload from the activity.
		 *
		 * @param bool Default true to enable activity media upload false to disable activity media upload.
		 */
		if ( ! apply_filters( 'rtmedia_enable_activity_media_upload', true ) ) {
			return;
		}
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
					'redirection'          => 'false',
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
		if ( class_exists( 'BuddyPress' ) && function_exists( 'bp_activity_add' ) ) {
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
					remove_filter( 'bp_activity_content_before_save', array( $this, 'bp_activity_content_before_save' ) );
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
		if ( ! empty( $comment_id ) && function_exists( 'bp_activity_delete' ) ) {

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
	 * Set the privacy for comment activity.
	 *
	 * @param string $comment_id Activity id of the comment.
	 * @param array  $r          Array of arguments.
	 */
	public function rtm_check_privacy_for_comments( $comment_id, $r ) {

		if ( empty( $r ) || empty( $comment_id ) || ( ! is_array( $r ) ) ) {
			return;
		}

		$activity_id = $r['activity_id'];
		$user_id     = $r['user_id'];
		$privacy_id  = bp_activity_get_meta( $activity_id, 'rtmedia_privacy' );

		$rtm_activity_model = new RTMediaActivityModel();
		$rtm_activity_model->set_privacy( $comment_id, $user_id, $privacy_id );
	}
}
