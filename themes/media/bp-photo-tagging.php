<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/* Phototagging code starts from here
 *
 */


function load_bp_data_callback() {
    global $bp, $wpdb;
    $photo_id = $_GET['photoID'];
    $bp->media->photo_tag = $wpdb->base_prefix . 'bp_photo_tags';
    $result = $wpdb->get_results("SELECT * FROM {$bp->media->photo_tag} WHERE PHOTOID = '{$photo_id}' ");
    $tag = count($result);
    $active_tags = ($result);
    if(!empty($result)) {
        for($i = 0 ;$i<$tag;$i++){
            (int)$active_tags[$i]->ID = $result[$i]->ID;
                 $active_tags[$i]->PHOTOID = $result[$i]->PHOTOID;
            (int)$active_tags[$i]->Y = $result[$i]->Y;
            (int)$active_tags[$i]->WIDTH = $result[$i]->WIDTH;
            (int)$active_tags[$i]->HEIGHT = $result[$i]->HEIGHT;
                $active_tags[$i]->MESSAGE = $result[$i]->MESSAGE;
            (int)$active_tags[$i]->X = $result[$i]->X;
        }
    }

    $active_tags = json_encode($active_tags);
    header('Content-type: application/json');
    echo $active_tags;
    die();
}
add_action('wp_ajax_load_bp_data', 'load_bp_data_callback');
add_action('wp_ajax_nopriv_load_bp_data', 'load_bp_data_callback');

//saving tagged data

function save_bp_tag_data_callback() {

    global $wpdb,$bp;

    $photo_id = $_GET['photoID'];
    $message = $_GET['message'];
    $height = $_GET['height'];
    $width = $_GET['width'];
    $x = $_GET['x'];
    $y = $_GET['y'];
    if(is_user_logged_in()){
   $bp->media->photo_tag = $wpdb->base_prefix . 'bp_photo_tags';
   $k = $wpdb->query("INSERT INTO {$bp->media->photo_tag} (PHOTOID, Y,WIDTH,HEIGHT,MESSAGE,X) VALUES ('{$photo_id}',{$y},{$width},{$height},'{$message}',{$x}) ");

   $id = $wpdb->get_var($wpdb->prepare("SELECT ID from {$bp->media->photo_tag} WHERE PHOTOID = '{$photo_id}' AND X = {$x} AND Y = {$y} "));

    $active_users = array('ID' => $id);
    $active_users = json_encode($active_users);
    header('Content-type: application/json');
    echo $active_users;
    }
    else{
        $active_users = array('ID' => $id);
    $active_users = json_encode($active_users);
    header('Content-type: application/json');
    echo $active_users;

    }
    die();
}
add_action('wp_ajax_save_bp_tag_data', 'save_bp_tag_data_callback');
add_action('wp_ajax_nopriv_save_bp_tag_data', 'save_bp_tag_data_callback');


//deleting tag data

function delete_bp_tag_data_callback() {
 global $wpdb,$bp;

    $id = $_GET['id'];
    if(is_user_logged_in()){

   $bp->media->photo_tag = $wpdb->base_prefix . 'bp_photo_tags';
   $k = $wpdb->query($wpdb->prepare("DELETE FROM {$bp->media->photo_tag} WHERE ID = {$id} "));

    $delete_tag = array("ID"=>$id );
    $delete_tag = json_encode($delete_tag);
    header('Content-type: application/json');
    echo $delete_tag;
     }
    else{
    $delete_tag = array("ID"=>$id );
    $delete_tag = json_encode($delete_tag);
    header('Content-type: application/json');
    echo $delete_tag;
    }
    die();
}
add_action('wp_ajax_delete_bp_tag_data', 'delete_bp_tag_data_callback');
add_action('wp_ajax_nopriv_delete_bp_tag_data', 'delete_bp_tag_data_callback');


?>
