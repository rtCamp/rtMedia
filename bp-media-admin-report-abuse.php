<?php
/**
 * Function to add a new nav item Under my Account ->Settings -> Abused Media
 * @global <type> $bp
 */
function bp_media_add_abuse_nav() {
	global $bp;

	/* Set up settings as a sudo-component for identification and nav selection */
	$bp->settings->id = 'settings';
	$bp->settings->slug = BP_SETTINGS_SLUG;

	/* Register this in the active components array */
	$bp->active_components[$bp->settings->slug] = $bp->settings->id;

	/* Add the settings navigation item */


	$settings_link = $bp->loggedin_user->domain . $bp->settings->slug . '/';
        if(is_site_admin())
        bp_core_new_subnav_item( array( 'name' => __( 'Abused Media', 'buddypress' ), 'slug' => 'abused-media', 'parent_url' => $settings_link, 'parent_slug' => $bp->settings->slug, 'screen_function' => 'bp_media_abused_list', 'position' => 30, 'user_has_access' => is_site_admin() ) );

}
add_action( 'wp', 'bp_media_add_abuse_nav', 2 );
add_action( 'admin_menu', 'bp_media_add_abuse_nav', 2 );

function bp_media_abused_list(){
//    echo 'hai';
    do_action( 'bp_media_abused_screen' );
        add_action( 'bp_template_title', 'bp_media_abused_title' );
	add_action( 'bp_template_content', 'bp_media_abused_content' );
        bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}


function bp_media_abused_title(){
    _e("Report Abuse to Media","buddypress");
}
function bp_media_abused_content(){
      global $bp,$wpdb,$kaltura_validation_data;
      
//       $picture_data = $kaltura_validation_data['client']->getbyIds->list($filter,$pager);
//      var_dump($bp);
      ob_clean();
      $q =  "select * from {$bp->media->table_report_abuse} ";
      $result = $wpdb->get_results($wpdb->prepare($q));
      
      echo "<table><tr><th>Media Thumbnail</th><th>Media owner</th><th>Abuse Report</th><th>Link</th><th>Type</th><th>Action</th></tr>";
      for($i=0;$i<count($result);$i++){
        echo '<tr id ="ig-'.$result[$i]->id.'">';
        echo "<th><img class=rt-abuse-list src=".$kaltura_validation_data['client']->media->get($result[$i]->entry_id)->thumbnailUrl."/></th>";
        echo "<td align=center>".$result[$i]->media_owner."</td>";
        echo "<td align=center>".$result[$i]->abuse_reporter."</td>";
//        echo '"<td><a href ="'.$result[$i]->abusued_url.'">'.$result[$i]->abusued_url.'</a></td>"';
        echo '<td align=center><a href ="'.$result[$i]->abusued_url.'">'.$kaltura_validation_data['client']->media->get($result[$i]->entry_id)->name.'</a></td>';
        echo "<td align=center>".$result[$i]->abusue_type."</td>";
        echo '<td align="center" id ="ignore-'.$result[$i]->id.'" ><a class="ignore" style ="cursor:pointer">Ignore</a></td>';// this js call is witten in general.js
        echo '</tr>';
      }
      echo "</table>";

//      var_dump($kaltura_validation_data);
//      var_dump($kaltura_validation_data['client']->media->get('564ah9dl2h')->name);
}


function undo_media_abuse_callback() {

    $image_id = $_POST['image_id'];
    global $wpdb, $bp;
    $url = $bp->root_domain;
    $q = "DELETE FROM {$bp->media->table_report_abuse} WHERE ID = {$image_id}";
    $p = $wpdb->query($q);
    if($p == 1 )
        echo '1';
    else
        echo '0';
    
    die(); //since wp ajax call must die here
}
add_action('wp_ajax_undo_media_abuse', 'undo_media_abuse_callback');
add_action('wp_ajax_undo_media_abuse', 'undo_media_abuse_callback');



?>