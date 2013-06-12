<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaAlbum
 *
 * @author Udit Desai <udit.desai@rtcamp.com>
 */
class RTMediaAlbum {

	/**
	 *
	 * @var type 
	 * 
	 * Media object associated with the album. It works as an interface
	 * for the actions specific the media from this album
	 */
	var $rt_media_object;

	/**
	 * 
	 */
	public function __construct() {
		add_action('init', 'register_post_types');
		$this->rt_media_object = new RTMediaMedia();
	}

	/**
	 * Register Custom Post Types required by rtMedia
	 */
	function register_post_types() {

		/* Set up Album labels */
		$album_labels = array(
			'name' => __( 'Albums', 'rtmedia' ),
			'singular_name' => __( 'Album', 'rtmedia' ),
			'add_new' => __( 'Create', 'rtmedia' ),
			'add_new_item' => __( 'Create Album', 'rtmedia' ),
			'edit_item' => __( 'Edit Album', 'rtmedia' ),
			'new_item' => __( 'New Album', 'rtmedia' ),
			'all_items' => __( 'All Albums', 'rtmedia' ),
			'view_item' => __( 'View Album', 'rtmedia' ),
			'search_items' => __( 'Search Albums', 'rtmedia' ),
			'not_found' => __( 'No album found', 'rtmedia' ),
			'not_found_in_trash' => __( 'No album found in Trash', 'rtmedia' ),
			'parent_item_colon' => '',
			'menu_name' => __( 'Albums', 'rtmedia' )
		);

		/* Set up Album post type arguments */
		$album_args = array(
			'labels' => $album_labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => false,
			'show_in_menu' => false,
			'query_var' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array(
				'title',
				'author',
				'thumbnail',
				'excerpt',
				'comments'
			)
		);

		/* register Album post type */
		register_post_type( 'rt_media_album', $album_args );
	}

	
	/**
	 * Method verifies the nonce passed while performing any CRUD operations
	 * on the album.
	 * 
	 * @param type $mode
	 * @return boolean
	 */
	function verify_nonce($mode) {

		$nonce = $_REQUEST["rt_media_{$mode}_album_nonce"];
		$mode = $_REQUEST['mode'];
		if (wp_verify_nonce($nonce, 'rt_media_' . $mode))
			return true;
		else
			return false;
	}

	/**
	 * returns user_id of the current logged in user in wordpress
	 * 
	 * @global type $current_user
	 * @return type
	 */
	function get_current_author() {

		global $current_user;
		get_currentuserinfo();
		return $current_user->ID;
	}

	/**
	 * Adds a new album
	 * 
	 * @global type $rt_media_interaction
	 * @param type $title
	 * @param type $author_id
	 * @param type $new
	 * @param type $post_id
	 * @return type
	 */
	function add($title = '', $author_id = false, $new = true, $post_id = false) {

		/* action to perform any task before adding the album */
		do_action('rt_media_before_add_album');

		$author_id = $author_id ? $author_id : $this->get_current_author();

		/* Album Details which will be passed to Database query to add the album */
		$post_vars = array(
			'post_title' => (empty($title)) ? 'Untitled Album' : $title,
			'post_status' => 'publish',
			'post_type' => 'rt_media_album',
			'post_author' => $author_id
		);

		/* Check whether to create a new album in wp_post table
		 * This is the case when a user creates a album of his own. We need to
		 * create a separte post in wp_post which will work as parent for
		 * all the media uploaded to that album
		 * 
		 *  */
		if($new)
			$album_id = wp_insert_post($post_vars);
		/**
		 * if user uploads any media directly to a post or a page or any custom
		 * post then the context in which the user is uploading a media becomes
		 * an album in itself. We do not need to create a separate album in this
		 * case.
		 */
		else $album_id = $post_id;

		$current_album = get_post($album_id, ARRAY_A);

		// add in the media since album is also a media
		//defaults
		global $rt_media_interaction;
		$attributes = array(
			'blog_id' => get_current_blog_id(),
			'media_id' => $album_id,
			'album_id' => false,
			'media_title' => $current_album['post_title'],
			'media_author' => $current_album['post_author'],
			'media_type' => 'album',
			'context' => $rt_media_interaction->context->type,
			'context_id' => $rt_media_interaction->context->id,
			'activity_id' => false,
			'privacy' => false
		);

		$this->rt_media_object->rt_insert_album($attributes);

		/* action to perform any task after adding the album */
		do_action('rt_media_after_add_album', $this);

		return $album_id;
	}

	/**
	 * Wrapper method to add a global album
	 * 
	 * @param type $title
	 * @return boolean
	 */
	function add_global($title ='') {

		$super_user_ids = get_super_admins();
		$author_id = $this->get_current_author();
		/**
		 * only admin privilaged user can add a global album
		 */
		if( in_array($author_id, $super_user_ids) ) {
			$album_id = $this->add($title, $author_id);

			$this->save_globals($album_id);

			return $album_id;
		} else
			return false;
	}

	/**
	 * Get the list of all global albums
	 * @return type
	 */
	static function get_globals() {
		return get_site_option('rt-media-global-albums',true);
	}

	/**
	 * There is a default global album which works as a Wall Post Album for the
	 * user.
	 * 
	 * @return type
	 */
	static function get_default() {
		$albums = self::get_globals();

		return $albums[0];
	}

	/**
	 * Save global albums for newly added album
	 * 
	 * @param type $album_ids
	 * @return boolean
	 */
	function save_globals($album_ids = false) {

		if(!$album_ids)
			return false;

		$albums = self::get_globals();

		if(!is_array($album_ids))
			$album_ids = array($album_ids);

		array_merge($albums, $album_ids);
		update_site_option('rt-media-global-albums', $albums);
	}

	/**
	 * Wrapper method to update details for any global album
	 * 
	 * @param type $id
	 * @param type $title
	 * @return boolean
	 */
	function update_global($id, $title = '') {

		/**
		 * Only admin can update global albums
		 */
		$super_user_ids = get_super_admins();
		if( in_array($this->get_current_author(), $super_user_ids) ) {

			return $this->update($id, $title);
		}
		else
			return false;
	}

	/**
	 * Update any album. Generic method for all the user.
	 * 
	 * @param type $id
	 * @param type $title
	 * @return boolean
	 */
	function update($id, $title = '') {

		/* Action to perform before updating the album */
		do_action('rt_media_before_update_album', $this);
		if ( empty($title) && empty($id) ) {
			return false;
		} else {

			$args = array(
				'ID' => $id,
				'post_title' => $title
			);
			$status = wp_insert_post($args);
			if (get_class($status) == 'WP_Error' || $status == 0) {
				return false;
			} else {
				/* Action to perform after updating the album */
				do_action('rt_media_after_update_album', $this);
				return true;
			}
		}

	}

	/**
	 * Wrapper method to delete a global album
	 * 
	 * @param type $id
	 * @return boolean
	 */
	function delete_global($id) {

		/**
		 * Only admin can delete a global album
		 */
		$super_user_ids = get_super_admins();
		if( in_array($this->get_current_author(), $super_user_ids) ) {

			$default_album = self::get_default();

			/**
			 * Default album is NEVER deleted.
			 */
			if($id == $default_album)
				return false;

			/**
			 * If a global album is deleted then all the media of that album
			 * is merged to the default global album and then the album is deleted.
			 */
			//merge with the default album
			$this->merge($default_album, $id);

			return $this->delete($id);
		}
		else
			return false;
	}

	/**
	 * Generic method to delete any album
	 * 
	 * @param type $id
	 * @return type
	 */
	function delete($id) {

		/* action to perform any task befor deleting an album */
		do_action('rt_media_before_delete_album', $this);

		/**
		 * First fetch all the media from that album
		 */
		add_filter('rt_db_model_per_page', array($this,'set_queries_per_page'),10,2);
		$page = 1;
		$flag = true;

		/**
		 * Delete each media from the album first
		 */
		while( $media = $this->rt_media_object->rt_media_model->get_by_album_id($id, $page) ) {

			$media_id = $media['result'][0]['media_id'];

			$flag = wp_delete_attachment($media_id);

			if(!$flag)
				break;

			$page++;
		}

		/**
		 * If all the media are deleted from the album then delete the album at last.
		 */
		if($flag) {
			$this->rt_media_object->delete($id);
		}

		/* action to perform any task after deleting an album */
		do_action('rt_media_after_delete_album', $this);
		return $flag;

	}

	/**
	 * Helper function to set number of queries in pagination
	 * 
	 * @param int $per_page
	 * @param type $table_name
	 * @return int
	 */
	function set_queries_per_page($per_page, $table_name) {

		$per_page = 1;
		return $per_page;
	}

	/**
	 * Generic function to merge two albums
	 * 
	 * @param type $primary_album_id
	 * @param type $secondary_album_id
	 * @return type
	 */
	function merge($primary_album_id, $secondary_album_id) {

		add_filter('rt_db_model_per_page', array($this,'set_queries_per_page'),10,2);
		$page = 1;

		/**
		 * Transfer all the media from secondary album to primary album
		 */
		while( $media = $this->rt_media_object->rt_media_model->get_by_album_id($secondary_album_id, $page) ) {

			$media_id = $media['result'][0]['media_id'];
			$this->rt_media_object->move($media_id,$primary_album_id);

			$page++;
		}

		return $primary_album_id;
	}

	/**
	 * Convert a post which is not indexed in rtMedia to an album.
	 * 
	 * All the attachments from that post will become media of the new album.
	 * 
	 * @global type $wpdb
	 * @param type $post_id
	 * @return boolean
	 */
	function convert_post($post_id) {

		global $wpdb;
		/**
		 * Fetch all the attachments from the given post
		 */
		$attachment_ids = $wpdb->get_results("SELECT ID
								FROM $wpdb->posts
								WHERE post_parent = $post_id");

		/**
		 * Create a album. Not a new album. Just give index to this post in rtMedia
		 */
		$album_id = $this->add($post['post_title'], $post['post_author'], false, $post_id);
		
		$album_data = $this->rt_media_model->get_by_media_id($album_id);

		/* Album details */
		$album_meta = array(
			'album_id' => $album_id,
			'context' => $album_data['results'][0]['context'],
			'context_id' => $album_data['results'][0]['context_id'],
			'activity_id' => $album_data['results'][0]['activity_id'],
			'privacy' => $album_data['results'][0]['privacy']
		);

		/**
		 * Index attachments in rtMedia
		 */
		$this->rt_media_object->rt_insert_media($attachment_ids, $album_meta);

		return true;
	}
}

?>
