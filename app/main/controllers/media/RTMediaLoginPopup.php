<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaLoginPopup
 *
 * @author sanket
 */
class RTMediaLoginPopup {
    
    function __construct() {
        add_action( 'rtmedia_album_gallery_actions', array( $this, 'rtmedia_add_upload_album_button_popup' ), 99 );
        add_action( 'rtmedia_media_gallery_actions', array( $this, 'rtmedia_add_upload_album_button_popup' ), 99 );
        add_action( 'rtmedia_before_media_gallery', array( $this, 'rtmedia_login_register_modal_popup' ) );
        add_action( 'rtmedia_before_album_gallery', array( $this, 'rtmedia_login_register_modal_popup' ) );
        // remove rtMedia Pro actions
		add_action( 'rtmedia_before_media_gallery', array( $this, 'remove_rtmedia_media_pro_hooks' ) );
        add_action( 'rtmedia_before_album_gallery', array( $this, 'remove_rtmedia_album_pro_hooks' ) );
    }
    
    function remove_rtmedia_media_pro_hooks() {
        remove_action( 'rtmedia_media_gallery_actions', 'rtmedia_add_upload_album_button', 99 );
        remove_action( 'rtmedia_before_media_gallery', 'rtmedia_login_register_modal' );
    }
    
    function remove_rtmedia_album_pro_hooks() {
        remove_action( 'rtmedia_album_gallery_actions', 'rtmedia_add_upload_album_button', 99 );
        remove_action( 'rtmedia_before_album_gallery', 'rtmedia_login_register_modal' );
    }
    
    function rtmedia_add_upload_album_button_popup() {
        if ( ! is_user_logged_in() ) {
            echo '<span><a href="#rtmedia-login-register-modal" class="primary rtmedia-upload-media-link rtmedia-modal-link" id="rtmedia-login-register-modal" title="' . __( 'Upload Media', 'rtmedia' ) . '"><i class="dashicons dashicons-upload rtmicon"></i>' . __( 'Upload' ) . '</a></span>';
        }
    }
    
    function rtmedia_login_register_modal_popup() {
        if ( ! is_user_logged_in() ) {
            ?>
            <div class="rtmedia-popup mfp-hide rtm-modal" id="rtmedia-login-register-modal">
                <div id="rtm-modal-container">
                    <h2 class="rtm-modal-title"><?php _e( 'Please login', 'rtmedia' ); ?></h2>

                    <p><?php _e( "You need to be logged in to upload Media or to create Album.", 'rtmedia' ); ?></p>

                    <p>
                        <?php echo __( 'Click ' ) . '<a href="' . wp_login_url() . '" title="' . __( 'Login', 'rtmedia' ) . '">' . __( 'HERE', 'rtmedia' ) . '</a>' . __( ' to login.', 'rtmedia' ); ?>
                    </p>
                </div>
            </div>
            <?php
        }
    }
    
}
