<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rtMediaAlbum
 *
 * @author Udit Desai <udit.desai@rtcamp.com>
 */
class RTMediaAlbum {
	
	var $rt_media_object;

	public function __construct() {
		add_action('init', 'register_post_types');
		$this->rt_media_object = new RTMediaMedia();
	}

	/**
	 * Register Custom Post Types required by BuddyPress Media
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
	
	function verify_nonce($mode) {

		$nonce = $_REQUEST["rt_media_{$mode}_album_nonce"];
		$mode = $_REQUEST['mode'];
		if (wp_verify_nonce($nonce, 'rt_media_' . $mode . '_album'))
			return true;
		else
			return false;
	}

	function get_current_author() {

		global $current_user;
		get_currentuserinfo();
		return $current_user->ID;
	}
	
	function add($title = '', $author_id = false) {
		
		if($this->verify_nonce('add')) {

			do_action('rt_media_before_add_album');

			$author_id = $author_id ? $author_id : $this->get_current_author();

			$post_vars = array(
				'post_title' => (empty($title)) ? 'Untitled Album' : $title,
				'post_status' => 'publish',
				'post_type' => 'rt_media_album',
				'post_author' => $author_id
			);
			$album_id = wp_insert_post($post_vars);
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

			do_action('rt_media_after_add_album', $this);

			return $album_id;
		} else
			return false;
	}

	function add_global($title ='') {

		$super_user_ids = get_super_admins();
		$author_id = $this->get_current_author();
		if( in_array($author_id, $super_user_ids) ) {
			$album_id = $this->add($title, $author_id);

			$this->save_globals($album_id);
		
			return $album_id;
		} else
			return false;
	}
	
	static function get_globals() {
		return get_site_option('rt-media-global-albums',true);
	}
	
	static function get_default() {
		$albums = self::get_globals();
		
		return $albums[0];
	}
	
	function save_globals($album_ids = false) {
		
		if(!$album_ids)
			return false;

		$albums = self::get_globals();
		
		if(!is_array($album_ids))
			$album_ids = array($album_ids);

		array_merge($albums, $album_ids);
		update_site_option('rt-media-global-albums', $albums);
	}

	function update_global($id, $title = '') {

		$super_user_ids = get_super_admins();
		if( in_array($this->get_current_author(), $super_user_ids) ) {

			return $this->update($id, $title);
		}
		else
			return false;
	}

	function update($id, $title = '') {

		if($this->verify_nonce('update')) {

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
					do_action('rt_media_after_update_album', $this);
					return true;
				}
			}
		} else
			return false;
	}

	function delete_global($id) {

		$super_user_ids = get_super_admins();
		if( in_array($this->get_current_author(), $super_user_ids) ) {
			
			$default_album = self::get_default();
			
			//merge with the default album
			$this->merge($default_album, $id);

			return $this->delete($id);
		}
		else
			return false;
	}
	
	function delete($id) {

		if($this->verify_nonce('delete')) {
			do_action('rt_media_before_delete_album', $this);
			
			$album_media = $this->rt_media_object->rt_media_model->get_by_album_id(id);

		//		foreach ($this->media_entries as $entry) {
					//delete the media under the album
		//        }

				//delete the album
	//        wp_delete_post($this->id, true);

			//remove from media table also - rtMediaMedia

			do_action('rt_media_after_delete_album', $this);
		} else
			return false;
	}

	function merge($primary_album_id, $secondary_album_id) {
		
		$page = 1;
		while( $media = $this->rt_media_object->rt_media_model->get_by_album_id($secondary_album_id, $page) ) {

			
			$page++;
		}
		
		// some jujitsu code by magic method of RTDBModel

		return $primary_album_id;
	}
	
	function convert_post($post_id) {

		return true;
	}
}

?>
