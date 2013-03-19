<?php

/**
 * Contains methods for template functions
 *
 * @package BuddyPressMedia
 * @subpackage Profile
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 */
class BPMediaTemplate {

    /**
     *
     * @global type $bp_media_current_album
     */
    function upload_form_multiple() {
        global $bp_media_current_album;
		$post_max_size = ini_get('post_max_size');
        $upload_max_filesize = ini_get('upload_max_filesize');
        $memory_limit = ini_get('memory_limit');
        $post_wall = __('Wall Posts', BP_MEDIA_TXT_DOMAIN); ?>
        <div id="bp-media-upload-ui" class="hide-if-no-js drag-drop">
            <div id="drag-drop-area">
                <div class="drag-drop-inside">
                    <p class="drag-drop-info"><?php _e('Drop files here', BP_MEDIA_TXT_DOMAIN); ?></p>
                    <p id="bp-media-album-or"><?php _e(' or ', BP_MEDIA_TXT_DOMAIN); ?></p>
                    <p class="drag-drop-buttons"><input id="bp-media-upload-browse-button" type="button" value="<?php _e('Upload Media', BP_MEDIA_TXT_DOMAIN); ?>" class="button" /></p>
                </div>
                <?php if (!isset($bp_media_current_album)) { ?>
                    <div id="bp-media-album-in"><span><?php _e('to', BP_MEDIA_TXT_DOMAIN); ?></span></div>
                    <div id="bp-media-album-prompt" title="Album">
                        <p><?php _e('Album', BP_MEDIA_TXT_DOMAIN); ?></p>
                        <div class="bp-media-album-content">
                            <select id="bp-media-selected-album"><?php
                    if (bp_is_current_component('groups')) {
                        $albums = new WP_Query(array(
                                    'post_type' => 'bp_media_album',
                                    'posts_per_page' => -1,
                                    'meta_key' => 'bp-media-key',
                                    'meta_value' => -bp_get_current_group_id(),
                                    'meta_compare' => '='
                                        ));
                    } else {
                        $albums = new WP_Query(array(
                                    'post_type' => 'bp_media_album',
                                    'posts_per_page' => -1,
                                    'author' => get_current_user_id()
                                        ));
                    }
                    if (isset($albums->posts) && is_array($albums->posts) && count($albums->posts) > 0) {
                        foreach ($albums->posts as $album) {
                            if ($album->post_title == $post_wall)
                                echo '<option value="' . $album->ID . '" selected="selected">' . $album->post_title . '</option>';
                            else
                                echo '<option value="' . $album->ID . '">' . $album->post_title . '</option>';
                        };
                    }else {
                        $album = new BPMediaAlbum();
                        if (bp_is_current_component('groups')) {
                            $current_group = new BP_Groups_Group(bp_get_current_group_id());
                            $album->add_album($post_wall, $current_group->creator_id, bp_get_current_group_id());
                        } else {
                            $album->add_album($post_wall, bp_loggedin_user_id());
                        }
                        echo '<option value="' . $album->get_id() . '" selected="selected">' . $album->get_title() . '</option>';
                    }
                    echo '<option id="create-new" value="create_new" >' . __('+ Create New Album', BP_MEDIA_TXT_DOMAIN) . '</option>';
                    ?>
                            </select>
                        </div>
                        <div class="hide">
                            <input type="text" id="bp_media_album_new" value="" placeholder="Album Name" /><br/>
                            <input type="button" class="button" id="btn-create-new" value="<?php _e('Create', BP_MEDIA_TXT_DOMAIN); ?>"/>
                            <input type="button" class="button" id="btn-create-cancel" value="<?php _e('Cancel', BP_MEDIA_TXT_DOMAIN); ?>"/>
                        </div>
                    </div>
                    <?php } else { ?>
                    <input type="hidden" id="bp-media-selected-album" value="<?php echo $bp_media_current_album->get_id(); ?>"/>
                <?php } ?>
            </div>
            <div id="bp-media-uploaded-files"></div>
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
                return trailingslashit(bp_displayed_user_domain() . BP_MEDIA_VIDEOS_SLUG . '/' . BP_MEDIA_VIDEOS_VIEW_SLUG . '/' . $media->ID);
                break;
            case 'audio' :
                return trailingslashit(bp_displayed_user_domain() . BP_MEDIA_AUDIO_SLUG . '/' . BP_MEDIA_AUDIO_VIEW_SLUG . '/' . $media->ID);
                break;
            case 'image' :
                return trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG . '/' . BP_MEDIA_IMAGES_VIEW_SLUG . '/' . $media->ID);
                break;
            default :
                return false;
        }
    }

    function the_permalink() {
        echo apply_filters('the_permalink', array($this, 'get_permalink'));
    }

    /**
     *
     * @param type $id
     * @return boolean
     */
    function the_content($id = 0) {
        if (is_object($id))
            $media = $id;
        else
            $media = &get_post($id);
        if (empty($media->ID))
            return false;
        if ($media->post_type != 'attachment')
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
    function the_album_content($id = 0) {
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
    function show_more($type = 'media') {
        $showmore = false;
		global $bp_media;
		$count = $bp_media->default_count();
        switch ($type) {
            case 'media':
                global $bp_media_query;
                //found_posts
                if ( bp_is_my_profile() || BPMediaGroupLoader::can_upload() ) {
                    if (isset($bp_media_query->found_posts) && $bp_media_query->found_posts > ($count-1) )
                        $showmore = true;
                } else {
                    if (isset($bp_media_query->found_posts) && $bp_media_query->found_posts > $count )
                        $showmore = true;
                }
                break;
            case 'albums':
                global $bp_media_albums_query;
                if (isset($bp_media_albums_query->found_posts) && $bp_media_albums_query->found_posts > $count ){
                        $showmore = true;
                }
                break;
        }
        if ($showmore) {
            echo '<div class="bp-media-actions"><a href="#" class="button" id="bp-media-show-more">' . __('Show More', BP_MEDIA_TXT_DOMAIN) . '</a></div>';
        }
    }

    /**
     *
     */

    /**
     *
     * @param type $mediaconst
     */
    function redirect($mediaconst) {
        bp_core_redirect(trailingslashit(bp_displayed_user_domain() . constant('BP_MEDIA_' . $mediaconst . '_SLUG')));
    }

    function loader() {
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
    }

    /**
     *
     * @global type $bp
     * @global type $bp_media_default_excerpts
     * @return type
     */
    function upload_form_multiple_activity() {
        global $bp, $bp_media_default_excerpts;
        if ($bp->current_component != 'activity')
            return;
        ?>
        <div id="bp-media-album-prompt" title="Select Album">
            <div class="bp-media-album-title">
                <span><?php _e('Select Album', BP_MEDIA_TXT_DOMAIN); ?></span>
                <span id="bp-media-close"><?php _e('x', BP_MEDIA_TXT_DOMAIN); ?></span>
            </div>
            <div class="bp-media-album-content">
                <select id="bp-media-selected-album"><?php
            $albums = new WP_Query(array(
                        'post_type' => 'bp_media_album',
                        'posts_per_page' => -1,
                        'author' => get_current_user_id()
                            ));
            if (isset($albums->posts) && is_array($albums->posts) && count($albums->posts) > 0) {
                foreach ($albums->posts as $album) {
                    if ($album->post_title == $post_wall)
                        echo '<option value="' . $album->ID . '" selected="selected">' . $album->post_title . '</option>';
                    else
                        echo '<option value="' . $album->ID . '">' . $album->post_title, BP_MEDIA_TXT_DOMAIN . '</option>';
                };
            }
            ?></select>
            </div>
            <div class="select-btn-div">
                <input id="selected-btn" type="button" class="btn" value="<?php _e('Select', BP_MEDIA_TXT_DOMAIN); ?>" />
                <input id="create-btn" type="button" class="btn" value="<?php _e('Create Album', BP_MEDIA_TXT_DOMAIN); ?>" />
                <div style="clear: both;"></div>
            </div>
        </div>
        <div id="bp-media-album-new" title="Create New Album">
            <div class="bp-media-album-title">
                <span><?php _e('Create Album', BP_MEDIA_TXT_DOMAIN); ?></span>
                <span id="bp-media-create-album-close"><?php _e('x', BP_MEDIA_TXT_DOMAIN); ?></span>
            </div>
            <div class="bp-media-album-content">
                <label for="bp_media_album_name"><?php _e('Album Name', BP_MEDIA_TXT_DOMAIN); ?></label>
                <input id="bp_media_album_name" type="text" name="bp_media_album_name" />
            </div>
            <div class="select-btn-div">
                <input id="create-album" type="button" class="btn" value="<?php _e('Create', BP_MEDIA_TXT_DOMAIN); ?>" />
            </div>
        </div>
        <div id="bp-media-upload-ui" class="hide-if-no-js drag-drop activity-component">
            <p class="drag-drop-buttons"><input id="bp-media-upload-browse-button" type="button" value="<?php _e('Add Media', BP_MEDIA_TXT_DOMAIN); ?>" class="button" /></p>
            <div id="bp-media-uploaded-files"></div>
        </div>
        <?php
    }

}
?>
