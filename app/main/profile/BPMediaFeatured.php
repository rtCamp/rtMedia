<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaFeatured
 *
 * @author saurabh
 */
class BPMediaFeatured {

	/**
	 *
	 */
	public $featured,$user, $settings;

	function __construct($user_id=false) {
		$this->init($user_id);
	}

	function init($user_id=false){
		if(!$user_id){
			$user = bp_displayed_user_id();
		}else{
			$user = $user_id;
		}
		$this->user = $user_id;
		$this->get_featured();
		$this->settings();
	}

	function set($media_id=false){
		if(!$media_id) return;
		bp_update_user_meta($this->user,'bp_media_featured_media',$media_id);
		$this->get();
	}

	function get(){
		$this->featured = bp_get_user_meta($this->user,'bp_media_featured_media', true);
	}

	function settings(){
		$settings = array();

		$this->settings = $settings;
	}

	function latest_update(){
		if($this->settings['latest_update']==true){
			add_filter( 'bp_activity_latest_update_content', array( $this, 'override_update' ) );
		}
	}

	function override_update($content){
		$featured_id = $this->featured;
		$featured_content = get_featured_content($featured_id);
		return $featured_content;

	}

	function valid_type($type){

	}

	function add_button(){

	}





	function featured_object(){

	}

	function link(){

	}
	function display(){

	}

}

function bp_media_featured($user_id=false){
	$featured = BPMediaFeatured($user_id);
	echo $featured->display();

}

function bp_media_get_featured($user_id=false){
	$featured = BPMediaFeatured($user_id);
	return $featured->featured_object();
}

?>
