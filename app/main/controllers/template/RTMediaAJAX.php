<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaAJAX
 *
 * @author udit
 */
class RTMediaAJAX {

	public function __construct() {
		add_action('wp_ajax_rt_media_backbone_template',array($this,'backbone_template'));
		add_action('wp_ajax_rt_media_create_album',array($this,'create_album'));
	}

	function backbone_template() {
		include RTMEDIA_PATH.'templates/media/media-gallery-item.php';
	}
        
        function create_album(){
            if ( isset($_POST['name']) && $_POST['name'] ) {
                $album = new RTMediaAlbum();
                $rt_media_id = NULL;
                $album->add($_POST['name'], get_current_user_id(), true, false, $rt_media_id);
                
                if ( $rt_media_id )
                    echo $rt_media_id;
                else
                    echo false;
                
            } else {
                echo false;
            }
            wp_die();
        }
}

?>
