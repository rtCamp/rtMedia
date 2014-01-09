<?php
global $rtmedia_query;

$model = new RTMediaModel();

$media = $model->get_media(array('id' => $rtmedia_query->media_query['album_id']), false, false);
global $rtmedia_media;
$rtmedia_media = $media[0];
?>
<div class="rtmedia-container rtmedia-single-container row rtmedia-media-edit">

        <h2><?php echo __ ( 'Edit Album : ' , 'rtmedia' ) . esc_attr($media[0]->media_title) ; ?></h2>

        <div class="rtmedia-edit-media-tabs rtmedia-editor-main columns large-12 small">
            <dl class="tabs" data-tab>
                <dd class="active"><a href="#details-tab"><i class='rtmicon-edit'></i><?php _e('Details', 'rtmedia') ;?></a></dd>
                <?php if(!is_rtmedia_group_album()) { ?>
                <dd class=""><a href="#manage-media-tab"><i class='rtmicon-edit'></i><?php _e('Manage Media', 'rtmedia') ;?></a></dd>
                <?php } ?>
                <!-- use this hook to add title of a new tab-->
                <?php do_action('rtmedia_add_edit_tab_title', 'album');?>
            </dl>
            
            <div class="tabs-content">
                <div class="content active" id="details-tab">
                    <form method="post">
                    <?php
                        RTMediaMedia::media_nonce_generator($rtmedia_query->media_query['album_id']);
                        $post_details = get_post($media[0]->media_id);
                        $content = apply_filters('the_content', $post_details->post_content);
                        $content = $post_details->post_content;
                    ?>
                    <div class="rtmedia-edit-title">
                        <label for="media_title"><?php _e('Title : ', 'rtmedia'); ?></label><?php rtmedia_title_input(); ?>
                    </div>
                    <?php do_action("rtmedia_add_album_privacy", 'album-edit'); ?>

                    <div class="rtmedia-editor-description">
                        <label for='description'><?php _e('Description: ', 'rtmedia') ?></label>
                        <?php

                                echo rtmedia_description_input( $editor = false);
                                        RTMediaMedia::media_nonce_generator(rtmedia_id());
                        ?>
                    </div>
                    <input type="submit" name="submit" class='rtmedia-save-album' value="<?php _e('Save Changes', 'rtmedia'); ?>" />
                   </form>
                </div>

                <!--media management tab-->
                <?php if(!is_rtmedia_group_album()) { ?>
                    
                <div class="content" id="manage-media-tab">
                    <?php if (have_rtmedia()) { ?>
                        <form class="rtmedia-album-edit rtmedia-bulk-actions" method="post" name="rtmedia_album_edit">
                            <?php wp_nonce_field('rtmedia_bulk_delete_nonce', 'rtmedia_bulk_delete_nonce'); ?>
                            <?php RTMediaMedia::media_nonce_generator($rtmedia_query->media_query['album_id']); ?>
                            <button type='button' class="select-all" title="<?php echo __('Select All Visible','rtmedia'); ?>"><i class='rtmicon-square-o'></i></button>
                            <button class="button rtmedia-move" type='button' title='<?php echo __('Move Selected media to another album.');?>' ><i class='rtmicon-move'></i> <?php _e('Move','rtmedia'); ?></button>
                            <button type="button" name="delete-selected" class="button rtmedia-delete-selected" title='<?php echo __('Delete Selected media from the album.');?>'><i class='rtmicon-trash'></i><?php _e('Delete','rtmedia'); ?></button>
                            <div class="rtmedia-move-container">
                                <?php $global_albums = rtmedia_get_site_option('rtmedia-global-albums'); ?>
                                <?php _e('Move selected media to the album : ', 'rtmedia'); ?>
                                <?php echo '<select name="album" class="rtmedia-user-album-list">'.rtmedia_user_album_list().'</select>'; ?>
                                <input type="button" class="rtmedia-move-selected" name="move-selected" value="<?php _e('Move Selected','rtmedia'); ?>" />
                            </div>


                            <ul class="rtmedia-list  large-block-grid-4 ">

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
                            <a id="rtMedia-galary-prev" <?php echo $display; ?> href="<?php echo rtmedia_pagination_prev_link(); ?>"><?php _e('Prev','rtmedia'); ?></a>

                            <?php
                            $display = '';
                            if (rtmedia_offset() + rtmedia_per_page_media() < rtmedia_count())
                                $display = 'style="display:block;"';
                            else
                                $display = 'style="display:none;"';
                            ?>
                            <a id="rtMedia-galary-next" <?php echo $display; ?> href="<?php echo rtmedia_pagination_next_link(); ?>"><?php _e('Next','rtmedia'); ?></a>
                        </form>
                        <?php } else { ?>
                            <p><?php _e('The album is empty.', 'rtmedia'); ?></p>
                        <?php } ?>
                </div>
                    <?php } ?>
            
            <!-- use this hook to add content of a new tab-->
            <?php do_action('rtmedia_add_edit_tab_content', 'album');?>
            </div>
        </div>

</div>