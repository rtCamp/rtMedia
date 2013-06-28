<div class="rt-media-container rt-media-single-container row">

    <?php if (have_rt_media()) : rt_media(); ?>

	<div class="rt-media-single-media columns large-8 small">

			<?php rt_media_title(); ?>


				<div class="rt-media-media">
					<?php rt_media_media(); ?>
				</div>

	</div>
	<div class="rt-media-single-meta columns large-4">

			<?php rt_media_description(); ?>

			<?php

			if(is_user_logged_in() && rt_media_edit_allowed()) {
			?>
					<a href="<?php echo rt_media_permalink() . 'edit/'; ?>"><button type="button"><?php echo __('Edit','rt-media'); ?></button></a>
			<?php
			}?>

			<?php
				if( rt_media_delete_allowed() ) {
					rt_media_delete_form();
				}
			?>
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
	</div>

    <?php else: ?>
        <p><?php echo __("Oops !! There's no media found for the request !!","rt-media"); ?></p>
    <?php endif; ?>

</div>
