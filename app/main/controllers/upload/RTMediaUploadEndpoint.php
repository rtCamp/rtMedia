<?php

/**
 * Description of RTMediaUploadEndpoint
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class RTMediaUploadEndpoint {

	public $upload;

	/**
	 *
	 */
    public function __construct() {
        add_action('rt_media_upload_redirect', array($this, 'template_redirect'));
    }

	/**
	 *
	 */
    function template_redirect() {

        if (!count($_POST)) {
            include get_404_template();
        } else {
            $nonce = $_REQUEST['rt_media_upload_nonce'];
            $mode = $_REQUEST['mode'];
            $rtupload =false;
            if (wp_verify_nonce($nonce, 'rt_media_upload_nonce')) {
                $model = new RTMediaUploadModel();
                $this->upload = $model->set_post_object();
                $rtupload = new RTMediaUpload($this->upload);
            }
            if(isset($_POST["redirect"]) && $_POST["redirect"]=="no" ){
                // Ha ha ha
                if(isset($_POST["activity_update"]) && $_POST["activity_update"]=="true"){
                    header('Content-type: application/json');
                    echo json_encode($rtupload->attachment_ids);
                    die();
                }
                die();
            }else{
                //wp_safe_redirect(wp_get_referer());
            }
        }

        die();
    }

}

?>