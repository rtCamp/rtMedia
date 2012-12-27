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

	function the_content( $id = 0 ) {
		if ( is_object( $id ) )
			$album = $id;
		else
			$album = &get_post( $id );
		if ( empty( $album->ID ) )
			return false;
		if ( ! $album->post_type == 'bp_media_album' )
			return false;
		try {
			$album = new BP_Media_Album( $album->ID );
			echo $album->get_album_gallery_content();
		} catch ( Exception $e ) {
			echo '';
		}
	}

}

?>
