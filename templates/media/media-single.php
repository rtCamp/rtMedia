<div class="rt-media-container rt-media-single-container row">

    <?php if (have_rt_media()) : rt_media(); ?>

	    <form method="post" class="large-8 columns">
			<?php echo rt_media_title_input(); ?>


			<?php if( rt_media_request_action()!="edit" ) { ?>
				<div class="rt-media-media">
					<img src="<?php rt_media_image('large','src'); ?>">
				</div>
			<?php } ?>

			<?php
			if(is_user_logged_in() && rt_media_edit_allowed()) {
				if( rt_media_request_action()!="edit" ) { ?>
					<a href="<?php echo rt_media_permalink() . 'edit/'; ?>"><button type="button">Edit</button></a>
				<?php } else {
					rt_media_image_editor();
				}
			}?>

			<?php
				if( rt_media_delete_allowed() && rt_media_request_action()!="edit" ) {
					rt_media_delete_form();
				}

				echo rt_media_description_input();
				if (rt_media_request_action() == "edit") {
					RTMediaMedia::media_nonce_generator(rt_media_id());
			?>
					<input type="submit" value="Save">
					<a href="<?php rt_media_permalink(); ?>"><input type="button" value="Back"></a>
			<?php } ?>
		</form>

        <div class="rt-media-item-actions">
            <?php rt_media_actions(); ?>
        </div>

		<?php //if(rt_media_comments_enabled()) { ?>
			<div class="rt-media-item-comments columns large-4">
				<h2>Comments</h2>
				<div class="rt-media-comments-container">
					<?php rt_media_comments(); ?>
				</div>
				<?php rt_media_comment_form(); ?>
			</div>
		<?php //} ?>

    <?php else: ?>
        <p><?php echo __("Oops !! There's no media found for the request !!","rt-media"); ?></p>
    <?php endif; ?>

</div>
