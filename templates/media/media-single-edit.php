<div class="rt-media-container rt-media-single-container row">

    <?php if (have_rt_media()) : rt_media(); ?>

	    <form method="post" class="large-8 columns" action="">
			<?php echo rt_media_title_input(); ?>


				<div class="rt-media-media">
					<img src="<?php rt_media_image('large','src'); ?>">
				</div>

                <?php do_action('rt_media_add_edit_fields', rt_media_type()); ?>

			<?php
				if( rt_media_delete_allowed() ) {
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


    <?php else: ?>
        <p><?php echo __("Oops !! There's no media found for the request !!","rt-media"); ?></p>
    <?php endif; ?>

</div>
