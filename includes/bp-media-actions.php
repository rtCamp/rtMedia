<?php

/**
 * 
 */
function bp_media_handle_uploads() {
	global $bp;
	if (isset($_POST['action']) && $_POST['action'] == 'wp_handle_upload') {
		if (isset($_FILES) && is_array($_FILES) && array_key_exists('bp_media_file', $_FILES) && $_FILES['bp_media_file']['name'] != '') {
			//$bp->{BP_MEDIA_SLUG}->messages['updated'][]='File '.$_FILES['bp_media_file']['name'].' was uploaded successfully';
			//$bp->{BP_MEDIA_SLUG}->messages['error'][]='Uploading function reached';
			//$bp->{BP_MEDIA_SLUG}->messages['updated'][]='Uploading function reached';
			//bp_core_add_message( __( 'No self-fives! :)', 'bp-example' ), 'error' );
			//include(admin_url('file.php'));
			$bp_media_entry = new BP_Media_Host_Wordpress();

			$entry = $bp_media_entry->add_media($_POST['bp_media_title'], $_POST['bp_media_description']);
			if ($entry === false)
				$bp->{BP_MEDIA_SLUG}->messages['error'][] = __('Media Upload failed, supported filetypes are .jpg, .png, .gif, .mp3 and .mp4', 'bp-media');
			else
				$bp->{BP_MEDIA_SLUG}->messages['updated'][] = __('Upload Successful', 'bp-media');
		}
		else {
			$bp->{BP_MEDIA_SLUG}->messages['error'][] = __('You did not specified a file to upload', 'bp-media');
		}
	}
}

add_action('bp_init', 'bp_media_handle_uploads');

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

function bp_media_enqueue_scripts_styles() {
	wp_enqueue_script('bp-media-mejs', plugins_url('includes/js/mediaelement-and-player.min.js', dirname(__FILE__)));
	wp_enqueue_script('bp-media-default', plugins_url('includes/js/bp-media.js', dirname(__FILE__)));
	wp_enqueue_style('bp-media-mecss', plugins_url('includes/css/mediaelementplayer.min.css', dirname(__FILE__)));
	wp_enqueue_style('bp-media-default', plugins_url('includes/css/bp-media-style.css', dirname(__FILE__)));
}

add_action('wp_enqueue_scripts', 'bp_media_enqueue_scripts_styles', 11);

/**
 * Deletes associated media entry and its files upon deletion of an activity.
 */
function bp_media_delete_activity_handler($activity_id, $user) {

	$post_id = bp_activity_get_meta($activity_id, 'bp_media_parent_post');
	$attachment_id = get_post_meta($post_id, 'bp_media_child_attachment', true);
	wp_delete_attachment($attachment_id, true);
	wp_delete_post($post_id, true);
}

/* Adds bp_media_delete_activity_handler() function to be called on bp_activity_before_action_delete_activity hook */
add_action('bp_activity_before_action_delete_activity', 'bp_media_delete_activity_handler', 10, 2);

/**
 * Called on bp_init by screen functions
 * @uses global $bp, $bp_media_query
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
 */
function bp_media_action_download_button() {
	echo '<a href="download" class="button item-button bp-secondary-action bp-media-download" title="Download">Download</a>';
}

/* Adds bp_media_action_download_button() function to be called on bp_activity_entry_meta hook */
add_action('bp_activity_entry_meta', 'bp_media_action_download_button');
?>