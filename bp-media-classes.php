<?php
/**
 * BP_Links classes
 *
 * @package Media_Component
 * @author rtCamp
 */

/**
 * A media belonging to a member
 *
 * @package Media_Component
 * All classes for  Media is written here
 */

Class BP_Media_Picture {
    var $id;
    var $user_id;
    var $entry_id;
    var $picture_id;
    var $picture_ids;
    var $date_uploaded;
    var $title;
    var $description;
    var $status;
    var $enable_wire;
    var $pic_org_path;
    var $pic_org_path_act;
    var $pic_mid_path;
    var $pic_mid_path_act;
    var $pic_small_path;
    var $pic_small_path_act;
    var $tot_rating;
    var $rating_ctr;
    var $media_type;
    var $localview;
    var $localrank;

    function bp_media_picture( $id = null ) {
        global $bp, $wpdb;

        if ( !$user_id )
            $user_id = $bp->displayed_user->id;


        if ( $id ) {
            $this->id = $id;
            $this->populate();
        }
    }

    function populate() {
        global $wpdb, $bp,$kaltura_validation_data,$e_id;

        //change following query to get sinlge value so that i can get entry_id directly from db ashish
//  //      $entry_id = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->media->table_media_data} WHERE id = %d ", $this->id) );
        $e_id = $wpdb->get_var($wpdb->prepare("SELECT * FROM {$bp->media->table_media_data} WHERE id = %d ", $this->id),1,0);
        $r_id = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bp->media->table_media_data} WHERE id = %d ", $this->id));

        //now im getting db_id which is database id and not the entry id
        //i have two options here.eiter call it from already fetched and stored global varible or again fetch it from kaltura


        try {
            $picture = $kaltura_validation_data['client']-> media -> get($e_id);
        }
        catch (Exception $e ) {
            $picture->id = -9999;
        }



//        $picture = $kaltura_validation_data['client']-> media -> get($e_id);
//        $picture = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bp->media->table_media_data} WHERE id = %d", $this->id ) );

        $this->user_id = $bp->displayed_user->id;
        $this->picture_id = $picture->id;
//        $this->date_uploaded = strtotime($picture->date_uploaded);
        $this->title = stripslashes($picture->name);
        $this->description = stripslashes($picture->description);
        $this->status = $picture->status;
        $this->enable_wire = $picture->enable_wire;
        $this->pic_org_path = $picture->dataUrl;
        $this->pic_org_path_act = $picture->dataUrl;
        $this->pic_mid_path = $picture->dataUrl;
        $this->pic_mid_path_act = $picture->dataUrl;
        $this->pic_small_path = $picture->thumbnailUrl;
        $this->pic_small_path_act = $picture->thumbnailUrl;
        $this->tot_rating = $r_id[0]->total_rating;
        $this->rating_ctr = $r_id[0]->rating_counter;
        $this->media_type = $r_id[0]->media_type;

    }

    function save() {
        global $wpdb, $bp;

        // Don't try and save if there is no user ID.
        if ( !$this->user_id)
            return false;


        if ( $this->id ) {
            // Update
            $sql = $wpdb->query( $wpdb->prepare(
                    "UPDATE {$bp->media->table_media_data} SET
						user_id = %d,
                                                date_uploaded = FROM_UNIXTIME(%d),
                                                title = %s,
                                                description = %s,
                                                status = %s,
                                                enable_wire = %d,
                                                pic_org_path = %s,
                                                pic_org_path_act =%s,
                                                pic_mid_path = %s,
                                                pic_mid_path_act =%s,
                                                pic_small_path = %s,
                                                pic_small_path_act =%s
                                                                WHERE id = %d",
                    $this->user_id,
                    $this->date_uploaded,
                    $this->title,
                    $this->description,
                    $this->status,
                    $this->enable_wire,
                    $this->pic_org_path,
                    $this->pic_org_path_act,
                    $this->pic_mid_path,
                    $this->pic_mid_path_act,
                    $this->pic_small_path,
                    $this->pic_small_path_act
                    ) );
        } else {
            // Save
            $query = $wpdb->prepare(
                    "INSERT INTO {$bp->media->table_media_data} (
                                                entry_id,
                                            	user_id
					) VALUES (
						%s, %d
					)",
                    $this->entry_id,
                    $this->user_id
            );
            $sql = $wpdb->query( $query );
        }

        if (!$sql)
            return false;

        //do_action( 'bp_pictures_picture_after_save', $this );

        if ( $this->id )
            return $this->id;
        else
            return $wpdb->insert_id;


    }

    function delete() {

        global $wpdb, $bp;

        if ( function_exists('bp_wire_install') ) {
            BP_Wire_Post::delete_all_for_item( $this->id, $bp->media->table_name_wire );
        }
        if ( !$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->media->table_media_data} WHERE id = %d", $bp->action_variables[0] ) ) )
            return false;

        return true;
    }

    /*
 * Currently I an using this function
    */
    /**
     *
     * @global <type> $bp :The BP object
     * @global <type> $wpdb : The wordpress db object
     * @global <type> $test_data for testing purpose
     * @global <type> $kaltura_validation_data kaltura data
     * @param <type> $user_id current user id
     * @param <type> $media_type type of media
     * @param <type> $view single/multiple
     * @return Media
     */
    function get_pictures_for_user( $user_id,$media_type,$view,$group_id) {
        global $bp, $wpdb,$test_data,$kaltura_validation_data;

        if ( !$bp->media )
            bp_media_setup_globals();


        if($group_id == 0) {
            $where = "";
        }
        else {
            $where = "WHERE group_id = $group_id";
        }

        if ( !bp_is_home() ) {
            if(!$user_id) {

//                $qry = "SELECT * FROM {$bp->media->table_media_data} ORDER BY id DESC";
                $picture_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->media->table_media_data} $where ORDER BY id DESC") );
                $rt_pictures = BP_Media_Picture::get_media_data_by_user($user_id,$media_type,$view,$group_id);
            }
            else {

//                $qry = "SELECT * FROM {$bp->media->table_media_data} WHERE user_id = $user_id ORDER BY id DESC";
                $picture_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->media->table_media_data} $where  ORDER BY id DESC") );

                $rt_pictures = BP_Media_Picture::get_media_data_by_user($user_id,$media_type,$view,$group_id);
            }
        }
        else {
            if($user_id == 0) {
                $picture_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->media->table_media_data} $where  ORDER BY id DESC") );
            }
            else {
                $picture_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->media->table_media_data} $where ORDER BY id DESC") );
            }
            //this function should give the data uploaded by this user only.
            $rt_pictures = BP_Media_Picture::get_media_data_by_user($user_id,$media_type,$view,$group_id);
        }

        return $rt_pictures;
    }

    /**
     *
     * @global <type> $wpdb
     * @global <type> $bp
     * @global <type> $list_entry_by_user list of media by user
     * @param <type> $user_id
     * @param <type> $media_type
     * @param <type> $view
     * @return array $list_entry_by_user;
     */
    function get_media_data_by_user($user_id,$media_type,$view,$group_id) {

        //following two functions get_media_data_by_user and get_media_data gives two level filtering of the content from kaltura
        //this function should give the data uploaded by this user only.
        global $wpdb,$bp, $list_entry_by_user;
        $list_entry_by_user = array();

        if($group_id == 0) {
            $where = "";
        }
        else {
            $where = "WHERE group_id = $group_id";
            $where_1 = "AND group_id = $group_id";
        }

        $list_all_elements = BP_Media_Picture::get_media_data($media_type,$view,$group_id); //data from kaltura user seperated

        $j=0;
        if($user_id == 0) {
            $current_user_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->media->table_media_data} $where") );
        }
        else {
            $current_user_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->media->table_media_data} WHERE user_id= $user_id $where_1") );
        }

        foreach ($list_all_elements['pictures'] as $key => $value) {

            for($i=0;$i<count($current_user_data);$i++) {
                if($value->id == $current_user_data[$i]->entry_id) {
                    $value->db_id = $current_user_data[$i]->id;
                    $value->localview = $current_user_data[$i]->views;
                    $value->local_tot_rank = $current_user_data[$i]->total_rating;
                    $value->local_rating_ctr = $current_user_data[$i]->rating_counter;
                    $list_entry_by_user['pictures'][$i] = $value;
                    switch($value->mediaType) {
                        case '1':
                            $media_slug = 'video';
                            break;
                        case '2':
                            $media_slug = 'photo';
                            break;
                        case '5':
                            $media_slug = 'audio';
                            break;
                        default :
                            $media_slug = 'mediaall';
                    }
                    $list_entry_by_user['media_slug'] = $media_type;

                    $j++;
                }
            }
        }
        $list_entry_by_user['count'] = $j++;
        return $list_entry_by_user;
    }
    //this function should give the data uploaded by kce
    function get_media_data($media_type,$view,$group_id) {

        global $bp,$wpdb;
        global $kaltura_validation_data;
        $filter = new KalturaMediaEntryFilter();
        $pager  = new KalturaFilterPager();

        switch ($media_type) {
            case 'video':
                $type = VIDEO;
                $filter->mediaTypeEqual = KalturaMediaType::VIDEO;

                break;
            case 'audio':
                $type = AUDIO;
                $filter->mediaTypeEqual = KalturaMediaType:: AUDIO;

                break;
            case 'photo':
                $type = IMAGE;
                $filter->mediaTypeEqual = KalturaMediaType:: IMAGE;
                break;
            case 'mediaall':
            //there is no need of filter if all media is required
                $filter = null;
                break;

        }

        if($group_id == 0)
            $where = "";
        else{
            $where = "WHERE group_id = $group_id";
            $where_1 = "AND group_id = $group_id";

        }
        if($view == 'multiple') {
            $picture_data = $kaltura_validation_data['client']-> media ->listAction($filter,$pager); // list all of the media items in the partnerID
            $total_picture_count = $picture_data -> totalCount;
            $temp = array( 'pictures' => $picture_data->objects, 'count' => $total_picture_count );

        }
        elseif ($view == 'single') {

            $current_item = $bp->action_variables[0];
            $query = "SELECT * FROM {$bp->media->table_media_data} where id = $current_item $where_1";
            $test = $wpdb->get_row($query);
//            var_dump($test);
            $k = $test->entry_id;
            try {
                $picture_data = $kaltura_validation_data['client']-> media -> get($k);
                $total_picture_count = 1;
                $temp_array = array();
                $temp_array[0] = $picture_data;
                $temp = array( 'pictures' => $temp_array, 'count' => $total_picture_count );
            }
            catch (Exception $e ) {
                $test->entry_id = -9999;
            }
        }

        return $temp;
    }


    function total_picture_count( $user_id = null ) {
        global $bp, $wpdb;

        if ( !$bp->media )
            bp_media_setup_globals();

        if ( !$user_id )
            $user_id = $bp->displayed_user->id;

        // If the user is logged in return the picture count including their hidden pictures.
        if ( !bp_is_home() )
            return $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(id) FROM {$bp->media->table_media_data} WHERE user_id = %d", $user_id) );
        else
            return $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(id) FROM {$bp->media->table_media_data} WHERE user_id = %d", $user_id) );
    }

    function get_activity_recent_ids_for_user( $user_id, $show_hidden = false ) {
        global $wpdb, $bp;

        // Hide Hidden Items?
        if ( !$show_hidden )
            $hidden_sql = " AND a.hide_sitewide = 0";
//                        echo $bp->media->id;
        $k = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT l.id FROM {$bp->media->table_media_data} AS l JOIN {$bp->activity->table_name} AS a ON l.id = a.item_id WHERE l.user_id = %d AND a.component = %s{$hidden_sql} ORDER BY a.date_recorded DESC LIMIT %d", $user_id, $bp->media->id, BP_MEDIA_PERSONAL_ACTIVITY_HISTORY ) );
//                $k = $wpdb->get_col( $wpdb->prepare("SELECT * FROM `wp_bp_activity` WHERE component = 'media' AND item_id =%d ORDER BY date_recorded DESC LIMIT %d",$bp->action_variables[0], BP_MEDIA_PERSONAL_ACTIVITY_HISTORY ));
//                echo $wpdb->last_query;
        return $k;

    }

}

/**
 *
 * @global <type> $single_pic_template
 * @global <type> $wpdb
 * @global <type> $bp
 * @global <type> $kaltura_validation_data
 * @param <type> $previous
 * @return immediate next/previous data
 */
function get_adjacent_picture($previous = true) {

    global $single_pic_template, $wpdb, $bp,$kaltura_validation_data;
    $current_picture_id = $single_pic_template->id;
    $adjacent = $previous ? 'previous' : 'next';
    $op = $previous ? '>' : '<';
    $order = $previous ? 'ASC' : 'DESC';

    //as my-media-data filter is activated, where clause shud include filter on user_id

    if($bp->current_action == 'mediaall')
        $where = $wpdb->prepare("WHERE id $op %d ", $current_picture_id);
    else
        $where = $wpdb->prepare("WHERE id $op %d  and media_type=%d", $current_picture_id,$single_pic_template->media_type);

    $sort  = "ORDER BY id $order LIMIT 1";
    $query = "SELECT * FROM {$bp->media->table_media_data} $where $sort";
    $test = $wpdb->get_row($query);
    $k = $test->entry_id;

    try {
        $picdata = $kaltura_validation_data['client']-> media -> get($k);
        return $test;
    }
    catch (Exception $e ) {
        $test->entry_id = -9999;
        return $test;
    }

}

?>