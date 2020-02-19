<?php
/**
 * Performs actions on comments
 *
 * @package    rtMedia
 */

/**
 * Class to perform actions on comments.
 *
 * @author Udit Desai <udit.desai@rtcamp.com>
 */
class RTMediaCommentModel {

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		// initialization.
	}

	/**
	 * Insert comment
	 *
	 * @access public
	 *
	 * @param  array $args Contains information on the comment.
	 *
	 * @return false|int
	 */
	public function insert( $args ) {

		return wp_insert_comment( $args );
	}

	/**
	 * Update comment.
	 *
	 * @access public
	 *
	 * @param  array $args Contains information on the comment.
	 *
	 * @return int
	 */
	public function update( $args ) {

		return wp_update_comment( $args, ARRAY_A );
	}

	/**
	 * Get comments.
	 *
	 * @access public
	 *
	 * @param  string $where where clause.
	 *
	 * @return array|int
	 */
	public function get( $where ) {

		return get_comments( $where );
	}

	/**
	 * Get comments by id.
	 *
	 * @access public
	 *
	 * @param  int $id Comment id.
	 *
	 * @return array|WP_Comment|null
	 */
	public function get_by_id( $id ) {

		return get_comment( $id );
	}

	/**
	 * Delete comments by id.
	 *
	 * @access public
	 *
	 * @param  int $id comment id.
	 *
	 * @return bool
	 */
	public function delete( $id ) {

		return wp_delete_comment( $id, true );
	}
}
