<?php

/**
 * Description of RTMediaUploadView
 *
 * @author joshua
 */
class RTMediaUploadView {

    private $attributes;

    /**
     *
     * @param type $attr
     */
    function __construct ( $attr ) {
        $this->attributes = $attr;
    }

    static function upload_nonce_generator ( $echo = true, $only_nonce = false ) {

        if ( $echo ) {
            wp_nonce_field ( 'rtmedia_upload_nonce', 'rtmedia_upload_nonce' );
        } else {
            if ( $only_nonce )
                return wp_create_nonce ( 'rtmedia_upload_nonce' );
            $token = array(
                'action' => 'rtmedia_upload_nonce',
                'nonce' => wp_create_nonce ( 'rtmedia_upload_nonce' )
            );

            return json_encode ( $token );
        }
    }

    /**
     * Render the uploader shortcode and attach the uploader panel
     *
     * @param type $template_name
     */
    public function render ( $template_name ) {

        global $rtmedia_query;
	$album = '';
		if( apply_filters( 'rtmedia_render_select_album_upload', true ) ) {
			if ( $rtmedia_query && isset( $rtmedia_query->media_query ) && isset( $rtmedia_query->media_query[ 'album_id' ] ) && is_rtmedia_album ()  ) {
				$album = '<input class="rtmedia-current-album" type="hidden" name="rtmedia-current-album" value="' . $rtmedia_query->media_query[ 'album_id' ] . '" />';
			} elseif ( is_rtmedia_album_enable () && $rtmedia_query && is_rtmedia_gallery () ) {

				if ( isset( $rtmedia_query->query[ 'context' ] ) && $rtmedia_query->query[ 'context' ] == 'profile' ) {
					$album = '<span> <label> <i class="rtmicon-picture-o"></i>' . __('Album','rtmedia') . ': </label><select name="album" class="rtmedia-user-album-list">' . rtmedia_user_album_list () . '</select></span>';
				}
				if (isset( $rtmedia_query->query[ 'context' ] ) && $rtmedia_query->query[ 'context' ] == 'group' ) {
					$album = '<span> <label> <i class="rtmicon-picture-o"></i>' . __('Album','rtmedia') . ': </label><select name="album" class="rtmedia-user-album-list">' . rtmedia_group_album_list () . '</select></span>';
				}
			}
		}
        $up_privacy = $privacy = ""; //uploader privacy dropdown for uploader under rtMedia Media tab.
        if( is_rtmedia_privacy_enable () && ( ! isset( $rtmedia_query->is_upload_shortcode ) || $rtmedia_query->is_upload_shortcode === false) ) {
            if( isset( $rtmedia_query->query[ 'context' ] ) && $rtmedia_query->query[ 'context' ] == 'group'){
                // if the context is group, then set the media privacy to public
                $privacy = "<input type='hidden' name='privacy' value='0'/>";
            }else {
                $up_privacy = new RTMediaPrivacy();
                $up_privacy = $up_privacy->select_privacy_ui( false, 'rtSelectPrivacy') ;
                if($up_privacy){
                    $privacy = "<span> <label for='privacy'> <i class='rtmicon-eye'></i> " . __('Privacy: ', 'rtmedia') . "</label>" . $up_privacy . "</span>";
                }
            }
        }
        $tabs = array(
            'file_upload' => array(
                'default' => array( 'title' => __( 'File Upload', 'rtmedia' ),
                    'content' =>
                    '<div id="rtmedia-upload-container" >'
                        . '<div id="drag-drop-area" class="drag-drop row">'
                                ."<div class='rtm-album-privacy'>" . $album . $privacy . "</div>"
								. apply_filters( 'rtmedia_uploader_before_select_files', "" )
                                . '<div class="rtm-select-files"><input id="rtMedia-upload-button" value="' . __( "Select your files", "rtmedia" ) . '" type="button" class="rtmedia-upload-input rtmedia-file" />'
                                . '<span class="rtm-seperator">' . __('or','rtmedia') .'</span><span class="drag-drop-info">' . __('Drop your files here', 'rtmedia') . '</span> <i class="rtm-file-size-limit rtmicon-info-circle"></i></div>'
								. apply_filters( 'rtmedia_uploader_after_select_files', "" )
								. apply_filters( 'rtmedia_uploader_before_start_upload_button', "" )
                                . '<input type="button" class="start-media-upload" value="' . __('Start upload', 'rtmedia') .'"/>'
								. apply_filters( 'rtmedia_uploader_after_start_upload_button', "" )
                        . '</div>'
                        . '<div class="row">'
                        . wp_nonce_field ( 'rtmedia_' . get_current_user_id(), 'rtmedia_media_delete_nonce' )
                    . '<table id="rtMedia-queue-list" class="rtMedia-queue-list"><tbody></tbody></table></div>'
                    . '</div>' ),
                //'activity' => array( 'title' => __ ( 'File Upload', 'rtmedia' ), 'content' => '<div class="rtmedia-container"><div id="rtmedia-action-update"><input type="button" class="rtmedia-add-media-button" id="rtmedia-add-media-button-post-update"  value="' . __ ( "Attach Files", "rtmedia" ) . '" /></div><div id="div-attache-rtmedia"><div id="rtmedia-whts-new-upload-container" ><div id="rtmedia-whts-new-drag-drop-area" class="drag-drop"><input id="rtmedia-whts-new-upload-button" value="' . __ ( "Select", "rtmedia" ) . '" type="button" class="rtmedia-upload-input rtmedia-file" /></div><div id="rtMedia-update-queue-list"></div></div></div></div>' )
				'activity' => array( 'title' => __( 'File Upload', 'rtmedia' ), 'content' => '<div class="rtmedia-plupload-container"><div id="rtmedia-action-update"><button type="button" class="rtmedia-add-media-button" id="rtmedia-add-media-button-post-update"><i class="rtmicon-plus-circle"></i>' . apply_filters('rtmedia_attach_file_message', __( 'Attach Files', 'rtmedia' ) ) . '</button>' . $up_privacy . '</div><div id="rtmedia-whts-new-upload-container"></div></div><div class="rtmedia-plupload-notice"><div id="rtm-upload-start-notice"><span>' . __('Upload will start only after you enter content and click Post Update.', 'rtmedia' ) . '</span></div><table id="rtMedia-queue-list" class="rtMedia-queue-list"><tbody></tbody></table></div>')
            ),
//			'file_upload' => array( 'title' => __('File Upload','rtmedia'), 'content' => '<div id="rtmedia-uploader"><p>Your browser does not have HTML5 support.</p></div>'),
            'link_input' => array( 'title' => __ ( 'Insert from URL', 'rtmedia' ), 'content' => '<input type="url" name="bp-media-url" class="rtmedia-upload-input rtmedia-url" />' ),
        );
        $tabs = apply_filters ( 'rtmedia_upload_tabs', $tabs );

        $attr = $this->attributes;
        $mode = (isset ( $_GET[ 'mode' ] ) && array_key_exists ( $_GET[ 'mode' ], $tabs )) ? $_GET[ 'mode' ] : 'file_upload';
        if ( $attr && is_array ( $attr ) ) {
            foreach ( $attr as $key => $val ) {
                ?>
                <input type='hidden' id="rt_upload_hf_<?php echo sanitize_key ( $key ); ?>" value='<?php echo $val; ?>' name ='<?php echo $key; ?>' />
                <?php
            }
        }
        $upload_type = 'default';
        if ( isset ( $attr[ 'activity' ] ) && $attr[ 'activity' ] )
            $upload_type = 'activity';

        $uploadHelper = new RTMediaUploadHelper();
        include $this->locate_template ( $template_name );
    }

    /**
     * Template Locator
     *
     * @param type $template
     * @return string
     */
    protected function locate_template ( $template ) {
        $located = '';

        $template_name = $template . '.php';

        if ( ! $template_name )
            $located = false;
        if ( file_exists ( STYLESHEETPATH . '/rtmedia/upload/' . $template_name ) ) {
            $located = STYLESHEETPATH . '/rtmedia/upload/' . $template_name;
        } else if ( file_exists ( TEMPLATEPATH . '/rtmedia/upload/' . $template_name ) ) {
            $located = TEMPLATEPATH . '/rtmedia/upload/' . $template_name;
        } else {
            $located = RTMEDIA_PATH . 'templates/upload/' . $template_name;
        }

        return $located

        ;
    }

}
