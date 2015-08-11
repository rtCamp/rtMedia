<?php

class RTMediaBPComponent extends BP_Component {

	var $messages = array(
		'error' => array( ),
		'info' => array( ),
		'updated' => array( )
	);
	/**
	 * Initialise the component with appropriate parameters.
	 * Add hook for plugins, themes, extensions to hook on to
	 * Activates the component
	 * Registers necessary post types
	 *
	 * @global object $bp The global BuddyPress object
	 */
	function __construct() {
		global $bp;
		parent::start( 'media', 'MEDIA', RTMEDIA_PATH );
		$bp->active_components[ $this->id ] = '1';
	}

	/**
	 * @param array $args
	 */
	function setup_globals( $args = array() ) {
		global $bp;
		$globals = array(
			'slug' => RTMEDIA_MEDIA_SLUG,
			'root_slug' => isset(
				$bp->pages->{$this->id}->slug ) ?
				$bp->pages->{$this->id}->slug : RTMEDIA_MEDIA_SLUG,
			'search_string' => __( 'Search Media...', 'rtmedia' ),
			'notification_callback' => 'rtmedia_bp_notifications_callback'
		);
		parent::setup_globals( $globals );
	}

	/**
	 * @param array $main_nav
	 * @param array $sub_nav
	 */
	function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		// Determine user to use
		if ( bp_displayed_user_domain() ) {
			$user_domain = bp_displayed_user_domain();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			return;
		}

		$slug        = apply_filters('rtmedia_media_tab_slug', RTMEDIA_MEDIA_SLUG );
		$media_page_link = trailingslashit( $user_domain . $slug );

		global $rtmedia;

		$main_nav = array(
			'name'                    => RTMEDIA_MEDIA_LABEL,
			'slug'                    => $slug,
			'position'                => apply_filters('rtmedia_media_tab_position',99),
			'screen_function'         => array( $this, 'media_screen' ),
			'default_subnav_slug'     => 'all',
		);

		$pos_index = 10;
		foreach ( $rtmedia->allowed_types as $type ) {

			$name = strtoupper ( $type[ 'name' ] );
			$type_label = __( defined('RTMEDIA_' . $name . '_PLURAL_LABEL') ? constant ( 'RTMEDIA_' . $name . '_PLURAL_LABEL' ) : $type[ 'plural_label' ], 'rtmedia' );

			$sub_nav[] = array(
				'name'            => $type_label,
				'slug'            => constant ( 'RTMEDIA_' . $name . '_SLUG' ),
				'parent_url'      => $media_page_link,
				'parent_slug'     => $slug,
				'screen_function' => array( $this, 'media_screen' ),
				'position'        => $pos_index,
			);

			$pos_index += 10;

		}

		parent::setup_nav( $main_nav, $sub_nav );

	}

	function media_screen () {
		global $bp;
//		echo '<pre>';
//		var_dump( $bp->current_component );
//		var_dump( $bp->current_action );
//		echo '</pre>';
		bp_core_load_template( apply_filters( 'rtmedia_template_filter', 'members/single/profile' ) );
	}

}

function rtmedia_bp_notifications_callback($action, $media_id, $initiator_id, $total_items){
	$params = array(
		'action'		=> $action,
		'media_id'		=> $media_id,
		'initiator_id'	=> $initiator_id,
		'total_items'	=> $total_items
	);
	return apply_filters('bp_media_notifications',$params);
}