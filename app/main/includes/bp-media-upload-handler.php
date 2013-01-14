<?php
ignore_user_abort(true);

/** Define the server path to the file wp-config here, if you placed WP-CONTENT outside the classic file structure */

$path  = '/'; // It should be end with a trailing slash

/** That's all, stop editing from here **/

if ( !defined('WP_LOAD_PATH') ) {

	/** classic root path if wp-content and plugins is below wp-config.php */
	$classic_root = dirname(dirname(dirname(dirname(dirname(dirname(dirname( __FILE__ ))))))).'/';
	echo $classic_root;
	//$classic_root = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/' ;
	if (file_exists( $classic_root . 'wp-load.php') )
		define( 'WP_LOAD_PATH', $classic_root);
	else
		if (file_exists( $path . 'wp-load.php') )
			define( 'WP_LOAD_PATH', $path);
		else
			exit("Could not find wp-load.php");
}

// let's load WordPress
require_once( WP_LOAD_PATH . 'wp-load.php');

//require_once( WP_LOAD_PATH . 'wp-admin/admin.php');

// Check for rights
if ( !is_user_logged_in() )
	wp_die(__("You are not allowed to be here"));

BPMediaActions::handle_uploads();

?>