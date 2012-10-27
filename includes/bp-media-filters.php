<?php
function bp_media_activity_permalink_filter($link, $activity_obj = null) {
	if ($activity_obj != null && 'media_upload' == $activity_obj->type) {
		if(preg_match('/bp_media_urlid=(\d+)/i',$activity_obj->primary_link, $result)&&isset($result[1])){
			try{
				$media=new BP_Media_Host_Wordpress($result[1]);
				return $media->get_media_activity_url();
			}
			catch(Exception $e){
				return $link;
			}
		}
	}
	if ($activity_obj != null && 'activity_comment' == $activity_obj->type) {
		$parent = bp_activity_get_meta($activity_obj->item_id, 'bp_media_parent_post');
		if ($parent) {
			try{
				$parent = new BP_Media_Host_Wordpress($parent);
				return $parent->get_url();
			}
			catch(Exception $e){
				return $link;
			}
		}
	}
	return $link;
}
add_filter('bp_activity_get_permalink', 'bp_media_activity_permalink_filter', 10, 2);

function bp_media_activity_action_filter($activity_action, $activity_obj = null) {
	if ($activity_obj != null && 'media_upload' == $activity_obj->type) {
		add_shortcode('bp_media_action', 'bp_media_shortcode_action');
		$activity_action = do_shortcode($activity_action);
		remove_shortcode('bp_media_action');
	}
	return $activity_action;
}
add_filter('bp_get_activity_action', 'bp_media_activity_action_filter', 10, 2);

function bp_media_activity_content_filter($activity_content, $activity_obj = null ) {
	if ($activity_obj != null && 'media_upload' == $activity_obj->type) {
		add_shortcode('bp_media_content', 'bp_media_shortcode_content');
		$activity_content = do_shortcode($activity_content);
		remove_shortcode('bp_media_content');
	}
	return $activity_content;
}
add_filter('bp_get_activity_content_body', 'bp_media_activity_content_filter', 10, 2);

function bp_media_activity_parent_content_filter($content) {
	add_shortcode('bp_media_action', 'bp_media_shortcode_action');
	add_shortcode('bp_media_content', 'bp_media_shortcode_content');
	$content=do_shortcode($content);
	remove_shortcode('bp_media_action');
	remove_shortcode('bp_media_content');
	return $content;
}
add_filter('bp_get_activity_parent_content', 'bp_media_activity_parent_content_filter');

function bp_media_delete_button_handler($link) {
	if(bp_current_component()=='media')
		$link=str_replace('delete-activity ', 'delete-activity-single ', $link);
	return $link;
}
add_filter('bp_get_activity_delete_link','bp_media_delete_button_handler');

function bp_media_items_count_filter ($title,$nav_item) {
	global $bp_media_count;
	switch($nav_item['slug']){
		case BP_MEDIA_SLUG	:
			$count=  intval($bp_media_count['images'])+intval($bp_media_count['videos'])+intval($bp_media_count['audio']);
			break;
		case BP_MEDIA_IMAGES_SLUG:
			$count=  intval($bp_media_count['images']);
			break;
		case BP_MEDIA_VIDEOS_SLUG:
			$count=  intval($bp_media_count['videos']);
			break;
		case BP_MEDIA_AUDIO_SLUG:
			$count=  intval($bp_media_count['audio']);
			break;
	}
	$count_html=' <span>'. $count.'</span>';
	return str_replace('</a>', $count_html.'</a>', $title);
}

/**
 * Added menu under buddypress menu 'my account' in admin bar 
 * 
 * @global type $wp_admin_bar 
 */

function bp_media_my_account_menu() {
    global $wp_admin_bar;
    
    $bp_media_admin_nav = array();
    
    // Added Main menu for BP Media
    $bp_media_admin_nav[] = array(
        'parent' => 'my-account-buddypress',
        'id'     => 'my-account-bpmedia',
        'title'  => __('BP Media', 'bp-media'),
        'href'   => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_SLUG),
        'meta'   => array(
            'class'  => 'menupop') 
    );
    
    // Uplaod Media
    $bp_media_admin_nav[] = array(
        'parent' => 'my-account-bpmedia',
        'id'     => 'my-account-upload-media',
        'title'  => __('Upload Media','bp-media'),
        'href'   => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_SLUG),
    );
    
    // Photos
    $bp_media_admin_nav[] = array(
        'parent' => 'my-account-bpmedia',
        'id'     => 'my-account-photos',
        'title'  => __('Photos','bp-media'),
        'href'   => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_IMAGES_SLUG)
    );
    
    // Video
    $bp_media_admin_nav[] = array(
        'parent' => 'my-account-bpmedia',
        'id'     => 'my-account-videos',
        'title'  => __('Videos','bp-media'),
        'href'   => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_VIDEOS_SLUG)
    );
    
    // Audio
    $bp_media_admin_nav[] = array(
        'parent' => 'my-account-bpmedia',
        'id'     => 'my-account-audio',
        'title'  => __('Audio','bp-media'),
        'href'   => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_AUDIO_SLUG)
    );
    
    // Albums
    $bp_media_admin_nav[] = array(
        'parent' => 'my-account-bpmedia',
        'id'     => 'my-account-album',
        'title'  => __('Albums','bp-media'),
        'href'   => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_ALBUMS_SLUG)
    );
    
    foreach( $bp_media_admin_nav as $admin_menu )
				$wp_admin_bar->add_menu( $admin_menu );
    
}

// and we hook our function via wp_before_admin_bar_render
add_action( 'wp_before_admin_bar_render', 'bp_media_my_account_menu' );

/**
 * Added menu under buddypress menu 'my account' in admin bar 
 * 
 * @global type $wp_admin_bar 
 */

function bp_media_adminbar_settings_menu() {
    global $wp_admin_bar;
    
    if( current_user_can('manage_options') ){

        $bp_media_admin_nav = array();
        $title = '<span class="ab-icon"></span><span class="ab-label">' . _x( 'BP Media', 'admin bar menu group label' ) . '</span>';

        // Added Main menu for BP Media
        $bp_media_admin_nav[] = array(        
            'id'     => 'bp-media-menu',
            'title'  => $title,
            'href'   => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-media-settings'  ), 'admin.php' ) ),
            'meta'   => array(
                'class'  => 'menupop bp-media-settings-menu') 
        );

        // Settins
        $bp_media_admin_nav[] = array(
            'parent' => 'bp-media-menu',
            'id'     => 'bp-media-settings',
            'title'  => __('BP Media Settings','bp-media'),
            'href'   => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-media-settings'  ), 'admin.php' ) )
        );

        // Addons
        $bp_media_admin_nav[] = array(
            'parent' => 'bp-media-menu',
            'id'     => 'my-account-addons',
            'title'  => __('Addons','bp-media'),
            'href'   => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-media-addons'  ), 'admin.php' ) )
        );

        // Support
        $bp_media_admin_nav[] = array(
            'parent' => 'bp-media-menu',
            'id'     => 'my-account-support',
            'title'  => __('Support','bp-media'),
            'href'   => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-media-support'  ), 'admin.php' ) )
        );

        foreach( $bp_media_admin_nav as $admin_menu )
                    $wp_admin_bar->add_menu( $admin_menu );
    }
}

// and we hook our function via wp_before_admin_bar_render
add_action( 'wp_before_admin_bar_render', 'bp_media_adminbar_settings_menu' );

