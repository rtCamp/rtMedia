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
        add_action('rtmedia_upload_redirect', array($this, 'template_redirect'));
    }

	/**
	 *
	 */
    function template_redirect() {

        if (!count($_POST)) {
            include get_404_template();
        } else {
            $nonce = $_REQUEST['rtmedia_upload_nonce'];
            $mode = $_REQUEST['mode'];
            $rtupload =false;
			$activity_id = -1;
            if (wp_verify_nonce($nonce, 'rtmedia_upload_nonce')) {
                $model = new RTMediaUploadModel();
                $this->upload = $model->set_post_object();
				if(isset($_POST['activity_id']) && $_POST['activity_id']!=-1) {
					$this->upload['activity_id'] = $_POST['activity_id'];
					$activity_id = $_POST['activity_id'];
				}
                $rtupload = new RTMediaUpload($this->upload);
				$mediaObj = new RTMediaMedia();
				$media = $mediaObj->model->get(array('id'=>$rtupload->media_ids[0]));
				if($activity_id==-1) {
					$activity_id = $mediaObj->insert_activity($rtupload->media_ids[0], $media[0]);
				} else {
					$mediaObj->model->update(array( 'activity_id' => $activity_id ), array( 'id' => $rtupload->media_ids[0] ));
				}
            }
            if(isset($_POST["redirect"]) && $_POST["redirect"]=="no" ){
                // Ha ha ha
                if(isset($_POST["rtmedia_update"]) && $_POST["rtmedia_update"]=="true"){
                    header('Content-type: application/json');
                    echo json_encode($rtupload->media_ids);
				} else {
					// Media Upload Case - on album/post/profile/group
					$data = array('activity_id'=>$activity_id);
					header('Content-type: application/json');
					echo json_encode($data);
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