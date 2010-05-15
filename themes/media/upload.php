<?php
/**
 * @package Media_Component
 */
?>
<?php
global $bp, $bp_picture_updated, $srcurl,$message,$type,$kaltura_validation_data,$ks,$groups_template;
if(is_kaltura_configured()):
    /**
     * Album feature is only for media (not group)
     * So these options MUST be avail only while uploading media from media component and NOT from group
     */
    if($bp->current_component == BP_MEDIA_SLUG) {
        ?>

<!-- message div -->
<div class="rt-album-message updated fade " id="message">
</div>

<!-- Album div -->
<div id="rt-album-create">
    <table border="1">
        <tr>
            <!--<td><input type="radio" name="rt-album" value="rt-new" id="rt-new-album-radio" checked="checked"/>Create New Album</td>-->
            <td><input type="radio" name="rt-album" id="rt-album-choice-new" value="rt-new" checked="checked"/>Create New Album</td>
            <td><input type="radio" name="rt-album" value="rt-select-existing" id="rt-album-choice-existing"  />Select  Existing Album</td>
        </tr>
        <tr>
            <td>
                <form action="" method="post" id="rt-create-album">
                    Album Visibility : &nbsp;
                    <ul class="rt-visibility">
                        <li><input type="radio" checked="checked" name="rt-visibility" value="public" /> Public</li>
                        <li><input type="radio" name="rt-visibility" value="private" />Private</li>
                        <!--<li>Friends Only<input type="radio" checked="checked" value="yes" name="notifications[notification_activity_new_reply]"></li>-->
                    </ul>
                    <br/>
                    <input name="rt-album-name" type="text"  id="rt-new-album-name"/>

                    <!--
                        This go button is must because we can not dependent on flash uploder for album name validation.
                    -->
                    <a href="JavaScript:void(0)" class="button" id="rt-create-album-button"> Go </a>
                    <span id="rt-album-create-loader" class="ajax-loader"></span>
                </form>

            </td>
            <td>
                <!--<div class="rt-album-selection">-->
                <form method="post" action="" name="" id="rt-selected-album">
                    Add Media to existing Album : &nbsp;


                    <select name="rt-album-list">
                                <?php
                                global $bp,$wpdb;
                                $user_id = $bp->loggedin_user->id;
                                $query = "SELECT name FROM {$bp->media->table_media_album} WHERE user_id = $user_id";
                                $result = $wpdb->get_results($query);
//                            var_dump($result);
                                ?><option value="">Default Album</option><?php
                                foreach ($result as $key => $value) {
//                                var_dump($value->name);
//                                var_dump($key);
                                    ?>
                        <option value="<?php echo $key?>"><?php echo $value->name;?></option>
                                    <?php
                                }
                                ?>
                    </select>
                    <br />
                    <!-- Change Name of the selected Album
                    <input name="" type="text" value="from the selected option"/>-->
                    <br />
                    <!--<a href="" class="button"> Go </a>-->
                </form>
                <!--</div>-->

            </td>

        </tr>

    </table>

<!-- <input type="radio" checked="checked" value="yes" name="notifications[notification_activity_new_reply]"> -->

</div><!-- #rt-album-create ends-->
        <?php
    } //this makes sure that album options are only available for media and nowhere in other components
?>
<?php
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
//        var_dump($rt_first_album);
        echo "<ul id = 'rt-album-list'>";

        foreach ($result as $key => $value) {
            ?>
                <li><?php echo $value->name?></li>
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
                <li class="<?php echo $value->name;?>" <?php if($rt_first_album == $value->name) {echo "style = 'display:inline'";} else {echo "style = 'display:none'";}?>><?php echo $value->entry_id?></li>
            <?php
        }
        echo "</ul>";
?>
                <div class="clear"></div>
</div>
    <?php


//    var_dump($result);
}
    $flashVars = array();
    $flashVars["uid"]               = $kaltura_validation_data['partnerUserID'];
    $flashVars["partnerId"]         = $kaltura_validation_data['partner_id'];
    $flashVars["ks"]                = $kaltura_validation_data['ks'];
    $flashVars["afterAddEntry"]     = "onContributionWizardAfterAddEntry";
    $flashVars["close"]             = "onContributionWizardClose";
    $flashVars["showCloseButton"]   = false;
    $flashVars["Permissions"]       = 1;
    $flash_url = get_site_option('bp_rt_kaltura_url')."/kse/ui_conf_id/501";
    $flashVarsStr = "userId=1&sessionId=".$kaltura_validation_data['ks']."&partnerId=".$kaltura_validation_data['partner_id']."&subPartnerId=".$kaltura_validation_data['subpartner_id']."&kshowId=-1&afterAddentry=onContributionWizardAfterAddEntry&close=onContributionWizardClose&termsOfUse=http://corp.kaltura.com/static/tandc&showCloseButton=false";
    ?>
<div id="kaltura_contribution_wizard_wrapper"></div>
<script type="text/javascript">
    var cwWidth = 680;
    var cwHeight = 360;
    var cwSwf = new SWFObject("<?php echo $flash_url ?>", "kaltura_contribution_wizard_wrapper1", cwWidth, cwHeight, "9", "#000000");
    cwSwf.addParam("flashVars", "<?php echo $flashVarsStr; ?>");
    cwSwf.addParam("allowScriptAccess", "always");
    cwSwf.addParam("allowNetworking", "all");
    cwSwf.write("kaltura_contribution_wizard_wrapper");
</script>

<script type="text/javascript">
    function onContributionWizardAfterAddEntry(entries) {
        var rt_entry_id_list= '';
        var rt_entry_media_type = '';
        var rt_entry_group_id='';
        for(var i = 0; i < entries.length; i++) {
            rt_entry_id_list = rt_entry_id_list+ entries[i].entryId+',';
            rt_entry_media_type = rt_entry_media_type+ entries[i].mediaType+',';
        }


        //here check which radio button is selected.(new album or existing album)
        rt_album_type = jQuery("input[@name='rt-album']:checked").val();
        //        console.log(rt_album_type);
        if( rt_album_type == 'rt-new'){
            //creating new album
            album_name = jQuery('#rt-new-album-name').val();
            visibility = jQuery("input[@name='rt-visibility']:checked").val();
        }
        if(rt_album_type == 'rt-select-existing'){
            //select album from drop down
            //get the name of the album from drop down
            console.log('existing album name = ');
            album_name = jQuery("#rt-selected-album select[@name='rt-album-list'] :selected'").text();
            console.log(album_name);
            visibility = 0; //by default set validity to 0
        }

        var data = {
            action: 'media_upload',
            rt_entry_id_list:rt_entry_id_list,
            rt_entry_media_type:rt_entry_media_type,
            rt_entry_group_id:'<?php echo $groups_template->group->id;?>',
            album_name :album_name,
            visibility : visibility
        };
        jQuery.post(ajaxurl, data, function(response) {
                        console.log(response);
            //            var data = {
            //                action: 'create_new_album',
            //                new_album_name : new_album_name,
            //                visibility : visibility
            //            };
            //
            //            jQuery.post(ajaxurl, data, function(response) {
            //                jQuery('.rt-album-message p').text(response);
            //            });
        });
    }
</script>
<script type="text/javascript">
    function onContributionWizardClose() {
    }
</script>
<?php else :?>
<div id="message" class="info">
    <p>Kaltura is not configured. Please contact Admin</p>
</div>

<?php endif;?>
