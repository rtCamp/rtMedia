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
            
            echo '<div class="bpm-tab-content bpm-'.$mode.'-ui">';
            do_action('bp_media_before_'.$mode.'_ui');
            echo $tabs[$mode]['content'];
            echo '<input type="hidden" name="mode" value="'.$mode.'" />';
            do_action('bp_media_after_'.$mode.'_ui');
            echo '</div>';
            echo '</div>';
            
            ?>

            <?php do_action('bp_media_after_uploader'); ?>
            
            <?php wp_nonce_field('bp_media_'.$mode, 'bp_media_upload_nonce'); ?>

            <input type="submit" name="bp-media-upload" value="<?php echo BP_MEDIA_UPLOAD_LABEL; ?>" />
        </form>
    </div>
        <?php } ?>