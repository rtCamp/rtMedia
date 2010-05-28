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
    global $bp,$kaltura_validation_data,$wpdb,$kaltura_list,$kaltura_data;

     if(isset($_POST['rt-submit'])) {
//        var_dump($_POST['rt-submit']);


       $filter_date = $_POST['filter-date'];
       $filter_user = $_POST['filter-user'];
       $filter_type = $_POST['filter-type'];
       $pick_date   = $_POST['pick-date'];

        switch($filter_type){
            case 'video':
                $filter_type = 1;
                break;
            case 'audio':
                $filter_type = 5;
                break;
            case 'photo':
              $filter_type = 2;
                break;
        }

        //to find username
       if(!empty($filter_user) && $filter_user!= -1 && $filter_type == -1){

            echo 'Right user query';
            $where = "WHERE md.user_id={$filter_user} AND md.user_id = wu.id";
            $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where} AND md.user_id = wu.id";
        }

        if(!empty($filter_type) && $filter_type != -1 && $filter_user == -1 ){
            echo 'Right media query';
            $where = "WHERE md.media_type={$filter_type}";
            $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where} AND md.user_id = wu.id";
        }

        if(!empty($filter_type) && !empty($filter_user) && $filter_type != -1 && $filter_user != -1){
            echo 'Right user + media query';
            $where = "WHERE md.user_id={$filter_user} AND md.media_type={$filter_type} AND md.user_id = wu.id";
            $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where}";
        }
        // for no filtereing
      if(!empty($filter_type) && !empty($filter_user) && $filter_type == -1 && $filter_user == -1){
         $where_default = "WHERE md.user_id = wu.id";
         $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where_default}";
      }

}
    else{
     $where_default = "WHERE md.user_id = wu.id";
      $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where_default}";
    }


    $result = $wpdb->get_results($wpdb->prepare($q));


        $cnt = count($result);


    $kaltura_data = get_data_from_kaltura($entry_list);//data fetched from kaltura
//    $kaltura_data[0]= array_reverse($kaltura_data[0]);
//    var_dump($result);

//    var_dump($kaltura_data,$result);

    $j = 0;
    foreach ($kaltura_data[0] as $key => $value) {
//            var_dump($value);

              for($i=0;$i<$cnt;$i++) {

                if($value->id == $result[$i]->entry_id) {

                    $value->db_id = $result[$i]->id;
                    $value->localview = $result[$i]->views;
                    $value->local_tot_rank = $result[$i]->total_rating;
                    $value->local_rating_ctr = $result[$i]->rating_counter;
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
                }
            }
        }


// var_dump($kaltura_list[0]);
 $pag_num = 5;

if(isset($_REQUEST['fpage']))
    $fpage = $_REQUEST['fpage'];
    else
    $fpage = 1;

    $fpage = isset( $_GET['fpage'] ) ? intval( $_GET['fpage'] ) : $fpage;

    //trying implementing pagination

 if(isset($_POST['rt-submit'])) {
       $filter_date = $_POST['filter-date'];
       $pick_date   = $_POST['pick-date'];
//       var_dump($kaltura_data[0]);
 switch ($filter_date){
        case 'last-week';
          $kaltura_list[0] = get_last_week();
//          var_dump($kaltura_list[0]);

        break;
        case 'last-month';
             $kaltura_list[0] = get_last_month();
            echo 'ashish1';
        break;
        case '-1';
        break;
       }
//       var_dump($filter_date);

 }


$kaltura_list[0] = array_slice( (array)$kaltura_list[0], intval( ( $fpage - 1 ) * $pag_num), intval( $pag_num ) );


$pagination = array(
  'base' => add_query_arg( array('fpage'=> '%#%', 'num' => $pag_num ) ), // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
  'format' => '', // ?page=%#% : %#% is replaced by the page number
  'total' => ceil( ($cnt) / $pag_num),
  'current' => $fpage,
  'prev_next' => true,
  'prev_text' => __('&laquo;'),
  'next_text' => __('&raquo;'),
  'type' => 'plain'

);

    //fetching username of those who had uploaded the media
    $q1 = "select DISTINCT(display_name), wu.id from {$wpdb->users} wu JOIN {$bp->media->table_media_data} md WHERE wu.id = md.user_id";
    $media_user_name = $wpdb->get_results($wpdb->prepare($q1));
//    var_dump($media_user_name);

    ?>

<div class="wrap">

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
                <?php echo paginate_links( $pagination ) ?>
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


                    for($k=0;$k<count($kaltura_list[0]);$k++) {

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
                        echo '<td class="column-title"><img height= "30" width = "45px"  src= "'.$kaltura_list[0][$k]->thumbnailUrl .'jpg"><p>'.$kaltura_list[0][$k]->name.'</p></td>';
                        echo '<td class="column-author">'.$result[$k]->user_login.'</td>';
                        echo '<td class="column-categories">'.$media_type.'</td>';
                        echo '<td class="column-date">'.date( "F j, Y",$kaltura_list[0][$k]->createdAt).'</td>';
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
    $filter = new KalturaMediaEntryFilter();
    $pager  = new KalturaFilterPager();

    try {
        $data1 =  $kaltura_validation_data['client']->media->listAction($filter,$pager);
    }
    catch (Exception $e) {
        echo 'Oops Error while fetching data .. try Again ';
    }
    return array($data1->objects,$data1->totalCount);
}

function get_last_week() {

     global $bp,$kaltura_validation_data;
    ob_clean();

    $lastweek = getDate();
    $lastweek = $lastweek[0]-2*24*60*60;

    echo $lastweek;

    echo date('d/m/y',$lastweek);

    $filter = new KalturaMediaEntryFilter();
    $pager  = new KalturaFilterPager();
    $pager->pageSize=5;
    $pager->pageIndex=1;

    $filter->createdAtGreaterThanOrEqual = $lastweek;
    try {
        $dataforlastweek =  $kaltura_validation_data['client']->media->listAction($filter,$pager);
    }
    catch (Exception $e) {
        echo 'Oops Error while fetching data .. try Again ';
    }

//    var_dump('---------------------',$dataforlastweek);
 $kaltura_list[0] = $dataforlastweek;
 return $kaltura_list[0]->objects;

}

function get_last_month() {

     global $bp,$kaltura_validation_data;
    ob_clean();

    $lastweek = getDate();
    $lastweek = $lastweek[0]-2*24*60*60;

    echo $lastweek;

    echo date('d/m/y',$lastweek);

    $filter = new KalturaMediaEntryFilter();
    $pager  = new KalturaFilterPager();

    $pager->pageSize=5;
    $pager->pageIndex=1;
    $filter->createdAtGreaterThanOrEqual = $lastweek;
    try {
        $dataforlastweek =  $kaltura_validation_data['client']->media->listAction($filter,$pager);
    }
    catch (Exception $e) {
        echo 'Oops Error while fetching data .. try Again ';
    }

//    var_dump('---------------------',$dataforlastweek);
 $kaltura_list[0] = $dataforlastweek;
 return $kaltura_list[0]->objects;

}

?>

///////////////////////////////////////////////////////////////////////////////////////////


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




function rt_media_admin_page() {
    global $bp,$kaltura_validation_data,$wpdb,$kaltura_list,$kaltura_data;
    $pag_num = 5;

    if(isset($_POST['delete_media'])) {
        if($_POST['rt-media-action'] == -1)
            return;
        if($_POST['rt-media-action'] == 'delete' ){

               $rt_entry_list = $_POST['linkcheck'];
               $rt_delete_success = rt_entry_to_delete($rt_entry_list);

        }
}



    if(isset($_POST['rt-submit'])){
    if(isset($_POST['filter-user']) || isset($_POST['filter-type']) || ((isset($_POST['filter-user'])) && (isset($_POST['filter-type'])))) {
     $filter_date = $_POST['filter-date'];
     $filter_user = $_POST['filter-user'];
     $filter_type = $_POST['filter-type'];
       $pick_date   = $_POST['pick-date'];
    switch($filter_type){
        case 'video':
                $filter_type = 1;
                break;
        case 'audio':
                $filter_type = 5;
                break;
        case 'photo':
              $filter_type = 2;
                break;
        }

        //to find username
        if(!empty($filter_user) && $filter_user!= -1 && $filter_type == -1){

            echo 'Right user query';
            $where = "WHERE md.user_id={$filter_user} AND md.user_id = wu.id";
            $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where} AND md.user_id = wu.id";
        }

        if(!empty($filter_type) && $filter_type != -1 && $filter_user == -1 ){
            echo 'Right media query';
            $where = "WHERE md.media_type={$filter_type}";
            $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where} AND md.user_id = wu.id";
        }

        if(!empty($filter_type) && !empty($filter_user) && $filter_type != -1 && $filter_user != -1){
            echo 'Right user + media query';
            $where = "WHERE md.user_id={$filter_user} AND md.media_type={$filter_type} AND md.user_id = wu.id";
            $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where}";
        }
        // for no filtereing
        if(!empty($filter_type) && !empty($filter_user) && $filter_type == -1 && $filter_user == -1){
         $where_default = "WHERE md.user_id = wu.id";
         $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where_default}";
      }
  }

  if(isset($_POST['filter-date']) && $_POST['filter-date'] != -1) {
        $filter_date = $_POST['filter-date'];
        $pick_date   = $_POST['pick-date'];
        if($fpage<1)$fpage=1;
       $fpage = 1;
    switch ($filter_date){
        case 'last-week';
        echo 'i last week';
          $kaltura_data = get_last_week($page);
          $kaltura_list[0] =$kaltura_data[0];
          $pag_num = 3;
        break;
        case 'last-month';
          $kaltura_data = get_last_month($page);
          echo 'ashis1';
          $kaltura_list[0]= $kaltura_data[0];
          $pag_num = 5;
        break;
        case '-1';
        break;
       }
}



}

    else{
     echo 'when no opration is done';
     $where_default = "WHERE md.user_id = wu.id";
      $q =  "select * from {$bp->media->table_media_data} md JOIN {$wpdb->users} wu {$where_default} ";
    }

    $result = $wpdb->get_results($wpdb->prepare($q));

    echo $wpdb->last_query;
    $cnt = count($result);
    $kaltura_data = get_data_from_kaltura($entry_list);//data fetched from kaltura
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
                }
            }
    }
//         var_dump($kaltura_list[0]);
    //trying implementing filter date

// if(isset($_POST['filter-date']) && $_POST['filter-date'] != -1) {
//        $filter_date = $_POST['filter-date'];
//        $pick_date   = $_POST['pick-date'];
//        if($fpage<1)$fpage=1;
//       $fpage = 1;
//    switch ($filter_date){
//        case 'last-week';
//        echo 'i last week';
//          $kaltura_data = get_last_week($page);
//          $kaltura_list[0] =$kaltura_data[0];
//          $pag_num = 3;
//        break;
//        case 'last-month';
//          $kaltura_data = get_last_month($page);
//          echo 'ashis1';
//          $kaltura_list[0]= $kaltura_data[0];
//          $pag_num = 5;
//        break;
//        case '-1';
//        break;
//       }
//}
//write such condition that it fetches the desired pagination data as formed

if(isset($_REQUEST['fpage'])){
//        var_dump($filter_date,$filter_type,$filter_user,$temp);
    $fpage = $_REQUEST['fpage'];

}
    else
    $fpage = 1;
    $fpage = isset( $_GET['fpage'] ) ? intval( $_GET['fpage'] ) : $fpage;

//var_dump($fpage,$pag_num);
$kaltura_list[0] = array_slice( (array)$kaltura_list[0], intval( ( $fpage - 1 ) * $pag_num), intval( $pag_num ) );


$pagination = array(
  'base' => add_query_arg( array('fpage'=> '%#%', 'num' => $pag_num ) ), // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
  'format' => '', // ?page=%#% : %#% is replaced by the page number
  'total' => ceil( ($cnt) / $pag_num),
  'current' => $fpage,
  'prev_next' => true,
  'prev_text' => __('&laquo;'),
  'next_text' => __('&raquo;'),
  'type' => 'plain'

);

    //fetching username of those who had uploaded the media
    $q1 = "select DISTINCT(display_name), wu.id from {$wpdb->users} wu JOIN {$bp->media->table_media_data} md WHERE wu.id = md.user_id";
    $media_user_name = $wpdb->get_results($wpdb->prepare($q1));
?>

<div class="wrap">

    <h2>Media Adminstration : Advanced</h2>
    <form id="media-filter" action="" method="post">
        <div class="tablenav">
            <div class="alignleft actions">
                <select name="rt-media-action">
                    <option value="-1" name="bulk-action" selected="selected"><?php _e('Bulk Actions'); ?></option>
                    <option name="bulk-delete" value="delete"><?php _e('Delete Forever'); ?></option>
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
                <?php echo paginate_links( $pagination ) ?>
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


                    for($k=0;$k<count($kaltura_list[0]);$k++) {

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
                        echo '<th scope="row" class="check-column"><input type="checkbox" name="linkcheck[]" value="'.$kaltura_list[0][$k]->id.'" /></th>';
                        echo '<td class="column-title"><img height= "30" width = "45px"  src= "'.$kaltura_list[0][$k]->thumbnailUrl .'jpg"><p>'.$kaltura_list[0][$k]->name.'</p></td>';
                        echo '<td class="column-author">'.$kaltura_list[0][$k]->display_name.'</td>';
                        echo '<td class="column-categories">'.$media_type.'</td>';
                        echo '<td class="column-date">'.date( "F j, Y",$kaltura_list[0][$k]->createdAt).'</td>';
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
    $filter = new KalturaMediaEntryFilter();
    $pager  = new KalturaFilterPager();

    try {
        $data1 =  $kaltura_validation_data['client']->media->listAction($filter,$pager);
    }
    catch (Exception $e) {
        echo 'Oops Error while fetching data .. try Again ';
    }
    return array($data1->objects,$data1->totalCount);
}

function get_last_week($page) {

     global $bp,$kaltura_validation_data;
    ob_clean();

    $lastweek = getDate();
    $lastweek = $lastweek[0]-7*24*60*60;

    echo $lastweek;

    echo date('d/m/y',$lastweek);

    $filter = new KalturaMediaEntryFilter();
    $pager  = new KalturaFilterPager();
    $pager->pageSize=3;
    $pager->pageIndex=$page;
    $filter->createdAtGreaterThanOrEqual = $lastweek;
    try {
        $dataforlastweek =  $kaltura_validation_data['client']->media->listAction($filter,$pager);
    }
    catch (Exception $e) {
        echo 'Oops Error while fetching data .. try Again ';
    }

//    var_dump('---------------------',$dataforlastweek);
 $kaltura_list[0] = $dataforlastweek;
 return array($kaltura_list[0]->objects,$kaltura_list[0]->totalCount);

}

function get_last_month($page) {

     global $bp,$kaltura_validation_data;
    ob_clean();

    $lastmonth = getDate();
    $lastmonth = $lastmonth[0]-30*24*60*60;

    echo $lastmonth;

    echo date('d/m/y',$lastmonth);

    $filter = new KalturaMediaEntryFilter();
    $pager  = new KalturaFilterPager();

    $pager->pageSize=5;
    $pager->pageIndex=$page;
    $filter->createdAtGreaterThanOrEqual = $lastmonth;
    try {
        $dataforlastmonth =  $kaltura_validation_data['client']->media->listAction($filter,$pager);
    }
    catch (Exception $e) {
        echo 'Oops Error while fetching data .. try Again ';
    }

//    var_dump('---------------------',$dataforlastmonth);
 $kaltura_list[0] = $dataforlastmonth;
 return array($kaltura_list[0]->objects,$kaltura_list[0]->totalCount);

}

function rt_entry_to_delete($rt_entry_list){
    global $wpdb,$kaltura_validation_data,$bp;

        var_dump($bp);
    //Since $wpdb is not having access for the tables  here so this is written
        $bp->media->table_media_data = $wpdb->base_prefix . 'bp_media_data';
        $bp->media->photo_tag = $wpdb->base_prefix . 'bp_media_photo_tags'; //added by ashish
        $bp->media->table_report_abuse = $wpdb->base_prefix . 'bp_media_report_abuse'; //added by ashish
        $bp->media->table_user_rating_data = $wpdb->base_prefix . 'bp_media_user_rating_list';//added by ashish


    $media_user_name = $wpdb->get_results($wpdb->prepare($q1));
    try{
    for($i=0;$i<count($rt_entry_list);$i++){
        if(!($rt_entry_list[$i]=='a')){
        $q = "select id from {$bp->media->table_media_data} WHERE entry_id='{$rt_entry_list[$i]}' ";
        $media_id = $wpdb->get_var($wpdb->prepare($q));

        $q_media_data   = "DELETE from {$bp->media->table_media_data} WHERE entry_id='{$rt_entry_list[$i]}' ";
        $q_media_photo_tag    = "DELETE from {$bp->media->table_photo_tag} WHERE entry_id= {$rt_entry_list[$i]} ";
        $q_media_rating = "DELETE from {$bp->media->table_user_rating_data} WHERE image_id='{$media_id}' ";
        $q_report_abuse = "DELETE from {$bp->media->table_report_abuse} WHERE entry_id='{$rt_entry_list[$i]}' ";

        echo $q_report_abuse;
        echo $q_media_data;
        echo $q_media_photo_tag;
        echo $q_media_rating;

//        var_dump($media_id);
        }
//        $kaltura_validation_data['client']->media->delete($rt_entry_list[$i]);

    }
//    var_dump('-----------------------',$rt_entry_list,'---------------------');
    }
    catch(Exception $e){
        echo "Error deleting data";
    }

}

?>