<div class="rt-media-container rt-media-single-container">

    <?php if (have_rt_media()) : rt_media(); ?>

		<?php if( (rt_media_edit_allowed() && rt_media_request_action()!="edit") ) { ?>
			<div class="rt-media-media">
				<img src="<?php rt_media_image('full','src'); ?>" width="<?php rt_media_image('full','width'); ?>" height="<?php rt_media_image('full','height'); ?>">
			</div>
		<?php } ?>
		<?php if( rt_media_edit_allowed() && rt_media_request_action()!="edit" ) { ?>
			<a href="<?php echo rt_media_url() . '/edit'; ?>">Edit</a>
		<?php } else {
			RTMediaTemplate::enqueue_image_editor_scripts();
			global $rt_media_query;
			$media_id = $rt_media_query->media[0]->media_id;
			$id = $rt_media_query->media[0]->id;
            //$editor = wp_get_image_editor(get_attached_file($id));
            include_once( ABSPATH . 'wp-admin/includes/image-edit.php' );
            echo '<div class="rt-media-image-editor-cotnainer">';
            echo '<div class="rt-media-image-editor" id="image-editor-' . $media_id . '"></div>';
            $thumb_url = wp_get_attachment_image_src($media_id, 'thumbnail', true);
            $nonce = wp_create_nonce("image_editor-$media_id");
            echo '<div id="imgedit-response-' . $media_id . '"></div>';
            echo '<div class="wp_attachment_image" id="media-head-' . $media_id . '">
                        <p id="thumbnail-head-' . $id . '"><img class="thumbnail" src="' . set_url_scheme($thumb_url[0]) . '" alt="" /></p>
			<p><input type="button" class="rt-media-image-edit" id="imgedit-open-btn-' . $media_id . '" onclick="imageEdit.open( \'' . $media_id . '\', \'' . $nonce . '\' )" class="button" value="Modifiy Image"> <span class="spinner"></span></p>
		</div>';
            echo '</div>';
		} ?>
		<?php if( rt_media_delete_allowed() && rt_media_request_action()!="edit" ) { ?>

			<form method="post">
				<input type="hidden" name="id" id="id" value="<?php echo rt_media_id(); ?>">
				<input type="hidden" name="request_action" id="request_action" value="delete">
				<?php RTMediaMedia::media_nonce_generator(true); ?>
				<input type="submit" value="Delete">
			</form>
		<?php } ?>
        <div class="rt-media-item-content">
            <?php rt_media_content(); ?>
        </div>

        <div class="rt-media-item-actions">
            <?php rt_media_actions(); ?>
        </div>

		<?php if(rt_media_comments_enabled()) { ?>
			<div class="rt-media-item-comments">
				<h2>Comments</h2>
				<div class="rt-media-container">
					<?php rt_media_comments(); ?>
				</div>
				<form method="post" action="<?php echo rt_media_url(). '/comments'; ?>" style="width: 400px;">
					<textarea rows="4" name="comment_content" id="comment_content"></textarea>
					<input type="submit" value="Comment">
					<?php RTMediaComment::comment_nonce_generator(); ?>
				</form>
			</div>
		<?php } ?>

    <?php else: ?>
        <p><?php echo __("Oops !! There's no media found for the request !!","rt-media"); ?></p>
    <?php endif; ?>

</div>