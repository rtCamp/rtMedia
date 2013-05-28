<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaQuery
 *
 * @author saurabh
 */
class RTMediaQuery {

	var $current_media = -1;
	var $in_the_loop = false;
	var $all_media = array();
	var $query_args = {};

	function __construct() {
		//$this->query_args = $query_args;
	}

	function have_media(){
		if ( $this->current_media + 1 < $this->media_count ) {
			return true;
		} elseif ( $this->current_media + 1 == $this->media_count && $this->media_count > 0 ) {
			do_action_ref_array('rt_media_loop_end', array(&$this));
			// Do some cleaning up after the loop
			$this->rewind_media();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_media(){
		global $rt_media;
		$this->in_the_loop = true;

		if ( $this->current_media == -1 ) // loop has just started
			do_action_ref_array('rt_media_loop_start', array(&$this));

		$rt_media = $this->next_media();
		setup_postdata($rt_media);
	}

	function next_media() {
		$this->current_media++;
		$this->media = $this->all_media[$this->current_media];
		return $this->media;
	}

	function get_post_data($media) {
		$post_query = get_post($media->ID);
		return $post_query;
	}

	function get_media_data($media_id){

	}

	function set_post_data(){
		foreach ($this->all_media as &$media){
			array_merge($media,$this->get_post_data($media->ID));
		}
	}

	function get_media_data(){
		foreach ($this->all_media as &$media){
			array_merge($media,$this->get_media_data($media->ID));
		}
	}

	function set_media_data(){

	}

	function complete_query(){

	}


	function the_title(){
		$this->media = false;
	}

}

?>
