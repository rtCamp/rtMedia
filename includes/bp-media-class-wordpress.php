<?php

/**
 * 
 */

class BP_Media_Host_Wordpress implements BP_Media {
	
	private $id,			//id of the entry
			$name,			//Name of the entry
			$description,	//Description of the entry
			$url,			//URL of the entry
			$type,			//Type of the entry (Video, Image or Audio)
			$owner;			//Owner of the entry.
	
	
	function add_media($name,$description) {
		include_once(ABSPATH.'wp-admin/includes/file.php');
		wp_insert_post($postarr);
		$file=wp_handle_upload($_FILES['bp_media_file']);
		$attachment=array();
		$url = $file['url'];
		$type = $file['type'];
		$file = $file['file'];
		$title = $name;
		$content = $description;
		$attachment =  array(
			'post_mime_type' => $type,
			'guid' => $url,
			'post_title' => $title,
			'post_content' => $content,
			'post_type'		=>	'bp_media'
		);
		if ( isset( $attachment['ID'] ) )
			unset( $attachment['ID'] );
		$id = wp_insert_attachment($attachment, $file, $post_id);

	}
	function remove_media() {
		
	}
	function update_media() {
		
	}
	function display_media() {
		
	}
}
?>