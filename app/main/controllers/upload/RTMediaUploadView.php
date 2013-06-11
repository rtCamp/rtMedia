<?php

/**
 * Description of BPMediaUploadView
 *
 * @author joshua
 */
class RTMediaUploadView {
	
	private $attributes;

    function __construct($attr) {
		
		$this->attributes = $attr;

//		wp_enqueue_style('quicksand', RT_MEDIA_URL.'app/assets/js/quicksand.js', array('jquery','jquery-effects-core'), RT_MEDIA_VERSION);
//		wp_enqueue_script('rt-media-helper', RT_MEDIA_URL.'app/assets/js/rt.media.helper.js', array('jquery','quicksand'), RT_MEDIA_VERSION);
		wp_enqueue_style('rt-media-main', RT_MEDIA_URL . 'app/assets/css/main.css', '', RT_MEDIA_VERSION);
    }

    public function render($template_name) {
        $tabs = array(
			'file_upload' => array( 'title' => __('File Upload','rt-media'), 'content' => '<div id="drag-drop-area"><input type="file" name="rt_media_file" class="rt-media-upload-input rt-media-file" /><input id="browse-button" type="button" value="Upload Media" class="button"></div>' ),
//			'file_upload' => array( 'title' => __('File Upload','rt-media'), 'content' => '<div id="rt-media-uploader"><p>Your browser does not have HTML5 support.</p></div>'),
			'link_input' => array( 'title' => __('Insert from URL','rt-media'),'content' => '<input type="url" name="bp-media-url" class="rt-media-upload-input rt-media-url" />' ),
        );
        $tabs = apply_filters('bp_media_upload_tabs', $tabs );

		$attr = $this->attributes;
		$mode = (isset($_GET['mode']) && array_key_exists($_GET['mode'], $tabs)) ? $_GET['mode'] : 'file_upload';

		$uploadHelper = new RTMediaUploadHelper();
		include $this->locate_template($template_name);

    }

    protected function locate_template($template) {
        $located = '';
		
		$template_name = $template . '.php';
		
		if (!$template_name)
			$located = false;
		if (file_exists(STYLESHEETPATH . '/rt-media/' . $template_name)) {
			$located = STYLESHEETPATH . '/rt-media/' . $template_name;
		} else if (file_exists(TEMPLATEPATH . '/rt-media/' . $template_name)) {
			$located = TEMPLATEPATH . '/rt-media/' . $template_name;
		} else {
			$located = RT_MEDIA_PATH . 'templates/upload/' . $template_name;
		}

        return $located;
    }

}

?>
