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
		6	=>'private',
		4	=>'friends',
		2	=>'users',
		0	=>'public'
	);

	var $enabled = false;


	/**
	 *
	 */
	function __construct() {

	}

	function privacy_ui() {

	}

	function save( $level = 0, $object_id = false ) {

		if(!array_key_exists($level,$this->settings))
			return false;

		return $this->save_by_object( $level, $object_id );
	}

	private function save_by_object(  $level = 0, $object_id = false ) {
		if($object_id==false)
			return false;

		$level = apply_filters('bp_media_save_privacy', $level);

		return update_post_meta($object_id,'bp_media_privacy',$level);

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
