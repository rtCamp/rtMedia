<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaImporter
 *
 * @author saurabh
 */
class BPMediaImporter {

	/**
	 *
	 */

	var $active;
	var $import_steps;

	function __construct() {

	}

	function table_exists($table){
		global $wpdb;

		if($wpdb->query("SHOW TABLES LIKE '".$table."'")==1){
			return true;
		}

		return false;
	}

	static function _active($path) {
		if ( ! function_exists( 'is_plugin_inactive' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		global $wpdb;
		$plugin_name = $path;
		$is_active = is_plugin_active( $plugin_name );
		if ( $is_active == true )
			return 1;
		$is_inactive = is_plugin_inactive( $plugin_name );
		if ( $is_inactive == true )
			return 0;
		if ( ! $is_active && ! $is_inactive )
			return -1;
	}

	function file_array($filepath){

		$path_info = pathinfo($filepath);

		$file['error']	= '';
		$file['name']	= $path_info['basename'];
		$file['type']	= mime_content_type($filepath);
		$file['tmp_name'] = $filepath;
		$file['size']	= filesize( $filepath);

		return $file;

	}

	function add_media($title='',$album_id=0,$is_multiple=false,$description='',$group_id=0,$filepath){
		$file = $this->file_array($filepath);
		$class_name = apply_filters('bp_media_transcoder', 'BPMediaHostWordpress', $type);
		$bp_media_entry = new $class_name();
		try {
			$entry = $bp_media_entry->add_media($title, $description, $album_id, $group_id, $is_multiple,$file);
			if (!isset($bp->{BP_MEDIA_SLUG}->messages['updated'][0]))
				$bp->{BP_MEDIA_SLUG}->messages['updated'][0] = __('Upload Successful', BP_MEDIA_TXT_DOMAIN);
		} catch (Exception $e) {
			$bp->{BP_MEDIA_SLUG}->messages['error'][] = $e->getMessage();
		}
	}


	function process_media($title='',$album_id=0,$is_multiple=false,$description='',$group_id=0,$filepath){

		$this->import_privacy();
		$this->add_media($title,$album_id,$is_multiple,$description,$group_id,$filepath);
		$this->cleanup();

	}

	function cleanup(){
		return;

	}

	function import_privacy(){
		return;

	}


}

?>
