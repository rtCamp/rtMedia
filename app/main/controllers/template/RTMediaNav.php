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

	function admin_nav(){
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
				'title' => RTMEDIA_ALL_LABEL,
				'href' => trailingslashit( get_rt_media_user_link( get_current_user_id() ) ) . 'media/'
			) );

			foreach ( $this->allowed_types as $type ) {
				if(!$this->options['allowedTypes_' . $type[ 'name' ] . '_enabled']) continue;
				$name = strtoupper( $type[ 'name' ] );
				$wp_admin_bar->add_menu( array(
					'parent' => 'my-account-' . constant('RTMEDIA_'.$name.'_SLUG'),
					'id' => 'my-account-media-' . constant('RTMEDIA_'.$name.'_SLUG'),
					'title' => constant('RTMEDIA_'.$name.'_LABEL'),
					'href' => trailingslashit( $this->get_user_link( get_current_user_id() ) ) . 'media/'.constant('RTMEDIA_'.$name.'_SLUG').'/'
				) );
			}
		}

	}

	function sub_nav() {
	global $bp;

	// If we are looking at a member profile, then the we can use the current component as an
	// index. Otherwise we need to use the component's root_slug
	$component_index = !empty( $bp->displayed_user ) ? bp_current_component() : bp_get_root_slug( bp_current_component() );

	if ( !bp_is_single_item() ) {
		if ( !isset( $bp->bp_options_nav[$component_index] ) || count( $bp->bp_options_nav[$component_index] ) < 1 ) {
			return false;
		} else {
			$the_index = $component_index;
		}
	} else {
		if ( !isset( $bp->bp_options_nav[bp_current_item()] ) || count( $bp->bp_options_nav[bp_current_item()] ) < 1 ) {
			return false;
		} else {
			$the_index = bp_current_item();
		}
	}

	// Loop through each navigation item
	foreach ( (array) $bp->bp_options_nav[$the_index] as $subnav_item ) {
		if ( !$subnav_item['user_has_access'] )
			continue;

		// If the current action or an action variable matches the nav item id, then add a highlight CSS class.
		if ( $subnav_item['slug'] == bp_current_action() ) {
			$selected = ' class="current selected"';
		} else {
			$selected = '';
		}

		// List type depends on our current component
		$list_type = bp_is_group() ? 'groups' : 'personal';

		// echo out the final list item
		echo apply_filters( 'bp_get_options_nav_' . $subnav_item['css_id'], '<li id="' . $subnav_item['css_id'] . '-' . $list_type . '-li" ' . $selected . '><a id="' . $subnav_item['css_id'] . '" href="' . $subnav_item['link'] . '">' . $subnav_item['name'] . '</a></li>', $subnav_item );
	}
	}

}

?>
