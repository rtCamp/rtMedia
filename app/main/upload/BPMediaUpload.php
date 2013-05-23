<?php

/**
 * Description of BPMediaUpload
 *
 * @author joshua
 */
class BPMediaUpload {
    
    private $default_modes = array('file_upload','link_input');
    var $file = NULL;
    var $post = NULL;
    var $url  = NULL;
    var $db = NULL;
    
    public function __construct($uploaded) {
        $nonce = $_REQUEST['bp_media_upload_nonce'];
        if (wp_verify_nonce($nonce, 'bp_media_'.$uploaded['mode'])){
            
            $this->file = new BPMediaUploadFile();
            $this->url = new BPMediaUploadUrl();
            $this->post = new BPMediaUploadPost();
            $this->db = new BPMediaUploadDB();
            
            $file_object = $this->upload($uploaded);
            $this->post->init($uploaded,$file_object);
            
        } else {
            return;
        }
    }
    
    function upload($uploaded){
        switch($uploaded['mode']){
            case 'file_upload': return $this->file->init($uploaded['files']);
                break;
            case 'link_input': return $this->url->init($uploaded);
                break;
            default:
                do_action('bp_media_upload_'.$mode,$uploaded);
        }
    }
    

}

?>
