<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaModifyScreen
 *
 * @author saurabh
 */
class BPMediaModifyScreen extends BPMediaScreen {


	public function __construct( $title, $slug ) {
		parent::__construct($title, $slug);
	}

	function entry_screen(){
		
	}
	function edit_screen(){

	}

	function entry_delete() {
		global $bp, $bp_media;
		if ( bp_loggedin_user_id() != bp_displayed_user_id() ) {
			bp_core_no_access( array(
				'message' => __( 'You do not have access to this page.', 'buddypress' ),
				'root' => bp_displayed_user_domain(),
				'redirect' => false
			) );
			exit;
		}
		if ( ! isset( $bp->action_variables[ 1 ] ) ) {
			@setcookie( 'bp-message', __('The requested url does not exist', $bp_media->text_domain), time() + 60 * 60 * 24, COOKIEPATH );
			@setcookie( 'bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH );
			wp_redirect( trailingslashit( bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG ) );
			exit;
		}
		global $bp_media_current_entry;
		try {
			$bp_media_current_entry = new BP_Media_Host_Wordpress( $bp->action_variables[ 1 ] );
		} catch ( Exception $e ) {
			/* Send the values to the cookie for page reload display */
			@setcookie( 'bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH );
			@setcookie( 'bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH );
			wp_redirect( trailingslashit( bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG ) );
			exit;
		}
		$post_id = $bp_media_current_entry->get_id();
		$activity_id = get_post_meta( $post_id, 'bp_media_child_activity', true );

		bp_activity_delete_by_activity_id( $activity_id );
		$bp_media_current_entry->delete_media();

		@setcookie( 'bp-message', __( 'Media deleted successfully', $bp_media->text_domain ), time() + 60 * 60 * 24, COOKIEPATH );
		@setcookie( 'bp-message-type', 'success', time() + 60 * 60 * 24, COOKIEPATH );
		wp_redirect( trailingslashit( bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG ) );
		exit;
	}

}

?>
