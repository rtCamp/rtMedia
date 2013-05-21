<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaUploadShortcode
 *
 * @author joshua
 */
class BPMediaUploadShortcode {
    public function __construct() {
        add_shortcode('bp-media-uploader', array($this,'render'));
    }
    
    function render(){
        $view = new BPMediaUploadView();
        return $view->render('upload/uploader.php');
    }
    
    
}

?>
