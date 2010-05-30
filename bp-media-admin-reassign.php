<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function rt_media_admin_page_reassign(){
     global $bp,$kaltura_validation_data,$wpdb,$kaltura_list,$kaltura_data;
    $pag_num = 6;

    if(isset($_REQUEST['assign_media_btn'])) {
        if($_REQUEST['rt-media-assign-action'] == -1 || $_REQUEST['user-id'] == -1)
             wp_redirect( admin_url('admin.php?page=bp-media-admin-reassign') );
        if($_REQUEST['rt-media-assign-action'] == 'reassign' && $_REQUEST['user-id'] ){
               $rt_newuserid =  $_REQUEST['user-id'];
               $rt_datapath = $_POST['assigncheck'];
//               $rt_mediatype = $_POST['rt_media_type'];


//               var_dump($rt_datapath,$rt_newuserid);
               

               $new_entry_id = reassign_media_owner($rt_datapath,$rt_newuserid);



        }





}

    $fpage = isset( $_REQUEST['fpage'] ) ? intval( $_REQUEST['fpage'] ) : 1;
    $owner = isset( $_REQUEST['user-to-reassign']) ? intval($_REQUEST['user-to-reassign']) : -1;
    $media_type_filter = isset( $_REQUEST['type-to-reassign']) ? intval($_REQUEST['type-to-reassign']) : -1;
    $time_filter = isset( $_REQUEST['filter-date']) ? ($_REQUEST['filter-date']) : -1;
   
//    var_dump($owner);
    
    if (isset ($_POST['user-to-reassign'])) {
        $fpage = 1;
    }

    $where = 'WHERE ';
    if ($owner != -1) {
        $where .= "md.user_id={$owner} AND ";
    }
    if ($media_type_filter != -1) {
        $where .= "md.media_type={$media_type_filter} AND ";
    }
    

    $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where}md.user_id = wu.id";
    $result = $wpdb->get_results($wpdb->prepare($q));

//    echo $wpdb->last_query;
    $cnt = count($result);


    $pagination = array(
      'base' => add_query_arg( array('fpage'=> '%#%', 'num' => $pag_num, 'user-to-reassign' => $owner, 'type-to-reassign' => $media_type_filter ) ), // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
      'format' => '', // ?page=%#% : %#% is replaced by the page number
      'total' => ceil( ($cnt) / $pag_num),
      'current' => $fpage,
      'prev_next' => true,
      'prev_text' => __('&laquo;'),
      'next_text' => __('&raquo;'),
      'type' => 'plain'

    );
    $rt_media_offset = ($fpage-1) * $pag_num;
//    $q .= " LIMIT {$rt_media_offset}, {$pag_num}";
//    echo $ql;
    $media_user_name = $wpdb->get_results($wpdb->prepare($q));
//    echo $wpdb->last_query;
//    var_dump($media_user_name);

    $kaltura_data = get_data_from_kaltura();//data fetched from kaltura
     $kaltura_list[0] = array();
    $j = 0;
    foreach ($kaltura_data[0] as $key => $value) {
    for($i=0;$i<$cnt;$i++) {
                if($value->id == $result[$i]->entry_id) {
                    $value->db_id = $result[$i]->id;
                    $value->localview = $result[$i]->views;
                    $value->local_tot_rank = $result[$i]->total_rating;
                    $value->display_name = $result[$i]->display_name;
                    $kaltura_list[0][$i] = $value;
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
                    $kaltura_list[0]['media_slug'] = $media_type;
                    $j++;
                }
            }
    }
     $kaltura_list[0]['count'] = $j++;
//  var_dump('-----------',$kaltura_list[0],'----------');

//var_dump($fpage,$pag_num);
$kaltura_list[0] = array_slice( (array)$kaltura_list[0], intval( ( $fpage - 1 ) * $pag_num), intval( $pag_num ) );

    $q_users =  "select DISTINCT(display_name), wu.id from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu WHERE md.user_id = wu.id";
    $res = $wpdb->get_results($wpdb->prepare($q_users));


?>

<div class="wrap">

    <h2>Media Reassign : Advanced</h2>
    <form id="media-reassign" action="" method="post">
        <div class="tablenav">
            <div class="alignleft actions">
                <select name="rt-media-assign-action">
                    <option value="-1" name="bulk-action" selected="selected"><?php _e('Bulk Actions'); ?></option>
                    <option name="bulk-reassign" value="reassign"><?php _e('Reassign Media'); ?></option>
                </select>
                <span><b>to</b></span>
                <select name="user-id">
                    <option value="-1" <?php if( ($newowner === -1) ) { echo 'SELECTED=selected';}?>><?php _e('Select User'); ?></option>
                        <?php
                        for($name =0 ; $name<count($res);$name++) {
                            ?>
                    <option value="<?php _e($res[$name]->id); ?>" <?php if($owner === intval($res[$name]->id) ) { echo 'SELECTED=selected';}?> ><?php _e($res[$name]->display_name)  ; ?></option>

                            <?php }?>
                </select>
                <input type="submit" value="<?php esc_attr_e('Reassign'); ?>" name="assign_media_btn" id="doaction" class="button-secondary action" />


                <select name="user-to-reassign">
                    <option value="-1" <?php if( ($owner === -1) ) { echo 'SELECTED=selected';}?>><?php _e('All Users'); ?></option>
                        <?php
                        for($name =0 ; $name<count($res);$name++) {
                            ?>
                    <option value="<?php _e($res[$name]->id); ?>" <?php if($owner === intval($res[$name]->id) ) { echo 'SELECTED=selected';}?> ><?php _e($res[$name]->display_name)  ; ?></option>

                            <?php }?>
                </select>

                <select name="type-to-reassign">
                    <option value="-1" <?php if($media_type_filter === -1) { echo 'SELECTED';}?>><?php _e('All Media'); ?></option>
                    <option value="2" <?php if($media_type_filter === 2) { echo 'SELECTED';}?>><?php _e('Photo'); ?></option>
                    <option value="1" <?php if($media_type_filter === 1) { echo 'SELECTED';}?>><?php _e('Video'); ?></option>
                    <option value="5" <?php if($media_type_filter === 5) { echo 'SELECTED';}?>><?php _e('Audio'); ?></option>
                </select>

                <input type="submit" id="post-query-submit" name ="rt-reassign-submit" value="<?php esc_attr_e('Filter'); ?>" class="button-secondary" />
                <?php echo paginate_links( $pagination ) ?>
            </div>
        </div>


        <table class="widefat post fixed" cellspacing="0">
            <thead>
                <tr>
                    <th scope="row" class="check-column"><input type="checkbox" name="aschk[]" value="a" /></th>
                    <th class="manage-column column-title">Thumbnail</th>
                    <th class="manage-column column-title">Media Owner</th>
                    <th class="manage-column column-title">Media Type</th>
                    <th class="manage-column column-title">Date of Creation</th>
                </tr>
            </thead>
            <tbody>

                    <?php
                     
                    $kaltura_cnt =count($kaltura_list[0]);
//                    echo $kaltura_cnt ;
//                    var_dump($kaltura_list[0]);

                    for($k=0;$k<$kaltura_cnt;$k++) {

                        switch($kaltura_list[0][$k]->mediaType) {
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
                        if(!empty($kaltura_list[0][$k]->id)){
                        echo '<tr>';
                        echo '<th scope="row" class="check-column"><input type="checkbox" name="assigncheck[]" value="'.$kaltura_list[0][$k]->dataUrl. ' '.$kaltura_list[0][$k]->mediaType.'" /></th>';
                        echo '<td class="column-title"><a href="'. $bp->root_domain.'/'.BP_MEDIA_SLUG.'/'.$media_type.'/'.$kaltura_list[0][$k]->db_id. '"><img height= "30" width = "45px"  src= "'.$kaltura_list[0][$k]->thumbnailUrl .'jpg"><p>'.$kaltura_list[0][$k]->name.'</p></a></td>';
                        echo '<td class="column-author">'.$kaltura_list[0][$k]->display_name.'</td>';
                        echo '<td class="column-categories">'.$media_type.'</td>';

                        echo '<td class="column-date">'.date( "F j, Y",$kaltura_list[0][$k]->createdAt).'</td>';
                        echo '</tr>';
                        }

                    }


                    ?>

            </tbody>
            <thead>
                <tr>
                    <th scope="row" class="check-column"><input type="checkbox" name="askchk[]" value="a" /></th>
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

function reassign_media_owner($rt_datapath,$rt_newuserid){
    global $bp,$kaltura_validation_data,$wpdb;
    $rt_olduserdata = $rt_datapath;
    $oldtype = $rt_media_type;
    $album_id = 1;
    $rt_entry_group_id =0;
//    var_dump($kaltura_validation_data);
    $t = "You can add Title Here";
    
    try{
        for($i =0;$i < count($rt_olduserdata);$i++ ){
            $path_type = split(' ',$rt_olduserdata[$i]);

        $kaltura_entry = new KalturaMediaEntry();
        $kaltura_entry->name = $t;
        $kaltura_entry->mediaType = intval($path_type[1]);
        $url = "$path_type[0]";
//            echo $kaltura_entry->name. ' ';
//            echo $kaltura_entry->mediaType. ' ';
//            echo $path_type[0]. '  <br>';
//            var_dump($kaltura_entry->mediaType,$path_type[0]);
        $newdata =  $kaltura_validation_data['client']->media->addFromUrl($kaltura_entry,$url);
//        var_dump($newdata);
        if(!empty($newdata)){
          $query = "INSERT INTO {$bp->media->table_media_data} (album_id, entry_id, user_id, media_type,group_id,date_uploaded)
            VALUES  (
                        '$album_id',
                        '$newdata->id',
                        '$rt_newuserid',
                        '$newdata->mediaType',
                        '$rt_entry_group_id',
                        '$newdata->createdAt'
                )";


        $wpdb->query($query);
        }
        }
        
        
    }
    catch(Exception $e){
        echo ' Oops Server Responded Unexpected';
    }


}
?>
