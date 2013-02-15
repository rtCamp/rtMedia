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

	static function file_array($filepath){

		$path_info = pathinfo($filepath);

		$file['error']	= '';
		$file['name']	= $path_info['basename'];
		$file['type']	= mime_content_type($filepath);
		$file['tmp_name'] = $filepath;
		$file['size']	= filesize( $filepath);

		return $file;

	}

	function create_album($album_name = '',$author_id=1){

		global $bp_media;

		if(array_key_exists('bp_album_import_name',$bp_media->options)){
			if($bp_media->options['bp_album_import_name']!=''){
				$album_name = $bp_media->options['bp_album_import_name'];
			}
		}
		$found_album = BuddyPressMedia::get_wall_album();

		if(count($found_album)< 1){
			$album = new BPMediaAlbum();
			$album->add_album($album_name,$author_id);
			$album_id = $album->get_id();
		}else{
			$album_id = $found_album[0]->ID;
		}
		return $album_id;
	}

	static function add_media($album_id, $title='',$description='',$filepath='',$privacy=0,$author_id=false){


		$files = BPMediaImporter::file_array($filepath);


			$bp_imported_media =new BPMediaHostWordpress();

			$imported_media_id = $bp_imported_media->add_media($title, $description, $album_id, 0, false, false, $files);

			wp_update_post($args=array('ID'=>$imported_media_id,'post_author'=> $author_id));

			$bp_album_privacy = $privacy;
			if($bp_album_privacy == 10)
				$bp_album_privacy = 6;

			$privacy = new BPMediaPrivacy();
			$privacy->save($bp_album_privacy,$imported_media_id);
	}


	function cleanup(){
		return;

	}


}

?>
