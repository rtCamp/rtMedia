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
		$label=__('Set as Album Cover','rt-media');
		parent::__construct('cover',false,$label);

	}

	function process(){
		global $rt_media_query;
		$media_id = $rt_media_query->action_query->id;

		$this->model = new RTMediaModel();

		$media = $this->model->get(array('id'=>$media_id));

		$media = $media[0];

		$album = $media->album_id;

		$this->model->update(array('cover_art',$media_id),array('id'=>$album));
		die(1);
	}

}

?>
