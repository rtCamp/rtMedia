<?php

class BPMediaFunction {

    function __construct() {
        //add_action('bp_init', array($this,'swap_filters'));
        add_filter('bp_get_activity_action', array($this, 'conditional_override_allowed_tags'), 1, 2);
        add_action('wp_ajax_my_featured_action', array($this, 'implement_featured_ajax'));
        add_action('wp_ajax_nopriv_my_featured_action', array($this, 'implement_featured_ajax'));
    }

    static function record_activity($args = '') {
        global $bp;
        if (!function_exists('bp_activity_add'))
            return false;
        $defaults = array(
            'component' => BP_MEDIA_SLUG, // The name/ID of the component e.g. groups, profile, mycomponent
        );
        add_filter('bp_activity_allowed_tags', 'BPMediaFunction::override_allowed_tags');
        $r = wp_parse_args($args, $defaults);
        $activity_id = bp_activity_add($r);
        return $activity_id;
    }

    static function override_allowed_tags($activity_allowedtags) {
        $activity_allowedtags['video'] = array();
        $activity_allowedtags['video']['id'] = array();
        $activity_allowedtags['video']['class'] = array();
        $activity_allowedtags['video']['src'] = array();
        $activity_allowedtags['video']['height'] = array();
        $activity_allowedtags['video']['width'] = array();
        $activity_allowedtags['video']['controls'] = array();
        $activity_allowedtags['video']['preload'] = array();
        $activity_allowedtags['video']['alt'] = array();
        $activity_allowedtags['video']['title'] = array();
        $activity_allowedtags['audio'] = array();
        $activity_allowedtags['audio']['id'] = array();
        $activity_allowedtags['audio']['class'] = array();
        $activity_allowedtags['audio']['src'] = array();
        $activity_allowedtags['audio']['controls'] = array();
        $activity_allowedtags['audio']['preload'] = array();
        $activity_allowedtags['audio']['alt'] = array();
        $activity_allowedtags['audio']['title'] = array();
        $activity_allowedtags['script'] = array();
        $activity_allowedtags['script']['type'] = array();
        $activity_allowedtags['script']['src'] = array();
        $activity_allowedtags['div'] = array();
        $activity_allowedtags['div']['id'] = array();
        $activity_allowedtags['div']['class'] = array();
        $activity_allowedtags['a'] = array();
        $activity_allowedtags['a']['title'] = array();
        $activity_allowedtags['a']['href'] = array();
        $activity_allowedtags['ul'] = array();
        $activity_allowedtags['li'] = array();

        return $activity_allowedtags;
    }

    static function show_formatted_error_message($messages, $type) {
        echo '<div id="message" class="' . $type . '">';
        if (is_array($messages)) {
            foreach ($messages as $key => $message) {
                if (is_string($message)) {
                    echo '<p>' . $message . '</p>';
                }
            }
        } else {
            if (is_string($messages)) {
                echo '<p>' . $messages . '</p>';
            }
        }
        echo '</div>';
    }

    /*
      function remove_kses_filter(){
      global $bp_media;
      if ($activity != null && in_array($activity->type, $bp_media->activity_types)) {
      remove_filter('bp_get_activity_content_body', 'bp_activity_filter_kses', 1);
      }
      }
     *
     */

    function conditional_override_allowed_tags($content, $activity = null) {
        global $bp_media;

        if ($activity != null && in_array($activity->type, $bp_media->activity_types)) {
            add_filter('bp_activity_allowed_tags', 'BPMediaFunction::override_allowed_tags', 1);
        }

        return $content;
    }

    function swap_filters() {
        //add_filter('bp_get_activity_content_body', 'BPMediaFunction::conditional_override_allowed_tags', 2, 2);
    }

    /**
     * Updates the media count of all users.
     */
    static function update_count() {
        global $wpdb;
        $query =
                "SELECT
		post_author,
		SUM(CASE WHEN post_mime_type LIKE 'image%' THEN 1 ELSE 0 END) as Images,
		SUM(CASE WHEN post_mime_type LIKE 'audio%' THEN 1 ELSE 0 END) as Audio,
		SUM(CASE WHEN post_mime_type LIKE 'video%' THEN 1 ELSE 0 END) as Videos,
		SUM(CASE WHEN post_type LIKE 'bp_media_album' THEN 1 ELSE 0 END) as Albums,
		COUNT(*) as Total
	FROM
		$wpdb->posts RIGHT JOIN $wpdb->postmeta on wp_postmeta.post_id = wp_posts.id
	WHERE
		`meta_key` = 'bp-media-key' AND
		`meta_value` > 0 AND
		( post_mime_type LIKE 'image%' OR post_mime_type LIKE 'audio%' OR post_mime_type LIKE 'video%' OR post_type LIKE 'bp_media_album')
	GROUP BY post_author";
        $result = $wpdb->get_results($query);
        if (!is_array($result))
            return false;

        foreach ($result as $obj) {

            $count = array(
                'images' => isset($obj->Images) ? $obj->Images : 0,
                'videos' => isset($obj->Videos) ? $obj->Videos : 0,
                'audio' => isset($obj->Audio) ? $obj->Audio : 0,
                'albums' => isset($obj->Albums) ? $obj->Albums : 0
            );
            bp_update_user_meta($obj->post_author, 'bp_media_count', $count);
        }
        return true;
    }

    static function update_media() {
        global $bp_media_current_entry;
        if ($bp_media_current_entry->update_media(array('description' => esc_html($_POST['bp_media_description']), 'name' => esc_html($_POST['bp_media_title'])))) {
            $bp_media_current_entry->update_media_activity();
            @setcookie('bp-message', 'The media has been updated', time() + 60 * 60 * 24, COOKIEPATH);
            @setcookie('bp-message-type', 'success', time() + 60 * 60 * 24, COOKIEPATH);
            wp_redirect($bp_media_current_entry->get_url());
            exit;
        } else {
            @setcookie('bp-message', 'The media update failed', time() + 60 * 60 * 24, COOKIEPATH);
            @setcookie('bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH);
            wp_redirect($bp_media_current_entry->get_edit_url());
            exit;
        }
    }

    static function check_user() {
        if (bp_loggedin_user_id() != bp_displayed_user_id()) {
            bp_core_no_access(array(
                'message' => __('You do not have access to this page.', BP_MEDIA_TXT_DOMAIN),
                'root' => bp_displayed_user_domain(),
                'redirect' => false
            ));
            exit;
        }
    }

    function page_not_exist() {
        @setcookie('bp-message', 'The requested url does not exist', time() + 60 * 60 * 24, COOKIEPATH);
        @setcookie('bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH);
        wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG));
        exit;
    }

    static function update_album_activity($album, $current_time = true, $delete_media_id = null) {
        if (!is_object($album)) {
            $album = new BPMediaAlbum($album);
        }
        $args = array(
            'post_parent' => $album->get_id(),
            'numberposts' => 4,
            'post_type' => 'attachment',
        );
        if ($delete_media_id)
            $args['exclude'] = $delete_media_id;
        $attachments = get_posts($args);
        if (is_array($attachments)) {
            $content = '<ul>';
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

    static function wp_comment_form_mod() {
        global $bp_media_current_entry;
        echo '<input type="hidden" name="redirect_to" value="' . $bp_media_current_entry->get_url() . '">';
    }

    function implement_featured_ajax() {
        if (isset($_POST['post_id'])) {
            if (isset($_POST['post_date']) && $_POST['remove_featured'] == 0) {
                update_post_meta($_POST['post_id'], 'featured', date('Y-m-d H:i:s', strtotime($_POST['post_date'])));
            } else {
                update_post_meta($_POST['post_id'], 'featured', FALSE);
            }
            die(1);
        }
    }

    /**
     * Redirects the user to the location given in the parameter as well as set the message
     * and context of redirect
     *
     * @param $location String The URL to redirect to
     * @param $message String The message to show on the page where redirected
     * @param $type String Type of message(updated, success, error, warning), works only if message is set
     * @param $status String The HTTP status header for the redirection page.
     */
    function redirect($location, $message = '', $type = 'updated', $status = '302') {
        if ($message != '')
            bp_core_add_message($message, 'error');
        bp_core_redirect($location, $status);
    }

}

?>