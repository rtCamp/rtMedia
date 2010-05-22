<?php
/* 
 * SHOW ALBUMS
 * 
 */
//this makes sure that album options are only available for media and nowhere in other components
    if($bp->current_component == BP_MEDIA_SLUG) {
	?>

<div id="rt-album-wrapper">
	    <?php

	    $user_id = $bp->loggedin_user->id;
	    $album_table =$bp->media->table_media_album;
	    $data_table =$bp->media->table_media_data;


	    $query = "SELECT * FROM $album_table WHERE user_id = $user_id";
	    $result = $wpdb->get_results($query);
	    //showing albums list

	    //store any album name
	    $rt_first_album = $result[0]->name;

	    echo "<ul id = 'rt-album-list-ul'>";

	    foreach ($result as $key => $value) {
		?>
    <li>
		    <?php echo $value->name?>

    </li>
		<?php
	    }
	    echo "</ul>";//rt-album list ends



	    $query = "SELECT * FROM $data_table INNER JOIN $album_table
                    WHERE $data_table.album_id = $album_table.album_id AND $album_table.user_id = $user_id
		    ";
	    $result = $wpdb->get_results($query);

	    //show pictures from selected albums pictures
	    echo "<ul id='rt-pics-list'>";
	    foreach ($result as $key => $value) {
		?>
    <li class="<?php echo $value->name;?>" <?php if($rt_first_album == $value->name) {
			echo "style = 'display:inline'";
		    } else {
			echo "style = 'display:none'";
		    }?>>
		    <?php //echo $value->entry_id?>
		    <?php
		    try {
			$picture_data = $kaltura_validation_data['client']-> media -> get($value->entry_id);
			?><img src="<?php echo $picture_data->thumbnailUrl;?>" />
			<?php }
		    catch (Exception $e ) {
		echo 'Error Connecting to Media Server';
		    break;
		}
		?>
    </li>
	    <?php
	}
	echo "</ul>";
	?>
    <div class="clear"></div>
</div>

	<?php
}
