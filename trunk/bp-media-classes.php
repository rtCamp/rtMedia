<?php
/**
 * BP_Media classes
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

        $e_id = $wpdb->get_var($wpdb->prepare("SELECT * FROM {$bp->media->table_media_data} WHERE id = %d ", $this->id),1,0);
        $r_id = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bp->media->table_media_data} WHERE id = %d ", $this->id));

        //now im getting db_id which is database id and not the entry id
        //i have two options here.eiter call it from already fetched and stored global varible or again fetch it from kaltura


        try {
            if($kaltura_validation_data)
            $picture = $kaltura_validation_data['client']-> media -> get($e_id);
        }
        catch (Exception $e ) {
            $picture->id = -9999;
        }

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
        if ( !$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->media->table_media_data} WHERE id = %d", rt_ret_action_variable($bp->current_action) ) ) )
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
    function get_pictures_for_user( $user_id,$media_type,$view,$group_id,$album_id,$type,$page, $per_page) {
        global $bp, $wpdb,$test_data,$kaltura_validation_data;

//        var_dump(  $user_id,$media_type,$view,$group_id,$album_id,$type,$page, $per_page);




        if ( !$bp->media )
            bp_media_setup_globals();




        if ( !bp_is_home() ) {

            //this function should give the data uploaded by this user only.
            //Problem with this function
            //Here Kaltura "listAction" API function does not allow more that 30 entry_id's data to be fetched.
            //Therefore, When media count > 30 does not show anything
//            $rt_pictures = BP_Media_Picture::get_media_data_by_user($user_id,$media_type,$view,$group_id,$album_id,$type);

            /**
             *
             * Algo.
             * 1. Enlist all the comma seperated entry_id's into a varible.
             * 2. Fetch all the data for those Entry_id's from Kaltura using baseEntry->getByIds
             * 3. Apply all possible filter that are implemented in BP_Media_Picture::get_media_data_by_user
             */
            $rt_pictures = BP_Media_Picture::rt_get_media_data_by_user($user_id,$media_type,$view,$group_id,$album_id,$type,$page, $per_page);

        }
//        print_r($rt_pictures);
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
    function get_media_data_by_user($user_id,$media_type,$view,$group_id,$album_id,$type) {

        //following two functions get_media_data_by_user and get_media_data gives two level filtering of the content from kaltura
        //this function should give the data uploaded by this user only.
        global $wpdb,$bp, $list_entry_by_user;
        $list_entry_by_user = array();


        //album are not ralated to group so $where variable can be independent of each other
        if($group_id == 0) {
            if($album_id == 1) {
                $where = "";
            }
            else {
                $where = "WHERE album_id = $album_id";
                $where_1 = "AND album_id = $album_id";
            }
        }
        else {
            $where = "WHERE group_id = $group_id";
            $where_1 = "AND group_id = $group_id";
        }


        $list_all_elements = BP_Media_Picture::get_media_data($media_type,$view,$group_id,$album_id,$type); //data from kaltura user seperated
//        print_r($list_all_elements);

        $j=0;
        if($user_id == 0) {//this will give all data of all users
            $current_user_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->media->table_media_data} $where") );
        }
        else {//this will give data of the perticulat user
            $current_user_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->media->table_media_data} WHERE user_id= $user_id $where_1") );
        }


        //why reverse? to get recent list
        $list_all_elements['pictures']= array_reverse($list_all_elements['pictures']);


        foreach ($list_all_elements['pictures'] as $key => $value) {
            for($i=0;$i<count($current_user_data);$i++) {

                if($value->id == $current_user_data[$i]->entry_id) {

                    /**
                     * Insert visibility of the media. ITs in album table
                     */
                    $q = "SELECT visibility,user_id,album_id from {$bp->media->table_media_album} WHERE album_id = {$current_user_data[$i]->album_id}";
                    $result = $wpdb->get_row($q);

                    //if album is private and logged in user is not viewing then skip
                    if(($result->album_id == $current_user_data[$i]->album_id) && ($result->visibility == 'private') && !($result->user_id == $bp->loggedin_user->id))
                        continue;
                    $value->visibility = $result->visibility;
                    $value->db_id = $current_user_data[$i]->id;
                    $value->owner_id = $current_user_data[$i]->user_id;
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


        /*ashish code edits here for sorting and populalarity wise and rating
        * Here simple usort function at last of this page which actually sorts for specicifc requirements
        */

        switch($type) {

            case 'active':
                return $list_entry_by_user;
                break;
            case 'recent':
                return $list_entry_by_user;
                break;
            case 'popular':
                usort($list_entry_by_user['pictures'],'get_by_popularlity');
                return $list_entry_by_user;
                break;
            case 'rating':
                usort($list_entry_by_user['pictures'],'get_by_rating');
                return $list_entry_by_user;
                break;
            default :
                return $list_entry_by_user;
                break;
        }

    }


    /**
     *  This function will merge two functions get_media_data_by_user and get_media_data. "get_media_data" function is called inside "get_media_data_by_user"
     * @param <type> $user_id
     * @param <type> $media_type
     * @param <type> $view
     * @param <type> $group_id
     * @param <type> $album_id
     * @param <type> $type
     */

    /**
     *
     * Algo.
     * 1. Enlist all the comma seperated entry_id's into a varible.
     * 2. Fetch all the data for those Entry_id's from Kaltura using baseEntry->getByIds
     * 3. Apply all possible filter that are implemented in BP_Media_Picture::get_media_data_by_user
     */

    function rt_get_media_data_by_user($user_id,$media_type,$view,$group_id,$album_id,$type,$page, $per_page) {
        global $wpdb,$bp, $list_entry_by_user,$kaltura_validation_data;
//        var_dump($user_id,$media_type,$view,$group_id,$album_id,$type,$page, $per_page);
//		var_dump($kaltura_validation_data['client']);
//		echo "<br />";


        $list_entry_by_user = array();
        $media_type_clause = '';
        switch($media_type) {
            case 'mediall': default:
            //keep this emplty as we need all type of media
                $media_type_clause = "";
                break;
            case 'photo':
                $media_type_clause = "media_type = 2 ";
                break;
            case 'video':
                $media_type_clause = "media_type = 1 ";
                break;
            case 'audio':
                $media_type_clause = "media_type = 5 ";
                break;
        }
        //test code written by ashish for soring the media on basis of comment /recent/rating

        /*
switch($type){
            case 'top-rated-media-data' :
                $orderby = " ORDER BY total_rating DESC ";
            break;
            case 'popular':
                 $orderby = " ORDER BY views DESC ";
            break;
        case 'recent-media-data' :
                $orderby =" ORDER BY date_uploaded DESC ";
            break;
        case 'recent' :
                $orderby =" ORDER BY date_uploaded DESC ";
            break;
            case 'commented-media-data':// since we have to fetch data from two tables and have to form result accordingly this alertation is done where u find the commented data
                if ($media_type_clause != "") {
                    $media_type_clause_for_comment = "AND ".$media_type_clause;
                }

                $qry = "SELECT entry_id, item_id, COUNT( wpa.item_id ) as comment_count FROM {$bp->activity->table_name} wpa JOIN {$bp->media->table_media_data} wpm WHERE wpa.item_id = wpm.id AND wpa.component = 'media' {$media_type_clause_for_comment} GROUP BY wpa.item_id ";
                $comment_result = $wpdb->get_results( $wpdb->prepare($qry) );

                   $orderby = " ORDER BY views DESC ";
            break;
        default:
            $orderby ="";
        break;

        }
*/

//            var_dump('----------------',$orderby);
        //TODO : "WHERE" clause patchwork need to be fixed

        //album are not ralated to group so $where variable can be independent of each other
        $where = '';
        if ((!$group_id && ($album_id || ($media_type != 'mediaall' ))) || ($media_type != 'mediaall' ) || $user_id != 0) {
            $where = 'WHERE ';
        }
        if('mediaall' == $media_type && $group_id){
            $where = 'WHERE ';
        }
        $and = 'AND ';
        if (!$group_id) {
            if ($album_id) {
                $where .= "album_id={$album_id} ";
                if (($media_type != 'mediaall' )) {
                    $where .= $and;
                }
            }
            if($media_type != 'mediaall' )
                    $where .= $media_type_clause;

        }
        else {
            $where .= " group_id={$group_id} ";
            if($media_type != 'mediaall' )
                    $where .= $and.$media_type_clause;
        }
        if ($user_id != 0) {
            if ((!$group_id && ($album_id || ($media_type != 'mediaall' ))) || ($media_type != 'mediaall' ) || $group_id) {
                $where .= $and;
            }
            $where .= "user_id={$user_id}";
        }


//        if($group_id == 0) {
//            if($album_id == 1) {
//                $where = "";
//                if($media_type != 'mediaall' )
//                    $where = " WHERE $media_type_clause";
//            }
//            else {
//                $where = "WHERE album_id = $album_id";
//                $where_1 = "AND album_id = $album_id";
//                $where = $where . " AND " . $media_type_clause;
//                $where_1 = $where1 . " AND " . $media_type_clause;
//            }
//        }
//        else {
//            $where = "WHERE group_id = $group_id";
//            $where_1 = "AND group_id = $group_id";
//            $where = $where . " AND " . $media_type_clause;
//            $where_1 = $where1 . " AND " . $media_type_clause;
//        }




//        print_r($media_type_clause);
//        print_r("SELECT * FROM {$bp->media->table_media_data} $where $media_type_clause");
        $j=0;
//        if($user_id == 0) {//this will give all data of all users
//            $q = "SELECT * FROM {$bp->media->table_media_data} $where $orderby";
//            $current_user_data = $wpdb->get_results( $wpdb->prepare($q) );
//        }
//        else {//this will give data of the perticulat user
//               $q =  "SELECT * FROM {$bp->media->table_media_data} $where $orderby";
//            $current_user_data = $wpdb->get_results( $wpdb->prepare($q) );
//        }
//

        $q = "SELECT * FROM {$bp->media->table_media_data} $where $orderby";

        $current_user_data = $wpdb->get_results( $wpdb->prepare($q) );

        $total_entry_id = count($current_user_data);
//		echo "<br />";
//		var_dump($total_entry_id,$q);

/*         if($type == 'commented-media-data')//Refer line on 384 comment ....  in swith case for $type in this function itself */
/*         $total_entry_id = count($comment_result); */

//        print_r($user_id);
//        echo '=======';
//        print_r($current_user_data);

        //Pagination
//       		echo "<br />================================";
//		var_dump($per_page);
//
//
//       		echo "<br />================================";
//		var_dump($total_entry_id);
//
//
//       		echo "<br />================================";
//		var_dump($page);

        $pagination = array(
                'base' => add_query_arg( array('fpage'=> '%#%', 'num' => $per_page) ), // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
                'format' => '', // ?page=%#% : %#% is replaced by the page number
                'total' => ceil( ($total_entry_id) / $per_page),
                'current' => $page,
                'prev_next' => true,
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'type' => 'plain'
        );

        $rt_post_offset = ($page-1) * $per_page;
        $q = $q . "LIMIT {$rt_post_offset}, {$per_page}";

//		echo "<br />================================<br />";
//		var_dump($q);

/*
        if($type == 'commented-media-data')//code by ashish for most commented feature //Refer line on 384 comment ....  in swith case for $type in this function itself
            $q = $qry . "LIMIT {$rt_post_offset}, {$per_page}";

*/

        $result = $wpdb->get_results($wpdb->prepare($q));
/* 		echo "<br />"; */
/* 		var_dump($result); */
        //A comma seperated list of 'entry_id's


        $rt_entry_id_list = '';



/** This is commented because kaltura is not able to process long list of entry_ids. */
/*

        if($type == 'commented-media-data'){ //Refer line on 384 comment ....  in swith case for $type in this function itself
         foreach ($comment_result as $key=>$value) {
            $rt_entry_id_list = $rt_entry_id_list . "$value->entry_id" . ',';
        }
        }
        else{
            foreach ($current_user_data as $key=>$value) {
            $rt_entry_id_list = $rt_entry_id_list . "$value->entry_id" . ',';
        }
        }
*/
         foreach ($result as $key=>$value) {
            $rt_entry_id_list = $rt_entry_id_list . "$value->entry_id" . ',';
        }


//        var_dump($rt_entry_id_list);

/*         var_dump($rt_entry_id_list); */

/*         var_dump($kaltura_validation_data ); */

//        print_r($rt_entry_id_list);
        try{
            if($kaltura_validation_data)
            $rt_entry_id_data['pictures'] =  $kaltura_validation_data['client']->baseEntry->getByIds($rt_entry_id_list);
/*         $rt_entry_id_data['pictures'] =  $kaltura_validation_data['client']->baseEntry->getByIds('9xzv6o9asc'); */


        }
        catch (Exception $e){
            echo "Server encountered error plz try later.";
        }

//        $rt_entry_id_data['pictures'] = array_reverse($rt_entry_id_data['pictures']);
//        var_dump($rt_entry_id_data);
        $count = '';
        $t =0;
        if($rt_entry_id_data)
        foreach ($rt_entry_id_data['pictures'] as $key => $value) {
            $count++;
            $q = "SELECT * FROM {$bp->media->table_media_data} WHERE entry_id = '$value->id'";
            $this_entry_ids_data_from_local_db = $wpdb->get_row($q);
            $value->db_id = $this_entry_ids_data_from_local_db->id;
            $value->localview = $this_entry_ids_data_from_local_db->views;
            $value->local_tot_rank = $this_entry_ids_data_from_local_db->total_rating;
            $value->local_rating_ctr = $this_entry_ids_data_from_local_db->rating_counter;
            $value->date_uploaded = $this_entry_ids_data_from_local_db->date_uploaded;
            if($type == 'commented-media-data'){ //Refer line on 384 comment ....  in swith case for $type in this function itself
                $value->comment_count = $comment_result[$t]->comment_count;
                $t++;

            }

            $q = "SELECT visibility,user_id,album_id from {$bp->media->table_media_album} WHERE album_id = '{$this_entry_ids_data_from_local_db->album_id}'";
            $result = $wpdb->get_row($q);

            //if album is private and logged in user is not viewing then skip
            if(($result->album_id == $this_entry_ids_data_from_local_db->album_id) && ($result->visibility == 'private') && !($result->user_id == $bp->loggedin_user->id))
                continue;
            $value->visibility = $result->visibility;
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
            $rt_entry_id_data['media_slug'] = $media_type;
        }

               //trail code here since getbyids is not fetching data as we demand.. nither in asc or desc order-- dnt knw why is comment is :: ashish

        // sorting the fetched data as per requirement since kaltura is not fetching the data as we are sending the ids... s
        // so we have to sort it manually by custom usort function..[ashish :-)]
/*

        switch($type){
            case 'recent-media-data':
                usort($rt_entry_id_data['pictures'],'get_by_date');
            break;
        case 'recent':
                usort($rt_entry_id_data['pictures'],'get_by_date');
            break;
        case 'commented-media-data':
            usort($rt_entry_id_data['pictures'],'get_by_comment');
        break;

            case 'top-rated-media-data':
            usort($rt_entry_id_data['pictures'],'get_by_rating');
            break;

        case 'rating':
            usort($rt_entry_id_data['pictures'],'get_by_rating');
            break;

        case 'popular':
            usort($rt_entry_id_data['pictures'],'get_by_popularlity');
            break;
        default:
         break;

        }
*/
        $rt_entry_id_data['count'] = $total_entry_id;
//        var_dump($rt_entry_id_data);

        //trail code here since getbyids is not fetching data as we demand.. nither in asc or desc orf
        return $rt_entry_id_data;
    }


    //this function should give the data uploaded by kce
    function get_media_data($media_type,$view,$group_id,$album_id,$type) {

        global $bp,$wpdb;
        global $kaltura_validation_data;
        $filter = new KalturaMediaEntryFilter();
        $pager  = new KalturaFilterPager();

        var_dump($view);
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
        else {
            $where = "WHERE group_id = $group_id";
            $where_1 = "AND group_id = $group_id";

        }
        if($view == 'multiple') {

//            $picture_data12 = $kaltura_validation_data['client']->baseEntry->getByIds('ya57fd4xxr,jfffjas4js');
//            print_r($picture_data12);
            if($kaltura_validation_data)
            $picture_data = $kaltura_validation_data['client']-> media ->listAction($filter,$pager); // list all of the media items in the partnerID
//            print_r($picture_data);
            $total_picture_count = $picture_data -> totalCount;
            $temp = array( 'pictures' => $picture_data->objects, 'count' => $total_picture_count );

        }
        elseif($view == 'widget') {
            if($kaltura_validation_data)
            $picture_data = $kaltura_validation_data['client']-> media ->listAction($filter,$pager); // list all of the media items in the partnerID
            $total_picture_count = $picture_data -> totalCount;
            $temp = array( 'pictures' => $picture_data->objects, 'count' => $total_picture_count );
        }
        elseif ($view == 'single') {


            $current_item = $bp->action_variables[0];
            $query = "SELECT * FROM {$bp->media->table_media_data} where id = $current_item $where_1";

            $test = $wpdb->get_row($query);
            $k = $test->entry_id;
            try {
                if($kaltura_validation_data)
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

        $k = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT l.id FROM {$bp->media->table_media_data} AS l JOIN {$bp->activity->table_name} AS a ON l.id = a.item_id WHERE l.user_id = %d AND a.component = %s{$hidden_sql} ORDER BY a.date_recorded DESC LIMIT %d", $user_id, $bp->media->id, BP_MEDIA_PERSONAL_ACTIVITY_HISTORY ) );
//        var_dump($wpdb->last_query);
//        var_dump($k);
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
 * TODO Need to fix this function as The media loop should be used to access this (media-loop.php)
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
    $test->album_id;
    $q11 = "SELECT * FROM {$bp->media->table_media_album} WHERE album_id = '$test->album_id'";
    $result = $wpdb->get_row($q11);

    if(($result->visibility == 'private') && !($bp->loggedin_user->id == $result->user_id)) {
        return 0;
    }

    try {
        $picdata = $kaltura_validation_data['client']-> media -> get($k);
        return $test;
    }
    catch (Exception $e ) {
        $test->entry_id = -9999;
        return $test;
    }

}

//added by ashish sorting functions

function get_by_popularlity($a,$b) {
    if($a->localview == $b->localview) {
        return 0;
    }
    return ($a->localview > $b->localview) ? -1 :1;
}

function get_by_date($a,$b) {
    if($a->date_uploaded == $b->date_uploaded) {
        return 0;
    }
    return ($a->date_uploaded > $b->date_uploaded) ? -1 :1;
}

function get_by_rating($a,$b) {
    if($a->local_tot_rank == $b->local_tot_rank) {
        return 0;
    }
    return ($a->local_tot_rank > $b->local_tot_rank) ? -1 :1;
}

function get_by_comment($a,$b) {
    if($a->comment_count == $b->comment_count) {
        return 0;
    }
    return ($a->comment_count > $b->comment_count) ? -1 :1;
}


?>