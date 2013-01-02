<?php
ignore_user_abort(true);

require_once(BP_MEDIA_PATH.'lib/bootstrap.php');

// Check for rights
if ( !is_user_logged_in() )
	wp_die(__("You are not allowed to be here"));

bp_media_handle_uploads();

?>