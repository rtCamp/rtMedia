<?php
ignore_user_abort(true);

require_once('lib/bootstrap.php');

// Disable error reporting or else AJAX Requests might give different data format
error_reporting(E_ALL);

// Check for rights
if ( !is_user_logged_in() )
	wp_die(__("You are not allowed to be here"));

//set_time_limit(0);

bp_media_handle_uploads();

?>