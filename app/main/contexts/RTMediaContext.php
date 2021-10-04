<?php
/**
 * Manages context for rtMedia
 *
 * @package    rtMedia
 */

/**
 * Class to manage context for rtMedia
 *
 * Default Context - The page on from which the request is generating will be taken
 * as the default context; if any context/context_id is not passed while uploading any media
 * or displaying the gallery.
 *
 * @author saurabh
 */
class RTMediaContext {

	/**
	 * Context Type. It can be any type among these. (post, page, custom_post, home_page, archive etc.)
	 *
	 * @var string
	 */

	public $type;

	/**
	 * Context id of the context
	 *
	 * @var int
	 */
	public $id;

	/**
	 * RTMediaContext constructor.
	 *
	 * @return RTMediaContext
	 */
	public function __construct() {
		$this->set_context();

		return $this;
	}

	/**
	 * Set current request context
	 */
	public function set_context() {
		if ( class_exists( 'BuddyPress' ) ) {
			$this->set_bp_context();
		} else {
			$this->set_wp_context();
		}
	}

	/**
	 * Set WordPress context
	 *
	 * @global object $post
	 */
	public function set_wp_context() {

		global $post;

		if ( is_author() ) {
			$this->type = 'profile';
			$this->id   = get_query_var( 'author' );
		} elseif ( isset( $post->post_type ) ) {
			$this->type = $post->post_type;
			$this->id   = $post->ID;
		} else {

			$wp_default_context = array( 'page', 'post' );

			$context = sanitize_text_field( filter_input( INPUT_POST, 'context', FILTER_SANITIZE_STRING ) );

			if ( ! empty( $context ) && in_array( $context, $wp_default_context, true ) ) {
				$this->type = $context;
				$this->id   = filter_input( INPUT_POST, 'context_id', FILTER_VALIDATE_INT );
			} else {
				$this->type = 'profile';
				$this->id   = get_current_user_id();
			}
		}
		$this->type = apply_filters( 'rtmedia_wp_context_type', $this->type );
		$this->id   = apply_filters( 'rtmedia_wp_context_id', $this->id );
	}

	/**
	 * Set BuddyPress context
	 */
	public function set_bp_context() {
		if ( bp_is_blog_page() && ! is_home() ) {
			$this->set_wp_context();
		} else {
			$this->set_bp_component_context();
		}
	}

	/**
	 * Set BuddyPress component context
	 */
	public function set_bp_component_context() {
		if ( bp_displayed_user_id() && ! bp_is_group() ) {
			$this->type = 'profile';
		} else {
			if ( ! bp_displayed_user_id() && bp_is_group() ) {
				$this->type = 'group';
			} else {
				$this->type = 'profile';
			}
		}
		$this->id = $this->get_current_bp_component_id();
		if ( null === $this->id ) {
			global $bp;
			$this->id = $bp->loggedin_user->id;
		}
	}

	/**
	 * Get current bp component id
	 *
	 * @return int/null
	 */
	public function get_current_bp_component_id() {
		switch ( bp_current_component() ) {
			case 'groups':
				if ( function_exists( 'bp_get_current_group_id' ) ) {
					return bp_get_current_group_id();
				}

				return null;
			default:
				return bp_displayed_user_id();
		}
	}
}
