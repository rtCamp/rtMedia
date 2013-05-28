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

	public $query = '', $media = '';

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

	function all_the_ids($media){
		return implode(',',array_keys($media));
	}

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

		}
	}

	function &get_media(){


		$this->populate_media();

		return $this->media;

	}
}


?>
