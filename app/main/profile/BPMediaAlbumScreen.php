<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaAlbumScreen
 *
 * @author saurabh
 */
class BPMediaAlbumScreen extends BPMediaScreen {

	public function __construct( $media_type, $slug ) {
		parent::__construct( $media_type, $slug );
	}

	function screen() {
		global $bp;
		if ( isset( $bp->action_variables[ 0 ] ) ) {
			switch ( $bp->action_variables[ 0 ] ) {
				case BP_MEDIA_ALBUMS_EDIT_SLUG :
					$this->edit_screen();
					break;
				case BP_MEDIA_ALBUMS_ENTRY_SLUG:
					$this->entry_screen();
					$this->template_actions( 'entry_screen' );
					break;
				case BP_MEDIA_DELETE_SLUG :
					if ( ! isset( $bp->action_variables[ 1 ] ) ) {
						$this->page_not_exist();
					}
					$this->entry_delete();
					break;
				default:
					$this->set_query();
					$this->template_actions( 'screen' );
			}
		} else {
			$this->set_query();
			$this->template_actions( 'screen' );
		}
		$this->template->loader();
	}

	function screen_content() {
		global $bp_media_albums_query;

		$this->hook_before();
		if ( $bp_media_albums_query && $bp_media_albums_query->have_posts() ):
			echo '<ul id="bp-media-list" class="bp-media-gallery item-list">';
			while ( $bp_media_albums_query->have_posts() ) : $bp_media_albums_query->the_post();
				$this->template->the_album_content();
			endwhile;
			echo '</ul>';
			$this->template->show_more();
		else:
			BPMediaFunction::show_formatted_error_message( sprintf( __( 'Sorry, no %s were found.', BP_MEDIA_TXT_DOMAIN ), $this->slug ), 'info' );
		endif;
		$this->hook_after();
	}

	function entry_screen() {
		global $bp, $bp_media_current_album;
		if ( ! $bp->action_variables[ 0 ] == BP_MEDIA_ALBUMS_ENTRY_SLUG )
			return false;
		try {
			$bp_media_current_album = new BPMediaAlbum( $bp->action_variables[ 1 ] );
		} catch ( Exception $e ) {
			/* Send the values to the cookie for page reload display */
			@setcookie( 'bp-message', $_COOKIE[ 'bp-message' ], time() + 60 * 60 * 24, COOKIEPATH );
			@setcookie( 'bp-message-type', $_COOKIE[ 'bp-message-type' ], time() + 60 * 60 * 24, COOKIEPATH );
			$this->template->redirect($this->media_const);
			exit;
		}
	}

	function entry_screen_content() {
		global $bp, $bp_media_current_album, $bp_media_query;
                if ( ! $bp->action_variables[ 0 ] == BP_MEDIA_ALBUMS_ENTRY_SLUG )
			return false;
		echo '<div class="bp_media_title">' . $bp_media_current_album->get_title() . '</div>';
		$this->inner_query( $bp_media_current_album->get_id() );
		$this->hook_before();
		if ( $bp_media_current_album && $bp_media_query->have_posts() ):
			echo '<ul id="bp-media-list" class="bp-media-gallery item-list">';
			while ( $bp_media_query->have_posts() ) : $bp_media_query->the_post();
				$this->template->the_content();
			endwhile;
			echo '</ul>';
			$this->template->show_more();
		else:
			BPMediaFunction::show_formatted_error_message( __( 'Sorry, no media items were found in this album.', BP_MEDIA_TXT_DOMAIN ), 'info' );
		endif;
		$this->hook_after();
	}

	function set_query() {
		global $bp, $bp_media_albums_query;
		if ( isset( $bp->action_variables ) && is_array( $bp->action_variables ) && isset( $bp->action_variables[ 0 ] ) && $bp->action_variables[ 0 ] == 'page' && isset( $bp->action_variables[ 1 ] ) && is_numeric( $bp->action_variables[ 1 ] ) ) {
			$paged = $bp->action_variables[ 1 ];
		} else {
			$paged = 1;
		}
		if ( $bp->current_action == BP_MEDIA_ALBUMS_SLUG ) {
			$args = array(
				'post_type' => 'bp_media_album',
				'author' => $bp->displayed_user->id,
				'paged' => $paged,
				'meta_key' => 'bp-media-key',
				'meta_value' => $bp->displayed_user->id,
				'meta_compare' => '='
			);
			$bp_media_albums_query = new WP_Query( $args );
		}
	}


	function template_actions( $action ) {
		add_action( 'bp_template_content', array( $this, $action . '_content' ) );
	}

	function inner_query( $album_id = 0 ) {
		global $bp, $bp_media_query;
		$paged = 0;
		$action_variables = isset( $bp->canonical_stack[ 'action_variables' ] ) ? $bp->canonical_stack[ 'action_variables' ] : null;
		if ( isset( $action_variables ) && is_array( $action_variables ) && isset( $action_variables[ 0 ] ) ) {
			if ( $action_variables[ 0 ] == 'page' && isset( $action_variables[ 1 ] ) && is_numeric( $action_variables[ 1 ] ) )
				$paged = $action_variables[ 1 ];
			else if ( isset( $action_variables[ 1 ] ) && $action_variables[ 1 ] == 'page' && isset( $action_variables[ 2 ] ) && is_numeric( $action_variables[ 2 ] ) )
				$paged = $action_variables[ 2 ];
		}
		if ( ! $paged )
			$paged = 1;
		$args = array(
			'post_type' => 'attachment',
			'post_status' => 'any',
			'post_parent' => $album_id,
			'paged' => $paged
		);
		$bp_media_query = new WP_Query( $args );
	}

}

?>
