<?php

/**
 * Description of BPMediaUploadView
 *
 * @author joshua
 */
class BPMediaUploadView {
    
    function __construct() {
    }

    public function render($template_name) {
        $tabs = array(
          'file_upload' => array( 'title' => __('File Upload','buddypress-media'), 'content' => '<div id="drag-drop-area"><input type="file" name="bp_media_file" class="bp-media-upload-input bp-media-file" /><input id="browse-button" type="button" value="Upload Media" class="button"></div>' ),
          'link_input' => array( 'title' => __('Insert from URL','buddypress-media'),'content' => '<input type="url" name="bp-media-url" class="bp-media-upload-input bp-media-url" />' ),
        );
        $tabs = apply_filters('bp_media_upload_tabs', $tabs );
        
        $mode = ( isset($_GET['mode']) &&  array_key_exists($_GET['mode'], $tabs) )?$_GET['mode']:'file_upload';

        include $this->locate_template($template_name);
    }

    protected function locate_template($template_name) {
        $located = '';
            if (!$template_name)
                $located = false;
            if (file_exists(STYLESHEETPATH . '/buddypress-media/' . $template_name)) {
                $located = STYLESHEETPATH . '/buddypress-media/' . $template_name;
            } else if (file_exists(TEMPLATEPATH . '/buddypress-media/' . $template_name)) {
                $located = TEMPLATEPATH . '/buddypress-media/' . $template_name;
            } else {
                $located = BP_MEDIA_PATH . 'templates/' . $template_name;
        }

        return $located;
    }

}

?>
