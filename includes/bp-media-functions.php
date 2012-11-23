<?php
function bp_media_record_activity($args = '') {
	global $bp;
	if (!function_exists('bp_activity_add'))
		return false;
	$defaults = array(
		'component' => BP_MEDIA_SLUG, // The name/ID of the component e.g. groups, profile, mycomponent
	);
	add_filter('bp_activity_allowed_tags', 'bp_media_override_allowed_tags');        
	$r = wp_parse_args($args, $defaults);        
	$activity_id = bp_activity_add($r);
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
	$activity_allowedtags['ul'] = array();
	$activity_allowedtags['li'] = array();

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
	global $bp_media_activity_types;
	if ($activity != null && in_array($activity->type,$bp_media_activity_types)) {
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
	$query =
	"SELECT
		post_author,
		SUM(CASE WHEN post_mime_type LIKE 'image%' THEN 1 ELSE 0 END) as Images,
		SUM(CASE WHEN post_mime_type LIKE 'audio%' THEN 1 ELSE 0 END) as Audio,
		SUM(CASE WHEN post_mime_type LIKE 'video%' THEN 1 ELSE 0 END) as Videos,
		SUM(CASE WHEN post_type LIKE 'bp_media_album' THEN 1 ELSE 0 END) as Albums,
		COUNT(*) as Total
	FROM
		$wpdb->posts RIGHT JOIN $wpdb->postmeta on wp_postmeta.post_id = wp_posts.id
	WHERE
		`meta_key` = 'bp-media-key' AND
		`meta_value` > 0 AND
		( post_mime_type LIKE 'image%' OR post_mime_type LIKE 'audio%' OR post_mime_type LIKE 'video%' OR post_type LIKE 'bp_media_album')
	GROUP BY post_author";
	$result = $wpdb->get_results($query);
	if(!is_array($result))
		return false;

	foreach ($result as $obj) {

		$count = array(
			'images' => isset($obj->Images)?$obj->Images:0,
			'videos' => isset($obj->Videos)?$obj->Videos:0,
			'audio' => isset($obj->Audio)?$obj->Audio:0,
			'albums'=>	isset($obj->Albums)?$obj->Albums:0
			);
		bp_update_user_meta($obj->post_author, 'bp_media_count', $count);
	}
	return true;
}

function bp_media_update_media(){
	global $bp_media_current_entry;
	if($bp_media_current_entry->update_media(array('description'=> esc_html($_POST['bp_media_description']),'name'=>esc_html($_POST['bp_media_title'])))){
                $bp_media_current_entry->update_media_activity();
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

function bp_media_update_album_activity($album,$current_time = true,$delete_media_id = null){
	if(!is_object($album)){
		$album = new BP_Media_Album($album);
	}
	$args = array(
		'post_parent' => $album->get_id(),
		'numberposts'	=>	4,
		'post_type'	=>	'attachment',
	);
	if($delete_media_id)
		$args['exclude'] = $delete_media_id;
	$attachments = get_posts($args);
	if(is_array($attachments)){
		$content = '<ul>';
		foreach($attachments as $media){
			$bp_media = new BP_Media_Host_Wordpress($media->ID);
			$content .= $bp_media->get_album_activity_content();
		}
		$content .= '</ul>';
		$activity_id = get_post_meta($album->get_id(), 'bp_media_child_activity');
		if($activity_id){
			$args = array(
				'in' => $activity_id,
			);

			$activity = @bp_activity_get($args);
			if(isset($activity['activities'][0]->id)){
				$args = array(
					'content'	=>	$content,
					'id'	=>	$activity_id,
					'type' => 'album_updated',
					'user_id' => $activity['activities'][0]->user_id,
					'action' => apply_filters( 'bp_media_filter_album_updated', sprintf( __( '%1$s added new media in album %2$s', 'bp-media'), bp_core_get_userlink( $activity['activities'][0]->user_id ), '<a href="' . $album->get_url() . '">' . $album->get_title() . '</a>' ) ),
					'component' => BP_MEDIA_SLUG, // The name/ID of the component e.g. groups, profile, mycomponent
					'primary_link' => $activity['activities'][0]->primary_link,
					'item_id' => $activity['activities'][0]->item_id,
					'secondary_item_id' => $activity['activities'][0]->secondary_item_id,
					'recorded_time' => $current_time? bp_core_current_time(): $activity['activities'][0]->date_recorded,
					'hide_sitewide' => $activity['activities'][0]->hide_sitewide
				);
				bp_media_record_activity($args);
			}
		}
	}
}

function bp_media_wp_comment_form_mod() {
	global $bp_media_current_entry;
	echo '<input type="hidden" name="redirect_to" value="'.$bp_media_current_entry->get_url().'">' ;
}
?>