<div class="rt-media-container rt-media-single-container row">

    <?php if (have_rt_media()) : rt_media(); ?>

	<?php
	if( rt_media_delete_allowed() ) {
		rt_media_delete_form();
	}
	?>

	    <form method="post" action="">
			<div class="rt-media-editor-main columns large-6 small">
			<?php echo rt_media_title_input(); ?>

			<?php do_action('rt_media_add_edit_fields', rt_media_type()); ?>

			</div>
			<div class="rt-media-editor-description columns large-6 small">
			<?php

				echo rt_media_description_input();
					RTMediaMedia::media_nonce_generator(rt_media_id());
			?>
			</div>
			<div class="rt-media-editor-buttons columns large-12 small">

					<input type="submit" value="Save">
					<a href="<?php rt_media_permalink(); ?>"><input type="button" value="Back"></a>
			</div>
			</form>


    <?php else: ?>
        <p><?php echo __("Oops !! There's no media found for the request !!","rt-media"); ?></p>
    <?php endif; ?>

</div>
