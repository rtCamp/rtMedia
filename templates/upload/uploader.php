<?php if (is_array($tabs) && count($tabs)) { ?>
<div class="rt-media-uploader no-js">
        <form id="rt-media-uploader-form" method="post" action="upload" enctype="multipart/form-data">
            <?php do_action('rt_media_before_uploader'); ?>
            
            <?php 
            $tab_html = '<ul>';
            foreach ( $tabs as $key => $tab ) {
                $tab_html .= '<li class="'.$key.'"><a href="'.add_query_arg(array('mode' => $key)).'" title="'.esc_attr($tab['title']).'">'.$tab['title'].'</a></li>';
            }
            $tab_html .= '</ul>';
            echo $tab_html;
            echo '<div class="rtm-tab-content-wrapper">';
            
            echo '<div id="rtm-'.$mode.'-ui" class="rtm-tab-content">';
            do_action('rt_media_before_'.$mode.'_ui');
            echo $tabs[$mode]['content'];
            echo '<input type="hidden" name="mode" value="'.$mode.'" />';
            do_action('rt_media_after_'.$mode.'_ui', $attr);
            echo '</div>';
            echo '</div>';
            
            ?>

            <?php do_action('rt_media_after_uploader'); ?>
            
            <?php RTMediaUploadView::upload_nonce_generator(true); ?>
			
			<?php

			global $rt_media_interaction;
//			$context_flag = $context_id_flag = $album_id_flag = false;
			if( !empty($attr) ) {

				foreach ($attr as $key=>$value) {

					if($key == 'context') {
						echo '<input type="hidden" name="context" value="'.$value.'" />';
//						$context_flag = true;
					}
					if($key == 'context_id') {
						echo '<input type="hidden" name="context_id" value="'.$value.'" />';
//						$context_id_flag = true;
					}
					if($key == 'privacy') {
						echo '<input type="hidden" name="privacy" value="'.$value.'" />';
					}
					if($key == 'album_id') {
						echo '<input type="hidden" name="album_id" value="'.$value.'" />';
//						$album_id_flag = true;
					}
				}
			}

/*			if(!$context_flag)
				echo '<input type="hidden" name="context" value="'.$rt_media_interaction->context->type.'" />';				
			if(!$context_id_flag)
				echo '<input type="hidden" name="context_id" value="'.$rt_media_interaction->context->id.'" />';
			if(!$album_id_flag && !$context_id_flag)
				echo '<input type="hidden" name="album_id" value="'.$rt_media_interaction->context->id.'" />';*/
			?>

            <input type="submit" id='rtMedia-start-upload' name="rt-media-upload" value="<?php echo RT_MEDIA_UPLOAD_LABEL; ?>" />
        </form>
    </div>
<?php } 
    $params = array(
            'url' => 'upload/',
            'runtimes' => 'gears,html5,flash,silverlight,browserplus',
            'browse_button' => 'rtMedia-upload-button',
            'container' => 'upload-container',
            'drop_element' => 'drag-drop-area',
            'filters' => apply_filters('bp_media_plupload_files_filter', array(array('title' => "Media Files", 'extensions' => "mp4,jpg,png,jpeg,gif,mp3"))),
            'max_file_size' => min(array(ini_get('upload_max_filesize'), ini_get('post_max_size'))),
            'multipart' => true,
            'urlstream_upload' => true,
            'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
            'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
            'file_data_name' => 'rt_media_file', // key passed to $_FILE.
            'multi_selection' => true,
            'multipart_params' => apply_filters('rt-media-multi-params', array('redirect'=>'no','action' => 'wp_handle_upload','_wp_http_referer'=> $_SERVER['REQUEST_URI'],'mode'=>'file_upload','rt_media_upload_nonce'=>RTMediaUploadView::upload_nonce_generator(false,true)))
        );
    
    
    
?>
<script type="text/javascript">
    var rtMedia_plupload_config=<?php echo json_encode($params); ?>;
</script> 
