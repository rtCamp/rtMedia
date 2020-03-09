<?php
/**
 * Handles rtMedia friends related tasks.
 *
 * @package rtMedia
 * @author saurabh
 */

/**
 * To Handle rtMedia friends related tasks.
 */
class RTMediaFriends {

	/**
	 * RTMediaFriends constructor.
	 */
	public function __construct() {
		if ( ! class_exists( 'BuddyPress' ) ) {
			return;
		}
		if ( ! bp_is_active( 'friend' ) ) {
			return;
		}
	}

	/**
	 * Function to get cached friends.
	 *
	 * @param int $user User ID to get friends.
	 *
	 * @return array|bool|mixed|void
	 */
	public function get_friends_cache( $user ) {

		if ( ! class_exists( 'BuddyPress' ) ) {
			return array();
		}
		if ( ! bp_is_active( 'friends' ) ) {
			return array();
		}

		if ( ! $user ) {
			return array();
		}

		$friends = wp_cache_get( 'rtmedia-user-friends-' . $user );
		if ( empty( $friends ) ) {
			$friends = self::refresh_friends_cache( $user );
		}

		return $friends;
	}

	/**
	 * Refresh friends cache for user.
	 *
	 * @param int $user User id to refresh cache.
	 */
	public static function refresh_friends_cache( $user ) {
		if ( ! class_exists( 'BuddyPress' ) ) {
			return;
		}
		if ( ! bp_is_active( 'friends' ) ) {
			return;
		}

		$friends = friends_get_friend_user_ids( $user );
		wp_cache_set( 'rtmedia-user-friends-' . $user, $friends );

		return $friends;
	}
}
