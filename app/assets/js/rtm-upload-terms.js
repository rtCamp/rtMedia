var rtmediaTermsConditionsElement;
/**
 * Check for terms and condition
 *
 * Remove Terms Warning Popup,
 * Add Warning Message Before Submit Button
 * By: Malav Vasita <malav.vasita@rtcamp.com>
 */
if ( 'object' === typeof rtMediaHook ) {

    /**
     * Check terms condition checkbox before uploading files.
     * 
     * @param {object/boolean} args Arguments passed when calling this hook.
     * @return {boolean}
     */
    rtMediaHook.register( 'rtmedia_js_before_upload', function ( args ) {
        if ( ! args ) {
            return args;
        }

        if ( 'undefined' === typeof args.uploader || 'undefined' === typeof args.uploader.settings || 'undefined' === typeof args.uploader.settings.multipart_params || 'undefined' === typeof args.src ) {
            return ( 'boolean' === typeof args ? args : true );
        }

        var multipart_params = args.uploader.settings.multipart_params;
        var request_key = false;
        var terms_key = false;
        var isTermsEnabled = false;

        if ( 'activity' === args.src ) {
            request_key = 'activity_terms_condition_request';
            terms_key = 'activity_terms_condition';

            if ( 'true' === rtmedia_upload_terms_data.activity_terms_enabled ) {
                isTermsEnabled = true;
            }
        } else if ( 'uploader' === args.src ) {
            request_key = 'uploader_terms_condition_request';
            terms_key = 'uploader_terms_condition';

            if ( 'true' === rtmedia_upload_terms_data.uploader_terms_enabled ) {
                isTermsEnabled = true;
            }
        }

        if ( ! isTermsEnabled ) {
            return true;
        }

        if ( request_key && terms_key && isTermsEnabled ) {
            multipart_params[ request_key ] = 'true';

            if ( rtmediaTermsConditionsElement && rtmediaTermsConditionsElement.length > 0 ) {
                multipart_params[ terms_key ] = ( rtmediaTermsConditionsElement.prop( 'checked' ) ? 'true' : 'false' );
            } else {
                var terms = $( '#rtmedia_upload_terms_conditions' );
                if ( terms.length > 0 ) {
                    rtmediaTermsConditionsElement = terms;

                    multipart_params[ terms_key ] = ( terms.prop( 'checked' ) ? 'true' : 'false' );
                }
            }
        }

        args.uploader.settings.multipart_params = multipart_params;

        return true;
    } );

    /**
     * Check for the terms checkbox to be checked before the media is uploaded.
     * Show warning if unchecked else proceed.
     
     * @param {object/boolean} args Arguments passed when calling this hook.
     * @return {boolean}
     */
    rtMediaHook.register( 'rtmedia_js_upload_file', function ( args ) {
        if ( ! args ) {
            return args;
        }

        var src = false;
        if ( false !== args && 'undefined' !== typeof args.src ) {
            src = args.src;
        }

        var parent = false;
        var isTermsEnabled = false;

        var terms = jQuery( '#rtmedia_upload_terms_conditions' );
        if ( 'uploader' === src ) {

            if ( 0 === terms.length ) {
                parent = jQuery( '#drag-drop-area' );
            } else {
                parent = terms.parent( '.rtmedia-upload-terms' );
            }

            if ( ( 'undefined' !== typeof rtmedia_upload_terms_data && 'undefined' === typeof rtmedia_upload_terms_data.uploader_terms_enabled ) || 'true' === rtmedia_upload_terms_data.uploader_terms_enabled ) {
                isTermsEnabled = true;
            }
        } else if ( 'activity' === src ) {

            if ( 0 === terms.length ) {
                parent = jQuery( '#whats-new-options' );
            } else {
                parent = terms.parent( '.rtmedia-upload-terms' );
            }

            if ( 'true' === rtmedia_upload_terms_data.activity_terms_enabled ) {
                isTermsEnabled = true;
            }
        } else if ( jQuery( '#drag-drop-area' ).length ) {
            if ( 0 === terms.length ) {
                parent = jQuery( '#drag-drop-area' );
            } else {
                parent = terms.parent( '.rtmedia-upload-terms' );
            }

            if ( ( 'undefined' !== typeof rtmedia_upload_terms_data && 'undefined' === typeof rtmedia_upload_terms_data.uploader_terms_enabled ) || 'true' === rtmedia_upload_terms_data.uploader_terms_enabled ) {
                isTermsEnabled = true;
            }
        }

        if ( ! isTermsEnabled ) {
            return ( 'boolean' === typeof args ? args : true );
        }

        if ( 0 === terms.length ) {
            rtp_display_terms_warning( parent, rtmedia_upload_terms_data.message );
            return false;
        }

        rtmediaTermsConditionsElement = terms;
        if ( terms.prop( 'checked' ) ) {
            return true;
        } else {
            rtp_display_terms_warning( parent, rtmedia_upload_terms_data.message );
            return false;
        }

    } );

    /**
     * Show the warning message if the terms checkbox is unchecked before posting the media update.
     */
    rtMediaHook.register( 'rtmedia_js_before_activity_added', function ( args ) {

        var terms_conditions_checkbox, form;
        var whats_new_submit = jQuery( '#aw-whats-new-submit' );

        if ( args && 'activity' === args.src ) {
            if ( 'false' === rtmedia_upload_terms_data.activity_terms_enabled ) {
                return true;
            }

            form = jQuery( '#whats-new-form' );
            terms_conditions_checkbox = form.find( '#rtmedia_upload_terms_conditions' );
        } else {
            terms_conditions_checkbox = jQuery( '#rtmedia_upload_terms_conditions' );
        }

        if ( 1 === terms_conditions_checkbox.length ) {
            terms_conditions_checkbox.removeAttr( 'disabled' );
            rtmediaTermsConditionsElement = terms_conditions_checkbox;

            if ( false === args ) {
                whats_new_submit.removeAttr( 'disabled' );
                whats_new_submit.removeClass( 'loading' );

                return args;
            }

            if ( ! terms_conditions_checkbox.is( ':checked' ) ) {
                whats_new_submit.removeAttr( 'disabled' );
                whats_new_submit.removeClass( 'loading' );

                if ( args && 'activity' === args.src ) {
                    rtp_display_terms_warning( form.find('.rtmedia-upload-terms'), rtmedia_upload_terms_data.message );
                } else {
                    rtp_display_terms_warning( terms_conditions_checkbox.parent( '.rtmedia-upload-terms' ), rtmedia_upload_terms_data.message );
                }

                return false;
            } else {
                terms_conditions_checkbox.prop( 'disabled', true );
            }
        } else {
            rtp_display_terms_warning( form.find( '#whats-new-options' ), rtmedia_upload_terms_data.message );

            return false;
        }

        return true;

    } );

    /**
     * When Select Attribute for media [ rtmedia-custom-attributes: Add-Ons ] Issue:8,
     * This Hook returns false that's why the attributes were not saved,
     * Added: 'return true;'
     * By: Yahil
     */
    rtMediaHook.register( 'rtmedia_js_after_file_upload', function () {

        var terms_conditions_checkbox = jQuery( '#rtmedia-upload-container #rtmedia_upload_terms_conditions' );

        if ( 1 === terms_conditions_checkbox.length ) {
            terms_conditions_checkbox.removeAttr( 'checked' );
            jQuery( '.rt_alert_msg' ).remove();
        }

        return true;

    });

    /**
     * Uncheck the terms checkbox after the activity is posted successfully.
     */
    rtMediaHook.register( 'rtmedia_js_after_activity_added', function () {

        jQuery( '#rtmedia_upload_terms_conditions' ).removeAttr( 'checked' ).removeAttr( 'disabled' );

        return true;

    } );
}

jQuery( document ).ready( function () {
    var terms_conditions_checkbox = jQuery( '#rtmedia_upload_terms_conditions' );

    /**
     * Fires before ajax request.
     * Send terms condition checkbox status on backend to validate it on server side.
     */
    jQuery.ajaxPrefilter( function ( options, originalOptions, jqXHR ) {
        if ( 'undefined' === typeof options || 'undefined' === typeof options.data || 'undefined' === typeof originalOptions || 'undefined' === typeof originalOptions.data ) {
            return true;
        }

        if ( 'post_update' === originalOptions.data.action && terms_conditions_checkbox.length ) {
            options.data += '&rtmedia_upload_terms_conditions=' + terms_conditions_checkbox.prop( 'checked' );
        }

        return true;
    } );


    terms_conditions_checkbox.on( 'click', function () {

        // Focus on `start upload` button.
        var upload_start_btn = jQuery( '.start-media-upload' );
        upload_start_btn.focus();

        // Show error message if terms-condition is not checked.
        if ( terms_conditions_checkbox.is( ':checked' ) ) {
            var alter_msg_span = terms_conditions_checkbox.siblings( 'span.rt_alert_msg' );

            if ( 1 === alter_msg_span.length ) {
                alter_msg_span.remove();
            } else {
                terms_conditions_checkbox.parent().siblings( 'span.rt_alert_msg' ).remove();
            }

        } else {
            rtp_display_terms_warning( terms_conditions_checkbox.parent( '.rtmedia-upload-terms' ), rtmedia_upload_terms_data.message );
        }

        if ( 'undefined' !== typeof rtmedia_direct_upload_enabled && '1' === rtmedia_direct_upload_enabled ) {
            var whats_new_submit = jQuery( '#aw-whats-new-submit' );

            if ( whats_new_submit.length ) {

                if ( '' !== jQuery( '#whats-new' ).val().trim() || jQuery( '#rtmedia_uploader_filelist' ).children( 'li' ).length ) {
                    whats_new_submit.trigger( 'click' );
                }

            } else {
                upload_start_btn.trigger( 'click' );
            }

        }
    } );

    // Handle privacy message on website.
    handle_privacy_message();

} );

/**
 * Show Error Message On Admin Side
 * Handle error on rtMedia settings
 * By: Malav Vasita
 */
function rtp_display_terms_warning( selector, rt_alert_msg ) {

    if ( 0 === jQuery( '.rt_alert_msg' ).length ) {
        var invalid_error_msg = jQuery( '<span />' ).attr( 'style', 'color:red; display:block; clear:both;' ).addClass( 'rt_alert_msg' ).empty().append( rt_alert_msg );
        selector.after( invalid_error_msg );
    }

}

/**
 * Show/Hide Privacy Message On Front End
 * Handle privacy message on website
 * By: Malav Vasita
 */
function handle_privacy_message() {

    jQuery( '#close_rtm_privacy_message' ).on( 'click', function () {
        var privacy_wrapper = jQuery( '.privacy_message_wrapper' );
        privacy_wrapper.fadeOut( 'slow', function () {
            privacy_wrapper.remove();
            jQuery.cookie(
                'rtm_show_privacy_message',
                'view',
                {
                    expires: 1,
                    path: '/'
                }
            );
        } );
    } );

}
