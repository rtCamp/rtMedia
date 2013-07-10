<li class="rtmedia-list-item">
    <div class="rtmedia-item-thumbnail">
        <a href ="<?php rtmedia_permalink(); ?>">
            <img src="<?php rtmedia_image('thumbnail'); ?>" >
        </a>
    </div>

    <div class="rtmedia-item-title">
        <h4 title="<?php echo rtmedia_title(); ?>">
            <a href="<?php rtmedia_permalink(); ?>">
                <?php echo rtmedia_title(); ?>
            </a>
        </h4>
    </div>

</li>