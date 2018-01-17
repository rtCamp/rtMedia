var rtMagnificPopup;
var rtm_masonry_container;
var comment_media = false;
function apply_rtMagnificPopup( selector ) {
	jQuery( 'document' ).ready( function( $ ) {
		var rt_load_more = '';
		if ( typeof ( rtmedia_load_more ) === 'undefined' ) {
			rt_load_more = 'Loading media';
		} else {
			rt_load_more = rtmedia_load_more;
		}
		if ( typeof( rtmedia_lightbox_enabled ) != 'undefined' && rtmedia_lightbox_enabled == '1' ) { // If lightbox is enabled.

			if ( $( '.activity-item .rtmedia-activity-container .rtmedia-list-item > a' ).siblings( 'p' ).children( 'a' ).length > 0 ) {
				$( '.activity-item .rtmedia-activity-container .rtmedia-list-item > a' ).siblings( 'p' ).children( 'a' ).addClass( 'no-popup' );
			}

			rtMagnificPopup = jQuery( selector ).magnificPopup( {
				delegate: 'a:not(.no-popup, .mejs-time-slider, .mejs-volume-slider, .mejs-horizontal-volume-slider)',
				type: 'ajax',
				tLoading: rt_load_more + ' #%curr%...',
				mainClass: 'mfp-img-mobile',
				preload: [ 1, 3 ],
				closeOnBgClick: true,
				gallery: {
					enabled: true,
					navigateByImgClick: true,
					arrowMarkup: '', // Disabled default arrows
					preload: [ 0, 1 ] // Will preload 0 - before current, and 1 after the current image
				},
				image: {
					tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
					titleSrc: function( item ) {
						return item.el.attr( 'title' ) + '<small>by Marsel Van Oosten</small>';
					}
				},
				callbacks: {
					ajaxContentAdded: function() {
						mfp = jQuery.magnificPopup.instance;
						if ( jQuery( mfp.items ).size() === 1 ) {
							jQuery( '.mfp-arrow' ).remove();
						}
						// When last second media is encountered in lightbox, load more medias if available
						var mfp = jQuery.magnificPopup.instance;
						var current_media = mfp.currItem.el;
						var li = current_media.parent();
						if ( ! li.is( 'li' ) ) {
							li = li.parent();
						}
						if ( ( li.is( ':nth-last-child(2)' ) || li.is( ':last-child' ) ) && li.find( 'a' ).hasClass('rtmedia-list-item-a') ) { // If its last second media
							var last_li = li.next();
							if ( jQuery( '#rtMedia-galary-next' ).css( 'display' ) == 'block' ) { // If more medias are available
								jQuery( '#rtMedia-galary-next' ).click(); // Load more
							}
						}

						var items = mfp.items.length;
						if ( mfp.index == ( items - 1 ) && ! ( li.is( ':last-child' ) ) ) {
							current_media.click();
							return;
						}

						var settings = { };

						if ( typeof _wpmejsSettings !== 'undefined' ) {
							settings.pluginPath = _wpmejsSettings.pluginPath;
						}
						var $single_meta_h = jQuery( ".rtmedia-container .rtmedia-single-meta" ).height();

						var probablymobile = false;
						// check if it's is an mobile or not
						if( typeof mfp != 'undefined' && typeof mfp.probablyMobile != 'undefined' && mfp.probablyMobile == true ){
							probablymobile = true;
						}
						/* adding auto play button in the popup */
						$( '.mfp-content .rtmedia-single-media .wp-audio-shortcode,.mfp-content .rtmedia-single-media .wp-video-shortcode,.mfp-content .rtmedia-single-media .bp_media_content video' ).attr( 'autoplay', true );

						// if it's mobile then add mute button to it
						if( probablymobile ){
							$( '.mfp-content .rtmedia-single-media .wp-video-shortcode,.mfp-content .rtmedia-single-media .bp_media_content video' ).attr( 'muted', false );
						}

						$( '.mfp-content .rtmedia-single-media .wp-audio-shortcode,.mfp-content .rtmedia-single-media .wp-video-shortcode,.mfp-content .rtmedia-single-media .bp_media_content video' ).mediaelementplayer( {
							// This is required to work with new MediaElement version.
                                                        classPrefix: 'mejs-',
                                                        // If the <video width> is not specified, this is the default
							defaultVideoWidth: 480,
							// always show the volume button
							hideVolumeOnTouchDevices: false,
							features: ['playpause','progress','current','volume','fullscreen'],
							// If the <video height> is not specified, this is the default
							defaultVideoHeight: 270,
							// always show control for mobile
							alwaysShowControls: probablymobile,
							enableAutosize: true,
							clickToPlayPause: true,
							 // if set, overrides <video height>
    						videoHeight: -1,
							success: function( mediaElement, domObject ) {
								mediaElement.addEventListener('loadeddata', function (e) {
									var $video_h = $( mediaElement ).height();
									var $window_h = $( window ).height();
									var $rtm_ltb = jQuery( "div.rtm-ltb-action-container" ).height();
									var $rtm_ltb = $rtm_ltb + 50;
									var $new_video_h =  $single_meta_h - $rtm_ltb;
									if( $video_h > $window_h ){
										jQuery( ".rtmedia-container #rtmedia-single-media-container .mejs-container" ).attr( "style", 'height:'+$new_video_h+'px !important; transition:0.2s' );
									}
			                    }, false);
								// Call the play method

								// check if it's mobile
								if( probablymobile && $( mediaElement ).hasClass( "wp-video-shortcode" ) ){
									jQuery( 'body' ).on('touchstart', '.mejs-overlay-button' , function(e) {
										mediaElement.paused ? mediaElement.play() : mediaElement.pause();
									});
								} else {
									mediaElement.play();
								}
							}
						} );
						$( '.mfp-content .mejs-audio .mejs-controls' ).css( 'position', 'relative' );
						rtMediaHook.call( 'rtmedia_js_popup_after_content_added', [ ] );

						if( typeof bp != 'undefined' ){
							if( typeof bp.mentions != 'undefined' && typeof bp.mentions.users != 'undefined' ){
							    $( '#atwho-container #atwho-ground-comment_content' ).remove();
							    $( '#comment_content' ).bp_mentions( bp.mentions.users );
							}
						}

						rtmedia_reset_video_and_audio_for_popup();

						apply_rtMagnificPopup( '.rtmedia-list-media.rtm-gallery-list, .rtmedia-activity-container ul.rtmedia-list, #bp-media-list,.bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content, .rtm-bbp-container, ul.rtm-comment-container' );
					},
					close: function( e ) {
						//Console.log(e);
						rtmedia_single_page_popup_close();
					},
					BeforeChange: function( e ) {
						//Console.log(e);
					}
				}
			} );
		}
		/**
		 * string replace Save From ok
		 * By: Yahil
		 */
		jQuery( document ).ajaxComplete(function(){
			jQuery('[id^=imgedit-leaving]').filter(function(){
				var text = jQuery(this).text();
				jQuery(this).text(text.replace('OK', 'Save'));
			});
		});
	} );
}
var rtMediaHook = {
	hooks: [ ],
	is_break: false,
	register: function( name, callback ) {
		if ( 'undefined' == typeof ( rtMediaHook.hooks[name] ) ) {
			rtMediaHook.hooks[name] = [ ];
		}
		rtMediaHook.hooks[name].push( callback );
	},
	call: function( name, arguments ) {
		if ( 'undefined' != typeof ( rtMediaHook.hooks[name] ) ) {
			for ( i = 0; i < rtMediaHook.hooks[name].length; ++i ) {
				if ( true != rtMediaHook.hooks[name][i]( arguments ) ) {
					rtMediaHook.is_break = true;
					return false;
					break;
				}
			}
 }
		return true;
	}
};

//Drop-down js
function rtmedia_init_action_dropdown( parent ) {
	var all_ul;
	var curr_ul;
	jQuery( parent+' .click-nav > span,'+parent+' .click-nav > div' ).toggleClass( 'no-js js' );
 	jQuery( parent+' .click-nav .js ul' ).hide();
 	jQuery( parent+' .click-nav .clicker' ).click( function( e ) {
		all_ul = jQuery( '#rtm-media-options .click-nav .clicker' ).next( 'ul' );
		curr_ul = jQuery( this ).next( 'ul' );
		jQuery.each( all_ul, function( index, value ) {
			if ( jQuery( value ).html() != curr_ul.html() ) {     // Check clicked option with other options
				jQuery( value ).hide();
			}
		} );
		jQuery( curr_ul ).toggle();
		e.stopPropagation();
	} );
}

jQuery( 'document' ).ready( function( $ ) {

	jQuery( '.rtmedia-uploader-div' ).css({
		'opacity': '1',
		'display': 'block',
		'visibility': 'visible'
	});

	jQuery( ' #whats-new-options ' ).css({
		'opacity': '1',
	});

	// Tabs
	if ( typeof $.fn.rtTab !== 'undefined' ) {
		$( '.rtm-tabs' ).rtTab();
	}

	// Open magnific popup as modal for create album/playlist
	if ( jQuery( '.rtmedia-modal-link' ).length > 0 ) {
		$( '.rtmedia-modal-link' ).magnificPopup( {
			type: 'inline',
			midClick: true, // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href
			closeBtnInside: true,
		} );
	}

	$( '#rt_media_comment_form' ).submit( function( e ) {
		if ( $.trim( $( '#comment_content' ).val() ) == '' ) {
			if ( jQuery( '#rtmedia-single-media-container' ).length == 0 ) {
				rtmedia_gallery_action_alert_message( rtmedia_empty_comment_msg, 'warning' );
			} else {
				rtmedia_single_media_alert_message( rtmedia_empty_comment_msg, 'warning' );
			}
			return false;
		} else {
			return true;
		}

	} );

	//Remove title from popup duplication
	$( 'li.rtmedia-list-item p a' ).each( function( e ) {
		$( this ).addClass( 'no-popup' );
	} );

	//Remove title from popup duplication
	$( 'li.rtmedia-list-item p a' ).each(function( e ) {
		$( this ).addClass( 'no-popup' );
	});
	//Rtmedia_lightbox_enabled from setting
	if ( typeof( rtmedia_lightbox_enabled ) != 'undefined' && rtmedia_lightbox_enabled == '1' ) {
		apply_rtMagnificPopup( '.rtmedia-list-media.rtm-gallery-list, .rtmedia-activity-container ul.rtmedia-list, #bp-media-list,.bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content, .rtm-bbp-container, ul.rtm-comment-container' );
	}

	jQuery.ajaxPrefilter(function( options, originalOptions, jqXHR ) {
		try {
	        if ( originalOptions.data == null || typeof( originalOptions.data ) == 'undefined' || typeof( originalOptions.data.action ) == 'undefined' ) {
	            return true;
	        }
	    } catch ( e ) {
	        return true;
	    }

	    // Handle lightbox in BuddyPress activity loadmore
	    if ( originalOptions.data.action == 'activity_get_older_updates' ) {
		    var orignalSuccess = originalOptions.success;
		    options.success = function( response ) {
				orignalSuccess( response );
				apply_rtMagnificPopup( '.rtmedia-activity-container ul.rtmedia-list, #bp-media-list, .bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content' );
				rtMediaHook.call( 'rtmedia_js_after_activity_added', [] );
		    };
		} else if ( originalOptions.data.action == 'get_single_activity_content' ) {
		    // Handle lightbox in BuddyPress single activity loadmore
		    var orignalSuccess = originalOptions.success;
		    options.success = function( response ) {
			    orignalSuccess( response );
			    setTimeout( function() {
				    apply_rtMagnificPopup( '.rtmedia-activity-container ul.rtmedia-list, #bp-media-list, .bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content' );
				    jQuery( 'ul.activity-list li.rtmedia_update:first-child .wp-audio-shortcode, ul.activity-list li.rtmedia_update:first-child .wp-video-shortcode' ).mediaelementplayer( {
					    // This is required to work with new MediaElement version.
                                            classPrefix: 'mejs-',
                                            // If the <video width> is not specified, this is the default
					    defaultVideoWidth: 480,
					    // If the <video height> is not specified, this is the default
					    defaultVideoHeight: 270
				    } );
			    }, 900 );
		    };
	    }
	});

	jQuery.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
		try {
			if ( originalOptions.data == null || typeof ( originalOptions.data ) == 'undefined' || typeof ( originalOptions.data.action ) == 'undefined' ) {
				return true;
			}
		} catch ( e ) {
			return true;
		}
		if ( originalOptions.data.action == 'activity_get_older_updates' ) {
			var orignalSuccess = originalOptions.success;
			options.success = function( response ) {
				orignalSuccess( response );
				apply_rtMagnificPopup( '.rtmedia-activity-container ul.rtmedia-list, #bp-media-list, .bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content' );

				jQuery( 'ul.activity-list li.rtmedia_update div.rtmedia-item-thumbnail > audio.wp-audio-shortcode, ul.activity-list li.rtmedia_update div.rtmedia-item-thumbnail > video.wp-video-shortcode' ).mediaelementplayer( {
					// This is required to work with new MediaElement version.
                                        classPrefix: 'mejs-',
                                        // If the <video width> is not specified, this is the default
					defaultVideoWidth: 480,
					// If the <video height> is not specified, this is the default
					defaultVideoHeight: 270
				} );

				setTimeout( function() {
					rtmedia_activity_stream_comment_media();
				}, 900 );

				rtMediaHook.call( 'rtmedia_js_after_activity_added', [ ] );
			};
		}
	} );

	jQuery( '.rtmedia-container' ).on( 'click', '.select-all', function( e ) {
		jQuery( this ).toggleClass( 'unselect-all' ).toggleClass( 'select-all' );
		jQuery( this ).attr( 'title', rtmedia_unselect_all_visible );
		jQuery( '.rtmedia-list input' ).each( function() {
			jQuery( this ).prop( 'checked', true );
		} );
		jQuery( '.rtmedia-list-item' ).addClass( 'bulk-selected' );
	} );



	jQuery( '.rtmedia-container' ).on( 'click', '.unselect-all', function( e ) {
		jQuery( this ).toggleClass( 'select-all' ).toggleClass( 'unselect-all' );
		jQuery( this ).attr( 'title', rtmedia_select_all_visible );
		jQuery( '.rtmedia-list input' ).each( function() {
			jQuery( this ).prop( 'checked', false );
		} );
		jQuery( '.rtmedia-list-item' ).removeClass( 'bulk-selected' );
	} );

	jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-move', function( e ) {
		jQuery( '.rtmedia-delete-container' ).slideUp();
		jQuery( '.rtmedia-move-container' ).slideToggle();
	} );

	jQuery( '#rtmedia-create-album-modal' ).on( 'click', '#rtmedia_create_new_album', function( e ) {
		$albumname = jQuery( '<span/>' ).text( jQuery.trim( jQuery( '#rtmedia_album_name' ).val() ) ).html();
		$album_description = jQuery( '#rtmedia_album_description' );
		$context = jQuery.trim( jQuery( '#rtmedia_album_context' ).val() );
		$context_id = jQuery.trim( jQuery( '#rtmedia_album_context_id' ).val() );
		$privacy = jQuery.trim( jQuery( '#rtmedia_select_album_privacy' ).val() );
		$create_album_nonce = jQuery.trim( jQuery( '#rtmedia_create_album_nonce' ).val() );

		if ( $albumname != '' ) {
			var data = {
				action: 'rtmedia_create_album',
				name: $albumname,
				description: $album_description.val(),
				context: $context,
				context_id: $context_id,
				create_album_nonce: $create_album_nonce
			};

			if ( $privacy !== '' ) {
				data[ 'privacy' ] = $privacy;
			}

			// Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$( '#rtmedia_create_new_album' ).attr( 'disabled', 'disabled' );
			var old_val = $( '#rtmedia_create_new_album' ).html();
			$( '#rtmedia_create_new_album' ).prepend( '<img src=\'' + rMedia_loading_file + '\' />' );
			jQuery.post( rtmedia_ajax_url, data, function( response ) {
				if ( typeof response.album != 'undefined' ) {
					response = jQuery.trim( response.album );
					var flag = true;
					$album_description.val('');
					$( '#rtmedia_album_name' ).focus();

					jQuery( '.rtmedia-user-album-list' ).each( function() {
						jQuery( this ).children( 'optgroup' ).each( function() {
							if ( jQuery( this ).attr( 'value' ) === $context ) {
								flag = false;

								jQuery( this ).append( '<option value="' + response + '">' + $albumname + '</option>' );

								return;
							}
						} );

						if ( flag ) {
							var label = $context.charAt( 0 ).toUpperCase() + $context.slice( 1 ) + ' ' + rtmedia_main_js_strings.rtmedia_albums;

							var opt_html = '<optgroup value="' + $context + '" label="' + label + '"><option value="' + response + '">' + $albumname + '</option></optgroup>';

							jQuery( this ).append( opt_html );
						}
					} );

					jQuery( 'select.rtmedia-user-album-list option[value="' + response + '"]' ).prop( 'selected', true );
					jQuery( '.rtmedia-create-new-album-container' ).slideToggle();
					jQuery( '#rtmedia_album_name' ).val( '' );
					jQuery( '#rtmedia-create-album-modal' ).append( '<div class=\'rtmedia-success rtmedia-create-album-alert\'><b>' + $albumname + '</b>' + rtmedia_album_created_msg + '</div>' );

					setTimeout( function() {
						jQuery( '.rtmedia-create-album-alert' ).remove();
					}, 4000 );

					setTimeout( function() {
						galleryObj.reloadView();
						jQuery( '.close-reveal-modal' ).click();
					}, 2000 );
				} else if ( typeof response.error != 'undefined' ) {
					rtmedia_gallery_action_alert_message( response.error, 'warning' );
				} else {
					rtmedia_gallery_action_alert_message( rtmedia_something_wrong_msg, 'warning' );
				}

				$( '#rtmedia_create_new_album' ).removeAttr( 'disabled' );
				$( '#rtmedia_create_new_album' ).html( old_val );
			} );
		} else {
			rtmedia_gallery_action_alert_message( rtmedia_empty_album_name_msg, 'warning' );
		}
	} );

	jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-delete-selected', function( e ) {
		if ( jQuery( '.rtmedia-list :checkbox:checked' ).length > 0 ) {
			if ( confirm( rtmedia_selected_media_delete_confirmation ) ) {
				jQuery( this ).closest( 'form' ).attr( 'action', '../../../' + rtmedia_media_slug + '/delete' ).submit();
			}
		} else {
			rtmedia_gallery_action_alert_message( rtmedia_no_media_selected, 'warning' );
		}
	} );

	jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-move-selected', function( e ) {
		if ( jQuery( '.rtmedia-list :checkbox:checked' ).length > 0 ) {
			if ( confirm( rtmedia_selected_media_move_confirmation ) ) {
				jQuery( this ).closest( 'form' ).attr( 'action', '' ).submit();
			}
		} else {
			rtmedia_gallery_action_alert_message( rtmedia_no_media_selected, 'warning' );
		}

	} );

	jQuery( '#buddypress' ).on( 'change', '.rtm-activity-privacy-opt', function() {

		var activity_id = jQuery( this ).attr( 'id' );
		activity_id = activity_id.split( '-' );
		activity_id = activity_id[ activity_id.length - 1 ];

		var that = this;

		data = {
			activity_id: activity_id,
			privacy: jQuery( this ).val(),
			nonce: jQuery( '#rtmedia_activity_privacy_nonce' ).val(),
			action: 'rtm_change_activity_privacy'
		};

		jQuery.post( ajaxurl, data, function( res ) {
			var message = '';
			var css_class = '';
			if ( res == 'true' ) {
				message = rtmedia_main_js_strings.privacy_update_success;
				css_class = 'success';
			} else {
				message = rtmedia_main_js_strings.privacy_update_error;
				css_class = 'fail';
			}

			jQuery( that ).after( '<p class="rtm-ac-privacy-updated ' + css_class + '">' + message + '</p>' );
			setTimeout( function() {
				jQuery( that ).siblings( '.rtm-ac-privacy-updated' ).remove();
			}, 2000 );
		} );
	} );

	jQuery( '.media_search_input' ).on( 'keyup', function() {
		rtm_search_media_text_validation();
	} );

	function rtmedia_media_view_counts() {
		//Var view_count_action = jQuery('#rtmedia-media-view-form').attr("action");
		if ( jQuery( '#rtmedia-media-view-form' ).length > 0 ) {
			var url = jQuery( '#rtmedia-media-view-form' ).attr( 'action' );
			jQuery.post( url, { }, function( data ) {

			} );
		}
	}

	rtmedia_media_view_counts();
	rtMediaHook.register( 'rtmedia_js_popup_after_content_added',
		function() {
				rtmedia_media_view_counts();
				rtmedia_init_media_deleting();
				mfp = jQuery.magnificPopup.instance;

				if ( jQuery( mfp.items ).size() > 1 && comment_media == false ) {
					rtmedia_init_popup_navigation();
				}else{
					rtmedia_disable_popup_navigation_all();
				}

				rtmedia_disable_popup_navigation_comment_focus();

				rtmedia_disable_popup_navigation_comment_media_focus();

				var height = $( window ).height();
				jQuery( '.rtm-lightbox-container .mejs-video' ).css( { 'height': height * 0.8, 'over-flow': 'hidden' } );
				jQuery( '.mfp-content .rtmedia-media' ).css( { 'max-height': height * 0.87, 'over-flow': 'hidden' } );
				//Mejs-video
				//init the options dropdown menu
  				rtmedia_init_action_dropdown( '.rtm-lightbox-container .rtmedia-actions' );
				//Get focus on comment textarea when comment-link is clicked
				jQuery( '.rtmedia-comment-link' ).on( 'click', function( e ) {
					e.preventDefault();
					jQuery( '#comment_content' ).focus();
				} );

				jQuery( '.rtm-more' ).shorten( { // Shorten the media description to 100 characters
					'showChars': 130
				} );

				//Show gallery title in lightbox at bottom
				var gal_title = $( '.rtm-gallery-title' ), title = '';
				if ( ! $.isEmptyObject( gal_title ) ) {
					title = gal_title.html();
				} else {
					title = $( '#subnav.item-list-tabs li.selected ' ).html();
				}
				if ( title != '' ) {
					$( '.rtm-ltb-gallery-title .ltb-title' ).html( title );
				}

				//Show image counts
				var counts = $( '#subnav.item-list-tabs li.selected span' ).html();
				$( 'li.total' ).html( counts );

				return true;
		}
	);

	function rtmedia_init_popup_navigation() {
		var rtm_mfp = jQuery.magnificPopup.instance;
		jQuery( '.mfp-arrow-right' ).on( 'click', function( e ) {
			rtm_mfp.next();
		} );
		jQuery( '.mfp-arrow-left' ).on( 'click', function( e ) {
			rtm_mfp.prev();
		} );

		jQuery( '.mfp-content .rtmedia-media' ).swipe( {
			//Generic swipe handler for all directions
			swipeLeft: function( event, direction, distance, duration, fingerCount ) 	// Bind leftswipe
			{
				rtm_mfp.next();
			},
			swipeRight: function( event, direction, distance, duration, fingerCount ) 	// Bind rightswipe
			{
				rtm_mfp.prev();
			},
			threshold: 0
		} );
	}


	function rtmedia_disable_popup_navigation_all(){
		// hide the left and right key
		jQuery( '.mfp-arrow-right' ).hide();
		jQuery( '.mfp-arrow-left' ).hide();

		// disable the left and right keyboard button
		jQuery( document ).unbind( 'keydown' );
	}

	function rtmedia_disable_popup_navigation_comment_focus() {
		rtmedia_disable_popup_navigation( '#comment_content' );
	}

	var dragArea = jQuery( '#drag-drop-area' );
	var activityArea = jQuery( '#whats-new' );
	var content = dragArea.html();
	jQuery( '#rtmedia-upload-container' ).after( '<div id=\'rtm-drop-files-title\'>' + rtmedia_drop_media_msg + '</div>' );
	if ( typeof rtmedia_bp_enable_activity != 'undefined' && rtmedia_bp_enable_activity == '1' ) {
		jQuery( '#whats-new-textarea' ).append( '<div id=\'rtm-drop-files-title\'>' + rtmedia_drop_media_msg + '</div>' );
	}
	jQuery( document )
			.on( 'dragover', function( e ) {
				e.preventDefault();
				/* check if media is dragging on same page */
				if ( e.target == this ) {
					return;
				}
				jQuery( '#rtm-media-gallery-uploader' ).show();
				if ( typeof rtmedia_bp_enable_activity != 'undefined' && rtmedia_bp_enable_activity == '1' ) {
					activityArea.addClass( 'rtm-drag-drop-active' );
				}

				dragArea.addClass( 'rtm-drag-drop-active' );
				jQuery( '#rtm-drop-files-title' ).show();
			} )
			.on( 'dragleave', function( e ) {
				e.preventDefault();
				if ( e.originalEvent.pageX != 0 && e.originalEvent.pageY != 0 ) {
					return false;
				}
				if ( typeof rtmedia_bp_enable_activity != 'undefined' && rtmedia_bp_enable_activity == '1' ) {
					activityArea.removeClass( 'rtm-drag-drop-active' );
					activityArea.removeAttr( 'style' );
				}
				dragArea.removeClass( 'rtm-drag-drop-active' );
				jQuery( '#rtm-drop-files-title' ).hide();

			} )
			.on( 'drop', function( e ) {
				e.preventDefault();
				/* Put cursor into activity box after dropping any media */
				jQuery( '.bp-suggestions' ).focus();
				if ( typeof rtmedia_bp_enable_activity != 'undefined' && rtmedia_bp_enable_activity == '1' ) {
					activityArea.removeClass( 'rtm-drag-drop-active' );
					activityArea.removeAttr( 'style' );
				}
				dragArea.removeClass( 'rtm-drag-drop-active' );
				jQuery( '#rtm-drop-files-title' ).hide();
			} );

	function rtmedia_init_media_deleting() {
		jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-delete-media', function( e ) {
			e.preventDefault();
			if ( confirm( rtmedia_media_delete_confirmation ) ) {
				jQuery( this ).closest( 'form' ).submit();
			}
		} );
	}

	jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-delete-album', function( e ) {
		e.preventDefault();
		if ( confirm( rtmedia_album_delete_confirmation ) ) {
			jQuery( this ).closest( 'form' ).submit();
		}
	} );

	jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-delete-media', function( e ) {
		e.preventDefault();
		if ( confirm( rtmedia_media_delete_confirmation ) ) {
			jQuery( this ).closest( 'form' ).submit();
		}
	} );

	rtmedia_init_action_dropdown( '' );

	$( document ).click( function() {
		if ( $( '.click-nav ul' ).is( ':visible' ) ) {
			$( '.click-nav ul', this ).hide();
		}
	} );

	//Get focus on comment textarea when comment-link is clicked
	jQuery( '.rtmedia-comment-link' ).on( 'click', function( e ) {
		e.preventDefault();
		jQuery( '#comment_content' ).focus();
	} );

	if ( jQuery( '.rtm-more' ).length > 0 ) {
		$( '.rtm-more' ).shorten( { // Shorten the media description to 100 characters
			'showChars': 200
		} );
	}

	// Masonry code for activity
	if ( typeof rtmedia_masonry_layout != 'undefined' && rtmedia_masonry_layout == 'true' && typeof rtmedia_masonry_layout_activity != 'undefined' && rtmedia_masonry_layout_activity == 'true' ) {
		// Arrange media into masonry view
		rtmedia_activity_masonry();
	}

	// Arrange media into masonry view right after upload or clicking on readmore link to activity without page load.
	jQuery( document ).ajaxComplete( function( event, xhr, settings ) {
		var get_action = get_parameter( 'action', settings.data );

		if ( ( 'post_update' === get_action || 'get_single_activity_content' === get_action || 'activity_get_older_updates' === get_action ) && typeof rtmedia_masonry_layout != 'undefined' && rtmedia_masonry_layout == 'true' && typeof rtmedia_masonry_layout_activity != 'undefined' && rtmedia_masonry_layout_activity == 'true' ) {
			rtmedia_activity_masonry();
		}
	} );

	// Masonry code
	if ( typeof rtmedia_masonry_layout != 'undefined' && rtmedia_masonry_layout == 'true' && jQuery( '.rtmedia-container .rtmedia-list.rtm-no-masonry' ).length == 0 ) {
		rtm_masonry_container = jQuery( '.rtmedia-container .rtmedia-list' );
		rtm_masonry_container.masonry( {
			itemSelector: '.rtmedia-list-item'
		} );
		setInterval( function() {
			jQuery.each( jQuery( '.rtmedia-list.masonry .rtmedia-item-title' ), function( i, item ) {
				jQuery( item ).width( jQuery( item ).siblings( '.rtmedia-item-thumbnail' ).children( 'img' ).width() );
			} );
			rtm_masonry_reload( rtm_masonry_container );
		}, 1000 );
		jQuery.each( jQuery( '.rtmedia-list.masonry .rtmedia-item-title' ), function( i, item ) {
			jQuery( item ).width( jQuery( item ).siblings( '.rtmedia-item-thumbnail' ).children( 'img' ).width() );
		} );
	}

	if ( jQuery( '.rtm-uploader-tabs' ).length > 0 ) {
		jQuery( '.rtm-uploader-tabs li' ).click( function( e ) {
			if ( ! jQuery( this ).hasClass( 'active' ) ) {
				jQuery( this ).siblings().removeClass( 'active' );
				jQuery( this ).parents( '.rtm-uploader-tabs' ).siblings().hide();
				class_name = jQuery( this ).attr( 'class' );
	            jQuery( this ).parents( '.rtm-uploader-tabs' ).siblings( '[data-id="' + class_name + '"]' ).show();
				jQuery( this ).addClass( 'active' );

				if ( class_name != 'rtm-upload-tab' ) {
					jQuery( 'div.moxie-shim' ).hide();
				} else {
					jQuery( 'div.moxie-shim' ).show();
				}
			}
		});
	}

	// Delete media from gallery page under the user's profile when user clicks the delete button on the gallery item.
	jQuery( '.rtmedia-container' ).on( 'click', '.rtm-delete-media', function( e ) {
		e.preventDefault();
		var confirmation = 'Are you sure you want to delete this media?';

		if ( typeof rtmedia_media_delete_confirmation != 'undefined' ) {
			confirmation = rtmedia_media_delete_confirmation;
		}

		if ( confirm( confirmation ) ) { // If user confirms, send ajax request to delete the selected media
			var curr_li = jQuery( this ).closest( 'li' );
			var nonce = jQuery( '#rtmedia_media_delete_nonce' ).val();

			var data = {
				action: 'delete_uploaded_media',
				nonce: nonce,
				media_id: curr_li.attr( 'id' )
			};

			jQuery.ajax( {
				url: ajaxurl,
				type: 'post',
				data: data,
				success: function( data ) {

					if ( data == '1' ) {
						//Media delete
						rtmedia_gallery_action_alert_message( rtmedia_main_js_strings.file_delete_success, 'success' );
						curr_li.remove();
						if ( typeof rtmedia_masonry_layout != 'undefined' && rtmedia_masonry_layout == 'true' && jQuery( '.rtmedia-container .rtmedia-list.rtm-no-masonry' ).length == 0 ) {
							rtm_masonry_reload( rtm_masonry_container );
						}
					} else { // Show alert message
						rtmedia_gallery_action_alert_message( rtmedia_file_not_deleted, 'warning' );
					}
				}
			} );
		}
	} );
} );

//Legacy media element for old activities
function bp_media_create_element( id ) {
	return false;
}

function rtmedia_version_compare( left, right ) {
	if ( typeof left + typeof right != 'stringstring' ) {
		return false;
	}

	var a = left.split( '.' ),
		b = right.split( '.' ),
		i = 0,
		len = Math.max( a.length, b.length );

	for ( ; i < len; i++ ) {
		if ( ( a[i] && ! b[i] && parseInt( a[i] ) > 0 ) || ( parseInt( a[i] ) > parseInt( b[i] ) ) ) {
			return true;
		} else if ( ( b[i] && ! a[i] && parseInt( b[i] ) > 0 ) || ( parseInt( a[i] ) < parseInt( b[i] ) ) ) {
			return false;
		}
	}

	return true;
}

function rtm_is_element_exist( el ) {
	if ( jQuery( el ).length > 0 ) {
		return true;
	} else {
		return false;
	}
}

function rtm_masonry_reload( el ) {
	setTimeout( function() {
		// We make masonry recalculate the element based on their current state.
		el.masonry( 'reload' );
	}, 250 );
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function( $ ) {
 $.fn.shorten = function( settings ) {
     'use strict';

     var config = {
         showChars: 100,
         minHideChars: 10,
         ellipsesText: '...',
         moreText: rtmedia_read_more,
         lessText: rtmedia__show_less,
         onLess: function() {},
         onMore: function() {},
         errMsg: null,
         force: false
     };

     if ( settings ) {
         $.extend( config, settings );
     }

     if ( $( this ).data( 'jquery.shorten' ) && ! config.force ) {
         return false;
     }
     $( this ).data( 'jquery.shorten', true );

     $( document ).off( 'click', '.morelink' );

	 $( document ).on({
		 click: function() {

			 var $this = $( this );
			 if ( $this.hasClass( 'less' ) ) {
				 $this.removeClass( 'less' );
				 $this.html( config.moreText );
				 $this.parent().prev().hide( 0, function() {
					 $this.parent().prev().prev().show();
				 })
				 .hide( 0, function() {
					 config.onLess();
				 });
			 } else {
				 $this.addClass( 'less' );
				 $this.html( config.lessText );
				 $this.parent().prev().show( 0, function() {
					 $this.parent().prev().prev().hide();
				 })
				 .show( 0, function() {
					 config.onMore();
				 });
			 }
			 return false;
		 }
		}, '.morelink' );

     return this.each(function() {
         var $this = $( this );

         var content = $this.html();
         var contentlen = $this.text().length;
         if ( contentlen > config.showChars + config.minHideChars ) {
             var c = content.substr( 0, config.showChars );
             if ( c.indexOf( '<' ) >= 0 ) // If there's HTML don't want to cut it
             {
                 var inTag = false; // I'm in a tag?
                 var bag = ''; // Put the characters to be shown here
                 var countChars = 0; // Current bag size
                 var openTags = []; // Stack for opened tags, so I can close them later
                 var tagName = null;

                 for ( var i = 0, r = 0; r <= config.showChars; i++ ) {
                     if ( content[i] == '<' && ! inTag ) {
                         inTag = true;

                         // This could be "tag" or "/tag"
                         tagName = content.substring( i + 1, content.indexOf( '>', i ) );

                         // If its a closing tag
                         if ( tagName[0] == '/' ) {

                             if ( tagName != '/' + openTags[0] ) {
                                 config.errMsg = 'ERROR en HTML: the top of the stack should be the tag that closes';
                             } else {
                                 openTags.shift(); // Pops the last tag from the open tag stack (the tag is closed in the retult HTML!)
                             }

                         } else {
                             // There are some nasty tags that don't have a close tag like <br/>
                             if ( tagName.toLowerCase() != 'br' ) {
                                 openTags.unshift( tagName ); // Add to start the name of the tag that opens
                             }
                         }
                     }
                     if ( inTag && content[i] == '>' ) {
                         inTag = false;
                     }

                     if ( inTag ) {
 bag += content.charAt( i );
 } // Add tag name chars to the result
                     else {
                         r++;
                         if ( countChars <= config.showChars ) {
                             bag += content.charAt( i ); // Fix to ie 7 not allowing you to reference string characters using the []
                             countChars++;
                         } else // Now I have the characters needed
                         {
                             if ( openTags.length > 0 ) // I have unclosed tags
                             {
                                 //Console.log('They were open tags');
                                 //console.log(openTags);
                                 for ( j = 0; j < openTags.length; j++ ) {
                                     //Console.log('Cierro tag ' + openTags[j]);
                                     bag += '</' + openTags[j] + '>'; // Close all tags that were opened

                                     // You could shift the tag from the stack to check if you end with an empty stack, that means you have closed all open tags
                                 }
                                 break;
                             }
                         }
                     }
                 }
                 c = $( '<div/>' ).html( bag + '<span class="ellip">' + config.ellipsesText + '</span>' ).html();
             }else {
                 c += config.ellipsesText;
             }

             var html = '<div class="shortcontent">' + c +
                 '</div><div class="allcontent">' + content +
                 '</div><span><a href="javascript://nop/" class="morelink">' + config.moreText + '</a></span>';

             $this.html( html );
             $this.find( '.allcontent' ).hide(); // Hide all text
             $( '.shortcontent p:last', $this ).css( 'margin-bottom', 0 ); //Remove bottom margin on last paragraph as it's likely shortened
         }
     });

 };

})( jQuery );

window.onload = function() {
	if ( 'undefined' != typeof rtmedia_masonry_layout && 'true' == rtmedia_masonry_layout && 0 == jQuery( '.rtmedia-container .rtmedia-list.rtm-no-masonry' ).length ) {
		rtm_masonry_reload( rtm_masonry_container );
	}

	rtm_search_media_text_validation();

	if ( check_condition( 'search' ) ) {
		jQuery( '#media_search_remove' ).show();
	}

};

/**
 * Update style as per search testbox value
 * issue: https://github.com/rtMediaWP/rtMedia/issues/834
 */
function rtm_search_media_text_validation() {
	if ( '' === jQuery( '#media_search_input' ).val() ) {
		jQuery( '#media_search' ).css( 'cursor', 'not-allowed');
	} else {
		jQuery( '#media_search' ).css( 'cursor', 'pointer');
	}
}

// Get query string parameters from url
function rtmediaGetParameterByName( name ) {
	name = name.replace( /[\[]/, '\\\[' ).replace( /[\]]/, '\\\]' );
	var regex = new RegExp( '[\\?&]' + name + '=([^&#]*)' ),
			results = regex.exec( location.search );
	return results == null ? '' : decodeURIComponent( results[1].replace( /\+/g, ' ' ) );
}

function rtmedia_single_media_alert_message( msg, action ) {
	var action_class = 'rtmedia-success';

	if ( 'warning' == action ) {
		action_class = 'rtmedia-warning';
	}

	jQuery( '.rtmedia-single-media .rtmedia-media' ).css( 'opacity', '0.2' );
	jQuery( '.rtmedia-single-media .rtmedia-media' ).after( '<div class=\'rtmedia-message-container\'><span class=\'' + action_class + '\'>' + msg + ' </span></div>' );

	setTimeout( function() {
		jQuery( '.rtmedia-single-media .rtmedia-media' ).css( 'opacity', '1' );
		jQuery( '.rtmedia-message-container' ).remove();
	}, 3000 );

	jQuery( '.rtmedia-message-container' ).click( function() {
		jQuery( '.rtmedia-single-media .rtmedia-media' ).css( 'opacity', '1' );
		jQuery( '.rtmedia-message-container' ).remove();
	} );
}

function rtmedia_gallery_action_alert_message( msg, action ) {
	var action_class = 'rtmedia-success';

	if ( 'warning' == action ) {
		action_class = 'rtmedia-warning';
	}
	var container = '<div class="rtmedia-gallery-alert-container"> </div>';
	jQuery( 'body' ).append( container );
	jQuery( '.rtmedia-gallery-alert-container' ).append( '<div class=\'rtmedia-gallery-message-box\'><span class=\'' + action_class + '\'>' + msg + ' </span></div>' );

	setTimeout( function() {
		jQuery( '.rtmedia-gallery-alert-container' ).remove();
	}, 3000 );

	jQuery( '.rtmedia-gallery-message-box' ).click( function() {
		jQuery( '.rtmedia-gallery-alert-container' ).remove();
	} );
}

// Set masonry view for activity
function rtmedia_activity_masonry() {
	jQuery('#activity-stream .rtmedia-activity-container .rtmedia-list').masonry({
		itemSelector: '.rtmedia-list-item',
		gutter: 7,
	});
	var timesRun = 0;
	var interval = setInterval( function() {
		timesRun += 1;
		// Run this for 5 times only.
		if(timesRun === 5){
			clearInterval(interval);
		}
		jQuery.each( jQuery( '.rtmedia-activity-container .rtmedia-list.masonry .rtmedia-item-title' ), function( i, item ) {
			jQuery( item ).width( jQuery( item ).siblings( '.rtmedia-item-thumbnail' ).children( 'img' ).width() );
		} );
		// Reload masonry view.
		rtm_masonry_reload( jQuery('#activity-stream .rtmedia-activity-container .rtmedia-list') );
	}, 1000 );
}

/**
 * Get specific parameter value from Query string.
 * @param string parameter Parameter of query string.
 * @param object data Set of data.
 * @return bool
 */
function get_parameter( parameter, data ) {

	if ( ! parameter ) {
		return false;
	}

	if ( ! data ) {
		data = window.location.href;
	}

	var parameter = parameter.replace( /[\[]/, "\\\[" ).replace( /[\]]/, "\\\]" );
	var expr      = parameter + "=([^&#]*)";
	var regex     = new RegExp( expr );
	var results   = regex.exec( data );

	if ( null !== results ) {
		return results[1];
	} else {
		return false;
	}
}
