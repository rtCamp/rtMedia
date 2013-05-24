<?php

/**
 * Description of BPMediaUpload
 *
 * @author joshua
 */
class BPMediaUpload {

    private $default_modes = array('file_upload', 'link_input');
    var $file = NULL;
    var $post = NULL;
    var $url = NULL;
    var $db = NULL;

    public function __construct($uploaded) {
        $this->file = new BPMediaUploadFile();
        $this->url = new BPMediaUploadUrl();
        $this->post = new BPMediaUploadPost();
        $this->db = new BPMediaUploadDB();

        $file_object = $this->upload($uploaded);

        if ($file_object) {
            $this->post->init($uploaded, $file_object);
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
