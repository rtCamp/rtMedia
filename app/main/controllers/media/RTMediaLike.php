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
		//print_r($actions);
		$actionwa = $this->action.'s';
		$actions = $actions[ 0 ]->{$actionwa};
		if ( $this->increase === true ) {
			$actions ++;
		} else {
			$actions --;
		}

		$this->model->update( array( $this->plural => $actions ), array( 'id' => $this->action_query->id ) );
		update_user_meta($this->interactor,'rtmedia_liked_media',$actions);
		return $actions;
	}

}

?>
