<?php

/**
 * Delete uploaded media
 */
function rtmedia_delete_uploaded_media() {

	$action   = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );
	$nonce    = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
	$media_id = filter_input( INPUT_POST, 'media_id', FILTER_SANITIZE_NUMBER_INT );

	if ( ! empty( $action ) && 'delete_uploaded_media' === $action && ! empty( $media_id ) ) {
		if ( wp_verify_nonce( $nonce, 'rtmedia_' . get_current_user_id() ) ) {
			$media  = new RTMediaMedia();
			$delete = $media->delete( $media_id );

			echo '1';

			wp_die();
		}
	}

	echo '0';

	wp_die();

}

add_action( 'wp_ajax_delete_uploaded_media', 'rtmedia_delete_uploaded_media' );
