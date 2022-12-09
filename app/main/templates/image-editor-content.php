<?php
/**
 * Template for - rtmedia_image_editor_content().
 *
 * @package rtMedia
 */

?>

<div class="content" id="panel2">

	<div class="rtmedia-image-editor-cotnainer" id="rtmedia-image-editor-cotnainer">

		<input type="hidden" id="rtmedia-filepath-old" name="rtmedia-filepath-old" value="<?php echo esc_url( $image_path ); ?>" />

		<div class="rtmedia-image-editor" id="image-editor-<?php echo esc_attr( $media_id ); ?>"></div>

		<?php $thumb_url = wp_get_attachment_image_src( $media_id, 'thumbnail', true ); ?>

		<div id="imgedit-response-<?php echo esc_attr( $media_id ); ?>"></div>

		<div class="wp_attachment_image" id="media-head-<?php echo esc_attr( $media_id ); ?>">

			<p id="thumbnail-head-<?php echo esc_attr( $media_id ); ?>">
				<img class="thumbnail" src="<?php echo esc_url( set_url_scheme( $thumb_url[0] ) ); ?>"
					alt="<?php echo esc_attr( rtmedia_title() ); ?>" />
			</p>

			<?php echo wp_kses( $modify_button, RTMedia::expanded_allowed_tags() ); ?>

		</div>

	</div>

</div>
