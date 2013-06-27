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
        add_action("bp_activity_posted_update",array(&$this, "bp_activity_posted_update"),99,3);
        add_action("bp_groups_posted_update",array(&$this, "bp_groups_posted_update"),99,4);
		add_action("bp_init",array($this,'non_threaded_comments'));
    }
	function non_threaded_comments() {
		if(isset($_POST['action']) && $_POST['action']=='new_activity_comment') {
			$activity_id = $_POST['form_id'];
			$act = new BP_Activity_Activity($activity_id);

			if($act->type=='rtmedia_activity_update')
				$_POST['comment_id'] = $_POST['form_id'];
		}
	}
    function bp_groups_posted_update($content, $user_id, $group_id, $activity_id){
        $this->bp_activity_posted_update($content, $user_id, $activity_id);
    }

    function bp_activity_posted_update($content, $user_id, $activity_id){
        if(isset($_POST["rtMedia_attached_files"]) && is_array($_POST["rtMedia_attached_files"])){
            $objActivity =  new RTMediaActivity($_POST["rtMedia_attached_files"],0,$content);
            global $wpdb, $bp;
            $wpdb->update($bp->activity->table_name,array("content"=>$objActivity->create_activity_html()),array("id"=>$activity_id));
        }


    }
    function bp_after_activity_post_form() {
        $url = $_SERVER["REQUEST_URI"];
        $url = trailingslashit($url);

        $params = array(
            'url' => (isset($url) && (strpos($url, "/media/") !== false)) ? str_replace("/media/", "/upload/", $url) : 'upload/',
            'runtimes' => 'gears,html5,flash,silverlight,browserplus',
            'browse_button' => 'rt-media-whts-new-upload-button',
            'container' => 'rt-media-whts-new-upload-container',
            'drop_element' => 'rt-media-whts-new-drag-drop-area',
            'filters' => apply_filters('bp_media_plupload_files_filter', array(array('title' => "Media Files", 'extensions' => "mp4,jpg,png,jpeg,gif,mp3"))),
            'max_file_size' => min(array(ini_get('upload_max_filesize'), ini_get('post_max_size'))),
            'multipart' => true,
            'urlstream_upload' => true,
            'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
            'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
            'file_data_name' => 'rt_media_file', // key passed to $_FILE.
            'multi_selection' => true,
            'multipart_params' => apply_filters('rt-media-multi-params', array('redirect' => 'no','activity_update'=>'true', 'action' => 'wp_handle_upload', '_wp_http_referer' => $_SERVER['REQUEST_URI'], 'mode' => 'file_upload', 'rt_media_upload_nonce' => RTMediaUploadView::upload_nonce_generator(false, true)))
        );
        wp_enqueue_script( 'rtmedia-backbone',false,array("rtmedia-backbone"),false,true);
        wp_localize_script('rtmedia-backbone', 'rtMedia_update_plupload_config', $params); ?>
        <div class="rt-media-container">
            <div id='rt-media-action-update'>
                <input type="button" class='rt-media-add-media-button' id='rt-media-add-media-button-post-update'  value="<?php _e("Add Media");?>" />
                <?php do_action("rtmedia_activity_update_privacy_hook"); ?>
            </div>
            <div id="div-attache-rtmedia">
                <div id="rt-media-whts-new-upload-container" >
                    <div id="rt-media-whts-new-drag-drop-area" class="drag-drop">
                        <input id="rt-media-whts-new-upload-button" value="Select" type="button" class="rt-media-upload-input rt-media-file" />
                    </div>
                    <div id="rtMedia-update-queue-list"></div>
                </div>
            </div>
        </div>
<?php
    }

}

?>
