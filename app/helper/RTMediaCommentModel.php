<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * File includes RTMediaCommentModel class
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
	 * @param  array $attr attributes.
	 *
	 * @return false|int
	 */
	public function insert( $attr ) {

		return wp_insert_comment( $attr );
	}

	/**
	 * Update comment.
	 *
	 * @access public
	 *
	 * @param  array $attr attributes.
	 *
	 * @return int
	 */
	public function update( $attr ) {

		return wp_update_comment( $attr, ARRAY_A );
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
	 * @param  int $id id.
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
	 * @param  int $id id.
	 *
	 * @return bool
	 */
	public function delete( $id ) {

		return wp_delete_comment( $id, true );
	}
}
