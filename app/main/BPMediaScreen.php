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
	public $media_type = '';

	public function __construct( $title, $slug ) {
		$this->title = $title;
		$this->slug = $slug;
	}

	public function screen_title() {
		return $this->title;
	}

	public function media( $media_type ) {
		$this->media_type = $media_type;
	}

	public function hook_before() {
		do_action( 'bp_media_before_content' );
		do_action( 'bp_media_before_' . $this->media_type );
	}

	public function hook_after() {
		do_action( 'bp_media_after_' . $this->media_type );
		do_action( 'bp_media_after_content' );
	}

	public function page_not_exist() {
		global $bp_media;
		@setcookie( 'bp-message', __( 'The requested url does not exist', $bp_media->text_domain ), time() + 60 * 60 * 24, COOKIEPATH );
		@setcookie( 'bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH );
		wp_redirect( trailingslashit( bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG ) );
		exit;
	}

	public function media_screen( $media_type ) {

		global $bp;

		$this->media( $media_type );

		$media_const = strtoupper( $this->media_type ) . 'S';


		$editslug = 'BP_MEDIA_' . $media_const . '_EDIT_SLUG';
		$entryslug = 'BP_MEDIA_' . $media_const . '_ENTRY_SLUG';
		remove_filter( 'bp_activity_get_user_join_filter', 'bp_media_activity_query_filter', 10 );
		if ( isset( $bp->action_variables[ 0 ] ) ) {
			switch ( $bp->action_variables[ 0 ] ) {
				case constant( $editslug ) :
					$this->edit_screen();
					break;
				case constant( $entryslug ) :
					$this->entry_screen();
					break;
				case BP_MEDIA_DELETE_SLUG :
					if ( ! isset( $bp->action_variables[ 1 ] ) ) {
						$this->page_not_exist();
					}
					$this->entry_delete();
					break;
				default:
					bp_media_set_query();
					add_action( 'bp_template_content', array( $this, 'screen_content' ) );
			}
		} else {
			bp_media_set_query();
			add_action( 'bp_template_content', array( $this, 'screen_content' ) );
		}
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	function entry_screen() {
		global $bp, $bp_media, $bp_media_current_entry;
		if ( ! isset( $bp->action_variables[ 1 ] ) ) {
			$this->page_not_exist();
		}
		try {
			$bp_media_current_entry = new BP_Media_Host_Wordpress( $bp->action_variables[ 1 ] );
			if ( $bp_media_current_entry->get_author() != bp_displayed_user_id() )
				throw new Exception( __( 'Sorry, the requested media does not belong to the user', $bp_media->text_domain ) );
		} catch ( Exception $e ) {
			/* Send the values to the cookie for page reload display */
			if ( isset( $_COOKIE[ 'bp-message' ] ) && $_COOKIE[ 'bp-message' ] != '' ) {
				@setcookie( 'bp-message', $_COOKIE[ 'bp-message' ], time() + 60 * 60 * 24, COOKIEPATH );
				@setcookie( 'bp-message-type', $_COOKIE[ 'bp-message-type' ], time() + 60 * 60 * 24, COOKIEPATH );
			} else {
				@setcookie( 'bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH );
				@setcookie( 'bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH );
			}
			wp_redirect( trailingslashit( bp_displayed_user_domain() . constant( 'BP_MEDIA_' . $this->media_type . '_SLUG' ) ) );
			exit;
		}
		add_action( 'bp_template_title', array( $this, 'entry_screen_title' ) );
		add_action( 'bp_template_content', array( $this, 'entry_screen_content' ) );
	}

	function media_screen_content() {
		global $bp_media_query;
		$this->hook_before();
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
		$this->hook_after();
	}

	function media_edit_screen() {
		global $bp_media_current_entry, $bp;
		if ( ! isset( $bp->action_variables[ 1 ] ) ) {
			$this->page_not_exist();
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
		add_action( 'bp_template_title', array( $this, 'edit_screen_title' ) );
		add_action( 'bp_template_content', array( $this, 'edit_screen_content' ) );
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
