/**
 * Responsive Table JS
 */
jQuery( document ).ready( function( $ ) {

	var rtm_warning = document.createElement( 'div' );
	rtm_warning.setAttribute( 'class', 'rtm-warning rtm-fly-warning hide' );

	// Tabs
	$( '.rtm-tabs' ).rtTab();

	// Show notice on change option settings
	$( 'input[name^="rtmedia-options"]' ).on( 'change', function () {
		$( '.rtm-save-settings-msg' ).remove();

		if ( 0 === $( '.rtm-fly-warning' ).length ) {
			rtm_warning.innerText = rtmedia_admin_strings.settings_changed;

			$( '.rtm-button-container.top' ).prepend( rtm_warning );
			$( '.rtm-fly-warning' ).slideDown();
		}
	} );

	// This is for chrome border issue
	$( '.rtm-img-size-setting .form-table tr:nth-child(7) td:last-child' ).attr( 'colspan', '3' );

	$( '.rtm-field-wrap .switch input[type=checkbox]' ).each( function() {
		var self = $( this );

		if ( ! self.parents( 'table' ).attr( 'data-depends' ) ) {
			if ( self.is( ':checked' ) ) {
				self.parents( 'table' ).next( '.rtm-notice' ).slideDown();

				self.parents( 'table' ).siblings( 'table' ).each( function() {
					if ( $( this ).attr( 'data-depends' ) ) {
						$( this ).slideDown();
					}
				} );
			} else {
				self.parents( 'table' ).next( '.rtm-notice' ).slideUp();

				self.parents( 'table' ).siblings( 'table' ).each( function() {
					if ( $( this ).attr( 'data-depends' ) ) {
						$( this ).slideUp();
					}
				} );
			}
		}

		if ( self.parents( 'tr' ).next( 'tr' ).attr( 'data-depends' ) ) {
			if ( self.is( ':checked' ) ) {
				self.parents( 'tr' ).next( 'tr' ).slideDown();
			} else {
				self.parents( 'tr' ).next( 'tr' ).slideUp();
			}
		}
	} );

	$( '.rtm-field-wrap .switch input[type=checkbox]' ).on( 'change', function() {
		var self = $( this );

		if ( ! self.parents( 'table' ).attr( 'data-depends' ) ) {
			self.parents( 'table' ).next( '.rtm-notice' ).slideToggle();

			self.parents( 'table' ).siblings( 'table' ).each( function() {
				if ( $( this ).attr( 'data-depends' ) ) {
					$( this ).slideToggle();
				}
			} );
		}

		if ( self.parents( 'tr' ).next( 'tr' ).attr( 'data-depends' ) ) {
			self.parents( 'tr' ).next( 'tr' ).slideToggle();
		}
	} );

	// Theme section lightbox like WordPress
	// May be not like Backbone, But I will surely update this code. ;)
	var ListView = Backbone.View.extend( {
		el: $( '.bp-media-admin' ), // attaches `this.el` to an existing element.
		events: {
			'click .rtm-theme': 'render',
			'click .rtm-close': 'close',
			'click .rtm-previous': 'previousTheme',
			'click .rtm-next': 'nextTheme',
			'keyup': 'keyEvent'
		},
		initialize: function() {
			_.bindAll( this, 'render', 'close', 'nextTheme', 'previousTheme', 'keyEvent' ); // fixes loss of context for 'this' within methods

			this.keyEvent();
		},
		render: function( event ) {
			$( '.rtm-theme' ).removeClass( 'rtm-modal-open' );

			var themeContent = $( event.currentTarget ).addClass( 'rtm-modal-open' ).find( '.rtm-theme-content' ).html();

			if ( $( '.rtm-theme-overlay' )[0] ) {
				$( '.rtm-theme-overlay' ).show();
				$( this.el ).find( '.rtm-theme-content-wrap' ).empty().append( themeContent );
			} else {
				var data = {
					themeContent: themeContent
				};

				$( this.el ).append( rtMediaAdmin.templates.rtm_theme_overlay( data ) );
			}

			if ( $( event.currentTarget ).is( ':first-child' ) ) {
				$( '.rtm-previous' ).addClass( 'disabled' );
			} else if ( $( event.currentTarget ).is( ':last-child' ) ) {
				$( '.rtm-next' ).addClass( 'disabled' );
			} else {
				$( '.rtm-next, .rtm-previous' ).removeClass( 'disabled' );
			}
		},
		close: function() {
			$( '.rtm-theme' ).removeClass( 'rtm-modal-open' );
			$( '.rtm-theme-overlay' ).hide();
			$( '.rtm-next, .rtm-previous' ).removeClass( 'disabled' );
		},
		nextTheme: function( event ) {
			$( '.rtm-next, .rtm-previous' ).removeClass( 'disabled' );

			if ( $( '.rtm-theme:last-child' ).hasClass( 'rtm-modal-open' ) ) {
				$( event.currentTarget ).addClass( 'disabled' );
			}

			$( '.rtm-modal-open' ).next().trigger( 'click' );

			return false;
		},
		previousTheme: function( event ) {
			$( '.rtm-next, .rtm-previous' ).removeClass( 'disabled' );

			if ( $( '.rtm-theme:first-child' ).hasClass( 'rtm-modal-open' ) ) {
				$( event.currentTarget ).addClass( 'disabled' );
			}

			$( '.rtm-modal-open' ).prev().trigger( 'click' );

			return false;
		},
		keyEvent: function() {
			// Bind keyboard events.
			$( 'body' ).on( 'keyup', function( event ) {
				// The right arrow key, next theme
				if ( 39 === event.keyCode ) {
					$( '.rtm-next, .rtm-previous' ).removeClass( 'disabled' );

					if ( $( '.rtm-theme:last-child' ).hasClass( 'rtm-modal-open' ) ) {
						$( event.currentTarget ).addClass( 'disabled' );
					}

					$( '.rtm-modal-open' ).next().trigger( 'click' );

					return false;
				}

				// The left arrow key, previous theme
				if ( 37 === event.keyCode ) {
					$( '.rtm-next, .rtm-previous' ).removeClass( 'disabled' );

					if ( $( '.rtm-theme:first-child' ).hasClass( 'rtm-modal-open' ) ) {
						$( event.currentTarget ).addClass( 'disabled' );
					}

					$( '.rtm-modal-open' ).prev().trigger( 'click' );

					return false;
				}

				// The escape key closes the preview
				if ( 27 === event.keyCode ) {
					$( '.rtm-close' ).trigger( 'click' );
				}
			} );
		}
	} );

	var listView = new ListView();

	/* Prevent license key validation by Enter Key as it deactivates the first plugin's license. */
	jQuery( '#rtm-licenses .regular-text' ).each( function() {
		jQuery( this ).keypress(function (event) {
			var keycode = (event.keyCode ? event.keyCode : event.which);
			/* check if key pressed is "Enter key" or not */
			if(keycode == '13'){
				return false;
			}
		} );
	} );

	/* Check if @import is inserted into css or not */
	jQuery( '#bp_media_settings_form' ).on( 'submit', function( event ) {
		jQuery( '#rtcss-notice' ).remove();
		// css input in textarea
		var css = jQuery( '#rtmedia-custom-css' ).val();
		// check if @import is used in css
		var matches = css.match(/@import\s*(url)?\s*\(?([^;]+?)\)?;/);
		if ( matches != null ) {
			var removable_line = matches[0];

			// if @import found in the css, then show error message
			if ( removable_line != null ) {
				jQuery( '#rtmedia-custom-css' ).after( '<div id="rtcss-notice" class="error"><p>' + rtmedia_admin_strings.wrong_css_input + '</p></div>' );
				return false;
			}
		}
	} );

} );
