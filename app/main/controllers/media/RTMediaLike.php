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
		$label=__('Like','rtmedia');
		parent::__construct('like',false,$label);

	}

	function process() {


		$this->model = new RTMediaModel();
		$actions = $this->model->get( array( 'id' => $this->action_query->id ) );
		$actionwa = $this->actions;
		$actions = $actions[ 0 ]->$actionwa;
		if ( $this->increase === true ) {
			$actions ++;
		} else {
			$actions --;
		}

		$this->model->update( array( $this->actions => $actions ), array( 'id' => $this->action_query->id ) );
		update_user_meta($user_id,'rtmedia_liked_media',$actions);
		return $actions;
	}

}

?>
