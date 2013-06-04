<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rtMediaAlbum
 *
 * @author dit Desai <udit.desai@rtcamp.com>
 */
class RTMediaAlbum {

	public function __construct() {
		add_action('init', 'register_post_types');
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

	function get_current_author() {

		global $current_user;
		get_currentuserinfo();
		return $current_user->ID;
	}
	
	function add($title = '', $author_id = false) {

		do_action('rt_media_before_add_album');
		global $current_user;
		get_currentuserinfo();

		$author_id = $author_id ? $author_id : $this->get_current_author();

		$post_vars = array(
            'post_title' => (empty($title)) ? 'Untitled Album' : $title,
            'post_status' => 'publish',
            'post_type' => 'rt_media_album',
            'post_author' => $author_id
        );
        $album_id = wp_insert_post($post_vars);

        do_action('rt_media_after_add_album', $this);
		
		return $album_id;
	}

	
	function add_global($title ='') {

		$super_user_id = get_super_admins();
		$album_id = $this->add_album($title, $super_user_id[0]);
		
		$this->save_global_album($album_id);
		
		return $album_id;
	}
	
	function get_globals() {
		return get_site_option('rt-media-global-albums',true);
	}
	
	function save_globals($album_ids = false) {
		
		if(!$album_ids)
			return false;

		$albums = $this->get_global_albums();
		
		if(!is_array($album_ids))
			$album_ids = array($album_ids);

		array_merge($albums, $album_ids);
		update_site_option('rt-media-global-albums', $albums);
	}
	
	function get_default() {
		$albums = $this->get_global_albums();
		
		return $albums[0];
	}
	
	function update($id, $title ='') {

		do_action('rt_media_before_edit_album', $this);
        if ( empty($title) && empty($id) ) {
            return false;
        } else {

            $args = array(
                'ID' => $this->id,
                'post_title' => $this->name
            );
            $status = wp_insert_post($args);
            if (get_class($status) == 'WP_Error' || $status == 0) {
                return false;
            } else {
				do_action('rt_media_after_edit_album', $this);
                return true;
            }
        }
	}

	function delete($album_id, $global=false) {

		do_action('rt_media_before_delete_album', $this);

		if($global) {
			// global delete function from the settings
		} else {
			//delete and merge
		}

//		foreach ($this->media_entries as $entry) {
			//delete the media under the album
//        }

			//delete the album
//        wp_delete_post($this->id, true);

		do_action('rt_media_after_delete_album', $this);
	}

	function merge($album_id_1, $album_id_2) {
		
		return true;
	}
	
	function convert_post($post_id) {
		return true;
	}
}

?>
