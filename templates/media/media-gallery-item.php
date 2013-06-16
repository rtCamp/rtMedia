<li class="rt-media-list-item">
    <div class="rt-media-item-thumbnail">
        <a href ="<?php rt_media_permalink(); ?>">
            <?php
                global $rt_media;
                $size = $rt_media->options['sizes']['image']['thumbnail'];
            ?>
            <img src="<?php rt_media_thumbnail(); ?>" width="<?php echo $size['width']; ?>">
        </a>
    </div>

    <div class="rt-media-item-title">
        <h4 title="<?php rt_media_title(); ?>">
            <a href="<?php rt_media_permalink(); ?>">
                <?php rt_media_title(); ?>
            </a>
        </h4>
    </div>

    <div class="rt-media-item-actions">
        <?php rt_media_actions(); ?>
    </div>
</li>