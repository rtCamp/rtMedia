<?php

/**
 * Description of RTMediaUploadView
 *
 * @author joshua
 */
class RTMediaUploadView {

	private $attributes;

	/**
	 *
	 * @param type $attr
	 */
    function __construct($attr) {

		$this->attributes = $attr;

    }

	static function upload_nonce_generator($echo = true) {

		if($echo) {
			wp_nonce_field('rt_media_upload_nonce','rt_media_upload_nonce');
		} else {
			$token = array(
				'action' => 'rt_media_upload_nonce',
				'nonce' => wp_create_nonce('rt_media_upload_nonce')
			);

			return json_encode($token);
		}
	}

	/**
	 * Render the uploader shortcode and attach the uploader panel
	 *
	 * @param type $template_name
	 */
    public function render($template_name) {
        $tabs = array(
			'file_upload' => array( 'title' => __('File Upload','rt-media'), 'content' => '<div id="drag-drop-area"><input type="file" name="rt_media_file[]" class="rt-media-upload-input rt-media-file" multiple="" /></div>' ),
//			'file_upload' => array( 'title' => __('File Upload','rt-media'), 'content' => '<div id="rt-media-uploader"><p>Your browser does not have HTML5 support.</p></div>'),
			'link_input' => array( 'title' => __('Insert from URL','rt-media'),'content' => '<input type="url" name="bp-media-url" class="rt-media-upload-input rt-media-url" />' ),
        );
        $tabs = apply_filters('bp_media_upload_tabs', $tabs );

		$attr = $this->attributes;
		$mode = (isset($_GET['mode']) && array_key_exists($_GET['mode'], $tabs)) ? $_GET['mode'] : 'file_upload';

		$uploadHelper = new RTMediaUploadHelper();
		include $this->locate_template($template_name);

    }

	/**
	 * Template Locator
	 *
	 * @param type $template
	 * @return string
	 */
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