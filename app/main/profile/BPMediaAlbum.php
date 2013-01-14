<?php

class BPMediaAlbum{
	private $id,
		$name,
		$description,
		$url,
		$owner,
		$delete_url,
		$thumbnail,
		$edit_url,
		$media_entries,
		$group_id;

	/**
	 * Constructs a new BP_Media_Album
	 *
	 * @param mixed $album_id optional Album ID of the element to be initialized if not defined, returns an empty element.
	 *
	 * @since BuddyPress Media 2.2
	 */
	function __construct($album_id = '') {
		if (!$album_id == '') {
			$this->init($album_id);
		}
	}

	/**
	 * Initializes the object
	 *
	 * @param mixed $album_id Album ID of the element to be initialized. Can be the ID or the object of the Album
	 *
	 * @since BuddyPress Media 2.2
	 */
	function init($album_id){
		if (is_object($album_id)) {
			$album = $album_id;
		} else {
			$album = &get_post($album_id);
		}
		if (empty($album->ID))
			throw new Exception(__('Sorry, the requested album does not exist.', BP_MEDIA_TXT_DOMAIN));
		$this->id = $album->ID;
		$this->description = $album->post_content;
		$this->name = $album->post_title;
		$this->owner = $album->post_author;
		$meta_key = get_post_meta($this->id, 'bp-media-key', true);
		/**
		 * We use bp-media-key to distinguish if the entry belongs to a group or not
		 * if the value is less than 0 it means it the group id to which the media belongs
		 * and if its greater than 0 then it means its the author id of the uploader
		 * But for use in the class, we use group_id as positive integer even though
		 * we use it as negative value in the bp-media-key meta key
		 */
		$this->group_id = $meta_key<0?-$meta_key:0;
		if($this->group_id>0){
			$current_group = new BP_Groups_Group($this->group_id);
			$group_url = bp_get_group_permalink($current_group);
			$this->url = trailingslashit($group_url . BP_MEDIA_ALBUMS_SLUG . '/' . $this->id);
			$this->edit_url = trailingslashit($group_url . BP_MEDIA_ALBUMS_SLUG . '/' . BP_MEDIA_ALBUMS_EDIT_SLUG . '/' . $this->id);
			$this->delete_url = trailingslashit($group_url . BP_MEDIA_ALBUMS_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id);
		}
		else{
			$this->url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_ALBUMS_SLUG . '/' . $this->id);
			$this->edit_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_ALBUMS_SLUG . '/' . BP_MEDIA_ALBUMS_EDIT_SLUG . '/' . $this->id);
			$this->delete_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_ALBUMS_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id);
		}
		$attachments = get_children(array(
			'numberposts' => 1,
			'order'=> 'DESC',
			'post_mime_type' => 'image',
			'post_parent' => $this->id,
			'post_type'	=>	'attachment'
		));
		$attachments_featured = get_children(array(
			'numberposts' => 1,
			'meta_key' => 'featured',
			'orderby' => 'meta_value',
			'post_mime_type' => 'image',
			'post_parent' => $this->id,
			'post_type'	=>	'attachment',
		));
		if($attachments_featured) {
                        foreach($attachments_featured as $attachment) {
                                $this->thumbnail = '<span><img src="'.wp_get_attachment_thumb_url( $attachment->ID ).'"></span>';
                        }
                }elseif ($attachments) {
                        foreach($attachments as $attachment) {
                                $this->thumbnail = '<span><img src="'.wp_get_attachment_thumb_url( $attachment->ID ).'"></span>';
                        }
                }else{
			$this->thumbnail = '<img src ="'.BP_MEDIA_URL.'app/assets/img/image_thumb.png">';
		}
		$this->media_entries = get_children(array(
			'post_parent' => $this->id,
			'post_type'	=>	'attachment'
		));
	}

	/**
	 * Adds a new album and initializes the object with the new album
	 *
	 * @param string $title The title of the album.
	 * @param string $author_id Optional The author id, defaults to zero in which case takes the logged in user id.
	 * @param string $group_id Optional The group id to which the album belongs, defaults to 0 meaning its not attached with a group.
	 *
	 * @since BuddyPress Media 2.2
	 */
	function add_album($title,$author_id = 0, $group_id = 0){
		do_action('bp_media_before_add_album');
		$author_id = $author_id?$author_id:get_current_user_id();
		$post_vars = array(
				'post_title'	=>	$title,
				'post_name'		=>	$title,
				'post_status'=>	'publish',
				'post_type'	=>	'bp_media_album',
				'post_author'=> $author_id
			);
		BPMediaActions::init_count($author_id);
		global $bp_media_count;
		$album_id = wp_insert_post($post_vars);
		if($group_id){
			add_post_meta($album_id, 'bp-media-key', (-$group_id));
		}
		else{
			add_post_meta($album_id, 'bp-media-key', $author_id);
		}
		$this->init($album_id);
		$bp_media_count['albums'] = intval(isset($bp_media_count['albums'])?$bp_media_count['albums']:0) + 1;
		bp_update_user_meta($author_id, 'bp_media_count', $bp_media_count);
		do_action('bp_media_after_add_album',$this);
		return $album_id;
	}

	/**
	 * Deletes the album and all associated attachments
	 *
	 * @since BuddyPress Media 2.2
	 */
	function delete_album(){
		do_action('bp_media_before_delete_album',  $this);
		foreach($this->media_entries as $entry){
			BPMediaActions::delete_media_handler($entry->ID);
			//do_action('bp_media_before_delete_media',$entry->ID); //Not working for some reason so called the required function directly
			wp_delete_attachment($entry->ID,true);
			do_action('bp_media_after_delete_media',$entry->ID);
		}
		$author_id = $this->owner;
		BPMediaActions::init_count($author_id);
		wp_delete_post($this->id,true);
		global $bp_media_count;
		$bp_media_count['albums'] = intval(isset($bp_media_count['albums'])?$bp_media_count['albums']:0) - 1;
		bp_update_user_meta($author_id, 'bp_media_count', $bp_media_count);
		do_action('bp_media_after_delete_album', $this);
	}

	function edit_album($title=''){
		do_action('bp_media_before_edit_album',$this);
		if($title==''){
			return false;
		}
		else{
			$this->name = $title;
			$args = array(
				'ID'	=> $this->id,
				'post_title'=>$this->name
			);
			$status = wp_insert_post($args);
			if(get_class($status)=='WP_Error'||$status==0){
				return false;
			}
			else{
				return true;
			}
		}
		do_action('bp_media_after_edit_album',$this);
	}

	function get_album_gallery_content(){
            global $bp_media
		?><li>
			<a href="<?php echo $this->url ?>" title="<?php _e($this->description,BP_MEDIA_TXT_DOMAIN); ?>">
				<?php echo $this->thumbnail; ?>
			</a>
			<h3 title="<?php echo $this->name ?>"><a href="<?php echo $this->url ?>" title="<?php _e($this->description,BP_MEDIA_TXT_DOMAIN); ?>"><?php echo $this->name;?></a><?php echo ' ('.count($this->media_entries).')'; ?></h3>
		</li><?php
	}

	/**
	 * Returns the attachments linked with the albume
	 *
	 * @since BuddyPress Media 2.2
	 */
	function get_entries(){
		return $this->media_entries;
	}

	/**
	 * Returns the title of the album
	 *
	 * @since BuddyPress Media 2.2
	 */
	function get_title(){
		return $this->name;
	}

	/**
	 * Echoes the title of the album
	 *
	 * @since BuddyPress Media 2.2
	 */
	function the_title(){
		echo $this->name;
	}

	/**
	 * Returns the id of the album
	 *
	 * @since BuddyPress Media 2.2
	 */
	function get_id(){
		return $this->id;
	}

	/**
	 * Returns the url of the album
	 *
	 * @since BuddyPress Media 2.2
	 */
	function get_url(){
		return $this->url;
	}

	/**
	 * Returns the owner's id
	 *
	 * @since BuddyPress Media 2.2
	 */
	function get_owner(){
		return $this->owner;
	}

	/**
	 * Returns the group id to which the media belongs, 0 if it does not belong to any group
	 */
	function get_group_id(){
		return $this->group_id;
	}
}
?>