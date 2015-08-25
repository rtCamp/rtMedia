<?php

class RTMediaBPComponent extends BP_Component {

	public $is_single_media_screen;
	public $is_media_gallery_screen;
	public $is_album_gallery_screen;
	public $is_custom_gallery_screen;
	public $current_media_page = 1;

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
		$this->init();
	}

	function init(){
		add_filter( 'rtmedia_query_filter', array( $this, 'remove_page_no_from_query' ), 10, 1 );
		add_filter( 'rtmedia_action_query_in_populate_media', array( $this, 'add_current_page_in_fetch_media' ), 10, 2 );
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

		global $bp;
//		var_dump( $bp->current_component );
//		var_dump( $bp->current_action );
//		var_dump( $bp->action_variables );

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

		// set up nav
		$main_nav = array(
			'name'                    => RTMEDIA_MEDIA_LABEL,
			'slug'                    => $slug,
			'position'                => apply_filters('rtmedia_media_tab_position',99),
			'screen_function'         => array( $this, 'media_gallery_screen' ),
			'default_subnav_slug'     => 'all',
		);

		$pos_index = 0;

		$sub_nav[] = array(
			'name'            => __( 'All', 'rtmedia' ),
			'slug'            => 'all',
			'parent_url'      => $media_page_link,
			'parent_slug'     => $slug,
			'screen_function' => array( $this, 'media_gallery_screen' ),
			'position'        => $pos_index += 10,
		);

		if( is_rtmedia_album_enable() ){
			$album_label = __( defined('RTMEDIA_ALBUM_PLURAL_LABEL') ? constant ( 'RTMEDIA_ALBUM_PLURAL_LABEL' ) : 'Albums', 'rtmedia' );
			$sub_nav[] = array(
				'name'            => $album_label,
				'slug'            => constant ( 'RTMEDIA_ALBUM_SLUG' ),
				'parent_url'      => $media_page_link,
				'parent_slug'     => $slug,
				'screen_function' => array( $this, 'album_gallery_screen' ),
				'position'        => $pos_index += 10,
			);
		}

		foreach ( $rtmedia->allowed_types as $type ) {

			$name = strtoupper ( $type[ 'name' ] );
			$type_label = __( defined('RTMEDIA_' . $name . '_PLURAL_LABEL') ? constant ( 'RTMEDIA_' . $name . '_PLURAL_LABEL' ) : $type[ 'plural_label' ], 'rtmedia' );

			$sub_nav[] = array(
				'name'            => $type_label,
				'slug'            => constant ( 'RTMEDIA_' . $name . '_SLUG' ),
				'parent_url'      => $media_page_link,
				'parent_slug'     => $slug,
				'screen_function' => array( $this, 'media_gallery_screen' ),
				'position'        => $pos_index += 10,
			);
		}

		if( $this->is_single_media() ){
			$sub_nav[] = array(
				'name'            => $bp->current_action,
				'slug'            => $bp->current_action,
				'parent_url'      => $media_page_link,
				'parent_slug'     => $slug,
				'screen_function' => array( $this, 'single_media_screen' ),
				'position'        => $pos_index += 10,
			);
		}

		if( $bp->current_action == 'pg' ){
			$sub_nav[] = array(
				'name'            => $bp->current_action,
				'slug'            => $bp->current_action,
				'parent_url'      => $media_page_link,
				'parent_slug'     => $slug,
				'screen_function' => array( $this, 'media_gallery_screen' ),
				'position'        => $pos_index += 10,
			);
		}

		// set template
		if( $this->is_single_media() ){
			$this->is_single_media_screen = true;
		} elseif( $bp->current_action == 'album' ){
			$this->is_album_gallery_screen = true;
		} else {
			$this->is_media_gallery_screen = true;
		}
		$this->is_custom_gallery_screen = false;

		//todo filter "is_media_gallery_screen", "is_album_gallery_screen" and "is_single_media_screen" and "is_custom_gallery_screen"

		parent::setup_nav( $main_nav, $sub_nav );

		// setup current media page no.
		$this->setup_current_media_page_no();
	}

	public function is_single_media(){
		global $bp;
		return apply_filters( 'rtm_bp_is_single_media', is_numeric( $bp->current_action ) );
	}

	public function setup_admin_bar( $wp_admin_nav = array() ) {
		global $rtmedia;
		$bp = buddypress();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$user_domain = bp_loggedin_user_domain();

			// Add the "My Account" sub menus
			$wp_admin_nav[] = array(
				'parent' => $bp->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => RTMEDIA_MEDIA_LABEL,
				'href'   => trailingslashit( $user_domain . $this->slug )
			);

			if ( is_rtmedia_album_enable () ) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $this->id,
					'id' => 'my-account-' . $this->id . '-' . RTMEDIA_ALBUM_SLUG,
					'title' => RTMEDIA_ALBUM_PLURAL_LABEL,
					'href' => trailingslashit ( $user_domain . $this->slug ) . RTMEDIA_ALBUM_SLUG . '/',
				);
			}

			foreach ( $rtmedia->allowed_types as $type ) {
				if( isset( $rtmedia->options[ 'allowedTypes_' . $type[ 'name' ] . '_enabled' ] ) ) {
					if ( ! $rtmedia->options[ 'allowedTypes_' . $type[ 'name' ] . '_enabled' ] )
						continue;
					$name = strtoupper ( $type[ 'name' ] );
					$wp_admin_nav[] = array(
						'parent' => 'my-account-' . constant ( 'RTMEDIA_MEDIA_SLUG' ),
						'id' => 'my-account-media-' . constant ( 'RTMEDIA_' . $name . '_SLUG' ),
						'title' => $type[ 'plural_label' ],
						'href' => trailingslashit ( $user_domain . $this->slug ) . constant ( 'RTMEDIA_' . $name . '_SLUG' ) . '/',
					);
				}
			}

			apply_filters( 'rtmedia_admin_bar_nav', $wp_admin_nav, $this->id );

			// Legacy rtMedia sub admin menu hook
			do_action( 'rtmedia_add_admin_bar_media_sub_menu', 'my-account-' . RTMEDIA_MEDIA_SLUG  );

		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

	function add_current_page_in_fetch_media( $action_query, $media_for_total_count ){

		if( isset( $action_query->page ) ){
			$action_query->page = $this->current_media_page;
		}

		return $action_query;
	}

	function remove_page_no_from_query( $query_param ){
		global $bp;

		if( $bp->current_action == 'pg' ){
			unset( $query_param[ 'media_type' ] );
		}

		return $query_param;
	}

	function setup_current_media_page_no(){
		global $bp;

		if( !empty( $bp->action_variables ) && is_array( $bp->action_variables ) ){
			if( $bp->current_action == 'pg' ){
				$this->current_media_page = $bp->action_variables[0];
			} elseif( $bp->action_variables[0] == 'pg' ){
				$this->current_media_page = $bp->action_variables[1];
			}
		}
	}

	function media_gallery_screen () {

		global $bp;
		// build media query
		$query_param = array();
		if( bp_is_user() ){
			$query_param[ 'context' ] = 'profile';
			$query_param[ 'context_id' ] = bp_displayed_user_id();
		}

		if( !empty( $bp->current_action ) && $bp->current_action != 'all' ){
			$query_param[ 'media_type' ] = $bp->current_action;
		}

		$this->init_media_query( $query_param );
		$this->load_template();
	}

	function album_gallery_screen(){
		// build media query
		$query_param = array();
		if( bp_is_user() ){
			$query_param[ 'context' ] = 'profile';
			$query_param[ 'context_id' ] = bp_displayed_user_id();
			$query_param[ 'media_type' ] = 'album';
		}

		$this->init_media_query( $query_param );
		$this->load_template();
	}

	function single_media_screen(){
		global $bp;

		// build media query
		$query_param = array();
		if( bp_is_user() ){
			$query_param[ 'context' ] = 'profile';
			$query_param[ 'context_id' ] = bp_displayed_user_id();
		}

		if( !empty( $bp->current_action ) ){
			$query_param[ 'id' ] = $bp->current_action;
		}

		$this->init_media_query( $query_param );
		$this->load_template();
	}

	function load_template(){
		add_action( 'bp_template_title', array( $this, 'rtm_bp_template_title' ) );
		add_action( 'bp_template_content', array( $this, 'rtm_bp_template_content' ) );

		bp_core_load_template( apply_filters( 'rtmedia_template_filter', 'members/single/plugins' ) );
	}

	function init_media_query( $query_param = array() ){
		global $rtmedia_query;
		$query_param = apply_filters( "rtmedia_query_filter", $query_param );
		$rtmedia_query = new RTMediaQuery ( $query_param );
//		var_dump( $rtmedia_query );
	}

	function rtm_bp_template_title(){
		echo get_rtmedia_gallery_title();
	}

	function rtm_bp_template_content(){
		if( $this->is_single_media_screen ){
			$template = 'media-single';
		} elseif( $this->is_album_gallery_screen ){
			$template = 'album-gallery';
		} else {
			$template = 'media-gallery';
		}
		include( RTMediaTemplate::locate_template( $template ) );
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