<?php

/***
 * You can hook in ajax functions in WordPress/BuddyPress by using the 'wp_ajax' action.
 * 
 * When you post your ajax call from javascript using jQuery, you can define the action
 * which will determin which function to run in your PHP component code.
 *
 * Here's an example:
 *
 * In Javascript we can post an action with some parameters via jQuery:
 * 
 * 			jQuery.post( ajaxurl, {
 *				action: 'my_example_action',
 *				'cookie': encodeURIComponent(document.cookie),
 *				'parameter_1': 'some_value'
 *			}, function(response) { ... } );
 *
 * Notice the action 'my_example_action', this is the part that will hook into the wp_ajax action.
 * 
 * You will need to add an add_action( 'wp_ajax_my_example_action', 'the_function_to_run' ); so that
 * your function will run when this action is fired.
 * 
 * You'll be able to access any of the parameters passed using the $_POST variable.
 *
 * Below is an example of the addremove_friend AJAX action in the friends component.
 */

function rt_media_friends_ajax_addremove_friend() {
	global $bp;

	if ( 'is_friend' == BP_Friends_Friendship::check_is_friend( $bp->loggedin_user->id, $_POST['fid'] ) ) {

		if ( !friends_remove_friend( $bp->loggedin_user->id, $_POST['fid'] ) ) {
			echo __( 'Friendship could not be canceled.', 'bp-component' );
		} else {
			echo '<a id="friend-' . $_POST['fid'] . '" class="add" rel="add" title="' . __( 'Add Friend', 'bp-component' ) . '" href="' . $bp->loggedin_user->domain . $bp['friends']['slug'] . '/add-friend/' . $_POST['fid'] . '">' . __( 'Add Friend', 'bp-component' ) . '</a>';
		}

	} else if ( 'not_friends' == BP_Friends_Friendship::check_is_friend( $bp->loggedin_user->id, $_POST['fid'] ) ) {

		if ( !friends_add_friend( $bp->loggedin_user->id, $_POST['fid'] ) ) {
			echo __( 'Friendship could not be requested.', 'bp-component');
		} else {
			echo '<a href="' . $bp->loggedin_user->domain . $bp['friends']['slug'] . '" class="requested">' . __( 'Friendship Requested', 'bp-component' ) . '</a>';
		}

	} else {
		echo __( 'Request Pending', 'bp-component' );
	}
	
	return false;
}
//add_action( 'wp_ajax_addremove_friend', 'friends_ajax_addremove_friend' );

?>