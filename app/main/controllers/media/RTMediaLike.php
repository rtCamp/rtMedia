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
		$actions = intval($actions[ 0 ]->{$actionwa});
		if ( $this->increase === true ) {
			$actions ++;
		} else {
			$actions --;
		}
                if($actions <0)
                    $actions = 0;
		$this->model->update( array( $this->plural => $actions ), array( 'id' => $this->action_query->id ) );
                
		update_user_meta($this->interactor,'rtmedia_liked_media',$like_media);
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

}

?>
