var rtMediaAdmin = new Object();

rtMediaAdmin.templates = {
	rtm_image : wp.template( 'rtm-image' ),
	rtm_msg_div : wp.template( 'rtm-msg-div' ),
	rtm_album_favourites_importer : wp.template( 'rtm-album-favourites-importer' ),
	rtm_map_mapping_failure : wp.template( 'rtm-map-mapping-failure' ),
	rtm_p_tag : wp.template( 'rtm-p-tag' ),
	rtm_theme_overlay : wp.template( 'rtm-theme-overlay' )
};

jQuery( document ).ready( function ( $ ) {

	var rtm_licence = $( '#rtm-licenses' );
	if ( rtm_licence.length > 0 ) {
		rtm_licence.find( '.license-inner:first input:first' ).focus();
	}

	var support_form_loader_div = document.createElement('div');
	support_form_loader_div.className = 'support_form_loader';

	// Hide settings saved message
	if ( $( '.rtm-save-settings-msg' ).length > 0 ) {
		setTimeout( function () {
			$( '.rtm-save-settings-msg' ).remove();
		}, 10000 );
	}

	/* Linkback */
	jQuery( '#spread-the-word' ).on( 'click', '#bp-media-add-linkback', function () {
		var data = {
			action: 'rtmedia_linkback',
			linkback: jQuery( '#bp-media-add-linkback:checked' ).length
		};
		jQuery.post( rtmedia_admin_ajax, data, function ( response ) {
		} );
	} );

	/* Select Request */
	jQuery( '#bp-media-settings-boxes' ).on( 'change', '#select-request', function () {
		if ( jQuery( this ).val() ) {
			jQuery( '#bp_media_settings_form .bp-media-metabox-holder' ).html();

			//'<div class="support_form_loader"></div>'

			jQuery( '#bp_media_settings_form .bp-media-metabox-holder' ).html( support_form_loader_div );
			var data = {
				action: 'rtmedia_select_request',
				form: jQuery( this ).val()
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post( ajaxurl, data, function ( response ) {
				jQuery( '#bp_media_settings_form .bp-media-metabox-holder' ).html();
				jQuery( '#bp_media_settings_form .bp-media-metabox-holder' ).html( response ).fadeIn( 'slow' );
			} );
		}
	} );

	/* Cancel Request */
	jQuery( '#bp-media-settings-boxes' ).on( 'click', '#cancel-request', function () {
		if ( jQuery( this ).val() ) {
			jQuery( '#bp_media_settings_form .bp-media-metabox-holder' ).html();

			// '<div class="support_form_loader"></div>'

			jQuery( '#bp_media_settings_form .bp-media-metabox-holder' ).html( support_form_loader_div );
			var data = {
				action: 'rtmedia_cancel_request'
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post( ajaxurl, data, function ( response ) {
				jQuery( '#bp_media_settings_form .bp-media-metabox-holder' ).html();
				jQuery( '#bp_media_settings_form .bp-media-metabox-holder' ).html( response ).fadeIn( 'slow' );
			} );
		}
	} );

	/* Submit Request */

	jQuery( '#bp-media-settings-boxes' ).on( 'submit', '#bp_media_settings_form, .rtmedia-settings-submit', function ( e ) {
		var return_code = true;
		var reg = new RegExp( '^[0-9]+$' );

		jQuery( "input[name*='defaultSizes']" ).each( function ( el ) {
			if ( !reg.test( jQuery( this ).val() ) ) {
				alert( "Invalid value for " + jQuery( this ).attr( 'name' ).replace( 'rtmedia-options[defaultSizes_', '' ).replace( ']', '' ).replace( /_/g, ' ' ).replace( /(\b)([a-zA-Z] )/g, function ( firstLetter ) {
					return firstLetter.toUpperCase();
				} ) );
				return_code = false;
				return false;
			}
		} );

		var general_videothumb = jQuery( 'input[name^="rtmedia-options[general_videothumbs]"]' );
		if ( return_code && general_videothumb.length > 0 && typeof general_videothumb != "undefined" ) {
			var error_msg = "";
			var general_videothumb_val = 0;
			if ( general_videothumb.val() <= 0 ) {
				error_msg += rtmedia_admin_strings.video_thumbnail_error;
				general_videothumb_val = 2;
			} else if ( !reg.test( general_videothumb.val() ) ) {
				error_msg += rtmedia_admin_strings.video_thumbnail_invalid_value + ' ' + Math.round( general_videothumb.val() ) + ".";
				general_videothumb_val = Math.round( general_videothumb.val() );
			}
			if ( error_msg != "" ) {
				alert( error_msg );
				general_videothumb.val( general_videothumb_val );
				return_code = false;
				return false;
			}
		}

		var general_jpeg_image_quality = jQuery( 'input[name^="rtmedia-options[general_jpeg_image_quality]"]' );
		if ( return_code && general_jpeg_image_quality.length > 0 && typeof general_jpeg_image_quality != "undefined" ) {
			var error_msg = "";
			var general_jpeg_image_quality_val = 0;
			if ( general_jpeg_image_quality.val() <= 0 ) {
				error_msg += rtmedia_admin_strings.jpeg_quality_negative_error;
				general_jpeg_image_quality_val = 90;
			} else if ( general_jpeg_image_quality.val() > 100 ) {
				error_msg += rtmedia_admin_strings.jpeg_quality_percentage_error;
				general_jpeg_image_quality_val = 100;
			} else if ( !reg.test( general_jpeg_image_quality.val() ) ) {
				error_msg += rtmedia_admin_strings.jpeg_quality_invalid_value + ' ' + Math.round( general_jpeg_image_quality.val() ) + ".";
				general_jpeg_image_quality_val = Math.round( general_jpeg_image_quality.val() );
			}
			if ( error_msg != "" ) {
				alert( error_msg );
				general_jpeg_image_quality.val( general_jpeg_image_quality_val );
				return_code = false;
				return false;
			}
		}

		var general_perPageMedia = jQuery( 'input[name^="rtmedia-options[general_perPageMedia]"]' );
		if ( return_code && general_perPageMedia.length > 0 && typeof general_perPageMedia != "undefined" ) {
			var error_msg = "";
			var general_perPageMedia_val = 0;
			if ( general_perPageMedia.val() < 1 ) {
				error_msg += rtmedia_admin_strings.per_page_media_negative_value;
				general_perPageMedia_val = 10;
			} else if ( jQuery.isNumeric( general_perPageMedia.val() ) && ( Math.floor( general_perPageMedia.val() ) != general_perPageMedia.val() ) ) {
				error_msg += rtmedia_admin_strings.per_page_media_positive_error + " " + Math.round( general_perPageMedia.val() ) + ".";
				general_perPageMedia_val = Math.round( general_perPageMedia.val() );
			}
			if ( error_msg != "" ) {
				alert( error_msg );
				general_perPageMedia.val( general_perPageMedia_val );
				return_code = false;
				return false;
			}
		}

		if ( !return_code ) {
			e.preventDefault();
		}
	} );

	jQuery( document ).on( 'click', "#bpm-services .encoding-try-now,#rtm-services .encoding-try-now", function ( e ) {
		e.preventDefault();
		if ( confirm( rtmedia_admin_strings.are_you_sure ) ) {
			var data = {
				src   : rtmedia_admin_url + "images/wpspin_light.gif"
			};

			jQuery( this ).after( rtMediaAdmin.templates.rtm_image( data ) );

			var data = {
				action: 'rtmedia_free_encoding_subscribe'
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.getJSON( ajaxurl, data, function ( response ) {
				if ( response.error === undefined && response.apikey ) {
					var tempUrl = window.location.href;
					var hash = window.location.hash;
					tempUrl = tempUrl.replace( hash, '' );
					document.location.href = tempUrl + '&apikey=' + response.apikey + hash;
				} else {
					jQuery( '.encoding-try-now' ).next().remove();
					jQuery( '#settings-error-encoding-error' ).remove();

					var data = {
						id : 'settings-error-encoding-error',
						msg : response.error,
						class : 'error'
					};

					jQuery( '#bp-media-settings-boxes' ).before( rtMediaAdmin.templates.rtm_msg_div( data ) );
				}
			} );
		}
	} );

	jQuery( document ).on( 'click', '#api-key-submit', function ( e ) {
		e.preventDefault();

		if ( jQuery( this ).next( 'img' ).length == 0 ) {
			var data = {
				src   : rtmedia_admin_url + "images/wpspin_light.gif"
			};

			jQuery( this ).after( rtMediaAdmin.templates.rtm_image( data ) );
		}

		var data = {
			action: 'rtmedia_enter_api_key',
			apikey: jQuery( '#new-api-key' ).val()
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.getJSON( ajaxurl, data, function ( response ) {
			if ( response.error === undefined && response.apikey ) {
				var tempUrl = window.location.href;
				var hash = window.location.hash;
				tempUrl = tempUrl.replace( hash, '' );

				if ( tempUrl.toString().indexOf( '&apikey=' + response.apikey ) == -1 ) {
					tempUrl += '&apikey=' + response.apikey;
				}
				if ( tempUrl.toString().indexOf( '&update=true' ) == -1 ) {
					tempUrl += '&update=true';
				}

				document.location.href = tempUrl + hash;
			} else {
				jQuery( '#settings-error-api-key-error' ).remove();

				var data = {
					id : 'settings-error-api-key-error',
					msg : response.error,
					class : 'error'
				};

				jQuery( 'h2:first' ).after( rtMediaAdmin.templates.rtm_msg_div( data ) );
			}

			jQuery( '#api-key-submit' ).next( 'img' ).remove();
		} );
	} );

	jQuery( document ).on( 'click', '#disable-encoding', function ( e ) {
		e.preventDefault();
		if ( confirm( rtmedia_admin_strings.disable_encoding ) ) {
			var data = {
				src   : rtmedia_admin_url + "images/wpspin_light.gif"
			};

			jQuery( this ).after( rtMediaAdmin.templates.rtm_image( data ) );

			var data = {
				action: 'rtmedia_disable_encoding'
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post( ajaxurl, data, function ( response ) {
				if ( response ) {
					jQuery( '.settings-error-encoding-disabled' ).remove();

					if ( jQuery( '#settings-encoding-successfully-updated' ).length > 0 ) {
						jQuery( '#settings-encoding-successfully-updated p' ).html( response );
					} else {
						var data = {
							id : 'settings-encoding-successfully-updated',
							msg : response,
							class : 'updated'
						};

						jQuery( 'h2:first' ).after( rtMediaAdmin.templates.rtm_msg_div( data ) );
					}

					jQuery( '#rtmedia-encoding-usage' ).hide();
					jQuery( '#disable-encoding' ).next( 'img' ).remove();
					jQuery( '#disable-encoding' ).hide();
					jQuery( '#enable-encoding' ).show();
				} else {
					jQuery( '#settings-error-encoding-disabled' ).remove();

					var data = {
						id : 'settings-error-encoding-disabled',
						msg : rtmedia_admin_strings.something_went_wrong,
						class : 'error'
					};

					jQuery( 'h2:first' ).after( rtMediaAdmin.templates.rtm_msg_div( data ) );
				}
			} );
		}
	} );

	jQuery( document ).on( 'click', '#enable-encoding', function ( e ) {
		e.preventDefault();
		if ( confirm( rtmedia_admin_strings.enable_encoding ) ) {
			var data = {
				src   : rtmedia_admin_url + "images/wpspin_light.gif"
			};

			jQuery( this ).after( rtMediaAdmin.templates.rtm_image( data ) );

			var data = {
				action: 'rtmedia_enable_encoding'
			};

			jQuery.post( ajaxurl, data, function ( response ) {
				if ( response ) {
					jQuery( '.settings-error-encoding-enabled' ).remove();

					if ( jQuery( '#settings-encoding-successfully-updated' ).length > 0 ) {
						jQuery( '#settings-encoding-successfully-updated p' ).html( response );
					} else {
						var data = {
							id : 'settings-encoding-successfully-updated',
							msg : response,
							class : 'updated'
						};

						jQuery( 'h2:first' ).after( rtMediaAdmin.templates.rtm_msg_div( data ) );
					}

					jQuery( '#enable-encoding' ).next( 'img' ).remove();
					jQuery( '#enable-encoding' ).hide();
					jQuery( '#disable-encoding' ).show();
				} else {
					jQuery( '#settings-error-encoding-disabled' ).remove();

					var data = {
						id : 'settings-error-encoding-enabled',
						msg : rtmedia_admin_strings.something_went_wrong,
						class : 'error'
					};

					jQuery( 'h2:first' ).after( rtMediaAdmin.templates.rtm_msg_div( data ) );
				}
			} );
		}
	} );

	jQuery( '.bp-media-encoding-table' ).on( 'click', '.bpm-unsubscribe', function ( e ) {
		e.preventDefault();

		jQuery( "#bpm-unsubscribe-dialog" ).dialog( {
			dialogClass: "wp-dialog",
			modal: true,
			buttons: {
				Unsubscribe: function () {
					jQuery( this ).dialog( "close" );

					var data = {
						src   : rtmedia_admin_url + "images/wpspin_light.gif"
					};

					jQuery( '.bpm-unsubscribe' ).after( rtMediaAdmin.templates.rtm_image( data ) );

					var data = {
						action: 'rtmedia_unsubscribe_encoding_service',
						note: jQuery( '#bpm-unsubscribe-note' ).val(),
						plan: jQuery( '.bpm-unsubscribe' ).attr( 'data-plan' ),
						price: jQuery( '.bpm-unsubscribe' ).attr( 'data-price' )
					};

					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					jQuery.getJSON( ajaxurl, data, function ( response ) {
						if ( response.error === undefined && response.updated ) {
							jQuery( '.bpm-unsubscribe' ).next().remove();
							jQuery( '.bpm-unsubscribe' ).after( response.form );
							jQuery( '.bpm-unsubscribe' ).remove();
							jQuery( '#settings-unsubscribed-successfully' ).remove();
							jQuery( '#settings-unsubscribe-error' ).remove();

							var data = {
								id : 'settings-unsubscribed-successfully',
								msg : response.updated,
								class : 'updated'
							};

							jQuery( 'h2:first' ).after( rtMediaAdmin.templates.rtm_msg_div( data ) );
							window.location.hash = '#settings-unsubscribed-successfully';
						} else {
							jQuery( '.bpm-unsubscribe' ).next().remove();
							jQuery( '#settings-unsubscribed-successfully' ).remove();
							jQuery( '#settings-unsubscribe-error' ).remove();

							var data = {
								id : 'settings-unsubscribe-error',
								msg : response.error,
								class : 'error'
							};

							jQuery( 'h2:first' ).after( rtMediaAdmin.templates.rtm_msg_div( data ) );
							window.location.hash = '#settings-unsubscribe-error';
						}
					} );
				}
			}
		} );
	} );

	function fireRequest( data ) {
		return jQuery.post( ajaxurl, data, function ( response ) {
			if ( response != 0 ) {
				var redirect = false;
				var progw = Math.ceil( ( ( ( parseInt( response ) * 20 ) + parseInt( data.values[ 'finished' ] ) ) / parseInt( data.values[ 'total' ] ) ) * 100 );
				if ( progw > 100 ) {
					progw = 100;
					redirect = true
				}
				jQuery( '#rtprogressbar>div' ).css( 'width', progw + '%' );
				finished = jQuery( '#rtprivacyinstaller span.finished' ).html();
				jQuery( '#rtprivacyinstaller span.finished' ).html( parseInt( finished ) + data.count );
				if ( redirect ) {
					jQuery.post( ajaxurl, {
						action: 'rtmedia_privacy_redirect'
					}, function ( response ) {
						window.location = settings_url;
					} );
				}
			} else {
				var map_data = {
					msg : "Row " + response + " failed."
				};

				jQuery( '#map_progress_msgs' ).html( rtMediaAdmin.templates.rtm_map_mapping_failure( map_data ) );
			}
		} );
	}

	jQuery( '#bpmedia-bpalbumimporter' ).on( 'change', '#bp-album-import-accept', function () {
		jQuery( '.bp-album-import-accept' ).toggleClass( 'i-accept' );
		jQuery( '.bp-album-importer-wizard' ).slideToggle();
	} );

	jQuery( '#rtprivacyinstall' ).click( function ( e ) {
		e.preventDefault();
		$progress_parent = jQuery( '#rtprivacyinstaller' );
		$progress_parent.find( '.rtprivacytype' ).each( function () {
			$type = jQuery( this ).attr( 'id' );
			if ( $type == 'total' ) {
				$values = [];
				jQuery( this ).find( 'input' ).each( function () {

					$values [ jQuery( this ).attr( 'name' ) ] = [ jQuery( this ).val() ];

				} );
				$data = {};
				for ( var i = 1; i <= $values[ 'steps' ][ 0 ]; i++ ) {
					$count = 20;
					if ( i == $values[ 'steps' ][ 0 ] ) {
						$count = parseInt( $values[ 'laststep' ][ 0 ] );
						if ( $count == 0 ) {
							$count = 20
						}
						;
					}
					newvals = {
						'page': i,
						'action': 'rtmedia_privacy_install',
						'count': $count,
						'values': $values
					}
					$data[ i ] = newvals;
				}
				var $startingpoint = jQuery.Deferred();
				$startingpoint.resolve();
				jQuery.each( $data, function ( i, v ) {
					$startingpoint = $startingpoint.pipe( function () {
						return fireRequest( v );
					} );
				} );
			}
		} );
	} );

	function fireimportRequest( data ) {
		return jQuery.getJSON( ajaxurl, data, function ( response ) {
			favorites = false;
			if ( response ) {
				var redirect = false;
				var media_progw = Math.ceil( ( ( ( parseInt( response.page ) * 5 ) + parseInt( data.values[ 'finished' ] ) ) / parseInt( data.values[ 'total' ] ) ) * 100 );
				comments_total = jQuery( '#bpmedia-bpalbumimporter .bp-album-comments span.total' ).html();
				users_total = jQuery( '#bpmedia-bpalbumimporter .bp-album-users span.total' ).html();
				media_total = jQuery( '#bpmedia-bpalbumimporter .bp-album-media span.total' ).html();
				comments_finished = jQuery( '#bpmedia-bpalbumimporter .bp-album-comments span.finished' ).html();
				users_finished = jQuery( '#bpmedia-bpalbumimporter .bp-album-users span.finished' ).html();
				var comments_progw = Math.ceil( ( ( ( parseInt( response.comments ) ) + parseInt( comments_finished ) ) / parseInt( comments_total ) ) * 100 );
				var users_progw = Math.ceil( ( parseInt( response.users ) / parseInt( users_total ) ) * 100 );
				if ( media_progw > 100 || media_progw == 100 ) {
					media_progw = 100;
					favorites = true
				}
				;
				jQuery( '.bp-album-media #rtprogressbar>div' ).css( 'width', media_progw + '%' );
				jQuery( '.bp-album-comments #rtprogressbar>div' ).css( 'width', comments_progw + '%' );
				jQuery( '.bp-album-users #rtprogressbar>div' ).css( 'width', users_progw + '%' );
				media_finished = jQuery( '#bpmedia-bpalbumimporter .bp-album-media span.finished' ).html();
				if ( parseInt( media_finished ) < parseInt( media_total ) )
					jQuery( '#bpmedia-bpalbumimporter .bp-album-media span.finished' ).html( parseInt( media_finished ) + data.count );
				jQuery( '#bpmedia-bpalbumimporter .bp-album-comments span.finished' ).html( parseInt( response.comments ) + parseInt( comments_finished ) );
				jQuery( '#bpmedia-bpalbumimporter .bp-album-users span.finished' ).html( parseInt( response.users ) );
				if ( favorites ) {
					favorite_data = {
						'action': 'rtmedia_rt_album_import_favorites',
						rtm_wpnonce: jQuery('#bpaimporter_wpnonce').val()
					}
					jQuery.post( ajaxurl, favorite_data, function ( response ) {
						if (response.hasOwnProperty(favorites) && (response.favorites !== 0 || response.favorites !== '0')) {
							if ( !jQuery( '.bp-album-favorites' ).length ) {
								var data = {
									users : response.users
								}

								jQuery( '.bp-album-comments' ).after( rtMediaAdmin.templates.rtm_album_favourites_importer( data ) );
							}

							$favorites = {};
							if ( response.offset != 0 || response.offset != '0' )
								start = response.offset * 1 + 1;
							else
								start = 1
							for ( var i = start; i <= response.users; i++ ) {
								$count = 1;
								if ( i == response.users ) {
									$count = parseInt( response.users % $count );
									if ( $count == 0 ) {
										$count = 1;
									}
								}

								newvals = {
									'action': 'rtmedia_rt_album_import_step_favorites',
									'offset': ( i - 1 ) * 1,
									'redirect': i == response.users,
									'rtm_wpnonce': jQuery('#bpaimporter_wpnonce').val()
								}
								$favorites[ i ] = newvals;
							}
							var $startingpoint = jQuery.Deferred();
							$startingpoint.resolve();
							jQuery.each( $favorites, function ( i, v ) {
								$startingpoint = $startingpoint.pipe( function () {
									return fireimportfavoriteRequest( v );
								} );
							} );
						} else {
							window.setTimeout( reload_url, 2000 );
						}
					}, 'json' );
				}
			} else {
				if (data.hasOwnProperty(page)) {
					var map_data = {
						msg : "Row " + response.page + " failed."
					};

					jQuery('#map_progress_msgs').html( rtMediaAdmin.templates.rtm_map_mapping_failure( map_data ) );
				} else {
					var map_data = {
						msg : rtmedia_admin_strings.request_failed
					};

					jQuery('#map_progress_msgs').html( rtMediaAdmin.templates.rtm_map_mapping_failure( map_data ) );
				}
			}
		} );
	}

	function fireimportfavoriteRequest( data ) {
		return jQuery.post( ajaxurl, data, function ( response ) {
			redirect = false;
			favorites_total = jQuery( '#bpmedia-bpalbumimporter .bp-album-favorites span.total' ).html();
			favorites_finished = jQuery( '#bpmedia-bpalbumimporter .bp-album-favorites span.finished' ).html();
			jQuery( '#bpmedia-bpalbumimporter .bp-album-favorites span.finished' ).html( parseInt( favorites_finished ) + 1 );
			var favorites_progw = Math.ceil( ( parseInt( favorites_finished + 1 ) / parseInt( favorites_total ) ) * 100 );
			if ( favorites_progw > 100 || favorites_progw == 100 ) {
				favorites_progw = 100;
				redirect = true;
			}
			jQuery( '.bp-album-favorites #rtprogressbar>div' ).css( 'width', favorites_progw + '%' );
			if ( redirect ) {
				window.setTimeout( reload_url, 2000 );
			}
		} );
	}

	function reload_url() {
		window.location = document.URL;
	}

	jQuery( '#bpmedia-bpalbumimport-cleanup' ).click( function ( e ) {
		e.preventDefault();
		jQuery.post( ajaxurl, {
			action: 'rtmedia_rt_album_cleanup',
			rtm_wpnonce: jQuery('#bpaimporter_wpnonce').val()
		}, function ( response ) {
			window.location = settings_rt_album_import_url;
		} );

	} );

	jQuery( '#bpmedia-bpalbumimporter' ).on( 'click', '#bpmedia-bpalbumimport', function ( e ) {
		e.preventDefault();
		if ( !jQuery( '#bp-album-import-accept' ).prop( 'checked' ) ) {
			jQuery( 'html, body' ).animate( {
				scrollTop: jQuery( '#bp-album-import-accept' ).offset().top
			}, 500 );
			var $el = jQuery( '.bp-album-import-accept' ),
				x = 500,
				originalColor = '#FFEBE8',
				i = 3; //counter

			( function loop() { //recurisve IIFE
				$el.css( "background-color", "#EE0000" );
				setTimeout( function () {
					$el.css( "background-color", originalColor );
					if ( --i )
						setTimeout( loop, x ); //restart loop
				}, x );
			}() );
			return;
		} else {
			jQuery( this ).prop( 'disabled', true );
		}
		wp_admin_url = ajaxurl.replace( 'admin-ajax.php', '' );

		if ( !jQuery( '.bpm-ajax-loader' ).length ) {
			var data = {
				src   : rtmedia_admin_url + "images/wpspin_light.gif",
				class : 'bpm-ajax-loader',
				norefresh : rtmedia_admin_strings.no_refresh
			};

			jQuery( this ).after( rtMediaAdmin.templates.rtm_image( data ) );
		}

		$progress_parent = jQuery( '#bpmedia-bpalbumimport' );
		$values = [];
		jQuery( this ).parent().find( 'input' ).each( function () {
			$values [ jQuery( this ).attr( 'name' ) ] = [ jQuery( this ).val() ];

		} );

		if ( $values[ 'steps' ][ 0 ] == 0 )
			$values[ 'steps' ][ 0 ] = 1;

		$data = {};
		for ( var i = 1; i <= $values[ 'steps' ][ 0 ]; i++ ) {
			$count = 5;
			if ( i == $values[ 'steps' ][ 0 ] ) {
				$count = parseInt( $values[ 'laststep' ][ 0 ] );
				if ( $count == 0 ) {
					$count = 5
				}
			}
			newvals = {
				'page': i,
				'action': 'rtmedia_rt_album_import',
				'count': $count,
				'values': $values,
				rtm_wpnonce: jQuery('#bpaimporter_wpnonce').val()
			}
			$data[ i ] = newvals;
		}
		var $startingpoint = jQuery.Deferred();
		$startingpoint.resolve();
		jQuery.each( $data, function ( i, v ) {
			$startingpoint = $startingpoint.pipe( function () {
				return fireimportRequest( v );
			} );
		} );
	} );

	jQuery( '#bp-media-settings-boxes' ).on( 'click', '.interested', function () {
		jQuery( '.interested-container' ).removeClass( 'hidden' );
		jQuery( '.choice-free' ).attr( 'required', 'required' );
	} );
	jQuery( '#bp-media-settings-boxes' ).on( 'click', '.not-interested', function () {
		jQuery( '.interested-container' ).addClass( 'hidden' );
		jQuery( '.choice-free' ).removeAttr( 'required' );
	} );

	jQuery( '#video-transcoding-main-container' ).on( 'click', '.video-transcoding-survey', function ( e ) {
		e.preventDefault();
		var data = {
			action: 'rtmedia_convert_videos_form',
			email: jQuery( '.email' ).val(),
			url: jQuery( '.url' ).val(),
			choice: jQuery( 'input[name="choice"]:checked' ).val(),
			interested: jQuery( 'input[name="interested"]:checked' ).val()
		}
		jQuery.post( ajaxurl, data, function ( response ) {
			var p_data = {
				msg :response,
				strong : 'yes'
			};

			jQuery( '#video-transcoding-main-container' ).html( rtMediaAdmin.templates.rtm_p_tag( p_data ) );
		} );
		return false;
	} );

	jQuery( '#bpmedia-bpalbumimporter' ).on( 'click', '.deactivate-bp-album', function ( e ) {
		e.preventDefault();
		$bpalbum = jQuery( this );
		var data = {
			action: 'rtmedia_rt_album_deactivate',
			rtm_wpnonce: jQuery('#bpaimporter_wpnonce').val()
		}
		jQuery.get( ajaxurl, data, function ( response ) {
			if ( response ) {
				location.reload();
			} else {
				var p_data = {
					msg : rtmedia_admin_strings.something_went_wrong
				};

				$bpalbum.parent().after( rtMediaAdmin.templates.rtm_p_tag( p_data ) );
			}
		} );
	} );

	jQuery( '.updated' ).on( 'click', '.bpm-hide-encoding-notice', function () {
		var data = {
			src   : rtmedia_admin_url + "images/wpspin_light.gif"
		};

		jQuery( this ).after( rtMediaAdmin.templates.rtm_image( data ) );

		var data = {
			action: 'rtmedia_hide_encoding_notice'
		}
		jQuery.post( ajaxurl, data, function ( response ) {
			if ( response ) {
				jQuery( '.bpm-hide-encoding-notice' ).closest( '.updated' ).remove();
			}
		} );
	} );

	if ( jQuery( '#rtmedia-bp-enable-activity' ).is( ":checked" ) ) {
		jQuery( ".rtmedia-bp-activity-setting" ).prop( "readonly", false );
	} else {
		jQuery( ".rtmedia-bp-activity-setting" ).prop( "readonly", true );
	}

	jQuery( '#rtmedia-bp-enable-activity' ).on( "click", function ( e ) {
		if ( jQuery( this ).is( ":checked" ) ) {
			jQuery( ".rtmedia-bp-activity-setting" ).prop( "readonly", false );
		} else {
			jQuery( ".rtmedia-bp-activity-setting" ).prop( "readonly", true );
		}
	} );

	var onData = '';
	var offData = '';
	if ( rtmedia_on_label !== undefined )
		onData = 'data-on-label="' + rtmedia_on_label + '"';
	if ( rtmedia_off_label !== undefined )
		offData = 'data-off-label="' + rtmedia_off_label + '"';

	var files;
	/* upload file immediately after selecting it */
	jQuery( 'input[type=file]' ).on( 'change', rtmedia_prepare_upload );

	function rtmedia_prepare_upload( event ) {
		files = event.target.files;
		rtmedia_upload_files( event );
	}

	jQuery( '#rtmedia-submit-request' ).click( function () {
		var flag = true;
		var name = jQuery( '#name' ).val();
		var email = jQuery( '#email' ).val();
		var website = jQuery( '#website' ).val();
		var subject = jQuery( '#subject' ).val();
		var details = jQuery( '#details' ).val();
		var request_type = jQuery( 'input[name="request_type"]' ).val();
		var request_id = jQuery( 'input[name="request_id"]' ).val();
		var server_address = jQuery( 'input[name="server_address"]' ).val();
		var ip_address = jQuery( 'input[name="ip_address"]' ).val();
		var server_type = jQuery( 'input[name="server_type"]' ).val();
		var user_agent = jQuery( 'input[name="user_agent"]' ).val();
		var debuglog_temp_path = jQuery( 'input[name="debuglog_temp_path"]' ).val();
		var form_data = {
			name: name,
			email: email,
			website: website,
			subject: subject,
			details: details,
			request_id: request_id,
			request_type: 'premium_support',
			server_address: server_address,
			ip_address: ip_address,
			server_type: server_type,
			user_agent: user_agent,
			debuglog_temp_path: debuglog_temp_path
		};
		if ( request_type == "bug_report" ) {
			var wp_admin_username = jQuery( '#wp_admin_username' ).val();
			if ( wp_admin_username == "" ) {
				alert( rtmedia_admin_support_strings.wp_admin_username_error );
				return false;
			}
			var wp_admin_pwd = jQuery( '#wp_admin_pwd' ).val();
			if ( wp_admin_pwd == "" ) {
				alert( rtmedia_admin_support_strings.wp_admin_pwd_error );
				return false;
			}
			var ssh_ftp_host = jQuery( '#ssh_ftp_host' ).val();
			if ( ssh_ftp_host == "" ) {
				alert( rtmedia_admin_support_strings.ssh_ftp_host_error );
				return false;
			}
			var ssh_ftp_username = jQuery( '#ssh_ftp_username' ).val();
			if ( ssh_ftp_username == "" ) {
				alert( rtmedia_admin_support_strings.ssh_ftp_username_error );
				return false;
			}
			var ssh_ftp_pwd = jQuery( '#ssh_ftp_pwd' ).val();
			if ( ssh_ftp_pwd == "" ) {
				alert( rtmedia_admin_support_strings.ssh_ftp_pwd_error );
				return false;
			}
			form_data = {
				name: name,
				email: email,
				website: website,
				subject: subject,
				details: details,
				request_id: request_id,
				request_type: 'premium_support',
				server_address: server_address,
				ip_address: ip_address,
				server_type: server_type,
				user_agent: user_agent,
				wp_admin_username: wp_admin_username,
				wp_admin_pwd: wp_admin_pwd,
				ssh_ftp_host: ssh_ftp_host,
				ssh_ftp_username: ssh_ftp_username,
				ssh_ftp_pwd: ssh_ftp_pwd
			};
		}
		for ( formdata in form_data ) {
			if ( form_data[ formdata ] == "" && formdata != 'debuglog_temp_path' ) {
				alert( "Please enter " + formdata.replace( "_", " " ) + " field." );
				return false;
			} else if ( form_data[ formdata ] == "" && formdata == 'debuglog_temp_path' ) {
				alert( "Please upload attachment." );
				return false;
			}
		}
		data = {
			action: "rtmedia_submit_request",
			form_data: form_data,
			support_wpnonce: jQuery('#support_wpnonce').val()
		};
		jQuery.post( ajaxurl, data, function ( data ) {
			data = data.trim();
			if ( data == "false" ) {
				alert( rtmedia_admin_support_strings.all_fields_error );
				return false;
			}
			$( '#rtmedia_service_contact_container' ).empty();
			$( '#rtmedia_service_contact_container' ).append( data );
		} );
		return false;
	} );

	/* Upload file to temporary folder  */
	function rtmedia_upload_files( event ) {
		event.stopPropagation(); // Stop stuff happening
		event.preventDefault(); // Totally stop stuff happening

		/* Create a formdata object and add the files */
		var data = new FormData();
		/**
		 * Append extra field defining the uploaded file must be settings json file
		 */
		if ( undefined !== event && undefined !== event.target && undefined !== event.target.name && 'rtFileInput' === event.target.name ) {
			data.append( 'import_export_control', event.target.name );
		}
		jQuery.each( files, function( key, value ) {
			data.append( key, value );
		});

		// Append nonce.
		var rtmedia_admin_upload_nonce = jQuery( '#rtmedia_admin_upload_nonce' ).val();
		if ( 'undefined' !== typeof rtmedia_admin_upload_nonce && '' !== rtmedia_admin_upload_nonce ) {
			data.append( 'rtmedia_admin_upload_nonce', rtmedia_admin_upload_nonce );
		}

		// Add upload action.
		data.append( 'action', 'rtmedia_admin_upload' );

		jQuery.ajax({
			url: rtmedia_admin_ajax,
			type: 'POST',
			data: data,
			cache: false,
			processData: false,
			contentType: false,
			success: function( data ) {
				if ( data.hasOwnProperty('rtm_response') && data.hasOwnProperty('rtm_response_msg') ) {
					jQuery('#rtm-setting-msg').remove();
					var setting_message = jQuery( '<div/>', {
						'id'    : 'rtm-setting-msg',
						'class' : 'rtm-fly-warning',
					});

					if( 'success' === data.rtm_response ) {
						setting_message.addClass( 'rtm-success rtm-save-settings-msg' );
						setting_message.text( data.rtm_response_msg );
						jQuery('.rtm-button-container.top').append( setting_message );
						location.reload();
					} else if ( 'error' === data.rtm_response ) {
						setting_message.addClass( 'rtm-warning' );
						setting_message.text( data.rtm_response_msg );
						jQuery('.rtm-button-container.top').append( setting_message );
						setting_message.delay( 3000 ).fadeOut( 100 );
					}
				}

				if( typeof data.error === 'undefined' ) {
					if ( data.exceed_size_msg ) {
						jQuery( '#debuglog' ).val( '' );
						alert( data.exceed_size_msg );
						return false;
					}
					/* if file uploaded successfully, then set that path into a hidden field. */
					jQuery('#debuglog_temp_path').val( data.debug_attachmanet );
				} else {
					jQuery( '#debuglog' ).val( '' );
					/* Show error in alert box. */
					alert( 'ERRORS: ' + data.error );
				}
			}
		});
	}

	jQuery( '#cancel-request' ).click( function () {
		return false;
	} );

	if ( jQuery( '.rtm_enable_masonry_view input[type=checkbox]' ).is( ":checked" ) ) {
		jQuery( '.rtm_enable_masonry_view' ).parents( '.metabox-holder' ).find( '.rtmedia-info' ).show();
	} else {
		jQuery( '.rtm_enable_masonry_view' ).parents( '.metabox-holder' ).find( '.rtmedia-info' ).hide();
	}
	jQuery( '.rtm_enable_masonry_view input[type=checkbox]' ).on( "click", function ( e ) {
		if ( jQuery( this ).is( ":checked" ) ) {
			jQuery( '.rtm_enable_masonry_view' ).parents( '.metabox-holder' ).find( '.rtmedia-info' ).show();
		} else {
			jQuery( '.rtm_enable_masonry_view' ).parents( '.metabox-holder' ).find( '.rtmedia-info' ).hide();
		}
	} );
	jQuery( "#rtm-masonry-change-thumbnail-info" ).click( function ( e ) {
		jQuery( "html, body" ).animate( { scrollTop: 0 }, '500', 'swing' );
	} );

	jQuery( '#rtm-export-button' ).click( function () {
		data = {
			action: "rtmedia_export_settings",
		};
		jQuery.post( ajaxurl, data, function ( data ) {
			var dataStr            = "data:text/json;charset=utf-8," + encodeURIComponent( JSON.stringify( data ) );
			var downloadAnchorNode = document.createElement( 'a' );
			downloadAnchorNode.setAttribute( 'href', dataStr );
			downloadAnchorNode.setAttribute( 'download', 'rtm-settings.json' );
			jQuery( 'body' ).append( downloadAnchorNode );
			downloadAnchorNode.click();
			downloadAnchorNode.remove();
		} );
	} );

	jQuery( '#rtm-export-data-button' ).click( function(){
		window.location.href = '/wp-admin/tools.php?page=export_personal_data';
	} );

	jQuery( '#rtm-erase-data-button' ).click(function () {
		window.location.href = '/wp-admin/tools.php?page=remove_personal_data';
	});

} );

function rtmedia_addon_do_not_show() {
	var data = {
		action: 'rtmedia_addon_popup_not_show_again'
	};
	jQuery.post( rtmedia_admin_ajax, data, function ( response ) {
		jQuery( '#TB_window' ).remove();
		jQuery( '#TB_overlay' ).remove();
	} );
}

jQuery( window ).load( function () {
	jQuery( '.rtmedia-addon-thickbox' ).trigger( 'click' );
} );
