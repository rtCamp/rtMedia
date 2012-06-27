<?php
/**
 * Screens for all the slugs defined in the BuddyPress Media Component
 */
//Exit if accessed directlly.
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
				//add_action('bp_template_title', 'bp_media_images_edit_screen_title');
				add_action('bp_template_content', 'bp_media_images_edit_screen_content');
				break;
			case BP_MEDIA_IMAGES_ENTRY_SLUG:
				//add_action('bp_template_title', 'bp_media_images_entry_screen_title');
				add_action('bp_template_content', 'bp_media_images_entry_screen_content');
				break;
			default:
				//add_action('bp_template_title', 'bp_media_images_screen_title');
				bp_media_set_query();
				add_action('bp_template_content', 'bp_media_images_screen_content');
		}
	} else {
		//add_action('bp_template_title', 'bp_media_images_screen_title');
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
		//_e('Images List Content');
		//bp_media_show_pagination();
		echo '<ul class="bp-media-gallery">';
		while ($bp_media_query->have_posts()) : $bp_media_query->the_post();
			bp_media_the_content();
		endwhile;
		echo '</ul>';
	else:
		bp_media_show_formatted_error_message(__('Sorry, no images were found.', 'bp-media'), 'info');
	endif;
}

/**
 * Screen function for Images Edit page
 */
function bp_media_images_edit_screen() {
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
	global $bp;
	_e('Images Entry Content');
}

/**
 * Screen function for Videos listing page (Default)
 */
function bp_media_videos_screen() {
	global $bp;
	if (isset($bp->action_variables[0])) {
		switch ($bp->action_variables[0]) {
			case BP_MEDIA_VIDEOS_EDIT_SLUG :
				add_action('bp_template_title', 'bp_media_videos_edit_screen_title');
				add_action('bp_template_content', 'bp_media_videos_edit_screen_content');
				break;
			case BP_MEDIA_VIDEOS_ENTRY_SLUG:
				add_action('bp_template_title', 'bp_media_videos_entry_screen_title');
				add_action('bp_template_content', 'bp_media_videos_entry_screen_content');
				break;
			default:
				add_action('bp_template_title', 'bp_media_videos_screen_title');
				add_action('bp_template_content', 'bp_media_videos_screen_content');
		}
	} else {
		add_action('bp_template_title', 'bp_media_videos_screen_title');
		add_action('bp_template_content', 'bp_media_videos_screen_content');
	}
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_videos_screen_title() {
	_e('Videos List Page');
}

function bp_media_videos_screen_content() {
	_e('Videos List Content');
	?><button id="testbutton">Testing</button>
	<div id="output"></div>
	<?php
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
	global $bp;
	_e('Videos Entry Content');
}

/**
 * Screen function for Audio listing page (Default)
 */
function bp_media_audio_screen() {
	global $bp;
	if (isset($bp->action_variables[0])) {
		switch ($bp->action_variables[0]) {
			case BP_MEDIA_AUDIO_EDIT_SLUG :
				add_action('bp_template_title', 'bp_media_audio_edit_screen_title');
				add_action('bp_template_content', 'bp_media_audio_edit_screen_content');
				break;
			case BP_MEDIA_AUDIO_ENTRY_SLUG:
				add_action('bp_template_title', 'bp_media_audio_entry_screen_title');
				add_action('bp_template_content', 'bp_media_audio_entry_screen_content');
				break;
			default:
				add_action('bp_template_title', 'bp_media_audio_screen_title');
				add_action('bp_template_content', 'bp_media_audio_screen_content');
		}
	} else {
		add_action('bp_template_title', 'bp_media_audio_screen_title');
		add_action('bp_template_content', 'bp_media_audio_screen_content');
	}
	bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bp_media_audio_screen_title() {
	_e('Audio List Page');
}

function bp_media_audio_screen_content() {
	_e('Audio List Content');
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
	global $bp;
	_e('Audio Entry Content');
}
?>