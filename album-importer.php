<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

function rt_media_importer_page() {
    if(isset($_REQUEST['rt_importer_media_btn']) ) {
        if($_REQUEST['rt-media-importer-action'] == -1 ) {
            wp_redirect( admin_url('admin.php?page=bp-media-admin-importer') );
            echo '  <div class="updated fade" style="background-color: #FFF123;">Please select an action</div>';
        }

        if( $_REQUEST['rt-media-importer-action'] == 'importer'&& empty($_REQUEST['importcheck'])) {
            wp_redirect( admin_url('admin.php?page=bp-media-admin-importer') );
            echo '  <div class="updated fade" style="background-color: #FFF123;">Please select Picture to Import !!!</div>';
        }

        if($_REQUEST['rt-media-importer-action'] == 'importer' && isset($_REQUEST['importcheck']) ) {

            $rt_importdetails = $_REQUEST['importcheck'];

            $imported_pic_result = rt_import_album_data_to_mediabp($rt_importdetails);
            if(!empty($imported_pic_result)) {
                echo '  <div class="updated fade" style="background-color: #FFF123;">Data uploaded Successfully</div>';
            }
        }
    }
    global $bp,$wpdb,$kaltura_validation_data;
    $album_query = "SELECT * FROM {$wpdb->base_prefix}bp_album";
    $album_result = $wpdb->get_results($album_query);
    $fpage = isset($_GET['fpage']) ? $_GET['fpage'] : 1;
    $pag_num = 5;
    $cnt = count($album_result);
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
    $album_query .= " LIMIT {$rt_media_offset}, {$pag_num}";
    $album_result = $wpdb->get_results($album_query);
    $user_query  = "select  wu.id ,display_name from {$wpdb->users} wu JOIN {$wpdb->base_prefix}bp_album bpa WHERE wu.id = bpa.owner_id ";
    $user_result = $wpdb->get_results($user_query);
    $imported_pic_ids = get_option('rt_bp_media_imported_media');
    $imported_pic_ids = explode(',', $imported_pic_ids);
    ?>
<div class="wrap">

    <h2>Media Importer :</h2>

    <form id="media-importer" action="" method="post">
        <div class="tablenav">
            <div class="alignleft actions">
                <select name="rt-media-importer-action">
                    <option value="-1" name="bulk-action" selected="selected"><?php _e('Select Action'); ?></option>
                    <option name="bulk-import" value="importer"><?php _e('Import Media'); ?></option>
                </select>
                <span><b>to</b></span>

                <input type="submit" value="<?php esc_attr_e('Import'); ?>" name="rt_importer_media_btn" id="rt_import_doaction" class="button-secondary action" />


                    <?php echo paginate_links( $pagination ) ?>
            </div>
        </div>


        <table class="widefat post fixed" cellspacing="0">
            <thead>
                <tr>
                    <th scope="row" class="check-column"><input type="checkbox" name="imchk[]" value="a" /></th>
                    <th class="manage-column column-title">Thumbnail</th>
                    <th class="manage-column column-title">Media Details</th>
                    <th class="manage-column column-title">Date of Creation</th>
                </tr>
            </thead>
            <tbody>

                    <?php
//                     for($i =0 ; $i<count($album_result);$i++){
//                         if($album_result[$i]->description=='')
//                                 $album_result[$i]->description="No Description";
                    $i = 0;
                    foreach ($album_result as $album_media) {
                        if (!in_array($album_media->id, $imported_pic_ids)) {
                            echo '<tr>';
                            echo '<th scope="row" class="check-column"><input type="checkbox" name="importcheck[]" value="'.$album_media->owner_id.'#'.$album_media->title.'# '. $album_media->description.'#'.$album_media->pic_org_url.'#'.strtotime($album_media->date_uploaded).'#'.$album_media->id .'" /></th>';
                            echo '<td class="column-title"><img height= "30" width = "45px"  src= "'.$album_media->pic_org_url .'"><p>'.$user_result[$i]->display_name.'</p></a></td>';
                            echo '<td class="column-author"><p><u>'.$album_media->title.'</u></p><p>'.trim(substr($album_media->description,0,10)).'...</p></td>';
                            echo '<td class="column-date">'.date($album_media->date_uploaded).'</td>';
                            echo '</tr>';
                            $i++;
                        }
                    }
                    ?>

            </tbody>
            <thead>

                <tr>
                    <th scope="row" class="check-column"><input type="checkbox" name="imchk[]" value="a" /></th>
                    <th class="manage-column column-title">Thumbnail</th>
                    <th class="manage-column column-title">Media Details</th>

                    <th class="manage-column column-title">Date of Creation</th>
                </tr>
            </thead>

        </table>


    </form>
        
</div>
<?php
}

//function to import data from album to kaltura server and our media data table
function rt_import_album_data_to_mediabp($rt_importdetails) {
    global $kaltura_validation_data,$bp,$wpdb;
    $site_url = get_option('siteurl');
    $split_data = split('#', $rt_importdetails);

    try {
        $imported_pic_ids = get_option('rt_bp_media_imported_media');
        $to_be_imported_pic_ids = array();
        for($i = 0 ;$i<count($rt_importdetails);$i++) {
            //1. add to kaltura server
            //2. insert the album/title/description
            //3. add title and description accordingly
            //4. add to media data table accordingly
            //5. use serialied data
            $split_data = split('#', $rt_importdetails[$i]);
            $user_id =  $split_data[0];
            $pic_title = $split_data[1];
            $pic_desc = $split_data[2];
            $pic_url = $site_url.$split_data[3];
            $pic_date = $split_data[4];
            $pic_id = $split_data[5];

            // form data as per requirement
            $entry = new KalturaMediaEntry();
            $entry->name = $pic_title;
            if($pic_desc == ' ' || $pic_desc == '')
                $entry->description = "";
            else
                $entry->description = $pic_desc;
            $entry->name = $pic_title;
            $entry->mediaType = 2;
            $entry->createdAt = $pic_date;


            $kaltura_data = $kaltura_validation_data['client']-> media ->addFromUrl($entry, $pic_url);

            if(!empty($kaltura_data)) {
                $media_type = 2;
                $total_rating = 1;
                $rating_counter = 1;
                $rating = 1;
                $views = 1;
                $group_id = 0;
                $album_id  = 1;
                $service_type = 'kaltura';

                $query = "INSERT INTO {$bp->media->table_media_data} (entry_id, user_id, service_type, media_type, total_rating, rating_counter, rating, views, group_id, album_id, date_uploaded)
            VALUES  (
                    '$kaltura_data->id',
                    '$user_id',
                    '$service_type',
                    '$media_type',
                    '$total_rating',
                    '$rating_counter',
                    '$rating',
                    '$views',
                    '$group_id',
                    '$album_id',
                    '$pic_date'
                )";


                if ($wpdb->query($query)) {
                    $to_be_imported_pic_ids[] = $pic_id;
                }

            }

        }
        $to_be_imported_pic_ids = implode(',', $to_be_imported_pic_ids);
        if (!empty($imported_pic_ids)) {
            $imported_pic_ids .= ','.$to_be_imported_pic_ids;
        }
        else {
            $imported_pic_ids = $to_be_imported_pic_ids;
        }
        update_option('rt_bp_media_imported_media', $imported_pic_ids);
    }
    catch(Exception $e) {
        echo $e. 'Oops Kaltura Server responded badly';
    }



    return $kaltura_data;

}
?>