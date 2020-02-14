// check for terms and condition

/**
 * Remove Terms Warning Popup,
 * Add Warning Message Before Submit Button
 * By: Malav Vasita <malav.vasita@rtcamp.com>
 */
if ( 'object' === typeof rtMediaHook ) {
	rtMediaHook.register( 'rtmedia_js_upload_file', function ( args ) {
		if ( false === args ) {
			return args;
		}

		var terms_conditions_checkbox = jQuery( '#rtmedia_upload_terms_conditions' );

		if ( terms_conditions_checkbox.length > 0 ) {
			if ( ! terms_conditions_checkbox.is( ':checked' ) ) {
				rtp_display_terms_warning( terms_conditions_checkbox.parent( '.rtmedia-upload-terms' ), rtmedia_upload_terms_data.message );
				return false;
			}
		}

		return true;
	} );

	rtMediaHook.register(
		'rtmedia_js_before_activity_added',
		function ( args ) {
			var terms_conditions_checkbox, form;
			var whats_new_submit = jQuery( '#aw-whats-new-submit' );

			if ( undefined !== args && false !== args && undefined !== args.src && 'activity' === args.src ) {
				form                      = jQuery( '#whats-new-form' );
				terms_conditions_checkbox = form.find( '#rtmedia_upload_terms_conditions' );
			} else {
				terms_conditions_checkbox = jQuery( '#rtmedia_upload_terms_conditions' );
			}

			if ( terms_conditions_checkbox.length > 0 ) {
				terms_conditions_checkbox.removeAttr( 'disabled' );

				if ( false == args ) {
					whats_new_submit.removeAttr( 'disabled' );
					whats_new_submit.removeClass( 'loading' );
					return args;
				}

				if ( ! terms_conditions_checkbox.is( ':checked' ) ) {
					whats_new_submit.removeAttr( 'disabled' );
					whats_new_submit.removeClass( 'loading' );
					if ( undefined !== args && false !== args && undefined !== args.src && 'activity' === args.src ) {
						rtp_display_terms_warning( form.find( '.rtmedia-upload-terms' ), rtmedia_upload_terms_data.message );
					} else {
						rtp_display_terms_warning( terms_conditions_checkbox.parent( '.rtmedia-upload-terms' ), rtmedia_upload_terms_data.message );
					}
					return false;
				}
			}
			return true;
		}
	);

	/**
	 * When Select Attribute for media [ rtmedia-custom-attributes: Add-Ons ] Issue:8,
	 * This Hook returns false that's why the attributes were not saved,
	 * Added: 'return true;'
	 * By: Yahil
	 */
	rtMediaHook.register( 'rtmedia_js_after_file_upload', function () {

		var terms_conditions_checkbox = jQuery( '#rtmedia-upload-container #rtmedia_upload_terms_conditions' );
		if ( terms_conditions_checkbox.length > 0 ) {
			terms_conditions_checkbox.removeAttr( 'checked' );
			jQuery( '.rt_alert_msg' ).remove();
		}
		return true;
	});

	rtMediaHook.register( 'rtmedia_js_after_activity_added', function() {
		var rtmedia_terms_conditions = jQuery( '#rtmedia_upload_terms_conditions' );
		if ( rtmedia_terms_conditions && rtmedia_terms_conditions.is(':checked') ) {
			rtmedia_terms_conditions.prop( 'checked', false );
		}
	} );
}

jQuery(document).ready(function () {
	var terms_conditions_checkbox = jQuery( '#rtmedia_upload_terms_conditions' );

	if ( terms_conditions_checkbox.length ) {
		terms_conditions_checkbox.on( 'click', function () {

			// If start upload button exist, then focus to that button.
			var upload_start_btn = jQuery('.start-media-upload');
			if ( upload_start_btn.length ) {
				upload_start_btn.focus();
			}

			// Show error message if terms-condition is not checked.
			if ( terms_conditions_checkbox.is( ':checked' ) ) {
				var alter_msg_span = terms_conditions_checkbox.siblings( 'span.rt_alert_msg' );
				if ( 0 < alter_msg_span.length ) {
					alter_msg_span.remove();
				} else {
					terms_conditions_checkbox.parent().siblings( 'span.rt_alert_msg' ).remove();
				}

			} else {
				rtp_display_terms_warning( terms_conditions_checkbox.parent( '.rtmedia-upload-terms' ), rtmedia_upload_terms_data.message );
			}

			if ( typeof rtmedia_direct_upload_enabled !== 'undefined' && rtmedia_direct_upload_enabled == '1' ) {
				var whats_new_submit = jQuery( '#aw-whats-new-submit' );
				if ( whats_new_submit.length > 0 ) {
					if ( jQuery( '#whats-new' ).val().trim() !== '' || jQuery( '#rtmedia_uploader_filelist' ).children( 'li' ).length > 0) {
						whats_new_submit.trigger( 'click' );
					}
				} else {
					jQuery( '.start-media-upload' ).trigger( 'click' );
				}
			}
		});
	}

});

/**
 * Show Error Message On Admin Side
 * Handle error on rtMedia settings
 * By: Malav Vasita
 */
function rtp_display_terms_warning ( selector, rt_alert_msg ) {
	if ( ! jQuery( '.rt_alert_msg' ).length ) {
		var invalid_error_msg = jQuery( "<span />" ).attr( 'style', 'color:red; display:block; clear:both;' ).addClass( 'rt_alert_msg' ).empty().append( rt_alert_msg );
		selector.after( invalid_error_msg );
	}
}

/**
 * Show/Hide Privacy Message On Front End
 * Handle privacy message on website
 * By: Malav Vasita
 */
function handle_privacy_message() {
	jQuery( '#close_rtm_privacy_message' ).on( 'click', function(c) {
		jQuery( '.privacy_message_wrapper' ).fadeOut( 'slow', function(c) {
			jQuery( '.privacy_message_wrapper' ).remove();
			jQuery.cookie( "rtm_show_privacy_message", "view", { expires : 1, path: "/" } );
		});
	});
}

jQuery( document ).ready( function() {
	handle_privacy_message();
} );
