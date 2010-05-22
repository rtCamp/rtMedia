<?php
/**
 * @package Media_Component
 */
?>
<?php
function bp_media_ajax_querystring($query_string, $object, $filter, $scope, $page, $search_terms, $extras ) {
//    var_dump('-=0-00000----',$query_string,'-=0-00000----');
    global $bp;
    if ($object!='media') //return false;
        return apply_filters( 'bp_media_ajax_querystring', $query_string, $object, $filter, $scope, $page, $search_terms, $extras);

//    var_dump($query_string, $object, $filter, $scope, $page, $search_terms, $extras);
    /* Set up the cookies passed on this AJAX request. Store a local var to avoid conflicts */
    if ( !empty( $_POST['cookie'] ) )
        $_BP_COOKIE = wp_parse_args( str_replace( '; ', '&', urldecode( $_POST['cookie'] ) ) );
    else
        $_BP_COOKIE = &$_COOKIE;


    if ( !empty( $_BP_COOKIE['bp-' . $object . '-filter'] ) && '-1' != $_BP_COOKIE['bp-' . $object . '-filter'] ) {
        $new_qs[] = 'type=' . $_BP_COOKIE['bp-' . $object . '-filter'];
        $new_qs[] = 'action=' . $_BP_COOKIE['bp-' . $object . '-filter']; // Activity stream filtering on action
//        $new_qs[] = 'user_id=' . $user_id;

    }



    if ( !empty( $_POST['cookie'] ) )
        $_BP_COOKIE = wp_parse_args( str_replace( '; ', '&', urldecode( $_POST['cookie'] ) ) );
    else{
        $_BP_COOKIE = &$_COOKIE;
     if('photo'== $bp->current_action && media == $bp->current_component ){
            $new_qs[] = 'scope=photo';
        }
     if('video' == $bp->current_action && media == $bp->current_component ){
            $new_qs[] = 'scope=video';
        }

    }
   
    if ( !empty( $_BP_COOKIE['bp-' . $object . '-scope'] ) ) {
        //replace here allmedia by mediaall

        /** ashish code : here i have checked the curreent action  and as per current action the Query
         * string is formed via conditions
        */
        if('photo'== $bp->current_action && media == $bp->current_component ){
            $new_qs[] = 'scope=photo';
        }elseif('video' == $bp->current_action && media == $bp->current_component ){
            $new_qs[] = 'scope=video';
        }else{

        if ( 'allmedia' != $_BP_COOKIE['bp-' . $object . '-scope'] && empty( $bp->displayed_user->id ) && !$bp->is_single_item )
            $new_qs[] = 'scope=' . $_BP_COOKIE['bp-' . $object . '-scope']; // Activity stream scope only on activity directory.
        if ( 'photo' == $_BP_COOKIE['bp-' . $object . '-scope'] ) {
//            $user_id = ( $bp->displayed_user->id ) ? $bp->displayed_user->id : $bp->loggedin_user->id;
//            $new_qs[] = 'user_id=' . $user_id;
        }
        if ( 'video' == $_BP_COOKIE['bp-' . $object . '-scope'] ) {
//            $user_id = ( $bp->displayed_user->id ) ? $bp->displayed_user->id : $bp->loggedin_user->id;
//            $new_qs[] = 'user_id=' . $user_id;
        }

        if ( 'audio' == $_BP_COOKIE['bp-' . $object . '-scope'] ) {
//            $user_id = ( $bp->displayed_user->id ) ? $bp->displayed_user->id : $bp->loggedin_user->id;
//            $new_qs[] = 'user_id=' . $user_id;
        }

        if ( 'upload' == $_BP_COOKIE['bp-' . $object . '-scope'] ) {
//            $user_id = ( $bp->displayed_user->id ) ? $bp->displayed_user->id : $bp->loggedin_user->id;
//            $new_qs[] = 'user_id=' . $user_id;
            }
        }//End of else-- end ashish code
    }
    if($_BP_COOKIE['bp-' . $object . '-filter'] == 'my-media-data') {
        $user_id = ( $bp->displayed_user->id ) ? $bp->displayed_user->id : $bp->loggedin_user->id;
        $new_qs[] = 'user_id=' . $user_id;
    }

    //media album filter : kapil
    $filter_string = $_BP_COOKIE['bp-' . $object . '-filter'];
    $rt_filter = explode('_', $filter_string);
    if($rt_filter[0] == 'rt-album-filter') {
        $album_id = $rt_filter[1];
        $new_qs[] = 'album_id=' . $album_id;
    }
    

// fnc crap!
//    rt_get_album_filter($_BP_COOKIE['bp-' . $object . '-filter']);
    /* If page and search_terms have been passed via the AJAX post request, use those */
    if ( !empty( $_POST['page'] ) && '-1' != $_POST['page'] )
        $new_qs[] = 'page=' . $_POST['page'];
    $new_query_string = empty( $new_qs ) ? '' : join( '&', (array)$new_qs );
    bp_init_media();
//var_dump('----',$new_query_string,'----');
    return apply_filters( 'bp_media_ajax_querystring', $new_query_string, $object, $filter, $scope, $page, $search_terms, $extras);
}
add_filter('bp_dtheme_ajax_querystring', 'bp_media_ajax_querystring',1,7);



function bp_media_object_template_loader() {
//    var_dump($_POST['filter']);
    if($_POST['scope'] == 'upload') {
        if(is_kaltura_configured()):

            bp_media_locate_template( array( 'media/upload.php' ), true );
        else:
            ?>
<div id="message" class="info">
    <p>Kaltura is not configured. Please contact Admin</p>
</div>
        <?php endif;?>

        <?php
    }
    else {
        ?>
<script>
    jQuery('.star').rating();
    jQuery('.star').rating('readOnly');
</script>
        <?php
        $object = esc_attr( $_POST['object'] );
        if(is_kaltura_configured()):
            bp_media_locate_template( array( "$object/$object-loop.php" ), true );
        else:
            ?>
<div id="message" class="info">
    <p>Kaltura is not configured. Please contact Admin</p>
</div>
        <?php endif;?>

        <?php
    }
}
add_action( 'wp_ajax_media_filter', 'bp_media_object_template_loader' );


function rt_media_upload() {

    global $current_user,$wpdb,$bp;

    $user_id = $bp->loggedin_user->id;
    $rt_entry_id_list       =  $_POST['rt_entry_id_list'];
    $rt_entry_media_type    =  $_POST['rt_entry_media_type'];
    $rt_entry_group_id      =  $_POST['rt_entry_group_id'];

    echo $album_name = $_POST['album_name'];
    $visibility = $_POST['visibility'];

    switch($visibility) {
        case 'public': default:
            $visibility = 1;
            break;
        case 'private':
            $visibility = 0;
            break;
    }
    //check for the new name availibility for the same user.
    //if the name is available in the database then, insert the records with the album id
    $user_id = $bp->loggedin_user->id;

    $album_name = trim($album_name);
    if($album_name == 'Default')
        $query = "SELECT name,album_id,visibility FROM {$bp->media->table_media_album} WHERE name='$album_name'";
    else
        $query = "SELECT name,album_id,visibility FROM {$bp->media->table_media_album} WHERE name='$album_name' AND user_id = $user_id";

    $result = $wpdb->get_row($query);

    
    if($result == NULL) {//no album found of this name so insert the all these media into default album
        $album_id = 1; //album_id = 0 means its a default album and every user has his default album
        $visibility = 1; //visibility = 1 => public by default; 0 => Private
    }
    else {
        $album_id = $result->album_id;
        $visibility = $result->visibility;
    }

    if($rt_entry_group_id == '' || $rt_entry_group_id == NULL)
        $rt_entry_group_id = 0;

    $rt_entry_id_list_arr = explode(',',$rt_entry_id_list);
    $rt_entry_list_arr_length = count($rt_entry_id_list_arr);

    $rt_entry_media_type_list_arr = explode(',',$rt_entry_media_type);
    $rt_entry_media_type_list_arr_length = count($rt_entry_media_type_list_arr);


    $rt_entry_type_list_arr = explode(',',$rt_entry_type_list);
    $rt_entry_media_type_list_arr_len = explode(',',$rt_entry_type_list);


    for($i = 0;$i<$rt_entry_list_arr_length-1;$i++) {

        $query = "INSERT INTO {$bp->media->table_media_data} (album_id, entry_id, user_id, media_type,group_id)
            VALUES  (
                        '$album_id',
                        '$rt_entry_id_list_arr[$i]',
                        '$current_user->ID',
                        '$rt_entry_media_type_list_arr[$i]',
                        '$rt_entry_group_id'
                )";


        $wpdb->query($query);
    }

}

add_action('wp_ajax_media_upload','rt_media_upload');
function bp_init_media() {
    ?>
<script>
    var objects = [ 'media'];
    var j = jQuery;
    j(objects).each( function(i) {
        if ( null != j.cookie('bp-' + objects[i] + '-filter') && j('li#' + objects[i] + '-order-select select').length )
            j('li#' + objects[i] + '-order-select select option[value=' + j.cookie('bp-' + objects[i] + '-filter') + ']').attr( 'selected', 'selected' );

        if ( null != j.cookie('bp-' + objects[i] + '-scope') && j('div.' + objects[i]).length ) {
            j('div.item-list-tabs li').each( function() {
                j(this).removeClass('selected');
            });
            j('div.item-list-tabs li#' + objects[i] + '-' + j.cookie('bp-' + objects[i] + '-scope') + ', div.item-list-tabs#object-nav li.current').addClass('selected');
        }
    });

</script>
    <?php

}

function rt_create_new_album() {
    global $wpdb,$bp;
    $new_album_name = $_POST['new_album_name'];
    $visibility = $_POST['visibility'];
    $user_id = $bp->loggedin_user->id;
    //check for duplicate album name
//    if found existing name then return
    $query = "SELECT name FROM {$bp->media->table_media_album} WHERE name = '$new_album_name' AND user_id = $user_id";
    $result = $wpdb->get_col($query);
//    print_r($result);
    if($result == NULL) {//no duplicate found so insert the album name
        $query_1 = "INSERT INTO {$bp->media->table_media_album}( user_id,visibility, name ,last_updated , category)
                VALUES  (
                            '$user_id',
                            '$visibility',
                            '$new_album_name',
                            now(),
                            ''
                )";
        $wpdb->query($query_1);
        $album_id = mysql_insert_id();

        echo "Album '" . $_POST['new_album_name'] ."' created successfully. Now you can Upload Media";
    }
    else {
        echo 'nops@#@Album name already in use. Please try different name';
    }
//    die();
}

add_action('wp_ajax_create_new_album','rt_create_new_album');

/**
 * Fetching thumbs for selected album from kaltura Server
 */
function rt_fetch_images_for_album() {

    global $wpdb,$bp,$kaltura_validation_data;
    $album_name = $_POST['album_name'];
    $album_name = trim($album_name);
    $album_table =$bp->media->table_media_album;
    $data_table =$bp->media->table_media_data;
    $user_id = $bp->loggedin_user->id;
    $query = "SELECT $data_table.entry_id FROM $album_table INNER JOIN $data_table WHERE $album_table.user_id = '$user_id' AND $album_table.album_id = $data_table.album_id AND $album_table.name = '$album_name'";
    $result = $wpdb->get_results($query);
//    var_dump($result);
    // Got the entry id's now fetch the thumbs from kaltura
    foreach ($result as $key => $value) {
        ?>
            <li>
        <?php
                try {
                    $picture_data = $kaltura_validation_data['client']-> media -> get($value->entry_id);
                     ?><img src="<?php echo $picture_data->thumbnailUrl;?>" />
                <?php }
                catch (Exception $e ) {
//                    $test->entry_id = -9999;
                        echo 'Error Connecting to Media Server';
                        break;
                }
                ?>

            </li>
        <?php
    }
}
add_action('wp_ajax_rt_fetch_images_for_album','rt_fetch_images_for_album');
?>