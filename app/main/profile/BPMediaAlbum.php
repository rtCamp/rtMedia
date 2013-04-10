<?php

/**
 * Adds the album screens and functionality
 *
 * @package BuddyPressMedia
 * @subpackage Profile
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 *
 */
class BPMediaAlbum {

    private $id,
            $name,
            $description,
            $url,
            $owner,
            $delete_url,
            $thumbnail,
            $edit_url,
            $media_entries,
            $group_id,
			$filters;

    /**
     *
     * @param type $album_id
     */
    /**
     * Constructs a new BP_Media_Album
     *
     * @param mixed $album_id optional Album ID of the element to be initialized if not defined, returns an empty element.
     *
     * @since BuddyPress Media 2.2
     */

    /**
     *
     * @param type $album_id
     */
    function __construct($album_id = '') {
        if (!$album_id == '') {
            $this->init($album_id);
        }
    }

    /**
     *
     * @param type $album_id
     * @throws Exception
     */
    /**
     * Initializes the object
     *
     * @param mixed $album_id Album ID of the element to be initialized. Can be the ID or the object of the Album
     *
     * @since BuddyPress Media 2.2
     */

    /**
     *
     * @param type $album_id
     * @throws Exception
     */
    function init($album_id) {
        if (is_object($album_id)) {
            $album = $album_id;
        } else {
            $album = &get_post($album_id);
        }
        if (empty($album->ID))
            throw new Exception(__('Sorry, the requested album does not exist.', 'buddypress-media'));

//		$required_access = BPMediaPrivacy::required_access($album_id);
//		$has_access = BPMediaPrivacy::has_access($album_id);

		global $bp;
//		$messages = BPMediaPrivacy::get_messages( 'album',$bp->displayed_user->fullname );
		$this->id = $album->ID;

		$meta_key = get_post_meta($this->id, 'bp-media-key', true);
        /**
         * We use bp-media-key to distinguish if the entry belongs to a group or not
         * if the value is less than 0 it means it the group id to which the media belongs
         * and if its greater than 0 then it means its the author id of the uploader
         * But for use in the class, we use group_id as positive integer even though
         * we use it as negative value in the bp-media-key meta key
         */

		$this->group_id = $meta_key < 0 ? -$meta_key : 0;
//		if($this->group_id<=0){
//			if(!$has_access){
//				throw new Exception($messages[$required_access]);
//			}
//		}

        $this->description = $album->post_content;
        $this->name = $album->post_title;
        $this->owner = $album->post_author;
        if ($this->group_id > 0 && bp_is_active('groups')) {
            $current_group = new BP_Groups_Group($this->group_id);
            $group_url = bp_get_group_permalink($current_group);
            $this->url = trailingslashit($group_url . BP_MEDIA_ALBUMS_SLUG . '/' . $this->id);
            $this->edit_url = trailingslashit($group_url . BP_MEDIA_ALBUMS_SLUG . '/' . BP_MEDIA_ALBUMS_EDIT_SLUG . '/' . $this->id);
            $this->delete_url = trailingslashit($group_url . BP_MEDIA_ALBUMS_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id);
        } else {
            $this->url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_ALBUMS_SLUG . '/' . $this->id);
            $this->edit_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_ALBUMS_SLUG . '/' . BP_MEDIA_ALBUMS_EDIT_SLUG . '/' . $this->id);
            $this->delete_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_ALBUMS_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id);
        }
        $attachments = get_children(array(
            'numberposts' => 1,
            'order' => 'DESC',
            'post_mime_type' => 'image',
            'post_parent' => $this->id,
            'post_type' => 'attachment'
                ));
		$thumbnail_id = get_post_thumbnail_id($this->id);
        if ($thumbnail_id) {
            $metadata = wp_get_attachment_metadata($thumbnail_id);
            $wpattachsize = isset($metadata['sizes']['bp_media_thumbnail'])?'bp_media_thumbnail':'thumbnail';
            $this->thumbnail = wp_get_attachment_image($thumbnail_id, $wpattachsize);
        } elseif ($attachments) {
            foreach ($attachments as $attachment) {
                $metadata = wp_get_attachment_metadata($attachment->ID);
                $wpattachsize = isset($metadata['sizes']['bp_media_thumbnail'])?'bp_media_thumbnail':'thumbnail';
                $this->thumbnail = wp_get_attachment_image($attachment->ID, $wpattachsize);
            }
        } else {
            $this->thumbnail = '<img src ="' . BP_MEDIA_URL . 'app/assets/img/image_thumb.png">';
        }
		$this->filter_entries();
		$this->type = 'album';
        $this->media_entries = get_children(array(
            'post_parent' => $this->id,
            'post_type' => 'attachment',
			'post_mime_type'=> $this->filters
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

    /**
     *
     * @global array $bp_media_count
     * @param type $title
     * @param type $author_id
     * @param type $group_id
     * @return type
     */
    function add_album($title, $author_id = 0, $group_id = 0) {
        do_action('bp_media_before_add_album');
		global $current_user;
		get_currentuserinfo();
        $author_id = $author_id ? $author_id : $current_user->ID;
        $post_vars = array(
            'post_title' => $title,
            'post_name' => $title,
            'post_status' => 'publish',
            'post_type' => 'bp_media_album',
            'post_author' => $author_id
        );
        $album_id = wp_insert_post($post_vars);
        if ($group_id) {
            update_post_meta($album_id, 'bp-media-key', (-$group_id));
        } else {
            update_post_meta($album_id, 'bp-media-key', $author_id);
        }
        $this->init($album_id);
        do_action('bp_media_after_add_album', $this);
    }


    /**
     * Deletes the album and all associated attachments
     *
     * @since BuddyPress Media 2.2
     */

    /**
     *
     * @global array $bp_media_count
     */
    function delete_album() {
        do_action('bp_media_before_delete_album', $this);
        foreach ($this->media_entries as $entry) {
            BPMediaActions::delete_media_handler($entry->ID);
            //do_action('bp_media_before_delete_media',$entry->ID); //Not working for some reason so called the required function directly
            wp_delete_attachment($entry->ID, true);
            do_action('bp_media_after_delete_media', $entry->ID);
        }
        $author_id = $this->owner;
        BPMediaActions::init_count($author_id);
        wp_delete_post($this->id, true);
        do_action('bp_media_after_delete_album', $this);
    }

    /**
     *
     * @param type $title
     * @return boolean
     */
    function edit_album($title = '') {
        do_action('bp_media_before_edit_album', $this);
        if ($title == '') {
            return false;
        } else {
            $this->name = $title;
            $args = array(
                'ID' => $this->id,
                'post_title' => $this->name
            );
            $status = wp_insert_post($args);
            if (get_class($status) == 'WP_Error' || $status == 0) {
                return false;
            } else {
                return true;
            }
        }
        do_action('bp_media_after_edit_album', $this);
    }

    /**
     *
     * @global type $bp_media
     */
    function get_album_gallery_content() {
        ?><li>
            <a href="<?php echo $this->url ?>" title="<?php _e($this->description, 'buddypress-media'); ?>">
        <?php echo $this->thumbnail; ?>
            </a>
            <h3 title="<?php echo $this->name ?>"><a href="<?php echo $this->url ?>" title="<?php _e($this->description, 'buddypress-media'); ?>"><?php echo ( ( strlen($this->name) > 14 ) ? substr($this->name, 0, 14) . "&hellip;" : $this->name ); ?> </a><?php echo ' (' . count($this->media_entries) . ')'; ?></h3>
        </li><?php
    }

    /**
     * Returns the attachments linked with the albume
     *
     * @since BuddyPress Media 2.2
     */

    /**
     *
     * @return type
     */
    function get_entries() {
        return $this->media_entries;
    }

	/**
	 *
	 * @global type $bp_media
	 */
	function filter_entries(){
		global $bp_media;
		$enabled = $bp_media->enabled();
		if(isset($enabled['upload'])) unset($enabled['upload']);
		if(isset($enabled['album'])) unset($enabled['album']);
		foreach($enabled as $type=>$active){
			if($active==true){
				$filters[] = $type;
			}

		}

		if(count($filters)==1) $filters = $filters[0];
		$this->filters = $filters;
	}


    /**
     * Returns the title of the album
     *
     * @since BuddyPress Media 2.2
     */

    /**
     *
     * @return type
     */
    function get_title() {
        return $this->name;
    }

    /**
     * Echoes the title of the album
     *
     * @since BuddyPress Media 2.2
     */
    function the_title() {
        echo $this->name;
    }

    /**
     * Returns the id of the album
     *
     * @since BuddyPress Media 2.2
     */

    /**
     *
     * @return type
     */
    function get_id() {
        return $this->id;
    }
	/**
     * Returns the id of the album
     *
     * @since BuddyPress Media 2.2
     */

    /**
     *
     * @return type
     */
    function get_type() {
        return $this->type;
    }

    /**
     * Returns the url of the album
     *
     * @since BuddyPress Media 2.2
     */

    /**
     *
     * @return type
     */
    function get_url() {
        return $this->url;
    }

    /**
     * Returns the owner's id
     *
     * @since BuddyPress Media 2.2
     */

    /**
     *
     * @return type
     */
    function get_owner() {
        return $this->owner;
    }

    /**
     * Returns the edit url of the album
     */

    /**
     *
     * @return type
     */
    function get_edit_url() {
        return $this->edit_url;
    }

    /**
     * Returns the delete url of the album
     */

    /**
     *
     * @return type
     */
    function get_delete_url() {
        return $this->delete_url;
    }

    /**
     * Returns the group id to which the media belongs, 0 if it does not belong to any group
     */

    /**
     *
     * @return type
     */
    function get_group_id() {
        return $this->group_id;
    }

}
?>