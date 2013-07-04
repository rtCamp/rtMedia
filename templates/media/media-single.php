<div class="rtmedia-container rtmedia-single-container">
    <div class="row">
        <?php if (have_rtmedia()) : rtmedia(); ?>

            <div class="rtmedia-single-media columns large-9">

                <?php rtmedia_title(); ?>


                <div class="rtmedia-media flex-video">
                    <?php rtmedia_media(true); ?>
                </div>

            </div>
            <div class="rtmedia-single-meta columns large-3">

                <?php rtmedia_description(); ?>

                <?php
                if (is_user_logged_in() && rtmedia_edit_allowed()) {
                    ?>
                    <a href="<?php echo rtmedia_permalink() . 'edit/'; ?>"><button type="button"><?php echo __('Edit', 'rtmedia'); ?></button></a>
                    <?php }
                ?>

                <?php
                if (rtmedia_delete_allowed()) {
                    rtmedia_delete_form();
                }
                ?>
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
