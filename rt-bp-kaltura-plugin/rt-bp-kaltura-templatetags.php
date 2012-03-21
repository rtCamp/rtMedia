<?php

/**
 * In this file you should define template tag functions that end users can add to their template files.
 * Each template tag function should echo the final data so that it will output the required information
 * just by calling the function name.
 */

class rt_media_Template {
    var $current_item = -1;
    var $item_count;
    var $items;
    var $item;

    var $in_the_loop;

    var $pag_page;
    var $pag_num;
    var $pag_links;

    //    var $blog_name;
    //    var $name;
    //    var $email;
    //    var $web_site_url;
    //    var $description;
    //    var $phone_number;
    //    var $content_category;
    //    var $adult_content;
    //    var $agree_to_terms;


    function rt_media_template( $user_id, $type, $per_page, $max ) {
        global $bp;

        if ( !$user_id )
            $user_id = $bp->displayed_user->id;

        $this->pag_page = isset( $_REQUEST['xpage'] ) ? intval( $_REQUEST['xpage'] ) : 1;
        $this->pag_num = isset( $_GET['num'] ) ? intval( $_GET['num'] ) : $per_page;
        $this->user_id = $user_id;

        // Item Requests
        if ( !$max || $max >= (int)$this->items['total'] )
            $this->total_item_count = (int)$this->items['total'];
        else
            $this->total_item_count = (int)$max;

        $this->items = $this->items['items'];

        if ( $max ) {
            if ( $max >= count($this->items) )
                $this->item_count = count($this->items);
            else
                $this->item_count = (int)$max;
        } else {
            $this->item_count = count($this->items);
        }

        $this->pag_links = paginate_links( array(
            'base' => add_query_arg( 'xpage', '%#%' ),
            'format' => '',
            'total' => ceil( (int) $this->total_item_count / (int) $this->pag_num ),
            'current' => (int) $this->pag_page,
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'mid_size' => 1
        ));
    }

    function has_items() {
        if ( $this->item_count )
            return true;

        return false;
    }

    function next_item() {
        $this->current_item++;
        $this->item = $this->items[$this->current_item];

        return $this->item;
    }

    function rewind_items() {
        $this->current_item = -1;
        if ( $this->item_count > 0 ) {
            $this->item = $this->items[0];
        }
    }

    function user_items() {
        if ( $this->current_item + 1 < $this->item_count ) {
            return true;
        } elseif ( $this->current_item + 1 == $this->item_count ) {
            do_action('loop_end');
            // Do some cleaning up after the loop
            $this->rewind_items();
        }

        $this->in_the_loop = false;
        return false;
    }

    function the_item() {
        global $item, $bp;

        $this->in_the_loop = true;
        $this->item = $this->next_item();

        if ( 0 == $this->current_item ) // loop has just started
            do_action('loop_start');
    }
}

/*
 * Kaltura Contribution Widget (KWC) integrated here.
 * http://kaltura.com
 */

function rt_media_upload_content() {
//    echo "kapil";
    $partner_id     = get_site_option('bp_rt_kaltura_partner_id');
    $subp_id        = $partner_id."00";
    $secret         = get_site_option('bp_rt_kaltura_secret');
    $admin_secret   = get_site_option('bp_rt_kaltura_admin_secret');

    //define session variables
    $partnerUserID          = 'ANONYMOUS';

    //construct Kaltura objects for session initiation
    $kaltura_url = get_site_option('bp_rt_kaltura_url');
    
    $config           = new KalturaConfiguration($partner_id);
    $config->serviceUrl = $kaltura_url;
    //var_dump($config);

    $client           = new KalturaClient($config);
//    var_dump($client);
//    die();
    $ks               = $client->session->start($secret, $partnerUserID, KalturaSessionType::USER);
//    var_dump($ks);
    
    //Prepare variables to be passed to embedded flash object.
    $flashVars = array();
    $flashVars["uid"]               = $partnerUserID;
    $flashVars["partnerId"]         = $partner_id;
    $flashVars["ks"]                = $ks;
    $flashVars["afterAddEntry"]     = "onContributionWizardAfterAddEntry";
    $flashVars["close"]             = "onContributionWizardClose";
    $flashVars["showCloseButton"]   = false;
    $flashVars["Permissions"]       = 1;
    //var_dump($flashVars);
    
    $flash_url = $kaltura_url."/kse/ui_conf_id/501";
    $flashVarsStr = "userId=1&sessionId=".$ks."&partnerId=".$partner_id."&subPartnerId=".$subp_id."&kshowId=-1&afterAddentry=onContributionWizardAfterAddEntry&close=onContributionWizardClose&termsOfUse=http://corp.kaltura.com/static/tandc&showCloseButton=false";

//    var_dump($flash_url);
//    var_dump($flashVarsStr);
//    http://www.kaltura.com/kse/ui_conf_id/501
    ?>
<!-- <div id="kcw"></div>
<script type="text/javascript">
    var params = {
        allowScriptAccess: "always",
        allowNetworking: "all",
        wmode: "opaque"
    };
    // php to js
    var flashVars = <?php //echo json_encode($flashVars); ?>;
//    swfobject.embedSWF("http://www.kaltura.com/kcw/ui_conf_id/1000199", "kcw", "680", "360", "9.0.0", "expressInstall.swf", flashVars, params);
//    swfobject.embedSWF("http://localhost/kalturaCE/kcw/ui_conf_id/1000199", "kcw", "680", "360", "9.0.0", "expressInstall.swf", flashVars, params);

//    swfobject.embedSWF(swfUrl, id, width, height, version, expressInstallSwfurl, flashvars, params, attributes, callbackFn)
</script>-->

<div id="kaltura_contribution_wizard_wrapper"></div>

<script type="text/javascript">
	var cwWidth = 680;
	var cwHeight = 360;

//	var topWindow = Kaltura.getTopWindow();
//	// fix for IE6, scroll the page up so modal would animate in the center of the window
//	if (jQuery.browser.msie && jQuery.browser.version < 7)
//		topWindow.scrollTo(0,0);

	var cwSwf = new SWFObject("<?php echo $flash_url ?>", "kaltura_contribution_wizard_wrapper", cwWidth, cwHeight, "9", "#000000");
	cwSwf.addParam("flashVars", "<?php echo $flashVarsStr; ?>");
	cwSwf.addParam("allowScriptAccess", "always");
	cwSwf.addParam("allowNetworking", "all");
	cwSwf.write("kaltura_contribution_wizard_wrapper");
</script>

<script type="text/javascript">
    /*
     * On successful upload Media contents, this function is triggered and the results are stored in the database.
     * Later on this information is useful to seperate the media contents userwise.
     */
    function onContributionWizardAfterAddEntry(entries) {
        var rt_entry_id_list= '';
        var rt_entry_type_list='';
        for(var i = 0; i < entries.length; i++) {
            rt_entry_id_list = rt_entry_id_list+ entries[i].entryId+',';
            rt_entry_type_list = rt_entry_type_list + entries[i].mediaType+',';
        }

        jQuery.ajax({
            type    : "POST",
            url     : "../../wp-content/plugins/rt-bp-kaltura-plugin/ajax/rt-bp-kaltura-upload-video.php",
            data    : "rt_entry_id_list="+rt_entry_id_list+"&rt_entry_type_list="+rt_entry_type_list,
            success : function(msg){
            }
        })

    }
</script>

<script type="text/javascript">
    function onContributionWizardClose() {
    }
</script>
<?php

}

/*
 * Checks whether kaltura is configured or not
 */
function rt_media_check_partner_id() {
    if(!get_site_option('bp_rt_kaltura_partner_id')) {
        return false;
    }
    else
        return true;
}
function bp_rt_media_get_kaltura() {
    echo "Please ask Admin to configure Media Component";
}

/*
 * For Kaltura, there is only one entity(i.e. Partner ID) storing media content but for multiuser site (i.e. WPMU),every user is storing indivisual Media.
 * The relationshit is stored in the database.
 * So at the time of displaying the content, extra logic is added for displying the content userwise.
 *
 */

function rt_media_photo_library() {
    global $wpdb,$current_user,$bp;

    //There are userwise uploaded images stored in database while uploading
    $result = $wpdb->get_results("SELECT * FROM wp_bp_rt_media
        WHERE rt_user_id = ".$bp->displayed_user->id." and rt_content_type=2");
    //var_dump($result);
    $rt_user_uploaded_photo_count = count($result);
    if($rt_user_uploaded_photo_count){
    $partner_id     = get_site_option('bp_rt_kaltura_partner_id');
    $subp_id        = $partner_id."00"; //kaltura says sub partner id is = partnerID * 100;
    $secret         = get_site_option('bp_rt_kaltura_secret');
    $admin_secret   = get_site_option('bp_rt_kaltura_admin_secret');
    //define session variables
    $partnerUserID    = 'ANONYMOUS';
    //construct Kaltura objects for session initiation
    $kaltura_url = get_site_option('bp_rt_kaltura_url');
    $config           = new KalturaConfiguration($partner_id);
    $config->serviceUrl = $kaltura_url;
    $client           = new KalturaClient($config);
    $ks               = $client->session->start($admin_secret, $partnerUserID, KalturaSessionType::ADMIN);
    $client->setKs($ks);  // set the session in the client
    $filter = new KalturaMediaEntryFilter();
    $pager = new KalturaFilterPager();

    //kaltura's inbuild pagination is used
    if(isset($_GET['paged'])) {
        $pagenum = $pager->pageIndex = $_GET['paged'];
    }
    else {
            $pagenum = $pager->pageIndex = 1;
    }
            $per_page = 25; // per page image quantity..
            $list = $client->media->listAction($filter,$pager); // list all of the media items in the partnerID
            $pager->pageSize = $this_page_count = count($list->objects)."</br>";
            $filter->statusEqual = KalturaEntryStatus::READY;
            $filter->mediaTypeEqual = KalturaMediaType::IMAGE;
            $count = $rt_user_uploaded_photo_count;
            $thumbnailUrlimg = array();//store the results in temporary array..
            $dataurlforimg = array();
            $bp->displayed_user->id;
    /* 
     * Filter the media content userwise.
     */
    if(!empty($list)){

       for($i=0;$i<$this_page_count;$i++) {
            for($j=0;$j<$rt_user_uploaded_photo_count;$j++) {
                     if($list->objects[$i]->id == $result[$j]->rt_content_id ) {
                         array_push($thumbnailUrlimg , $list->objects[$i]->thumbnailUrl); //store the result of images in array...
                         array_push($dataurlforimg , $list->objects[$i]->dataUrl);//store dataurl in array
                         update_usermeta($bp->displayed_user->id,'rt_bp_kalturaimage',$thumbnailUrlimg); //store and updates image result in user meta table of DB..
                         update_usermeta($bp->displayed_user->id,'rt_bp_kalturagetdataurl',$dataurlforimg);//update dataURL

                }
            }
        }
    }

    for($i = ($pagenum-1)*$per_page; $i<$pagenum*$per_page; $i++){
        $thumbnailUrlimg = get_usermeta($bp->displayed_user->id,rt_bp_kalturaimage); // Getting data from the user
        $kalturagetdataurl = get_usermeta($bp->displayed_user->id,rt_bp_kalturagetdataurl);//getdataURL
            if($thumbnailUrlimg[$i]!=''){ // if for if images are null...

                ?>
                <a href="<?php echo $kalturagetdataurl[$i];?>.jpg"  class="thickbox" rel="gallery-plants">
                    <img src="<?php echo $thumbnailUrlimg[$i];?>.jpg" alt="Thumb" />
                </a>
                <?php
            }
        }
        /*
         * Part of Pagination.
         * Kaltura API supports pagination.
         */
        $page_links = paginate_links( array(
            'base' => add_query_arg( 'paged', '%#%' ),
            'format' => '',
            'prev_text' => __('&laquo;previous '),
            'next_text' => __('&raquo;'),
            'total' => ceil($count/ $per_page),
            'current' => $pagenum
        ));
        ?>
            <div class="tablenav">
                <div class="tablenav-pages"><?php $page_links_text = sprintf( '<span > ' . __( 'Displaying %s&#8211;%s of %s' ) . '</span><br>%s</>',
                        number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 ),
                        number_format_i18n( min( $pagenum * $per_page, $count) ),
                        number_format_i18n( $count),
                        $page_links
                    );
		    if(!empty($list)){

			echo $page_links_text;
		    }?>
                </div>
            </div>
<?php
    }
    else{
        echo "You have not uploaded Photos yet...You can upload it using Upload Widget..!";    }

}
/*
 * Ready Made widget is used here from http://katura.com
 */
function bp_rt_media_video_library() {
    global $wpdb,$current_user,$bp;
    /*
     * Get the userwise media content.
     */
    $result = $wpdb->get_results("SELECT * FROM wp_bp_rt_media
        WHERE rt_user_id = ".$bp->displayed_user->id." and rt_content_type=1");
    $rt_user_uploaded_video_count = count($result);
    if($rt_user_uploaded_video_count){
    $partner_id     = get_site_option('bp_rt_kaltura_partner_id');
    $subp_id        = $partner_id."00";
    $secret         = get_site_option('bp_rt_kaltura_secret');
    $admin_secret   = get_site_option('bp_rt_kaltura_admin_secret');
    $partnerUserID  = 'ANONYMOUS';
    $kaltura_url = get_site_option('bp_rt_kaltura_url');
    $config         = new KalturaConfiguration($partner_id);
    $config->serviceUrl = $kaltura_url;


    $client           = new KalturaClient($config);
    $ks               = $client->session->start($admin_secret, $partnerUserID, KalturaSessionType::ADMIN);
    $client->setKs($ks);  // set the session in the client

    $filter = new KalturaMediaEntryFilter();
    $filter->statusEqual = KalturaEntryStatus::READY;
    $filter->mediaTypeEqual = KalturaMediaType::VIDEO;

    $pager = new KalturaFilterPager();
    $list = $client->media->listAction($filter);  // list all of the media items in the partner
    $rt_kaltura_video_list_count =  count($list->objects);
    $rt_kaltura_video_user_wise_count = count($result);
    $rt_flag = 1;
    for($i=0;$i<$rt_kaltura_video_list_count;$i++){
        for($j=0;$j<$rt_kaltura_video_user_wise_count;$j++){
            if($list->objects[$i]->id==$result[$j]->rt_content_id){
                $rt_flag = 0;
                break;
            }
            else
                continue;
        }

        if($rt_flag == 1)
            unset($list->objects[$i]);
    }
    $entryId = $list->objects[0]->id;
    $player_width = 500;
    $player_height = 310;
    $autoPlay = "1";
    $backgroundColor = "000000";
    ?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.1/swfobject.js"></script>
<div id="asdf">
<object
    name="mykdp"
    id="mykdp"
    type="application/x-shockwave-flash"
    allowScriptAccess="always"
    allowNetworking="all"
    allowFullScreen="true"
    height="335" width="400"
    data="http://www.kaltura.com/index.php/kwidget/cache_st/1253598756/wid/_<?php echo $partner_id?>/uiconf_id/48410/entry_id/<?php echo $entryId?>">

    <param name="allowScriptAccess" value="always" />
    <param name="allowNetworking" value="all" />
    <param name="allowFullScreen" value="true" />
    <param name="bgcolor" value="#000000" />
    <param name="movie" value="http://www.kaltura.com/index.php/kwidget/cache_st/1253598756/wid/_<?php echo $partner_id?>/uiconf_id/48410/entry_id/<?php echo $entryId?>"/>
    <param name="flashVars" value=""/>
    <a href="http://corp.kaltura.com">video platform</a>
    <a href="http://corp.kaltura.com/technology/video_management">video management</a>
    <a href="http://corp.kaltura.com/solutions/overview">video solutions</a>
    <a href="http://corp.kaltura.com/technology/video_player">free video player</a>
    >
</object>
</div>
<div id="container" style="text-align:center;float:left;">
    <div class="navi"></div>
    <a class="prev_video"></a>
    <div class="scrollable">
        <div class="items">
                <?php foreach ($list->objects as $mediaEntry): ?>
                    <?php
                    $name        = $mediaEntry->name; // get the entry name
                    $id          = $mediaEntry->id;
                    $thumbUrl    = $mediaEntry->thumbnailUrl;  // get the entry thumbnail URL
                    $description = $mediaEntry->description;
                    ?>
            <a title="<?php echo $name; ?>" href="javascript:LoadMedia('<?php echo $id; ?>')"><img alt="Kaltura Thumbnail: <?php echo $name; ?>" title="<?php echo $name; ?>" src="<?php echo $thumbUrl; ?>" ></a>
                <?php endforeach; ?>
        </div>
    </div>
    <a class="next_video"></a>


</div>

<script language="javascript">
    function LoadMedia(entryId) {
        jQuery('#mykdp').get(0).insertMedia("-1",entryId,'true');
    }
</script>

<script>
    jQuery(function() {
        jQuery("div.scrollable").scrollable();
    });
</script>
<?php
    }else{
        echo "You have not uploaded Video yet...You can upload it using Upload Widget..!";    
    }

}
/*
 * Audio Library
 */
function bp_rt_media_audio_library() {
    $partner_id     = get_site_option('bp_rt_kaltura_partner_id');//33971;
    $subp_id        = $partner_id."00"; //kaltura says sub partner id is = partnerID * 100;
    $secret         = get_site_option('bp_rt_kaltura_secret');//'b1e47971818829dc375fe32a0db10d43';
    $admin_secret   = get_site_option('bp_rt_kaltura_admin_secret');//'1aa25c5cbe4ecb421530378e74a19650';

    //define session variables
    $partnerUserID    = 'ANONYMOUS';

    //construct Kaltura objects for session initiation
    $kaltura_url = get_site_option('bp_rt_kaltura_url');
    $config           = new KalturaConfiguration($partner_id);
    $config->serviceUrl = $kaltura_url;
    $client           = new KalturaClient($config);
    $ks               = $client->session->start($admin_secret, $partnerUserID, KalturaSessionType::ADMIN);
    $client->setKs($ks);  // set the session in the client

    $filter = new KalturaMediaEntryFilter();
    $filter->statusEqual = KalturaEntryStatus::READY;
    $filter->mediaTypeEqual = KalturaMediaType::AUDIO;
    $pager = new KalturaFilterPager();
    $pager->pageSize = 50;
    $pager->pageIndex = 1;
    $list = $client->media->listAction($filter); // list all of the media items in the partner

    $count = count($list->objects);

    $i=0;
    echo "Feature will be added soon...";

}
/*
 * Header tabs for the Media Component
 */
function rt_media_header_tabs() {
    global $bp;
    ?>
<li<?php if ( !strcasecmp($bp->current_action,'photo' )) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain.$bp->rt_media->slug ?>/photo"><?php _e( 'Photo', 'bp-events' ) ?></a></li>
<li<?php if ( !strcasecmp($bp->current_action,'video' )) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain.$bp->rt_media->slug ?>/video"><?php _e( 'Video', 'bp-events' ) ?></a></li>
<li<?php if ( !strcasecmp($bp->current_action,'audio' )) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain.$bp->rt_media->slug ?>/audio"><?php _e( 'Audio', 'bp-events' ) ?></a></li>
    <?php
    do_action( 'events_header_tabs' );
}

function rt_media_has_items( $args = '' ) {
    global $bp, $items_template;

	/***
	 * This function should accept arguments passes as a string, just the same
	 * way a 'query_posts()' call accepts parameters.
	 * At a minimum you should accept 'per_page' and 'max' parameters to determine
	 * the number of items to show per page, and the total number to return.
	 *
	 * e.g. bp_get_example_has_items( 'per_page=10&max=50' );
	 */

	/***
	 * Set the defaults for the parameters you are accepting via the "bp_get_example_has_items()"
	 * function call
	 */
    $defaults = array(
        'user_id' => false,
        'per_page' => 10,
        'max' => false,
        'type' => 'newest'
    );

	/***
	 * This function will extract all the parameters passed in the string, and turn them into
	 * proper variables you can use in the code - $per_page, $max
	 */
    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    $items_template = new rt_media_Template( $user_id, $type, $per_page, $max );

    return $items_template->has_items();
}

function rt_media_the_item() {
    global $items_template;
    return $items_template->the_item();
}

function rt_media_items() {
    global $items_template;
    return $items_template->user_items();
}

function rt_media_item_name() {
    echo rt_media_get_item_name();
}
	/* Always provide a "get" function for each template tag, that will return, not echo. */
function rt_media_get_item_name() {
    global $items_template;
    echo apply_filters( 'rt_media_get_item_name', $items_template->item->name ); // ): $items_template->item->name;
}

function rt_media_item_pagination() {
    echo rt_media_get_item_pagination();
}
function rt_media_get_item_pagination() {
    global $items_template;
    return apply_filters( 'rt_media_get_item_pagination', $items_template->pag_links );
}
