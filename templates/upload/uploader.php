<div class="bp-media-uploader no-js">
    <form method="post" action="upload" enctype="multipart/form-data">
        <?php do_action('bp_media_before_uploader'); ?>

        <?php if ($is_url) { ?>
            <input type="url" name="bp-media-url" class="bp-media-upload-input bp-media-url" />
        <?php } else { ?>
            <input type="file" name="bp-media-file" class="bp-media-upload-input bp-media-file" />
        <?php } ?>

        <?php do_action('bp_media_after_uploader'); ?>
            
        <input type="submit" name="bp-media-upload" value="<?php echo BP_MEDIA_UPLOAD_LABEL; ?>" />
    </form>
</div>