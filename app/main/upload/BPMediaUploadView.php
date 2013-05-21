<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaUploadView
 *
 * @author joshua
 */
class BPMediaUploadView {

    function __construct($upload_object = false) {
        ;
    }

    function render($template_name) {
        $is_url = isset($_GET['is_url'])?$_GET['is_url']:false;
        include $this->locate_template($template_name);
    }

    function locate_template($template_name) {
        $located = '';
            if (!$template_name)
                $located = false;
            if (file_exists(STYLESHEETPATH . '/' . $template_name)) {
                $located = STYLESHEETPATH . '/' . $template_name;
            } else if (file_exists(TEMPLATEPATH . '/' . $template_name)) {
                $located = TEMPLATEPATH . '/' . $template_name;
            } else {
                $located = BP_MEDIA_PATH . 'templates/' . $template_name;
        }

        return $located;
    }

}

?>
