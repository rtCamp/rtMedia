<?php if (is_array($tabs) && count($tabs)) { ?>
<div class="bp-media-uploader no-js">
        <form method="post" action="upload" enctype="multipart/form-data">
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
            
            <?php wp_nonce_field('rt_media_' . $mode, 'rt_media_add_media_nonce'); ?>
			
			<?php

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

            <input type="submit" name="rt-media-upload" value="<?php echo RT_MEDIA_UPLOAD_LABEL; ?>" />
        </form>
    </div>
        <?php } ?>