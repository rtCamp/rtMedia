<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaPrivacy
 *
 * @author saurabh
 */
class BPMediaPrivacy {

	var $settings = array(
		'private' => false,
		'friends' => false,
		'users' => false,
		'public' => true
	);

	var $enabled = false;

	var $contexts = array(
		'site'		=> false,
		'group'		=> false,
		'profile'	=> true
	);


	/**
	 *
	 */
	function __construct() {

	}

	function privacy_ui() {

	}

	function save( $context = '', $settings = array( ), $object_id = false, $object_type = 'media' ) {
		if ( empty( $settings ) )
			return false;

		$defaults = $this->settings;
		$settings = wp_parse_args( $settings, $defaults );
		return $this->save_by_object( $context, $settings, $object_id, $object_type );
	}

	private function save_by_object( $context = '', $settings = array( ), $object_id = false, $object_type = 'media' ) {
		if($object_id==false)
			return false;

		$settings = apply_filters('bp_media_save_privacy', $settings);

		switch ($object_type){
			case 'media':
				return update_post_meta($object_id,'bp_media_privacy',$settings);
				break;
			case 'profile':
				return update_user_meta($object_id,'bp_media_privacy',$settings);
				break;
			case 'activity':
				break;
			case 'group':
				break;

		}
		//do_action('bp_media_non_media_privacy',$object_id, $settings);

	}
	function check($object_id=false, $object_type='media'){
		if($object_id==false)
			return;
		switch ($object_type){
			case 'media':
				$settings = get_post_meta($object_id,'bp_media_privacy');
				break;
			case 'profile':
				get_user_meta($object_id,'bp_media_privacy',$settings);
				return update_user_meta($object_id,'bp_media_privacy',$settings);
				break;
			case 'activity':
				break;
			case 'group':
				break;

		}


	}

	function get_friends(){

	}


}

?>
