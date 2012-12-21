<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaImageEditScreen
 *
 * @author saurabh
 */
class BPMediaImageEditScreen extends BPMediaScreen {

	function __construct( $title, $slug ) {
		parent::__construct( $title, $slug );
	}

	function bp_media_images_edit_screen() {
		global $bp_media_current_entry, $bp;
		if ( ! isset( $bp->action_variables[ 1 ] ) ) {
			bp_media_page_not_exist();
		}
		//Creating global bp_media_current_entry for later use
		try {
			$bp_media_current_entry = new BP_Media_Host_Wordpress( $bp->action_variables[ 1 ] );
		} catch ( Exception $e ) {
			/* Send the values to the cookie for page reload display */
			@setcookie( 'bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH );
			@setcookie( 'bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH );
			wp_redirect( trailingslashit( bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG ) );
			exit;
		}
		bp_media_check_user();

		//For saving the data if the form is submitted
		if ( array_key_exists( 'bp_media_title', $_POST ) ) {
			bp_media_update_media();
		}
		add_action( 'bp_template_title', 'edit_screen_title' );
		add_action( 'bp_template_content', 'edit_screen_content' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

}

?>
