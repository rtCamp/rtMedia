<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaNav
 *
 * @author saurabh
 */
class RTMediaNav {

	/**
	 *
	 */
	function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'admin_nav' ), 99 );
	}

	function admin_nav() {
		//		$wp_admin_bar->add_menu( array(
//			'parent'    => 'my-account',
//			'id'        => 'my-account-buddypress',
//			'title'     => __( 'My Account' ),
//			'group'     => true,
//			'meta'      => array(
//				'class' => 'ab-sub-secondary'
//			)
//		) );
		global $wp_admin_bar;

		// Bail if this is an ajax request
		if ( ! bp_use_wp_admin_bar() || defined( 'DOING_AJAX' ) )
			return;

		// Only add menu for logged in user
		if ( is_user_logged_in() ) {

			// Add secondary parent item for all BuddyPress components
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account',
				'id' => 'my-account-' . RTMEDIA_MEDIA_SLUG,
				'title' => RTMEDIA_MEDIA_LABEL,
				'href' => trailingslashit( get_rt_media_user_link( get_current_user_id() ) ) . 'media/'
			) );

			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-' . RTMEDIA_MEDIA_SLUG,
				'id' => 'my-account-media-' . RTMEDIA_MEDIA_SLUG,
				'title' => __('Wall Post','rt-media'),
				'href' => trailingslashit( get_rt_media_user_link( get_current_user_id() ) ) . 'media/'.RTMediaAlbum::get_default()
			) );
			global $rt_media;

			foreach ( $rt_media->allowed_types as $type ) {
				if ( ! $rt_media->options[ 'allowedTypes_' . $type[ 'name' ] . '_enabled' ] )
					continue;
				$name = strtoupper( $type[ 'name' ] );
				$wp_admin_bar->add_menu( array(
					'parent' => 'my-account-' . constant( 'RTMEDIA_MEDIA_SLUG' ),
					'id' => 'my-account-media-' . constant( 'RTMEDIA_' . $name . '_SLUG' ),
					'title' => constant( 'RTMEDIA_' . $name . '_LABEL' ),
					'href' => trailingslashit( get_rt_media_user_link( get_current_user_id() ) ) . 'media/' . constant( 'RTMEDIA_' . $name . '_SLUG' ) . '/'
				) );
			}
		}
	}

	static function sub_nav() {
		global $rt_media, $rt_media_query;

		$default = false;
		if(!isset($rt_media_query->action_query->action)||empty($rt_media_query->action_query->action)){
			$default = true;
		}

		$global_album = '';
		if(isset($rt_media_query->action_query->id)) {
			if($rt_media_query->action_query->id==RTMediaAlbum::get_default())
				$global_album = 'class = "current selected"';
		}
		echo apply_filters( 'rt_media_sub_nav_wall_post' ,
				'<li id="rt-media-nav-item-wall-post-li" ' . $global_album . '><a id="rt-media-nav-item-wall-post" href="' . trailingslashit( get_rt_media_user_link( get_current_user_id() ) ) . 'media/' . RTMediaAlbum::get_default() . '/' . '">' . __("Wall Post","rt-media") . '</a></li>' );


		foreach ( $rt_media->allowed_types as $type ) {
			if ( ! $rt_media->options[ 'allowedTypes_' . $type[ 'name' ] . '_enabled' ] )
				continue;

			$selected = '';

			if(!$default){


				if ( $type[ 'name' ] == $rt_media_query->action_query->action ) {
					$selected = ' class="current selected"';
				} else {
					$selected = '';
				}
			}

			$context = isset($rt_media_query->query['context'])?$rt_media_query->query['context']:'default';
			$context_id = isset($rt_media_query->query['context_id'])?$rt_media_query->query['context_id']:0;
			$name = strtoupper( $type[ 'name' ] );
			echo apply_filters( 'rt_media_sub_nav_' .$type['name'] ,
					'<li id="rt-media-nav-item-' . $type['name'] . '-' . $context .'-'. $context_id. '-li" ' . $selected . '><a id="rt-media-nav-item-' . $type['name'] . '" href="' . trailingslashit( get_rt_media_user_link( get_current_user_id() ) ) . 'media/' . constant( 'RTMEDIA_' . $name . '_SLUG' ) . '/' . '">' . constant( 'RTMEDIA_' . $name . '_LABEL' ) . '</a></li>', $type['name'] );

		}

	}

}

?>
