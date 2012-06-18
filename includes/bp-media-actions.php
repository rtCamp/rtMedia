<?php
/**
 * 
 */

function bp_media_handle_uploads() {
	global $bp;
	if(isset($_FILES)&&  is_array($_FILES)&&  array_key_exists('bp_media_file', $_FILES)){
		//$bp->{BP_MEDIA_SLUG}->messages['updated'][]='File '.$_FILES['bp_media_file']['name'].' was uploaded successfully';
		//$bp->{BP_MEDIA_SLUG}->messages['error'][]='Uploading function reached';
		//$bp->{BP_MEDIA_SLUG}->messages['updated'][]='Uploading function reached';
		//bp_core_add_message( __( 'No self-fives! :)', 'bp-example' ), 'error' );
		//include(admin_url('file.php'));
		include_once(ABSPATH.'wp-admin/includes/file.php');
		$file=wp_handle_upload($_FILES['bp_media_file']);
		echo '<pre>';
		var_dump($file);
		echo '</pre>';
	}
}
add_action('bp_init','bp_media_handle_uploads');

function bp_media_show_messages() {
	global $bp;
	if(is_array($bp->{BP_MEDIA_SLUG}->messages)) {
		

		$types=array('error','updated','info');
		foreach($types as $type){
			if(count($bp->{BP_MEDIA_SLUG}->messages[$type])>0) {
				bp_media_show_formatted_message($bp->{BP_MEDIA_SLUG}->messages[$type],$type);
			}
		}
		
	}
}

add_action('bp_media_before_content','bp_media_show_messages');
?>