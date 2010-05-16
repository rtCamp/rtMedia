<?php
	/*
	 * This is main file for adding Kaltua Media Button inside wordpress post-editor
	 */

/* **** ACTIONS AND FILTERS *** */
// add the button to the media button bar 
add_filter( 'media_buttons_context', 'kaltura_add_media_button', 100);

// action handler - will run when kaltura iframe will load inside thickbox
add_action( 'media_upload_kaltura', 'kaltura_media_add_thickbox', 100);

//kaltura iframe header - add css & js
add_action('admin_print_styles', 'admin_head_kaltura_media_library_form_styles');
add_action('admin_print_scripts', 'admin_head_kaltura_media_library_form_scripts');

function kaltura_add_media_button($context)  {
	global $post_ID, $temp_ID;
	$uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);

	$iconurl = BP_MEDIA_PLUGIN_URL .'/editor/img/bp-media-logo-13x13.png';
	$kaltura_media_button = '%s<a href="media-upload.php?post_id='.$uploading_iframe_ID.'&amp;type=kaltura&amp;TB_iframe=true" title="Add Kaltura Media" class="thickbox"><img src="'.$iconurl.'" alt="All Kaltura Media" /></a>';
	return sprintf($context, $kaltura_media_button);
}

function admin_head_kaltura_media_library_form_styles(){
	global $type;
	
	//add media CSS		
	if($type == 'kaltura' ){
		wp_enqueue_style( 'media' );
		wp_enqueue_style( 'kaltura-media-editor-css',BP_MEDIA_PLUGIN_URL .'/editor/css/bp-editor.css');
	}	
}

function admin_head_kaltura_media_library_form_scripts(){
	global $type;
	//add media CSS		
	if($type == 'kaltura' ){
		?><script type="text/javascript">post_id = <?php echo intval($_REQUEST['post_id']); ?>;</script><?php
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script( 'kaltura-media-editor-js',BP_MEDIA_PLUGIN_URL .'/editor/js/bp-editor.js.php');
	}	
}

// the media_upload functions
function kaltura_media_add_thickbox($stuff) {
	// clean up the tabs to only show kaltura
	$post_id = $_GET['post_id']; 

	//HTML of iframe
	return wp_iframe('kaltura_media_library_form',$post_id);
}

//main function which will "write" body of iframe
function kaltura_media_library_form($post_id){
    global $wpdb, $wp_query, $wp_locale, $type, $tab, $post_mime_types;
    ?>
	<!-- header and menu -->
	<div id="kaltura-tabs">
		<ul>
			<li <!-- id="tab-kaltura_media_all -->"><a href="<?php echo BP_MEDIA_PLUGIN_URL ?>/editor/php/bp-editor-ajax.php?filter=all&start=0">All Media</a></li>
			<li <!-- id="tab-kaltura_media_photo" -->><a href="<?php echo BP_MEDIA_PLUGIN_URL ?>/editor/php/bp-editor-ajax.php?filter=photo&start=0">Photos</a></li>
			<li <!-- id="tab-kaltura_media_audio" -->><a href="<?php echo BP_MEDIA_PLUGIN_URL ?>/editor/php/bp-editor-ajax.php?filter=audio&start=0">Audios</a></li>
			<li <!-- id="tab-kaltura_media_video" -->><a href="<?php echo BP_MEDIA_PLUGIN_URL ?>/editor/php/bp-editor-ajax.php?filter=video&start=0">Videos</a></li>
		</ul>
<!--
		<div id="tab-kaltura_media_all">
			<!-- All Media -->
		</div>
		<div id="tab-kaltura_media_photo">
			<!-- All Media -->
		</div>
		<div id="tab-kaltura_media_audio">
			<!-- All Media -->
		</div>
		<div id="tab-kaltura_media_video">
			<!-- All Media -->
		</div>	
-->	
	</div>	
	<?php
}
?>