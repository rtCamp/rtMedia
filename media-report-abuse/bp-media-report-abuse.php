<?php
/* 
 * This section contains the report abuse
 * Any registered member can report abused content to admin
 */

/*  Report Abuse
 *
 */

function media_report_abuse_callback() {
    global $wpdb,$bp; // this is how you get access to the database
    $report_id = $_GET['report_id'];
    $report_type = $_GET['report_type'];
    $report_url = $_GET['report_url'];

    $kalturaid = get_kaltura_media_id($report_id);

    $media_user_owner_id = rt_who_owns_this_media($report_id);
    $meda_owner_username = bp_core_get_username($media_user_owner_id);
    $abuse_reporter = bp_get_loggedin_user_fullname();
    $admin_email = get_option('admin_email');

    $to = $admin_email;
    $subject = 'Report Abuse on Media id : '.$report_id;
    $wpdb->query("INSERT INTO {$bp->media->table_report_abuse} (media_id,entry_id,media_owner,abuse_reporter,abusued_url,abusue_type) VALUES ({$report_id},'{$kalturaid}','{$meda_owner_username}','{$abuse_reporter}','{$report_url}','{$report_type}') ");

    $check_abuse = $wpdb->query("SELECT sum(counter) from {$bp->media->table_report_abuse} WHERE media_id = {$report_id} AND entry_id = '{$kalturaid}' ");

    $message = 'This following is an Abuse reported by '. $abuse_reporter .'

    URL = '.$report_url.'
    Media id :'.$report_id .'.
    kaltura id : '.$kalturaid .'
    Media Owner : '.$meda_owner_username    .'
    Abused :'.$check_abuse .' time
';
    wp_mail($to, $subject, $message);
 echo 'Your report has been sent to administrator';

 die();
}
add_action('wp_ajax_media_report_abuse', 'media_report_abuse_callback');
add_action('wp_ajax_nopriv_media_report_abuse', 'media_report_abuse_callback');


function get_kaltura_media_id($id){
    global $bp, $wpdb;
    return  $wpdb->get_var($wpdb->prepare("SELECT entry_id from {$bp->media->table_media_data} WHERE id = {$id} "));
}
?>
