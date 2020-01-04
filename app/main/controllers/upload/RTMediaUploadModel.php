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
	 * Set uploaded media data in class upload object.
	 *
	 * @param array $upload_params array of parameters.
	 *
	 * @return array
	 */
	public function set_post_object( $upload_params = array() ) {
		// todo: check what's in POST.
		$upload_array = empty( $upload_params ) ? $_POST : $upload_params; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
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

		if ( ! is_array( $this->upload['taxonomy'] ) ) {
			$this->upload['taxonomy'] = array( $this->upload['taxonomy'] );
		}

		if ( ! is_array( $this->upload['custom_fields'] ) ) {
			$this->upload['custom_fields'] = array( $this->upload['custom_fields'] );
		}

		if ( ! $this->has_album_id() || ! $this->has_album_permissions() ) {
			$this->set_album_id();
		}

		if ( ! $this->has_author() ) {
			$this->set_author();
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
		// yet to be coded for the privacy options of the album.
		return true;
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
