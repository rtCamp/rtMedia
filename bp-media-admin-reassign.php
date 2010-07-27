<?php

/**
 * This page shows list of media and provides facility to admin to reassign media to other users
 */
function rt_media_admin_page_reassign() {
?>
<!--
<form name="test" method="get" action="<?php // echo admin_url('admin.php?page=bp-media-admin-reassign'); ?>">
    <input type="hidden" name="page" value="bp-media-admin-reassign" />
    <input type="text" name="rt-name" value="test" />
    
    <input type="submit" name="rt-submit" value="go"/>
</form>
-->

<?php
//Process the filter here. Filters are set as user select them from the form's drop down options
    //Here by default 0 for all users and if other that 0, the number represents user_id
    $rt_user_filter = '0';
    //Here by default 0 for all the media and 1:video 2:photo 5:audio
    $rt_media_type_filter = '0';

    //code to remove from list

    if ($_REQUEST['removephoto'] == "removephoto") {
        $value = $_REQUEST['value'];
        $get_rt_photo_feature_option = get_option('rt_bp_add_to_photo_feature_list');

        $k = array_search($value, $get_rt_photo_feature_option);
        unset($get_rt_photo_feature_option[$k]);
        $new_list = array_values($get_rt_photo_feature_option);
        delete_option('rt_bp_add_to_photo_feature_list');
        update_option('rt_bp_add_to_photo_feature_list', $new_list);
        echo '  <div class="updated fade" style="background-color: #FFF123;">Featured Photo Option Saved</div>';
    }

    if ($_REQUEST['removevideo'] == "removevideo") {
        $value = $_REQUEST['value'];
        $get_rt_video_feature_option = get_option('rt_bp_add_to_video_feature_list');
        $k = array_search($value, $get_rt_video_feature_option);
        unset($get_rt_video_feature_option[$k]);
        $new_list = array_values($get_rt_video_feature_option);

        delete_option('rt_bp_add_to_video_feature_list');
        update_option('rt_bp_add_to_video_feature_list', $new_list);
        echo '  <div class="updated fade" style="background-color: #FFF123;">Featured Video Option Saved</div>';
    }



    if (isset($_REQUEST['rt_filter_button'])) {

        if ($_REQUEST['rt_user_filter'] != '0') {
            $rt_user_filter = $_REQUEST['rt_user_filter'];
        }


        if ($_REQUEST['rt_media_type_filter'] != '0') {
            $rt_media_type_filter = $_REQUEST['rt_media_type_filter'];
        }
    }

    //Here GET value in pagination is given preferance over the selected value
    if (isset($_GET['user'])) {
        $rt_user_filter = $_GET['user'];
    }
    
    if (isset($_GET['media_type'])) {
        $rt_media_type_filter = $_GET['media_type'];

    }
    if (isset($_REQUEST['rt_apply_action'])) {
        $rt_current_action = '';
        if (isset($_REQUEST['rt_get_action'])) {
            $rt_entry_list = $_REQUEST['rt_checked_list'];
            switch ($_REQUEST['rt_get_action']) {
                case '0': default:
                    //do nothing
//                    echo "no action";
                    break;
                case '1':
                    //Action = Delete Forever
                    if ($_REQUEST['rt_get_action'] == 0) {
                        wp_redirect(admin_url('admin.php?page=bp-media-admin-reassign'));
                        echo '  <div class="updated fade" style="background-color: #FFF123;">Please select an action</div>';
                    }
                    if ($_REQUEST['rt_get_action'] == 1) {
                        $rt_delete_success = rt_delete_media($rt_entry_list);
                        if ($rt_delete_success) {
                            echo '  <div class="updated fade" style="background-color: #FFF123;">Selected entries Deleted Successfully
                                      </div>';
                        } else {
                            echo '  <div class="updated fade" style="background-color: #FFF123;">Media not Deleted!
                                      </div>';
                        }
                    }
                    break;
                case '2':
                    //Action = Reassign
                    rt_reassign_media($rt_entry_list, $_REQUEST['rt_user_id_list']);
                    break;
                
                case '3'://action = Add to feature video list

                        if ($_REQUEST['rt_get_action'] == 0) {
                        wp_redirect(admin_url('admin.php?page=bp-media-admin-reassign'));
                        echo '  <div class="updated fade" style="background-color: #FFF123;">Please select an action</div>';
                    }

                    //this if section is modified by Kapil to accomodate changes of storing order of photos to be displayed in widget
                    if (($_REQUEST['rt_get_action'] == 3 ) && ($_REQUEST['rt_user_id_list'] == 0 )) {

                        /**
                         * This is simple array with subscript so order is associated with entry_id.
                         * Algo:
                         * 1. Here for the first time just keep the array in option table as it is.
                         * 2. Whenever the order is changed from the UI, we have to sort the array in 'rt_bp_add_to_photo_feature_list' value.
                         * 3. Newly added entry_ids will be appended at the end with new subscript.
                         *
                         */


                        // Algo step 1: for the first time this value is stored in options table
                        //Here, adding new elements to the list also possible so, we have to append new elements to the list at the end
                        /* PRASAD CODE */
                        /*
                         * 1. check whether videos already exists in options table if not update_options first time
                         * 2. else update_options with merged array. Before that check whether user is trying to add an item which is already 'Featured'
                         *    If so then dont add.
                         */
                            $rt_old_featured_video = get_option('rt_bp_add_to_video_feature_list');
                            if(is_array($rt_old_featured_video)){
                                for($i=0;$i<count($rt_entry_list);$i++){
                                    if(!in_array($rt_entry_list[$i], $rt_old_featured_video)){
                                        $new_list_123 = array_merge($rt_old_featured_video,Array($rt_entry_list[$i]));
                                        update_option('rt_bp_add_to_video_feature_list',$new_list_123);
                                    }
                                }

                                echo '  <div class="updated fade" style="background-color: #FFF123;">Your Featured Video Items Saved Successfuly!
                                          </div>';
                            }
                            else
                                update_option('rt_bp_add_to_video_feature_list',$rt_entry_list);

                        /* PRASAD CODE END */
                    } else {
                            echo '  <div class="updated fade" style="background-color: #FFF123;">Please check your operation!
                                      </div>';
                    }
                    break;



//                    if ($_REQUEST['rt_get_action'] == 0) {
//                        wp_redirect(admin_url('admin.php?page=bp-media-admin-reassign'));
//                        echo '  <div class="updated fade" style="background-color: #FFF123;">Please select an action</div>';
//                    }
//                    if (($_REQUEST['rt_get_action'] == 3 ) && ($_REQUEST['rt_user_id_list'] == 0 )) {
//
//                        $rt_video_list = get_option('rt_bp_add_to_video_feature_list');
//                        foreach ($rt_entry_list as $rt_new_value) {
//                            $rt_video_list[] = $rt_new_value;
//                        }
//                        update_option('rt_bp_add_to_video_feature_list', $rt_video_list);
//                        echo '  <div class="updated fade" style="background-color: #FFF123;">Your Featured Video Items Saved Successfuly!
//                                      </div>';
//                    } else {
//                        echo '  <div class="updated fade" style="background-color: #FFF123;">Please check your operation!
//                                      </div>';
//                    }
//                    break;

                case '4': //action = Add to Featured Photo
                    if ($_REQUEST['rt_get_action'] == 0) {
                        wp_redirect(admin_url('admin.php?page=bp-media-admin-reassign'));
                        echo '  <div class="updated fade" style="background-color: #FFF123;">Please select an action</div>';
                    }

                    //this if section is modified by Kapil to accomodate changes of storing order of photos to be displayed in widget
                    if (($_REQUEST['rt_get_action'] == 4 ) && ($_REQUEST['rt_user_id_list'] == 0 )) {
                        
                        /**
                         * This is simple array with subscript so order is associated with entry_id.
                         * Algo:
                         * 1. Here for the first time just keep the array in option table as it is.
                         * 2. Whenever the order is changed from the UI, we have to sort the array in 'rt_bp_add_to_photo_feature_list' value.
                         * 3. Newly added entry_ids will be appended at the end with new subscript.
                         * 
                         */
                        

                        // Algo step 1: for the first time this value is stored in options table
                        //Here, adding new elements to the list also possible so, we have to append new elements to the list at the end
                        /* PRASAD CODE */
                        /*
                         * 1. check whether photos already exists in options table if not update_options first time
                         * 2. else update_options with merged array. Before that check whether user is trying to add an item which is already 'Featured'
                         *    If so then dont add.
                         */
                            $rt_old_featured_photo = get_option('rt_bp_add_to_photo_feature_list');
                            if(is_array($rt_old_featured_photo)){
                                for($i=0;$i<count($rt_entry_list);$i++){
                                    if(!in_array($rt_entry_list[$i], $rt_old_featured_photo)){
                                        $new_list_123 = array_merge($rt_old_featured_photo,Array($rt_entry_list[$i]));
                                        update_option('rt_bp_add_to_photo_feature_list',$new_list_123);
                                    }
                                }
                                echo '  <div class="updated fade" style="background-color: #FFF123;">Your Featured Photo Items Saved Successfuly!!!!
                                          </div>';
                            }
                            else
                                update_option('rt_bp_add_to_photo_feature_list',$rt_entry_list);

                        /* PRASAD CODE END */

                    } else {
                            echo '  <div class="updated fade" style="background-color: #FFF123;">Please check your operation!
                                      </div>';
                    }
                    break;
            }
        }
    }
    //Now proper filters are set, lets fetch filtered data here
    rt_show_filtered_entries($rt_user_filter, $rt_media_type_filter);
}

function rt_show_filtered_entries($rt_user_filter, $rt_media_type_filter) {
    global $bp, $wpdb, $kaltura_validation_data;
    // If $rt_user_filter == 0 then all the users
    // If $rt_media_type_filter == 0 then all type of media
    //TODO : This value must be made dynamic and user must be able to edit it somewhere from dashboard
    $rt_per_page = get_option('bp_rt_dashboard_size'); //reference is in bp_media_admin.php for dynamic listing of media items;
    //Total number of entry_id stored in buddypress DB table (wp_bp_media_data)
    //Here $where determines "Filtering" of data
    //WARNING : DO NOT DELETE FOLLOWING THREE COMMENTED LINES!
    //Temporary filter values rtcamp = 926 test = 1023
    //$rt_user_filter = '926';
    //$rt_media_type_filter = '2';

    $and = ' AND ';
    $where = ' WHERE ';
    $query_condition1 = '';
    $query_condition2 = '';

    if ($rt_user_filter != '0') {
        $query_condition1 = " user_id = '$rt_user_filter'"; //2
    }
    if ($rt_media_type_filter != '0') {
        $query_condition2 = " media_type = '$rt_media_type_filter'";
    }
    if ($rt_user_filter == '0' && $rt_media_type_filter == '0') {
        //I.e. if both the above filters are zero, query should be fired without any where clause
        $and = '';
        $where = '';
        $query_condition1 = '';
        $query_condition2 = '';
    }

    //To remove AND from query
    if ($query_condition1 == '') {
        $and = '';
    }
    if ($query_condition2 == '') {
        $and = '';
    }

    $q = "select entry_id,user_id,media_type,id from {$bp->media->table_media_data} $where $query_condition1 $and $query_condition2";
    $result1 = $wpdb->get_results($wpdb->prepare($q));


//--------------------------------------------------------
//    //code for filtering featured list
    if($rt_media_type_filter=='6'){
         $get_rt_photo_feature_option = get_option('rt_bp_add_to_photo_feature_list');
       foreach ($get_rt_photo_feature_option as $key => $value){
        $tocheck .= '"'.$value.'"'.',';
    }
    $tocheck =  substr_replace($tocheck ,"",-1);
         $q =  "select entry_id,user_id,media_type,id from {$bp->media->table_media_data} WHERE entry_id IN ($tocheck)";
         $result1 = $wpdb->get_results($wpdb->prepare($q));

    }

    if($rt_media_type_filter=='7'){
         $get_rt_video_feature_option = get_option('rt_bp_add_to_video_feature_list');
         foreach ($get_rt_video_feature_option as $key => $value){
        $tocheck .= '"'.$value.'"'.',';
    }
    $tocheck =  substr_replace($tocheck ,"",-1);
         $q =  "select entry_id,user_id,media_type,id from {$bp->media->table_media_data} WHERE entry_id IN ($tocheck)";

         $result1 = $wpdb->get_results($wpdb->prepare($q));
    }

//--------------------------------------------------------

    $total_entry_id = count($result1);
    //This tells us the page number of our last page
    //Here pagination logic must be applied
    $fpage = isset($_REQUEST['fpage']) ? intval($_REQUEST['fpage']) : 1;

    $pagination = array(
        'base' => add_query_arg(array('fpage' => '%#%', 'num' => $rt_per_page, 'user' => $rt_user_filter, 'media_type' => $rt_media_type_filter)), // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
        'format' => '', // ?page=%#% : %#% is replaced by the page number
        'total' => ceil(($total_entry_id) / $rt_per_page),
        'current' => $fpage,
        'prev_next' => true,
        'prev_text' => __('&laquo;'),
        'next_text' => __('&raquo;'),
        'type' => 'plain'
    );

    $rt_post_offset = ($fpage - 1) * $rt_per_page;
    $q = $q . "LIMIT {$rt_post_offset}, {$rt_per_page}";
    $result = $wpdb->get_results($wpdb->prepare($q));

    //A comma seperated list of 'entry_id's
    $rt_entry_id_list = '';
    //Number of rows to be shown here must be paginated.
    //So the entry_id's to be inserted into $rt_entry_id_list are paginated and not after fetching the list
    foreach ($result as $key => $value) {
        $rt_entry_id_list = $rt_entry_id_list . $value->entry_id . ",";
    }
    //Entry_id's are fetched from kaltura so error must be catch, if any
    try {
        $rt_entry_id_data = $kaltura_validation_data['client']->baseEntry->getByIds($rt_entry_id_list);
//      Here the list must be shown in proper html form
    } catch (Exception $e) { //unable to get data from Kaltura server
        echo 'Oops Error while fetching data .. try Again ';
    }
?>

<?php
    //Get the media owner list


    $q = "SELECT  distinct user_login,user_id, user_table.ID FROM {$bp->media->table_media_data} media_table INNER JOIN {$wpdb->users} user_table WHERE media_table.user_id = user_table.ID ORDER BY user_table.user_login";

//    var_dump($q);
//    $q = "SELECT DISTINCT user_id from {$bp->media->table_media_data} ";
//    var_dump($q);

    $rt_distinct_user_ids = $wpdb->get_results($wpdb->prepare($q));
    $q1 = "SELECT user_login,ID from {$wpdb->users} ORDER BY user_login";
    $rt_user_id_list = $wpdb->get_results($wpdb->prepare($q1));
?>

    

    <div class="wrap">
        <h2>Media Dashboard</h2>
        <br/>

        <?php //echo admin_url('admin.php?page=bp-media-admin-reassign'); ?>
        <form action="<?php echo admin_url('admin.php?page=bp-media-admin-reassign'); ?>" method="get" name="rt_form_filter">
            <input type="hidden" name="page" value="bp-media-admin-reassign" />

            <select name="rt_get_action" id="rt_get_action" >
                <option value="0">Select Action</option>
                <option value="1">Delete Forever</option>
                <option value="2">Reassign</option>
                <option value="3">Add to Feature Video</option>
                <option value="4">Add to Feature Photo</option>
            </select>

            <select name="rt_user_id_list" id="rt_user_id_list">
                <option value="0">Assign To User</option>
            <?php foreach ($rt_user_id_list as $key => $value) {
 ?>
                <option value="<?php echo $value->ID; ?>"><?php echo $value->user_login; ?></option>
<?php } ?>
        </select>
        <input name="rt_apply_action" id="rt_apply_action" type="submit" value="Apply" class ="button-secondary action"/>

        <select name="rt_user_filter" id="rt_user_filter">
            <option value="0">Media Owner</option>
<?php foreach ($rt_distinct_user_ids as $key => $value) { ?>
                <option value="<?php echo $value->user_id; ?>"
            <?php
                if ((isset($_REQUEST['user']) && $_REQUEST['user'] == $value->user_id) || $_REQUEST['rt_user_filter'] == $value->user_id) {
                    echo "selected = 'selected'";
                }
            ?>
                        >
                        <?php //echo rt_get_user_login_name($value->user_id)?>
<?php echo $value->user_login; ?>
            </option>
<?php }

?>
                </select>
                <select name="rt_media_type_filter" id="rt_media_type_filter">
                    <option value="0">Media Type</option>
                    <option value="1" <?php
                    if ((isset($_REQUEST['rt_media_type_filter']) && $_REQUEST['rt_media_type_filter'] == '1') || $_REQUEST['rt_media_type_filter'] == '1') {
//                    if ($_REQUEST['rt_media_type_filter'] == '1') {
                        echo "selected = 'selected'";
                    }
?>>Video</option>
                    <option value="2" <?php
                    if ((isset($_REQUEST['rt_media_type_filter']) && $_REQUEST['rt_media_type_filter'] == '2') || $_REQUEST['rt_media_type_filter'] == '2') {
                        echo "selected = 'selected'";
                    }
?>>Photo</option>
                    <option value="5" <?php
                    if ((isset($_REQUEST['rt_media_type_filter']) && $_REQUEST['rt_media_type_filter'] == '5') || $_REQUEST['rt_media_type_filter'] == '5') {
                        echo "selected = 'selected'";
                    }
?>>Audio</option>
                    <option value="6" <?php
                    if ((isset($_REQUEST['rt_media_type_filter']) && $_REQUEST['rt_media_type_filter'] == '6') || $_REQUEST['rt_media_type_filter'] == '6') {
                        echo "selected = 'selected'";
                    }
?>>Featured Photo</option>
                    <option value="7" <?php
                    if ((isset($_REQUEST['rt_media_type_filter']) && $_REQUEST['rt_media_type_filter'] == '7') || $_REQUEST['rt_media_type_filter'] == '7') {
                        echo "selected = 'selected'";
                    }
?>>Featured Video</option>
        </select>

        <input type="submit" name="rt_filter_button" value="Filter" class ="button-secondary action"/>
<?php echo "<p>" . paginate_links($pagination) . "</p>"; ?>
                    <table class="widefat post fixed" cellspacing="0">
                        <thead>
                            <tr>
                                <th scope="row" class="check-column"><input type="checkbox" name="aschk[]" value="a" /></th>
                                <th class="manage-column column-title">Thumbnail</th>
                                <th class="manage-column column-title">Title</th>
                                <th class="manage-column column-title">Media Owner</th>
                                <th class="manage-column column-title">Media Type</th>
                                <th class="manage-column column-title">Date of Creation</th>
                            </tr>
                        </thead>
                        <tbody>

                <?php
//    The $result gives me paginated data
                    $rt_photo_list = get_option('rt_bp_add_to_photo_feature_list');
                    $rt_video_list = get_option('rt_bp_add_to_video_feature_list');

                    $i = 0;
                    foreach ($rt_entry_id_data as $key => $value) {
//                    echo '<pre>';print_r($result[$i]->id);
//                        for($j=0;$j<count($result);$j++){
                        ?>
                        <tr>
                            <th scope="row" class="check-column"><input type="checkbox" name="rt_checked_list[]" value="<?php echo $value->id ?>" /></th>
                            <td class="column-title"><a href="<?php echo rt_get_media_info($result, $value->id, '1'); ?>"><img height= "30" width = "45px"  src= "<?php echo $value->thumbnailUrl . 'jpg' ?> " alt="<?php echo $value->title ?>"/></a>
                        <br>
                        <?php
//                    if(i$rt_photo_list[$i] == $value->id )
                        if ($rt_photo_list) {
                            if (in_array($value->id, $rt_photo_list))
                                echo '<a class ="rt-remove" href=" ' . admin_url('admin.php?page=bp-media-admin-reassign&removephoto=removephoto&value=') . $value->id . '" id =' . $value->id . '>Remove Featured Photo</a>';
                        }
                        if ($rt_video_list) {
                            if (in_array($value->id, $rt_video_list))
                                echo '<a class ="rt-remove" href=" ' . admin_url('admin.php?page=bp-media-admin-reassign&removevideo=removevideo&value=') . $value->id . '" id =' . $value->id . '>Remove Featured Video</a>';
                        }
                        ?>
                    </td>
                    <td class="column-owner">
                        <h2 class="rt-mediatitle-admin" title="Click Here to Edit Media Title">
                            <p id="rt-single-media-id_<?php echo $result[$i]->id; ?>">
                                <?php echo $value->name; ?>
                            </p>
                        </h2>
                    </td>
                    <td class="column-author"><?php echo rt_get_media_info($result, $value->id, '2'); ?></td>
                    <td class="column-categories"><?php echo rt_media_type($value->mediaType) ?></td>
                    <td class="column-date"><?php echo date("F j, Y", $value->createdAt); ?></td>
                </tr>


                <?php
                        $i++;
                    }
                ?>
                </tbody>
                <thead>
                    <tr>
                        <th scope="row" class="check-column"><input type="checkbox" name="askchk[]" value="a" /></th>
                        <th class="manage-column column-title">Thumbnail</th>
                        <th class="manage-column column-title">Title</th>
                        <th class="manage-column column-title">Media Owner</th>
                        <th class="manage-column column-title">Media Type</th>
                        <th class="manage-column column-title">Date of Creation</th>
                    </tr>
                </thead>

            </table>
<?php echo "<p>" . paginate_links($pagination) . "</p>"; ?>
                </form>


    <?php
                    //To Show featured Media on Media Dashboard
                    rt_featured_photos_video_list();
    ?>
                    </div>
<?php
                    return $rt_html;
                }

                /**
                 *
                 * @param <type> $user_id
                 * @return <type> $user_login_name
                 */
                function rt_get_user_login_name($user_id) {
                    $user_info = get_userdata($user_id);
                    $user_login_name = $user_info->user_login;
                    return $user_login_name;
                }

                /**
                 *
                 * @param <type> $result
                 * @param <type> $entry_id
                 * @param <type> $filter
                 * @return <type> Depending upon filter, returns value
                 *                  filter = 1 => return media link
                 *                  filter = 2 => return Owner name
                 */
                function rt_get_media_info($result, $entry_id, $filter) {

                    $owner_id = '';
                    $db_id = '';
                    foreach ($result as $key => $value) {
                        if ($value->entry_id == $entry_id) {
                            $owner_id = $value->user_id;
                            $db_id = $value->id;
                        }
                    }
                    $profile_url = get_bloginfo('url') . '/members/' . rt_get_user_login_name($owner_id) . '/profile';
                    $media_url = get_bloginfo('url') . '/' . BP_MEDIA_SLUG . '/mediaall_' . $db_id;

                    switch ($filter) {
                        case '2':
                            return '<a href= "' . $profile_url . '">' . rt_get_user_login_name($owner_id) . '</a>';
                            break;
                        case '1':
                            return $media_url;
                            break;
                    }
                }

                /**
                 *
                 * @param <type> $mediaType (integer)
                 * @return <type> Returns Media Type in Words ;)
                 */
                function rt_media_type($mediaType) {
                    $type = '';
                    switch ($mediaType) {
                        case '1' :
                            $type = 'Video';
                            break;
                        case '2' :
                            $type = 'Photo';
                            break;
                        case '5' :
                            $type = 'Audio';
                            break;
                    }
                    return $type;
                }

                /**
                 *
                 * @global <type> $wpdb
                 * @global <type> $kaltura_validation_data
                 * @global <type> $bp
                 * @param <type> $rt_entry_list
                 * @return <type> true on successful deletion
                 */
                function rt_delete_media($rt_entry_list) {
                    global $wpdb, $kaltura_validation_data, $bp;

                    $result_add = 0;
//    $media_user_name = $wpdb->get_results($wpdb->prepare($q1));
                    try {
                        for ($i = 0; $i < count($rt_entry_list); $i++) {
                            if (!($rt_entry_list[$i] == 'a')) {
                                $q = "select id from {$bp->media->table_media_data} WHERE entry_id='{$rt_entry_list[$i]}' ";
                                $media_id = $wpdb->get_var($wpdb->prepare($q));

                                $q_media_data = "DELETE from {$bp->media->table_media_data} WHERE entry_id='{$rt_entry_list[$i]}' ";
                                $q_media_photo_tag = "DELETE from {$bp->media->table_photo_tag} WHERE entry_id= {$rt_entry_list[$i]} ";
                                $q_media_rating = "DELETE from {$bp->media->table_user_rating_data} WHERE image_id='{$media_id}' ";
                                $q_report_abuse = "DELETE from {$bp->media->table_report_abuse} WHERE entry_id='{$rt_entry_list[$i]}' ";

                                $wpdb->query($q_media_data);
                                $wpdb->query($q_media_photo_tag);
                                $wpdb->query($q_media_rating);
                                $wpdb->query($q_report_abuse);
                                $kaltura_result = $rt_kaltura_result = $kaltura_validation_data['client']->media->delete($rt_entry_list[$i]);
//                $result_add = intval($kaltura_result->executionTime)+ intval($result_add);
                            }
                        }
                        return true;
                    } catch (Exception $e) {
                        return false;
                    }
                }

                /**
                 * Algo.
                 * 1. Get all related data to existing entry_id's
                 * 2. Resubmit the data to kaltura using baseEntry->getByIds action pair
                 * 3. Get the new Entry_id's
                 * 4. Insert them into local database and assign them to new user
                 * 5. Stop
                 * @global <type> $bp
                 * @global <type> $wpdb
                 * @global <type> $kaltura_validation_data
                 * @param <type> $rt_entry_list : List of entry_id's
                 * @param <type> $user_id
                 *
                 * TODO : Getting the entry_data can be skiped here as we already have data in form.
                 *        This can be optimized and for now may give Timeout error
                 */
                function rt_reassign_media($rt_entry_list, $user_id) {
                    global $bp, $wpdb, $kaltura_validation_data;
                    $error = false;

                    $rt_entry_list_for_kaltura = '';
                    foreach ($rt_entry_list as $key) {
                        $rt_entry_list_for_kaltura = $rt_entry_list_for_kaltura . $key . ',';
                    }
                    try {
                        $entry_id_data = $kaltura_validation_data['client']->baseEntry->getByIds($rt_entry_list_for_kaltura);
                    } catch (Exception $e) {
                        echo "Server encountered error plz try later";
                    }
                    foreach ($entry_id_data as $key => $value) {

                        $dataUrl = $value->dataUrl;
                        try {
                            $new_kaltura_entry = $kaltura_validation_data['client']->media->addFromUrl($value, $dataUrl);
                        } catch (Exception $e) {
                            $error = true; //TODO this can be optiomized
//            echo 'Caught exception: ',  $e->getMessage(), "\n";
                            echo "Server encountered error plz try later";
                        }
                        if (!$error) {
                            //Media entry has been successfully updated on Kaltura server so update local database accordingly

                            $album_id = 1;
                            $rt_entry_group_id = 0;
                            $query = "INSERT INTO {$bp->media->table_media_data} (album_id, entry_id, user_id, media_type,group_id,date_uploaded)
            VALUES  (
                        '$album_id',
                        '$new_kaltura_entry->id',
                        '$user_id',
                        '$new_kaltura_entry->mediaType',
                        '$rt_entry_group_id',
                        '$new_kaltura_entry->createdAt'
                )";
                            $wpdb->query($query);
                        }
                    }
                }

                /**
                 * Featured Media table on Media Dashboard
                 * Photo : option varibale : rt_bp_add_to_photo_feature_list
                 * Video : option varibale : rt_bp_add_to_video_feature_list
                 *
                 */
                function rt_featured_photos_video_list() {
                    //Featured Photo list stored in : rt_bp_add_to_photo_feature_list
                    //Featured Video list stored in : rt_bp_add_to_video_feature_list
//                    $rt_featured_video_list = get_option('rt_bp_add_to_video_feature_list');

                    //Generates HTML for featured photo list
                    rt_get_featured_photo_html();
                    rt_get_featured_video_html();
                }

                function rt_get_featured_photo_html() {
                    global $bp, $wpdb;

                    //This entry id data must be sorted according to the stored sort order.
                    
                    $entry_id_data = rt_fetch_kaltura_data('photo');
                    $sorted_entry_id_data = rt_sort_this_stuff($entry_id_data,'photo');

                    $q = "select entry_id,user_id,media_type,id from {$bp->media->table_media_data}";
                    $result = $wpdb->get_results($wpdb->prepare($q));
?>
                    <h2> Featured Photo List </h2>
                    <p class="rt-featured-media-description">
                        Just Drag and Drop the row and put them any order you want. The same order will appear in Featured Photo Widget.
                    </p>
                        <?php
//                        foreach ($sorted_entry_id_data as $key => $value) {
//                            var_dump($key);
//                            echo " === ";
//                            var_dump($value->id);
//                            echo "<br />";
//                        }
                        ?>

                <div id="rt-contentRight-photo"></div>

		<div id="rt-contentLeft-photo">
			<ul>
                        <?php foreach ($sorted_entry_id_data as $key => $value) { ?>
                                <li id="recordsArray_<?php echo $value->id; ?>">
                                    <span><a href="<?php echo rt_get_media_info($result, $value->id, '1'); ?>"><img height= "30" width = "45px"  src= "<?php echo $value->thumbnailUrl . 'jpg' ?> " alt="hel"/></a></span>
                                    <span class="rt-media-dashboard-title"><?php echo $value->name; ?></span>
                                    <span class="rt-media-dashboard-user">: Added By : <?php echo rt_get_media_info($result, $value->id, '2'); ?></span>
                                </li>

                        <?php } ?>
			</ul>
		</div>


<!--                    <table class="widefat post fixed" cellspacing="0" id="contentLeft">
                        <thead>
                            <tr>
                                <th class="manage-column column-title">entry id</th>
                                <th class="manage-column column-title">Thumbnail</th>
                                <th class="manage-column column-title">Title</th>
                                <th class="manage-column column-title">Media Owner</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($sorted_entry_id_data as $key => $value) { ?>
                        <tr>
                            <td><?php echo $value->id; ?></td>
                            <td><a href="<?php echo rt_get_media_info($result, $value->id, '1'); ?>"><img height= "30" width = "45px"  src= "<?php echo $value->thumbnailUrl . 'jpg' ?> " alt="hel"/></a></td>
                            <td><?php echo $value->name; ?></td>
                            <td><?php echo rt_get_media_info($result, $value->id, '2'); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>

-->
                
<?php
                }



                function rt_get_featured_video_html() {
                    global $bp, $wpdb;

                    //This entry id data must be sorted according to the stored sort order.

                    $entry_id_data = rt_fetch_kaltura_data('video');

                    $sorted_entry_id_data = rt_sort_this_stuff($entry_id_data,'video');

                    

                    
                    $q = "select entry_id,user_id,media_type,id from {$bp->media->table_media_data}";
                    $result = $wpdb->get_results($wpdb->prepare($q));
?>
                    <h2> Featured Video List </h2>
                    <p class="rt-featured-media-description">
                        Just Drag and Drop the row and put them any order you want. The same order will appear in Featured Video Widget.
                    </p>

                        <?php
//                        foreach ($sorted_entry_id_data as $key => $value) {
//                            var_dump($key);
//                            echo " === ";
//                            var_dump($value->id);
//                            echo "<br />";
//                        }
                        ?>

                <div id="rt-contentRight-video"></div>

		<div id="rt-contentLeft-video">
			<ul>
                        <?php foreach ($sorted_entry_id_data as $key => $value) { ?>
                                <li id="recordsArray_<?php echo $value->id; ?>">
                                    <span><a href="<?php echo rt_get_media_info($result, $value->id, '1'); ?>"><img height= "30" width = "45px"  src= "<?php echo $value->thumbnailUrl . 'jpg' ?> " alt="<?php echo $value->title ?>"/></a></span>
                                    <span class="rt-media-dashboard-title"><?php echo $value->name; ?></span>
                                    <span class="rt-media-dashboard-user"> : Added By : <?php echo rt_get_media_info($result, $value->id, '2'); ?></span>
                                </li>

                        <?php } ?>
			</ul>
		</div>


<!--                    <table class="widefat post fixed" cellspacing="0" id="contentLeft">
                        <thead>
                            <tr>
                                <th class="manage-column column-title">entry id</th>
                                <th class="manage-column column-title">Thumbnail</th>
                                <th class="manage-column column-title">Title</th>
                                <th class="manage-column column-title">Media Owner</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($sorted_entry_id_data as $key => $value) { ?>
                        <tr>
                            <td><?php echo $value->id; ?></td>
                            <td><a href="<?php echo rt_get_media_info($result, $value->id, '1'); ?>"><img height= "30" width = "45px"  src= "<?php echo $value->thumbnailUrl . 'jpg' ?> " alt="hel"/></a></td>
                            <td><?php echo $value->name; ?></td>
                            <td><?php echo rt_get_media_info($result, $value->id, '2'); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>

-->

<?php
                }

                /**
                 *  When entry_id data is fetched from kaltura using baseEntry->getByIds, it sends data sorted by entry_id.
                 *  We want to sort the content as per the order stored in option table >> "rt_bp_add_to_photo_feature_list".
                 *
                 * @param <type> $entry_id_data : this is data for entry_is's from kaltura and we have to sort this and return
                 */
                function rt_sort_this_stuff($entry_id_data,$media) {
                    $sorted_entry_id_data_1 = $entry_id_data;

                    if($media == 'photo')
                        $rt_entry_id_list_sequence = get_option('rt_bp_add_to_photo_feature_list');
                    if($media == 'video')
                        $rt_entry_id_list_sequence = get_option('rt_bp_add_to_video_feature_list');

                    $sorted_entry_id_data = '';
                    $rt_cnt = 0;
                    foreach ($rt_entry_id_list_sequence as $key){
                        $rt_get_entry_id_object = rt_get_entry_id_object($key,$entry_id_data);
                        $sorted_entry_id_data[$rt_cnt] = $rt_get_entry_id_object;
                        $rt_cnt++;
                    }


                    return $sorted_entry_id_data;
                }

                function rt_get_entry_id_object($entry_id,$entry_id_data) {
                    foreach ($entry_id_data as $key =>$value){
                        if($entry_id == $value->id){
                            return $value;
                        }
                    }
                }
                function rt_fetch_kaltura_data($media) {
                    global $kaltura_validation_data;

                    $rt_entry_id_list = rt_convert_into_comma_seperated_value($media);

                    try {
                        $entry_id_data = $kaltura_validation_data['client']->baseEntry->getByIds($rt_entry_id_list);
                    } catch (Exception $e) {
                        echo "Server encountered error plz try later";
                    }
                    return $entry_id_data;
                }

                function rt_convert_into_comma_seperated_value($media) {
                    if($media == 'photo')
                        $rt_featured_media_list = get_option('rt_bp_add_to_photo_feature_list');
                    if($media == 'video')
                        $rt_featured_media_list = get_option('rt_bp_add_to_video_feature_list');

//                    var_dump($rt_featured_media_list);
                    $rt_entry_id_list = '';
                    foreach ($rt_featured_media_list as $key => $value) {
                        $rt_entry_id_list = $rt_entry_id_list . ',' . $value;
                    }
                    return $rt_entry_id_list;
                }
                
                function rt_updateRecordsListings_photo() {
                    $content = $_REQUEST['action'];
//                    var_dump($content);
                    $updateRecordsArray = $_REQUEST['new_sequence'];
                    $updateRecordsArray1 = $_REQUEST['recordsArray'];

                    $updateRecordsArray = '&' . $updateRecordsArray;
                    $test = explode("&recordsArray[]=", $updateRecordsArray);
                    array_shift($test);

                    delete_option('rt_bp_add_to_photo_feature_list');
                    update_option('rt_bp_add_to_photo_feature_list',$test);


                }
                
                add_action('wp_ajax_updateRecordsListings_photo', 'rt_updateRecordsListings_photo');


                function rt_updateRecordsListings_video() {
                    $content = $_REQUEST['action'];
                    $updateRecordsArray = $_REQUEST['new_sequence'];
                    $updateRecordsArray1 = $_REQUEST['recordsArray'];

                    $updateRecordsArray = '&' . $updateRecordsArray;
                    $test = explode("&recordsArray[]=", $updateRecordsArray);
                    array_shift($test);

                    delete_option('rt_bp_add_to_video_feature_list');
                    update_option('rt_bp_add_to_video_feature_list',$test);


                }

                add_action('wp_ajax_updateRecordsListings_video', 'rt_updateRecordsListings_video');

?>
