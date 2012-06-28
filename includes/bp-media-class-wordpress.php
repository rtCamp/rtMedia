<?php

/**
 * 
 */
class BP_Media_Host_Wordpress implements BP_Media_Host {

	private $id, //id of the entry
		$name, //Name of the entry
		$description, //Description of the entry
		$url, //URL of the entry
		$type, //Type of the entry (Video, Image or Audio)
		$owner;   //Owner of the entry

	function __construct($media_id = 0) {
		if (!$media_id == '') {
			$this->init($media_id);
		}
	}

	function init($media_id = '') {
		if (is_object($media_id)) {
			$media = $media_id;
		} else {
			$media = &get_post($media_id);
		}
		if (empty($media->ID))
			return false;
		$this->id= $media->ID;
		$this->description=$media->post_content;
		$this->name= $media->post_title;
		$this->owner=$media->post_author;
		$this->type=  get_post_meta($media->ID,'bp_media_type',true);
		switch ($this->type) {
				case 'video' : 
					$this->url=trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG . '/watch/' . $$this->id);
					break;
				case 'audio' : 
					$this->url=trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG . '/listen/' . $this->id);
					break;
				case 'image' :
					$this->url = trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG . '/view/' . $this->id);
					break;
				default : 
					return false;
			}
	}

	function add_media($name, $description) {
		global $bp, $wpdb;
		include_once(ABSPATH . 'wp-admin/includes/file.php');
		include_once(ABSPATH . 'wp-admin/includes/image.php');
		//media_handle_upload('async-upload', $_REQUEST['post_id']);
		$postarr = array(
			'post_status' => 'draft',
			'post_type' => 'bp_media',
			'post_content' => $description,
			'post_title' => $name
		);
		$post_id = wp_insert_post($postarr);


		$file = wp_handle_upload($_FILES['bp_media_file']);
		if (isset($file['error']) || $file === null) {
			wp_delete_post($post_id, true);
			return false;
		}

		$attachment = array();
		$url = $file['url'];
		$type = $file['type'];
		$file = $file['file'];
		$title = $name;
		$content = $description;
		$attachment = array(
			'post_mime_type' => $type,
			'guid' => $url,
			'post_title' => $title,
			'post_content' => $content,
			'post_parent' => $post_id,
		);
		switch ($type) {
			case 'video/mp4' :
				$type = 'video';
				break;
			case 'audio/mpeg' :
				$type = 'audio';
				break;
			case 'image/gif' :
			case 'image/jpeg' :
			case 'image/png' :
				$type = 'image';
				break;
			default : unlink($file);
				wp_delete_post($post_id, true);
				unlink($file);
				$activity_content = false;
				return false;
		}
		$activity_content = '[bp_media_content id="' . $post_id . '"]';
		$attachment_id = wp_insert_attachment($attachment, $file, $post_id);
		if (!is_wp_error($attachment_id)) {
			wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file));
		} else {
			wp_delete_post($post_id, true);
			unlink($file);
			return false;
		}
		$postarr['ID'] = $post_id;
		$postarr['post_mime_type'] = $type;
		$postarr['post_status'] = 'publish';
		//$wpdb->update( $wpdb->posts, $postarr, array( 'ID' => $post_id ) );
		wp_insert_post($postarr);
		$activity_id = bp_media_record_activity(array(
			'action' => '[bp_media_action id="' . $post_id . '"]',
			'content' => $activity_content,
			'primary_link' => '[bp_media_url id="' . $post_id . '"]',
			'type' => 'media_upload'
			));
		bp_activity_update_meta($activity_id, 'bp_media_parent_post', $post_id);
		update_post_meta($post_id, 'bp_media_child_activity', $activity_id);
		update_post_meta($post_id, 'bp_media_child_attachment', $attachment_id);
		update_post_meta($post_id, 'bp_media_type', $type);
		update_post_meta($post_id, 'bp_media_hosting', 'wordpress');
		$this->id = $post_id;
		$this->name = $name;
		$this->description = $description;
		$this->owner = bp_loggedin_user_id();
		$this->type = $type;
		$this->url = $url;
	}

	function remove_media() {
		
	}

	function update_media() {
		
	}

	function display_media() {
		
	}

	function display_media_activity_content() {
		if (bp_is_activity_component()) {
			$activity_content = '<span class="bp_media_title">' . $this->name . '</span><span class="bp_media_description">' . $description . '</span><span class="bp_media_content">';
			switch ($type) {
				case 'video/mp4' : $activity_content.='<video src="' . $url . '" width="640" height="480" type="video/mp4" id="bp_media_video_' . $post_id . '" controls="controls" preload="none"></video><script>jQuery("#bp_media_video_' . $post_id . '").mediaelementplayer();</script></span>';
					$type = 'video';
					break;
				case 'audio/mpeg' : $activity_content.='<audio src="' . $url . '" type="audio/mp3" id="bp_media_audio_' . $post_id . '" controls="controls" preload="none" ></audio><script>jQuery("#bp_media_audio_' . $post_id . '").mediaelementplayer();</script>';
					$type = 'audio';
					break;
				case 'image/gif' :
				case 'image/jpeg' :
				case 'image/png' : $activity_content.='<img src="' . $url . '" id="bp_media_image_' . $post_id . '" />';
					$activity_url = trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG . '/view/' . $post_id);
					$type = 'image';
					break;
				default : unlink($file);
					wp_delete_post($post_id, true);
					unlink($file);
					$activity_content = false;
					return false;
			}
			$activity_content .= '</span>';
			return $activity_content;
		}
	}

	function display_media_activity_url() {
		if (!bp_is_activity_component())
			return false;
		$activity_url = bp_media_get_the_permalink($this->id);
		return $activity_url;
	}

	function display_media_activity_action() {
		if (!bp_is_activity_component())
			return false;
		$activity_action=sprintf(__("%s uploaded a media."), bp_core_get_userlink($this->owner));
		return $activity_action;
	}

}

?>