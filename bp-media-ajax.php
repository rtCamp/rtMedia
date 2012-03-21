<?php
/**
 * @package Media_Component
 */
?>
<?php
function bp_media_ajax_querystring($query_string, $object, $filter, $scope, $page, $search_terms, $extras ) {
    global $bp;
    if ($object!='media') //return false;
        return apply_filters( 'bp_media_ajax_querystring', $query_string, $object, $filter, $scope, $page, $search_terms, $extras);

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
    else
        $_BP_COOKIE = &$_COOKIE;

    if ( !empty( $_BP_COOKIE['bp-' . $object . '-scope'] ) ) {
        //replace here allmedia by mediaall
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

    }
    if($_BP_COOKIE['bp-' . $object . '-filter'] == 'my-media-data') {
        $user_id = ( $bp->displayed_user->id ) ? $bp->displayed_user->id : $bp->loggedin_user->id;
        $new_qs[] = 'user_id=' . $user_id;
    }
    /* If page and search_terms have been passed via the AJAX post request, use those */
    if ( !empty( $_POST['page'] ) && '-1' != $_POST['page'] )
        $new_qs[] = 'page=' . $_POST['page'];
    $new_query_string = empty( $new_qs ) ? '' : join( '&', (array)$new_qs );
    bp_init_media();

    return apply_filters( 'bp_media_ajax_querystring', $new_query_string, $object, $filter, $scope, $page, $search_terms, $extras);
}
add_filter('bp_dtheme_ajax_querystring', 'bp_media_ajax_querystring',10,7);



function bp_media_object_template_loader() {

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


function media_upload() {

    global $current_user,$wpdb,$bp;


    echo '<br/>'.$rt_entry_id_list =  $_POST['rt_entry_id_list'];
    echo '<br/>'.$rt_entry_media_type =  $_POST['rt_entry_media_type'];

    $rt_entry_id_list_arr = explode(',',$rt_entry_id_list);
    $rt_entry_list_arr_length = count($rt_entry_id_list_arr);

    $rt_entry_media_type_list_arr = explode(',',$rt_entry_media_type);
    $rt_entry_media_type_list_arr_length = count($rt_entry_media_type_list_arr);


    $rt_entry_type_list_arr = explode(',',$rt_entry_type_list);
    $rt_entry_media_type_list_arr_len = explode(',',$rt_entry_type_list);


    for($i = 0;$i<$rt_entry_list_arr_length-1;$i++) {
        $query = "INSERT INTO {$bp->media->table_media_data} (entry_id,user_id,media_type) VALUES  ('$rt_entry_id_list_arr[$i]','$current_user->ID','$rt_entry_media_type_list_arr[$i]')";
        $wpdb->query($query);

    }
}
add_action('wp_ajax_media_upload','media_upload');
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

?>