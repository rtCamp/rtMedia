<?php
/**
 * Description of BPMediaActivity
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaActivity')) {

    class BPMediaActivity {

        var $default_album_id;
        var $attachment_id = 0;
        var $content = '';
        var $media_count = 1;

        public function __construct() {
            add_action('bp_after_activity_post_form', array($this, 'activity_uploader'));
            add_action('wp_ajax_bp_media_post_update', array($this, 'post_update'));
        }

        public function activity_uploader() {
            ?>
            <div id="bp-media-activity-upload-ui" class="hide-if-no-js drag-drop">
                <input id="bp-media-activity-upload-browse-button" type="button" value="<?php _e('Insert Media', BP_MEDIA_TXT_DOMAIN); ?>" class="button" />
                <div id="bp-media-activity-uploaded-files"></div>
            </div>
            <div id="bp-media-activity-post-update-append" style="display:none"></div><?php
        }

        public function post_update() {
            // Bail if not a POST action
            if ('POST' !== strtoupper($_SERVER['REQUEST_METHOD']))
                return;

            // Check the nonce
            check_admin_referer('post_update', '_wpnonce_post_update');

            if (!is_user_logged_in())
                exit('-1');

            $multiple = isset($_POST['multiple']) ? $_POST['multiple'] : 0;
            $media_id = isset($_POST['media_id']) ? $_POST['media_id'] : 0;
            $this->content = isset($_POST['content']) ? $_POST['content'] : '';
            $group_id = (isset($_POST['group_id']) && ('groups' == $_POST['group_id']) && isset($_POST['item_id'])) ? $_POST['item_id'] : false;

            if ($media_id) {

                if (strpos($media_id, '-')) {
                    $media_ids = explode('-', $media_id);
                } else {
                    $media_ids = array($media_id);
                }

                $this->media_count = count($media_ids);

                add_filter('bp_media_single_activity_title', array($this, 'activity_content'));
                add_filter('bp_media_single_activity_description', create_function('', 'return "";'));
                foreach ($media_ids as $id) {
                    wp_update_post(array('ID' => $id, 'post_content' => $this->content));
                    remove_action('bp_media_album_updated', 'BPMediaActions::album_activity_update');
                    add_action('bp_media_album_updated', array($this, 'update_album_activity_upload'));
                    BPMediaActions::activity_create_after_add_media($id, $multiple, false);
                }

                if ($multiple) {
                    $activity_id = get_post_meta(get_post_field('post_parent', $media_id), 'bp_media_child_activity', true);
                } else {
                    $activity_id = get_post_meta($media_id, 'bp_media_child_activity', true);
                }

                if (empty($activity_id))
                    exit('-1<div id="message" class="error"><p>' . __('There was a problem posting your update, please try again.', 'buddypress') . '</p></div>');
//
                if (bp_has_activities('include=' . $activity_id)) {
                    while (bp_activities()) {
                        bp_the_activity();
                        locate_template(array('activity/entry.php'), true);
                    }
                }
            }




//            $activity_id = 0;
//            if (empty($_POST['object']) && bp_is_active('activity')) {
//                $activity_id = bp_activity_post_update(array('content' => $_POST['content']));
//            } elseif ($_POST['object'] == 'groups') {
//                if (!empty($_POST['item_id']) && bp_is_active('groups'))
//                    $activity_id = groups_post_update(array('content' => $_POST['content'], 'group_id' => $_POST['item_id']));
//            } else {
//                $activity_id = apply_filters('bp_activity_custom_update', $_POST['object'], $_POST['item_id'], $_POST['content']);
//            }
//


            exit;
        }

        public function activity_content() {
            return '<p class="bp-media-album-activity-upload-content">' . $this->content . '</p>';
        }

        /**
         * 
         * @param BPMediaAlbum $album
         * @param type $current_time
         * @param type $delete_media_id
         */
        function update_album_activity_upload($album, $current_time = true, $delete_media_id = null) {
            if (!is_object($album)) {
                $album = new BPMediaAlbum($album);
            }
            $args = array(
                'post_parent' => $album->get_id(),
                'numberposts' => $this->media_count,
                'post_type' => 'attachment',
            );
            if ($delete_media_id)
                $args['exclude'] = $delete_media_id;
            $attachments = get_posts($args);
            if (is_array($attachments)) {
                $content = NULL;
                if ($this->content)
                    $content .= '<p class="bp-media-album-activity-upload-content">' . $this->content . '</p>';
                $content .= '<ul>';
                foreach ($attachments as $media) {
                    $bp_media = new BPMediaHostWordpress($media->ID);
                    $content .= $bp_media->get_album_activity_content();
                }

                $content .= '</ul>';
                $activity_id = get_post_meta($album->get_id(), 'bp_media_child_activity');
                if ($activity_id) {
                    $args = array(
                        'in' => $activity_id,
                    );

                    $activity = @bp_activity_get($args);
                    if (isset($activity['activities'][0]->id)) {
                        $args = array(
                            'content' => $content,
                            'id' => $activity_id,
                            'type' => 'album_updated',
                            'user_id' => $activity['activities'][0]->user_id,
                            'action' => apply_filters('bp_media_filter_album_updated', sprintf(__('%1$s added new media in album %2$s', BP_MEDIA_TXT_DOMAIN), bp_core_get_userlink($activity['activities'][0]->user_id), '<a href="' . $album->get_url() . '">' . $album->get_title() . '</a>')),
                            'component' => BP_MEDIA_SLUG, // The name/ID of the component e.g. groups, profile, mycomponent
                            'primary_link' => $activity['activities'][0]->primary_link,
                            'item_id' => $activity['activities'][0]->item_id,
                            'secondary_item_id' => $activity['activities'][0]->secondary_item_id,
                            'recorded_time' => $current_time ? bp_core_current_time() : $activity['activities'][0]->date_recorded,
                            'hide_sitewide' => $activity['activities'][0]->hide_sitewide
                        );
                        BPMediaFunction::record_activity($args);
                    }
                }
            }
        }

    }

}
?>
