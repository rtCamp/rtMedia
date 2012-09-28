<?php
function bp_media_record_activity($args = '') {
	global $bp;
	if (!function_exists('bp_activity_add'))
		return false;
	$defaults = array(
		'id' => false, // Pass an existing activity ID to update an existing entry.
		'action' => '', // The activity action - e.g. "Jon Doe posted an update"
		'content' => '', // Optional: The content of the activity item e.g. "BuddyPress is awesome guys!"
		'component' => BP_MEDIA_SLUG, // The name/ID of the component e.g. groups, profile, mycomponent
		'type' => false, // The activity type e.g. activity_update, profile_updated
		'primary_link' => '', // Optional: The primary URL for this item in RSS feeds (defaults to activity permalink)
		'user_id' => $bp->loggedin_user->id, // Optional: The user to record the activity for, can be false if this activity is not for a user.
		'item_id' => false, // Optional: The ID of the specific item being recorded, e.g. a blog_id
		'secondary_item_id' => false, // Optional: A second ID used to further filter e.g. a comment_id
		'recorded_time' => bp_core_current_time(), // The GMT time that this activity was recorded
		'hide_sitewide' => false  // Should this be hidden on the sitewide activity stream?
	);
	add_filter('bp_activity_allowed_tags', 'bp_media_override_allowed_tags');
	$r = wp_parse_args($args, $defaults);
	extract($r);
	$activity_id = bp_activity_add(array('id' => $id, 'user_id' => $user_id, 'action' => $action, 'content' => $content, 'primary_link' => $primary_link, 'component' => $component, 'type' => $type, 'item_id' => $item_id, 'secondary_item_id' => $secondary_item_id, 'recorded_time' => $recorded_time, 'hide_sitewide' => $hide_sitewide));
	return $activity_id;
}

function bp_media_override_allowed_tags($activity_allowedtags) {
	$activity_allowedtags['video'] = array();
	$activity_allowedtags['video']['id'] = array();
	$activity_allowedtags['video']['class'] = array();
	$activity_allowedtags['video']['src'] = array();
	$activity_allowedtags['video']['height'] = array();
	$activity_allowedtags['video']['width'] = array();
	$activity_allowedtags['video']['controls'] = array();
	$activity_allowedtags['video']['preload'] = array();
	$activity_allowedtags['video']['alt'] = array();
	$activity_allowedtags['video']['title'] = array();
	$activity_allowedtags['audio'] = array();
	$activity_allowedtags['audio']['id'] = array();
	$activity_allowedtags['audio']['class'] = array();
	$activity_allowedtags['audio']['src'] = array();
	$activity_allowedtags['audio']['controls'] = array();
	$activity_allowedtags['audio']['preload'] = array();
	$activity_allowedtags['audio']['alt'] = array();
	$activity_allowedtags['audio']['title'] = array();
	$activity_allowedtags['script'] = array();
	$activity_allowedtags['script']['type'] = array();
	$activity_allowedtags['div'] = array();
	$activity_allowedtags['div']['id'] = array();
	$activity_allowedtags['div']['class'] = array();
	$activity_allowedtags['a'] = array();
	$activity_allowedtags['a']['title'] = array();
	$activity_allowedtags['a']['href'] = array();
	return $activity_allowedtags;
}

function bp_media_show_formatted_error_message($messages, $type) {
	echo '<div id="message" class="' . $type . '">';
	if (is_array($messages)) {
		foreach ($messages as $key => $message) {
			if (is_string($message)) {
				echo '<p>' . $message . '</p>';
			}
		}
	} else {
		if (is_string($messages)) {
			echo '<p>' . $messages . '</p>';
		}
	}
	echo '</div>';
}

function bp_media_conditional_override_allowed_tags($content, $activity=null) {
	if ($activity != null && $activity->type == 'media_upload') {
		add_filter('bp_activity_allowed_tags', 'bp_media_override_allowed_tags', 1);
	}
	return bp_activity_filter_kses($content);
}

function bp_media_swap_filters() {
	add_filter('bp_get_activity_content_body', 'bp_media_conditional_override_allowed_tags', 1, 2);
	remove_filter('bp_get_activity_content_body', 'bp_activity_filter_kses', 1);
}
add_action('bp_init', 'bp_media_swap_filters');

/**
 * Updates the media count of all users.
 */
function bp_media_update_count() {
	global $wpdb;
	$query = "SELECT COUNT(*) AS total,b.meta_value AS type,a.post_author 
		FROM $wpdb->posts AS a,$wpdb->postmeta AS b 
		WHERE (a.id = b.post_id) AND a.post_type='bp_media' AND b.meta_key='bp_media_type' 
		GROUP BY b.meta_value,a.post_author";
	$result = $wpdb->get_results($query);
	$users_count = array();
	foreach ($result as $obj) {
		$users_count[$obj->post_author][$obj->type] = $obj->total;
	}
	$users = get_users();
	foreach ($users as $user) {
		if (array_key_exists($user->ID, $users_count)) {
			$count = array(
				'images' => isset($users_count[$user->ID]['image']) ? intval($users_count[$user->ID]['image']) : 0,
				'videos' => isset($users_count[$user->ID]['video']) ? intval($users_count[$user->ID]['video']) : 0,
				'audio' => isset($users_count[$user->ID]['audio']) ? intval($users_count[$user->ID]['audio']) : 0,
			);
		} else {
			$count = array(
				'images' => 0,
				'videos' => 0,
				'audio' => 0
			);
		}
		bp_update_user_meta($user->ID, 'bp_media_count', $count);
	}
	return true;
}

function bp_media_update_media(){
	global $bp_media_current_entry;
	if($bp_media_current_entry->update_media(array('name'=> esc_html($_POST['bp_media_title']),'description'=> esc_html($_POST['bp_media_description'])))){
		@setcookie('bp-message', 'The media has been updated' , time() + 60 * 60 * 24, COOKIEPATH);
		@setcookie('bp-message-type', 'success' , time() + 60 * 60 * 24, COOKIEPATH);
		wp_redirect($bp_media_current_entry->get_url());
		exit;
	}
	else{
		@setcookie('bp-message', 'The media update failed' , time() + 60 * 60 * 24, COOKIEPATH);
		@setcookie('bp-message-type', 'error' , time() + 60 * 60 * 24, COOKIEPATH);
		wp_redirect($bp_media_current_entry->get_edit_url());
		exit;
	}
}

function bp_media_check_user() {
	if (bp_loggedin_user_id() != bp_displayed_user_id()) {
		bp_core_no_access(array(
			'message' => __('You do not have access to this page.', 'buddypress'),
			'root' => bp_displayed_user_domain(),
			'redirect' => false
		));
		exit;
	}
}

function bp_media_page_not_exist() {
	@setcookie('bp-message', 'The requested url does not exist' , time() + 60 * 60 * 24, COOKIEPATH);
	@setcookie('bp-message-type', 'error' , time() + 60 * 60 * 24, COOKIEPATH);
	wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG));
	exit;
}

/**
 * Display feeds from a specified Feed URL
 *
 * @param string $feed_url The Feed URL.
 *
 * @since BP Media 2.0
 */
function bp_media_get_feeds($feed_url = 'http://rtcamp.com/tag/buddypress/feed/') {
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

?>