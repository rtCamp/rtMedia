<div class="rtmedia-container rtmedia-single-container">
    <div class="row">
        <?php if (have_rtmedia()) : rtmedia(); ?>

            <div id="rtmedia-single-media-container" class="rtmedia-single-media columns large-9">

                <?php rtmedia_title(); ?>


                <div class="rtmedia-media flex-video" id ="rtmedia-media-<?php echo rtmedia_id(); ?>">
                    <?php rtmedia_media(true); ?>
                </div>

            </div>
            <div class="rtmedia-single-meta columns large-3">

                <?php rtmedia_description(); ?>

                <div class="rtmedia-item-actions">
                    <?php rtmedia_actions(); ?>
                </div>

                <?php if (rtmedia_comments_enabled()) { ?>
                    <div class="rtmedia-item-comments row">
                        <div class="large-12 columns">
                            <h2>Comments</h2>
                            <div class="rtmedia-comments-container">
                                <?php rtmedia_comments(); ?>
                            </div>
                            <?php rtmedia_comment_form(); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>

        <?php else: ?>
            <p><?php echo __("Oops !! There's no media found for the request !!", "rtmedia"); ?></p>
        <?php endif; ?>

    </div>
</div>
