/**
 * Created by Jignesh Nakrani on 18/8/15,
 * Updated By Yahil Madakiya on 26/9/16,
 * Moved to rtMedia core by Malav Vasita on 30/05/2018.
 */

/**
 * Add Validation For Extra New Field
 * Remove Functionality And Create Function For Decrease Code. :)
 * By: Yahil and Malav
 */
jQuery( document ).ready( function ( $ ) {
    var general_enable_upload_terms               = jQuery( 'input[name^="rtmedia-options[general_enable_upload_terms]"]' );
    var activity_enable_upload_terms              = jQuery( 'input[name^="rtmedia-options[activity_enable_upload_terms]"]' );
    var general_upload_terms_page_link            = jQuery( 'input[name^="rtmedia-options[general_upload_terms_page_link]"]' );
    var general_upload_terms_message              = jQuery( 'input[name^="rtmedia-options[general_upload_terms_message]"]' );
    var general_upload_terms_error_message        = jQuery( 'input[name^="rtmedia-options[general_upload_terms_error_message]"]' );
    var general_upload_terms_show_pricacy_message = jQuery( 'input[name^="rtmedia-options[general_upload_terms_show_pricacy_message]"]' );
    var general_upload_terms_privacy_message      = jQuery( 'textarea[name^="rtmedia-options[general_upload_terms_privacy_message]"]' );

    rtp_terms_option_toggle();
    jQuery( 'input[name^="rtmedia-options[general_enable_upload_terms]"], input[name^="rtmedia-options[activity_enable_upload_terms]"], input[name^="rtmedia-options[general_upload_terms_show_pricacy_message]"]' ).change( function(){
        rtp_terms_option_toggle();
    } );
    jQuery( '#bp-media-settings-boxes' ).on( 'submit', '#bp_media_settings_form, .rtmedia-settings-submit', function (e) {
        var return_code = true;

        if (return_code && general_enable_upload_terms.length > 0 && 'undefined' !== typeof general_enable_upload_terms ||
            return_code && activity_enable_upload_terms.length > 0 && typeof 'undefined' !== activity_enable_upload_terms ) {
            var error_msg = "";
            if ( true === general_enable_upload_terms.prop( 'checked' ) || true === activity_enable_upload_terms.prop( 'checked' ) ) {
                jQuery( '.error_msg' ).remove();
                jQuery( '.rtm-form-text' ).css( 'border-color', '#ddd' );
                if ( !/^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test( general_upload_terms_page_link.val() ) ) {
                    error_msg += rtm_upload_terms_error_msgs.valid_url;
                    return rtp_show_error_message ( general_upload_terms_page_link, error_msg );
                }

                /* Check "Terms of Service Message" Emply Or Not */
                if ( '' === general_upload_terms_message.val().trim() ) {
                    error_msg += rtm_upload_terms_error_msgs.terms_msg;
                    return rtp_show_error_message ( general_upload_terms_message, error_msg );
                }

                /* Check "Error Message" Emply Or Not */
                if (  '' === general_upload_terms_error_message.val().trim() ) {
                    error_msg += rtm_upload_terms_error_msgs.error_msg;
                    return rtp_show_error_message ( general_upload_terms_error_message, error_msg );
                }
            }
        }
        if ( return_code && general_upload_terms_show_pricacy_message.length > 0 && 'undefined' !== typeof general_upload_terms_show_pricacy_message ) {
            var error_msg = "";
            if ( general_upload_terms_show_pricacy_message.prop( 'checked' ) ) {
                jQuery( '.error_msg' ).remove();

                /* Check "Terms of Service Message" Emply Or Not */
                if ( general_upload_terms_privacy_message.val().trim() == '' ) {
                    error_msg += rtm_upload_terms_error_msgs.privacy_msg;
                    return rtp_show_error_message ( general_upload_terms_privacy_message, error_msg );
                }
            }
        }
    } );

    /* Show Error Message If Incorrect Validation  */
    function rtp_show_error_message( selector, error_msg ) {
        var elm_selector = jQuery( selector );
        elm_selector.focus();
        elm_selector.css( 'border-color', 'red' );
        if ( elm_selector.parent().length > 0 && 'error_msg' !== elm_selector.parent().attr( 'class' ) ) {
            var invalid_error_msg = jQuery( "<span />" ).attr( 'style', 'display:block' ).addClass( 'error_msg' ).html( error_msg );
            elm_selector.after( invalid_error_msg );
        }
        return_code = false;
        return false;
    }

    /**
     * Show/Hide InputBox
     * If Terms of Service Off For "Upload Screen" And "Activity Screen" Then Hide InputBox
     * By: Yahil And Malav
     */
    function rtp_terms_option_toggle() {
         if ( true === general_enable_upload_terms.prop( 'checked' ) || true === activity_enable_upload_terms.prop( 'checked' ) ) {
             general_upload_terms_page_link.closest( '.form-table' ).slideDown();
             general_upload_terms_message.closest( '.form-table' ).slideDown();
             general_upload_terms_error_message.closest( '.form-table' ).slideDown();
         } else {
             general_upload_terms_page_link.closest( '.form-table' ).slideUp();
             general_upload_terms_message.closest( '.form-table' ).slideUp();
             general_upload_terms_error_message.closest( '.form-table' ).slideUp();
         }

         // Show privacy message
         if( true === general_upload_terms_show_pricacy_message.prop( 'checked' ) ) {
            general_upload_terms_privacy_message.closest( '.form-table' ).slideDown();
         } else {
            general_upload_terms_privacy_message.closest( '.form-table' ).slideUp();
         }
    }
});

