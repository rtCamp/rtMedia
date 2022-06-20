<?php
/**
 * Template for - rtmedia_merge_album_modal().
 *
 * @package rtMedia
 */

?>

<div class="rtmedia-merge-container rtmedia-popup mfp-hide" id="rtmedia-merge">
	<div id="rtm-modal-container">
		<h2 class="rtm-modal-title"><?php esc_html_e( 'Merge Album', 'buddypress-media' ); ?></h2>
		<form method="post" class="album-merge-form" action="merge/">
			<p>
				<span><?php esc_html_e( 'Select Album to merge with : ', 'buddypress-media' ); ?></span>
				<select name="album" class="rtmedia-merge-user-album-list"><?php echo wp_kses( $album_list, RTMedia::expanded_allowed_tags() ); ?></select>
			</p>
			<?php wp_nonce_field( 'rtmedia_merge_album_' . $rtmedia_query->media_query['album_id'], 'rtmedia_merge_album_nonce' ); ?>
			<input type="submit" class="rtmedia-merge-selected" name="merge-album" value="<?php esc_html_e( 'Merge Album', 'buddypress-media' ); ?>"/>
		</form>
	</div>
</div>
