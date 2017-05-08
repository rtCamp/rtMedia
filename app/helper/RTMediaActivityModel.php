<?php
/**
 * Created by PhpStorm.
 * User: ritz <ritesh.patel@rtcamp.com>
 * Date: 11/9/14
 * Time: 2:32 PM
 */

if ( ! class_exists( 'RTDBModel' ) ) {
	return;
}

class RTMediaActivityModel extends RTDBModel {

	function __construct() {
		parent::__construct( 'rtm_activity', false, 10, true );
	}

	function get( $columns, $offset = false, $per_page = false, $order_by = 'activity_id DESC' ) {
		$columns['blog_id'] = get_current_blog_id();

		return parent::get( $columns, $offset, $per_page, $order_by );
	}

	function insert( $row ) {
		$row['blog_id'] = get_current_blog_id();

		return parent::insert( $row );
	}

	function update( $data, $where ) {
		$where['blog_id'] = get_current_blog_id();

		return parent::update( $data, $where );
	}

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
	 * @param array $media_ids_of_activity List of all the Media Id that is being updated.
	 * @param int $privacy Privacy to set.
	 */
	public function profile_activity_update( $media_ids_of_activity = array(), $privacy, $parent_activity_id = false ) {

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

		if ( ! empty ( $parent_activity_id ) ) {
			// Get all the activities from item_id.
			$parent_activity_id = intval( $parent_activity_id );
			$activity_parents = bp_activity_get(
				array(
					'filter' => array( 'primary_id' => $parent_activity_id ),
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

	public function set_privacy( $activity_id, $user_id, $privacy ) {
		if ( function_exists( 'bp_activity_update_meta' ) ) {
			bp_activity_update_meta( $activity_id, 'rtmedia_privacy', $privacy );
		}

		/* check is value exits or not */
		if ( ! $this->check( $activity_id ) ) {
			$this->insert( array( 'activity_id' => $activity_id, 'user_id' => $user_id, 'privacy' => $privacy ) );
		} else {
			$this->update( array( 'activity_id' => $activity_id, 'user_id' => $user_id, 'privacy' => $privacy ), array( 'activity_id' => $activity_id ) );
		}

		// update privacy of corresponding media
		$media_model    = new RTMediaModel();
		$activity_media = $media_model->get( array( 'activity_id' => $activity_id ) );
		if ( ! empty( $activity_media ) && is_array( $activity_media ) ) {
			foreach ( $activity_media as $single_media ) {
				/* get all the media ids in the activity */
				$media_ids_of_activity[] = $single_media->id;

				$where   = array( 'id' => $single_media->id );
				$columns = array( 'privacy' => $privacy );

				// update media privacy
				$media_model->update( $columns, $where );
			}
		}
	}

	public function set_privacy_for_rtmedia_activity( $parent_activity_id, $activity_id, $user_id ) {
		/*  get default privacy*/
		$privacy = get_rtmedia_default_privacy();

		/* get parent privacy  */
		$activity_privacy = $this->get( array( 'activity_id' => $parent_activity_id ) );
		if ( isset( $activity_privacy[0] ) && isset( $activity_privacy[0]->privacy ) ) {
			$privacy = $activity_privacy[0]->privacy;
		}

		/* add the privacy */
		$this->set_privacy( $activity_id, $user_id, $privacy );
	}
}
