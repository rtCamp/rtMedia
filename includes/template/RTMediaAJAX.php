<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaAJAX
 *
 * @author udit
 */
class RTMediaAJAX {

	public function __construct() {
		add_action( 'wp_ajax_rtmedia_backbone_template', array( $this, 'backbone_template' ) );
		add_action( 'wp_ajax_rtmedia_create_album', array( $this, 'create_album' ) );
		add_action( 'rtmedia_bp_media_query_init', array( $this, 'rtm_bp_load_more_media' ) );
		add_action( 'rtmedia_bp_media_query_init', array( $this, 'rtm_bp_load_lightbox_media' ) );
	}

	/**
	 * Hooked to "rtmedia_bp_media_query_init" action
	 *
	 * Check if current request is single media ajax request and load main template accordingly.
	 */
	function rtm_bp_load_lightbox_media(){
		global $bp, $rt_ajax_request;
		if( $rt_ajax_request && $bp->media->is_single_media_screen ){
			status_header( 200 );
			include( RTMediaTemplate::locate_template( 'main', '' ) );
			die();
		}
	}

	/**
	 * Hooked to "rtmedia_bp_media_query_init" action
	 *
	 * Check and return JSON if current request for media is JSON.
	 */
	function rtm_bp_load_more_media(){
		global $rtmedia_query;
		if( is_a( $rtmedia_query, 'RTMediaQuery' ) && isset( $rtmedia_query->format ) && $rtmedia_query->format == 'json' ){
			status_header ( 200 );
			$rtmedia_template = new RTMediaTemplate();
			$rtmedia_template->json_output();
		}
	}

	function backbone_template() {
		include RTMEDIA_PATH.'templates/media/media-gallery-item.php';
	}

	function create_album() {
		$nonce = $_POST[ 'create_album_nonce' ];

		$return['error'] = false;
		if( wp_verify_nonce( $nonce, 'rtmedia_create_album_nonce' ) && isset( $_POST[ 'name' ] ) && $_POST[ 'name' ] && is_rtmedia_album_enable() ) {
			if( isset( $_POST[ 'context' ] ) && $_POST[ 'context' ] == "group" ) {
				$group_id = !empty( $_POST[ 'context_id' ] ) ? $_POST[ 'context_id' ] : '';

				if( can_user_create_album_in_group( $group_id ) == false ) {
					$return['error'] = __( 'You can not create album in this group.', 'rtmedia' );
				}
			}

			$create_album = apply_filters( "rtm_is_album_create_enable", true );
			if( !$create_album ) {
				$return['error'] = __( 'You can not create album.', 'rtmedia' );
			}

			$create_album = apply_filters( "rtm_display_create_album_button", true, $_POST[ 'context_id' ] );
			if( !$create_album ) {
				$return['error'] = __( 'You can not create more albums, you exceed your album limit.', 'rtmedia' );
			}

			if( $return['error'] !== false ){
				echo json_encode( $return );
				wp_die();
			}

			$album = new RTMediaAlbum();

			// setup context values
			$context = $_POST['context'];
			if( $context == 'profile' ){
				$context_id = get_current_user_id();
			} else {
				$context_id = ( isset( $_POST['context_id'] ) ? $_POST['context_id'] : 0 );
			}

			// setup new album data
			$album_data = apply_filters( 'rtmedia_create_album_data', array(
				'title' => $_POST['name'],
				'author' => get_current_user_id(),
				'new' => true,
				'post_id'=> false,
				'context' => $context,
				'context_id' => $context_id,
			) );

			$rtmedia_id = $album->add( $album_data['title'], $album_data['author'], $album_data['new'], $album_data['post_id'], $album_data['context'], $album_data['context_id'] );

			$rtMediaNav = new RTMediaNav();

			if( $_POST[ 'context' ] == "group" ) {
				$rtMediaNav->refresh_counts( $_POST[ 'context_id' ], array( "context" => $_POST[ 'context' ], 'context_id' => $_POST[ 'context_id' ] ) );
			} else {
				$rtMediaNav->refresh_counts( get_current_user_id(), array( "context" => "profile", 'media_author' => get_current_user_id() ) );
			}

			if( $rtmedia_id ){
				$return['album'] = apply_filters( 'rtmedia_create_album_response', $rtmedia_id );
				echo json_encode( $return );
			} else {
				echo false;
			}
		} else {
			$return['error'] = __( 'Data mismatch, Please insert data properly.', 'rtmedia' );
			echo json_encode( $return );
		}

		wp_die();
	}
}
