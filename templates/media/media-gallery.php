<div class="rt-media-container">
    <?php do_action('rtmedia_before_media_gallery'); ?>

    <?php rt_media_uploader() ?>

    <h2><?php echo __('Media Gallery', 'rt-media'); ?></h2>
    <ul class="rt-media-list large-block-grid-5">
        <?php if (have_rt_media()) { ?>



            <?php while (have_rt_media()) : rt_media(); ?>

                <?php include ('media-gallery-item.php'); ?>

            <?php endwhile; ?>

        </ul>


        <!--  these links will be handled by backbone later
                                        -- get request parameters will be removed  -->
        <?php
        $display = '';
        if (rt_media_offset() != 0)
            $display = 'style="display:block;"';
        else
            $display = 'style="display:none;"';
        ?>
        <a id="rtMedia-galary-prev" <?php echo $display; ?> href="<?php echo rt_media_pagination_prev_link(); ?>"><?php echo __('Prev','rt-media'); ?></a>

        <?php
        $display = '';
        if (rt_media_offset() + rt_media_per_page_media() < rt_media_count())
            $display = 'style="display:block;"';
        else
            $display = 'style="display:none;"';
        ?>
        <a id="rtMedia-galary-next" <?php echo $display; ?> href="<?php echo rt_media_pagination_next_link(); ?>"><?php echo __('Next','rt-media'); ?></a>

    <?php } else { ?>
        <p><?php echo __("Oops !! There's no media found for the request !!", "rt-media"); ?></p>
    <?php } ?>
</ul>
<?php do_action('rtmedia_after_media_gallery'); ?>

</div>
