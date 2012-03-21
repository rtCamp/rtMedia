<?php

/**
 * In this file you should create and register widgets for your component.
 *
 * Widgets should be small, contained functionality that a site administrator can drop into
 * a widget enabled zone (column, sidebar etc)
 *
 * Good examples of suitable widget functionality would be short lists of updates or featured content.
 *
 * For example the friends and groups components have widgets to show the active, newest and most popular
 * of each.
 */
 
 /***
  * Localization Issues with BuddyPress Widgets
  *
  * NOTE: Although the WordPress widget API clearly advises developers not to use the widget functions starting
  * with wp_, there is an issue with localization of Widgets in BuddyPress if the register_sidebar_widget() is used.
  *
  * If you're not planning on distributing your custom components, then you can code your widgets with the
  * register_sidebar_widget() function call instead of wp_register_sidebar_widget(). However, if you plan to make your
  * custom component available to the community, you should use wp_register_sidebar_widget() at this time.
  *
  * See BuddyPress Changeset 1244 for more details 
  *
  * Two alternate ways:
  *
  * 	A: Will work fine but cause localization issues for others trying to translate
  *
  * 		register_sidebar_widget( __( 'Cool Example Widget', 'rt-bp-kaltura' ), 'rt_media_widget_cool_widget');
  * 		register_widget_control( __( 'Cool Example Widget', 'rt-bp-kaltura' ), 'rt_media_widget_cool_widget_control' );
  *
  * 	B: Addresses localization issues but is considered outdated by WordPress widget API
  *
  *			wp_register_sidebar_widget( 'buddypress-example', __( 'Cool Example Widget', 'rt-bp-kaltura' ), 'rt_media_widget_cool_widget');
  *			wp_register_widget_control( 'buddypress-example', __( 'Cool Example Widget', 'rt-bp-kaltura' ), 'rt_media_widget_cool_widget_control' );
  *
  *
  * @link http://codex.wordpress.org/Plugins/WordPress_Widgets_Api Widgets API
  */
  
 /*	NOTE: Once WPMU is updated to the WP2.8 codebase, you should consider the
	new widget API available here: http://codex.wordpress.org/Version_2.8#New_Widgets_API
*/
  
/**
 * bp_component_register_widgets()
 *
 * This function will register your widgets so that they will show up on the widget list
 * for site administrators to drop into their widget zones.
 */
function rt_media_register_widgets() {
	global $current_blog;
	/* Site welcome widget */
    wp_register_sidebar_widget( 'buddypress-example', __( 'Recently Added Pictures', 'rt-media' ), 'rt_media_widget_cool_widget');
    wp_register_widget_control( 'buddypress-example', __( 'Recently Added Pictures', 'rt-media' ), 'rt_media_widget_cool_widget_control' );
	
	/* Include the javascript and /or CSS needed for activated widgets only. If none needed, this code can be left out. */
	if ( is_active_widget( 'rt_media_widget_cool_widget' ) ) {
		wp_enqueue_script( 'rt_media_widget_cool_widget-js', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/js/widget-example.js', array('jquery', 'jquery-livequery-pack') );
		wp_enqueue_style( 'rt_media_widget_cool_widget-css', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/css/widget-example.css' );
	}
}
add_action( 'plugins_loaded', 'rt_media_register_widgets' );

/**
 * rt_media_widget_cool_widget()
 *
 * This function controls the actual HTML output of the widget. This is where you will
 * want to query whatever you need, and render the actual output.
 */
function rt_media_widget_cool_widget($args) {
    global $current_blog, $bp,$wpdb;
    extract($args);
	/***
	 * This is where you'll want to fetch the widget settings and use them to modify the
	 * widget's output.
	 */
	$options = get_blog_option( $current_blog->blog_id, 'rt_media_widget_cool_widget' );



?>
	<?php echo $before_widget; ?>
	<?php echo $before_title
		. $widget_name 
		. $after_title; ?>

	<?php
 	/* Consider using object caching here (see the bottom of 'rt-bp-kaltura.php' for more info)
	 * 
	 * Example:
	 *	
	 *	if ( empty( $options['max_groups'] ) || !$options['max_groups'] ) 
	 *		$options['max_groups'] = 5; 
	 *
	 *	if ( !$groups = wp_cache_get( 'popular_groups', 'bp' ) ) {
	 *		$groups = groups_get_popular( $options['max_groups'], 1 );
	 *		wp_cache_set( 'popular_groups', $groups, 'bp' );
	 *	}
	 *
	 */
    //    var_dump($bp);
//    echo "hi hello";
//    echo $options['max_events']."<br />";

    //global $wpdb,$current_user,$bp;


    //var_dump($bp);
    //echo "<br >displayed user id = ".$bp->displayed_user->id;
    //rt_content_id 	rt_content_type 	rt_user_id



    $query = "SELECT * FROM wp_rt_media WHERE rt_content_type=2 order by rt_created_at desc limit 0,$options[max_events]";
//    var_dump($query);
    $result = $wpdb->get_results($query);
    $rt_user_uploaded_photo_count = count($result);

//        var_dump($result);


    //    echo "<br />Photo Library<br />";
    $partner_id     = get_site_option('bp_rt_kaltura_partner_id');//33971 33890;//get_site_option('bp_rt_kaltura_partner_id')
    $subp_id        = $partner_id."00"; //kaltura says sub partner id is = partnerID * 100;
    $secret         = get_site_option('bp_rt_kaltura_secret');//'750166299b1554171056463fb4917fc0';//'b1e47971818829dc375fe32a0db10d43'; //
    $admin_secret   = get_site_option('bp_rt_kaltura_admin_secret');//'fd496ade9ed0646b2d8a74ae39eae8d5';//'1aa25c5cbe4ecb421530378e74a19650';//

    //define session variables
    $partnerUserID    = 'ANONYMOUS';

    //construct Kaltura objects for session initiation
    $config           = new KalturaConfiguration($partner_id);
    $client           = new KalturaClient($config);

    $ks               = $client->session->start($admin_secret, $partnerUserID, KalturaSessionType::ADMIN);
    $client->setKs($ks);  // set the session in the client

    $filter = new KalturaMediaEntryFilter();
    $filter->statusEqual = KalturaEntryStatus::READY;
    $filter->mediaTypeEqual = KalturaMediaType::IMAGE;
    //		$filter->tagsLike = "demos";

    $pager = new KalturaFilterPager();
    $pager->pageSize = 10;
    $pager->pageIndex = 1;
    $list = $client->media->listAction($filter); // list all of the media items in the partner

    $count = count($list->objects);

	?>
    <?php
    for($i=0;$i<$count;$i++) {
        for($j=0;$j<$rt_user_uploaded_photo_count;$j++) {
            if($list->objects[$i]->id == $result[$j]->rt_content_id){
             ?>

                    <a href="<?php echo $list->objects[$i]->dataUrl?>" title="">
                        <img src="<?php echo $list->objects[$i]->thumbnailUrl?>" alt="Thumb" />
                    </a>
	<?php
	
            }
        }
    }

    //            var_dump($result);
    //    var_dump($list);


    ?>




    <?php

	/***
	 * This is where you add your HTML and render what you want your widget to display.
	 */
	
	?>

	<?php echo $after_widget; ?>
<?php
}

/**
 * rt_media_widget_cool_widget_control()
 *
 * This function will enable a "edit" menu on your widget. This lets site admins click
 * the edit link on the widget to set options. The options you can then use in the display of 
 * your widget.
 *
 * For example, in the groups component widget there is a setting called "max-groups" where
 * a user can define how many groups they would like to display.
 */
function rt_media_widget_cool_widget_control() {
	global $current_blog;
	
	$options = $newoptions = get_blog_option( $current_blog->blog_id, 'rt_media_widget_cool_widget');

	if ( $_POST['rt-bp-kaltura-widget-cool-widget'] ) {
		$newoptions['option_name'] = strip_tags( stripslashes( $_POST['rt-bp-kaltura-widget-cool-widget-option'] ) );
	}
	
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_blog_option( $current_blog->blog_id, 'rt_media_widget_cool_widget', $options );
	}
?>
<p>
    <label for="rt-bp-kaltura-widget-cool-widget-option">
            <?php _e( 'Max number of Picuters to show:', 'rt-bp-kaltura' ); ?><br />
        <input class="widefat" id="rt-bp-kaltura-widget-cool-widget-option" name="rt-bp-kaltura-widget-cool-widget-option" type="text" value="<?php echo attribute_escape( $options['option_name'] ); ?>" style="width: 30%" />
    </label>
</p>
	<input type="hidden" id="rt-bp-kaltura-widget-cool-widget" name="rt-bp-kaltura-widget-cool-widget" value="1" />

<?php
}
?>