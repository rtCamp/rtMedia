<?php

//
//function bp_media_add_abuse_nav() {
//	global $bp;
//
//	/* Set up settings as a sudo-component for identification and nav selection */
//	$bp->settings->id = 'settings';
//	$bp->settings->slug = BP_SETTINGS_SLUG;
//
//	/* Register this in the active components array */
//	$bp->active_components[$bp->settings->slug] = $bp->settings->id;
//
//	$settings_link = $bp->loggedin_user->domain . $bp->settings->slug . '/';
//
////	bp_core_new_subnav_item( array( 'name' => __( 'Report Abuse', 'buddypress' ),
////                                        'slug' => 'media-admin-report-abuse',
////                                        'parent_url' => $settings_link,
////                                        'parent_slug' => $bp->settings->slug,
////                                        'screen_function' => 'bp_media_abuse_list',
////                                        'position' => 20, 'user_has_access' => is_site_admin()
////            ));
//       do_action( 'bp_media_add_abuse_nav');
//}
////add_action( 'wp', 'bp_media_add_abuse_nav', 2 );
////add_action( 'admin_menu', 'bp_media_add_abuse_nav', 2 );
//
//add_action( 'plugins_loaded', 'bp_media_add_abuse_nav' );
//add_action( 'admin_menu', 'bp_media_add_abuse_nav' );
//
//
//
//function bp_media_abuse_list(){
//    echo 'ashish';
//        add_action( 'bp_media_title', 'bp_media_screen_general_settings_title' );
//	add_action( 'bp_media_content', 'bp_media_screen_general_settings_content' );
//
//	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
//}
//
//
//function bp_media_screen_general_settings_title() {
//	_e( 'Abused Content', 'buddypress' );
//}
//
//function bp_media_screen_general_settings_content() {
//	global $bp, $current_user, $bp_settings_updated, $pass_error;
//        echo 'anand';
//}
?>