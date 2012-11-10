<?php

/**
 * Screens for all the slugs defined in the BuddyPress Media
 */

/* Exit if accessed directlly. */
if (!defined('ABSPATH'))
	exit;

/**
 * Screen function for Upload page
 */
function bp_media_upload_screen() {
	add_action('wp_enqueue_scripts','bp_media_upload_enqueue');
	add_action('bp_template_title', 'bp_media_upload_screen_title');
	add_action('bp_template_content', 'bp_media_upload_screen_content');
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

/**
 * Screen function to display upload screen title
 */
function bp_media_upload_screen_title() {
	_e('Upload Media');
}

/**
 * Screen function to display upload screen content
 */
function bp_media_upload_screen_content() {
	do_action('bp_media_before_content');
	bp_media_show_upload_form_multiple();
	do_action('bp_media_after_content');
}

/**
 * Screen function for Images listing page (Default)
 */
function bp_media_images_screen() {
	global $bp;
	remove_filter('bp_activity_get_user_join_filter','bp_media_activity_query_filter',10);
	if (isset($bp->action_variables[0])) {
		switch ($bp->action_variables[0]) {
			case BP_MEDIA_IMAGES_EDIT_SLUG :
				bp_media_images_edit_screen();
				break;
			case BP_MEDIA_IMAGES_ENTRY_SLUG:
				global $bp_media_current_entry;
				if(!isset($bp->action_variables[1])){
					bp_media_page_not_exist();
				}
				try {
					$bp_media_current_entry = new BP_Media_Host_Wordpress($bp->action_variables[1]);
					if($bp_media_current_entry->get_author()!=  bp_displayed_user_id())
						throw new Exception(__('Sorry, the requested media does not belong to the user'));
				} catch (Exception $e) {
					/* Send the values to the cookie for page reload display */
					if(isset($_COOKIE['bp-message'])&&$_COOKIE['bp-message']!=''){
						@setcookie('bp-message', $_COOKIE['bp-message'], time() + 60 * 60 * 24, COOKIEPATH);
						@setcookie('bp-message-type', $_COOKIE['bp-message-type'], time() + 60 * 60 * 24, COOKIEPATH);
					}
					else{
						@setcookie('bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH);
						@setcookie('bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH);
					}
					wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG));
					exit;
				}
				add_action('bp_template_title', 'bp_media_images_entry_screen_title');
				add_action('bp_template_content', 'bp_media_images_entry_screen_content');
				break;
			case BP_MEDIA_DELETE_SLUG :
				if(!isset($bp->action_variables[1])){
					bp_media_page_not_exist();
				}
				bp_media_entry_delete();
				break;
			default:
				bp_media_set_query();
				add_action('bp_template_content', 'bp_media_images_screen_content');
		}
	} else {
		bp_media_set_query();
		add_action('bp_template_content', 'bp_media_images_screen_content');
	}
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

/**
 * Screen function to display images screen title
 */
function bp_media_images_screen_title() {
	_e('Images List Page');
}

/**
 * Screen function to display images screen content
 */
function bp_media_images_screen_content() {
	global $bp_media_query;
	if ($bp_media_query && $bp_media_query->have_posts()):
		do_action('bp_media_before_content');
		echo '<ul id="bp-media-list" class="bp-media-gallery item-list">';
		while ($bp_media_query->have_posts()) : $bp_media_query->the_post();
			bp_media_the_content();
		endwhile;
		echo '</ul>';
		bp_media_display_show_more();
		do_action('bp_media_after_content');
	else:
		bp_media_show_formatted_error_message(__('Sorry, no images were found.', 'bp-media'), 'info');
	endif;
}

/**
 * Screen function for Images Edit page
 */
function bp_media_images_edit_screen() {
	global $bp_media_current_entry,$bp;
	if(!isset($bp->action_variables[1])){
		bp_media_page_not_exist();
	}
	//Creating global bp_media_current_entry for later use
	try {
		$bp_media_current_entry = new BP_Media_Host_Wordpress($bp->action_variables[1]);

	} catch (Exception $e) {
		/* Send the values to the cookie for page reload display */
		@setcookie('bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH);
		@setcookie('bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH);
		wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG));
		exit;
	}
	bp_media_check_user();

	//For saving the data if the form is submitted
	if(array_key_exists('bp_media_title', $_POST)){
		bp_media_update_media();
	}
	add_action('bp_template_title', 'bp_media_images_edit_screen_title');
	add_action('bp_template_content', 'bp_media_images_edit_screen_content');
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

/**
 * Screen function to dipslay edit images screen title
 */
function bp_media_images_edit_screen_title() {
	_e('Edit Image','bp-media');
}

/**
 * Screen function to display edit image screen content
 */
function bp_media_images_edit_screen_content() {
	global $bp, $bp_media_current_entry,$bp_media_default_excerpts;
	?>
	<form method="post" class="standard-form" id="bp-media-upload-form">
		<label for="bp-media-upload-input-title"><?php _e('Image Title', 'bp-media'); ?></label><input id="bp-media-upload-input-title" type="text" name="bp_media_title" class="settings-input" maxlength="<?php echo max(array($bp_media_default_excerpts['single_entry_title'],$bp_media_default_excerpts['activity_entry_title'])) ?>" value="<?php echo $bp_media_current_entry->get_title(); ?>" />
		<label for="bp-media-upload-input-description"><?php _e('Image Description', 'bp-media'); ?></label><input id="bp-media-upload-input-description" type="text" name="bp_media_description" class="settings-input" maxlength="<?php echo max(array($bp_media_default_excerpts['single_entry_description'],$bp_media_default_excerpts['activity_entry_description'])) ?>" value="<?php echo $bp_media_current_entry->get_content(); ?>" />
		<div class="submit"><input type="submit" class="auto" value="Update" /><a href="<?php echo $bp_media_current_entry->get_url(); ?>" class="button" title="Back to Media File">Back to Media</a></div>
	</form>
	<?php
}

/**
 * Screen function for Images Entry page
 */
function bp_media_images_entry_screen() {
	add_action('bp_template_title', 'bp_media_images_entry_screen_title');
	add_action('bp_template_content', 'bp_media_images_entry_screen_content');
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_images_entry_screen_title() {
	global $bp_media_current_entry;
	/** @var $bp_media_current_entry BP_Media_Host_Wordpress */
	if(is_object($bp_media_current_entry))
		echo $bp_media_current_entry->get_media_single_title();
	_e('Images Entry Page');
}

function bp_media_images_entry_screen_content() {
	global $bp, $bp_media_current_entry,$bp_media_options;
	if (!$bp->action_variables[0] == BP_MEDIA_IMAGES_ENTRY_SLUG)
		return false;
	do_action('bp_media_before_content');
	echo '<div class="bp-media-single bp-media-image">';
	echo $bp_media_current_entry->get_media_single_content();
	echo $bp_media_current_entry->show_comment_form();
	echo '</div>';
	do_action('bp_media_after_content');
}

/**
 * Screen function for Videos listing page (Default)
 */
function bp_media_videos_screen() {
	global $bp;
	remove_filter('bp_activity_get_user_join_filter','bp_media_activity_query_filter',10);
	if (isset($bp->action_variables[0])) {
		switch ($bp->action_variables[0]) {
			case BP_MEDIA_VIDEOS_EDIT_SLUG :
				bp_media_video_edit_screen();
				break;
			case BP_MEDIA_VIDEOS_ENTRY_SLUG:
				global $bp_media_current_entry;
				if (!$bp->action_variables[0] == BP_MEDIA_IMAGES_ENTRY_SLUG)
					return false;
				try {
					$bp_media_current_entry = new BP_Media_Host_Wordpress($bp->action_variables[1]);
				} catch (Exception $e) {
					/* Send the values to the cookie for page reload display */
					@setcookie('bp-message', $_COOKIE['bp-message'], time() + 60 * 60 * 24, COOKIEPATH);
					@setcookie('bp-message-type', $_COOKIE['bp-message-type'], time() + 60 * 60 * 24, COOKIEPATH);
					wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_VIDEOS_SLUG));
					exit;
				}
				add_action('bp_template_content', 'bp_media_videos_entry_screen_content');
				break;
			case BP_MEDIA_DELETE_SLUG :
				if(!isset($bp->action_variables[1])){
					bp_media_page_not_exist();
				}
				bp_media_entry_delete();
				break;
			case '206':
				wp_redirect('http://google.com');
			default:
				bp_media_set_query();
				add_action('bp_template_content', 'bp_media_videos_screen_content');
		}
	} else {
		bp_media_set_query();
		add_action('bp_template_content', 'bp_media_videos_screen_content');
	}
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_videos_screen_title() {
	_e('Videos List Page');
}

function bp_media_videos_screen_content() {
	global $bp_media_query;
	if ($bp_media_query && $bp_media_query->have_posts()):
		do_action('bp_media_before_content');
		echo '<ul id="bp-media-list" class="bp-media-gallery item-list">';
		while ($bp_media_query->have_posts()) : $bp_media_query->the_post();
			bp_media_the_content();
		endwhile;
		echo '</ul>';
		bp_media_display_show_more();
		do_action('bp_media_after_content');
	else:
		bp_media_show_formatted_error_message(__('Sorry, no videos were found.', 'bp-media'), 'info');
	endif;
}

/**
 * Screen function for Videos Edit page
 */
function bp_media_videos_edit_screen() {
	global $bp_media_current_entry,$bp;
	if(!isset($bp->action_variables[1])){
		bp_media_page_not_exist();
	}
	//Creating global bp_media_current_entry for later use
	try {
		$bp_media_current_entry = new BP_Media_Host_Wordpress($bp->action_variables[1]);
	} catch (Exception $e) {
		/* Send the values to the cookie for page reload display */
		@setcookie('bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH);
		@setcookie('bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH);
		wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG));
		exit;
	}
	bp_media_check_user();

	//For saving the data if the form is submitted
	if(array_key_exists('bp_media_title', $_POST)){
		bp_media_update_media();
	}
	add_action('bp_template_title', 'bp_media_videos_edit_screen_title');
	add_action('bp_template_content', 'bp_media_videos_edit_screen_content');
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_videos_edit_screen_title() {
	_e('Edit Video');
}

function bp_media_videos_edit_screen_content() {
	global $bp, $bp_media_current_entry,$bp_media_default_excerpts;
	?>
	<form method="post" class="standard-form" id="bp-media-upload-form">
		<label for="bp-media-upload-input-title"><?php _e('Video Title', 'bp-media'); ?></label><input id="bp-media-upload-input-title" type="text" name="bp_media_title" class="settings-input" maxlength="<?php echo max(array($bp_media_default_excerpts['single_entry_title'],$bp_media_default_excerpts['activity_entry_title'])) ?>" value="<?php echo $bp_media_current_entry->get_title(); ?>" />
		<label for="bp-media-upload-input-description"><?php _e('Video Description', 'bp-media'); ?></label><input id="bp-media-upload-input-description" type="text" name="bp_media_description" class="settings-input" maxlength="<?php echo max(array($bp_media_default_excerpts['single_entry_description'],$bp_media_default_excerpts['activity_entry_description'])) ?>" value="<?php echo $bp_media_current_entry->get_content(); ?>" />
		<div class="submit"><input type="submit" class="auto" value="Update" /><a href="<?php echo $bp_media_current_entry->get_url(); ?>" class="button" title="Back to Media File">Back to Media</a></div>
	</form>
	<?php
}

/**
 * Screen function for Videos Entry page
 */
function bp_media_videos_entry_screen() {
	add_action('bp_template_title', 'bp_media_videos_entry_screen_title');
	add_action('bp_template_content', 'bp_media_videos_entry_screen_content');
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_videos_entry_screen_title() {
	_e('Videos Entry Page');
}

function bp_media_videos_entry_screen_content() {
	global $bp, $bp_media_current_entry, $bp_media_options;
	if (!$bp->action_variables[0] == BP_MEDIA_VIDEOS_ENTRY_SLUG)
		return false;
	do_action('bp_media_before_content');
	echo '<div class="bp-media-single bp-media-video">';
	echo $bp_media_current_entry->get_media_single_content();
//	echo '<div class="bp-media-actions">';
//	echo '<a href="#comment" class="button acomment-reply bp-primary-action">Comment</a>';
//	if(bp_loggedin_user_id()==  bp_displayed_user_id()){
//		echo '<a href="'.$bp_media_current_entry->get_edit_url().'" class="button item-button bp-secondary-action edit-media">Edit</a>';
//		echo '<a href="'.$bp_media_current_entry->get_delete_url().'" class="button item-button bp-secondary-action delete-media confirm">Delete</a>';
//	}
//	if(isset($bp_media_options['download_enabled'])&&$bp_media_options['download_enabled']==true){
//		echo '<a href="'.$bp_media_current_entry->get_attachment_url().'" class="button item-button bp-secondary-action download-media">Download</a>';
//	}
//	echo '</div>';
	echo $bp_media_current_entry->show_comment_form();
	echo '</div>';
	do_action('bp_media_after_content');
}

/**
 * Screen function for Audio listing page (Default)
 */
function bp_media_audio_screen() {
	global $bp;
	remove_filter('bp_activity_get_user_join_filter','bp_media_activity_query_filter',10);
	if (isset($bp->action_variables[0])) {
		switch ($bp->action_variables[0]) {
			case BP_MEDIA_AUDIO_EDIT_SLUG :
				bp_media_audio_edit_screen();
				break;
			case BP_MEDIA_AUDIO_ENTRY_SLUG:
				global $bp_media_current_entry;
				if (!$bp->action_variables[0] == BP_MEDIA_IMAGES_ENTRY_SLUG)
					return false;
				try {
					$bp_media_current_entry = new BP_Media_Host_Wordpress($bp->action_variables[1]);
				} catch (Exception $e) {
					/* Send the values to the cookie for page reload display */
					@setcookie('bp-message', $_COOKIE['bp-message'], time() + 60 * 60 * 24, COOKIEPATH);
					@setcookie('bp-message-type', $_COOKIE['bp-message-type'], time() + 60 * 60 * 24, COOKIEPATH);
					wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_AUDIO_SLUG));
					exit;
				}
				add_action('bp_template_content', 'bp_media_audio_entry_screen_content');
				break;
			case BP_MEDIA_DELETE_SLUG :
				if(!isset($bp->action_variables[1])){
					bp_media_page_not_exist();
				}
				bp_media_entry_delete();
				break;
			default:
				bp_media_set_query();
				add_action('bp_template_content', 'bp_media_audio_screen_content');
		}
	} else {
		bp_media_set_query();
		add_action('bp_template_content', 'bp_media_audio_screen_content');
	}
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

/**
 * Screen function to dipslay edit audio screen title
 */
function bp_media_audio_screen_title() {
	_e('Audio List Page');
}

/**
 * Screen function to dipslay edit audio screen content
 */
function bp_media_audio_screen_content() {
	global $bp_media_query;
	if ($bp_media_query && $bp_media_query->have_posts()):
		do_action('bp_media_before_content');
		echo '<ul id="bp-media-list" class="bp-media-gallery item-list">';
		while ($bp_media_query->have_posts()) : $bp_media_query->the_post();
			bp_media_the_content();
		endwhile;
		echo '</ul>';
		bp_media_display_show_more();
		do_action('bp_media_after_content');
	else:
		bp_media_show_formatted_error_message(__('Sorry, no audio files were found.', 'bp-media'), 'info');
	endif;
}

/**
 * Screen function for Audio Edit page
 */
function bp_media_audio_edit_screen() {
	global $bp_media_current_entry,$bp;
	if(!isset($bp->action_variables[1])){
		bp_media_page_not_exist();
	}
	//Creating global bp_media_current_entry for later use
	try {
		$bp_media_current_entry = new BP_Media_Host_Wordpress($bp->action_variables[1]);

	} catch (Exception $e) {
		/* Send the values to the cookie for page reload display */
		@setcookie('bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH);
		@setcookie('bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH);
		wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG));
		exit;
	}
	bp_media_check_user();

	//For saving the data if the form is submitted
	if(array_key_exists('bp_media_title', $_POST)){
		bp_media_update_media();
	}
	add_action('bp_template_title', 'bp_media_audio_edit_screen_title');
	add_action('bp_template_content', 'bp_media_audio_edit_screen_content');
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_audio_edit_screen_title() {
	_e('Edit Audio');
}

function bp_media_audio_edit_screen_content() {
	global $bp, $bp_media_current_entry,$bp_media_default_excerpts;
	?>
	<form method="post" class="standard-form" id="bp-media-upload-form">
		<label for="bp-media-upload-input-title"><?php _e('Audio Title', 'bp-media'); ?></label><input id="bp-media-upload-input-title" type="text" name="bp_media_title" class="settings-input" maxlength="<?php echo max(array($bp_media_default_excerpts['single_entry_title'],$bp_media_default_excerpts['activity_entry_title'])) ?>" value="<?php echo $bp_media_current_entry->get_title(); ?>" />
		<label for="bp-media-upload-input-description"><?php _e('Audio Description', 'bp-media'); ?></label><input id="bp-media-upload-input-description" type="text" name="bp_media_description" class="settings-input" maxlength="<?php echo max(array($bp_media_default_excerpts['single_entry_description'],$bp_media_default_excerpts['activity_entry_description'])) ?>" value="<?php echo $bp_media_current_entry->get_content(); ?>" />
		<div class="submit"><input type="submit" class="auto" value="Update" /><a href="<?php echo $bp_media_current_entry->get_url(); ?>" class="button" title="Back to Media File">Back to Media</a></div>
	</form>
	<?php
}

/**
 * Screen function for Audio Entry page
 */
function bp_media_audio_entry_screen() {
	add_action('bp_template_title', 'bp_media_audio_entry_screen_title');
	add_action('bp_template_content', 'bp_media_audio_entry_screen_content');
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_audio_entry_screen_title() {
	_e('Audio Entry Page');
}

function bp_media_audio_entry_screen_content() {
	global $bp, $bp_media_current_entry, $bp_media_options;
	if (!$bp->action_variables[0] == BP_MEDIA_AUDIO_ENTRY_SLUG)
		return false;
	do_action('bp_media_before_content');
	echo '<div class="bp-media-single bp-media-audio">';
	echo $bp_media_current_entry->get_media_single_content();
//	echo '<div class="bp-media-actions">';
//	echo '<a href="#comment" class="button acomment-reply bp-primary-action">Comment</a>';
//	if(bp_loggedin_user_id()==  bp_displayed_user_id()){
//		echo '<a href="'.$bp_media_current_entry->get_edit_url().'" class="button item-button bp-secondary-action edit-media">Edit</a>';
//		echo '<a href="'.$bp_media_current_entry->get_delete_url().'" class="button item-button bp-secondary-action delete-media confirm">Delete</a>';
//	}
//	if(isset($bp_media_options['download_enabled'])&&$bp_media_options['download_enabled']==true){
//		echo '<a href="'.$bp_media_current_entry->get_attachment_url().'" class="button item-button bp-secondary-action download-media">Download</a>';
//	}
//	echo '</div>';
	echo $bp_media_current_entry->show_comment_form();
	echo '</div>';
	do_action('bp_media_after_content');
}

function bp_media_entry_delete() {
	global $bp;
	if (bp_loggedin_user_id() != bp_displayed_user_id()) {
		bp_core_no_access(array(
			'message' => __('You do not have access to this page.', 'buddypress'),
			'root' => bp_displayed_user_domain(),
			'redirect' => false
		));
		exit;
	}
	if(!isset($bp->action_variables[1])){
		@setcookie('bp-message', 'The requested url does not exist' , time() + 60 * 60 * 24, COOKIEPATH);
		@setcookie('bp-message-type', 'error' , time() + 60 * 60 * 24, COOKIEPATH);
		wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG));
		exit;
	}
	global $bp_media_current_entry;
	try {
		$bp_media_current_entry = new BP_Media_Host_Wordpress($bp->action_variables[1]);
	} catch (Exception $e) {
		/* Send the values to the cookie for page reload display */
		@setcookie('bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH);
		@setcookie('bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH);
		wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG));
		exit;
	}
	$post_id = $bp_media_current_entry->get_id();
	$activity_id=get_post_meta($post_id,'bp_media_child_activity',true);

	bp_activity_delete_by_activity_id($activity_id);
	$bp_media_current_entry->delete_media();

	@setcookie('bp-message', __('Media deleted successfully','bp-media'), time() + 60 * 60 * 24, COOKIEPATH);
	@setcookie('bp-message-type', 'success', time() + 60 * 60 * 24, COOKIEPATH);
	wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG));
	exit;
}

/**
 * Screen function for Albums listing page (Default)
 */
function bp_media_albums_screen() {
	global $bp;
	if (isset($bp->action_variables[0])) {
		switch ($bp->action_variables[0]) {
			case BP_MEDIA_ALBUMS_EDIT_SLUG :
				bp_media_album_edit_screen();
				break;
			case BP_MEDIA_ALBUMS_ENTRY_SLUG:
				global $bp_media_current_album;
				if (!$bp->action_variables[0] == BP_MEDIA_ALBUMS_ENTRY_SLUG)
					return false;
				try {
					$bp_media_current_album = new BP_Media_Album($bp->action_variables[1]);
				} catch (Exception $e) {
					/* Send the values to the cookie for page reload display */
					@setcookie('bp-message', $_COOKIE['bp-message'], time() + 60 * 60 * 24, COOKIEPATH);
					@setcookie('bp-message-type', $_COOKIE['bp-message-type'], time() + 60 * 60 * 24, COOKIEPATH);
					wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_ALBUMS_SLUG));
					exit;
				}
				add_action('bp_template_content', 'bp_media_albums_entry_screen_content');
				break;
			case BP_MEDIA_DELETE_SLUG :
				if(!isset($bp->action_variables[1])){
					bp_media_page_not_exist();
				}
				bp_media_entry_delete();
				break;
			default:
				bp_media_albums_set_query();
				add_action('bp_template_content', 'bp_media_albums_screen_content');
		}
	} else {
		bp_media_albums_set_query();
		add_action('bp_template_content', 'bp_media_albums_screen_content');
	}
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_albums_screen_title() {
	_e('Albums List Page');
}

function bp_media_albums_screen_content() {
	global $bp_media_albums_query;
	if ($bp_media_albums_query && $bp_media_albums_query->have_posts()):
		do_action('bp_media_before_content');
		echo '<ul id="bp-media-list" class="bp-media-gallery item-list">';
		while ($bp_media_albums_query->have_posts()) : $bp_media_albums_query->the_post();
			bp_media_album_the_content();
		endwhile;
		echo '</ul>';
		bp_media_display_show_more('albums');
		do_action('bp_media_after_content');
	else:
		bp_media_show_formatted_error_message(__('Sorry, no albums were found.', 'bp-media'), 'info');
	endif;
}

/**
 * Screen function for Albums Edit page
 */
function bp_media_albums_edit_screen() {
	global $bp_media_current_album,$bp;
	if(!isset($bp->action_variables[1])){
		bp_media_page_not_exist();
	}
	//Creating global bp_media_current_entry for later use
	try {
		$bp_media_current_album = new BP_Media_Album($bp->action_variables[1]);
	} catch (Exception $e) {
		/* Send the values to the cookie for page reload display */
		@setcookie('bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH);
		@setcookie('bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH);
		wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG));
		exit;
	}
	bp_media_check_user();

	//For saving the data if the form is submitted
	if(array_key_exists('bp_media_title', $_POST)){
		bp_media_update_media();
	}
	add_action('bp_template_title', 'bp_media_albums_edit_screen_title');
	add_action('bp_template_content', 'bp_media_albums_edit_screen_content');
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_albums_edit_screen_title() {
	_e('Edit Album');
}

function bp_media_albums_edit_screen_content() {
	global $bp, $bp_media_current_entry,$bp_media_default_excerpts;
	?>
	<form method="post" class="standard-form" id="bp-media-upload-form">
		<label for="bp-media-upload-input-title"><?php _e('Album Title', 'bp-media'); ?></label><input id="bp-media-upload-input-title" type="text" name="bp_media_title" class="settings-input" maxlength="<?php echo max(array($bp_media_default_excerpts['single_entry_title'],$bp_media_default_excerpts['activity_entry_title'])) ?>" value="<?php echo $bp_media_current_entry->get_title(); ?>" />
		<label for="bp-media-upload-input-description"><?php _e('Album Description', 'bp-media'); ?></label><input id="bp-media-upload-input-description" type="text" name="bp_media_description" class="settings-input" maxlength="<?php echo max(array($bp_media_default_excerpts['single_entry_description'],$bp_media_default_excerpts['activity_entry_description'])) ?>" value="<?php echo $bp_media_current_entry->get_content(); ?>" />
		<div class="submit"><input type="submit" class="auto" value="Update" /><a href="<?php echo $bp_media_current_entry->get_url(); ?>" class="button" title="Back to Media File">Back to Media</a></div>
	</form>
	<?php
}

function bp_media_albums_entry_screen_content() {
	global $bp, $bp_media_current_album,$bp_media_query;
	if (!$bp->action_variables[0] == BP_MEDIA_ALBUMS_ENTRY_SLUG)
		return false;
	echo '<div class="bp_media_title">'.$bp_media_current_album->get_title().'</div>';
	bp_media_albums_set_inner_query($bp_media_current_album->get_id());
	if ($bp_media_query && $bp_media_query->have_posts()):
		do_action('bp_media_before_content');
		echo '<ul id="bp-media-list" class="bp-media-gallery item-list">';
		while ($bp_media_query->have_posts()) : $bp_media_query->the_post();
			bp_media_the_content();
		endwhile;
		echo '</ul>';
		bp_media_display_show_more();
		do_action('bp_media_after_content');
	else:
		bp_media_show_formatted_error_message(__('Sorry, no media items were found in this album.', 'bp-media'), 'info');
	endif;
}
?>