<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaFriends
 *
 * @author saurabh
 */
class RTMediaFriends {

	/**
	 *
	 */
	function __construct() {
		if(!class_exists('BuddyPress')) return;
		if(!bp_is_active('friend'))return;
		add_action('friends_friendship_accepted',array($this,'refresh_friends_cache'));
		add_action('friends_friendship_deleted',array($this,'refresh_friends_cache'));
	}

	function get_friends_cache( $user ) {

		if(!class_exists('BuddyPress')) return array();
		if(!bp_is_active('friends'))return array();

		if ( ! $user )
			return array();
		$friends = wp_cache_get( 'rtmedia-user-friends-' . $user );
		if ( $friends === false ) {
			$friends = $this->refresh_friends_cache($user);
		}
		return $friends;
	}

	function refresh_friends_cache($user){
		if(!class_exists('BuddyPress')) return;
		if(!bp_is_active('friends'))return;
		$friends = friends_get_friend_user_ids($user);
		wp_cache_set( 'rtmedia-user-friends-' . $user, $friends );
		return $friends;
	}

}