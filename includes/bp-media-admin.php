<?php
/**
 * Add the BuddyPress Media Component's options menu in the BuddyPress' options subnavigation.
 * 
 * @since BP Media 2.0
 */
function bp_media_add_admin_menu() {
	global $bp;
	if (!is_super_admin())
		return false;

	$page = add_submenu_page('bp-general-settings', __('BuddyPress Media Component Settings', 'bp-media'), __('MediaBP', 'bp-media'), 'manage_options', 'bp-media-settings', 'bp_media_admin_menu'
	);
	add_action('admin_print_styles-' . $page, 'bp_media_admin_enqueue');
}
add_action(bp_core_admin_hook(), 'bp_media_add_admin_menu');

/**
 * Displays and updates the options menu of BuddyPress Media Component
 * 
 * @since BP Media 2.0
 */
function bp_media_admin_menu() {
	$bp_media_errors=array();
	$bp_media_messages=array();
	
	if(array_key_exists('submit', $_POST)){
		check_admin_referer('bp_media_update_options');
		if(array_key_exists('refresh_media_count', $_POST)){
			if(!bp_media_update_count())
				$bp_media_errors[]="Recounting Failed";
			else
				$bp_media_messages[]="Recounting of media files done successfully";
		}
		if(array_key_exists('remove_linkback', $_POST)&&$_POST['remove_linkback']=='1'){
			update_option('bp_media_remove_linkback', '1');
		}
		else{
			update_option('bp_media_remove_linkback', '0');
		}
	}
	?>
	<div class="metabox-fixed metabox-holder alignright">
		<?php bp_media_default_admin_sidebar(); ?>
	</div>
	<div class="wrap bp-media-admin">
		<div id="icon-bp-media" class="icon32"><br/></div>
		<h2>BuddyPress Media Component Settings</h2>
		<?php if(count($bp_media_errors)) { ?>
		<div class="error"><p><?php foreach($bp_media_errors as $error) echo $error.'<br/>'; ?></p></div>
		<?php } if(count($bp_media_messages)){?>
		<div class="updated"><p><?php foreach($bp_media_messages as $message) echo $message.'<br/>'; ?></p></div>
		<?php }?>
		<form method="post">
			 <?php wp_nonce_field( 'bp_media_update_options' ); ?>
			<table class="form-table ">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="refresh_media_count">Re-Count Media Entries</label></th>
						<td> <fieldset><legend class="screen-reader-text"><span>Re-Count Media Entries</span></legend><label for="refresh_media_count">
									<input name="refresh_media_count" type="checkbox" id="refresh_media_count" value="1">
									Check for Re-Count</label>
							</fieldset></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="remove_linkback">Remove Linkback</label></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span>Remove Linkback</span></legend>
								<label for="remove_linkback"><input name="remove_linkback" type="checkbox" id="remove_linkback" value="1" <?php if(get_option('bp_media_remove_linkback')=='1') echo 'checked="checked"' ?>> Removes the link to MediaBP from footer</label>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"></p></form>
	</div>
	<?php
}

/**
 * Display feeds from a specified Feed URL
 *
 * @param string $feed_url The Feed URL.
 *
 * @since BP Media 2.0
 */
function bp_media_get_feeds($feed_url = 'http://rtcamp.com/blog/category/buddypress-media/feed/') {
	// Get RSS Feed(s)
	require_once( ABSPATH . WPINC . '/feed.php' );
	$maxitems = 0;
	// Get a SimplePie feed object from the specified feed source.
	$rss = fetch_feed($feed_url);
	if (!is_wp_error($rss)) { // Checks that the object is created correctly
		// Figure out how many total items there are, but limit it to 5.
		$maxitems = $rss->get_item_quantity(5);

		// Build an array of all the items, starting with element 0 (first element).
		$rss_items = $rss->get_items(0, $maxitems);
	}
	?>
	<ul><?php
	if ($maxitems == 0) {
		echo '<li>' . __('No items', 'bp-media') . '.</li>';
	} else {
		// Loop through each feed item and display each item as a hyperlink.
		foreach ($rss_items as $item) {
			?>
				<li>
					<a href='<?php echo $item->get_permalink(); ?>' title='<?php echo __('Posted ', 'bp-media') . $item->get_date('j F Y | g:i a'); ?>'><?php echo $item->get_title(); ?></a>
				</li><?php
		}
	}
	?>
	</ul><?php
}

/**
 * Default BuddyPress Media Component admin sidebar with metabox styling
 * 
 * @since BP Media 2.0
 */
function bp_media_default_admin_sidebar() {
	?>
	<div class="postbox" id="social">
		<div title="<?php _e('Click to toggle', 'bp-media'); ?>" class="handlediv"><br /></div>
		<h3 class="hndle"><span><?php _e('Getting Social is Good', 'bp-media'); ?></span></h3>
		<div class="inside" style="text-align:center;">
			<a href="<?php printf('%s', 'http://www.facebook.com/rtCamp.solutions/'); ?>" target="_blank" title="<?php _e('Become a fan on Facebook', 'bp-media'); ?>" class="bp-media-facebook bp-media-social"><?php _e('Facebook', 'bp-media'); ?></a>
			<a href="<?php printf('%s', 'https://twitter.com/rtcamp/'); ?>" target="_blank" title="<?php _e('Follow us on Twitter', 'bp-media'); ?>" class="bp-media-twitter bp-media-social"><?php _e('Twitter', 'bp-media'); ?></a>
			<a href="<?php printf('%s', 'http://feeds.feedburner.com/rtcamp/'); ?>" target="_blank" title="<?php _e('Subscribe to our feeds', 'bp-media'); ?>" class="bp-media-rss bp-media-social"><?php _e('RSS Feed', 'bp-media'); ?></a>
		</div>
	</div>

	<div class="postbox" id="donations">
		<div title="<?php _e('Click to toggle', 'bp-media'); ?>" class="handlediv"><br /></div>
		<h3 class="hndle"><span><?php _e('Promote, Donate, Share', 'bp-media'); ?>...</span></h3>
		<div class="inside">
			<p><?php printf(__('Buy coffee/beer for team behind <a href="%s" title="BuddyPress Media Component">BuddyPress Media Component</a>.', 'bp-media'), 'http://rtcamp.com/buddypress-media/'); ?></p>
			<div class="bp-media-paypal" style="text-align:center">
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
					<a style=" text-align:center;" name="fb_share" type="box_count" share_url="http://rtcamp.com/buddypress-media/"></a>
				</div>
				<div class="rt-twitter" style="">
					<a href="<?php printf('%s', 'http://twitter.com/share'); ?>"  class="twitter-share-button" data-text="I &hearts; #mediabp"  data-url="http://rtcamp.com/buddypress-media/" data-count="vertical" data-via="mediabp"><?php _e('Tweet', 'bp-media'); ?></a>
					<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>

	<div class="postbox" id="support">
		<div title="<?php _e('Click to toggle', 'bp-media'); ?>" class="handlediv"><br /></div>
		<h3 class="hndle"><span><?php _e('Free Support', 'bp-media'); ?></span></h3>
		<div class="inside"><p><?php printf(__(' If you are facing any problems while using BuddyPress Media Component, or have good ideas for improvements, please discuss the same in our <a href="%s" target="_blank" title="Click here for BuddyPress Media Component Free Support">Support forums</a>', 'bp-media'), 'http://rtcamp.com/support/forum/buddypress-media/'); ?>.</p></div>
	</div>

	<div class="postbox" id="latest_news">
		<div title="<?php _e('Click to toggle', 'bp-media'); ?>" class="handlediv"><br /></div>
		<h3 class="hndle"><span><?php _e('Latest News', 'bp-media'); ?></span></h3>
		<div class="inside"><?php bp_media_get_feeds(); ?></div>
	</div><?php
}

/**
 * Enqueues the scripts and stylesheets needed for the BuddyPress Media Component's options page
 */
function bp_media_admin_enqueue() {
	wp_enqueue_style('bp-media-admin-style', plugins_url('includes/css/bp-media-admin.css', dirname(__FILE__)));
	wp_enqueue_script('rt-fb-share', ('http://static.ak.fbcdn.net/connect.php/js/FB.Share'), '', '', true);
}
add_action('admin_enqueue_scripts', 'bp_media_admin_enqueue');
?>