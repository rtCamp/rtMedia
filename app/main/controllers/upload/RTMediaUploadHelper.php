<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaPLUploadHelper
 *
 * @author Udit Desai <udit.desai@rtcamp.com>
 */
class RTMediaUploadHelper {

	public function __construct() {

//		wp_enqueue_script('pl-uploader', RT_MEDIA_URL.'app/assets/js/plupload.js', array('jquery'), RT_MEDIA_VERSION);
//		wp_enqueue_script('pl-uploader-html5', RT_MEDIA_URL.'app/assets/js/plupload.html5.js', array('pl-uploader'), RT_MEDIA_VERSION);
//		wp_enqueue_script('pl-upload-queue', RT_MEDIA_URL.'app/assets/js/jquery.plupload.queue.js', array('jquery','pl-uploader'), RT_MEDIA_VERSION);
//		wp_enqueue_script('rt-media-helper', RT_MEDIA_URL.'app/assets/js/rt.media.helper.js', array('jquery','pl-uploader','pl-uploader-html5'), RT_MEDIA_VERSION);

//		wp_enqueue_style('pl-upload-queue', RT_MEDIA_URL.'app/assets/css/jquery.plupload.queue.css', '', RT_MEDIA_VERSION);
	}
	
	static function file_upload() {

		$end_point = new RTMediaUploadEndpoint();
		$end_point->template_redirect();
	}
}



?>
