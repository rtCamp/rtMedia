jQuery(document).ready(function($) {
	console.log('rtmedia-admin.js loaded', window?.rtmedia_rtmedia_admin);	
	
    // Handle dismissal of the GoDAM banner
	$(document).on('click', '.godam-admin-banner .notice-dismiss', function() {
		// Send AJAX request to mark the banner as dismissed
		var data = {
		    action: 'install_godam_hide_admin_notice', // action hook
			security: window?.rtmedia_rtmedia_admin?.godam_banner_nonce // nonce for security
		};

		// Perform the AJAX request
		$.post(ajaxurl, data, function(response) {
			console.log('Notice dismissed and saved.');
		});
    });

	/**
	 * Disable inputs and change background color to differentiate disabled inputs,
	 * if 'Activity Streams' component is disabled in BuddyPress Settings.
	 */
	if ( ! window?.rtmedia_rtmedia_admin?.bp_is_active__activity ) {
		$('#rtmedia-bp-enable-activity, #rtmedia-enable-comment-activity, #rtmedia-enable-like-activity')
			.prop('disabled', true)
			.next().css('background-color', '#808080');
	
		$('#rtmedia-activity-feed-limit').prop('disabled', true);
	}

	/**
     * Disable inputs and change background color to differentiate disabled inputs,
	 * if 'User Groups' component is disabled in BuddyPress Settings.
	 */
	if ( ! window?.rtmedia_rtmedia_admin?.bp_is_active__groups) {
		$('#rtmedia-enable-on-group')
			.prop('disabled', true)
			.next().css('background-color', '#808080');
	}

	// Handle Notices
	// Addon update notice dismissal
	$( '.rtmedia-addon-update-notice.is-dismissible' ).on( 'click', '.notice-dismiss', function() {
		var data = {
			action: 'rtmedia_hide_addon_update_notice',
			_rtm_nonce: $('#rtmedia-addon-notice').val(),
		};
		$.post(ajaxurl, data, function (response) {
			$('.rtmedia-addon-update-notice').remove();
		});
	});

	// InspireBook release notice dismissal
	$( '.rtmedia-inspire-book-notice.is-dismissible' ).on( 'click', '.notice-dismiss', function() {
		var data = {
			action: 'rtmedia_hide_inspirebook_release_notice',
			_rtm_nonce: $('#rtmedia_hide_inspirebook_nonce').val()
		};
		$.post( ajaxurl, data, function ( response ) {
			$('.rtmedia-inspire-book-notice').remove();
		});
	});

	// Premium Addon notice dismissal
	$( '.rtmedia-pro-split-notice.is-dismissible' ).on( 'click', '.notice-dismiss', function() {
		var data = {action: 'rtmedia_hide_premium_addon_notice', _rtm_nonce: $('#rtm_nonce').val() };
		$.post( ajaxurl, data, function ( response ) {
			$('.rtmedia-pro-split-notice').remove();
		});
	});

	// Transcoder notice dismissal
	$( '.install-transcoder-notice.is-dismissible' ).on( 'click', '.notice-dismiss', function() {
		var data = {
			action: 'install_transcoder_hide_admin_notice',
			install_transcoder_notice_nonce: $('#install_transcoder_hide_notice_nonce').val()
		};
		$.post( ajaxurl, data, function ( response ) {
			$('.install-transcoder-notice').remove();
		});
	});

	// Media Size Import notice dismissal
	$( '#rtmedia_hide_media_size_import_notice' ).on( 'click', function() {
		console.log('clicked on rtmedia_hide_media_size_import_notice');
		var data = {action: 'rtmedia_hide_media_size_import_notice'};
		jQuery.post(ajaxurl, data, function (response) {
			response = response.trim();
			if (response === '1')
				jQuery('.rtmedia-media-size-import-error').remove();
		});
	});	

	// Template Override notice dismissal
	$( '#rtmedia-hide-template-notice' ).on( 'click', function(e) {
		e.preventDefault();
		var nonce = $(this).data('nonce');
		var data = {action: 'rtmedia_hide_template_override_notice', _rtm_nonce: nonce};
		jQuery.post(ajaxurl, data, function (response) {
			response = response.trim();
			if (response === '1')
				jQuery('.rtmedia-update-template-notice').remove();
		});
	});

});