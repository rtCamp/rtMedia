<?php
/**
 * Manages rtMedia interactions
 *
 * @package    rtMedia
 */

/**
 * Class to manage rtMedia interactions.
 *
 * @author ritz
 */
class RTMediaInteractionModel extends RTDBModel {

	/**
	 * RTMediaInteractionModel Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct( 'rtm_media_interaction', false, 10, true );
	}

	/**
	 * Check user id and media id.
	 *
	 * @access public
	 *
	 * @param  int|string $user_id user id.
	 * @param  int|string $media_id media id.
	 * @param  string     $action action name.
	 *
	 * @return bool
	 */
	public function check( $user_id = '', $media_id = '', $action = '' ) {
		if ( '' === $user_id || '' === $media_id || '' === $action ) {
			return false;
		}

		$columns = array(
			'user_id'  => $user_id,
			'media_id' => $media_id,
			'action'   => $action,
		);

		$results = $this->get( $columns );

		if ( $results ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get a array of media details.
	 *
	 * @access public
	 *
	 * @param  int|string $user_id user id.
	 * @param  int|string $media_id media id.
	 * @param  string     $action action name.
	 *
	 * @return array|bool $results
	 */
	public function get_row( $user_id = '', $media_id = '', $action = '' ) {
		if ( '' === $user_id && '' === $media_id && '' === $action ) {
			return false;
		}

		$columns = array();
		if ( '' !== $user_id ) {
			$columns['user_id'] = $user_id;
		}
		if ( '' !== $media_id ) {
			$columns['media_id'] = $media_id;
		}
		if ( '' !== $action ) {
			$columns['action'] = $action;
		}

		$results = $this->get( $columns );

		return $results;
	}
}
