<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaImageScreen
 *
 * @author saurabh
 */
class BPMediaImageScreen extends BPMediaScreen {

	function __construct( $title, $slug ) {
		parent::__construct( $title, $slug );
		
	}

	function ui() {
		global $bp;
		remove_filter( 'bp_activity_get_user_join_filter', 'bp_media_activity_query_filter', 10 );
		if ( isset( $bp->action_variables[ 0 ] ) ) {
			switch ( $bp->action_variables[ 0 ] ) {
				case BP_MEDIA_IMAGES_EDIT_SLUG :
					edit_screen();
					break;
				case BP_MEDIA_IMAGES_ENTRY_SLUG:
					global $bp_media_current_entry;
					if ( ! isset( $bp->action_variables[ 1 ] ) ) {
						bp_media_page_not_exist();
					}
					try {
						$bp_media_current_entry = new BP_Media_Host_Wordpress( $bp->action_variables[ 1 ] );
						if ( $bp_media_current_entry->get_author() != bp_displayed_user_id() )
							throw new Exception( __( 'Sorry, the requested media does not belong to the user' ) );
					} catch ( Exception $e ) {
						/* Send the values to the cookie for page reload display */
						if ( isset( $_COOKIE[ 'bp-message' ] ) && $_COOKIE[ 'bp-message' ] != '' ) {
							@setcookie( 'bp-message', $_COOKIE[ 'bp-message' ], time() + 60 * 60 * 24, COOKIEPATH );
							@setcookie( 'bp-message-type', $_COOKIE[ 'bp-message-type' ], time() + 60 * 60 * 24, COOKIEPATH );
						} else {
							@setcookie( 'bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH );
							@setcookie( 'bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH );
						}
						wp_redirect( trailingslashit( bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG ) );
						exit;
					}
					add_action( 'bp_template_title', 'entry_screen_title' );
					add_action( 'bp_template_content', 'entry_screen_content' );
					break;
				case BP_MEDIA_DELETE_SLUG :
					if ( ! isset( $bp->action_variables[ 1 ] ) ) {
						bp_media_page_not_exist();
					}
					bp_media_entry_delete();
					break;
				default:
					bp_media_set_query();
					add_action( 'bp_template_content', 'screen' );
			}
		} else {
			bp_media_set_query();
			add_action( 'bp_template_content', 'screen' );
		}
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	function generate_ui() {
		global $bp_media_query;
		if ( $bp_media_query && $bp_media_query->have_posts() ):
			echo '<ul id="bp-media-list" class="bp-media-gallery item-list">';
			while ( $bp_media_query->have_posts() ) : $bp_media_query->the_post();
				bp_media_the_content();
			endwhile;
			echo '</ul>';
			bp_media_display_show_more();
		else:
			bp_media_show_formatted_error_message( __( 'Sorry, no images were found.', 'bp-media' ), 'info' );
		endif;
	}



}

?>
