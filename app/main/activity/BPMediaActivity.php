<?php
/**
 * Description of BPMediaActivity
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaActivity')) {

    class BPMediaActivity {

        var $attachment_id = 0;
        var $content = '';
        var $media_count = 1;

        public function __construct() {
            add_action('bp_after_activity_post_form', array($this, 'activity_uploader'));
//            add_action('bp_activity_new_update_content', array($this, 'activity_new_update_content'));
            add_action('wp_ajax_bp_media_post_update_media_html', array($this, 'post_update_media_html'));
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

            if ($media_id) {

                if (strpos($media_id, '-')) {
                    $media_ids = explode('-', $media_id);
                } else {
                    $media_ids = array($media_id);
                }

                $this->media_count = count($media_ids);
                
                add_filter('bp_media_single_activity_title',array($this,'activity_content'));
                add_filter('bp_media_single_activity_description',create_function('','return "";'));
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




//            if (!$_POST['multiple'] && $_POST['content']) {
//                wp_update_post(array('ID' => $_POST['media_id'], 'post_content' => $_POST['content']));
//                BPMediaActions::activity_create_after_add_media($_POST['media_id'], false, true );
//                if (bp_has_activities('include=' . $_POST['media_id'])) {
//                    while (bp_activities()) {
//                        bp_the_activity();
//                        $this->media_activity_entry();
//                    }
//                }
//            }
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
        
        public function activity_content(){
            return '<p class="bp-media-album-activity-upload-content">' . $this->content . '</p>';
        }

        public function post_update_media_html() {
            global $bp_media_counter;
            $attachment_id = isset($_POST['attachment_id']) ? $_POST['attachment_id'] : 0;
            if ($attachment_id) {
                $media_info = new BPMediaHostWordpress($attachment_id);
                $link = $media_info->get_media_activity_url();
                $thumbnail_id = get_post_meta($attachment_id, 'bp_media_thumbnail', true);
                $type = explode('/', get_post_field('post_mime_type', $attachment_id));
                switch ($type[0]) {
                    case 'image':
                        echo '<p class="bp_media_activity_upload"><a href="' . $link . '">' . wp_get_attachment_image($attachment_id, 'thumbnail') . '</a></p>';
                        break;
                    case 'video':
                        if ($thumbnail_id) {
                            $image_array = image_downsize($thumbnail_id, 'bp_media_activity_image');
                            $activity_content = apply_filters('bp_media_single_activity_filter', '<video poster="' . $image_array[0] . '" src="' . wp_get_attachment_url($attachment_id) . '" width="320" height="240" type="video/mp4" id="bp_media_video_' . $attachment_id . '_' . $bp_media_counter . '" controls="controls" preload="none"></video></span><script>bp_media_create_element("bp_media_video_' . $attachment_id . '_' . $bp_media_counter . '");</script>', $media_info, true);
                        } else {
                            $activity_content = apply_filters('bp_media_single_activity_filter', '<video src="' . wp_get_attachment_url($attachment_id) . '" width="320" height="240" type="video/mp4" id="bp_media_video_' . $attachment_id . '_' . $bp_media_counter . '" controls="controls" preload="none"></video></span><script>bp_media_create_element("bp_media_video_' . $attachment_id . '_' . $bp_media_counter . '");</script>', $media_info, true);
                        }
                        echo '<p class="bp_media_activity_upload">' . $activity_content . '</p>';
                        break;
                    case 'audio':
                        echo '<p class="bp_media_activity_upload"><a href="' . $link . '"><img src="' . BP_MEDIA_URL . 'app/assets/img/audio_thumb.png' . '" /></a></p>';
                        break;
                }
            }
            die();
        }

        public function activity_new_update_content($content) {
            if ($this->attachment_id) {
                $content .= 'testing';
                $content .= wp_get_attachment_image($this->attachment_id, 'thumbnail');
                $this->attachment_id = 0;
            }
            return $content;
        }

        public function media_activity_entry() {
            ?>
            <?php do_action('bp_before_activity_entry'); ?>

            <li class="<?php bp_activity_css_class(); ?>" id="activity-<?php bp_activity_id(); ?>">
                <div class="activity-avatar">
                    <a href="<?php bp_activity_user_link(); ?>">

                        <?php bp_activity_avatar(); ?>

                    </a>
                </div>

                <div class="activity-content">

                    <div class="activity-header">

                        <?php bp_activity_action(); ?>

                    </div>

                    <?php if ('activity_comment' == bp_get_activity_type()) : ?>

                        <div class="activity-inreplyto">
                            <strong><?php _e('In reply to: ', 'buddypress'); ?></strong><?php bp_activity_parent_content(); ?> <a href="<?php bp_activity_thread_permalink(); ?>" class="view" title="<?php _e('View Thread / Permalink', 'buddypress'); ?>"><?php _e('View', 'buddypress'); ?></a>
                        </div>

                    <?php endif; ?>

                    <?php if (bp_activity_has_content()) : ?>

                        <div class="activity-inner">

                            <?php bp_activity_content_body(); ?>

                        </div>

                    <?php endif; ?>

                    <?php do_action('bp_activity_entry_content'); ?>

                    <?php if (is_user_logged_in()) : ?>

                        <div class="activity-meta">

                            <?php if (bp_activity_can_comment()) : ?>

                                <a href="<?php bp_get_activity_comment_link(); ?>" class="button acomment-reply bp-primary-action" id="acomment-comment-<?php bp_activity_id(); ?>"><?php printf(__('Comment <span>%s</span>', 'buddypress'), bp_activity_get_comment_count()); ?></a>

                            <?php endif; ?>

                            <?php if (bp_activity_can_favorite()) : ?>

                                <?php if (!bp_get_activity_is_favorite()) : ?>

                                    <a href="<?php bp_activity_favorite_link(); ?>" class="button fav bp-secondary-action" title="<?php esc_attr_e('Mark as Favorite', 'buddypress'); ?>"><?php _e('Favorite', 'buddypress'); ?></a>

                                <?php else : ?>

                                    <a href="<?php bp_activity_unfavorite_link(); ?>" class="button unfav bp-secondary-action" title="<?php esc_attr_e('Remove Favorite', 'buddypress'); ?>"><?php _e('Remove Favorite', 'buddypress'); ?></a>

                                <?php endif; ?>

                            <?php endif; ?>

                            <?php if (bp_activity_user_can_delete()) bp_activity_delete_link(); ?>

                            <?php do_action('bp_activity_entry_meta'); ?>

                        </div>

                    <?php endif; ?>

                </div>

                <?php do_action('bp_before_activity_entry_comments'); ?>

                <?php if (( is_user_logged_in() && bp_activity_can_comment() ) || bp_activity_get_comment_count()) : ?>

                    <div class="activity-comments">

                        <?php bp_activity_comments(); ?>

                        <?php if (is_user_logged_in()) : ?>

                            <form action="<?php bp_activity_comment_form_action(); ?>" method="post" id="ac-form-<?php bp_activity_id(); ?>" class="ac-form"<?php bp_activity_comment_form_nojs_display(); ?>>
                                <div class="ac-reply-avatar"><?php bp_loggedin_user_avatar('width=' . BP_AVATAR_THUMB_WIDTH . '&height=' . BP_AVATAR_THUMB_HEIGHT); ?></div>
                                <div class="ac-reply-content">
                                    <div class="ac-textarea">
                                        <textarea id="ac-input-<?php bp_activity_id(); ?>" class="ac-input" name="ac_input_<?php bp_activity_id(); ?>"></textarea>
                                    </div>
                                    <input type="submit" name="ac_form_submit" value="<?php _e('Post', 'buddypress'); ?>" /> &nbsp; <?php _e('or press esc to cancel.', 'buddypress'); ?>
                                    <input type="hidden" name="comment_form_id" value="<?php bp_activity_id(); ?>" />
                                </div>

                                <?php do_action('bp_activity_entry_comments'); ?>

                                <?php wp_nonce_field('new_activity_comment', '_wpnonce_new_activity_comment'); ?>

                            </form>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

                <?php do_action('bp_after_activity_entry_comments'); ?>

            </li>

            <?php do_action('bp_after_activity_entry'); ?>

            <?php
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
