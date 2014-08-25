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

    function __construct () {
        global $rtmedia;
        if ( $rtmedia->options[ "buddypress_enableOnActivity" ] != 0 ) {
            add_action ( "bp_after_activity_post_form", array( &$this, "bp_after_activity_post_form" ) );
            add_action ( "bp_activity_posted_update", array( &$this, "bp_activity_posted_update" ), 99, 3 );
            add_action ( "bp_groups_posted_update", array( &$this, "bp_groups_posted_update" ), 99, 4 );
        }
        add_action ( "bp_init", array( $this, 'non_threaded_comments' ) );
        add_action ( "bp_activity_comment_posted", array( $this, "comment_sync" ), 10, 2 );
        add_action ( "bp_activity_delete_comment", array( $this, "delete_comment_sync" ), 10, 2 );
        add_filter ( 'bp_activity_allowed_tags', array( &$this, 'override_allowed_tags' ) );
        add_filter ( 'bp_get_activity_parent_content', array( &$this, 'bp_get_activity_parent_content' ) );
        add_action ( 'bp_activity_deleted_activities', array( &$this, 'bp_activity_deleted_activities' ) );
    }

    function bp_activity_deleted_activities ( $activity_ids_deleted ) {
        //$activity_ids_deleted
        $rt_model = new RTMediaModel();
        $all_media = $rt_model->get ( array( "activity_id" => $activity_ids_deleted ) );
        if ( $all_media ) {
            $media = new RTMediaMedia();
            remove_action ( 'bp_activity_deleted_activities', array( &$this, 'bp_activity_deleted_activities' ) );
            foreach ( $all_media as $single_media ) {
                $media->delete ( $single_media->id, false, false );
            }
        }
    }

    function bp_get_activity_parent_content ( $content ) {
        global $activities_template;

        // Get the ID of the parent activity content
        if ( ! $parent_id = $activities_template->activity->item_id )
            return false;

        // Bail if no parent content
        if ( empty ( $activities_template->activity_parents[ $parent_id ] ) )
            return false;

        // Bail if no action
        if ( empty ( $activities_template->activity_parents[ $parent_id ]->action ) )
            return false;

        // Content always includes action
        $content = $activities_template->activity_parents[ $parent_id ]->action;

        // Maybe append activity content, if it exists
        if ( ! empty ( $activities_template->activity_parents[ $parent_id ]->content ) )
            $content .= ' ' . $activities_template->activity_parents[ $parent_id ]->content;

        // Remove the time since content for backwards compatibility
        $content = str_replace ( '<span class="time-since">%s</span>', '', $content );
        return $content;
    }
    function delete_comment_sync($activity_id, $comment_id){
        global $wpdb;
        $comment_id = $wpdb->get_var($wpdb->prepare("select comment_id from {$wpdb->commentmeta} where meta_key = 'activity_id' and meta_value=%s",$comment_id));
        if($comment_id){
            wp_delete_comment($comment_id , true);
        }
    }
    function comment_sync ( $comment_id, $param ) {

        $user_id = '';
	$comment_author = '';
        extract($param);
	if( !empty($user_id) ){
		$user_data = get_userdata($user_id);
		$comment_author = $user_data->data->user_login;
	}
	$mediamodel = new RTMediaModel();
	$media = $mediamodel->get(array('activity_id' => $param[ 'activity_id' ]));
	// if there is only single media in activity
	if(sizeof($media) == 1 && isset($media[0]->media_id)) {
	    $media_id = $media[0]->media_id;
	    $comment = new RTMediaComment();
            $id = $comment->add ( array( 'comment_content' => $param[ 'content' ], 'comment_post_ID' => $media_id, 'user_id' => $user_id, 'comment_author' => $comment_author ) );
            update_comment_meta($id, 'activity_id', $comment_id);
	}
    }

    function non_threaded_comments () {
        if ( isset ( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'new_activity_comment' ) {
            $activity_id = $_POST[ 'form_id' ];
            $act = new BP_Activity_Activity ( $activity_id );

            if ( $act->type == 'rtmedia_update' )
                $_POST[ 'comment_id' ] = $_POST[ 'form_id' ];
        }
    }

    function bp_groups_posted_update ( $content, $user_id, $group_id, $activity_id ) {
        $this->bp_activity_posted_update ( $content, $user_id, $activity_id );
    }

    function bp_activity_posted_update ( $content, $user_id, $activity_id ) {
		global $wpdb, $bp;
		$updated_content = "";

		// hook for rtmedia buddypress before activity posted
		do_action( 'rtmedia_bp_before_activity_posted', $content, $user_id, $activity_id );

        if ( isset ( $_POST[ "rtMedia_attached_files" ] ) && is_array ( $_POST[ "rtMedia_attached_files" ] ) ) {
            $updated_content = $wpdb->get_var ( "select content from  {$bp->activity->table_name} where  id= $activity_id" );

            $objActivity = new RTMediaActivity ( $_POST[ "rtMedia_attached_files" ], 0, $updated_content );
            $html_content = $objActivity->create_activity_html();
            bp_activity_update_meta($activity_id, "bp_old_activity_content", $html_content);
            bp_activity_update_meta($activity_id, "bp_activity_text", $updated_content);
            $wpdb->update ( $bp->activity->table_name, array( "type" => "rtmedia_update", "content" => $html_content ), array( "id" => $activity_id ) );
            $mediaObj = new RTMediaModel();
            $sql = "update $mediaObj->table_name set activity_id = '" . $activity_id . "' where blog_id = '".get_current_blog_id()."' and id in (" . implode ( ",", $_POST[ "rtMedia_attached_files" ] ) . ")";
            $wpdb->query ( $sql );
        }
		// hook for rtmedia buddypress after activity posted
		do_action( 'rtmedia_bp_activity_posted', $updated_content, $user_id, $activity_id );

        if ( isset ( $_POST[ 'rtmedia-privacy' ] ) ) {
            $privacy = -1;
            if ( is_rtmedia_privacy_enable () ) {
                if ( is_rtmedia_privacy_user_overide () ) {
                    $privacy = $_POST[ 'rtmedia-privacy' ];
                } else {
                    $privacy = get_rtmedia_default_privacy ();
                }
            }
            bp_activity_update_meta ( $activity_id, 'rtmedia_privacy', $privacy );
        }
    }

    function bp_after_activity_post_form () {
        $url = $_SERVER[ "REQUEST_URI" ];
        $url = trailingslashit ( $url );
        $allow_upload = apply_filters( 'rtmedia_allow_uploader_view', true, 'activity' );
        if( $allow_upload ) {
            $params = array(
                'url' => (isset ( $url ) && (strpos ( $url, "/media/" ) !== false)) ? str_replace ( "/media/", "/upload/", $url ) : 'upload/',
                'runtimes' => 'html5,flash,html4',
                'browse_button' => 'rtmedia-add-media-button-post-update',// browse button assigned to "Attach Files" Button.
                'container' => 'rtmedia-whts-new-upload-container',
                'drop_element' => 'whats-new-textarea',// drag-drop area assigned to activity update textarea
                'filters' => apply_filters ( 'rtmedia_plupload_files_filter', array( array( 'title' => __( 'Media Files', 'rtmedia' ), 'extensions' => get_rtmedia_allowed_upload_type () ) ) ),
                'max_file_size' => min ( array( ini_get ( 'upload_max_filesize' ), ini_get ( 'post_max_size' ) ) ),
                'multipart' => true,
                'urlstream_upload' => true,
                'flash_swf_url' => includes_url ( 'js/plupload/plupload.flash.swf' ),
                'silverlight_xap_url' => includes_url ( 'js/plupload/plupload.silverlight.xap' ),
                'file_data_name' => 'rtmedia_file', // key passed to $_FILE.
                'multi_selection' => true,
                'multipart_params' => apply_filters ( 'rtmedia-multi-params', array( 'redirect' => 'no', 'rtmedia_update' => 'true', 'action' => 'wp_handle_upload', '_wp_http_referer' => $_SERVER[ 'REQUEST_URI' ], 'mode' => 'file_upload', 'rtmedia_upload_nonce' => RTMediaUploadView::upload_nonce_generator ( false, true ) ) ),
            'max_file_size_msg' => apply_filters("rtmedia_plupload_file_size_msg",min ( array( ini_get ( 'upload_max_filesize' ), ini_get ( 'post_max_size' ) ) ))
            );
            if ( wp_is_mobile () )
                $params[ 'multi_selection' ] = false;
            $params = apply_filters("rtmedia_modify_upload_params",$params);
            wp_enqueue_script ( 'rtmedia-backbone', false, '', false, true );
            $is_album = is_rtmedia_album () ? true : false;
            $is_edit_allowed = is_rtmedia_edit_allowed () ? true : false;
            wp_localize_script ( 'rtmedia-backbone', 'is_album', $is_album );
            wp_localize_script ( 'rtmedia-backbone', 'is_edit_allowed', $is_edit_allowed );
            wp_localize_script ( 'rtmedia-backbone', 'rtMedia_update_plupload_config', $params );


            $uploadView = new RTMediaUploadView ( array( 'activity' => true ) );
            $uploadView->render ( 'uploader' );
        } else {
            echo "<div class='rtmedia-upload-not-allowed'>" . apply_filters( 'rtmedia_upload_not_allowed_message', __('You are not allowed to upload/attach media.','rtmedia'), 'activity' ) . "</div>";
        }
    }

    function override_allowed_tags ( $activity_allowedtags ) {

        $activity_allowedtags[ 'video' ] = array( );
        $activity_allowedtags[ 'video' ][ 'id' ] = array( );
        $activity_allowedtags[ 'video' ][ 'class' ] = array( );
        $activity_allowedtags[ 'video' ][ 'src' ] = array( );
        $activity_allowedtags[ 'video' ][ 'controls' ] = array( );
        $activity_allowedtags[ 'video' ][ 'preload' ] = array( );
        $activity_allowedtags[ 'video' ][ 'alt' ] = array( );
        $activity_allowedtags[ 'video' ][ 'title' ] = array( );
        $activity_allowedtags[ 'video' ][ 'width' ] = array( );
        $activity_allowedtags[ 'video' ][ 'height' ] = array( );
        $activity_allowedtags[ 'video' ][ 'poster' ] = array( );
        $activity_allowedtags[ 'audio' ] = array( );
        $activity_allowedtags[ 'audio' ][ 'id' ] = array( );
        $activity_allowedtags[ 'audio' ][ 'class' ] = array( );
        $activity_allowedtags[ 'audio' ][ 'src' ] = array( );
        $activity_allowedtags[ 'audio' ][ 'controls' ] = array( );
        $activity_allowedtags[ 'audio' ][ 'preload' ] = array( );
        $activity_allowedtags[ 'audio' ][ 'alt' ] = array( );
        $activity_allowedtags[ 'audio' ][ 'title' ] = array( );
        $activity_allowedtags[ 'audio' ][ 'width' ] = array( );
        $activity_allowedtags[ 'audio' ][ 'poster' ] = array( );
        $activity_allowedtags[ 'div' ] = array( );
        $activity_allowedtags[ 'div' ][ 'id' ] = array( );
        $activity_allowedtags[ 'div' ][ 'class' ] = array( );
        $activity_allowedtags[ 'a' ] = array( );
        $activity_allowedtags[ 'a' ][ 'title' ] = array( );
        $activity_allowedtags[ 'a' ][ 'href' ] = array( );
        $activity_allowedtags[ 'ul' ] = array( );
        $activity_allowedtags[ 'ul' ][ 'class' ] = array( );
        $activity_allowedtags[ 'li' ] = array( );
        $activity_allowedtags[ 'li' ][ 'class' ] = array( );

        /* Legacy Code */
        //$activity_allowedtags[ 'script' ] = array( );
        //$activity_allowedtags[ 'script' ][ 'type' ] = array( );

        return $activity_allowedtags;
    }

}
