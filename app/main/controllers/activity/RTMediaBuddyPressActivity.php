<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaBuddyPressActivity
 *
 * @author faishal
 */
class RTMediaBuddyPressActivity {

    function __construct() {
        global $rtmedia;
        if($rtmedia->options["buddypress_enableOnActivity"]!==0){
            add_action("bp_after_activity_post_form", array(&$this, "bp_after_activity_post_form"));
            add_action("bp_activity_posted_update", array(&$this, "bp_activity_posted_update"), 99, 3);
            add_action("bp_groups_posted_update", array(&$this, "bp_groups_posted_update"), 99, 4);
        }
        add_action("bp_init", array($this, 'non_threaded_comments'));
        add_action("bp_activity_comment_posted", array($this, "comment_sync"), 10, 2);
        add_filter('bp_activity_allowed_tags', array(&$this, 'override_allowed_tags'));
    }

    function comment_sync($comment_id, $param) {

        // comment_id 40
        // Array( [id] => [content] => testing [user_id] => 1 [activity_id] => 26 [parent_id] => 26)
        $activity = new BP_Activity_Activity($param['activity_id']);
        if ($activity->type == 'rtmedia_update') {
            $media_id = $activity->item_id;
            $comment = new RTMediaComment();
            $comment->add(array('comment_content' => $param['content'], 'comment_post_ID' => $media_id));
        }
    }

    function non_threaded_comments() {
        if (isset($_POST['action']) && $_POST['action'] == 'new_activity_comment') {
            $activity_id = $_POST['form_id'];
            $act = new BP_Activity_Activity($activity_id);

            if ($act->type == 'rtmedia_update')
                $_POST['comment_id'] = $_POST['form_id'];
        }
    }

    function bp_groups_posted_update($content, $user_id, $group_id, $activity_id) {
        $this->bp_activity_posted_update($content, $user_id, $activity_id);
    }

    function bp_activity_posted_update($content, $user_id, $activity_id) {
        if (isset($_POST["rtMedia_attached_files"]) && is_array($_POST["rtMedia_attached_files"])) {
            $objActivity = new RTMediaActivity($_POST["rtMedia_attached_files"], 0, $content);
            global $wpdb, $bp;
            $wpdb->update($bp->activity->table_name, array("type" => "rtmedia_update", "content" => $objActivity->create_activity_html()), array("id" => $activity_id));
        }
        if (isset($_POST['rtmedia-privacy']))
                bp_activity_update_meta($activity_id, 'rtmedia_privacy', ($_POST['rtmedia-privacy'] == 0) ? -1 : $_POST['rtmedia-privacy']);
    }

    function bp_after_activity_post_form() {
        $url = $_SERVER["REQUEST_URI"];
        $url = trailingslashit($url);

        $params = array(
            'url' => (isset($url) && (strpos($url, "/media/") !== false)) ? str_replace("/media/", "/upload/", $url) : 'upload/',
            'runtimes' => 'gears,html5,flash,silverlight,browserplus',
            'browse_button' => 'rtmedia-whts-new-upload-button',
            'container' => 'rtmedia-whts-new-upload-container',
            'drop_element' => 'rtmedia-whts-new-drag-drop-area',
            'filters' => apply_filters('bp_media_plupload_files_filter', array(array('title' => "Media Files", 'extensions' => "mp4,jpg,png,jpeg,gif,mp3"))),
            'max_file_size' => min(array(ini_get('upload_max_filesize'), ini_get('post_max_size'))),
            'multipart' => true,
            'urlstream_upload' => true,
            'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
            'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
            'file_data_name' => 'rtmedia_file', // key passed to $_FILE.
            'multi_selection' => true,
            'multipart_params' => apply_filters('rtmedia-multi-params', array('redirect' => 'no', 'rtmedia_update' => 'true', 'action' => 'wp_handle_upload', '_wp_http_referer' => $_SERVER['REQUEST_URI'], 'mode' => 'file_upload', 'rtmedia_upload_nonce' => RTMediaUploadView::upload_nonce_generator(false, true)))
        );
        wp_enqueue_script('rtmedia-backbone', false, '', false, true);
        $is_album = is_rtmedia_album() ? true : false;
        $is_edit_allowed = is_rtmedia_edit_allowed() ? true: false;
        wp_localize_script('rtmedia-backbone', 'is_album', $is_album);
        wp_localize_script('rtmedia-backbone', 'is_edit_allowed', $is_edit_allowed);
        wp_localize_script('rtmedia-backbone', 'rtMedia_update_plupload_config', $params);


        $uploadView = new RTMediaUploadView(array('activity' => true));
        $uploadView->render('uploader');
    }

    function override_allowed_tags($activity_allowedtags) {

        $activity_allowedtags['video'] = array();
        $activity_allowedtags['video']['id'] = array();
        $activity_allowedtags['video']['class'] = array();
        $activity_allowedtags['video']['src'] = array();
        $activity_allowedtags['video']['controls'] = array();
        $activity_allowedtags['video']['preload'] = array();
        $activity_allowedtags['video']['alt'] = array();
        $activity_allowedtags['video']['title'] = array();
        $activity_allowedtags['video']['width'] = array();
        $activity_allowedtags['video']['height'] = array();
        $activity_allowedtags['video']['poster'] = array();
        $activity_allowedtags['audio'] = array();
        $activity_allowedtags['audio']['id'] = array();
        $activity_allowedtags['audio']['class'] = array();
        $activity_allowedtags['audio']['src'] = array();
        $activity_allowedtags['audio']['controls'] = array();
        $activity_allowedtags['audio']['preload'] = array();
        $activity_allowedtags['audio']['alt'] = array();
        $activity_allowedtags['audio']['title'] = array();
        $activity_allowedtags['audio']['width'] = array();
        $activity_allowedtags['audio']['poster'] = array();
        $activity_allowedtags['div'] = array();
        $activity_allowedtags['div']['id'] = array();
        $activity_allowedtags['div']['class'] = array();
        $activity_allowedtags['a'] = array();
        $activity_allowedtags['a']['title'] = array();
        $activity_allowedtags['a']['href'] = array();
        $activity_allowedtags['ul'] = array();
        $activity_allowedtags['ul']['class'] = array();
        $activity_allowedtags['li'] = array();

        return $activity_allowedtags;
    }

}

?>
