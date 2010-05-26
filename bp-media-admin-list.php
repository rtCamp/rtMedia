<?php
//require_once('admin.php');
function media_add_admin_css() {
    wp_enqueue_style( 'media_add_admin_css',  BP_MEDIA_PLUGIN_URL . '/themes/media/css/media-admin.css' );
    wp_enqueue_style( 'media_add_datepicker_css',  BP_MEDIA_PLUGIN_URL . '/themes/media/css/datepicker/jquery.ui.all.css' );

}
add_action( 'admin_menu', 'media_add_admin_css' );

function media_add_admin_js() {
    wp_deregister_script('jquery');
    wp_enqueue_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js');
    wp_enqueue_script( 'rt_jqueryui',  BP_MEDIA_PLUGIN_URL . '/themes/media/js/jquery.ui.core.js','jquery' );
    wp_enqueue_script( 'media_add_admin_js',  BP_MEDIA_PLUGIN_URL . '/themes/media/js/jquery.ui.datepicker.js','rt_jqueryui' );
}
add_action( 'admin_menu', 'media_add_admin_js' );

function rt_media_administration() {
    global $wpdb, $bp;

    if ( !is_site_admin() )
        return false;

    /* Add the administration tab under the "Site Admin" tab for site administrators */
    add_submenu_page('bp-general-settings', //$parent
            __('Media Adminstration','Media Adminstration'),//$page_title
            __('Media Adminstration','Media Adminstration'),//$menu_title
            'manage_options',//$access_level
            'bp-media-admin',//$file
            "rt_media_admin_page" );//$function
}
add_action('admin_menu', 'rt_media_administration');


//code to catch the post fields


    if(isset($_POST['delete_media'])) {
         $a = $_POST['rt-media-action'];
        echo $a;
}

  











function rt_media_admin_page() {
    global $bp,$kaltura_validation_data,$wpdb;




     if(isset($_POST['rt-submit'])) {
//        var_dump($_POST['rt-submit']);
       $filter_date = $_POST['filter-date'];
       $filter_user = $_POST['filter-user'];
       $filter_type = $_POST['filter-type'];
       $pick_date   = $_POST['pick-date'];
       echo $filter_user;

        switch($filter_type){
            case 'Video':
                $filter_type = 1;
                break;
            case 'Audio':
                $filter_type = 5;
                break;
            case 'Photo':
                $filter_type = 2;
                break;
        }

        //to find username

        
        $where_user = "WHERE md.user_id={$filter_user} AND md.user_id = wu.id";
  

}




    
    $where_default = "WHERE md.user_id = wu.id";
    if(!empty($filter_user))
        $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where_user}";
    else
        $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where_default}";

        $result = $wpdb->get_results($wpdb->prepare($q));
        echo $wpdb->last_query;
        $cnt = count($result);



    for($i = 0;$i < $cnt;$i++) {
        if(!($i==0))
            $entry_list .=  ',';
        $entry_list  .= $result[$i]->entry_id ;
    }
    $kaltura_list = get_data_from_kaltura($entry_list);

    //fetching username of those who had uploaded the media
    $q1 = "select DISTINCT(display_name), wu.id from {$wpdb->users} wu JOIN {$bp->media->table_media_data} md WHERE wu.id = md.user_id";
    $media_user_name = $wpdb->get_results($wpdb->prepare($q1));
//    var_dump($media_user_name);

    ?>

<div class="wrap">
    <?php echo $tang.'anand'; ?>
    <h2>Media Adminstration : Advanced</h2>
    <form id="media-filter" action="" method="post">
        <div class="tablenav">
            <div class="alignleft actions">
                <select name="rt-media-action">
                    <option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
                    <option value="delete"><?php _e('Delete Forever'); ?></option>
                </select>
                <input type="submit" value="<?php esc_attr_e('Apply'); ?>" name="delete_media" id="doaction" class="button-secondary action" />

                <select name="filter-date">
                    <option value="-1" selected="selected"><?php _e('Time Filter'); ?></option>
                    <option value="last-week"><?php _e('Last Week'); ?></option>
                    <option value="last-month"><?php _e('Last Month'); ?></option>
                </select>
                <span>Specify Date: <input type="text" id="datepicker" name="pick-date" size="10"></span>

                <select name="filter-user">
                    <option value="-1" selected="selected"><?php _e('User Filter'); ?></option>
                        <?php

                        for($name =0 ; $name<count($media_user_name);$name++) {
                            ?>
                    <option value="<?php _e($media_user_name[$name]->id); ?>"><?php _e($media_user_name[$name]->display_name); ?></option>
                            <?php }?>
                </select>

                <select name="filter-type">
                    <option value="-1" selected="selected"><?php _e('Media Filter'); ?></option>
                    <option value="photo"><?php _e('Photo'); ?></option>
                    <option value="video"><?php _e('Video'); ?></option>
                    <option value="audio"><?php _e('Audio'); ?></option>
                </select>

                <input type="submit" id="post-query-submit" name ="rt-submit" value="<?php esc_attr_e('Filter'); ?>" class="button-secondary" />
            </div>
        </div>


        <table class="widefat post fixed" cellspacing="0">
            <thead>
                <tr>
                    <th scope="row" class="check-column"><input type="checkbox" name="linkcheck[]" value="a" /></th>
                    <th class="manage-column column-title">Thumbnail</th>
                    <th class="manage-column column-title">Media Owner</th>
                    <th class="manage-column column-title">Media Type</th>
                    <th class="manage-column column-title">Date of Creation</th>
                </tr>
            </thead>
            <tbody>

                    <?php


                    for($k=0;$k<count($kaltura_list);$k++) {

                        switch($result[$k]->media_type) {
                            case '2':
                                $media_type ='Photo';
                                break;
                            case '1':
                                $media_type ='Video';
                                break;
                            case '5':
                                $media_type ='Audio';
                                break;
                        }
                        echo '</tr>';
                        echo '<th scope="row" class="check-column"><input type="checkbox" name="linkcheck[]" value="a" /></th>';
                        echo '<td class="column-title"><img height= "30" width = "45px"  src= "'.$kaltura_list[$k]->thumbnailUrl .'jpg"><p>'.$kaltura_list[$k]->name.'</p></td>';
                        echo '<td class="column-author">'.$result[$k]->user_login.'</td>';
                        echo '<td class="column-categories">'.$media_type.'</td>';
                        echo '<td class="column-date">'.date( "F j, Y",$kaltura_list[$k]->createdAt).'</td>';
                        echo '</tr>';

                    }


                    ?>

            </tbody>
            <thead>
                <tr>
                    <th scope="row" class="check-column"><input type="checkbox" name="linkcheck[]" value="a" /></th>
                    <th class="manage-column column-title">Thumbnail</th>
                    <th class="manage-column column-title">Media Owner</th>
                    <th class="manage-column column-title">Media Type</th>
                    <th class="manage-column column-title">Date of Creation</th>
                </tr>
            </thead>

        </table>


    </form>

</div>
    <?php
}

function get_data_from_kaltura($entry_ids = false) {
    global $bp,$kaltura_validation_data;
    ob_clean();

    try {
//        var_dump($kaltura_validation_data['client']->baseEntry);
        $data = $kaltura_validation_data['client']->baseEntry->getByIds($entry_ids);
    }
    catch (Exception $e) {
        echo 'Oops Error while fetching data .. try Again ';
    }

    return $data;
}



?>