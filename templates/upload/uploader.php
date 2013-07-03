<?php if (is_array($tabs) && count($tabs)) { ?>
<div class="rtmedia-container">
<div class="rtmedia-uploader no-js">
        <form id="rtmedia-uploader-form" method="post" action="upload" enctype="multipart/form-data">
            <?php do_action('rtmedia_before_uploader'); ?>

            <?php
//            $tab_html = '<ul>';
//            foreach ( $tabs as $key => $tab ) {
//                $tab_html .= '<li class="'.$key.'"><a href="'.add_query_arg(array('mode' => $key)).'" title="'.esc_attr($tab['title']).'">'.$tab['title'].'</a></li>';
//            }
//            $tab_html .= '</ul>';
//            echo $tab_html;
            echo '<div class="rtm-tab-content-wrapper">';
				echo '<div id="rtm-'.$mode.'-ui" class="rtm-tab-content">';
					do_action('rtmedia_before_'.$mode.'_ui');
					echo $tabs[$mode][$upload_type]['content'];
					echo '<input type="hidden" name="mode" value="'.$mode.'" />';
					do_action('rtmedia_after_'.$mode.'_ui', $attr);
				echo '</div>';
            echo '</div>';

            ?>

            <?php do_action('rtmedia_after_uploader'); ?>

            <?php RTMediaUploadView::upload_nonce_generator(true); ?>

			<?php

			global $rtmedia_interaction;
//			$context_flag = $context_id_flag = $album_id_flag = false;
			if( !empty($attr) ) {

				foreach ($attr as $key=>$value) {

					if($key == 'context')
						echo '<input type="hidden" name="context" value="'.$value.'" />';
					if($key == 'context_id')
						echo '<input type="hidden" name="context_id" value="'.$value.'" />';
					if($key == 'privacy')
						echo '<input type="hidden" name="privacy" value="'.$value.'" />';
					if($key == 'album_id')
						echo '<input type="hidden" name="album_id" value="'.$value.'" />';
				}
			}
			?>

            <input type="submit" id='rtMedia-start-upload' name="rtmedia-upload" value="<?php echo RTMEDIA_UPLOAD_LABEL; ?>" />
        </form>
    </div>
</div>
<?php }