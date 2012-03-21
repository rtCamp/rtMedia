<?php
/**
 * @package Media_Component
 */
?>
<?php
global $bp, $bp_picture_updated, $srcurl,$message,$type,$kaltura_validation_data,$ks;

?>


                <?php if(is_kaltura_configured()):?>


    <?php
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
    var cwSwf = new SWFObject("<?php echo $flash_url ?>", "kaltura_contribution_wizard_wrapper", cwWidth, cwHeight, "9", "#000000");
    cwSwf.addParam("flashVars", "<?php echo $flashVarsStr; ?>");
    cwSwf.addParam("allowScriptAccess", "always");
    cwSwf.addParam("allowNetworking", "all");
    cwSwf.write("kaltura_contribution_wizard_wrapper");
</script>

<script type="text/javascript">
    function onContributionWizardAfterAddEntry(entries) {

        var rt_entry_id_list= '';
        var rt_entry_media_type = '';

        for(var i = 0; i < entries.length; i++) {
            rt_entry_id_list = rt_entry_id_list+ entries[i].entryId+',';
            rt_entry_media_type = rt_entry_media_type+ entries[i].mediaType+',';
        }


//        console.log(rt_entry_media_type,rt_entry_id_list);
//        console.log(rt_entry_media_type+'  =  '+rt_entry_id_list);
        var data = {
            action: 'media_upload',
            rt_entry_id_list:rt_entry_id_list,
            rt_entry_media_type:rt_entry_media_type

        };
        jQuery.post(ajaxurl, data, function(response) {
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
