<?php
/**
 * Template for - rtmedia_create_album_modal().
 *
 * @package rtMedia
 */

?>

<div class="mfp-hide rtmedia-popup" id="rtmedia-create-album-modal">
	<div id="rtm-modal-container">
		<?php do_action( 'rtmedia_before_create_album_modal' ); ?>
		<h2 class="rtm-modal-title"><?php esc_html_e( 'Create an Album', 'buddypress-media' ); ?></h2>
		<p>
			<label class="rtm-modal-grid-title-column" for="rtmedia_album_name"><?php esc_html_e( 'Album Title : ', 'buddypress-media' ); ?></label>
			<input type="text" id="rtmedia_album_name" value="" class="rtm-input-medium" />
		</p>
		<p>
			<label class="rtm-modal-grid-title-column" for="rtmedia_album_description"><?php esc_html_e( 'Album Description : ', 'buddypress-media' ); ?></label>
			<textarea type="text" id="rtmedia_album_description" value="" class="rtm-input-medium"></textarea>
		</p>
		<?php do_action( 'rtmedia_add_album_privacy' ); ?>
		<input type="hidden" id="rtmedia_album_context" value="<?php echo esc_attr( $rtmedia_query->query['context'] ); ?>">
		<input type="hidden" id="rtmedia_album_context_id" value="<?php echo esc_attr( $rtmedia_query->query['context_id'] ); ?>">
		<?php wp_nonce_field( 'rtmedia_create_album_nonce', 'rtmedia_create_album_nonce' ); ?>
		<p>
			<button type="button" id="rtmedia_create_new_album"><?php esc_html_e( 'Create Album', 'buddypress-media' ); ?></button>
		</p>
		<?php do_action( 'rtmedia_after_create_album_modal' ); ?>
	</div>
</div>
