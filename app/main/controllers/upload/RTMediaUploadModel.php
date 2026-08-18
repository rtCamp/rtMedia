<?php
/**
 * Handles uploaded media operations.
 *
 * @package rtMedia
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */

/**
 * Class to handle uploaded media operations.
 */
class RTMediaUploadModel {

	/**
	 * Uploaded media details.
	 *
	 * @var array
	 */
	public $upload = array(
		'mode'          => 'file_upload',
		'context'       => false,
		'context_id'    => false,
		'privacy'       => 0,
		'custom_fields' => array(),
		'taxonomy'      => array(),
		'album_id'      => false,
		'files'         => false,
		'title'         => false,
		'description'   => false,
		'media_author'  => false,
	);

	/**
	 * Whether the endpoint has derived this as an internal comment-media upload.
	 *
	 * @var bool
	 */
	private $internal_comment_media_upload = false;

	/**
	 * Set uploaded media data in class upload object.
	 *
	 * @param array $upload_params array of parameters.
	 *
	 * @return array
	 */
	public function set_post_object( $upload_params = array() ) {
		// todo: check what's in POST.
		$upload_array = empty( $upload_params ) ? $_POST : $upload_params; // phpcs:ignore
		$this->upload = wp_parse_args( $upload_array, $this->upload );
		$this->sanitize_object();

		return $this->upload;
	}

	/**
	 * Check if context is set for uploaded media.
	 *
	 * @return boolean
	 */
	public function has_context() {
		if ( isset( $this->upload['context_id'] ) && ! empty( $this->upload['context_id'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Assign values to upload media object.
	 *
	 * @global type $rtmedia_interaction
	 */
	public function sanitize_object() {

		// Never trust a client-supplied author: force the acting user. On the token API
		// (no WP session) keep the author the API layer has already set.
		if ( is_user_logged_in() ) {
			$this->upload['media_author'] = get_current_user_id();
		} elseif ( ! $this->has_author() ) {
			$this->set_author();
		}

		if ( ! $this->has_context() ) {
			// Set context_id to Logged in user id if context is profile and context_id is not provided.
			if ( 'profile' === $this->upload['context'] || 'bp_member' === $this->upload['context'] ) {
				$this->upload['context']    = 'profile';
				$this->upload['context_id'] = get_current_user_id();
			} else {
				global $rtmedia_interaction;

				$this->upload['context']    = $rtmedia_interaction->context->type;
				$this->upload['context_id'] = $rtmedia_interaction->context->id;
			}
		}

		// Verify the acting user may write to the requested context and album; otherwise
		// fall back to safe defaults (own profile / own default album).
		$this->authorize_targets();

		if ( ! is_array( $this->upload['taxonomy'] ) ) {
			$this->upload['taxonomy'] = array( $this->upload['taxonomy'] );
		}

		if ( ! is_array( $this->upload['custom_fields'] ) ) {
			$this->upload['custom_fields'] = array( $this->upload['custom_fields'] );
		}

		if ( is_rtmedia_privacy_enable() ) {

			if ( is_rtmedia_privacy_user_overide() ) {

				$privacy = filter_input( INPUT_POST, 'privacy', FILTER_SANITIZE_NUMBER_INT );

				if ( is_null( $privacy ) ) {
					$this->upload['privacy'] = get_rtmedia_default_privacy();
				} else {
					$this->upload['privacy'] = $privacy;
				}
			} else {
				$this->upload['privacy'] = get_rtmedia_default_privacy();
			}
		} else {
			$this->upload['privacy'] = 0;
		}

		// Keep privacy within the levels the site actually offers.
		$this->upload['privacy'] = rtmedia_sanitize_privacy_level( $this->upload['privacy'] );
	}

	/**
	 * Get uploaded media author.
	 *
	 * @return int
	 */
	public function has_author() {
		return $this->upload['media_author'];
	}

	/**
	 * Set upload media author as current user.
	 */
	public function set_author() {
		$this->upload['media_author'] = get_current_user_id();
	}

	/**
	 * Check if album id is set.
	 *
	 * @return boolean
	 */
	public function has_album_id() {
		if ( ! $this->upload['album_id'] || 'undefined' === $this->upload['album_id'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Check album permissions.
	 *
	 * @return boolean
	 */
	public function has_album_permissions() {
		$album_id = $this->upload['album_id'];
		if ( ! $album_id || 'undefined' === $album_id ) {
			return false;
		}

		$user = intval( $this->upload['media_author'] );

		// Site admins bypass, matching rtMedia's is_rt_admin() ( list_users ) convention.
		if ( $user && user_can( $user, 'list_users' ) ) {
			return true;
		}

		// The shared global "wall post" albums are a valid destination for everyone.
		if ( function_exists( 'rtmedia_global_albums' ) ) {
			$globals = array_map( 'intval', (array) rtmedia_global_albums() );
			if ( in_array( intval( $album_id ), $globals, true ) ) {
				return true;
			}
		}

		$model = new RTMediaModel();
		$album = $model->get( array( 'id' => $album_id ) );
		if ( empty( $album ) ) {
			return false;
		}
		$album = $album[0];

		// The destination has to actually be an album, otherwise a photo or video id
		// would be accepted and end up as the parent of the upload.
		if ( ! isset( $album->media_type ) || 'album' !== $album->media_type ) {
			return false;
		}

		// The album owner may add to their own album.
		if ( $user && intval( $album->media_author ) === $user ) {
			return true;
		}

		// Group albums: users who may post in the owning group may add.
		if ( 'group' === $album->context ) {
			return $this->can_user_upload_in_target_group( $user, intval( $album->context_id ) );
		}

		return false;
	}

	/**
	 * Validate the upload's context and album against the acting user, falling back to
	 * safe defaults (the user's own profile / default album) when not permitted.
	 */
	public function authorize_targets() {
		$user = intval( $this->upload['media_author'] );

		if ( 'profile' === $this->upload['context'] || 'bp_member' === $this->upload['context'] ) {
			// A user may only upload to their own profile.
			$this->upload['context']    = 'profile';
			$this->upload['context_id'] = $user;
		} elseif ( 'group' === $this->upload['context'] ) {
			// Only users who may post in the target group may upload into it.
			if ( ! $this->can_user_upload_in_target_group( $user, intval( $this->upload['context_id'] ) ) ) {
				$this->fallback_to_profile_context( $user );
			}
		} elseif ( 'comment-media' === $this->upload['context'] ) {
			if ( ! $this->is_internal_comment_media_upload() ) {
				$this->fallback_to_profile_context( $user );
			}
		} elseif ( 'comment' === $this->upload['context'] ) {
			if ( ! $this->can_user_upload_in_comment_context( $user, intval( $this->upload['context_id'] ) ) ) {
				$this->fallback_to_profile_context( $user );
			}
		} elseif ( 'reply' === $this->upload['context'] ) {
			if ( ! $this->can_user_upload_in_reply_context( $user, intval( $this->upload['context_id'] ) ) ) {
				$this->fallback_to_profile_context( $user );
			}
		} elseif ( ! $this->can_user_upload_in_post_context( $user, $this->upload['context'], intval( $this->upload['context_id'] ) ) ) {
			$this->fallback_to_profile_context( $user );
		}

		if ( ! $this->has_album_id() || ! $this->has_album_permissions() ) {
			$this->set_album_id();
		}
	}

	/**
	 * Reset the upload target to the acting user's profile.
	 *
	 * @param int $user User id.
	 */
	private function fallback_to_profile_context( $user ) {
		$this->upload['context']    = 'profile';
		$this->upload['context_id'] = $user;
	}

	/**
	 * Mark whether the current upload was derived from the internal comment-media path.
	 *
	 * @param bool $is_internal Whether this is an internal comment-media upload.
	 */
	public function set_internal_comment_media_upload( $is_internal ) {
		$this->internal_comment_media_upload = (bool) $is_internal;
	}

	/**
	 * Whether this request is the internally-derived comment-media upload path.
	 *
	 * @return boolean
	 */
	private function is_internal_comment_media_upload() {
		return $this->internal_comment_media_upload;
	}

	/**
	 * Whether a user may upload media attached to a comment target.
	 *
	 * @param int $user       User id.
	 * @param int $context_id Comment id.
	 *
	 * @return boolean
	 */
	private function can_user_upload_in_comment_context( $user, $context_id ) {
		$allowed = ( $user && $context_id && get_comment( $context_id ) )
			? user_can( $user, 'edit_comment', $context_id )
			: false;

		return (bool) apply_filters( 'rtmedia_can_user_upload_in_comment_context', $allowed, $context_id, $user, $this->upload ); /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
	}

	/**
	 * Whether a user may upload media attached to a reply target.
	 *
	 * @param int $user       User id.
	 * @param int $context_id Reply id.
	 *
	 * @return boolean
	 */
	private function can_user_upload_in_reply_context( $user, $context_id ) {
		$allowed = ( $user && $context_id && user_can( $user, 'edit_reply', $context_id ) );

		if ( ! $allowed ) {
			$allowed = $this->can_user_upload_in_post_context( $user, 'reply', $context_id );
		}

		return (bool) apply_filters( 'rtmedia_can_user_upload_in_reply_context', $allowed, $context_id, $user, $this->upload ); /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
	}

	/**
	 * Whether a user may upload media attached to a post-like target.
	 *
	 * @param int    $user       User id.
	 * @param string $context    Context/post type.
	 * @param int    $context_id Context id.
	 *
	 * @return boolean
	 */
	private function can_user_upload_in_post_context( $user, $context, $context_id ) {
		$allowed = false;

		if ( $user && $context_id && function_exists( 'post_type_exists' ) && post_type_exists( $context ) ) {
			$post = get_post( $context_id );
			if ( $post && $context === $post->post_type ) {
				$allowed = user_can( $user, 'edit_post', $context_id );
			}
		}

		return (bool) apply_filters( 'rtmedia_can_user_upload_in_context', $allowed, $context, $context_id, $user, $this->upload ); /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
	}

	/**
	 * Whether a user may upload into a given BuddyPress group (members, or site admins).
	 *
	 * @param int $user     User id.
	 * @param int $group_id Group id.
	 *
	 * @return boolean
	 */
	private function can_user_upload_in_target_group( $user, $group_id ) {
		if ( $user && user_can( $user, 'list_users' ) ) {
			return true;
		}

		$allowed = ( $user && $group_id && function_exists( 'groups_is_user_member' ) )
			? (bool) groups_is_user_member( $user, $group_id )
			: false;

		return (bool) apply_filters( 'rtm_can_user_upload_in_group', $allowed, $group_id, $user ); /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
	}

	/**
	 * Check if album exists.
	 *
	 * @param int $id Album id.
	 *
	 * @return boolean
	 */
	public function album_id_exists( $id ) {
		// todo:remove if not used anywhere.
		return true;
	}

	/**
	 * Set Album id to upload media based on Buddypress enabled.
	 */
	public function set_album_id() {
		if ( class_exists( 'BuddyPress' ) ) {
			$this->set_bp_album_id();
		} else {
			$this->set_wp_album_id();
		}
	}

	/**
	 * Set Album id to upload media based on current page.
	 */
	public function set_bp_album_id() {
		if ( bp_is_blog_page() ) {
			$this->set_wp_album_id();
		} else {
			$this->set_bp_component_album_id();
		}
	}

	/**
	 * Set Album id to upload media.
	 *
	 * @throws RTMediaUploadException Throws for upload error.
	 */
	public function set_wp_album_id() {
		if ( isset( $this->upload['context'] ) ) {

			$this->upload['album_id'] = $this->upload['context_id'];

			// If context is profile then set album_id to default global album.
			if ( 'profile' === $this->upload['context'] ) {
				$this->upload['album_id'] = RTMediaAlbum::get_default();
			}
		} else {
			throw new RTMediaUploadException( 9 ); // Invalid Context.
		}
	}

	/**
	 * Set album id.
	 */
	public function set_bp_component_album_id() {
		switch ( bp_current_component() ) {
			default:
				$this->upload['album_id'] = RTMediaAlbum::get_default();
				break;
		}
	}
}
