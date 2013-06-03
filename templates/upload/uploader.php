<?php if (is_array($tabs) && count($tabs)) { ?>
<div class="bp-media-uploader no-js">
        <form method="post" action="upload" enctype="multipart/form-data">
            <?php do_action('bp_media_before_uploader'); ?>
            
            <?php 
            $tab_html = '<ul>';
            foreach ( $tabs as $key => $tab ) {
                $tab_html .= '<li class="'.$key.'"><a href="'.add_query_arg(array('mode' => $key)).'" title="'.esc_attr($tab['title']).'">'.$tab['title'].'</a></li>';
            }
            $tab_html .= '</ul>';
            echo $tab_html;
            echo '<div class="bpm-tab-content-wrapper">';
            
            echo '<div id="bpm-'.$mode.'-ui" class="bpm-tab-content">';
            do_action('bp_media_before_'.$mode.'_ui');
            echo $tabs[$mode]['content'];
            echo '<input type="hidden" name="mode" value="'.$mode.'" />';
            do_action('bp_media_after_'.$mode.'_ui');
            echo '</div>';
            echo '</div>';
            
            ?>

            <?php do_action('bp_media_after_uploader'); ?>
            
            <?php wp_nonce_field('bp_media_'.$mode, 'bp_media_upload_nonce'); ?>
			
			<?php

			if( !empty($attr) ) {

				foreach ($attr as $key=>$value) {
					if($key == 'context')
						echo '<input type="hidden" name="context" value="'.$value.'" />';
					if($key == 'context_id')
						echo '<input type="hidden" name="context_id" value="'.$value.'" />';
					if($key == 'privacy')
						echo '<input type="hidden" name="privacy" value="'.$value.'" />';
				}
			}
			?>

            <input type="submit" name="bp-media-upload" value="<?php echo BP_MEDIA_UPLOAD_LABEL; ?>" />
        </form>
    </div>
        <?php } ?>