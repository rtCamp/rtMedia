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
}

?>