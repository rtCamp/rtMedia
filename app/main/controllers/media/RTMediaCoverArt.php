<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaCoverArt
 *
 * @author saurabh
 */
class RTMediaCoverArt extends RTMediaUserInteraction{

	/**
	 *
	 */
	function __construct() {
		$defaults = array(
		'action' => 'cover',
		'label' => 'Set as Album Cover',
		'plural' => '',
		'undo_label' => 'Unset as Album Cover',
		'privacy' => 60,
		'countable' => false,
		'single' => false,
		'repeatable' => false,
		'undoable' => true
		);
		parent::__construct($defaults);

	}

	function process(){
		global $rtmedia_query;
		$media_id = $rtmedia_query->action_query->id;

		$this->model = new RTMediaModel();

		$media = $this->model->get(array('id'=>$media_id));

		$media = $media[0];

		$album = $media->album_id;

		$this->model->update(array('cover_art',$media_id),array('id'=>$album));
		return 1;
	}

}

?>
