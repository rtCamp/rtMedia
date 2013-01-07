<?php
ignore_user_abort(true);

$bootstrap = dirname(__FILE__);
$bootstrap = str_replace('app/main/includes','',$bootstrap);
$bootstrap .= 'lib/bootstrap.php';
require_once($bootstrap);

// Check for rights
if ( !is_user_logged_in() )
	wp_die(__("You are not allowed to be here"));

BPMediaActions::bp_media_handle_uploads();

?>