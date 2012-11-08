<?php
function bp_media_activity_permalink_filter($link, $activity_obj = null) {
	global $bp_media_activity_types;
	if ($activity_obj != null && in_array($activity_obj->type,$bp_media_activity_types)) {
		if($activity_obj->primary_link!=''){
			try{
				return $activity_obj->primary_link;
			}
			catch(Exception $e){
				return $link;
			}
		}
	}
	if ($activity_obj != null && 'activity_comment' == $activity_obj->type) {
		global $activities_template;
		remove_filter('bp_activity_get_user_join_filter','bp_media_activity_query_filter',10);
		$parent = $activity_obj->item_id;
		if ($parent) {
			try{
				if(isset($activities_template->activity_parents[$parent])){
					return $activities_template->activity_parents[$parent]->primary_link;
				}
				else{
					$activities = bp_activity_get(array('in' => $parent));
					if(isset($activities['activities'][0])){
						$activities_template->activity_parents[$parent] = $activities['activities'][0];
						return $activities['activities'][0]->primary_link;
					}
				}
			}
			catch(Exception $e){
				return $link;
			}
		}
	}
	return $link;
}
add_filter('bp_activity_get_permalink', 'bp_media_activity_permalink_filter', 10, 2);

function bp_media_activity_parent_content_filter($activity_content) {
	global $activities_template;
	$defaults = array(
		'hide_user' => false
	);
	if ( !$parent_id = $activities_template->activity->item_id )
		return false;
	if(!isset($bp_media_hidden_activity_cache[$parent_id])){
		$activities = bp_activity_get(array('in' => $parent_id));
		if(isset($activities['activities'][0])){
			$bp_media_hidden_activity_cache[$parent_id] = $activities['activities'][0];
		}
	}
	if ( empty( $bp_media_hidden_activity_cache[$parent_id] ) )
		return false;

	if ( empty( $bp_media_hidden_activity_cache[$parent_id]->content ) )
		$content = $bp_media_hidden_activity_cache[$parent_id]->action;
	else
		$content = $bp_media_hidden_activity_cache[$parent_id]->action . ' ' . $bp_media_hidden_activity_cache[$parent_id]->content;

	// Remove the time since content for backwards compatibility
	$content = str_replace( '<span class="time-since">%s</span>', '', $content );

	// Remove images
	$content = preg_replace( '/<img[^>]*>/Ui', '', $content );

	return $content;
	return $activity_content;
}
//add_filter('bp_get_activity_parent_content', 'bp_media_activity_parent_content_filter', 1);

function bp_media_delete_button_handler($link) {
	if(bp_current_component()=='media')
		$link=str_replace('delete-activity ', 'delete-activity-single ', $link);
	return $link;
}
add_filter('bp_get_activity_delete_link','bp_media_delete_button_handler');

function bp_media_items_count_filter ($title,$nav_item) {
	global $bp_media_count;
	$bp_media_count = wp_parse_args($bp_media_count,array(
		'images'	=>	0,
		'videos'	=>	0,
		'audio'		=>	0,
		'albums'	=>	0
	));
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
		case BP_MEDIA_ALBUMS_SLUG:
			$count=  intval($bp_media_count['albums']);
			break;
	}
	$count_html=' <span>'. $count.'</span>';
	return str_replace('</a>', $count_html.'</a>', $title);
}

/**
 * To hide some activities of multiple uploads
 */
function bp_media_activity_query_filter($query){
	global $wpdb;
	$query = preg_replace('/WHERE/i', 'WHERE a.secondary_item_id!=-999 AND ',$query);
	return $query;
}
add_filter('bp_activity_get_user_join_filter','bp_media_activity_query_filter',10);

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
        'title'  => __('Media', 'bp-media'),
        'href'   => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_SLUG),
        'meta'   => array(
            'class'  => 'menupop')
    );

    // Uplaod Media
    /*$bp_media_admin_nav[] = array(
        'parent' => 'my-account-bpmedia',
        'id'     => 'my-account-upload-media',
        'title'  => __('Upload Media','bp-media'),
        'href'   => trailingslashit(bp_loggedin_user_domain() . BP_MEDIA_SLUG),
    );*/

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
add_action( 'admin_bar_menu', 'bp_media_my_account_menu', 1 );

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

        // Settings
        $bp_media_admin_nav[] = array(
            'parent' => 'bp-media-menu',
            'id'     => 'bp-media-settings',
            'title'  => __('Settings','bp-media'),
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


/**
 *  Set BP Media dashboard  widget
 *
 */
//add_action('wp_dashboard_setup','bp_media_dashboard_widgets');

function bp_media_dashboard_widgets(){
    global $wp_meta_boxes;
    // Buddypress Media
//	if ( is_user_admin() )
		wp_add_dashboard_widget( 'dashboard_media_widget', __( 'BuddPress Media' ), 'bp_media_dashboard_media' );

    global $wp_meta_boxes;

	// Get the regular dashboard widgets array
	// (which has our new widget already but at the end)

	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

	// Backup and delete our new dashbaord widget from the end of the array

	$example_widget_backup = array('dashboard_media_widget' => $normal_dashboard['dashboard_media_widget']);
	unset($normal_dashboard['dashboard_media_widget']);

	// Merge the two arrays together so our widget is at the beginning

	$sorted_dashboard = array_merge($example_widget_backup, $normal_dashboard);

	// Save the sorted array back into the original metaboxes

	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;

}

function bp_media_dashboard_media(){

    /* Single user media counts */
    $photos_count = bp_media_admin_total_count('photo');
    $videos_count = bp_media_admin_total_count('video');
    $audio_count  = bp_media_admin_total_count('audio');
    $albums_count = bp_media_admin_total_count('album');

    /* Group media counts */
    $g_photos_count = bp_media_admin_group_total_count('photo');
    $g_videos_count = bp_media_admin_group_total_count('video');
    $g_audio_count  = bp_media_admin_group_total_count('audio');
    $g_albums_count = bp_media_admin_group_total_count('album');

    ?>
    <div class="bp-media-dashboard">
        <h3 class="sub"><?php _e('Users','bp-media');?> </h3>
        <div class="table table_user">
            <div class=""><span class="media-cnt"><?php echo $photos_count; ?></span><span class="media-label"><?php _e('Total Photos','bp-media'); ?></span></div>
            <div class=""><span class="media-cnt"><?php echo $videos_count; ?></span><span class="media-label"><?php _e('Total Videos','bp-media'); ?></span></div>
            <div class=""><span class="media-cnt"><?php echo $audio_count; ?></span><span class="media-label"><?php _e('Total Audio','bp-media'); ?></span></div>
            <div class=""><span class="media-cnt"><?php echo $albums_count; ?></span><span class="media-label"><?php _e('Total Albums','bp-media'); ?></span></div>
        </div><!-- .table_user -->
        <h3 class="sub"><?php _e('Groups','bp-media');?> </h3>
        <div class="table table_group">

        </div><!-- .table_group -->
    </div><!-- .bp-media-dashboard-->

<?php
}

function bp_media_admin_total_count($media_type){

    switch($media_type){
        case 'photo':
            return bp_media_total_count_media('image');

        case 'video':
            return bp_media_total_count_media('video');

        case 'audio':
            return bp_media_total_count_media('audio');

        case 'album':
            return bp_media_total_count_albums();
    }
}

function bp_media_admin_group_total_count($media_type){

    switch($media_type){
        case 'photo':
            return bp_media_total_count_media('image');

        case 'video':
            return bp_media_total_count_media('video');

        case 'audio':
            return bp_media_total_count_media('audio');

        case 'album':
            return bp_media_total_count_albums();
    }
}


function bp_media_total_count_media($type){
    global $wpdb;

    $query = "SELECT COUNT(*) AS total
            FROM  wp_posts RIGHT JOIN wp_postmeta on wp_postmeta.post_id = wp_posts.id
            WHERE wp_postmeta.meta_key = 'bp-media-key' AND wp_postmeta.meta_value > 0 AND ( wp_posts.post_mime_type LIKE '$type%')";

    $result = $wpdb->get_var( ( $query ) );

    if(isset($result )) return $result;
        else return false;
}


function bp_media_total_count_albums(){
    global $wpdb;

    $query = "SELECT COUNT(*) AS total
            FROM  wp_posts RIGHT JOIN wp_postmeta on wp_postmeta.post_id = wp_posts.id
            WHERE wp_postmeta.meta_key = 'bp-media-key' AND wp_postmeta.meta_value < 0 ";

    $result = $wpdb->get_var( ( $query ) );

    if(isset($result )) return $result;
        else return false;

}
?>