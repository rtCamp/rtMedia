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

	static function upload_nonce_generator($echo = true,$only_nonce =false) {

		if($echo) {
			wp_nonce_field('rt_media_upload_nonce','rt_media_upload_nonce');
		} else {
                        if($only_nonce)
                            return wp_create_nonce('rt_media_upload_nonce');
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
        global $rt_media_query;
        $album = '';
        if ( $rt_media_query && is_rt_media_album()){
            $album = '<input class="rt-media-current-album" type="hidden" name="rt-media-current-album" value="'.$rt_media_query->media_query['album_id'].'" />';
	}elseif ( !is_single() && $rt_media_query && is_rt_media_gallery() ){
            $album = '<select name="album" class="rt-media-user-album-list">'.rt_media_user_album_list().'</select>';

	}
	$tabs = array(
			'file_upload' => array(
				'default' => array('title' => __('File Upload','rt-media'), 'content' => '<div id="rtmedia-upload-container" ><div id="drag-drop-area" class="drag-drop">'.$album.'<input id="rtMedia-upload-button" value="Select" type="button" class="rt-media-upload-input rt-media-file" /></div><table id="rtMedia-queue-list"></table></div>' ),
				'activity' => array('title' => __('File Upload','rt-media'), 'content' => '<div class="rt-media-container"><div id="rt-media-action-update"><input type="button" class="rt-media-add-media-button" id="rt-media-add-media-button-post-update"  value="' . __("Add Media","rt-media") . '" /></div><div id="div-attache-rtmedia"><div id="rt-media-whts-new-upload-container" ><div id="rt-media-whts-new-drag-drop-area" class="drag-drop"><input id="rt-media-whts-new-upload-button" value="Select" type="button" class="rt-media-upload-input rt-media-file" /></div><div id="rtMedia-update-queue-list"></div></div></div></div>')
			),
//			'file_upload' => array( 'title' => __('File Upload','rt-media'), 'content' => '<div id="rt-media-uploader"><p>Your browser does not have HTML5 support.</p></div>'),
			'link_input' => array( 'title' => __('Insert from URL','rt-media'),'content' => '<input type="url" name="bp-media-url" class="rt-media-upload-input rt-media-url" />' ),
        );
        $tabs = apply_filters('rt_media_upload_tabs', $tabs );

		$attr = $this->attributes;
		$mode = (isset($_GET['mode']) && array_key_exists($_GET['mode'], $tabs)) ? $_GET['mode'] : 'file_upload';

		$upload_type = 'default';
		if(isset($attr['activity']) && $attr['activity'])
			$upload_type = 'activity';

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
			$located = RTMEDIA_PATH . 'templates/upload/' . $template_name;
		}

        return $located;
    }

}

?>