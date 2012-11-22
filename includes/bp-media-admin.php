<?php
if(version_compare(BP_MEDIA_DB_VERSION,get_site_option('bp_media_db_version','1.0'),'>')){
		add_action('admin_notices', 'bp_media_upgrade_db_notice');
}

add_action('wp_loaded','bp_media_upgrade_script');
function bp_media_upgrade_script(){
	if(isset($_GET['bp_media_upgrade_db']) && empty($_REQUEST['settings-updated'])){
		check_admin_referer('bp_media_upgrade_db','wp_nonce');
		require_once('bp-media-upgrade-script.php');
		$current_version = get_site_option('bp_media_db_version','1.0');
		if($current_version == '2.0')
			bp_media_upgrade_from_2_0_to_2_1();
		else
			bp_media_upgrade_from_1_0_to_2_1();
		remove_action('admin_notices', 'bp_media_upgrade_db_notice');
	}
}


/**
 * Displays admin notice to upgrade BuddyPress Media Database
 */
function bp_media_upgrade_db_notice() {
	?>
	<div class="error"><p>
		Please click upgrade to upgrade the database of BuddyPress Media <a class="button" id="refresh_media_count" href ="<?php echo bp_media_get_admin_url( add_query_arg( array( 'page' => 'bp-media-settings', 'bp_media_upgrade_db' => 1 ,'wp_nonce' => wp_create_nonce( 'bp_media_upgrade_db' ) ), 'admin.php' ) ) ?>" class="button" title="<?php printf(__('It will migrate your BuddyPress Media\'s earlier database to new database.')); ?>">Upgrade</a>
	</p></div>
	<?php
}

/**
 * Add the BuddyPress Media's options menu in the BuddyPress' options subnavigation.
 *
 * @since BP Media 2.0
 */
function bp_media_add_admin_menu() {

	global $bp,$bp_media_errors,$bp_media_messages;
	if (!is_super_admin())
        return false;
	$bp_media_errors=array();
	$bp_media_messages=array();
	global $bp_media_options;
	$bp_media_options = get_site_option('bp_media_options',array(
		'videos_enabled'	=>	true,
		'audio_enabled'		=>	true,
		'images_enabled'	=>	true,
		'download_enabled'	=>	true,
		'remove_linkback'	=>	1,
	));
	if(isset($_POST['submit'])){
		if(isset($_POST['bp_media_options'])){
			foreach($bp_media_options as $option=>$value){
				if(isset($_POST['bp_media_options'][$option])){
					switch($_POST['bp_media_options'][$option]){
						case 'true'	:
							$bp_media_options[$option] = true;
							break;
						case '1'	:
							$bp_media_options[$option] = 1;
							break;
						case '2'	:
							$bp_media_options[$option] = 2;
							break;
						default		:
							$bp_media_options[$option] = false;
					}
				}
				else{
					$bp_media_options[$option] = false;
				}
			}
			if(update_site_option('bp_media_options', $bp_media_options)){
				$bp_media_messages[0]="<b>Settings saved.</b>";
			}
		}
		do_action('bp_media_save_options');
		$bp_media_messages = apply_filters('bp_media_settings_messages',$bp_media_messages);
		$bp_media_errors = apply_filters('bp_media_settings_errors',$bp_media_errors);
	}
	else if(isset($_GET['bp_media_refresh_count'])){
		check_admin_referer('bp_media_refresh_count','wp_nonce');
		if(!bp_media_update_count())
			$bp_media_errors[]="<b>Recounting Failed</b>";
		else
			$bp_media_messages[]="<b>Recounting of media files done successfully</b>";
	}

	if(isset($bp_media_errors) && count($bp_media_errors)) { ?>
		<div class="error"><p><?php foreach($bp_media_errors as $error) echo $error.'<br/>'; ?></p></div><?php
	} if(isset($bp_media_messages) && count($bp_media_messages)){  ?>
		<div class="updated"><p><?php foreach($bp_media_messages as $message) echo $message.'<br/>'; ?></p></div><?php
	}

	add_menu_page( 'BP Media Component', 'BP Media', 'manage_options', 'bp-media-settings', 'bp_media_settings_page' );
	add_submenu_page( 'bp-media-settings', __( 'BP-Media Settings', 'bp-media' ), __( 'Settings', 'bp-media' ), 'manage_options', 'bp-media-settings', "bp_media_settings_page" );
	add_submenu_page( 'bp-media-settings', __( 'BP-Media Addons', 'bp-media' ), __( 'Addons', 'bp-media' ), 'manage_options', 'bp-media-addons', "bp_media_settings_page" );
	add_submenu_page( 'bp-media-settings', __( 'BP-Media Support', 'bp-media' ), __( 'Support ', 'bp-media' ), 'manage_options', 'bp-media-support', "bp_media_settings_page" );

    $tab = isset( $_GET['page'] )  ? $_GET['page'] : "bp-media-settings";
    add_action('admin_print_styles-' . $tab, 'bp_media_admin_enqueue');
}

add_action(bp_core_admin_hook(), 'bp_media_add_admin_menu');

add_action('admin_init','bp_media_on_load_page');

/**
*   Applies WordPress metabox funtionality to metaboxes
*
*
*/
function bp_media_on_load_page() {

    /* Javascripts loaded to allow drag/drop, expand/collapse and hide/show of boxes. */
    wp_enqueue_script( 'common' );
    wp_enqueue_script( 'wp-lists' );
    wp_enqueue_script( 'postbox' );

    // Check to see which tab we are on
    $tab = isset( $_GET['page'] )  ? $_GET['page'] : "bp-media-settings";

    switch ( $tab ) {
        case 'bp-media-addons' :
            // All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
            add_meta_box('bp-media-addons-list_metabox',__('BuddyPress Media Addons for Audio/Video Conversion','bp-media'),'bp_media_addons_list','bp-media-settings', 'normal', 'core' );
            break;
        case 'bp-media-support' :
            // All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
            add_meta_box( 'post_summaries_options_metabox', __('BuddyPress Media Support', 'rtPanel'), 'bp_media_support', 'bp-media-settings', 'normal', 'core' );
            break;
        case $tab :
            // All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
            add_meta_box( 'bp_media_settings_metabox', __('BuddyPress Media Settings', 'rtPanel'), 'bp_media_admin_menu', 'bp-media-settings', 'normal', 'core' );
            add_meta_box( 'bp_media_options_metabox', __('Spread the word', 'rtPanel'), 'bp_media_settings_options', 'bp-media-settings', 'normal', 'core' );
            add_meta_box( 'bp_media_other_options_metabox', __('BuddyPress Media Other options', 'rtPanel'), 'bp_media_settings_other_options', 'bp-media-settings', 'normal', 'core' );
            break;
    }
}


function bp_media_settings_page(){
	global $bp_media_errors,$bp_media_messages;
    $tab = isset( $_GET['page'] )  ? $_GET['page'] : "bp-media-settings";

    ?>
    <div class="wrap bp-media-admin">
        <?php //screen_icon( 'buddypress' ); ?>
        <div id="icon-buddypress" class="icon32"><br></div>
        <h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs( __( 'Media', 'bp-media' ) ); ?></h2>
        <div class="metabox-holder columns-2">
            <div class="bp-media-settings-tabs"><?php
                // Check to see which tab we are on
               if(current_user_can('manage_options')){
                    $tabs_html    = '';
                    $idle_class   = 'media-nav-tab';
                    $active_class = 'media-nav-tab media-nav-tab-active';
                    $tabs = array();

                    // Check to see which tab we are on
                    $tab = isset( $_GET['page'] )  ? $_GET['page'] : "bp-media-settings";
                    /* BP Media */
                    $tabs[] = array(
                        'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-media-settings'  ), 'admin.php' ) ),
                        'title' => __( 'BP Media Settings', 'bp-media' ),
                        'name' => __( 'Settings', 'bp-media' ),
                        'class' => ($tab == 'bp-media-settings') ? $active_class : $idle_class. ' first_tab'
                    );

                    $tabs[] = array(
                        'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-media-addons'  ), 'admin.php' ) ),
                        'title' => __( 'BP Media Addons', 'bp-media' ),
                        'name' => __( 'Addons', 'bp-media' ),
                        'class' => ($tab == 'bp-media-addons') ? $active_class : $idle_class
                    );

                    $tabs[] = array(
                        'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-media-support'  ), 'admin.php' ) ),
                        'title' => __( 'BP Media Support', 'bp-media' ),
                        'name' => __( 'Support', 'bp-media' ),
                        'class' => ($tab == 'bp-media-support') ? $active_class : $idle_class. ' last_tab'
                    );

                    $pipe = '|' ;
                    $i = '1';
                    foreach($tabs as $tab){
                        if($i!=1) $tabs_html.=$pipe;
                        $tabs_html.= '<a title=""' . $tab['title'] . '" " href="' . $tab['href'] . '" class="' . $tab['class'] . '">' . $tab['name'] . '</a>';
                        $i++;
                    }
                    echo $tabs_html;
                }?>
            </div>

            <div id="bp-media-settings-boxes">

                <form id="bp_media_settings_form" name="bp_media_settings_form" action="" method="post" enctype="multipart/form-data">
                    <?php

                    settings_fields( 'bp_media_options_settings');
                    do_settings_fields( 'bp_media_options_settings','' );
                    do_meta_boxes( 'bp-media-settings', 'normal', '' ); ?>

                    <script type="text/javascript">
                        //<![CDATA[
                        jQuery(document).ready( function($) {
                            // close postboxes that should be closed
                            $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                            // postboxes setup
                            postboxes.add_postbox_toggles('bp-media-settings');
                        });
                        //]]>
                    </script>
                </form>
            </div><!-- .bp-media-settings-boxes -->
            <div class="metabox-fixed metabox-holder alignright bp-media-metabox-holder">
                    <?php bp_media_default_admin_sidebar(); ?>
            </div>
        </div><!-- .metabox-holder -->
    </div><!-- .bp-media-admin --><?php
}


/**
 * Displays and updates the options menu of BuddyPress Media
 *
 * @since BP Media 2.0
 */
function bp_media_admin_menu() {

	$bp_media_errors=array();
	$bp_media_messages=array();

	global $bp_media_options;
	$bp_media_options = get_site_option('bp_media_options',array(
		'videos_enabled'	=>	true,
		'audio_enabled'		=>	true,
		'images_enabled'	=>	true,
		'download_enabled'	=>	true,
		'remove_linkback'	=>	1,
	));
	?>

    <?php if(count($bp_media_errors)) { ?>
    <div class="error"><p><?php foreach($bp_media_errors as $error) echo $error.'<br/>'; ?></p></div>
    <?php } if(count($bp_media_messages)){  ?>
    <div class="updated"><p><?php foreach($bp_media_messages as $message) echo $message.'<br/>'; ?></p></div>
    <?php } ?>
    <table class="form-table ">
        <tbody>
            <tr valign="top">
                <th scope="row"><label for="videos_enabled">Videos</label></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span>Enable Videos</span></legend>
                        <label for="videos_enabled"><input name="bp_media_options[videos_enabled]" type="checkbox" id="videos_enabled" value="true" <?php global $bp_media_options;checked($bp_media_options['videos_enabled'],true) ?>> (Check to enable video upload functionality)</label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="audio_enabled">Audio</label></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span>Enable Audio</span></legend>
                        <label for="audio_enabled"><input name="bp_media_options[audio_enabled]" type="checkbox" id="audio_enabled" value="true" <?php checked($bp_media_options['audio_enabled'],true) ?>> (Check to enable audio upload functionality)</label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="images_enabled">Images</label></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span>Enable Images</span></legend>
                        <label for="images_enabled"><input name="bp_media_options[images_enabled]" type="checkbox" id="images_enabled" value="true" <?php checked($bp_media_options['images_enabled'],true) ?>> (Check to enable images upload functionality)</label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="download_enabled">Download</label></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span>Enable Download</span></legend>
                        <label for="download_enabled"><input name="bp_media_options[download_enabled]" type="checkbox" id="download_enabled" value="true" <?php checked($bp_media_options['download_enabled'],true) ?>> (Check to enable download functionality)</label>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>

    <?php do_action('bp_media_extension_options'); ?>

    <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"></p>
    <div class="clear"></div><?php
}

function bp_media_settings_other_options(){ ?>

        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="refresh_media_count">Re-Count Media Entries</label></th>
                    <td> <fieldset>
                            <a id="refresh_media_count" href ="?page=bp-media-settings&bp_media_refresh_count=1&wp_nonce=<?php echo wp_create_nonce( 'bp_media_refresh_count' ); ?>" class="button" title="<?php printf(__('It will re-count all media entries of all users and correct any discrepancies.')); ?>">Re-Count</a>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="clear"></div>

<?php }

function bp_media_settings_options(){
	global $bp_media_options;
    $bp_media_options = get_site_option('bp_media_options',array(
		'videos_enabled'	=>	true,
		'audio_enabled'		=>	true,
		'images_enabled'	=>	true,
		'download_enabled'	=>	true,
		'remove_linkback'	=>	1,
	));
    ?>
    <table class="form-table ">
        <tbody>
            <tr valign="top">
                <th scope="row"><label for="remove_linkback">Spread the word</label></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span>Yes, I want to support BuddyPress Media</span></legend>
                        <label for="remove_linkback_yes"><input name="bp_media_options[remove_linkback]" type="radio" id="remove_linkback_yes" value="2" <?php checked($bp_media_options['remove_linkback'], '2' ); ?>> Yes, I support BuddyPress Media</label>
                        <br/>
                        <legend class="screen-reader-text"><span>No, I don't want to support BuddyPress Media</span></legend>
                        <label for="remove_linkback_no"><input name="bp_media_options[remove_linkback]" type="radio" id="remove_linkback_no" value="1" <?php checked($bp_media_options['remove_linkback'], '1' ); ?>> No, I don't support BuddyPress Media</label>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>
    <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"></p>
    <div class="clear"></div>
<?php }




function bp_media_addons_list(){ ?>

    <div class="addon-list">
        <ul class="products">

            <li class="product first">
                <a href="http://rtcamp.com/store/buddypress-media-kaltura/"  title="BuddyPress - Media Kaltura Add-on">
                    <img width="240" height="184" title="BuddyPress - Media Kaltura Add-on" alt="BuddyPress - Media Kaltura Add-on" src="http://cdn.rtcamp.com/files/2012/10/new-buddypress-media-kaltura-logo-240x184.png">
                </a>
                <h4><a href="http://rtcamp.com/store/buddypress-media-kaltura/"  title="BuddyPress - Media Kaltura Add-on">BuddyPress-Media Kaltura Add-on</a></h4>
                <div class="product_desc">
                    <p>Add support for more video formats using Kaltura video solution.</p>
		    <p>Works with Kaltura.com, self-hosted Kaltura-CE and Kaltura-on-premise.</p>
                </div>
		<div class="product_footer">
                	<span class="price alignleft"><span class="amount">$99</span></span>
                    	<a class="add_to_cart_button  alignright product_type_simple"  href="http://rtcamp.com/store/?add-to-cart=15446"><?php _e('Buy Now', 'bp-media'); ?></a>
	    		<a class="alignleft product_demo_link"  href="http://demo.rtcamp.com/bpm-kaltura/" title="BuddyPress Media Kaltura Add-on">Live Demo</a>
		 </div><!-- .product_footer -->
            </li>
            <li class="product last">
                <a href="http://rtcamp.com/store/buddypress-media-ffmpeg-converter/" title="BuddyPress-Media FFMPEG Converter Plugin" >
                    <img width="240" height="184" title="BuddyPress-Media FFMPEG Converter Plugin" alt="BuddyPress-Media FFMPEG Converter Plugin" src="http://cdn.rtcamp.com/files/2012/09/ffmpeg-logo-240x184.png">
                </a>
                <h4><a href="http://rtcamp.com/store/buddypress-media-ffmpeg-converter/" title="BuddyPress-Media FFMPEG Converter Plugin" >BuddyPress-Media FFMPEG Add-on</a></h4>
                <div class="product_desc">
                    <p>Add supports for more audio &amp; video formats using open-source <a href="https://github.com/rtCamp/media-node">media-node</a>.</p>
                    <p>Media node comes with automated setup script for Ubuntu/Debian.</p>
                </div>
                <div class="product_footer">
		    <span class="price alignleft"><span class="amount">$49</span></span>
                    <a class="add_to_cart_button alignright  product_type_simple"  href="http://rtcamp.com/store/?add-to-cart=13677"><?php _e('Buy Now', 'bp-media'); ?></a>
    		<a class="alignleft product_demo_link" href="http://demo.rtcamp.com/bpm-media" title="BuddyPress Media FFMPEG Add-on">Live Demo</a>
                </div><!-- .product_footer -->
            </li>

        </ul><!-- .products -->
    </div><!-- .addon-list -->

<?php }


function bp_media_support(){ global $current_user; ?>

    <div class="bp-media-support">
        <h2><?php _e('Need Help/Support?', 'bp-media');?></h2>
        <ul class="support_list">
            <li><a href="http://rtcamp.com/buddypress-media/faq/"  title="<?php _e('Read FAQ', 'bp-media');?>"><?php _e('Read FAQ', 'bp-media');?></a> </li>
            <li><a href="http://rtcamp.com/support/forum/buddypress-media/"  title="<?php _e('Free Support Forum', 'bp-media');?>"><?php _e('Free Support Forum', 'bp-media');?></a></li>
            <li><a href="https://github.com/rtCamp/buddypress-media/issues/"  title="<?php _e('Github Issue Tracker', 'bp-media');?>"><?php _e('Github Issue Tracker', 'bp-media');?> </a> </li>
        </ul>
        <br/>

        <h2><?php _e('Hire Us!', 'bp-media');?></h2>

        <h4><a href="http://rtcamp.com/contact/?purpose=hire"><?php _e('We are available for customisation and premium support. Get on touch with us. :-)', 'bp-media');?></a></h4>
<!--
        <div class="bp-media-form" id="premium-support-form" >
            <h4><?php _e('Please fill the form for premium support'); ?></h4>
            <ul>
                <li>
                    <label class="bp-media-label" for="ur_name"><?php _e('Your Name:','bp-media');?></label><input class="bp-media-input" id="ur_name" type="text" name="premium_support[ur_name]" value="<?php echo (isset($_REQUEST['premium_support']['ur_name']))? $_REQUEST['premium_support']['ur_name'] : $current_user->user_login; ?>"/>
                </li>
                <li>
                    <label class="bp-media-label" for="ur_email"><?php _e('Your Email-Id:','bp-media');?></label><input id="ur_email" class="bp-media-input" type="text" name="premium_support[ur_email]" value="<?php echo (isset($_REQUEST['premium_support']['ur_name']))? $_REQUEST['premium_support']['ur_name'] : get_option('admin_email'); ?>"/>
                </li>
                <li>
                    <label class="bp-media-label" for="ur_query"><?php _e('Details:','bp-media');?></label><textarea id="ur_query" class="bp-media-textarea" type="text" name="premium_support[ur_query]"/><?php echo (isset($_REQUEST['premium_support']['ur_query']))? $_REQUEST['premium_support']['ur_query'] : ''; ?></textarea>
                </li>
                <li>
                    <label class="bp-media-label" for="ur_budget"><?php _e('Your Budget:','bp-media');?></label><input id="ur_budget" class="bp-media-input" type="text" name="premium_support[ur_budget]" value="<?php echo (isset($_REQUEST['premium_support']['ur_budget']))? $_REQUEST['premium_support']['ur_budget'] : ''; ?>"/>
                </li>
                <li>
                    <label class="bp-media-label" for="ur_delivery_date"><?php _e('Expected Delivery Date:','bp-media');?></label><input id="ur_delivery_date" class="bp-media-input" type="text" name="premium_support[ur_delivery_date]" value="<?php echo (isset($_REQUEST['premium_support']['ur_delivery_date']))? $_REQUEST['premium_support']['ur_delivery_date'] : ''; ?>"/>
                </li>
            </ul>
            <p class="submit"><input type="submit" name="premium_form_submit" id="submit" class="button-primary" value="Submit"></p>
        </div><!-- .premium-support-form-->
        <br/>
    </div>

<?php }


/**
 * Default BuddyPress Media admin sidebar with metabox styling
 *
 * @since BP Media 2.0
 */
function bp_media_default_admin_sidebar() {	?>

    <div class="rtmetabox postbox" id="branding">
        <div class="inside">
        <a href="http://rtcamp.com" title="Empowering The Web With WordPress" id="logo"><img src="<?php echo plugins_url( '/img/rtcamp-logo.png', __FILE__ ); ?>" alt="rtCamp" /></a>
            <ul id="social">
                <li><a href="<?php printf('%s', 'http://www.facebook.com/rtCamp.solutions/'); ?>"  title="<?php _e('Become a fan on Facebook', 'bp-media'); ?>" class="bp-media-facebook bp-media-social"><?php _e('Facebook', 'bp-media'); ?></a></li>
                <li><a href="<?php printf('%s', 'https://twitter.com/rtcamp/'); ?>"  title="<?php _e('Follow us on Twitter', 'bp-media'); ?>" class="bp-media-twitter bp-media-social"><?php _e('Twitter', 'bp-media'); ?></a></li>
                <li><a href="<?php printf('%s', 'http://feeds.feedburner.com/rtcamp/'); ?>"  title="<?php _e('Subscribe to our feeds', 'bp-media'); ?>" class="bp-media-rss bp-media-social"><?php _e('RSS Feed', 'bp-media'); ?></a></li>
            </ul>
        </div>
    </div>

	<div class="rtmetabox postbox" id="support">

		<h3 class="hndle"><span><?php _e('Need Help?', 'bp-media'); ?></span></h3>
		<div class="inside"><p><?php printf(__(' Please use our <a href="%s">free support forum</a>.<br/><span class="bpm-aligncenter">OR</span><br/>
		<a href="%s">Hire us!</a> To get professional customisation/setup service.', 'bp-media'), 'http://rtcamp.com/support/forum/buddypress-media/','http://rtcamp.com/buddypress-media/hire/'); ?>.</p></div>
	</div>

	<div class="rtmetabox postbox" id="donate">

		<h3 class="hndle"><span><?php _e('Donate', 'bp-media'); ?></span></h3>
		<span><a href="http://rtcamp.com/donate/" title="Help the development keep going."><img class="bp-media-donation-image" src ="<?php echo plugins_url( '/img/donate.gif', __FILE__ ); ?>"   /></a></span>
		<div class="inside"><p><?php printf(__('Help us release more amazing features faster. Consider making a donation to our consistent efforts.', 'bp-media')); ?>.</p></div>
	</div>

	<div class="rtmetabox postbox" id="bp-media-premium-addons">

		<h3 class="hndle"><span><?php _e('Premium Addons', 'bp-media'); ?></span></h3>
		<div class="inside">
			<ul>
 <li><a href="http://rtcamp.com/store/buddypress-media-kaltura/" title="BuddyPress Media Kaltura">BPM-Kaltura</a> - add support for Kaltura.com/Kaltura-CE based video conversion support</li>
				<li><a href="http://rtcamp.com/store/buddy-press-media-ffmpeg/" title="BuddyPress Media FFMPEG">BPM-FFMPEG</a> - add FFMEG-based audio/video conversion support</li>
			</ul>
			<h4><?php printf(__('Are you a developer?','bp-media')) ?></h4>
			<p><?php printf(__('If you are developing a BuddyPress Media addon we would like to include it in above list. We can also help you sell them. <a href="%s">More info!</a>','bp-media'),'http://rtcamp.com/contact/') ?></p></h4>
		</div>
	</div>

	<div class="rtmetabox postbox" id="bp_media_latest_news">

		<h3 class="hndle"><span><?php _e('Latest News', 'bp-media'); ?></span></h3>
		<div class="inside"><img src ="<?php echo admin_url(); ?>/images/wpspin_light.gif" /> Loading...</div>
	</div><?php
}



/**
 * Enqueues the scripts and stylesheets needed for the BuddyPress Media's options page
 */
function bp_media_admin_enqueue() {
    $current_screen = get_current_screen();
    $admin_js = trailingslashit(site_url()).'?bp_media_get_feeds=1';
    wp_enqueue_script('bp-media-js',plugins_url('includes/js/bp-media.js', dirname(__FILE__)));
    wp_localize_script('bp-media-js','bp_media_news_url',$admin_js);
    wp_enqueue_style('bp-media-admin-style', plugins_url('includes/css/bp-media-style.css', dirname(__FILE__)));

}
add_action('admin_enqueue_scripts', 'bp_media_admin_enqueue');



/**
 * Adds a tab for Media settings in the BuddyPress settings page
 */
function bp_media_admin_tab() {

    if(current_user_can('manage_options')){
        $tabs_html    = '';
        $idle_class   = 'nav-tab';
        $active_class = 'nav-tab nav-tab-active';
        $tabs = array();

        // Check to see which tab we are on
        $tab = isset( $_GET['page'] )  ? $_GET['page'] : "bp-media-settings";
        /* BP Media */
        $tabs[] = array(
            'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-media-settings'  ), 'admin.php' ) ),
            'title' => __( 'BP Media', 'bp-media' ),
            'name' => __( 'BP Media', 'bp-media' ),
            'class' => ($tab == 'bp-media-settings' || $tab == 'bp-media-addons' || $tab == 'bp-media-support') ? $active_class : $idle_class
        );

        foreach($tabs as $tab){
            $tabs_html.= '<a id="bp-media" title= "' . $tab['title'] . '"  href="' . $tab['href'] . '" class="' . $tab['class'] . '">' . $tab['name'] . '</a>';
        }
        echo $tabs_html;
    }
}

add_action('bp_admin_tabs','bp_media_admin_tab');

?>