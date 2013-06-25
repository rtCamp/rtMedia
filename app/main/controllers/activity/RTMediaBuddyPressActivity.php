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
        add_action("bp_after_activity_post_form", array(&$this, "bp_after_activity_post_form"));
    }

    function bp_after_activity_post_form() {
        $url = $_SERVER["REQUEST_URI"];
        $url = trailingslashit($url);

        $params = array(
            'url' => (isset($url) && (strpos($url, "/media/") !== false)) ? str_replace("/media/", "/upload/", $url) : 'upload/',
            'runtimes' => 'gears,html5,flash,silverlight,browserplus',
            'browse_button' => 'rtMedia-upload-button',
            'container' => 'rtmedia-upload-container',
            'drop_element' => 'drag-drop-area',
            'filters' => apply_filters('bp_media_plupload_files_filter', array(array('title' => "Media Files", 'extensions' => "mp4,jpg,png,jpeg,gif,mp3"))),
            'max_file_size' => min(array(ini_get('upload_max_filesize'), ini_get('post_max_size'))),
            'multipart' => true,
            'urlstream_upload' => true,
            'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
            'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
            'file_data_name' => 'rt_media_file', // key passed to $_FILE.
            'multi_selection' => true,
            'multipart_params' => apply_filters('rt-media-multi-params', array('redirect' => 'no', 'action' => 'wp_handle_upload', '_wp_http_referer' => $_SERVER['REQUEST_URI'], 'mode' => 'file_upload', 'rt_media_upload_nonce' => RTMediaUploadView::upload_nonce_generator(false, true)))
        );
        wp_enqueue_script( 'rtmedia-backbone',false,array(),false,true);
        wp_localize_script('rtmedia-backbone', 'rtMedia_plupload_config', $params);

    }

}

?>
