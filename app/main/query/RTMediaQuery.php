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

	function __construct() {

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
		setup_postdata($media);
	}

	function next_media() {

		$this->current_media++;

		$this->media = $this->all_media[$this->current_media];
		return $this->media;
	}

}

?>
