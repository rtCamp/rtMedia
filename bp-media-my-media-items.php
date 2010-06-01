<?php
/* 
 * For adding menu and submenu actions of my media list
 * and open the template in the editor.
 */



function rt_bp_media_add_admin_menu() {
    global $bp;

        $bp->media->slug = BP_MEDIA_SLUG;

        bp_core_new_nav_item( array(
		'name' => __( 'Media', 'bp-media' ),
		'slug' => $bp->media->slug,
		'position' => 80,
		'screen_function' => 'bp_media_add_new_media',
		'default_subnav_slug' => 'rtaddmedia'
	) );


	$media_link = $bp->loggedin_user->domain . $bp->media->slug . '/';
        // Add Media
        bp_core_new_subnav_item( array(
		'name' => __( 'Add New Media', 'buddypress-media' ),
		'slug' => 'rtaddmedia',
		'parent_slug' => $bp->media->slug,
		'parent_url' => $media_link,
		'screen_function' => 'bp_media_add_new_media',
		'position' => 10
	) );
        //My Photos
        bp_core_new_subnav_item( array(
		'name' => __( 'My Photos', 'buddypress-media' ),
		'slug' => 'rtmyphoto',
		'parent_slug' => $bp->media->slug,
		'parent_url' => $media_link,
		'screen_function' => 'bp_media_my_photo',
		'position' => 20
	) );
        //My Videos
        bp_core_new_subnav_item( array(
		'name' => __( 'My Video', 'buddypress-media' ),
		'slug' => 'rtmyvideo',
		'parent_slug' =>$bp->media->slug,
		'parent_url' => $media_link,
		'screen_function' => 'bp_media_my_video',
		'position' => 30
	) );
        //My Audio
        bp_core_new_subnav_item( array(
		'name' => __( 'My Audio', 'buddypress-media' ),
		'slug' => 'rtmyaudio',
		'parent_slug' => $bp->media->slug,
		'parent_url' => $media_link,
		'screen_function' => 'bp_media_my_audio',
		'position' => 40
	) );

	

}
add_action( 'wp', 'rt_bp_media_add_admin_menu', 2);
add_action( 'admin_menu', 'rt_bp_media_add_admin_menu',2 );

//for all media
function bp_media_add_new_media(){
    global $bp;
        do_action( 'bp_media_add_new_media' );
        add_action( 'bp_template_title', 'bp_media_add_new_media_title' );
	add_action( 'bp_template_content', 'bp_media_add_new_media_content' );
        bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );

}

function bp_media_my_photo(){
    global $bp;
        do_action( 'bp_media_my_photo' );
        add_action( 'bp_template_title', 'bp_media_my_photo_title' );
	add_action( 'bp_template_content', 'bp_media_my_photo_content' );
        bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );

}

function bp_media_my_video(){
    global $bp;
        do_action( 'bp_media_my_video' );
        add_action( 'bp_template_title', 'bp_media_my_video_title' );
	add_action( 'bp_template_content', 'bp_media_my_video_content' );
        bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );

}

function bp_media_my_audio(){
    global $bp;
        do_action( 'bp_media_my_audio' );
        add_action( 'bp_template_title', 'bp_media_my_audio_title' );
	add_action( 'bp_template_content', 'bp_media_my_audio_content' );
        bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );

}

//All title for add media,photo,video,audio here
function bp_media_my_video_title(){
    _e("My Videos List","buddypress-media");
}

function bp_media_my_audio_title(){
    _e("My Audio List","buddypress-media");
}

function bp_media_my_photo_title(){
    _e("My Photo List","buddypress-media");
}


// Content for add media, photo ,video,audio goes here


function bp_media_add_new_media_content(){
      global $bp,$wpdb,$kaltura_validation_data;
      
        if(is_kaltura_configured()):
      
               bp_media_locate_template( array( 'media/upload.php' ), true );
                else:
                bp_core_add_message( __( 'Kaltura setting is not properly configured', 'buddypress-media' ), 'error' );
                endif;

}

function bp_media_my_photo_content(){
      global $bp,$wpdb,$kaltura_validation_data;

        if(is_kaltura_configured()):
//            var_dump($bp->current_action,$bp->action_variable,$bp->current_component);
            $media_type = 2;
            $owner_id = $bp->loggedin_user->id;
            $q = "SELECT id,entry_id from {$bp->media->table_media_data} where user_id = $owner_id AND media_type = $media_type ";
            $result = $wpdb->get_results($wpdb->prepare($q));
            if(empty($result)){
                echo '<div id="message" class="info"><p>There is no photo in your list</p></div>';
                 return;
            }
            
            $fpage = isset($_GET['fpage']) ? $_GET['fpage'] : 1;
            $pag_num = 10;
            $cnt = count($result);
            $pagination = array(
                'base' => add_query_arg( array('fpage'=> '%#%', 'num' => $pag_num ) ), // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
                'format' => '', // ?page=%#% : %#% is replaced by the page number
                'total' => ceil( ($cnt) / $pag_num),
                'current' => $fpage,
                'prev_next' => true,
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'type' => 'plain'
            );
            $rt_media_offset = ($fpage-1) * $pag_num;
            $q .= " LIMIT {$rt_media_offset}, {$pag_num}";
            $result = $wpdb->get_results($q);

            for($i=0;$i<count($result);$i++){
                $entry_list .= $result[$i]->entry_id . ',';
                
            }
            
            $fetched_data = get_kaltura_data_for_user($entry_list);
            echo'<div class="pagination">'. paginate_links($pagination) .'</div>';
            echo '<ul id="rt-media-list" class= "item-list">';
            $i=0;
                foreach($fetched_data as $data){
                     echo '<li class="rt-picture-thumb rt-my-media-list">';
                     echo '<a href ="'.get_option('siteurl').'/'.BP_MEDIA_SLUG.'/'.photo.'/'.$result[$i]->id .'" ><img src = "'.$data->thumbnailUrl.'"></a>';
                     echo '<span class="rt-title">'.$data->name.'</span>';
                     echo '</li>';
                     $i++;
                }
            echo '</ul>';


                else:
               bp_core_add_message( __( 'Please configure Kaltura settings.', 'buddypress-media' ), 'error' );
                endif;

}

function bp_media_my_audio_content(){
       global $bp,$wpdb,$kaltura_validation_data;

        if(is_kaltura_configured()):
//            var_dump($bp->current_action,$bp->action_variable,$bp->current_component);
            $media_type = 5;
            $owner_id = $bp->loggedin_user->id;
            $q = "SELECT id,entry_id from {$bp->media->table_media_data} where user_id = $owner_id AND media_type = $media_type ";
            $result = $wpdb->get_results($wpdb->prepare($q));
            if(empty($result)){
                echo '<div id="message" class="info"><p>There is no audio in your list</p></div>';
                 return;
            }

            $fpage = isset($_GET['fpage']) ? $_GET['fpage'] : 1;
            $pag_num = 10;
            $cnt = count($result);
            $pagination = array(
                'base' => add_query_arg( array('fpage'=> '%#%', 'num' => $pag_num ) ), // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
                'format' => '', // ?page=%#% : %#% is replaced by the page number
                'total' => ceil( ($cnt) / $pag_num),
                'current' => $fpage,
                'prev_next' => true,
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'type' => 'plain'
            );
            $rt_media_offset = ($fpage-1) * $pag_num;
            $q .= " LIMIT {$rt_media_offset}, {$pag_num}";
            $result = $wpdb->get_results($q);

            for($i=0;$i<count($result);$i++){
                $entry_list .= $result[$i]->entry_id . ',';

            }

            $fetched_data = get_kaltura_data_for_user($entry_list);
            echo'<div class="pagination">'. paginate_links($pagination) .'</div>';
            echo '<ul id="rt-media-list" class= "item-list">';
            $i=0;
                foreach($fetched_data as $data){
                     echo '<li class="rt-picture-thumb rt-my-media-list">';
                     echo '<a href ="'.get_option('siteurl').'/'.BP_MEDIA_SLUG.'/'.audio.'/'.$result[$i]->id .'" ><img src = "'.$data->thumbnailUrl.'"></a>';
                     echo '<span class="rt-title">'.$data->name.'</span>';
                     echo '</li>';
                     $i++;
                }
            echo '</ul>';


                else:
               bp_core_add_message( __( 'Please configure Kaltura settings.', 'buddypress-media' ), 'error' );
                endif;

}

function bp_media_my_video_content(){
      global $bp,$wpdb,$kaltura_validation_data;

        if(is_kaltura_configured()):
//            var_dump($bp->current_action,$bp->action_variable,$bp->current_component);
            $media_type = 1;
            $owner_id = $bp->loggedin_user->id;
            $q = "SELECT id,entry_id from {$bp->media->table_media_data} where user_id = $owner_id AND media_type = $media_type ";
            $result = $wpdb->get_results($wpdb->prepare($q));
            
            if(empty($result)){
            echo '<div id="message" class="info"><p>There is no video in your list</p></div>';
                 return;
            }
     

            $fpage = isset($_GET['fpage']) ? $_GET['fpage'] : 1;
            $pag_num = 10;
            $cnt = count($result);
            $pagination = array(
                'base' => add_query_arg( array('fpage'=> '%#%', 'num' => $pag_num ) ), // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
                'format' => '', // ?page=%#% : %#% is replaced by the page number
                'total' => ceil( ($cnt) / $pag_num),
                'current' => $fpage,
                'prev_next' => true,
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'type' => 'plain'
            );
            $rt_media_offset = ($fpage-1) * $pag_num;
            $q .= " LIMIT {$rt_media_offset}, {$pag_num}";
            $result = $wpdb->get_results($q);

            for($i=0;$i<count($result);$i++){
                $entry_list .= $result[$i]->entry_id . ',';

            }

            $fetched_data = get_kaltura_data_for_user($entry_list);
            echo'<div class="pagination">'. paginate_links($pagination) .'</div>';
            echo '<ul id="rt-media-list" class= "item-list">';
            $i=0;
                foreach($fetched_data as $data){
                     echo '<li class="rt-picture-thumb rt-my-media-list">';
                     echo '<a href ="'.get_option('siteurl').'/'.BP_MEDIA_SLUG.'/'.video.'/'.$result[$i]->id .'" ><img src = "'.$data->thumbnailUrl.'"></a>';
                     echo '<span class="rt-title">'.$data->name.'</span>';
                     echo '</li>';
                     $i++;
                }
            echo '</ul>';


                else:
               bp_core_add_message( __( 'Please configure Kaltura settings.', 'buddypress-media' ), 'error' );
                endif;

}

function get_kaltura_data_for_user($entry_list){
    global $bp,$wpdb,$kaltura_validation_data;
    try{
        $kaltura_data = $kaltura_validation_data['client']->baseEntry->getByIds($entry_list);
    }
    catch (Exception $e){
        echo ' Error Server Error';
    }
    
    return $kaltura_data;

}


?>