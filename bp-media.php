<?php
/**
 * This is the main controller file from where the flow to the view and the model goes on.
 * @author rtCamp
 * @package Media_Component
 *
 * It includes all the required files for its functionality
 */
?>
<?php

require ( BP_MEDIA_PLUGIN_DIR . '/bp-media-classes.php' );
require ( BP_MEDIA_PLUGIN_DIR . '/bp-media-templatetags.php' );
require ( BP_MEDIA_PLUGIN_DIR . '/bp-media-widgets.php' );
require ( BP_MEDIA_PLUGIN_DIR . '/bp-media-ajax.php' );
require ( BP_MEDIA_PLUGIN_DIR . '/photo-tagging/bp-photo-tagging.php' );
require ( BP_MEDIA_PLUGIN_DIR . '/media-report-abuse/bp-media-report-abuse.php' );
require ( BP_MEDIA_PLUGIN_DIR . '/bp-media-admin.php' );
require ( BP_MEDIA_PLUGIN_DIR . '/lib-kaltura/KalturaClient.php' );
require ( BP_MEDIA_PLUGIN_DIR . '/editor/bp-editor.php' );		//inculde support for post-editor media button
require ( BP_MEDIA_PLUGIN_DIR . '/bp-media-admin-report-abuse.php' );
require ( BP_MEDIA_PLUGIN_DIR . '/bp-media-admin-list.php' );

/*
 * Installs bp_media
 * Create required tables
 *
 * @global <type> $wpdb
 * @global <type> $bp
 */
function bp_media_install() {
    global $wpdb, $bp;
    if ( ! empty($wpdb->charset) )
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
    if ( ! empty($wpdb->collate) )
        $charset_collate .= " COLLATE $wpdb->collate";

        // create media table

    $sql[] = "CREATE TABLE {$bp->media->table_media_data} (
                            id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            entry_id varchar(100),
                            user_id bigint(20) NOT NULL,
                            service_type varchar(100) DEFAULT 'kaltura' NOT NULL,
                            media_type int(10) ,
                            total_rating int(10) DEFAULT '1',
                            rating_counter int(10) DEFAULT '1',
                            rating int(10) DEFAULT '1',
                            views int(10) DEFAULT '1',
                            group_id bigint(20),
                            album_id BIGINT( 100 ) NOT NULL,
                            date_uploaded BIGINT( 100 ) NOT NULL,
                            KEY (id)
                            ) {$charset_collate};";


    //create albums

    $sql[] = "CREATE TABLE {$bp->media->table_media_album}  (
    `album_id` BIGINT( 100 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `user_id` bigint(20) NOT NULL,
    `visibility` VARCHAR( 30 ) NOT NULL ,
    `name` VARCHAR( 100 ) NOT NULL ,
    `last_updated` DATETIME NOT NULL ,
    `category` VARCHAR( 100 ),
    KEY (album_id)
    );";

    //create photo tagging

    $sql[] = "CREATE TABLE {$bp->media->photo_tag} (
                            ID bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            PHOTOID varchar(100),
                            Y bigint(20),
                            WIDTH bigint(20),
                            HEIGHT int(10) ,
                            MESSAGE varchar(255),
                            X int(10),
                            KEY (ID)
                            ) {$charset_collate};";


//creating Report Abuse table


    $sql[] = "CREATE TABLE {$bp->media->table_report_abuse} (
                            id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            media_id bigint(20) ,
                            entry_id varchar(100),
                            media_owner varchar(100),
                            abuse_reporter varchar(100),
                            abusued_url varchar(255) ,
                            abusue_type varchar(255) ,
                            counter int(10) DEFAULT '1',

                            KEY (id)
                            ) {$charset_collate};";

       //creating User Rating table

    $sql[] = "CREATE TABLE {$bp->media->table_user_rating_data} (
	  		image_id int(11),
			user_id int(11)
	 	   ) {$charset_collate};";




    require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );
    dbDelta($sql);


        //Default album must have album_id = 0 and user_id = 0 to make sure everybody can use it
    //check if any row?

    $q = "select * from wp_bp_media_album";
    $result = $wpdb->query($q);

    if(!$result){
        $query_1 = "INSERT INTO {$bp->media->table_media_album} ( `user_id`,`visibility`,`name`,`last_updated`,`category`) VALUES (0, 'public', 'Default', now(), NULL);";
        $wpdb->query($query_1);

    }


    update_site_option( 'bp-media-db-version', BP_MEDIA_DB_VERSION );
}
//this is not operational as bp1.2 wire is completely changed and need to rethink the whole thing :kapil
function media_wire_install() {
    global $wpdb, $bp;

    if ( !empty($wpdb->charset) )
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

    $sql[] = "CREATE TABLE {$bp->media->table_name_wire} (
	  		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			item_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			content longtext NOT NULL,
			date_posted datetime NOT NULL,
			KEY item_id (item_id),
			KEY user_id (user_id)
	 	   ) {$charset_collate};";

    require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
//    dbDelta($sql);
}



function media_setup_globals() {

    global $bp, $wpdb, $kaltura_validation_data,$ks;
    if(get_site_option('bp_rt_kaltura_url')) {
        $config         	= new KalturaConfiguration(get_site_option('bp_rt_kaltura_partner_id'));
        $config->serviceUrl     = get_site_option('bp_rt_kaltura_url');
        $client        		= new KalturaClient($config);

        $ks                     = $client->session->start(get_site_option( 'bp_rt_kaltura_admin_secret'), 'ANONYMOUS', KalturaSessionType::ADMIN);
        $client->setKs($ks);  // set the session in the client


        $kaltura_validation_data =
                array(
                'partner_id'            => get_site_option('bp_rt_kaltura_partner_id'),
                'subpartner_id'         => get_site_option('bp_rt_kaltura_subpartner_id'),
                'admin_secret'          => get_site_option( 'bp_rt_kaltura_admin_secret'),
                'secret'                => get_site_option( 'bp_rt_kaltura_secret'),
                'partnerUserID'         => 'ANONYMOUS'
        );

        $kaltura_validation_data['config']  = $config;
        $kaltura_validation_data['client']  = $client;
        $kaltura_validation_data['ks']      = $ks;

    }
    else {
        $media_msg = __("Please enter valid Partner Details for Kaltura");
        $media_notice = '<div class="updated fade"><p><strong>'.$media_msg.'</strong></p></div>';
        //fix this : message appearing twice
//        add_action('admin_menu', create_function("", 'echo \''.$media_notice.'\';'));
    }

    $bp->media->table_media_data = $wpdb->base_prefix . 'bp_media_data';
    $bp->media->table_media_album = $wpdb->base_prefix . 'bp_media_album';
    $bp->media->photo_tag = $wpdb->base_prefix . 'bp_media_photo_tags'; //added by ashish
    $bp->media->table_report_abuse = $wpdb->base_prefix . 'bp_media_report_abuse'; //added by ashish
    $bp->media->table_user_rating_data = $wpdb->base_prefix . 'bp_media_user_rating_list';
    $bp->media->image_base = BP_MEDIA_PLUGIN_URL . '/themes/media/images'; //kapil
    $bp->media->format_activity_function = 'bp_media_format_activity';
    $bp->media->format_notification_function = 'bp_media_format_notifications';
    if ( function_exists('bp_wire_install') )
        $bp->media->table_name_wire = $wpdb->base_prefix . 'bp_media_wire';
    $bp->version_numbers->media = BP_MEDIA_VERSION;
    $bp->media->id = 'media';
    $bp->media->slug = BP_MEDIA_SLUG;
    $bp->active_components[$bp->media->slug] = $bp->media->id;
    $bp->media->view = 'multiple';
}
add_action( 'plugins_loaded', 'media_setup_globals', 5 );
add_action( 'admin_menu', 'media_setup_globals', 2 );

function media_add_admin_menu() {
    global $wpdb, $bp;


    if ( !is_site_admin() )
        return false;

    /* Add the administration tab under the "Site Admin" tab for site administrators */
    add_submenu_page('bp-general-settings', //$parent
            __('Kaltura Setting','Kaltura Setting'),//$page_title
            __('Kaltura Setting','Kaltura Setting'),//$menu_title
            'manage_options',//$access_level
            'bp-media-setup',//$file
            "media_admin" );//$function
}
add_action('admin_menu', 'media_add_admin_menu');



function bp_media_check_installed() {
    global $wpdb, $bp;

    if ( !is_site_admin() )
        return false;

    bp_media_install();

}
add_action( 'admin_menu', 'bp_media_check_installed' );
//
//add_action( 'admin_menu', 'bp_media_add_admin_menu' );


function bp_media_header_nav_setup() {//done
    global $bp;
    if(empty($bp->current_action) || ($bp->current_action == 'mediaall') || ($bp->current_action == 'audio') && (!empty($bp->action_variables[0])) )
    $selected = ( bp_is_page( BP_MEDIA_SLUG ) ) ? ' class="selected"' : '';
    $title = __( 'Media', 'Media' );
    echo sprintf('<li%s><a href="%s/%s" title="%s">%s</a></li>', $selected, get_option('home'), BP_MEDIA_SLUG, $title, $title );
}
add_action( 'bp_nav_items', 'bp_media_header_nav_setup', 100);


//code by ashish
function bp_photo_header_nav_setup() {//done
    global $bp;

    bp_is_active($component);
//    $selected = ( bp_is_media_component( BP_MEDIA_SLUG ) ) ? '' : '';
    $selected = (bp_is_photo_action(BP_PHOTO_SLUG)) ? ' class="selected"' : '';
    $title = __( 'Photo', 'Photo' );
    echo sprintf('<li%s><a href="%s/%s" title="%s">%s</a></li>', $selected, get_option('home'), BP_MEDIA_SLUG.'/'.BP_PHOTO_SLUG, $title, $title );
}
add_action( 'bp_nav_items', 'bp_photo_header_nav_setup', 100);


function bp_video_header_nav_setup() {//done
    global $bp;
    $selected = (bp_is_video_action(BP_VIDEO_SLUG) ) ? ' class="selected"' : '';
    $title = __( 'Video', 'Video' );
    echo sprintf('<li%s><a href="%s/%s" title="%s">%s</a></li>', $selected, get_option('home'), BP_MEDIA_SLUG.'/'.BP_VIDEO_SLUG, $title, $title );
}
add_action( 'bp_nav_items', 'bp_video_header_nav_setup', 100);

//end by ashish

function media_setup_root_component() { //done
    bp_core_add_root_component( BP_MEDIA_SLUG );
}
add_action( 'plugins_loaded', 'media_setup_root_component', 2 );

//code by ashish
function photo_setup_root_component() { //done
    bp_core_add_root_component( BP_MEDIA_SLUG );
}
add_action( 'plugins_loaded', 'photo_setup_root_component', 3 );

function video_setup_root_component() { //done
    bp_core_add_root_component( BP_MEDIA_SLUG );
}
add_action( 'plugins_loaded', 'video_setup_root_component', 3 );
//end by ashish

function media_directory_media_setup() {
    global $bp;
    if ( $bp->current_component == BP_MEDIA_SLUG && !$bp->is_single_item && !$bp->displayed_user->id) {
        $bp->is_directory = true;
        bp_media_load_template( 'media/index' );
    }
}
add_action( 'wp', 'media_directory_media_setup', 2 );

// code by ashish

function photo_directory_media_setup() {
    global $bp;
    if ( $bp->current_component == BP_MEDIA_SLUG && $bp->current_action == BP_PHOTO_SLUG && !$bp->is_single_item && !$bp->displayed_user->id) {
        $bp->is_directory = true;
        bp_media_load_template( 'media/photo/index' );
    }
}
add_action( 'wp', 'photo_directory_media_setup', 2 );

function video_directory_media_setup() {
    global $bp;
    if ( $bp->current_component == BP_MEDIA_SLUG && $bp->current_action == BP_VIDEO_SLUG && !$bp->is_single_item && !$bp->displayed_user->id) {
        $bp->is_directory = true;
        bp_media_load_template( 'media/video/index' );
    }
}
add_action( 'wp', 'video_directory_media_setup', 2 );
//end by ashish



function media_add_js() {
    global $bp;
    $js_path = BP_MEDIA_PLUGIN_URL.'/themes/media/js/';
    wp_enqueue_script( 'bp-media-swfobject', $js_path.'swfobject.js');
    wp_enqueue_script( 'bp-media-rating', $js_path.'rating.js');
    wp_enqueue_script( 'bp-media-general', $js_path.'general.js');
     if('photo' == $bp->current_action && 'media' == $bp->current_component)
             wp_enqueue_script('thickbox');
}
add_action( 'wp_print_scripts', 'media_add_js', 1 );

function media_add_single_js() {
    global $bp;

    $js_path = BP_MEDIA_PLUGIN_URL.'/themes/media/js/';

    $action = array("mediaall","photo","audio","video");
    $cc = $bp->current_component;
    $ca = $bp->current_action;
    $av = $bp->action_variables[0];
    if(in_array($ca, $action) && media == $cc && (!empty($av))) {
       
        wp_deregister_script('bp-media-general');
        wp_deregister_script('jquery');
        wp_enqueue_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js');
//        if(is_user_logged_in()){
//            $photo_tag_path = BP_MEDIA_PLUGIN_URL.'/photo-tagging/';
//            $abuse_path = BP_MEDIA_PLUGIN_URL.'/media-report-abuse/';
//            wp_enqueue_script( 'bp-phototagger',$photo_tag_path.'phototagger-jquery.js',true);
//            wp_enqueue_script( 'bp-phototag-init',$photo_tag_path.'photo-tag-init.js',true);
//            wp_enqueue_script( 'bp-media-abuse', $abuse_path.'abuse.js',true);
//        }
         wp_enqueue_script( 'bp-media-single', $js_path.'single.js');
        
    }
}

add_action( 'wp_print_scripts', 'media_add_single_js', 1 );

function picture_new_wire_post( $picture_id, $content ) {
    global $bp;

    /* Check the nonce first. */
    if ( !check_admin_referer( 'bp_wire_post' ) )
        return false;

    $private = false;

    if ( $wire_post_id = bp_wire_new_post( $picture_id, $content, $bp->media->slug, $private ) ) {

        bp_core_add_notification( $picture_id, $bp->displayed_user->id, $bp->media->slug, 'picture_new_wire_post' );
   
        do_action( 'picture_new_wire_post', $picture_id, $content );

        return true;

    }

    return false;
}

function picture_delete_wire_post( $wire_post_id, $table_name ) {
    global $bp;

    /* Check the nonce first. */
    if ( !check_admin_referer( 'bp_media_wire_delete_link' ) )
        return false;

    if ( bp_wire_delete_post( $wire_post_id, $bp->media->slug, $table_name ) ) {
    

        do_action( 'picture_deleted_wire_post', $wire_post_id );
        return true;
    }

    return false;
}

function bp_picture_record_activity( $args ) {
    if ( function_exists('bp_activity_record') ) {
        extract( (array)$args );
        bp_activity_record( $item_id, $component_name, $component_action, $is_private, $secondary_item_id, $user_id, $secondary_user_id, $recorded_time );
    }
}

function bp_picture_delete_activity( $args ) {
    if ( function_exists('bp_activity_delete') ) {
        extract( (array)$args );
        bp_activity_delete( $item_id, $component_name, $component_action, $user_id, $secondary_item_id );
    }
}

function bp_media_format_activity( $item_id, $user_id, $action, $secondary_item_id = false, $for_secondary_user = false ) {
    global $bp;

    /* $action is the 'component_action' variable set in the record function. */
    switch( $action ) {
        case 'picture_new_wire_post':

            $wire_post = new BP_Wire_Post( $bp->media->table_name_wire, $item_id );

            if (!$wire_post || !$wire_post->content )
                return false;

            $user_link = bp_core_get_userlink( $user_id );
            $picture_link = bp_get_picture_permalink( $wire_post->item_id);
            $post_excerpt = bp_create_excerpt( $wire_post->content );

            $content = sprintf ( __('%s wrote on the wire of %s', 'buddypress'), $user_link, '<a href="'.$picture_link.'">picture</a>' ) . ' <span class="time-since">%s</span>';
            $content .= '<blockquote>' . $post_excerpt . '</blockquote>';

            $content = apply_filters( 'bp_media_wire_post_activity', $content, $user_link, $post_excerpt );

            return array(
                    'primary_link' => $group_link,
                    'content' => $content
            );
            break;
    }

    /* By adding a do_action here, people can extend your component with new activity items. */
    do_action( 'bp_media_format_activity', $action, $item_id, $user_id, $action, $secondary_item_id, $for_secondary_user );

    return false;
}

function bp_media_screen_notification_settings() {
    global $current_user;
    ?>
<table class="notification-settings" id="bp-media-notification-settings">
    <tr>
        <th class="icon"></th>
        <th class="title"><?php _e( 'Picture Media', 'bp-media' ) ?></th>
        <th class="yes"><?php _e( 'Yes', 'bp-media' ) ?></th>
        <th class="no"><?php _e( 'No', 'bp-media' )?></th>
    </tr>
    <tr>
        <td></td>
        <td><?php _e( 'A member post wire on picture', 'bp-media' ) ?></td>
        <td class="yes"><input type="radio" name="notifications[notification_wire_post_picture]" value="yes" <?php if ( !get_usermeta( $current_user->id,'notification_wire_post_picture') || 'yes' == get_usermeta( $current_user->id,'notification_wire_post_picture') ) { ?>checked="checked" <?php } ?>/></td>
        <td class="no"><input type="radio" name="notifications[notification_wire_post_picture]" value="no" <?php if ( get_usermeta( $current_user->id,'notification_wire_post_picture') == 'no' ) { ?>checked="checked" <?php } ?>/></td>
    </tr>
        <?php do_action( 'bp_media_notification_settings' ); ?>
</table>
    <?php
}
add_action( 'bp_notification_settings', 'bp_media_screen_notification_settings' );

function bp_media_format_notifications( $action, $item_id, $secondary_item_id, $total_items ) {
    global $bp;

    switch ( $action ) {
        case 'picture_new_wire_post':

            if ( (int)$total_items > 1 ) {
                return apply_filters( 'bp_media_picture_new_wire_post_notification', '<a href="' . $bp->loggedin_user->domain . $bp->media->slug . '/picture/"' . '">' . sprintf( __( '%d New Wire Posts on Picture', 'bp-media' ), (int)$total_items ) . '</a>', $total_items );
            } else {
                return apply_filters( 'bp_media_picture_new_wire_post_notification', '<a href="' . $bp->loggedin_user->domain . $bp->media->slug .'/picture/'.$item_id.'">' . __( 'New Wire Post on Picture', 'bp-media' ). '</a>', $item_id);
            }
            break;
    }

    do_action( 'bp_media_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

    return false;
}



function bp_media_remove_screen_notifications() {
    global $bp;
    bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->media->slug, 'picture_new_wire_post' );
}
add_action( 'bp_media_picture', 'bp_media_remove_screen_notifications' );

/*
 * More functions to retrive pictures
*/
/**
 *
 * @param int $user_id :the user id
 * @param string $media_type :type of mediia all/photos/videos/audio
 * @param string $view single/multiple
 * @return the media for user
 */
function bp_pictures_get_pictures_for_user( $user_id, $media_type,$view,$group_id,$album_id,$type ) { //kapil

    return BP_Media_Picture::get_pictures_for_user( $user_id,$media_type,$view,$group_id,$album_id,$type); //kapil
}
/**
 * This function sets a new navigation item Media to the Buddypress Component
 *
 * @global <type> $bp
 *
 */
function media_setup_nav() {
    global $bp;

    //Single page template is loaded here....
    if($bp->action_variables[0] && is_media_exists($bp->action_variables[0]) && $bp->current_component == 'media') {
        $media_link = $bp->root_domain . '/' . $bp->media->slug . '/' . $bp->current_action . '/' .$bp->action_variables[0];
        //setting up subnavigation items for "media" page only and not for the members page.
        bp_core_new_nav_default( array( 'parent_slug' => $bp->media->slug, 'screen_function' => 'media_screen_media_activity', 'subnav_slug' => 'activity' ) );
        /* Add the "Home" subnav item, as this will always be present */
        bp_core_new_subnav_item( array( 'name' => __( 'Activity', 'buddypress' ), 'slug' => 'activity', 'parent_url' => $media_link, 'parent_slug' => $bp->media->slug, 'screen_function' => 'media_screen_media_activity', 'position' => 10, 'item_css_id' => 'activity' ) );

        $bp->is_single_item = true;
        $bp->media->view = 'single';
        bp_core_load_template( apply_filters( 'media_template_media_home', 'media/single/home' ) );
    }
    else{
        $bp->media->view = 'multiple';
    }
    $counter_indicator = (10);


    bp_core_new_subnav_item( array(
            'name' => __( 'Media', 'bp-media' ),
            'slug' => 'media',
            'parent_slug' => $bp->groups->slug,
            'parent_url' => $bp->loggedin_user->domain . $bp->groups->slug . '/',
            'screen_function' => 'bp_media_screen_settings_menu',
            'position' => 40,
            'user_has_access' => bp_is_my_profile() // Only the logged in user can access this on his/her profile
            ) );


    $media_link = $link = $bp->loggedin_user->domain . $bp->media->slug . '/';

    do_action( 'media_setup_nav');

}

add_action( 'plugins_loaded', 'media_setup_nav' );
add_action( 'admin_menu', 'media_setup_nav' );


/**
 * bp_media_screen_settings_menu for media option in groups
 */


function bp_media_screen_settings_menu() {
    echo 'test';
}

/**
 * Screen function for Photo Media
 */
function media_screen_photo() {
    do_action( 'media_screen_photo' );
    bp_core_load_template( apply_filters( 'media_template_photo', 'members/single/home' ) );
}
/**
 * Screen function for Audio Media
 */
function media_screen_audio() {
    do_action( 'media_screen_audio' );
    bp_core_load_template( apply_filters( 'media_screen_audio', 'members/single/home' ) );
}

/**
 * Screen function for Video Media
 */

function media_screen_video() {
    do_action( 'media_screen_video' );
    bp_core_load_template( apply_filters( 'media_screen_video', 'members/single/home' ) );
}
/**
 * Screen function for Uploading Media
 */

function media_screen_upload() {
    do_action( 'media_screen_upload' );
    bp_core_load_template( apply_filters( 'media_screen_upload', 'members/single/home' ) );
}

function media_members_content() {
    bp_media_locate_template( array( 'media/single/media.php' ), true );
}
add_action('bp_after_member_body','media_members_content');
/**
 * Adding custom CSS and JS to the Buddypress Media Component
 */
function media_add_css() {
    global $bp;
       $css_file_path = BP_MEDIA_PLUGIN_URL."/themes/media/css/";
    if (bp_is_user_media()) {
        wp_enqueue_style( 'bp-media-structure', $css_file_path.'media.css');
        wp_enqueue_style( 'bp-media-rate', $css_file_path.'rating.css');
    }else
    if($bp->current_component == $bp->groups->slug && $bp->current_action == $bp->media->slug) {
        wp_enqueue_style( 'bp-media-structure', $css_file_path.'media.css');
        wp_enqueue_style( 'bp-media-rate', $css_file_path.'rating.css');
       
    }
    if('photo' == $bp->current_action && 'media' == $bp->current_component)
        wp_enqueue_style( 'bp-media-thickbox', $css_file_path.'thickbox.css');
}
add_action( 'wp_print_styles', 'media_add_css' );

/**
 *
 * Changing the title of media with respect to the kaltura server using Wordpress Admin Ajax
 * @global <type> $wpdb
 * @global <type> $bp
 * @global <type> $kaltura_validation_data
 *
 */
function media_change_title_callback() {
    $id = $_POST['id'];
    $new_title = $_POST['new_title'];
    global $wpdb,$bp,$kaltura_validation_data; // this is how you get access to the database
    $bp->media->table_media_data = $wpdb->base_prefix . 'bp_media_data';
    $e_id = $wpdb->get_var($wpdb->prepare("SELECT entry_id from {$bp->media->table_media_data} WHERE id = {$id} "));

    try {
        $k = new KalturaMediaEntry();
        $k->name=$new_title;
        $kaltura_validation_data['client']->media->update($e_id,$k);
        echo $new_title;
    }
    catch(Exception $e) {
        echo e. "Error in updating title :: Kaltura Error";
    }
    die();
}
add_action('wp_ajax_media_change_title', 'media_change_title_callback');
add_action('wp_ajax_nopriv_media_change_title', 'media_change_title_callback');

/**
 ** Changing the description of media with respect to the kaltura server using Wordpress Admin Ajax
 *
 * @global <type> $wpdb
 * @global <type> $bp
 * @global <type> $kaltura_validation_data
 */
function media_change_description_callback() {
    $id = $_POST['id'];
    $new_desc = $_POST['new_desc'];
    global $wpdb,$bp,$kaltura_validation_data; // this is how you get access to the database
    $bp->media->table_media_data = $wpdb->base_prefix . 'bp_media_data';
    $e_id = $wpdb->get_var($wpdb->prepare("SELECT entry_id from {$bp->media->table_media_data} WHERE id = {$id} "));
    try {
        $k = new KalturaMediaEntry();
        $k->description=$new_desc;
        $kaltura_validation_data['client']->media->update($e_id,$k);
        echo $new_desc;
    }
    catch(Exception $e) {
        echo e. "Error updating description :: Kaltura Error";
    }
    die();
}
add_action('wp_ajax_media_change_description', 'media_change_description_callback');
add_action('wp_ajax_nopriv_media_change_description', 'media_chage_description_callback');

/**
 *
 * Updating Stars using Wp admin-ajax
 * Ajax call ..  Reference can be found in single.js
 * @global <type> $wpdb
 * @global <type> $bp
 */
function media_user_rating_callback() {
    $rating = $_POST['rating'];
    $image_id = $_POST['image_id'];
    $user_id = $_POST['user_id'];
    global $wpdb, $bp;
    $bp->media->table_media_data = $wpdb->base_prefix . 'bp_media_data';
    $bp->media->table_media_rating = $wpdb->base_prefix . 'bp_media_user_rating_list';
    $q = "SELECT * FROM {$bp->media->table_media_rating} WHERE image_id = {$image_id} AND user_id = {$user_id} ";
    $result = $wpdb->query($q);

    if(empty($result)) {
        $q1 = "UPDATE {$bp->media->table_media_data} SET total_rating = total_rating + {$rating}, rating_counter= rating_counter + 1 WHERE ID = {$image_id} ";
        $wpdb->query($q1);
//        echo $wpdb->last_query;
        $q2 = "INSERT INTO {$bp->media->table_media_rating} (image_id, user_id) VALUES ({$image_id},{$user_id}) ";
        $wpdb->query($q2); //insert data here since we can check that user cannt make multiple rating
         echo 'THANKS / ';
    }
    else {
        echo "ALREADY VOTED / " ;
    }

    die();
}
add_action('wp_ajax_media_user_rating', 'media_user_rating_callback');
add_action('wp_ajax_nopriv_media_user-rating', 'media_user_rating_callback');

/**
 * Udating the view counter values
 * ajax call reference can be found in single.js
 * @global <type> $wpdb
 * @global <type> $bp
 */
function media_view_update_callback() {

    $image_id = $_POST['image_id'];
    global $wpdb, $bp;
    $url = $bp->root_domain;
    echo $url;
//    $bp->media->table_media_data = $wpdb->base_prefix . 'bp_media_data';
    $q = "UPDATE {$bp->media->table_media_data} SET views = views + 1  WHERE ID = {$image_id} ";
    $p = $wpdb->query($q);
    die(); //ajax call must die after the sequence is complete
}
add_action('wp_ajax_media_view_update', 'media_view_update_callback');
add_action('wp_ajax_nopriv_media_view_update', 'media_view_update_callback');

/**
 *
 * Deleting the media from local db and kaltura server as well ~~ code reference can be found in single.js
 * @global <type> $bp
 * @global <type> $kaltura_validation_data
 * @global <type> $wpdb
 */
function media_delete_server_callback() {
    global $bp,$kaltura_validation_data,$wpdb;
    $media_id = $_POST['media_id'];
//    echo $media_id;
    $kaltura_id = get_kaltura_media_id($media_id);
    $e_id = $wpdb->get_var("SELECT entry_id from {$bp->media->table_media_data} WHERE ID = {$media_id} ");
    if (isMediaOwner($media_id)) {
        $pic = new BP_Media_Picture($picture_id);
        if ( !$pic->delete() ) {
            echo ('Error / ');
        }
        else {
            $kaltura_validation_data['client']->media->delete($e_id);

            $q = "DELETE FROM {$bp->media->photo_tag} WHERE PHOTOID={$kaltura_id}";
            $q1 = "DELETE FROM {$bp->media->table_report_abuse} WHERE entry_id ={$kaltura_id}";
            $q2 = "DELETE FROM {$bp->media->table_user_rating_data} WHERE image_id={$media_id}";
            $wpdb->query($q);
            $wpdb->query($q1);
            $wpdb->query($q2);
            echo "Media deleted from Server";
            //redireect code
        }
    }
    else {
        echo "Error Deleting from server";
    }
    die();
}
add_action('wp_ajax_media_delete_server', 'media_delete_server_callback');
add_action('wp_ajax_nopriv_media_delete_server', 'media_delete_server_callback');

/**
 * Deleting media from the locally installed db //using ajax call u can find this reference in single.js
 * @global <type> $bp
 */
function media_delete_local_callback() {
    global $bp;
    // what u have deleted? audio / video / photo

    $media_id = $_POST['media_id'];
    if (isMediaOwner($media_id)) {
        $pic = new BP_Media_Picture($media_id);

        if ( !$pic->delete() ) {
            echo "Error !!!";
        }
        else {

            $q = "DELETE FROM {$bp->media->photo_tag} WHERE PHOTOID={$kaltura_id}";
            $q1 = "DELETE FROM {$bp->media->table_report_abuse} WHERE entry_id ={$kaltura_id}";
            $q2 = "DELETE FROM {$bp->media->table_user_rating_data} WHERE image_id={$media_id}";
            $wpdb->query($q);
            $wpdb->query($q1);
            $wpdb->query($q2);
            echo "Successfully deleted !!!";
        }
    }
}
add_action('wp_ajax_media_delete_local', 'media_delete_local_callback');
add_action('wp_ajax_nopriv_media_delete_local', 'media_delete_local_callback');

//media streaming
/**
 *
 * Updating to the Activity streaming specific to media
 * @param string $object Media
 * @param int $item_id action_variables
 * @param string $content data/activity
 * @return <type>
 */
function bp_media_activity_custom_update( $object, $item_id, $content ) {

    // object MUST be media
    if ( 'media' == $object ) {
        return bp_media_post_update( array( 'type' => BP_MEDIA_ACTIVITY_ACTION_COMMENT, 'media_id' => $item_id, 'content' => $content ) );
    } else {
        return $object;
    }
}
add_filter( 'bp_activity_custom_update', 'bp_media_activity_custom_update', 10, 3 );



//Activity test code
/**
 *Returns filtered activity specific to media
 * @global <type> $bp
 * @param <type> $query_string
 * @param <type> $object
 * @param <type> $filter
 * @param <type> $scope
 * @param <type> $page
 * @param <type> $search_terms
 * @param <type> $extras
 * @return <type>
 */
function bp_media_dtheme_ajax_querystring_activity_filter( $query_string, $object, $filter, $scope, $page, $search_terms, $extras ) {
    global $bp;

    if("single" == $bp->media->view) {
        $do_filter = false;

        // only filter activity. ignore profile activity.
        if ( $bp->media->id == $object || !bp_is_my_profile() ) {
            $do_filter = true;
        }


        if ( $do_filter ) {

            // parse query string
            $args = array();
            parse_str( $query_string, $args );

            // override with media object
            $args['object'] = $bp->media->id;
            $args['action'] = BP_MEDIA_ACTIVITY_ACTION_COMMENT;
            // set primary id to current media id if applicable

            if ( $bp->media->id ) {
                $args['primary_id'] = $bp->action_variables[0];
            }

            // return modified query string
            return http_build_query( $args );
        }

        // no filtering
    }
    else {
        return $query_string;
    }


}
add_filter( 'bp_dtheme_ajax_querystring', 'bp_media_dtheme_ajax_querystring_activity_filter', 200, 7 );

//End of activity test code

function bp_media_post_update( $args = '' ) {
    global $bp;

    $defaults = array(
            'type' => BP_MEDIA_ACTIVITY_ACTION_COMMENT,
            'content' => false,
            'user_id' => $bp->loggedin_user->id,
            'media_id' => $bp->action_variables[0]
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    if ( empty($content) || empty($user_id) || empty($media_id) )
        return false;
    $bp->media->current_media = new BP_Media_Picture( $media_id );


    /* Record this in activity streams */
    $activity_action = sprintf( __( '%s posted a comment on the media %s:', 'buddypress'), bp_core_get_userlink( $user_id ), '<a href="' . bp_get_media_permalink($bp->media->current_media) . '">' . attribute_escape($bp->media->current_media->title) . '</a>' );
    $activity_id = bp_media_record_activity( array(
            'user_id' => $user_id,
            'action' => apply_filters( 'bp_media_activity_new_update_action', $activity_action ),
            'content' => apply_filters( 'bp_media_activity_new_update_content', $content ),
            'type' => $type,
            'item_id' => $media_id
            ) );


    /* Require the notifications code so email notifications can be set on the 'bp_activity_posted_update' action. */
    do_action( 'bp_media_posted_update', $content, $user_id, $media_id, $activity_id );
    return $activity_id;
}
/**
 *Record to activity streaming
 * @global <type> $bp
 * @param <type> $args
 * @return <type>
 *
 */
function bp_media_record_activity( $args = '' ) {
    global $bp;

    if ( !function_exists( 'bp_activity_add' ) )
        return false;


    /* If media is not public, hide the activity sitewide. */

    $privacy = false;

    $defaults = array(
            'id' => false,
            'user_id' => $bp->loggedin_user->id,
            'action' => '',
            'content' => '',
            'primary_link' => '',
            'component' => $bp->media->id,
            'type' => false,
            'item_id' => false,
            'secondary_item_id' => false,
            'recorded_time' => gmdate( "Y-m-d H:i:s" ),
            'hide_sitewide' => $privacy
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );
    $rt_test_media = bp_activity_add( array(
            'id' => $id,
            'user_id' => $user_id,
            'action' => $action,
            'content' => $content,
            'primary_link' => $primary_link,
            'component' => $component,
            'type' => $type,
            'item_id' => $item_id,
            'secondary_item_id' => $secondary_item_id,
            'recorded_time' => $recorded_time,
            'hide_sitewide' => $hide_sitewide )
    );

    return $rt_test_media;
}
/**
 *
 * callback function to the filtering of activity
 * @global <type> $bp
 * @param <type> $user_id
 * @return <type>
 */
function bp_media_recent_activity_item_ids_for_user( $user_id = false ) {
    global $bp;
    
    if ( !$user_id )
        $user_id = ( $bp->displayed_user->id ) ? $bp->displayed_user->id : $bp->loggedin_user->id;

    return BP_Media_Picture::get_activity_recent_ids_for_user( $user_id );
}
/**
 * This code is intented for search purpose for reference only
 * @global <type> $bp
 *
 */
function bp_directory_media_search_form() {
    global $bp; ?>
<form action="" method="get" id="search-media-form">
    <label><input type="text" name="s" id="media_search" value="<?php if ( isset( $_GET['s'] ) ) {
        echo attribute_escape( $_GET['s'] );
    } else {
        _e( 'Search anything...', 'buddypress-media' );
    } ?>"  onfocus="if (this.value == '<?php _e( 'Search anything...', 'buddypress-media' ) ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e( 'Search anything...', 'buddypress-media' ) ?>';}" /></label>
    <input type="submit" id="media_search_submit" name="media_search_submit" value="<?php _e( 'Search', 'buddypress-media' ) ?>" />
</form>
    <?php
}

function bp_media_fetch_avatar( $args = '', $media = false ) {

    $defaults = array(
            'item_id' => false,
            'type' => 'full',
            'width' => false,
            'height' => false,
            'class' => 'avatar',
            'css_id' => false,
            'alt' => __( 'Media Avatar', 'buddypress-media' )
    );

    $params = wp_parse_args( $args, $defaults );

    // hard code these options to prevent tampering
    // DO NOT try to use a gravatar, ever!
    $params['object'] = 'media';
    $params['avatar_dir'] = 'media-avatars';
    $params['no_grav'] = true;

    // try to grab avatar file
    $avatar = bp_core_fetch_avatar( $params );

    if ( !empty( $avatar ) ) {

        // found an avatar file, return html for it
        return $avatar;

    } else {

        return apply_filters( 'bp_media_fetch_avatar_not_found', sprintf( '<img src="%s" alt="%s" id="%s" class="%s"%s%s />', $avatar_url, $alt, $css_id, $class, $attr_width, $attr_height ), $args );
    }
}

/**
 * Filter all AJAX bp_filter_request() calls to add group and user ids to group home page calls
 *
 * @param string $query_string
 * @return string
 */
function bp_media_dtheme_ajax_querystring_group_filter( $query_string ) {
    global $bp;

    // look for groups component and links action
    if ( $bp->groups->slug == $bp->current_component && $bp->current_action == $bp->media->slug ) {

        $args = array();
        parse_str( $query_string, $args );

        // inject group id
        $args['group_id'] = $bp->groups->current_group->id;

        // inject scope if we are on my group mediaall/photo/video/audio page
        switch ($bp->action_variables[0]) {
            case 'mediaall' : default:
                $args['scope'] = 'mediaall';
                break;
            case 'video' :
                $args['scope'] = 'video';
                break;
            case 'audio' :
                $args['scope'] = 'audio';
                break;
            case 'photo' :
                $args['scope'] = 'photo';
                break;
        }

        return http_build_query( $args );
    }
    return $query_string;
}
add_filter( 'bp_dtheme_ajax_querystring', 'bp_media_dtheme_ajax_querystring_group_filter', 1 );
/**
 *This function retrieves the kaltura id stored in db
 * @global <type> $bp
 * @global <type> $wpdb
 * @param <type> $id
 * @return <type> 
 */
function get_kaltura_media_id($id){
    global $bp, $wpdb;
    $q = "SELECT entry_id from {$bp->media->table_media_data} WHERE id = {$id} ";
    return  $wpdb->get_var($wpdb->prepare($q));
}
/**
 * this function is for changing the media album via ajax call ref can be found in single.js
 *
 */

function rt_album_update_callback() {
    $image_id = $_POST['image_id'];
    $album_id = $_POST['album_id'];
    global $wpdb,$bp,$kaltura_validation_data; // this is how you get access to the database

    $q1 = "UPDATE {$bp->media->table_media_data} SET album_id = {$album_id} WHERE ID = {$image_id} ";
    $wpdb->query($q1);
    echo 'Changed to album ';

    die();//Ajax call must die here
}
add_action('wp_ajax_rt_album_update', 'rt_album_update_callback');
add_action('wp_ajax_nopriv_rt_album_update', 'rt_album_update_callback');
/**
 * Feed Trail
 */

function bp_media_action_link_feed() {
	global $bp, $wp_query;

	if ( $bp->current_component != $bp->media->slug || $bp->current_action != 'feed' )
		return false;

	$wp_query->is_404 = false;
	status_header( 200 );

	include_once( 'feed/bp-media-feed.php' );
	die;
}
add_action( 'bp_init', 'bp_media_action_link_feed', 6 );
?>
