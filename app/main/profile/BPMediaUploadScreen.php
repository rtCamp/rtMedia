<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaUploadScreen
 *
 * @author saurabh
 */
class BPMediaUploadScreen extends BPMediaScreen {

	public function __construct( $media_type, $slug ) {
		parent::__construct( $media_type, $slug );
	}

	function upload_screen() {
		add_action( 'wp_enqueue_scripts', array( $this, 'upload_enqueue' ) );
		add_action( 'bp_template_title', array( $this, 'upload_screen_title' ) );
		add_action( 'bp_template_content', array( $this, 'upload_screen_content' ) );
		$this->template->loader();
	}

	function upload_screen_title() {
		_e( 'Upload Media', BP_MEDIA_TXT_DOMAIN );
	}

	function upload_screen_content() {
		$this->hook_before();

		$this->template->upload_form_multiple();

		$this->hook_after();
	}

	function upload_enqueue() {
		$params = array(
			'url' => BP_MEDIA_URL . 'app/main/includes/bp-media-upload-handler.php',
			'runtimes' => 'gears,html5,flash,silverlight,browserplus',
			'browse_button' => 'bp-media-upload-browse-button',
			'container' => 'bp-media-upload-ui',
			'drop_element' => 'drag-drop-area',
			'filters' => apply_filters( 'bp_media_plupload_files_filter', array( array( 'title' => "Media Files", 'extensions' => "mp4,jpg,png,jpeg,gif,mp3" ) ) ),
			'max_file_size' => min( array( ini_get( 'upload_max_filesize' ), ini_get( 'post_max_size' ) ) ),
			'multipart' => true,
			'urlstream_upload' => true,
			'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
			'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
			'file_data_name' => 'bp_media_file', // key passed to $_FILE.
			'multi_selection' => true,
			'multipart_params' => apply_filters( 'bp_media_multipart_params_filter', array( 'action' => 'wp_handle_upload' ) )
		);
		wp_enqueue_script( 'bp-media-uploader', BP_MEDIA_URL . 'app/assets/js/bp-media-uploader.js', array( 'plupload', 'plupload-html5', 'plupload-flash', 'plupload-silverlight', 'plupload-html4', 'plupload-handlers', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-dialog' ) );
		wp_localize_script( 'bp-media-uploader', 'bp_media_uploader_params', $params );
		wp_enqueue_style( 'bp-media-default', BP_MEDIA_URL . 'app/assets/css/bp-media-style.css' );
		//wp_enqueue_style("wp-jquery-ui-dialog"); //Its not styling the Dialog box as it should so using different styling
		wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
	}

}
?>
