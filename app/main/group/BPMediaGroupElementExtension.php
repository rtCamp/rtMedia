<?php
/**
 * Description of BPMediaGroupElementExtension
 *
 * @author faishal
 */
if (class_exists('BP_Group_Extension')) :

    class BPMediaGroupElementExtension extends BP_Group_Extension {

        var $enable_edit_item = false;
        var $enable_create_step = false;

        function __construct($name, $slug) {
            $this->name = $name;
            $this->slug = $slug;
        }

        function display() {
            global $bp;
            BPMediaGroupAction::bp_media_groups_set_query();
            $current_tab = BP_MEDIA_IMAGES_SLUG;
            if (isset($bp->action_variables[0])) {
                $current_tab = $bp->action_variables[0];
            }
            BPMediaGroup::navigation_menu();
            $media_type = "";
            $slug = "";
            switch ( $current_tab ) {
                case BP_MEDIA_IMAGES_SLUG:
                    $media_type = "image";
                    $slug = BP_MEDIA_IMAGES_SLUG;
                    break;
                case BP_MEDIA_VIDEOS_SLUG:
                    $media_type = "video";
                    $slug = BP_MEDIA_VIDEOS_SLUG;
                    break;
                case BP_MEDIA_AUDIO_SLUG:
                    $media_type = "audio";
                    $slug = BP_MEDIA_AUDIO_SLUG;
                    break;
                case BP_MEDIA_ALBUMS_SLUG:
                    $media_type = "album";
                    $slug = BP_MEDIA_ALBUMS_SLUG;
                    break;
                case BP_MEDIA_UPLOAD_SLUG:
                    $media_type = "upload";
                    $slug = BP_MEDIA_ALBUMS_SLUG;
                    break;
                default:
                /** @todo Error is to be displayed for 404 */
            }
            if ($slug != "" && $media_type != "") {
                if (isset($bp->action_variables[1])) {
                    switch ($bp->action_variables[1]) {
                        case 'edit':
                            //Edit screen for image
                            break;
                        case 'delete':
                            //Delete function for media file
                            break;
                        default:
                            if (intval(bp_action_variable(1)) > 0) {
                                global $bp_media_current_entry;
                                try {
                                    $bp_media_current_entry = new BPMediaHostWordpress(bp_action_variable(1));
                                    if ($bp_media_current_entry->get_group_id() != bp_get_current_group_id())
                                        throw new Exception(__('Sorry, the requested media does not belong to the group', BP_MEDIA_TXT_DOMAIN));
                                } catch (Exception $e) {
                                    /** Error Handling when media not present or not belong to the group */
                                    bp_media_display_error($e->getMessage());
                                    return;
                                }
                                if ($media_type == "album") {
                                    $bp_media_content = new BPMediaAlbumScreen($media_type, BP_MEDIA_ALBUMS_ENTRY_SLUG);
                                    $bp->action_variables[ 0 ]= BP_MEDIA_ALBUMS_ENTRY_SLUG;
                                    $bp_media_content->entry_screen();
                                } else {
                                    $bp_media_content = new BPMediaScreen($media_type, $slug);
                                }
                                $bp_media_content->entry_screen_content();

                                break;
                            } else {
                                /** @todo display 404 */
                            }
                    }
                } else {
                    if ($media_type == "album") {
                        BPMediaGroupAction::bp_media_groups_albums_set_query();
                        $bp_media_content = new BPMediaAlbumScreen($media_type, $slug);
                        $bp_media_content->screen_content();
                    } else if ( $media_type == 'upload' ) {
                        if (BPMediaGroup::can_upload()) {
                            $bp_media_upload = new BPMediaUploadScreen('upload', BP_MEDIA_UPLOAD_SLUG);
                            $bp_media_upload->upload_screen_content();
                        }
                    } else {
                        $bp_media_content = new BPMediaScreen($media_type, $slug);
                        $bp_media_content->screen_content();
                    }
                }
            }
        }

        function widget_display() {

        }

        function bp_media_display_error($errorMessage) {
            ?>
            <div id="message" class="error">
                <p>
                    <?php _e($errorMessage, BP_MEDIA_TXT_DOMAIN); ?>
                </p>
            </div>
            <?php
        }

    }

    endif;
//bp_register_group_extension("BP_Media_Group_Extension_' . constant('BP_MEDIA_' . $item . '_SLUG') . '" );
?>
