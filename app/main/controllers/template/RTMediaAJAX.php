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
		add_action('wp_ajax_rtmedia_backbone_template',array($this,'backbone_template'));
		add_action('wp_ajax_rtmedia_create_album',array($this,'create_album'));
	}

	function backbone_template() {
		include RTMEDIA_PATH.'templates/media/media-gallery-item.php';
	}

        function create_album(){
            if ( isset($_POST['name']) && $_POST['name'] && is_rtmedia_album_enable()) {
                if(isset($_POST['context']) && $_POST['context'] =="group"){
                    $group_id = !empty( $_POST['group_id']) ? $_POST['group_id'] : '';
                    if(can_user_create_album_in_group($group_id) == false){
                        echo false;
                        wp_die();
                    }
                }
                 $create_album = apply_filters("rtm_is_album_create_enable",true);
		if(!$create_album) {
		    echo false;
		    wp_die();
		}
		$create_album = apply_filters("rtm_display_create_album_button",true, $_POST['context_id']);
		if(!$create_album) {
		    echo false;
		    wp_die();
		}
		$album = new RTMediaAlbum();
                $rtmedia_id = $album->add($_POST['name'], get_current_user_id(), true, false, $_POST['context'], $_POST['context_id']);
                $rtMediaNav = new RTMediaNav();
                if (  $_POST['context'] == "group" ) {
                    $rtMediaNav->refresh_counts ( $_POST['context_id'], array( "context" =>  $_POST['context'], 'context_id' => $_POST['context_id'] ) );
                } else {
                    $rtMediaNav->refresh_counts ( get_current_user_id(), array( "context" => "profile", 'media_author' => get_current_user_id() ) );
                }
                if ( $rtmedia_id )
                    echo $rtmedia_id;
                else
                    echo false;

            } else {
                echo false;
            }
            wp_die();
        }
}
