<?php

/**
 * 
 */
function bp_media_add_admin_menu() {
	global $bp;
	if (!is_super_admin())
		return false;

	add_submenu_page('bp-general-settings', __('BuddyPress Media Component Settings', 'bp-media'), __('BP Media', 'bp-media'), 'manage_options', 'bp-media-settings', 'bp_media_admin_menu');
}

add_action(bp_core_admin_hook(), 'bp_media_add_admin_menu');

function bp_media_admin_menu() {
	$section = new RTL_Form_Section(array(
			'title' => 'BuddyPress Media Component Settings',
			'description' => 'Settings page for BP Media Component'
		));
	$section->add_field(new RTL_Form_Field(array(
			'name' => 'test',
			'label' => 'Test Field',
			'type' => 'text'
		)));

	$section->render();
	echo '</div>';
}

/**
 * Display feeds from a specified Feed URL
 *
 * @param string $feed_url The Feed URL.
 *
 * @since rtPanel 2.0
 */
function bp_media_get_feeds( $feed_url='http://rtcamp.com/blog/category/buddypress-media/feed/' ) {

    // Get RSS Feed(s)
    require_once( ABSPATH . WPINC . '/feed.php' );
    $maxitems = 0;
    // Get a SimplePie feed object from the specified feed source.
    $rss = fetch_feed( $feed_url );
    if ( !is_wp_error( $rss ) ) { // Checks that the object is created correctly

        // Figure out how many total items there are, but limit it to 5.
        $maxitems = $rss->get_item_quantity( 5 );

        // Build an array of all the items, starting with element 0 (first element).
        $rss_items = $rss->get_items( 0, $maxitems );
        
    } ?>
    <ul><?php
        if ( $maxitems == 0 ) {
            echo '<li>'.__( 'No items', 'rtPanel' ).'.</li>';
        } else {
            // Loop through each feed item and display each item as a hyperlink.
            foreach ( $rss_items as $item ) { ?>
                <li>
                    <a href='<?php echo $item->get_permalink(); ?>' title='<?php echo __( 'Posted ', 'rtPanel' ) . $item->get_date( 'j F Y | g:i a' ); ?>'><?php echo $item->get_title(); ?></a>
                </li><?php
            }
        } ?>
    </ul><?php
}

/**
 * Default rtPanel admin sidebar with metabox styling
 *
 * @return rtPanel_admin_sidebar
 *
 * @since rtPanel 2.0
 */
function bp_media_default_admin_sidebar() { ?>
    <div class="postbox" id="social">
        <div title="<?php _e('Click to toggle', 'rtPanel'); ?>" class="handlediv"><br /></div>
        <h3 class="hndle"><span><?php _e('Getting Social is Good', 'rtPanel'); ?></span></h3>
        <div class="inside" style="text-align:center;">
            <a href="<?php printf( '%s', 'http://www.facebook.com/rtPanel' ); ?>" target="_blank" title="<?php _e( 'Become a fan on Facebook', 'rtPanel' ); ?>" class="rtpanel-facebook"><?php _e( 'Facebook', 'rtPanel' ); ?></a>
            <a href="<?php printf( '%s', 'http://twitter.com/rtPanel' ); ?>" target="_blank" title="<?php _e( 'Follow us on Twitter', 'rtPanel' ); ?>" class="rtpanel-twitter"><?php _e( 'Twitter', 'rtPanel' ); ?></a>
            <a href="<?php printf( '%s', 'http://feeds.feedburner.com/rtpanel' ); ?>" target="_blank" title="<?php _e( 'Subscribe to our feeds', 'rtPanel' ); ?>" class="rtpanel-rss"><?php _e( 'RSS Feed', 'rtPanel' ); ?></a>
        </div>
    </div>

    <div class="postbox" id="donations">
        <div title="<?php _e('Click to toggle', 'rtPanel'); ?>" class="handlediv"><br /></div>
        <h3 class="hndle"><span><?php _e( 'Promote, Donate, Share', 'rtPanel' ); ?>...</span></h3>
        <div class="inside">
            <p><?php printf( __( 'Buy coffee/beer for team behind <a href="%s" title="rtPanel">rtPanel</a>.', 'rtPanel' ), 'http://rtcamp.com/rtpanel/' ); ?></p>
            <div class="rt-paypal" style="text-align:center">
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                    <input type="hidden" name="cmd" value="_donations" />
                    <input type="hidden" name="business" value="paypal@rtcamp.com" />
                    <input type="hidden" name="lc" value="US" />
                    <input type="hidden" name="item_name" value="BuddyPress Media Component" />
                    <input type="hidden" name="no_note" value="0" />
                    <input type="hidden" name="currency_code" value="USD" />
                    <input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest" />
                    <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online!" />
                    <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
                </form>
            </div>
            <div class="rt-social-share" style="text-align:center; width: 135px; margin: 2px auto">
                <div class="rt-facebook" style="float:left; margin-right:5px;">
                    <a style=" text-align:center;" name="fb_share" type="box_count" share_url="http://rtpanel.com/"></a>
                </div>
                <div class="rt-twitter" style="">
                    <a href="<?php printf( '%s', 'http://twitter.com/share' ); ?>"  class="twitter-share-button" data-text="I &hearts; #rtPanel"  data-url="http://rtcamp.com/rtpanel/" data-count="vertical" data-via="rtPanel"><?php _e( 'Tweet', 'rtPanel' ); ?></a>
                    <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>

    <div class="postbox" id="support">
        <div title="<?php _e( 'Click to toggle', 'rtPanel'); ?>" class="handlediv"><br /></div>
        <h3 class="hndle"><span><?php _e( 'Free Support', 'rtPanel' ); ?></span></h3>
        <div class="inside"><p><?php printf( __( ' If you are facing any problems while using rtPanel, or have good ideas for improvements, please discuss the same in our <a href="%s" target="_blank" title="Click here for rtPanel Free Support">Support forums</a>', 'rtPanel' ), 'http://rtcamp.com/support/forum/rtpanel/' ); ?>.</p></div>
    </div>

    <div class="postbox" id="latest_news">
        <div title="<?php _e( 'Click to toggle', 'rtPanel'); ?>" class="handlediv"><br /></div>
        <h3 class="hndle"><span><?php _e( 'Latest News', 'rtPanel' ); ?></span></h3>
        <div class="inside"><?php bp_media_get_feeds(); ?></div>
    </div><?php
}

?>