<?php
/**
 * Handles album operations.
 *
 * @author Udit Desai <udit.desai@rtcamp.com>
 * @package rtMedia
 */

/**
 * Class to Handle album operations.
 */
class RTMediaAlbum {

	/**
	 * Media object associated with the album. It works as an interface
	 * for the actions specific the media from this album
	 *
	 * @var RTMediaMedia
	 */
	public $media;

	/**
	 * RTMediaAlbum constructor.
	 */
	public function __construct() {
		add_action( 'init', array( &$this, 'register_post_types' ), 12 );
		add_action( 'init', array( &$this, 'rtmedia_album_custom_post_status' ), 13 );
		$this->media = new RTMediaMedia();
	}

	/**
	 * Register custom post status for album.
	 */
	public function rtmedia_album_custom_post_status() {
		$args = array(
			'label'                     => _x( 'hidden', 'Status General Name', 'buddypress-media' ),
			// translators: %s: Album.
			'label_count'               => _n_noop( 'Hidden (%s)', 'Hidden (%s)', 'buddypress-media' ),
			'public'                    => false,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false,
			'exclude_from_search'       => true,
		);
		register_post_status( 'hidden', $args );
	}


	/**
	 * Register Custom Post Types required by rtMedia
	 */
	public function register_post_types() {

		// Set up Album labels.
		$album_labels = array(
			'name'               => esc_html__( 'Albums', 'buddypress-media' ),
			'singular_name'      => esc_html__( 'Album', 'buddypress-media' ),
			'add_new'            => esc_html__( 'Create', 'buddypress-media' ),
			'add_new_item'       => esc_html__( 'Create Album', 'buddypress-media' ),
			'edit_item'          => esc_html__( 'Edit Album', 'buddypress-media' ),
			'new_item'           => esc_html__( 'New Album', 'buddypress-media' ),
			'all_items'          => esc_html__( 'All Albums', 'buddypress-media' ),
			'view_item'          => esc_html__( 'View Album', 'buddypress-media' ),
			'search_items'       => esc_html__( 'Search Albums', 'buddypress-media' ),
			'not_found'          => esc_html__( 'No album found', 'buddypress-media' ),
			'not_found_in_trash' => esc_html__( 'No album found in Trash', 'buddypress-media' ),
			'parent_item_colon'  => esc_html__( 'Parent', 'buddypress-media' ),
			'menu_name'          => esc_html__( 'Albums', 'buddypress-media' ),
		);

		$album_slug = apply_filters( 'rtmedia_album_rewrite_slug', 'rtmedia-album' );

		$rewrite = array(
			'slug'       => $album_slug,
			'with_front' => false,
			'pages'      => true,
			'feeds'      => false,
		);

		// Set up Album post type arguments.
		$album_args = array(
			'labels'             => $album_labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => 'rtmedia_album',
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => true,
			'menu_position'      => null,
			'rewrite'            => $rewrite,
			'supports'           => array( 'title', 'author', 'thumbnail', 'excerpt', 'comments' ),
		);
		$album_args = apply_filters( 'rtmedia_albums_args', $album_args );

		// register Album post type.
		register_post_type( 'rtmedia_album', $album_args );
	}

	/**
	 * Method verifies the nonce passed while performing any CRUD operations
	 * on the album.
	 *
	 * @param string $mode Album operation mode.
	 *
	 * @return boolean
	 */
	public function verify_nonce( $mode ) {

		$nonce = sanitize_text_field( filter_input( INPUT_POST, "rtmedia_{$mode}_album_nonce", FILTER_SANITIZE_STRING ) );
		$mode  = sanitize_text_field( filter_input( INPUT_POST, 'mode', FILTER_SANITIZE_STRING ) );

		if ( empty( $mode ) ) {
			$mode = '';
		}

		if ( wp_verify_nonce( $nonce, 'rtmedia_' . $mode ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns user_id of the current logged in user in WordPress
	 *
	 * @global int $current_user
	 * @return int
	 */
	public function get_current_author() {

		return intval( apply_filters( 'rtmedia_current_user', get_current_user_id() ) );
	}

	/**
	 * Adds a new album.
	 *
	 * @param string   $title Album title.
	 * @param bool|int $author_id Author id.
	 * @param bool     $new Weather new or not.
	 * @param bool|int $post_id Post id.
	 * @param bool     $context Album context.
	 * @param bool     $context_id Context id.
	 * @param string   $album_description Album description.
	 *
	 * @return int
	 * @global type     $rtmedia_interaction
	 */
	public function add( $title = '', $author_id = false, $new = true, $post_id = false, $context = false, $context_id = false, $album_description = '' ) {

		global $rtmedia_interaction;
		// action to perform any task before adding the album.
		do_action( 'rtmedia_before_add_album' );

		$author_id = $author_id ? $author_id : $this->get_current_author();

		// Album Details which will be passed to Database query to add the album.
		$post_vars = array(
			'post_title'  => ( empty( $title ) ) ? esc_html__( 'Untitled Album', 'buddypress-media' ) : $title,
			'post_type'   => 'rtmedia_album',
			'post_author' => $author_id,
			'post_status' => 'hidden',
		);

		if ( ! empty( $album_description ) ) {
			$post_vars['post_content'] = $album_description;
		}

		/**
		 * Check whether to create a new album in wp_post table
		 * This is the case when a user creates a album of his own. We need to
		 * create a separate post in wp_post which will work as parent for
		 * all the media uploaded to that album
		 */
		if ( $new ) {
			$album_id = wp_insert_post( $post_vars );
		} else {
			/**
			 * If user uploads any media directly to a post or a page or any custom
			 * post then the context in which the user is uploading a media becomes
			 * an album in itself. We do not need to create a separate album in this
			 * case.
			 */
			$album_id = $post_id;
		}

		$current_album = get_post( $album_id, ARRAY_A );
		if ( false === $context ) {
			$context = ( isset( $rtmedia_interaction->context->type ) ) ? $rtmedia_interaction->context->type : null;
		}
		if ( false === $context_id ) {
			$context_id = ( isset( $rtmedia_interaction->context->id ) ) ? $rtmedia_interaction->context->id : null;
		}

		// add in the media since album is also a media.
		// defaults.
		$attributes = array(
			'blog_id'      => get_current_blog_id(),
			'media_id'     => $album_id,
			'album_id'     => null,
			'media_title'  => $current_album['post_title'],
			'media_author' => $current_album['post_author'],
			'media_type'   => 'album',
			'context'      => $context,
			'context_id'   => $context_id,
			'activity_id'  => null,
			'privacy'      => null,
		);

		$attributes  = apply_filters( 'rtmedia_before_save_album_attributes', $attributes, $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		$rtmedia_id  = $this->media->insert_album( $attributes );
		$rtmedia_nav = new RTMediaNav();
		$media_count = $rtmedia_nav->refresh_counts(
			$context_id,
			array(
				'context'      => $context,
				'media_author' => $context_id,
			)
		);

		// action to perform any task after adding the album.
		global $rtmedia_points_media_id;
		$rtmedia_points_media_id = $rtmedia_id;
		do_action( 'rtmedia_after_add_album', $this );

		return $rtmedia_id;
	}

	/**
	 * Wrapper method to add a global album
	 *
	 * @param string $title Album title.
	 *
	 * @return bool|int
	 */
	public function add_global( $title = '' ) {

		$author_id = $this->get_current_author();
		/**
		 * Only admin privileged user can add a global album
		 */
		if ( current_user_can( 'activate_plugins' ) ) {

			$album_id = $this->add( $title, $author_id, true, false );

			$this->save_globals( $album_id );

			return $album_id;
		} else {
			return false;
		}
	}

	/**
	 * Get the list of all global albums
	 *
	 * @return bool/mixed/void
	 */
	public static function get_globals() {
		return rtmedia_get_site_option( 'rtmedia-global-albums' );
	}

	/**
	 * There is a default global album which works as a Wall Post Album for the
	 * user.
	 *
	 * @return int|bool
	 */
	public static function get_default() {
		$albums = self::get_globals();
		if ( isset( $albums[0] ) ) {
			return intval( $albums[0] );
		} else {
			return false;
		}
	}

	/**
	 * Save global albums for newly added album
	 *
	 * @param bool|int $album_ids Album ids to save as globals.
	 *
	 * @return bool
	 */
	public function save_globals( $album_ids = false ) {

		if ( ! $album_ids ) {
			return false;
		}

		$albums = self::get_globals();

		if ( ! $albums ) {
			$albums = array();
		}

		if ( ! is_array( $album_ids ) ) {
			$album_ids = array( $album_ids );
		}

		$albums = array_merge( $albums, $album_ids );
		rtmedia_update_site_option( 'rtmedia-global-albums', $albums );

	}

	/**
	 * Wrapper method to update details for any global album
	 *
	 * @param int    $id Album id.
	 * @param string $title Album title.
	 *
	 * @return bool
	 */
	public function update_global( $id, $title = '' ) {

		/**
		 * Only admin can update global albums
		 */
		$super_user_ids = get_super_admins();
		if ( in_array( $this->get_current_author(), array_map( 'intval', $super_user_ids ), true ) ) {
			return $this->update( $id, $title );
		} else {
			return false;
		}
	}

	/**
	 * Update any album. Generic method for all the user.
	 *
	 * @param int    $id Album id.
	 * @param string $title Album title.
	 *
	 * @return bool
	 */
	public function update( $id, $title = '' ) {

		// Action to perform before updating the album.
		do_action( 'rtmedia_before_update_album', $this );

		if ( empty( $title ) && empty( $id ) ) {
			return false;
		} else {

			$args = array(
				'ID'         => $id,
				'post_title' => $title,
			);

			$status = wp_insert_post( $args );

			if ( is_wp_error( $status ) || 0 === $status ) {
				return false;
			} else {
				// Action to perform after updating the album.
				do_action( 'rtmedia_after_update_album', $this );

				return true;
			}
		}
	}

	/**
	 * Wrapper method to delete a global album
	 *
	 * @param int $id Album id.
	 *
	 * @return boolean
	 */
	public function delete_global( $id ) {

		/**
		 * Only admin can delete a global album
		 */
		$super_user_ids = get_super_admins();
		if ( in_array( $this->get_current_author(), array_map( 'intval', $super_user_ids ), true ) ) {

			$default_album = self::get_default();

			/**
			 * Default album is NEVER deleted.
			 */
			if ( intval( $id ) === $default_album ) {
				return false;
			}

			/**
			 * If a global album is deleted then all the media of that album
			 * is merged to the default global album and then the album is deleted.
			 */
			// merge with the default album.
			$this->merge( $default_album, $id );

			return $this->delete( $id );
		} else {
			return false;
		}
	}

	/**
	 * Generic method to delete any album
	 *
	 * @param int $id Media album id.
	 *
	 * @return bool
	 */
	public function delete( $id ) {

		// action to perform any task before deleting an album.
		do_action( 'rtmedia_before_delete_album', $this );

		/**
		 * First fetch all the media from that album
		 */
		add_filter( 'rt_db_model_per_page', array( $this, 'set_queries_per_page' ), 10, 2 );
		$page = 1;
		$flag = true;

		/**
		 * Delete each media from the album first
		 */
		do {
			$media = $this->media->model->get_by_album_id( $id, $page );

			if ( ! empty( $media['result'] ) ) {
				$media_id = $media['result'][0]['media_id'];

				$flag = wp_delete_attachment( $media_id );

				if ( ! $flag ) {
					break;
				}
			}

			$page++;

		} while ( ! empty( $media ) );

		/**
		 * If all the media are deleted from the album then delete the album at last.
		 */
		if ( $flag ) {
			$this->media->delete( $id );
		}

		// action to perform any task after deleting an album.
		do_action( 'rtmedia_after_delete_album', $this );

		return $flag;
	}

	/**
	 * Helper function to set number of queries in pagination
	 *
	 * @param int    $per_page Per page result.
	 * @param string $table_name Table name for query.
	 *
	 * @return int
	 */
	public function set_queries_per_page( $per_page, $table_name ) {

		$per_page = 1;

		return $per_page;
	}

	/**
	 * Generic function to merge two albums
	 *
	 * @param int $primary_album_id Primary album id.
	 * @param int $secondary_album_id Secondary album id.
	 *
	 * @return int/bool
	 */
	public function merge( $primary_album_id, $secondary_album_id ) {

		add_filter( 'rt_db_model_per_page', array( $this, 'set_queries_per_page' ), 10, 2 );
		$page = 1;

		/**
		 * Transfer all the media from secondary album to primary album
		 */
		do {
			$media = $this->media->model->get_by_album_id( $secondary_album_id, $page );

			if ( ! empty( $media['result'] ) ) {
				$media_id = $media['result'][0]['media_id'];
				$this->media->move( $media_id, $primary_album_id );
			}

			$page++;
		} while ( ! empty( $media ) );

		$author        = $this->get_current_author();
		$admins        = get_super_admins();
		$global_albums = self::get_globals();

		if ( in_array( intval( $secondary_album_id ), array_map( 'intval', $global_albums ), true ) ) {
			if ( in_array( $author, array_map( 'intval', $admins ), true ) ) {
				$this->delete_global( $secondary_album_id );
			} else {
				return false;
			}
		} else {
			$this->delete( $secondary_album_id );
		}

		return $primary_album_id;
	}
}
