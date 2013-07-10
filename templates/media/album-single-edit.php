<?php
global $rtmedia_query;

$model = new RTMediaModel();

$media = $model->get_media(array('id' => $rtmedia_query->media_query['album_id']), false, false);
?>
<div class="rtmedia-container rtmedia-single-container">

    <form method="post">
        <?php
        RTMediaMedia::media_nonce_generator($rtmedia_query->media_query['album_id']);
        $post_details = get_post($media[0]->media_id);
        $content = apply_filters('the_content', $post_details->post_content);
        ?>

        <input type="text" name="media_title" value="<?php echo esc_attr($media[0]->media_title); ?>" />
        <?php wp_editor($content, 'description', array('media_buttons' => false)); ?>
        <input type="submit" name="submit" value="Submit" />

    </form>
    <?php if (have_rtmedia()) { ?>
        <br />
        <form class="rtmedia-bulk-actions" method="post">
            <?php wp_nonce_field('rtmedia_bulk_delete_nonce', 'rtmedia_bulk_delete_nonce'); ?>
            <?php RTMediaMedia::media_nonce_generator($rtmedia_query->media_query['album_id']); ?>
            <span class="rtmedia-selection"><a class="select-all" href="#"><?php echo __('Select All Visible','rtmedia'); ?></a> |
                <a class="unselect-all" href="#"><?php echo __('Unselect All Visible','rtmedia'); ?></a> | </span>
            <br />
            <input type="button" class="button rtmedia-move" value="<?php echo __('Move','rtmedia'); ?>" />
            <input type="submit" name="delete-selected" class="button rtmedia-delete-selected" value="<?php echo __('Delete Selected','rtmedia'); ?>" />
            <div class="rtmedia-move-container">
                <?php $global_albums = get_site_option('rtmedia-global-albums'); ?>
                <?php _e('Move selected media to', 'rtmedia'); ?>
                <?php echo '<select name="album" class="rtmedia-user-album-list">'.rtmedia_user_album_list().'</select>'; ?>
                <input type="submit" class="rtmedia-move-selected" name="move-selected" value="Move Selected" />
            </div>


            <ul class="rtmedia-list  large-block-grid-4">

                <?php while (have_rtmedia()) : rtmedia(); ?>

                    <?php include ('media-gallery-item.php'); ?>

                <?php endwhile; ?>

            </ul>


            <!--  these links will be handled by backbone later
                                            -- get request parameters will be removed  -->
            <?php
            $display = '';
            if (rtmedia_offset() != 0)
                $display = 'style="display:block;"';
            else
                $display = 'style="display:none;"';
            ?>
            <a id="rtMedia-galary-prev" <?php echo $display; ?> href="<?php echo rtmedia_pagination_prev_link(); ?>"><?php echo __('Prev','rtmedia'); ?></a>

            <?php
            $display = '';
            if (rtmedia_offset() + rtmedia_per_page_media() < rtmedia_count())
                $display = 'style="display:block;"';
            else
                $display = 'style="display:none;"';
            ?>
            <a id="rtMedia-galary-next" <?php echo $display; ?> href="<?php echo rtmedia_pagination_next_link(); ?>"><?php echo __('Next','rtmedia'); ?></a>

        <?php } else { ?>
            <p><?php echo __("The album is empty.", "rtmedia"); ?></p>
        <?php } ?>
    </form>


</div>