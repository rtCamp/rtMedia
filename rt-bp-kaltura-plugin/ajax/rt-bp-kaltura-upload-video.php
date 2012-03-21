<?php
/*
 * This is ajax file called when any media contents is uploaded from KCW
 */
require_once("../../../../wp-load.php");
global $wpdb,$current_user;


$rt_entry_id_list =  $_POST['rt_entry_id_list'];
$rt_entry_id_list_arr = explode(',',$rt_entry_id_list);
$rt_entry_list_arr_length = count($rt_entry_id_list_arr);



echo "<br />type = ".$rt_entry_type_list =  $_POST['rt_entry_type_list'];
$rt_entry_type_list_arr = explode(',',$rt_entry_type_list);

var_dump($rt_entry_type_list);

/**
 * The contents are uploaded userwise, but only wp db knows whos content is what and
 * not the kaltura as partner ID for all the social network is same...!
 */

//test#1:rows are not inserted on opera...!!!

global $current_user;
for($i = 0;$i<$rt_entry_list_arr_length-1;$i++){
    $query = "INSERT INTO wp_bp_rt_media (rt_content_id,rt_content_type,rt_user_id, rt_created_at) VALUES  ('$rt_entry_id_list_arr[$i]','$rt_entry_type_list_arr[$i]','$current_user->ID',NOW())";
    $wpdb->query($query);
}
?>
