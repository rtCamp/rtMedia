<li class="rt-media-list-item">
    <div class="rt-media-item-thumbnail">
        <a href ="<?php rt_media_permalink(); ?>">
            <img src="<?php rt_media_image('thumbnail'); ?>">
        </a>
    </div>

    <div class="rt-media-item-title">
        <h4 title="<?php echo rt_media_title(); ?>">
            <a href="<?php rt_media_permalink(); ?>">
                <?php echo rt_media_title(); ?>
            </a>
        </h4>
    </div>

</li>