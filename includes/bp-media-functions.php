<?php

/**
 * 
 */
function bp_media_record_activity( $args = '' ) {
	global $bp;

	if ( !function_exists( 'bp_activity_add' ) )
		return false;

	$defaults = array(
		'id'                => false, // Pass an existing activity ID to update an existing entry.
		'action'            => '',    // The activity action - e.g. "Jon Doe posted an update"
		'content'           => '',    // Optional: The content of the activity item e.g. "BuddyPress is awesome guys!"
		'component'         => BP_MEDIA_SLUG, // The name/ID of the component e.g. groups, profile, mycomponent
		'type'              => false, // The activity type e.g. activity_update, profile_updated
		'primary_link'      => '',    // Optional: The primary URL for this item in RSS feeds (defaults to activity permalink)

		'user_id'           => $bp->loggedin_user->id, // Optional: The user to record the activity for, can be false if this activity is not for a user.
		'item_id'           => false, // Optional: The ID of the specific item being recorded, e.g. a blog_id
		'secondary_item_id' => false, // Optional: A second ID used to further filter e.g. a comment_id
		'recorded_time'     => bp_core_current_time(), // The GMT time that this activity was recorded
		'hide_sitewide'     => false  // Should this be hidden on the sitewide activity stream?
	);
	add_filter('bp_activity_allowed_tags','bp_media_override_allowed_tags');
	$r = wp_parse_args( $args, $defaults );
	extract( $r );
	$activity_id=bp_activity_add( array( 'id' => $id, 'user_id' => $user_id, 'action' => $action, 'content' => $content, 'primary_link' => $primary_link, 'component' => $component, 'type' => $type, 'item_id' => $item_id, 'secondary_item_id' => $secondary_item_id, 'recorded_time' => $recorded_time, 'hide_sitewide' => $hide_sitewide ) );
	//remove_filter('bp_activity_allowed_tags','bp_media_override_allowed_tags');
	return $activity_id;
}

function bp_media_override_allowed_tags($activity_allowedtags) {
	$activity_allowedtags['video']				=	array();
	$activity_allowedtags['video']['id']		=	array();
	$activity_allowedtags['video']['class']		=	array();
	$activity_allowedtags['video']['src']		=	array();
	$activity_allowedtags['video']['height']	=	array();
	$activity_allowedtags['video']['width']		=	array();
	$activity_allowedtags['video']['controls']	=	array();
	$activity_allowedtags['video']['preload']	=	array();
	$activity_allowedtags['video']['alt']		=	array();
	$activity_allowedtags['video']['title']		=	array();
	$activity_allowedtags['audio']				=	array();
	$activity_allowedtags['audio']['id']		=	array();
	$activity_allowedtags['audio']['class']		=	array();
	$activity_allowedtags['audio']['src']		=	array();
	$activity_allowedtags['audio']['controls']	=	array();
	$activity_allowedtags['audio']['preload']	=	array();
	$activity_allowedtags['audio']['alt']		=	array();
	$activity_allowedtags['audio']['title']		=	array();
	$activity_allowedtags['script']				=	array();
	$activity_allowedtags['script']['type']		=	array();
	return $activity_allowedtags;
}

function bp_media_show_formatted_error_message($messages,$type) {
	echo '<div id="message" class="'.$type.'">';
	foreach($messages as $key=>$message) {
		if(is_string($message)){
			echo '<p>'.$message.'</p>';
		}
	}
	echo '</div>';
}

function bp_media_conditional_override_allowed_tags($content,$activity) {
	if($activity->type='media_upload') {
		add_filter('bp_activity_allowed_tags','bp_media_override_allowed_tags',1);
	}
	return bp_activity_filter_kses($content);
}
function bp_media_swap_filters(){
	add_filter('bp_get_activity_content_body','bp_media_conditional_override_allowed_tags',1,2);
	remove_filter( 'bp_get_activity_content_body' , 'bp_activity_filter_kses',1);
}
add_action('bp_init','bp_media_swap_filters');

?>