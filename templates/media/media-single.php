<div class="rt-media-container rt-media-single-container">

    <?php if (have_rt_media()) : rt_media(); ?>

        <?php
            global $rt_media;
            $size = $rt_media->options['sizes']['image']['large'];
        ?>

        <div class="rt-media-media">
            <img src="<?php rt_media_thumbnail(); ?>" width="<?php echo $size['width']; ?>">
        </div>

        <h4 class="rt-media-item-title" title="<?php rt_media_title(); ?>">
            <?php rt_media_title(); ?>
        </h4>

        <div class="rt-media-item-content">
            <?php rt_media_content(); ?>
        </div>

        <div class="rt-media-item-actions">
            <?php rt_media_actions(); ?>
        </div>

        <?php rt_media_comments(); ?>

    <?php else: ?>
        <p><?php echo __("Oops !! There's no media found for the request !!","rt-media"); ?></p>
    <?php endif; ?>

</div>