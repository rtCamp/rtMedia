<?php
/**
 * @package Media_Component
 */
?>
<?php

/***
 * This file is used to add site administration menus to the WordPress backend.
 *
 * If you need to provide configuration options for your component that can only
 * be modified by a site administrator, this is the best place to do it.
 *
 * However, if your component has settings that need to be configured on a user
 * by user basis - it's best to hook into the front end "Settings" menu.
 */

/**
 * bp_media_admin()
 *
 * Checks for form submission, saves component settings and outputs admin screen HTML.
 */


/*
 * Checks for form submission, saves component settings and outputs admin screen HTML.
 */
function media_admin() { //for the media control options added by ashish
    global $bp, $bbpress_live;

    if(isset($_POST['submit-size'])){
        if($_POST['dashboard-size'] && $_POST['media-size']){
            $dashboard = $_POST['dashboard-size'];
            $media     = $_POST['media-size'];

            update_option('bp_rt_dashboard_size',$dashboard);
            update_option('bp_rt_media_size',$media);

      echo '<div class="updated fade" style="background-color: #FFF123;">Media Control Settings Saved Successfully !</div>';

        }else{
            echo '<div class="updated fade" style="background-color: #FFF000;">Media Control Settings Encountered Error.. Try Again Later</div>';
        }
    }//End of media control options settings

    if(isset($_POST['Submit'])) {
        if($_POST['partner_id'] && $_POST['email'] && $_POST['password'] && $_POST['kaltura_url']) {
            $partnerId         = $_POST['partner_id'];
            $adminEmail        = $_POST['email'];
            $cmsPassword       = $_POST['password'];
             $kaltura_url       = $_POST['kaltura_url'];
            try {
                $config         = new KalturaConfiguration($partnerId);
            }
            catch (Exception $e ) {
                ?>
<div class="updated fade" style="background-color: #FF0000;">
    <b>Invalid Partner ID</b>
</div>
            <?php
            }
            if(!$e) {
                try {
                    $config->serviceUrl = $kaltura_url;
                    $client         = new KalturaClient($config);
			
                    $partnerInfo    = $client->partner->getSecrets($partnerId, $adminEmail, $cmsPassword);
                    update_option( 'bp_rt_kaltura_url',            $config->serviceUrl );
                    update_option( 'bp_rt_kaltura_partner_id',     $partnerId );

                    update_option( 'bp_rt_kaltura_subpartner_id',     $partnerId.'00' );

                    update_option( 'bp_rt_kaltura_cms_user',       $adminEmail );
                    update_option( 'bp_rt_kaltura_secret',         $partnerInfo->secret );
                    update_option( 'bp_rt_kaltura_admin_secret',   $partnerInfo->adminSecret );
                    update_option( 'bp_rt_kaltura_cms_password',   $cmsPassword );


                    ?>

<div class="updated fade" style="background-color: #A0FF9F;">
    <b>You have successfully installed Media Pack</b>
</div>
                <?php
                }
                catch(Exception $e ) {

                    ?>
<div class="updated fade" style="background-color: #EFB3B3;">
    <b>Sorry, Not able to authenticate you account!</b>
</div>
                <?php
                }
            }
        }
        else {
            ?>
<div class="updated fade" style="background-color: #EFB3B3;">
    <b>Please do not keep any field blank! </b>
</div>
        <?php
        }

    }?>

<?php
    global $kaltura_validation_data;
    if(!$kaltura_validation_data){
        ?>
        <div class="updated fade" style="background-color: #EFB3B3;">
            Unable to Connect Media Host Server!
        </div>

        <?php
    }

?>
<div class="wrap">
    <h2>Kaltura Settings</h2>
    <form name="form1" method="post" />
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e("Partner ID"); ?>:</th>
            <td><input type="text" id="partner_id" name="partner_id" value="<?php echo get_site_option( 'bp_rt_kaltura_partner_id')?>" size="10" />
                <span class="description">Don't have a PartnerID? <a href="http://corp.kaltura.com/about/signup">Get PartnerID for free</a></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("Email"); ?>:</th>
            <td><input type="text" id="email" name="email" value="<?php echo get_site_option( 'bp_rt_kaltura_cms_user')?>" size="40" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("Password"); ?>:</th>
            <td><input type="password" id="password" name="password" value="<?php echo get_site_option( 'bp_rt_kaltura_cms_password')?>" size="20" /> </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("Kaltura Server URL"); ?>:</th>
            <td><input type="text" id="kaltura_url" name="kaltura_url" value="<?php if(get_site_option( 'bp_rt_kaltura_url')!='') echo get_site_option( 'bp_rt_kaltura_url');else echo "http://www.kaltura.com"?>" size="40"/></td>
        </tr>
    </table>
    <p class="submit" style="text-align: left; "><input type="submit" name="Submit" value="<?php _e('Complete installation') ?>" /></p>
    <!-- <p class="submit" style="text-align: left; "><input type="submit" name="Submit" value="<?php _e('Complete installation') ?>" /></p> -->

    <h2>Help and Support</h2>
    <table class="form-table">
        <tr valign="top">
            <td>
                <p>
                    <b>Help:</b> By default, all images, videos & audios uplaoded by your members get stored on Kaltura's own server.
                    <br/>
                    If you are concerned about your users privacy, you can use free and open-source self-hosted kaltura solution named <a href="http://www.kaltura.org/project/kalturaCE">KalturaCE </a>.
                </p>
                <p>
                    <b>Professional Support:</b> KalturaCE usage requires a good webhost and some technical expertise for installation & configuration.
                    <br/>
                    You can <a href="http://rtcamp.com/contact/">hire us</a> to get this done peacefully! You can also get some free help from <a href="http://rtforums.com">our forum</a>.
                </p>
            </td>
        </tr>
    </table>
<!--<h2>Help & Support Links...</h2>-->
<p>
    <ul style="padding-left:20px; list-style-type:disc">
        <li><a href="http://mediabp.com/"> Project Homepage</a></li>
        <li><a href="http://rtforums.com/forum/buddypress-media-component">Report A Bug/Request a feature</a></li>
        <li><a href="http://mediabp.com/contact/">Paid Support - For KalturaCE and any other BuddyPress customization and theme support</a></li>
    </ul>
</p>

    <input type="hidden" name="is_postback" value="postback" />
</form>
    <h2>Media Control</h2>
    <form name="frm-size-ctrl" method="post">
    <p>Number of media shown per page on DashBoard <input type ="text" name="dashboard-size" value="<?php if(get_site_option( 'bp_rt_dashboard_size')!='') echo get_site_option( 'bp_rt_dashboard_size');else echo "12"?>" size="4"/></p>
    <p>Number of media shown per page on FrontEnd <input type ="text" name="media-size" value="<?php if(get_site_option( 'bp_rt_media_size')!='') echo get_site_option( 'bp_rt_media_size');else echo "12"?>" size="4"/></p>
    <p class="submit" style="text-align: left; "><input type="submit" name="submit-size" value="<?php _e('Save Size') ?>" /></p>
    </form>
    <hr/>

<h3>Like this Plugin? Support its development...</h3>
<p>Your donations will help us devote more time for adding features to this free & open-source plugin. <!--Please check Project roadmap for upcoming features.-->
    </p>
<p>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="hosted_button_id" value="9488824">
        <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
    </form>
</p>
</div>
<?php
}
?>