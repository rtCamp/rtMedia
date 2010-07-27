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
    <p><input type="radio" name="rt-album-choice" id="rt-album-choice-new" value="rt-new"/>Create New Album</p>
	    <form action="" method="post" id="rt-create-album">
			<input name="rt-album-name" type="text"  id="rt-new-album-name" size="25"/>
			<a href="JavaScript:void(0)" class="button" id="rt-create-album-button"> Go </a>
			<br/>
			<label>Keep this album:
			    <ul class="rt-visibility">
				<li><input type="radio" checked="checked" name="rt-visibility" value="public" /> Public</li>
                                <li><input type="radio" name="rt-visibility" value="private" disabled="true" title="Private Facility coming soon"/>Private</li>
				<!--<li>Friends Only<input type="radio" checked="checked" value="yes" name="notifications[notification_activity_new_reply]"></li>-->
			    </ul>
			</label>
			<!--
			    This go button is must because we can not dependent on flash uploder for album name validation.
			-->
			<span id="rt-album-create-loader" class="ajax-loader"></span>
	    </form>
    <p><input type="radio" name="rt-album-choice" value="rt-select-existing" id="rt-album-choice-existing"  />Select  Existing Album</p>
	<!--<div class="rt-album-selection">-->
	<form method="post" action="" name="" id="rt-selected-album">
	    Upload to : &nbsp;


	    <select name="rt-album-list">
			<?php
			global $bp,$wpdb;
			$user_id = $bp->loggedin_user->id;
			$query = "SELECT name FROM {$bp->media->table_media_album} WHERE user_id = $user_id";
			$result = $wpdb->get_results($query);
			?><option value="">Default</option><?php
			foreach ($result as $key => $value) {
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

<!-- <input type="radio" checked="checked" value="yes" name="notifications[notification_activity_new_reply]"> -->

</div><!-- #rt-album-create ends-->
	<?php
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
<!--
If current Component is group then show the KCW
-->
    <?php if($bp->current_component == BP_GROUPS_SLUG) {?>
<div id="kaltura_contribution_wizard_wrapper"></div>
	<?php }else { ?>
<div id="kaltura_contribution_wizard_wrapper" style="display: none"><div id ="eeee">'HELLO</div></div>
	<?php }?>
<script type="text/javascript">
    var cwWidth = 620;
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
        
        
        
        for(i = 0; i < entries.length; i++) {
            rt_entry_id_list = rt_entry_id_list+ entries[i].entryId+',';
            rt_entry_media_type = rt_entry_media_type+ entries[i].mediaType+',';
        
        }

        //as album_name and visibility is only related to group and groups may not have album, both variables must be initialized
        var album_name = ''
        var visibility = '1'; //every group media is by default public
        //here check which radio button is selected.(new album or existing album)
        rt_album_type = jQuery("#rt-album-create p input[@name='rt-album-choice']:checked").val();
        if( rt_album_type == 'rt-new'){
            //creating new album
            album_name = jQuery('#rt-new-album-name').val();
            visibility = jQuery("#rt-create-album input[@name='rt-visibility']:checked").val();
        }
        if(rt_album_type == 'rt-select-existing'){
            //select album from drop down
            //get the name of the album from drop down
            album_name = jQuery("#rt-selected-album select[@name='rt-album-list'] :selected'").text();
            visibility = 0; //by default set validity to 0
        }

        var data = {
            action: 'media_upload',
            rt_entry_id_list:rt_entry_id_list,
            rt_entry_media_type:rt_entry_media_type,
            rt_entry_group_id:'<?php echo $groups_template->group->id;?>',
            album_name :album_name,
            visibility : visibility,
          
        };
        jQuery.post(ajaxurl, data, function(response) {
            //            var data = {
            //                action: 'create_new_album',
            //                new_album_name : new_album_name,
            //                visibility : visibility
            //            };
            //
            //            jQuery.post(ajaxurl, data, function(response) {
            //                jQuery('.rt-album-message p').text(response);
            //            });
            alert('Media Uploded');
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
