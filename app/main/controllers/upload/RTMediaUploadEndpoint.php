<?php

/**
 * Description of BPMediaUploadEndpoint
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class RTMediaUploadEndpoint {

	public $upload;

    public function __construct() {
        add_action('rt_media_upload_redirect', array($this, 'template_redirect'));
        $media = new RTMediaMedia();
		$media->delete_hook(); // should be placed somewhere else ( just does the trick here )
    }


    function template_redirect() {
		error_log("redirect\n");
        if (!count($_POST)) {
			error_log("404\n");
            include get_404_template();
        } else {
            $nonce = $_REQUEST['bp_media_upload_nonce'];
            $mode = $_REQUEST['mode'];
			error_log("b4 verify nonce");
            if (wp_verify_nonce($nonce, 'bp_media_' . $mode)) {
                $model = new RTMediaUploadModel();
                $this->upload = $model->set_post_object();

                $upload = new RTMediaUpload($this->upload);
            }
            wp_safe_redirect(wp_get_referer());
        }

        exit;
    }

}

?>
