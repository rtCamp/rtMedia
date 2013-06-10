<?php

/**
 * Description of BPMediaUpload
 *
 * @author joshua
 */
class RTMediaUpload {

    private $default_modes = array('file_upload', 'link_input');
    var $file = NULL;
    var $media = NULL;
    var $url = NULL;

    public function __construct($uploaded) {
        $this->file = new RTMediaUploadFile();
        $this->url = new RTMediaUploadUrl();
        $this->media = new RTMediaMedia();

        $file_object = $this->upload($uploaded);

		print_r($file_object);
		print_r($uploaded);
		if ($file_object && $uploaded) {
            if($this->media->add($uploaded, $file_object)){
                do_action('rt_media_after_add_media');
				echo '\n inside and return true';
				return true;
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
                do_action('rt_media_upload_' . $uploaded['mode'], $uploaded);
        }
    }

}

?>
