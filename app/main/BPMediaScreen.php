<?php

if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of BPMediaScreen
 *
 * @author saurabh
 */
class BPMediaScreen {

	public $title = NULL;
	public $slug = NULL;

	public function __construct( $title, $slug ) {
		$this->title = $title;
		$this->slug = $slug;
	}

	public function screen_title() {
		return $this->title;
	}

	public function hook_before() {
		do_action( 'bp_media_before_content' );
		do_action( 'bp_media_before_' . $this->slug );
	}

	public function hook_after() {
		do_action( 'bp_media_after_' . $this->slug );
		do_action( 'bp_media_after_content' );
	}

	public function page_not_exist() {
		global $bp_media;
		@setcookie( 'bp-message', __( 'The requested url does not exist', $bp_media->text_domain ), time() + 60 * 60 * 24, COOKIEPATH );
		@setcookie( 'bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH );
		wp_redirect( trailingslashit( bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG ) );
		exit;
	}

	public function entry_screen( $media_type ) {

		global $bp;

		$media_const = strtoupper( $media_type ) . 'S';


		$editslug = 'BP_MEDIA_' . $media_const . '_EDIT_SLUG';
		$entryslug = 'BP_MEDIA_' . $media_const . '_ENTRY_SLUG';
		remove_filter( 'bp_activity_get_user_join_filter', 'bp_media_activity_query_filter', 10 );
		if ( isset( $bp->action_variables[ 0 ] ) ) {
			switch ( $bp->action_variables[ 0 ] ) {
				case constant( $editslug ) :
					$this->edit_screen( $media_type );
					break;
				case constant( $entryslug ) :
					global $bp_media_current_entry;
					if ( ! isset( $bp->action_variables[ 1 ] ) ) {
						$this->page_not_exist();
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
						wp_redirect( trailingslashit( bp_displayed_user_domain() . constant( 'BP_MEDIA_' . $media_type . '_SLUG' ) ) );
						exit;
					}
					add_action( 'bp_template_title', array($this,'entry_screen_title') );
					add_action( 'bp_template_content', array($this,'entry_screen_content') );
					break;
				case BP_MEDIA_DELETE_SLUG :
					if ( ! isset( $bp->action_variables[ 1 ] ) ) {
						$this->page_not_exist();
					}
					$this->entry_delete();
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

	}

	function screen() {
		$this->hook_before();

		$this->generate_ui();

		$this->hook_after();
	}

	public function process() {

	}

	public function ux() {

	}

}

?>
