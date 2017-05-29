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
	}

	function backbone_template() {
		include RTMEDIA_PATH . 'templates/media/media-gallery-item.php';
	}

	function create_album() {
		$nonce = filter_input( INPUT_POST, 'create_album_nonce', FILTER_SANITIZE_STRING );
		$_name = filter_input( INPUT_POST, 'name', FILTER_SANITIZE_STRING );
		$_description = filter_input( INPUT_POST, 'description', FILTER_SANITIZE_STRING );

		$return['error'] = false;
		if ( wp_verify_nonce( $nonce, 'rtmedia_create_album_nonce' ) && isset( $_name ) && $_name && is_rtmedia_album_enable() ) {
			$_context    = filter_input( INPUT_POST, 'context', FILTER_SANITIZE_STRING );
			$_context_id = filter_input( INPUT_POST, 'context_id', FILTER_SANITIZE_NUMBER_INT );
			if ( ! empty( $_context ) && 'group' === $_context ) {
				$group_id = ! empty( $_context_id ) ? $_context_id : '';
				if ( false === can_user_create_album_in_group( $group_id ) ) {
					$return['error'] = esc_html__( 'You can not create album in this group.', 'buddypress-media' );
				}
			}

			$create_album = apply_filters( 'rtm_is_album_create_enable', true );
			if ( ! $create_album ) {
				$return['error'] = esc_html__( 'You can not create album.', 'buddypress-media' );
			}

			$create_album = apply_filters( 'rtm_display_create_album_button', true, $_context_id );
			if ( ! $create_album ) {
				$return['error'] = esc_html__( 'You can not create more albums, you exceed your album limit.', 'buddypress-media' );
			}

			if ( false !== $return['error'] ) {
				wp_send_json( $return );
			}

			$album = new RTMediaAlbum();

			// setup context values
			$context = $_context;
			if ( 'profile' === $context ) {
				$context_id = get_current_user_id();
			} else {
				$context_id = ( ! empty( $_context_id ) ? $_context_id : 0 );
			}

			// setup new album data
			$album_data = apply_filters( 'rtmedia_create_album_data', array(
				'title'             => $_name,
				'author'            => get_current_user_id(),
				'new'               => true,
				'post_id'           => false,
				'context'           => $context,
				'context_id'        => $context_id,
				'album_description' => $_description,
			) );

			$rtmedia_id = $album->add( $album_data['title'], $album_data['author'], $album_data['new'], $album_data['post_id'], $album_data['context'], $album_data['context_id'], $album_data['album_description'] );

			$rtmedia_nav = new RTMediaNav();

			if ( 'group' === $_context ) {
				$rtmedia_nav->refresh_counts( $_context_id, array(
					'context'    => $_context,
					'context_id' => $_context_id,
				) );
			} else {
				$rtmedia_nav->refresh_counts( get_current_user_id(), array(
					'context'      => 'profile',
					'media_author' => get_current_user_id(),
				) );
			}

			if ( $rtmedia_id ) {
				$return['album'] = apply_filters( 'rtmedia_create_album_response', $rtmedia_id );
				wp_send_json( $return );
			} else {
				echo false;
			}
		} else {
			$return['error'] = esc_html__( 'Data mismatch, Please insert data properly.', 'buddypress-media' );
			wp_send_json( $return );
		}

		wp_die();
	}
}
