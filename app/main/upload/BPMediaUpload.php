<?php

/**
 * Description of BPMediaUpload
 *
 * @author joshua
 */
class BPMediaUpload {
    
    private $default_modes = array('file_upload','link_input');
    
    public function __construct($uploaded) {
        
        $this->process($uploaded);
        
    }
    
    function process($uploaded){
        switch($uploaded['mode']){
            case 'file_upload': new BPMediaUploadFile($uploaded);
                break;
            case 'link_input': new BPMediaUploadUrl($uploaded);
                break;
            default:
                do_action('bp_media_upload_'.$mode,$uploaded);
        }
    }
    

}

?>
