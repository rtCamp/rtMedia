<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaLike
 *
 * @author saurabh
 */
class RTMediaLike extends RTMediaUserInteraction {

	/**
	 *
	 */
	function __construct() {
		$args = array(
		'action' => 'like',
		'label' => 'Like',
		'plural' => 'Likes',
		'undo_label' => 'Unlike',
		'privacy' => 20,
		'countable' => true,
		'single' => false,
		'repeatable' => false,
		'undoable' => true
		);

//


                    parent::__construct($args);

	}

	function process() {

		$actions = $this->model->get( array( 'id' => $this->action_query->id ) );
                $like_media = get_user_meta($this->interactor,"rtmedia_liked_media",true);
                if(strpos("," . $like_media . ",","," . $this->action_query->id . ",") === false){
                    $this->increase =true;
                    if($like_media =="")
                        $like_media = $this->action_query->id . ",";
                    else
                        $like_media .= "," . $this->action_query->id;
                } else {
                    $this->increase =false;
                    $like_media = trim(str_replace("," . $this->action_query->id . ",", ",",",". $like_media .","), ",");
                }
		$actionwa = $this->action.'s';

                $return = array();

                $actions = intval($actions[ 0 ]->{$actionwa});
		if ( $this->increase === true ) {
			$actions ++;
                        $return["next"] = "<span>" .$actions ."</span>" . $this->undo_label;
		} else {
			$actions --;
                        $return["next"] = "<span>" .$actions ."</span>" .  $this->label;
		}
                if($actions <0)
                    $actions = 0;

                $return["count"] = $actions;
		$this->model->update( array( $this->plural => $actions ), array( 'id' => $this->action_query->id ) );

		update_user_meta($this->interactor,'rtmedia_liked_media',$like_media);
                if(isset($_REQUEST["json"]) && $_REQUEST["json"]=="true"){
                    echo json_encode($return);
                    die();
                }
                else{
                    wp_safe_redirect ($_SERVER["HTTP_REFERER"]);
                }
                return $actions;
	}

        function is_liked() {
            $like_media = get_user_meta($this->interactor, "rtmedia_liked_media", true);
            if (strpos("," . $like_media . ",", "," . $this->action_query->id . ",") === false) {
                $this->increase = true;
                return false;
            } else {
                $this->increase = false;
                return true;
            }
        }
        function before_render(){
            $enable_like = true;
            $enable_like = apply_filters('rtmedia_check_enable_disable_like',$enable_like);
            if(!$enable_like)
                return false;
            if($this->is_liked()){
                $this->label =  $this->undo_label;
            }
            $actions = $this->model->get( array( 'id' => $this->action_query->id ) );
            if(isset($actions[ 0 ]->likes)){
                $actions = intval($actions[ 0 ]->likes);
            }else{
                $actions = 0;
            }
            $this->label =  "<span>" .$actions ."</span>" . $this->label;
        }

}

?>
