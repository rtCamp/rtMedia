<?php
/**
 * Class RTMediaActivityModel file.
 * User: ritz <ritesh.patel@rtcamp.com>
 * Date: 11/9/14
 * Time: 2:32 PM
 *
 * @package    rtMedia
 */

if ( ! class_exists( 'RTDBModel' ) ) {
	return;
}

/**
 * Class to monitor media activity.
 */
class RTMediaActivityModel extends RTDBModel {

	/**
	 * RTMediaActivityModel constructor.
	 */
	public function __construct() {
		parent::__construct( 'rtm_activity', false, 10, true );
	}

	/**
	 * Get columns.
	 *
	 * @param array    $columns Columns.
	 * @param bool|int $offset Offset.
	 * @param bool|int $per_page Per page.
	 * @param string   $order_by Order by.
	 *
	 * @return array
	 */
	public function get( $columns, $offset = false, $per_page = false, $order_by = 'activity_id DESC' ) {
		$columns['blog_id'] = get_current_blog_id();

		return parent::get( $columns, $offset, $per_page, $order_by );
	}

	/**
	 * Get activity without setting blog_id.
	 * This function is needed because there's no way to get activity of a different blog.
	 * Existing get method sets blog_id to current blog.
	 *
	 * @since v4.6.4
	 *
	 * @param array    $columns  Columns.
	 * @param bool|int $offset   Offset.
	 * @param bool|int $per_page Per page.
	 * @param string   $order_by Order by.
	 *
	 * @return array Returned data.
	 */
	public function get_without_blog_id( $columns, $offset = false, $per_page = false, $order_by = 'activity_id DESC' ) {
		return parent::get( $columns, $offset, $per_page, $order_by );
	}

	/**
	 * Insert row.
	 *
	 * @param array $row Row data.
	 *
	 * @return int
	 */
	public function insert( $row ) {
		$row['blog_id'] = get_current_blog_id();

		return parent::insert( $row );
	}

	/**
	 * Update data.
	 *
	 * @param array $data Data.
	 * @param array $where Where condition.
	 *
	 * @return mixed
	 */
	public function update( $data, $where ) {
		$where['blog_id'] = get_current_blog_id();

		return parent::update( $data, $where );
	}

	/**
	 * Check if activity exists.
	 *
	 * @param string $activity_id activity id.
	 *
	 * @return bool
	 */
	public function check( $activity_id = '' ) {
		if ( '' === $activity_id ) {
			return false;
		}

		$columns = array(
			'activity_id' => $activity_id,
			'blog_id'     => get_current_blog_id(),
		);
		$results = $this->get( $columns );

		if ( $results ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Update the Privacy setting of the Profile Media Activity Type of Media.
	 * Update all the Privacy Setting of the Media that has Comment and link on it of which an activity is being created
	 *
	 * @since 4.3
	 *
	 * @param array    $media_ids_of_activity List of all the Media Id that is being updated.
	 * @param int      $privacy Privacy to set.
	 * @param int|bool $parent_activity_id Parent activity id.
	 */
	public function profile_activity_update( $media_ids_of_activity = array(), $privacy = 0, $parent_activity_id = false ) {

		// Check if activity stream is active or not.
		if ( ! function_exists( 'bp_activity_get' ) ) {
			return;
		}

		foreach ( $media_ids_of_activity as $media_id_of_activity ) {
			// Get all the activities from item_id.
			$activity_parents = bp_activity_get( array( 'filter' => array( 'primary_id' => $media_id_of_activity ) ) );

			/* if has activity */
			if ( ! empty( $activity_parents['activities'] ) ) {
				foreach ( $activity_parents['activities'] as $parent ) {
					$this->set_privacy( $parent->id, $parent->user_id, $privacy );
				}
			}
		}

		if ( ! empty( $parent_activity_id ) ) {
			// Get all the activities from item_id.
			$parent_activity_id = intval( $parent_activity_id );
			$activity_parents   = bp_activity_get(
				array(
					'filter'           => array( 'primary_id' => $parent_activity_id ),
					'display_comments' => true,
				)
			);

			// if has activity.
			if ( ! empty( $activity_parents['activities'] ) ) {
				foreach ( $activity_parents['activities'] as $parent ) {
					$this->set_privacy( $parent->id, $parent->user_id, $privacy );
				}
			}
		}
	}

	/**
	 * Set privacy for activity.
	 *
	 * @param int    $activity_id activity id.
	 * @param int    $user_id user id.
	 * @param string $privacy privacy.
	 */
	public function set_privacy( $activity_id, $user_id, $privacy ) {
		if ( function_exists( 'bp_activity_update_meta' ) ) {
			bp_activity_update_meta( $activity_id, 'rtmedia_privacy', $privacy );
		}

		/* check is value exits or not */
		if ( ! $this->check( $activity_id ) ) {
			$this->insert(
				array(
					'activity_id' => $activity_id,
					'user_id'     => $user_id,
					'privacy'     => $privacy,
				)
			);
		} else {
			$this->update(
				array(
					'activity_id' => $activity_id,
					'user_id'     => $user_id,
					'privacy'     => $privacy,
				),
				array(
					'activity_id' => $activity_id,
				)
			);
		}

		// update privacy of corresponding media.
		$media_model    = new RTMediaModel();
		$activity_media = $media_model->get( array( 'activity_id' => $activity_id ) );
		if ( ! empty( $activity_media ) && is_array( $activity_media ) ) {
			foreach ( $activity_media as $single_media ) {
				/* get all the media ids in the activity */
				$media_ids_of_activity[] = $single_media->id;

				$where   = array( 'id' => $single_media->id );
				$columns = array( 'privacy' => $privacy );

				// update media privacy.
				$media_model->update( $columns, $where );
			}
		}
	}

	/**
	 * Set privacy for rtMedia activity.
	 *
	 * @param int $parent_activity_id parent activity id.
	 * @param int $activity_id activity id.
	 * @param int $user_id user id.
	 */
	public function set_privacy_for_rtmedia_activity( $parent_activity_id, $activity_id, $user_id ) {
		// get default privacy.
		$privacy = get_rtmedia_default_privacy();

		// get parent privacy.
		$activity_privacy = $this->get( array( 'activity_id' => $parent_activity_id ) );
		if ( isset( $activity_privacy[0] ) && isset( $activity_privacy[0]->privacy ) ) {
			$privacy = $activity_privacy[0]->privacy;
		}

		// add the privacy.
		$this->set_privacy( $activity_id, $user_id, $privacy );
	}
}
