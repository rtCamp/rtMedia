<?php
/* 
 * For adding menu and submenu actions of my media list
 * and open the template in the editor.
 */



function rt_bp_media_add_admin_menu() {
    global $bp;
//        $bp->is_single_item = true;
//        $bp->media->current_media = new BP_Media_Picture( $media_id );
//    
//        $bp->media->id = 'media';
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
		'name' => __( 'Add New Media', 'bp-media' ),
		'slug' => 'rtaddmedia',
		'parent_slug' => $bp->media->slug,
		'parent_url' => $media_link,
		'screen_function' => 'bp_media_add_new_media',
		'position' => 10
	) );
        //My Photos
        bp_core_new_subnav_item( array(
		'name' => __( 'My Photos', 'bp-media' ),
		'slug' => 'rtmyphoto',
		'parent_slug' => $bp->media->slug,
		'parent_url' => $media_link,
		'screen_function' => 'bp_media_add_new_media',
		'position' => 20
	) );
        //My Videos
        bp_core_new_subnav_item( array(
		'name' => __( 'My Video', 'bp-media' ),
		'slug' => 'rtmyvideo',
		'parent_slug' =>$bp->media->slug,
		'parent_url' => $media_link,
		'screen_function' => 'bp_media_add_new_media',
		'position' => 30
	) );
        //My Audio
        bp_core_new_subnav_item( array(
		'name' => __( 'My Audio', 'bp-media' ),
		'slug' => 'rtmyaudio',
		'parent_slug' => $bp->media->slug,
		'parent_url' => $media_link,
		'screen_function' => 'bp_media_add_new_media',
		'position' => 40
	) );

	

}
add_action( 'wp', 'rt_bp_media_add_admin_menu' );
add_action( 'admin_menu', 'rt_bp_media_add_admin_menu' );


function bp_media_add_new_media(){
    global $bp;
        do_action( 'bp_media_add_new_media' );
        add_action( 'bp_template_title', 'bp_media_add_new_media_title' );
	add_action( 'bp_template_content', 'bp_media_add_new_media_content' );
        bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}


function bp_media_add_new_media_title(){
    _e("Add New Media","buddypress");
}
function bp_media_add_new_media_content(){
      global $bp,$wpdb,$kaltura_validation_data;

        if(is_kaltura_configured()):
                   echo 'ashish';
//               bp_media_locate_template( array( 'media/upload.php' ), true );
                else:
                echo '<div id="message" class="info">
                        <p>Kaltura is not configured. Please contact Admin</p>
                    </div>';
                endif;

}


?>