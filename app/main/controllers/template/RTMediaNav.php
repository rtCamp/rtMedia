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

		if ( class_exists( 'BuddyPress' ) ) {
			add_action( 'bp_init', array( $this, 'custom_media_nav_tab' ), 10, 1 );
			//add_action( 'bp_init', array( $this, 'custom_media_sub_nav_tab' ), 10, 20 );
		}


	}

	/**
	 * Load Custom tabs on BuddyPress
	 *
	 * @global object $bp global BuddyPress object
	 */
	function custom_media_nav_tab() {
		//$counts = $this->actual_counts();

		//print_r($counts);die();

		bp_core_new_nav_item( array(
			'name' => RTMEDIA_MEDIA_LABEL,// '<span>'.$counts['total']['all'].'</span>',
			'slug' => RTMEDIA_MEDIA_SLUG,
			'default_subnav_slug' => 'all'
		) );


		if ( bp_is_group() ) {
			global $bp;
			$bp->bp_options_nav[ bp_get_current_group_slug() ][ 'media' ] = array(
				'name' => RTMEDIA_MEDIA_LABEL,//. '<span>'.$counts['total']['all'].'</span>',
				'link' => ( (is_multisite()) ? get_site_url( get_current_blog_id() ) : get_site_url() ) . '/groups/' . bp_get_current_group_slug() . '/media',
				'slug' => RTMEDIA_MEDIA_SLUG,
				'user_has_access' => true,
				'css_id' => 'rt-media-media-nav',
				'position' => 99,
				'default_subnav_slug' => 'all'
			);
		}
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
			$is_group = false;
			$profile = self::profile_id();
			if(!$profile){
				$profile = self::group_id();
				$is_group = true;
			}


			if(!$is_group){
				$profile_link = trailingslashit(
							get_rt_media_user_link(
									$profile
									)
							) ;
			}else{
				$profile_link = trailingslashit(
							get_rt_media_group_link(
									$profile
									)
							) ;
			}

			echo apply_filters( 'rt_media_sub_nav_' .$type['name'] ,
					'<li id="rt-media-nav-item-' . $type['name']
					. '-' . $context .'-'. $context_id. '-li" ' . $selected
					. '><a id="rt-media-nav-item-' . $type['name'] . '" href="'
					. $profile_link. 'media/'
					. constant( 'RTMEDIA_' . $name . '_SLUG' ) . '/' . '">'
					. constant( 'RTMEDIA_' . $name . '_LABEL' ) . '</a></li>',
					$type['name']
					);

		}

	}

	function refresh_counts($user_id){
		$model = new RTMediaModel();
        $counts = $model->get_counts($user_id);

		$media_count = array();
		foreach($counts as $count){
			$media_count[$count->privacy]= $count;
			unset($media_count[$count->privacy]->privacy);

		}

        update_user_meta($user_id, 'rt_media_count', $counts);
        return $media_count;
	}

	function get_counts(){
		$profile_id = $this->profile_id();
		if(!$profile_id)return false;
		$counts = get_user_meta($profile_id, 'rt_media_count');
		if(empty($counts)){
			echo 'wtf';
			$counts = $this->refresh_counts($profile_id);
		}

		return $counts;

	}

	function profile_id(){
		global $rt_media_query;
		if(isset($rt_media_query->query['context']) && ($rt_media_query->query['context']=='profile')){
			return $rt_media_query->query['context_id'];
		}

		return false;

	}

	function group_id(){
		global $rt_media_query;
		if(isset($rt_media_query->query['context']) && ($rt_media_query->query['context']=='group')){
			return $rt_media_query->query['context_id'];
		}
	}

	function actual_counts(){
		if(!$this->profile_id()) return;
		$media_count = $this->get_counts();
		$privacy = $this->set_privacy();
		$total = array('all'=>0);
		//print_r($media_count);die();

		foreach($media_count as $private=>$ind_count){
			if($private<=$privacy){
				foreach($ind_count as $type=>$ind_ind_count){
					if($type!='album'){
						$total['all']+= (int)$ind_ind_count;
					}
					$total[$type]+=(int)$ind_ind_count;
				}
			}else{
				unset($media_count[$private]);
			}
		}

		$media_count['total'] = $total;
		//print_r($media_count);
		return $media_count;
	}

	function visitor_id() {
		if ( is_user_logged_in() ) {
			$user = get_current_user_id();
		} else {
			$user = 0;
		}
		return $user;
	}

	function set_privacy() {
		$user = $this->visitor_id();
		$privacy = 0;
		if ( $user ) {
			$privacy = 20;
		}
		$profile = $this->profile_id();
		if(class_exists('BuddyPress')&&bp_is_active('friends')){

			if(friends_check_friendship_status( $user, $profile )){
				$privacy = 40;
			}
		}
		if($user===$profile){
			$privacy = 60;
		}

		return $privacy;
	}




}

?>
