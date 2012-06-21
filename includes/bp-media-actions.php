<?php
/**
 * 
 */

function bp_media_handle_uploads() {
	global $bp;
	if(isset($_POST['action'])&&$_POST['action']=='wp_handle_upload'){
		if(isset($_FILES)&&  is_array($_FILES)&&  array_key_exists('bp_media_file', $_FILES)&& $_FILES['bp_media_file']['name']!=''){
			//$bp->{BP_MEDIA_SLUG}->messages['updated'][]='File '.$_FILES['bp_media_file']['name'].' was uploaded successfully';
			//$bp->{BP_MEDIA_SLUG}->messages['error'][]='Uploading function reached';
			//$bp->{BP_MEDIA_SLUG}->messages['updated'][]='Uploading function reached';
			//bp_core_add_message( __( 'No self-fives! :)', 'bp-example' ), 'error' );
			//include(admin_url('file.php'));
			$bp_media_entry=new BP_Media_Host_Wordpress();

			$entry=$bp_media_entry->add_media($_POST['bp_media_title'],$_POST['bp_media_description']);
			if($entry===false)
				$bp->{BP_MEDIA_SLUG}->messages['error'][]=__('Media Upload failed, supported filetypes are .jpg, .png, .gif, .mp3 and .mp4','bp-media');
			else
				$bp->{BP_MEDIA_SLUG}->messages['updated'][]=__('Upload Successful','bp-media');

		}
		else{
			$bp->{BP_MEDIA_SLUG}->messages['error'][]=__('You did not specified a file to upload','bp-media');
		}
	}
}
add_action('bp_init','bp_media_handle_uploads');

function bp_media_show_messages() {
	global $bp;
	if(is_array($bp->{BP_MEDIA_SLUG}->messages)) {
		

		$types=array('error','updated','info');
		foreach($types as $type){
			if(count($bp->{BP_MEDIA_SLUG}->messages[$type])>0) {
				bp_media_show_formatted_error_message($bp->{BP_MEDIA_SLUG}->messages[$type],$type);
			}
		}
		
	}
}

add_action('bp_media_before_content','bp_media_show_messages');


function bp_media_enqueue_scripts_styles() {
	wp_enqueue_script( 'bp-media-mejs',plugins_url( 'includes/js/mediaelement-and-player.min.js' , dirname(__FILE__) ));
	wp_enqueue_script( 'bp-media-default',plugins_url( 'includes/js/bp-media.js' , dirname(__FILE__) ));
	wp_enqueue_style('bp-media-mecss', plugins_url( 'includes/css/mediaelementplayer.min.css' , dirname(__FILE__) ));
	wp_enqueue_style('bp-media-default', plugins_url( 'includes/css/bp-media-style.css' , dirname(__FILE__) ));
}
add_action( 'wp_enqueue_scripts', 'bp_media_enqueue_scripts_styles', 11 );
?>