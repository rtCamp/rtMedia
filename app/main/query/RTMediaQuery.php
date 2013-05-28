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

	public $query = '';
	public $media = '';

	public $media_count = 0;
	public $current_media = -1;
	public $in_the_media_loop = false;


	function __construct($query = '') {
		if ( ! empty($query) ) {
			$this->query($query);
		}
	}

	function &query( $query ) {
//		$this->init();
		$this->query = $this->query_vars = wp_parse_args( $query );
		return $this->get_media();
	}

//	function parse_query( $query =  '' ) {
//		if ( ! empty( $query ) ) {
//			$this->init();
//			$this->query = $this->query_vars = wp_parse_args( $query );
//		} elseif ( ! isset( $this->query ) ) {
//			$this->query = $this->query_vars;
//		}
//	}


	function populate_media(){
		$this->model = new BPMediaModel();

		unset($this->query->meta_query);
		unset($this->query->tax_query);

                $pre_media = $this->model->get_media($this->query);

		foreach($pre_media as $pre_medium){
			$this->media[$pre_medium->media_id]=$pre_medium;
		}

		if(is_multisite()){
			foreach($this->media as $mk=>$mv){
				$blogs[$media['blog_id']][$mk]= $mv;
			}


			foreach ($blogs as $blog_id=>&$media){
				switch_to_blog($blog_id);
				$this->populate_post_data($media);
				wp_reset_query();
			}
			restore_current_blog();
		}else{
			$this->populate_post_data($this->media);
		}
	}

	function populate_post_data($media){
		if(!empty($media) && is_array($media)){
			$media_post_query_args = array(
				'post_type'		=> 'any',
				'post_status'		=> 'any',
				'post__in'		=> array_keys($media),
				'meta_query'	=> $this->query_vars->meta_query,
				'tax_query'		=> $this->query_vars->tax_query,
			);
			$media_post_query = new WP_Query($media_post_query_args);
			$media_post_data = $media_post_query->posts;
			foreach ($media_post_data as $post){
				$this->media[$post->ID] = (object)(array_merge((array)$this->media[$post->ID], (array)$post));
                                $this->media[$post->ID]->id = intval($this->media[$post->ID]->id);
                                unset($this->media[$post->ID]->ID);
			}

			$this->media_count = count($this->media);

		}
	}

	function have_media(){
		if ( $this->current_media + 1 < $this->media_count ) {
			return true;
		} elseif ( $this->current_media + 1 == $this->media_count && $this->media_count > 0 ) {
			do_action_ref_array('rt_media_loop_end', array(&$this));
			// Do some cleaning up after the loop
			$this->rewind_media();
		}

		$this->in_the_media_loop = false;
		return false;
	}

	function rt_media() {
		global $rt_media;
		$this->in_the_media_loop = true;

		if ( $this->current_media == -1 ) // loop has just started
			do_action_ref_array('rt_media_loop_start', array(&$this));

		$rt_media = $this->next_media();
	}

	function next_media(){
		$this->current_media++;

		$this->rt_media = $this->media[$this->current_media];
		return $this->rt_media;
	}
	function rewind_media() {
		$this->current_media = -1;
		if ( $this->media_count > 0 ) {
			$this->media = $this->media[0];
		}
	}

	function &get_media(){


		$this->populate_media();

		return $this->media;

	}
}


?>
