<?php

/**
 * Description of BPMediaUpload
 *
 * @author joshua
 */
class RTMediaUpload {

    private $default_modes = array('add_media', 'link_input');
    var $file = NULL;
    var $media = NULL;
    var $url = NULL;

    public function __construct($uploaded) {
        $this->file = new RTMediaUploadFile();
        $this->url = new RTMediaUploadUrl();
        $this->media = new RTMediaMedia();

        $file_object = $this->upload($uploaded);


		$uploaded = $this->verify_album_id($uploaded);

        if ($file_object && $uploaded) {
            if($this->media->add($uploaded, $file_object)){
                do_action('rt_media_after_add_media');
				return true;
            }
        } else {
            return false;
        }
    }
	
	function verify_album_id($uploaded) {

		if(isset($uploaded['album_id']))
			return $uploaded;
		
		if(isset($uploaded['context'])) {
			$uploaded['album_id'] = $uploaded['context_id'];
			return $uploaded;
		}
		
		return false;	// incorrect request without context
	}

    function upload($uploaded) {
        switch ($uploaded['mode']) {
            case 'add_media': return $this->file->init($uploaded['files']);
                break;
            case 'link_input': return $this->url->init($uploaded);
                break;
            default:
                do_action('rt_media_upload_' . $uploaded['mode'], $uploaded);
        }
    }

}

?>
