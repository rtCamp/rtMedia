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

        /**
         *
         * @param type $name
         * @param type $slug
         */
        function __construct($name, $slug) {
            $this->name = $name;
            $this->slug = $slug;
        }

        /**
         *
         * @global type $bp
         * @global BPMediaHostWordpress $bp_media_current_entry
         * @return type
         * @throws Exception
         */
        function display() {
            global $bp,$bp_media_current_entry;
            //For saving the data if the form is submitted
            if ( bp_action_variable(2) )
                $bp_media_current_entry = new BPMediaHostWordpress(bp_action_variable(2));
            $current_tab = BP_MEDIA_IMAGES_SLUG;
            if (isset($bp->action_variables[0])) {
                $current_tab = $bp->action_variables[0];
            }
            BPMediaGroupLoader::navigation_menu();
            $media_type = "";
            $slug = "";
            switch ($current_tab) {
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
            if ($media_type == "album") {
                        $bp_media_content = new BPMediaAlbumScreen($media_type, $slug);
                    } else if ($media_type == 'upload') {
                        if (BPMediaGroupLoader::can_upload()) {
                            $bp_media_content = new BPMediaUploadScreen('upload', BP_MEDIA_UPLOAD_SLUG);
                        }
                    } else {
                        $bp_media_content = new BPMediaScreen($media_type, $slug);
                    }
            if ($slug != "" && $media_type != "") {
                if (isset($bp->action_variables[1])) {
                    switch ($bp->action_variables[1]) {
                        case 'edit':
                            //$bp_media_content->edit_screen_content();
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
                                        throw new Exception(__('Sorry, the requested media does not belong to the group', 'buddypress-media'));
                                } catch (Exception $e) {
                                    /** Error Handling when media not present or not belong to the group */
                                    $this->bp_media_display_error($e->getMessage());
                                    return;
                                }
                                if ($media_type == "album") {
                                    $bp->action_variables[0] = BP_MEDIA_ALBUMS_VIEW_SLUG;
                                    echo '<h3>'.get_the_title($bp->action_variables[1]).'</h3>';
                                    $bp_media_content->entry_screen();
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
                        $bp_media_content->screen_content();
                    } else if ($media_type == 'upload') {
                        if (BPMediaGroupLoader::can_upload()) {
                            $bp_media_content->upload_screen_content();
                        }
                    } else {
                        $bp_media_content->screen_content();
                    }
                }
            }
        }

        function widget_display() {

        }

        /**
         *
         * @param type $errorMessage
         */
        function bp_media_display_error($errorMessage) {
            ?>
            <div id="message" class="error">
                <p>
                    <?php _e($errorMessage, 'buddypress-media'); ?>
                </p>
            </div>
            <?php
        }

    }

    endif;
//bp_register_group_extension("BP_Media_Group_Extension_' . constant('BP_MEDIA_' . $item . '_SLUG') . '" );
?>
