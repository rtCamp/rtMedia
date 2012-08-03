<?php

/**
 * Screens for all the slugs defined in the BuddyPress Media Component
 */

/* Exit if accessed directlly. */
if (!defined('ABSPATH'))
	exit;

/**
 * Screen function for Upload page
 */
function bp_media_upload_screen() {
	add_action('bp_template_title', 'bp_media_upload_screen_title');
	add_action('bp_template_content', 'bp_media_upload_screen_content');
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_upload_screen_title() {
	_e('Upload Page');
}

function bp_media_upload_screen_content() {
	do_action('bp_media_before_content');
	bp_media_show_upload_form();
	do_action('bp_media_after_content');
}

/**
 * Screen function for Images listing page (Default)
 */
function bp_media_images_screen() {
	global $bp;
	if (isset($bp->action_variables[0])) {
		switch ($bp->action_variables[0]) {
			case BP_MEDIA_IMAGES_EDIT_SLUG :
				bp_media_images_edit_screen();
				break;
			case BP_MEDIA_IMAGES_ENTRY_SLUG:
				global $bp_media_current_entry;
				if (!$bp->action_variables[0] == BP_MEDIA_IMAGES_ENTRY_SLUG)
					return false;
				try {
					$bp_media_current_entry = new BP_Media_Host_Wordpress($bp->action_variables[1]);
				} catch (Exception $e) {
					/* Send the values to the cookie for page reload display */
					@setcookie('bp-message', $_COOKIE['bp-message'], time() + 60 * 60 * 24, COOKIEPATH);
					@setcookie('bp-message-type', $_COOKIE['bp-message-type'], time() + 60 * 60 * 24, COOKIEPATH);
					wp_redirect(trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG));
					exit;
				}
				add_action('bp_template_content', 'bp_media_images_entry_screen_content');
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

function bp_media_images_screen_title() {
	_e('Images List Page');
}

function bp_media_images_screen_content() {
	global $bp_media_query;
	if ($bp_media_query && $bp_media_query->have_posts()):
		bp_media_show_pagination();
		do_action('bp_media_before_content');
		echo '<ul id="groups-list" class="bp-media-gallery item-list">';
		while ($bp_media_query->have_posts()) : $bp_media_query->the_post();
			bp_media_the_content();
		endwhile;
		echo '</ul>';
		do_action('bp_media_after_content');
		bp_media_show_pagination('bottom');
	else:
		bp_media_show_formatted_error_message(__('Sorry, no images were found.', 'bp-media'), 'info');
	endif;
}

/**
 * Screen function for Images Edit page
 */
function bp_media_images_edit_screen() {
	if (bp_loggedin_user_id() != bp_displayed_user_id()) {
		bp_core_no_access(array(
			'message' => __('You do not have access to this page.', 'buddypress'),
			'root' => bp_displayed_user_domain(),
			'redirect' => false
		));
		exit;
	}
	add_action('bp_template_title', 'bp_media_images_edit_screen_title');
	add_action('bp_template_content', 'bp_media_images_edit_screen_content');
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_images_edit_screen_title() {
	_e('Images Edit Page');
}

function bp_media_images_edit_screen_content() {
	global $bp;
	_e('Images Edit Page Content');
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
	_e('Images Entry Page');
}

function bp_media_images_entry_screen_content() {
	global $bp, $bp_media_current_entry;
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
	if (isset($bp->action_variables[0])) {
		switch ($bp->action_variables[0]) {
			case BP_MEDIA_VIDEOS_EDIT_SLUG :
				add_action('bp_template_content', 'bp_media_videos_edit_screen_content');
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
		bp_media_show_pagination();
		do_action('bp_media_before_content');
		echo '<ul class="bp-media-gallery">';
		while ($bp_media_query->have_posts()) : $bp_media_query->the_post();
			bp_media_the_content();
		endwhile;
		echo '</ul>';
		do_action('bp_media_after_content');
		bp_media_show_pagination('bottom');
	else:
		bp_media_show_formatted_error_message(__('Sorry, no videos were found.', 'bp-media'), 'info');
	endif;
}

/**
 * Screen function for Videos Edit page
 */
function bp_media_videos_edit_screen() {
	add_action('bp_template_title', 'bp_media_videos_edit_screen_title');
	add_action('bp_template_content', 'bp_media_videos_edit_screen_content');
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_videos_edit_screen_title() {
	_e('Videos Edit Page');
}

function bp_media_videos_edit_screen_content() {
	global $bp;
	_e('Videos Edit Page Content');
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
	global $bp, $bp_media_current_entry;
	if (!$bp->action_variables[0] == BP_MEDIA_VIDEOS_ENTRY_SLUG)
		return false;
	do_action('bp_media_before_content');
	echo '<div class="bp-media-single bp-media-video">';
	echo $bp_media_current_entry->get_media_single_content();
	echo $bp_media_current_entry->show_comment_form();
	echo '</div>';
	do_action('bp_media_after_content');
}

/**
 * Screen function for Audio listing page (Default)
 */
function bp_media_audio_screen() {
	global $bp;
	if (isset($bp->action_variables[0])) {
		switch ($bp->action_variables[0]) {
			case BP_MEDIA_AUDIO_EDIT_SLUG :
				add_action('bp_template_content', 'bp_media_audio_edit_screen_content');
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

function bp_media_audio_screen_title() {
	_e('Audio List Page');
}

function bp_media_audio_screen_content() {
	global $bp_media_query;
	if ($bp_media_query && $bp_media_query->have_posts()):
		bp_media_show_pagination();
		do_action('bp_media_before_content');
		echo '<ul class="bp-media-gallery">';
		while ($bp_media_query->have_posts()) : $bp_media_query->the_post();
			bp_media_the_content();
		endwhile;
		echo '</ul>';
		do_action('bp_media_after_content');
		bp_media_show_pagination('bottom');
	else:
		bp_media_show_formatted_error_message(__('Sorry, no audio files were found.', 'bp-media'), 'info');
	endif;
}

/**
 * Screen function for Audio Edit page
 */
function bp_media_audio_edit_screen() {
	add_action('bp_template_title', 'bp_media_audio_edit_screen_title');
	add_action('bp_template_content', 'bp_media_audio_edit_screen_content');
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_audio_edit_screen_title() {
	_e('Audio Edit Page');
}

function bp_media_audio_edit_screen_content() {
	global $bp;
	_e('Audio Edit Page Content');
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
	global $bp, $bp_media_current_entry;
	if (!$bp->action_variables[0] == BP_MEDIA_AUDIO_ENTRY_SLUG)
		return false;
	do_action('bp_media_before_content');
	echo '<div class="bp-media-single bp-media-audio">';
	echo $bp_media_current_entry->get_media_single_content();
	echo $bp_media_current_entry->show_comment_form();
	echo '</div>';
	do_action('bp_media_after_content');
}
?>