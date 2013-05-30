<?php

/**
 * Description of BPMediaUpload
 *
 * @author joshua
 */
class RTMediaUpload {

    private $default_modes = array('file_upload', 'link_input');
    var $file = NULL;
    var $post = NULL;
    var $url = NULL;
    var $db = NULL;

    public function __construct($uploaded) {
        $this->file = new RTMediaUploadFile();
        $this->url = new RTMediaUploadUrl();
        $this->post = new RTMediaUploadPost();
        $this->db = new RTMediaUploadDB();

        $file_object = $this->upload($uploaded);

        if ($file_object) {
            if($this->post->init($uploaded, $file_object)){
                do_action('bp_media_after_add_media');
            }
        } else {
            return false;
        }
    }

    function upload($uploaded) {
        switch ($uploaded['mode']) {
            case 'file_upload': return $this->file->init($uploaded['files']);
                break;
            case 'link_input': return $this->url->init($uploaded);
                break;
            default:
                do_action('bp_media_upload_' . $mode, $uploaded);
        }
    }

}

?>
