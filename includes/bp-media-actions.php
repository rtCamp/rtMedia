<?php
/**
 * Handles the uploads and creates respective posts for the upload
 * 
 * @since BP Media 2.0
 */
function bp_media_handle_uploads() {
	global $bp;
	if (isset($_POST['action']) && $_POST['action'] == 'wp_handle_upload') {
		if (isset($_FILES) && is_array($_FILES) && array_key_exists('bp_media_file', $_FILES) && $_FILES['bp_media_file']['name'] != '') {
			$bp_media_entry = new BP_Media_Host_Wordpress();
			try {
				$title = isset($_POST['bp_media_title']) ? ($_POST['bp_media_title'] != "") ? $_POST['bp_media_title'] : pathinfo($_FILES['bp_media_file']['name'], PATHINFO_FILENAME) : pathinfo($_FILES['bp_media_file']['name'], PATHINFO_FILENAME);
				$entry = $bp_media_entry->add_media($title, $_POST['bp_media_description']);
				$bp->{BP_MEDIA_SLUG}->messages['updated'][] = __('Upload Successful', 'bp-media');
			} catch (Exception $e) {
				$bp->{BP_MEDIA_SLUG}->messages['error'][] = $e->getMessage();
			}
		} else {
			$bp->{BP_MEDIA_SLUG}->messages['error'][] = __('You did not specified a file to upload', 'bp-media');
		}
	}
}
add_action('bp_init', 'bp_media_handle_uploads');

/**
 * Displays the messages that other functions/methods creates according to the BuddyPress' formating
 * 
 * @since BP Media 2.0
 */
function bp_media_show_messages() {
	global $bp;
	if (is_array($bp->{BP_MEDIA_SLUG}->messages)) {
		$types = array('error', 'updated', 'info');
		foreach ($types as $type) {
			if (count($bp->{BP_MEDIA_SLUG}->messages[$type]) > 0) {
				bp_media_show_formatted_error_message($bp->{BP_MEDIA_SLUG}->messages[$type], $type);
			}
		}
	}
}
add_action('bp_media_before_content', 'bp_media_show_messages');

/**
 * Enqueues all the required scripts and stylesheets for the proper working of BuddyPress Media Component
 * 
 * @since BP Media 2.0
 */
function bp_media_enqueue_scripts_styles() {
	wp_enqueue_script('bp-media-mejs', plugins_url('includes/media-element/mediaelement-and-player.min.js', dirname(__FILE__)));
	wp_enqueue_script('bp-media-default', plugins_url('includes/js/bp-media.js', dirname(__FILE__)));
	wp_enqueue_style('bp-media-mecss', plugins_url('includes/media-element/mediaelementplayer.min.css', dirname(__FILE__)));
	wp_enqueue_style('bp-media-default', plugins_url('includes/css/bp-media-style.css', dirname(__FILE__)));
}

add_action('wp_enqueue_scripts', 'bp_media_enqueue_scripts_styles', 11);

/**
 * Deletes associated media entry and its files upon deletion of an activity.
 * 
 * @since BP Media 2.0
 */
function bp_media_delete_activity_handler($activity_id, $user) {
	global $bp_media_count;
	bp_media_init_count(bp_loggedin_user_id());
	$post_id = bp_activity_get_meta($activity_id, 'bp_media_parent_post');
	$type = get_post_meta($post_id, 'bp_media_type', true);
	switch ($type) {
		case 'image':
			$bp_media_count['images'] = intval($bp_media_count['images']) - 1;
			break;
		case 'video':
			$bp_media_count['videos'] = intval($bp_media_count['videos']) - 1;
			break;
		case 'audio':
			$bp_media_count['audio'] = intval($bp_media_count['audio']) - 1;
			break;
	}
	$attachment_id = get_post_meta($post_id, 'bp_media_child_attachment', true);
	wp_delete_attachment($attachment_id, true);
	wp_delete_post($post_id, true);
	bp_update_user_meta(bp_loggedin_user_id(), 'bp_media_count', $bp_media_count);
}

/* Adds bp_media_delete_activity_handler() function to be called on bp_activity_before_action_delete_activity hook */
add_action('bp_activity_before_action_delete_activity', 'bp_media_delete_activity_handler', 10, 2);

/**
 * Called on bp_init by screen functions
 * 
 * @uses global $bp, $bp_media_query
 * 
 * @since BP Media 2.0
 */
function bp_media_set_query() {
	global $bp, $bp_media_query;
	switch ($bp->current_action) {
		case BP_MEDIA_IMAGES_SLUG:
			$type = 'image';
			break;
		case BP_MEDIA_AUDIO_SLUG:
			$type = 'audio';
			break;
		case BP_MEDIA_VIDEOS_SLUG:
			$type = 'video';
			break;
		default :
			$type = null;
	}
	if (isset($bp->action_variables) && is_array($bp->action_variables) && isset($bp->action_variables[0]) && $bp->action_variables[0] == 'page' && isset($bp->action_variables[1]) && is_numeric($bp->action_variables[1])) {
		$paged = $bp->action_variables[1];
	} else {
		$paged = 1;
	}
	if ($type) {
		$args = array(
			'post_type' => 'bp_media',
			'author' => $bp->displayed_user->id,
			'meta_key' => 'bp_media_type',
			'meta_value' => $type,
			'meta_compare' => 'LIKE',
			'paged' => $paged
		);
		$bp_media_query = new WP_Query($args);
	}
}

/**
 * Adds a download button on single entry pages of media files.
 * 
 * @since BP Media 2.0
 */
function bp_media_action_download_button() {
	if(!in_array('bp_media_current_entry', $GLOBALS))
		return false;
	global $bp_media_current_entry;
	if($bp_media_current_entry!=NULL)
		echo '<a href="'.$bp_media_current_entry->get_attachment_url().'" class="button item-button bp-secondary-action bp-media-download" title="Download">Download</a>';
}
add_action('bp_activity_entry_meta', 'bp_media_action_download_button'); 

/* Should be used with Content Disposition Type for media files set to attachment */

/**
 * Shows the media count of a user in the tabs
 * 
 * @since BP Media 2.0
 */
function bp_media_init_count($user = null) {
	global $bp_media_count;
	if (!$user)
		$user = bp_displayed_user_id();
	if ($user < 1) {
		$bp_media_count = null;
		return false;
	}
	$count = bp_get_user_meta($user, 'bp_media_count', true);
	if (!$count) {
		$bp_media_count = array('images' => 0, 'videos' => 0, 'audio' => 0);
		bp_update_user_meta($user, 'bp_media_count', $bp_media_count);
	} else {
		$bp_media_count = $count;
	}
	add_filter('bp_get_displayed_user_nav_' . BP_MEDIA_SLUG, 'bp_media_items_count_filter', 10, 2);

	if (bp_current_component() == BP_MEDIA_SLUG) {
		add_filter('bp_get_options_nav_' . BP_MEDIA_IMAGES_SLUG, 'bp_media_items_count_filter', 10, 2);
		add_filter('bp_get_options_nav_' . BP_MEDIA_VIDEOS_SLUG, 'bp_media_items_count_filter', 10, 2);
		add_filter('bp_get_options_nav_' . BP_MEDIA_AUDIO_SLUG, 'bp_media_items_count_filter', 10, 2);
	}
	return true;
}
add_action('init', 'bp_media_init_count');

/**
 * Displays the footer of the BP Media Plugin if enabled through the dashboard options page
 * 
 * @since BP Media 2.0
 */
function bp_media_footer() { ?>
	<div id="bp-media-footer"><p>We &hearts; <a href="http://rtcamp.com/buddypress-media/">MediaBP</a></p></div>
	<?php
}
if(get_option('bp_media_remove_linkback')!='1')
	add_action('bp_footer','bp_media_footer');
?>