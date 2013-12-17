<?php if ( is_array ( $tabs ) && count ( $tabs ) ) { ?>
    <div class="rtmedia-container">
    <?php

	if( isset($attr['rtmedia_simple_file_upload']) && $attr['rtmedia_simple_file_upload'] == true) {
	    if(isset($attr['rtmedia_upload_without_form']) && $attr['rtmedia_upload_without_form'] == true) {
		echo '<div class="rtmedia-simple-file-upload">';
	    } else {
		echo '<form id="rtmedia-uploader-form" method="post" action="upload" enctype="multipart/form-data">';
		echo '<div class="rtmedia-simple-file-upload">';
	    }
	    RTMediaUploadView::upload_nonce_generator ( true );
	    if ( ! empty ( $attr ) ) {
                    foreach ( $attr as $key => $value ) {
                        if ( $key == 'context' )
                            echo '<input type="hidden" name="context" value="' . $value . '" />';
                        else if ( $key == 'context_id' )
                            echo '<input type="hidden" name="context_id" value="' . $value . '" />';
                        else if ( $key == 'privacy' )
                            echo '<input type="hidden" name="privacy" value="' . $value . '" />';
                        else if ( $key == 'album_id' )
                            echo '<input type="hidden" name="album_id" value="' . $value . '" />';
			else if ( $key == 'title' )
			    echo '<p class="rtmedia-file-upload-p rtmedia-file-upload-title"><input type="text" name="title" /></p>';
			else if ( $key == 'description' )
			    echo '<p class="rtmedia-file-upload-p rtmedia-file-upload-desc"><textarea name="description"></textarea></p>';
			else
			    echo "<input type='hidden' id='rt_upload_hf_".sanitize_key ( $key )."' value='".$value."' name ='".$key."' />";
                    }
                }
		if(isset($attr['rtmedia_upload_allow_multiple']) && $attr['rtmedia_upload_allow_multiple'] == true) {
		    echo '<div class="rtm-file-input-container"> <p class="rtmedia-file-upload-p"> <input type="file" name="rtmedia_file_multiple[]" multiple="true" class="rtm-simple-file-input" id="rtmedia_simple_file_input" /> </p> </div>';
		} else {
		    echo '<div class="rtm-file-input-container"> <p class="rtmedia-file-upload-p"> <input type="file" name="rtmedia_file" class="rtm-simple-file-input" id="rtmedia_simple_file_input" /> </p> </div>';
		}
		do_action("rtmedia_add_upload_content");

	    if(isset($attr['rtmedia_upload_without_form']) && $attr['rtmedia_upload_without_form'] == true) {
		echo '</div>';
	    } else {
		echo '<p> <input type="submit" name="rtmedia_simple_file_upload_submit" /> </p> ';
		echo '</div>';
		echo '</form>';
	    }
	} else {
    ?>
	<div class="rtmedia-uploader no-js">
            <form id="rtmedia-uploader-form" method="post" action="upload" enctype="multipart/form-data">
                <?php do_action ( 'rtmedia_before_uploader' ); ?>

                <?php
//            $tab_html = '<ul>';
//            foreach ( $tabs as $key => $tab ) {
//                $tab_html .= '<li class="'.$key.'"><a href="'.add_query_arg(array('mode' => $key)).'" title="'.esc_attr($tab['title']).'">'.$tab['title'].'</a></li>';
//            }
//            $tab_html .= '</ul>';
//            echo $tab_html;
                echo '<div class="rtm-tab-content-wrapper">';
                echo '<div id="rtm-' . $mode . '-ui" class="rtm-tab-content">';
                do_action ( 'rtmedia_before_' . $mode . '_ui' );
                echo $tabs[ $mode ][ $upload_type ][ 'content' ];
                echo '<input type="hidden" name="mode" value="' . $mode . '" />';
                do_action ( 'rtmedia_after_' . $mode . '_ui', $attr );
                echo '</div>';
                echo '</div>';
                ?>

                <?php do_action ( 'rtmedia_after_uploader' ); ?>

                <?php RTMediaUploadView::upload_nonce_generator ( true ); ?>

                <?php
                global $rtmedia_interaction;
//			$context_flag = $context_id_flag = $album_id_flag = false;
                if ( ! empty ( $attr ) ) {

                    foreach ( $attr as $key => $value ) {

                        if ( $key == 'context' )
                            echo '<input type="hidden" name="context" value="' . $value . '" />';
                        if ( $key == 'context_id' )
                            echo '<input type="hidden" name="context_id" value="' . $value . '" />';
                        if ( $key == 'privacy' )
                            echo '<input type="hidden" name="privacy" value="' . $value . '" />';
                        if ( $key == 'album_id' )
                            echo '<input type="hidden" name="album_id" value="' . $value . '" />';
                    }
                }
                ?>

                <input type="submit" id='rtMedia-start-upload' name="rtmedia-upload" value="<?php echo RTMEDIA_UPLOAD_LABEL; ?>" />
            </form>
        </div>
    <?php
	}
    ?>
    </div>
    <?php
}