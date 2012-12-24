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

	public function screen() {
		$editslug = 'BP_MEDIA_' . $this->media_const . '_EDIT_SLUG';
		$entryslug = 'BP_MEDIA_' . $this->media_const . '_ENTRY_SLUG';

		global $bp;

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
		$this->template_loader();
	}

	function bp_media_albums_set_query() {
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

	function bp_media_set_query() {
		global $bp, $bp_media_query, $bp_media_posts_per_page;
		switch ( $bp->current_action ) {
			case BP_MEDIA_IMAGES_SLUG:
				$type = 'image';
				break;
			case BP_MEDIA_AUDIO_SLUG:
				$type = 'audio';
				break;
			case BP_MEDIA_VIDEOS_SLUG:
				$type = 'video';
				break;
			case BP_MEDIA_ALBUMS_SLUG:
				$type = 'album';
				break;
			default :
				$type = null;
		}
		if ( isset( $bp->action_variables ) && is_array( $bp->action_variables ) && isset( $bp->action_variables[ 0 ] ) && $bp->action_variables[ 0 ] == 'page' && isset( $bp->action_variables[ 1 ] ) && is_numeric( $bp->action_variables[ 1 ] ) ) {
			$paged = $bp->action_variables[ 1 ];
		} else {
			$paged = 1;
		}
		if ( $type ) {
			$args = array(
				'post_type' => 'attachment',
				'post_status' => 'any',
				'post_mime_type' => $type,
				'author' => $bp->displayed_user->id,
				'meta_key' => 'bp-media-key',
				'meta_value' => $bp->displayed_user->id,
				'meta_compare' => '=',
				'paged' => $paged,
				'posts_per_page' => $bp_media_posts_per_page
			);
			if ( $bp->current_action == BP_MEDIA_ALBUMS_SLUG ) {
				$args[ 'post_type' ] = 'bp_media_album';
			}
			$bp_media_query = new WP_Query( $args );
		}
	}

}

?>
