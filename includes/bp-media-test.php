<?php



function test_activity() {
	global $bp;
	bp_media_record_activity(array(
		'action'		=>	sprintf(__("%s uploaded a media."),bp_core_get_userlink(bp_loggedin_user_id() )),
		'content'		=>	'<a href="#"><img src="http://www.gravatar.com/avatar/24a9f6007ea484a8f571baa78bac2be3"></a><h3>Media title Goes here...</h3><span>Media Description Goes here....</span>',
		'primary_link'	=>	'http://somwhere.com',
		'type'			=>	'media_upload'
	));
}

?>