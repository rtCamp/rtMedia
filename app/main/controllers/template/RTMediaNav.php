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

	function media_screen() {
		return;
	}

	/**
	 * Load Custom tabs on BuddyPress
	 *
	 * @global object $bp global BuddyPress object
	 */
	function custom_media_nav_tab() {
		//$counts = $this->actual_counts();
                if (! function_exists("bp_core_new_nav_item"))
                    return;
		//print_r($counts);die();
                global $rtmedia;
                if($rtmedia->options["buddypress_enableOnProfile"]!==0){
		bp_core_new_nav_item( array(
			'name' => RTMEDIA_MEDIA_LABEL,// '<span>'.$counts['total']['all'].'</span>',
			'slug' => RTMEDIA_MEDIA_SLUG,
			'screen_function' => array($this,'media_screen'),
			'default_subnav_slug' => 'all'
		) );
                        }

		if ( bp_is_group() && $rtmedia->options["buddypress_enableOnGroup"]!==0 ) {
			global $bp;
			$bp->bp_options_nav[ bp_get_current_group_slug() ][ 'media' ] = array(
				'name' => RTMEDIA_MEDIA_LABEL,//. '<span>'.$counts['total']['all'].'</span>',
				'link' => ( (is_multisite()) ? get_site_url( get_current_blog_id() ) : get_site_url() ) . '/groups/' . bp_get_current_group_slug() . '/media',
				'slug' => RTMEDIA_MEDIA_SLUG,
				'user_has_access' => true,
				'css_id' => 'rtmedia-media-nav',
				'position' => 99,
				'screen_function' => array($this,'media_screen'),
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

                if(! function_exists("bp_use_wp_admin_bar"))
                    return;
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
				'href' => trailingslashit( get_rtmedia_user_link( get_current_user_id() ) ) . 'media/'
			) );

//			$wp_admin_bar->add_menu( array(
//				'parent' => 'my-account-' . RTMEDIA_MEDIA_SLUG,
//				'id' => 'my-account-media-' . RTMEDIA_MEDIA_SLUG,
//				'title' => __('Wall Posts','rtmedia'),
//				'href' => trailingslashit( get_rtmedia_user_link( get_current_user_id() ) ) . 'media/'.RTMediaAlbum::get_default().'/'
//			) );

			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-' . RTMEDIA_MEDIA_SLUG,
				'id' => 'my-account-media-' . RTMEDIA_ALBUM_SLUG,
				'title' => __('Albums','rtmedia'),
				'href' => trailingslashit( get_rtmedia_user_link( get_current_user_id() ) ) . 'media/album/'
			) );

			global $rtmedia;

			foreach ( $rtmedia->allowed_types as $type ) {
				if ( ! $rtmedia->options[ 'allowedTypes_' . $type[ 'name' ] . '_enabled' ] )
					continue;
				$name = strtoupper( $type[ 'name' ] );
				$wp_admin_bar->add_menu( array(
					'parent' => 'my-account-' . constant( 'RTMEDIA_MEDIA_SLUG' ),
					'id' => 'my-account-media-' . constant( 'RTMEDIA_' . $name . '_SLUG' ),
					'title' => $type[ 'plural_label' ],
					'href' => trailingslashit( get_rtmedia_user_link( get_current_user_id() ) ) . 'media/' . constant( 'RTMEDIA_' . $name . '_SLUG' ) . '/'
				) );
			}
		}
	}

	static function sub_nav() {
		global $rtmedia, $rtmedia_query;

		$default = false;

		if(!isset($rtmedia_query->action_query->action)||empty($rtmedia_query->action_query->action)){
			$default = true;
		}
		//print_r($rtmedia_query->action_query);

		$global_album = '';
//		if(isset($rtmedia_query->action_query->id) && $rtmedia_query->action_query->id==RTMediaAlbum::get_default())
//			$global_album = 'class = "current selected"';
//		echo apply_filters( 'rtmedia_sub_nav_wall_post' ,
//				'<li id="rtmedia-nav-item-wall-post-li" ' . $global_album . '><a id="rtmedia-nav-item-wall-post" href="' . trailingslashit( get_rtmedia_user_link( get_current_user_id() ) ) . 'media/' . RTMediaAlbum::get_default() . '/' . '">' . __("Wall Posts","rtmedia") . '</a></li>' );

		$albums = '';
		if(isset($rtmedia_query->action_query->media_type) && $rtmedia_query->action_query->media_type=='album')
			$albums = 'class="current selected"';

                if ( function_exists('bp_is_group') && bp_is_group() )
                    $link = get_rtmedia_group_link(bp_get_group_id());
                else
                    $link = get_rtmedia_user_link( get_current_user_id() );
		echo apply_filters( 'rtmedia_sub_nav_albums' ,
				'<li id="rtmedia-nav-item-albums-li" ' . $albums . '><a id="rtmedia-nav-item-albums" href="' . trailingslashit( $link ) . 'media/album/">' . __("Albums","rtmedia") . '</a></li>' );

		foreach ( $rtmedia->allowed_types as $type ) {
			//print_r($type);
			if ( ! $rtmedia->options[ 'allowedTypes_' . $type[ 'name' ] . '_enabled' ] )
				continue;

			$selected = '';

				if ( isset($rtmedia_query->action_query->media_type) && $type[ 'name' ] == $rtmedia_query->action_query->media_type ) {
					$selected = ' class="current selected"';
				} else {
					$selected = '';
				}

			$context = isset($rtmedia_query->query['context'])?$rtmedia_query->query['context']:'default';
			$context_id = isset($rtmedia_query->query['context_id'])?$rtmedia_query->query['context_id']:0;
			$name = strtoupper( $type[ 'name' ] );
			$is_group = false;
			$profile = self::profile_id();
			if(!$profile){
				$profile = self::group_id();
				$is_group = true;
			}


			if(!$is_group){
				$profile_link = trailingslashit(
							get_rtmedia_user_link(
									$profile
									)
							) ;
			}else{
				$profile_link = trailingslashit(
							get_rtmedia_group_link(
									$profile
									)
							) ;
			}

			echo apply_filters( 'rtmedia_sub_nav_' .$type['name'] ,
					'<li id="rtmedia-nav-item-' . $type['name']
					. '-' . $context .'-'. $context_id. '-li" ' . $selected
					. '><a id="rtmedia-nav-item-' . $type['name'] . '" href="'
					. $profile_link. 'media/'
					. constant( 'RTMEDIA_' . $name . '_SLUG' ) . '/' . '">'
					. $type['plural_label'] . '</a></li>',
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

        update_user_meta($user_id, 'rtmedia_count', $counts);
        return $media_count;
	}

	function get_counts(){
		$profile_id = $this->profile_id();
		if(!$profile_id)return false;
		$counts = get_user_meta($profile_id, 'rtmedia_count');
		if(empty($counts)){
			echo 'wtf';
			$counts = $this->refresh_counts($profile_id);
		}

		return $counts;

	}

	function profile_id(){
		global $rtmedia_query;
		if(isset($rtmedia_query->query['context']) && ($rtmedia_query->query['context']=='profile')){
			return $rtmedia_query->query['context_id'];
		}

		return false;

	}

	function group_id(){
		global $rtmedia_query;
		if(isset($rtmedia_query->query['context']) && ($rtmedia_query->query['context']=='group')){
			return $rtmedia_query->query['context_id'];
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
