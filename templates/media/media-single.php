<div class="rt-media-container rt-media-single-container">

    <?php if (have_rt_media()) : rt_media(); ?>

		<?php if( rt_media_request_action()!="edit" ) { ?>
			<div class="rt-media-media">
				<img src="<?php rt_media_image('full','src'); ?>" width="<?php rt_media_image('full','width'); ?>" height="<?php rt_media_image('full','height'); ?>">
			</div>
		<?php } ?>

		<?php
		if(is_user_logged_in()) {
			if( rt_media_edit_allowed() && rt_media_request_action()!="edit" ) { ?>
				<a href="<?php echo rt_media_url() . '/edit'; ?>"><button>Edit</button></a>
			<?php } else {
				rt_media_image_editor();
			}
		}?>

		<?php
			if( rt_media_delete_allowed() && rt_media_request_action()!="edit" ) {
				rt_media_delete_form();
		} ?>

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
				<?php rt_media_comment_form(); ?>
			</div>
		<?php } ?>

    <?php else: ?>
        <p><?php echo __("Oops !! There's no media found for the request !!","rt-media"); ?></p>
    <?php endif; ?>

</div>