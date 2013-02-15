<?php

class BPMediaTemplateFunctions {

    /**
     * 
     * @global type $bp
     * @global type $bp_media_default_excerpts
     * @global type $bp_media_options
     */
    function show_upload_form() {
        global $bp, $bp_media_default_excerpts, $bp_media_options;
        $allowed = array(
            'type' => array(),
            'accept' => array()
        );
        if ($bp_media_options['images_enabled']) {
            $allowed['type'][] = 'image';
            $allowed['accept'][] = 'image/*';
        }
        if ($bp_media_options['audio_enabled']) {
            $allowed['type'][] = 'audio';
            $allowed['accept'][] = 'audio/mp3';
        }
        if ($bp_media_options['videos_enabled']) {
            $allowed['type'][] = 'video';
            $allowed['accept'][] = 'video/mp4';
            $allowed['accept'][] = 'video/quicktime';
        }
        $allowed = apply_filters('bp_media_allowed_filter', $allowed);
        $accept = implode(',', $allowed['accept']);
        ?>
        <form method="post" enctype="multipart/form-data" class="standard-form" id="bp-media-upload-form">
            <label for="bp-media-upload-input-title"><?php _e('Media Title', BP_MEDIA_TXT_DOMAIN); ?></label><input id="bp-media-upload-input-title" type="text" name="bp_media_title" class="settings-input" maxlength="<?php echo max(array($bp_media_default_excerpts['single_entry_title'], $bp_media_default_excerpts['activity_entry_title'])) ?>" />
            <label for="bp-media-upload-input-description"><?php _e('Media Description', BP_MEDIA_TXT_DOMAIN); ?></label><input id="bp-media-upload-input-description" type="text" name="bp_media_description" class="settings-input" maxlength="<?php echo max(array($bp_media_default_excerpts['single_entry_description'], $bp_media_default_excerpts['activity_entry_description'])) ?>" />
            <label for="bp-media-upload-file"><?php _e('Select Media File', BP_MEDIA_TXT_DOMAIN) ?> (Max File Size:<?php echo min(array(ini_get('upload_max_filesize'), ini_get('post_max_size'))); ?> , Allowed types: <?php echo implode(', ', $allowed['type']) ?>)</label><input type="file" name="bp_media_file" id="bp-media-upload-file" accept="<?php echo $accept ?>" />
            <input type="hidden" name="action" value="wp_handle_upload" />
            <div class="submit"><input type="submit" class="auto" value="Upload" /></div>
        </form>
        <?php
    }

    //------------- Function removed show_upload_form_multiple() ------------
    /**
     * 
     * @global type $bp
     * @global type $bp_media_paginated_links
     * @global type $bp_media_query
     * @global type $bp_media_albums_query
     * @param type $type
     * @param type $inner
     */
    function show_pagination($type = 'top', $inner = false) {
        global $bp, $bp_media_paginated_links, $bp_media_query, $bp_media_albums_query;
        switch ($bp->current_action) {
            case BP_MEDIA_IMAGES_SLUG :
                $current = $bp_media_query->found_posts > 1 ? BP_MEDIA_IMAGES_LABEL : BP_MEDIA_IMAGES_LABEL_SINGULAR;
                $current_single = BP_MEDIA_IMAGES_LABEL_SINGULAR;
                break;
            case BP_MEDIA_VIDEOS_SLUG :
                $current = $bp_media_query->found_posts > 1 ? BP_MEDIA_VIDEOS_LABEL : BP_MEDIA_VIDEOS_LABEL_SINGULAR;
                $current_single = BP_MEDIA_VIDEOS_LABEL_SINGULAR;
                break;
            case BP_MEDIA_AUDIO_SLUG :
                $current = BP_MEDIA_AUDIO_LABEL;
                $current_single = BP_MEDIA_AUDIO_LABEL_SINGULAR;
                break;
            case BP_MEDIA_ALBUMS_SLUG:
                $current = BP_MEDIA_ALBUMS_LABEL;
                $current_single = BP_MEDIA_ALBUMS_LABEL_SINGULAR;
                break;
            default :
                $current = BP_MEDIA_LABEL;
                $current_single = BP_MEDIA_LABEL_SINGULAR;
        }

        if ($bp->current_action == BP_MEDIA_ALBUMS_SLUG && !$inner) {
            $args = array(
                'base' => trailingslashit(bp_displayed_user_domain() . $bp->current_action . '/') . '%_%',
                'format' => 'page/%#%',
                'total' => $bp_media_albums_query->max_num_pages,
                'current' => $bp_media_albums_query->query_vars['paged'],
                'type' => 'array',
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
            );
            $start_num = intval($bp_media_albums_query->query_vars['posts_per_page'] * ($bp_media_albums_query->query_vars['paged'] - 1)) + 1;
            $from_num = $start_num;
            $to_num = $start_num + $bp_media_albums_query->post_count - 1;
            $total = $bp_media_albums_query->found_posts;
            $bp_media_paginated_links = paginate_links($args);
        } else {
            if ($inner) {
                $current = BP_MEDIA_LABEL;
                $current_single = BP_MEDIA_LABEL_SINGULAR;
                $args = array(
                    'base' => trailingslashit(bp_displayed_user_domain() . $bp->current_action . '/' . $bp->action_variables[1]) . '%_%',
                    'format' => 'page/%#%',
                    'total' => $bp_media_query->max_num_pages,
                    'current' => $bp_media_query->query_vars['paged'],
                    'type' => 'array',
                    'prev_text' => '&larr;',
                    'next_text' => '&rarr;',
                );
            } else {
                $args = array(
                    'base' => trailingslashit(bp_displayed_user_domain() . $bp->current_action . '/') . '%_%',
                    'format' => 'page/%#%',
                    'total' => $bp_media_query->max_num_pages,
                    'current' => $bp_media_query->query_vars['paged'],
                    'type' => 'array',
                    'prev_text' => '&larr;',
                    'next_text' => '&rarr;',
                );
            }
            $start_num = intval($bp_media_query->query_vars['posts_per_page'] * ($bp_media_query->query_vars['paged'] - 1)) + 1;
            $from_num = $start_num;
            $to_num = $start_num + $bp_media_query->post_count - 1;
            $total = $bp_media_query->found_posts;
            $bp_media_paginated_links = paginate_links($args);
        }
        ?>
        <div id="pag-<?php echo $type; ?>" class="pagination no-ajax">
            <div class="pag-count">
                Viewing <?php echo $current_single ?> <?php echo $from_num ?> to <?php echo $to_num ?> (of <?php echo $total; ?> <?php echo $current ?>)
            </div>
            <div class="pagination-links">
                <?php if (is_array($bp_media_paginated_links)) : foreach ($bp_media_paginated_links as $link) : ?>
                        <?php echo $link; ?>
                    <?php endforeach;
                endif;
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * 
     * @param type $id
     * @return boolean
     */
    function get_permalink($id = 0) {
        if (is_object($id))
            $media = $id;
        else
            $media = &get_post($id);
        if (empty($media->ID))
            return false;
        if (!$media->post_type == 'bp_media')
            return false;
        switch (get_post_meta($media->ID, 'bp_media_type', true)) {
            case 'video' :
                return trailingslashit(bp_displayed_user_domain() . BP_MEDIA_VIDEOS_SLUG . '/' . BP_MEDIA_VIDEOS_ENTRY_SLUG . '/' . $media->ID);
                break;
            case 'audio' :
                return trailingslashit(bp_displayed_user_domain() . BP_MEDIA_AUDIO_SLUG . '/' . BP_MEDIA_AUDIO_ENTRY_SLUG . '/' . $media->ID);
                break;
            case 'image' :
                return trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG . '/' . BP_MEDIA_IMAGES_ENTRY_SLUG . '/' . $media->ID);
                break;
            default :
                return false;
        }
    }

    function the_permalink() {
        echo apply_filters('the_permalink', $this->get_permalink());
    }

    function the_content($id = 0) {
        if (is_object($id))
            $media = $id;
        else
            $media = &get_post($id);
        if (empty($media->ID))
            return false;
        if ($media->post_type != 'bp_media')
            return false;
        try {
            $media = new BPMediaHostWordpress($media->ID);
            echo $media->get_media_gallery_content();
        } catch (Exception $e) {
            echo '';
        }
    }

    /**
     * 
     * @param type $id
     * @return boolean
     */
    function album_the_content($id = 0) {
        if (is_object($id))
            $album = $id;
        else
            $album = &get_post($id);
        if (empty($album->ID))
            return false;
        if (!$album->post_type == 'bp_media_album')
            return false;
        try {
            $album = new BPMediaAlbum($album->ID);
            echo $album->get_album_gallery_content();
        } catch (Exception $e) {
            echo '';
        }
    }

    /**
     * 
     * @global type $bp_media_query
     * @global type $bp_media_albums_query
     * @param type $type
     */
    function display_show_more($type = 'media') {
        $showmore = false;
        switch ($type) {
            case 'media':
                global $bp_media_query;
                //found_posts
                if (isset($bp_media_query->found_posts) && $bp_media_query->found_posts > 10)
                    $showmore = true;
                break;
            case 'albums':
                global $bp_media_albums_query;
                if (isset($bp_media_query->found_posts) && $bp_media_query->found_posts > 10)
                    $showmore = true;
                break;
        }
        if ($showmore) {
            echo '<div class="bp-media-actions"><a href="#" class="button" id="bp-media-show-more">' . __('Show More', BP_MEDIA_TXT_DOMAIN) . '</a></div>';
        }
    }

    //----------- Function removed show_upload_form_multiple_activity() -------------
}
?>