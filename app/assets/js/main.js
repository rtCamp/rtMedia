/*! 
 * rtMedia JavaScript Library 
 * @package rtMedia 
 *//*! Magnific Popup - v1.0.0 - 2015-01-03
 * http://dimsemenov.com/plugins/magnific-popup/
 * Copyright (c) 2015 Dmitry Semenov; */
;
( function ( factory ) {
	if ( typeof define === 'function' && define.amd ) {
		// AMD. Register as an anonymous module.
		define( [ 'jquery' ], factory );
	} else if ( typeof exports === 'object' ) {
		// Node/CommonJS
		factory( require( 'jquery' ) );
	} else {
		// Browser globals
		factory( window.jQuery || window.Zepto );
	}
}( function ( $ ) {

	/*>>core*/
	/**
	 *
	 * Magnific Popup Core JS file
	 *
	 */


	/**
	 * Private static constants
	 */
	var CLOSE_EVENT = 'Close',
			BEFORE_CLOSE_EVENT = 'BeforeClose',
			AFTER_CLOSE_EVENT = 'AfterClose',
			BEFORE_APPEND_EVENT = 'BeforeAppend',
			MARKUP_PARSE_EVENT = 'MarkupParse',
			OPEN_EVENT = 'Open',
			CHANGE_EVENT = 'Change',
			NS = 'mfp',
			EVENT_NS = '.' + NS,
			READY_CLASS = 'mfp-ready',
			REMOVING_CLASS = 'mfp-removing',
			PREVENT_CLOSE_CLASS = 'mfp-prevent-close';


	/**
	 * Private vars
	 */
	/*jshint -W079 */
	var mfp, // As we have only one instance of MagnificPopup object, we define it locally to not to use 'this'
			MagnificPopup = function () {
			},
			_isJQ = ! ! ( window.jQuery ),
			_prevStatus,
			_window = $( window ),
			_document,
			_prevContentType,
			_wrapClasses,
			_currPopupType;


	/**
	 * Private functions
	 */
	var _mfpOn = function ( name, f ) {
		mfp.ev.on( NS + name + EVENT_NS, f );
	},
			_getEl = function ( className, appendTo, html, raw ) {
				var el = document.createElement( 'div' );
				el.className = 'mfp-' + className;
				if ( html ) {
					el.innerHTML = html;
				}
				if ( ! raw ) {
					el = $( el );
					if ( appendTo ) {
						el.appendTo( appendTo );
					}
				} else if ( appendTo ) {
					appendTo.appendChild( el );
				}
				return el;
			},
			_mfpTrigger = function ( e, data ) {
				mfp.ev.triggerHandler( NS + e, data );

				if ( mfp.st.callbacks ) {
					// converts "mfpEventName" to "eventName" callback and triggers it if it's present
					e = e.charAt( 0 ).toLowerCase() + e.slice( 1 );
					if ( mfp.st.callbacks[e] ) {
						mfp.st.callbacks[e].apply( mfp, $.isArray( data ) ? data : [ data ] );
					}
				}
			},
			_getCloseBtn = function ( type ) {
				if ( type !== _currPopupType || ! mfp.currTemplate.closeBtn ) {
					mfp.currTemplate.closeBtn = $( mfp.st.closeMarkup.replace( '%title%', mfp.st.tClose ) );
					_currPopupType = type;
				}
				return mfp.currTemplate.closeBtn;
			},
			// Initialize Magnific Popup only when called at least once
			_checkInstance = function () {
				if ( ! $.magnificPopup.instance ) {
					/*jshint -W020 */
					mfp = new MagnificPopup();
					mfp.init();
					$.magnificPopup.instance = mfp;
				}
			},
			// CSS transition detection, http://stackoverflow.com/questions/7264899/detect-css-transitions-using-javascript-and-without-modernizr
			supportsTransitions = function () {
				var s = document.createElement( 'p' ).style, // 's' for style. better to create an element if body yet to exist
						v = [ 'ms', 'O', 'Moz', 'Webkit' ]; // 'v' for vendor

				if ( s['transition'] !== undefined ) {
					return true;
				}

				while ( v.length ) {
					if ( v.pop() + 'Transition' in s ) {
						return true;
					}
				}

				return false;
			};



	/**
	 * Public functions
	 */
	MagnificPopup.prototype = {
		constructor: MagnificPopup,
		/**
		 * Initializes Magnific Popup plugin.
		 * This function is triggered only once when $.fn.magnificPopup or $.magnificPopup is executed
		 */
		init: function () {
			var appVersion = navigator.appVersion;
			mfp.isIE7 = appVersion.indexOf( "MSIE 7." ) !== - 1;
			mfp.isIE8 = appVersion.indexOf( "MSIE 8." ) !== - 1;
			mfp.isLowIE = mfp.isIE7 || mfp.isIE8;
			mfp.isAndroid = ( /android/gi ).test( appVersion );
			mfp.isIOS = ( /iphone|ipad|ipod/gi ).test( appVersion );
			mfp.supportsTransition = supportsTransitions();

			// We disable fixed positioned lightbox on devices that don't handle it nicely.
			// If you know a better way of detecting this - let me know.
			mfp.probablyMobile = ( mfp.isAndroid || mfp.isIOS || /(Opera Mini)|Kindle|webOS|BlackBerry|(Opera Mobi)|(Windows Phone)|IEMobile/i.test( navigator.userAgent ) );
			_document = $( document );

			mfp.popupsCache = { };
		},
		/**
		 * Opens popup
		 * @param  data [description]
		 */
		open: function ( data ) {

			var i;

			if ( data.isObj === false ) {
				// convert jQuery collection to array to avoid conflicts later
				mfp.items = data.items.toArray();

				mfp.index = 0;
				var items = data.items,
						item;
				for ( i = 0; i < items.length; i ++ ) {
					item = items[i];
					if ( item.parsed ) {
						item = item.el[0];
					}
					if ( item === data.el[0] ) {
						mfp.index = i;
						break;
					}
				}
			} else {
				mfp.items = $.isArray( data.items ) ? data.items : [ data.items ];
				mfp.index = data.index || 0;
			}

			// if popup is already opened - we just update the content
			if ( mfp.isOpen ) {
				mfp.updateItemHTML();
				return;
			}

			mfp.types = [ ];
			_wrapClasses = '';
			if ( data.mainEl && data.mainEl.length ) {
				mfp.ev = data.mainEl.eq( 0 );
			} else {
				mfp.ev = _document;
			}

			if ( data.key ) {
				if ( ! mfp.popupsCache[data.key] ) {
					mfp.popupsCache[data.key] = { };
				}
				mfp.currTemplate = mfp.popupsCache[data.key];
			} else {
				mfp.currTemplate = { };
			}



			mfp.st = $.extend( true, { }, $.magnificPopup.defaults, data );
			mfp.fixedContentPos = mfp.st.fixedContentPos === 'auto' ? ! mfp.probablyMobile : mfp.st.fixedContentPos;

			if ( mfp.st.modal ) {
				mfp.st.closeOnContentClick = false;
				mfp.st.closeOnBgClick = false;
				mfp.st.showCloseBtn = false;
				mfp.st.enableEscapeKey = false;
			}


			// Building markup
			// main containers are created only once
			if ( ! mfp.bgOverlay ) {

				// Dark overlay
				mfp.bgOverlay = _getEl( 'bg' ).on( 'click' + EVENT_NS, function () {
					mfp.close();
				} );

				mfp.wrap = _getEl( 'wrap' ).attr( 'tabindex', - 1 ).on( 'click' + EVENT_NS, function ( e ) {
					if ( mfp._checkIfClose( e.target ) ) {
						mfp.close();
					}
				} );

				mfp.container = _getEl( 'container', mfp.wrap );
			}

			mfp.contentContainer = _getEl( 'content' );
			if ( mfp.st.preloader ) {
				mfp.preloader = _getEl( 'preloader', mfp.container, mfp.st.tLoading );
			}


			// Initializing modules
			var modules = $.magnificPopup.modules;
			for ( i = 0; i < modules.length; i ++ ) {
				var n = modules[i];
				n = n.charAt( 0 ).toUpperCase() + n.slice( 1 );
				mfp['init' + n].call( mfp );
			}
			_mfpTrigger( 'BeforeOpen' );


			if ( mfp.st.showCloseBtn ) {
				// Close button
				if ( ! mfp.st.closeBtnInside ) {
					mfp.wrap.append( _getCloseBtn() );
				} else {
					_mfpOn( MARKUP_PARSE_EVENT, function ( e, template, values, item ) {
						values.close_replaceWith = _getCloseBtn( item.type );
					} );
					_wrapClasses += ' mfp-close-btn-in';
				}
			}

			if ( mfp.st.alignTop ) {
				_wrapClasses += ' mfp-align-top';
			}



			if ( mfp.fixedContentPos ) {
				mfp.wrap.css( {
					overflow: mfp.st.overflowY,
					overflowX: 'hidden',
					overflowY: mfp.st.overflowY
				} );
			} else {
				mfp.wrap.css( {
					top: _window.scrollTop(),
					position: 'absolute'
				} );
			}
			if ( mfp.st.fixedBgPos === false || ( mfp.st.fixedBgPos === 'auto' && ! mfp.fixedContentPos ) ) {
				mfp.bgOverlay.css( {
					height: _document.height(),
					position: 'absolute'
				} );
			}



			if ( mfp.st.enableEscapeKey ) {
				// Close on ESC key
				_document.on( 'keyup' + EVENT_NS, function ( e ) {
					if ( e.keyCode === 27 ) {
						mfp.close();
					}
				} );
			}

			_window.on( 'resize' + EVENT_NS, function () {
				mfp.updateSize();
			} );


			if ( ! mfp.st.closeOnContentClick ) {
				_wrapClasses += ' mfp-auto-cursor';
			}

			if ( _wrapClasses )
				mfp.wrap.addClass( _wrapClasses );


			// this triggers recalculation of layout, so we get it once to not to trigger twice
			var windowHeight = mfp.wH = _window.height();


			var windowStyles = { };

			if ( mfp.fixedContentPos ) {
				if ( mfp._hasScrollBar( windowHeight ) ) {
					var s = mfp._getScrollbarSize();
					if ( s ) {
						windowStyles.marginRight = s;
					}
				}
			}

			if ( mfp.fixedContentPos ) {
				if ( ! mfp.isIE7 ) {
					windowStyles.overflow = 'hidden';
				} else {
					// ie7 double-scroll bug
					$( 'body, html' ).css( 'overflow', 'hidden' );
				}
			}



			var classesToadd = mfp.st.mainClass;
			if ( mfp.isIE7 ) {
				classesToadd += ' mfp-ie7';
			}
			if ( classesToadd ) {
				mfp._addClassToMFP( classesToadd );
			}

			// add content
			mfp.updateItemHTML();

			_mfpTrigger( 'BuildControls' );

			// remove scrollbar, add margin e.t.c
			$( 'html' ).css( windowStyles );

			// add everything to DOM
			mfp.bgOverlay.add( mfp.wrap ).prependTo( mfp.st.prependTo || $( document.body ) );

			// Save last focused element
			mfp._lastFocusedEl = document.activeElement;

			// Wait for next cycle to allow CSS transition
			setTimeout( function () {

				if ( mfp.content ) {
					mfp._addClassToMFP( READY_CLASS );
					mfp._setFocus();
				} else {
					// if content is not defined (not loaded e.t.c) we add class only for BG
					mfp.bgOverlay.addClass( READY_CLASS );
				}

				// Trap the focus in popup
				_document.on( 'focusin' + EVENT_NS, mfp._onFocusIn );

			}, 16 );

			mfp.isOpen = true;
			mfp.updateSize( windowHeight );
			_mfpTrigger( OPEN_EVENT );

			return data;
		},
		/**
		 * Closes the popup
		 */
		close: function () {
			if ( ! mfp.isOpen )
				return;
			_mfpTrigger( BEFORE_CLOSE_EVENT );

			mfp.isOpen = false;
			// for CSS3 animation
			if ( mfp.st.removalDelay && ! mfp.isLowIE && mfp.supportsTransition ) {
				mfp._addClassToMFP( REMOVING_CLASS );
				setTimeout( function () {
					mfp._close();
				}, mfp.st.removalDelay );
			} else {
				mfp._close();
			}
		},
		/**
		 * Helper for close() function
		 */
		_close: function () {
			_mfpTrigger( CLOSE_EVENT );

			var classesToRemove = REMOVING_CLASS + ' ' + READY_CLASS + ' ';

			mfp.bgOverlay.detach();
			mfp.wrap.detach();
			mfp.container.empty();

			if ( mfp.st.mainClass ) {
				classesToRemove += mfp.st.mainClass + ' ';
			}

			mfp._removeClassFromMFP( classesToRemove );

			if ( mfp.fixedContentPos ) {
				var windowStyles = { marginRight: '' };
				if ( mfp.isIE7 ) {
					$( 'body, html' ).css( 'overflow', '' );
				} else {
					windowStyles.overflow = '';
				}
				$( 'html' ).css( windowStyles );
			}

			_document.off( 'keyup' + EVENT_NS + ' focusin' + EVENT_NS );
			mfp.ev.off( EVENT_NS );

			// clean up DOM elements that aren't removed
			mfp.wrap.attr( 'class', 'mfp-wrap' ).removeAttr( 'style' );
			mfp.bgOverlay.attr( 'class', 'mfp-bg' );
			mfp.container.attr( 'class', 'mfp-container' );

			// remove close button from target element
			if ( mfp.st.showCloseBtn &&
					( ! mfp.st.closeBtnInside || mfp.currTemplate[mfp.currItem.type] === true ) ) {
				if ( mfp.currTemplate.closeBtn )
					mfp.currTemplate.closeBtn.detach();
			}


			if ( mfp._lastFocusedEl ) {
				$( mfp._lastFocusedEl ).focus(); // put tab focus back
			}
			mfp.currItem = null;
			mfp.content = null;
			mfp.currTemplate = null;
			mfp.prevHeight = 0;

			_mfpTrigger( AFTER_CLOSE_EVENT );
		},
		updateSize: function ( winHeight ) {

			if ( mfp.isIOS ) {
				// fixes iOS nav bars https://github.com/dimsemenov/Magnific-Popup/issues/2
				var zoomLevel = document.documentElement.clientWidth / window.innerWidth;
				var height = window.innerHeight * zoomLevel;
				mfp.wrap.css( 'height', height );
				mfp.wH = height;
			} else {
				mfp.wH = winHeight || _window.height();
			}
			// Fixes #84: popup incorrectly positioned with position:relative on body
			if ( ! mfp.fixedContentPos ) {
				mfp.wrap.css( 'height', mfp.wH );
			}

			_mfpTrigger( 'Resize' );

		},
		/**
		 * Set content of popup based on current index
		 */
		updateItemHTML: function () {
			var item = mfp.items[mfp.index];

			// Detach and perform modifications
			mfp.contentContainer.detach();

			if ( mfp.content )
				mfp.content.detach();

			if ( ! item.parsed ) {
				item = mfp.parseEl( mfp.index );
			}

			var type = item.type;

			_mfpTrigger( 'BeforeChange', [ mfp.currItem ? mfp.currItem.type : '', type ] );
			// BeforeChange event works like so:
			// _mfpOn('BeforeChange', function(e, prevType, newType) { });

			mfp.currItem = item;





			if ( ! mfp.currTemplate[type] ) {
				var markup = mfp.st[type] ? mfp.st[type].markup : false;

				// allows to modify markup
				_mfpTrigger( 'FirstMarkupParse', markup );

				if ( markup ) {
					mfp.currTemplate[type] = $( markup );
				} else {
					// if there is no markup found we just define that template is parsed
					mfp.currTemplate[type] = true;
				}
			}

			if ( _prevContentType && _prevContentType !== item.type ) {
				mfp.container.removeClass( 'mfp-' + _prevContentType + '-holder' );
			}

			var newContent = mfp['get' + type.charAt( 0 ).toUpperCase() + type.slice( 1 )]( item, mfp.currTemplate[type] );
			mfp.appendContent( newContent, type );

			item.preloaded = true;

			_mfpTrigger( CHANGE_EVENT, item );
			_prevContentType = item.type;

			// Append container back after its content changed
			mfp.container.prepend( mfp.contentContainer );

			_mfpTrigger( 'AfterChange' );
		},
		/**
		 * Set HTML content of popup
		 */
		appendContent: function ( newContent, type ) {
			mfp.content = newContent;

			if ( newContent ) {
				if ( mfp.st.showCloseBtn && mfp.st.closeBtnInside &&
						mfp.currTemplate[type] === true ) {
					// if there is no markup, we just append close button element inside
					if ( ! mfp.content.find( '.mfp-close' ).length ) {
						mfp.content.append( _getCloseBtn() );
					}
				} else {
					mfp.content = newContent;
				}
			} else {
				mfp.content = '';
			}

			_mfpTrigger( BEFORE_APPEND_EVENT );
			mfp.container.addClass( 'mfp-' + type + '-holder' );

			mfp.contentContainer.append( mfp.content );
		},
		/**
		 * Creates Magnific Popup data object based on given data
		 * @param  {int} index Index of item to parse
		 */
		parseEl: function ( index ) {
			var item = mfp.items[index],
					type;

			if ( item.tagName ) {
				item = { el: $( item ) };
			} else {
				type = item.type;
				item = { data: item, src: item.src };
			}

			if ( item.el ) {
				var types = mfp.types;

				// check for 'mfp-TYPE' class
				for ( var i = 0; i < types.length; i ++ ) {
					if ( item.el.hasClass( 'mfp-' + types[i] ) ) {
						type = types[i];
						break;
					}
				}

				item.src = item.el.attr( 'data-mfp-src' );
				if ( ! item.src ) {
					item.src = item.el.attr( 'href' );
				}
			}

			item.type = type || mfp.st.type || 'inline';
			item.index = index;
			item.parsed = true;
			mfp.items[index] = item;
			_mfpTrigger( 'ElementParse', item );

			return mfp.items[index];
		},
		/**
		 * Initializes single popup or a group of popups
		 */
		addGroup: function ( el, options ) {
			var eHandler = function ( e ) {
				e.mfpEl = this;
				mfp._openClick( e, el, options );
			};

			if ( ! options ) {
				options = { };
			}

			var eName = 'click.magnificPopup';
			options.mainEl = el;

			if ( options.items ) {
				options.isObj = true;
				el.off( eName ).on( eName, eHandler );
			} else {
				options.isObj = false;
				if ( options.delegate ) {
					el.off( eName ).on( eName, options.delegate, eHandler );
				} else {
					options.items = el;
					el.off( eName ).on( eName, eHandler );
				}
			}
		},
		_openClick: function ( e, el, options ) {
			var midClick = options.midClick !== undefined ? options.midClick : $.magnificPopup.defaults.midClick;


			if ( ! midClick && ( e.which === 2 || e.ctrlKey || e.metaKey ) ) {
				return;
			}

			var disableOn = options.disableOn !== undefined ? options.disableOn : $.magnificPopup.defaults.disableOn;

			if ( disableOn ) {
				if ( $.isFunction( disableOn ) ) {
					if ( ! disableOn.call( mfp ) ) {
						return true;
					}
				} else { // else it's number
					if ( _window.width() < disableOn ) {
						return true;
					}
				}
			}

			if ( e.type ) {
				e.preventDefault();

				// This will prevent popup from closing if element is inside and popup is already opened
				if ( mfp.isOpen ) {
					e.stopPropagation();
				}
			}


			options.el = $( e.mfpEl );
			if ( options.delegate ) {
				options.items = el.find( options.delegate );
			}
			mfp.open( options );
		},
		/**
		 * Updates text on preloader
		 */
		updateStatus: function ( status, text ) {

			if ( mfp.preloader ) {
				if ( _prevStatus !== status ) {
					mfp.container.removeClass( 'mfp-s-' + _prevStatus );
				}

				if ( ! text && status === 'loading' ) {
					text = mfp.st.tLoading;
				}

				var data = {
					status: status,
					text: text
				};
				// allows to modify status
				_mfpTrigger( 'UpdateStatus', data );

				status = data.status;
				text = data.text;

				mfp.preloader.html( text );

				mfp.preloader.find( 'a' ).on( 'click', function ( e ) {
					e.stopImmediatePropagation();
				} );

				mfp.container.addClass( 'mfp-s-' + status );
				_prevStatus = status;
			}
		},
		/*
		 "Private" helpers that aren't private at all
		 */
		// Check to close popup or not
		// "target" is an element that was clicked
		_checkIfClose: function ( target ) {

			if ( $( target ).hasClass( PREVENT_CLOSE_CLASS ) ) {
				return;
			}

			var closeOnContent = mfp.st.closeOnContentClick;
			var closeOnBg = mfp.st.closeOnBgClick;

			if ( closeOnContent && closeOnBg ) {
				return true;
			} else {

				// We close the popup if click is on close button or on preloader. Or if there is no content.
				if ( ! mfp.content || $( target ).hasClass( 'mfp-close' ) || ( mfp.preloader && target === mfp.preloader[0] ) ) {
					return true;
				}

				// if click is outside the content
				if ( ( target !== mfp.content[0] && ! $.contains( mfp.content[0], target ) ) ) {
					if ( closeOnBg ) {
						// last check, if the clicked element is in DOM, (in case it's removed onclick)
						if ( $.contains( document, target ) ) {
							return true;
						}
					}
				} else if ( closeOnContent ) {
					return true;
				}

			}
			return false;
		},
		_addClassToMFP: function ( cName ) {
			mfp.bgOverlay.addClass( cName );
			mfp.wrap.addClass( cName );
		},
		_removeClassFromMFP: function ( cName ) {
			this.bgOverlay.removeClass( cName );
			mfp.wrap.removeClass( cName );
		},
		_hasScrollBar: function ( winHeight ) {
			return ( ( mfp.isIE7 ? _document.height() : document.body.scrollHeight ) > ( winHeight || _window.height() ) );
		},
		_setFocus: function () {
			( mfp.st.focus ? mfp.content.find( mfp.st.focus ).eq( 0 ) : mfp.wrap ).focus();
		},
		_onFocusIn: function ( e ) {
			if ( e.target !== mfp.wrap[0] && ! $.contains( mfp.wrap[0], e.target ) ) {
				mfp._setFocus();
				return false;
			}
		},
		_parseMarkup: function ( template, values, item ) {
			var arr;
			if ( item.data ) {
				values = $.extend( item.data, values );
			}
			_mfpTrigger( MARKUP_PARSE_EVENT, [ template, values, item ] );

			$.each( values, function ( key, value ) {
				if ( value === undefined || value === false ) {
					return true;
				}
				arr = key.split( '_' );
				if ( arr.length > 1 ) {
					var el = template.find( EVENT_NS + '-' + arr[0] );

					if ( el.length > 0 ) {
						var attr = arr[1];
						if ( attr === 'replaceWith' ) {
							if ( el[0] !== value[0] ) {
								el.replaceWith( value );
							}
						} else if ( attr === 'img' ) {
							if ( el.is( 'img' ) ) {
								el.attr( 'src', value );
							} else {
								el.replaceWith( '<img src="' + value + '" class="' + el.attr( 'class' ) + '" />' );
							}
						} else {
							el.attr( arr[1], value );
						}
					}

				} else {
					template.find( EVENT_NS + '-' + key ).html( value );
				}
			} );
		},
		_getScrollbarSize: function () {
			// thx David
			if ( mfp.scrollbarSize === undefined ) {
				var scrollDiv = document.createElement( "div" );
				scrollDiv.style.cssText = 'width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;';
				document.body.appendChild( scrollDiv );
				mfp.scrollbarSize = scrollDiv.offsetWidth - scrollDiv.clientWidth;
				document.body.removeChild( scrollDiv );
			}
			return mfp.scrollbarSize;
		}

	}; /* MagnificPopup core prototype end */




	/**
	 * Public static functions
	 */
	$.magnificPopup = {
		instance: null,
		proto: MagnificPopup.prototype,
		modules: [ ],
		open: function ( options, index ) {
			_checkInstance();

			if ( ! options ) {
				options = { };
			} else {
				options = $.extend( true, { }, options );
			}


			options.isObj = true;
			options.index = index || 0;
			return this.instance.open( options );
		},
		close: function () {
			return $.magnificPopup.instance && $.magnificPopup.instance.close();
		},
		registerModule: function ( name, module ) {
			if ( module.options ) {
				$.magnificPopup.defaults[name] = module.options;
			}
			$.extend( this.proto, module.proto );
			this.modules.push( name );
		},
		defaults: {
			// Info about options is in docs:
			// http://dimsemenov.com/plugins/magnific-popup/documentation.html#options

			disableOn: 0,
			key: null,
			midClick: false,
			mainClass: '',
			preloader: true,
			focus: '', // CSS selector of input to focus after popup is opened

			closeOnContentClick: false,
			closeOnBgClick: true,
			closeBtnInside: true,
			showCloseBtn: true,
			enableEscapeKey: true,
			modal: false,
			alignTop: false,
			removalDelay: 0,
			prependTo: null,
			fixedContentPos: 'auto',
			fixedBgPos: 'auto',
			overflowY: 'auto',
			closeMarkup: '<button title="%title%" type="button" class="mfp-close">&times;</button>',
			tClose: 'Close (Esc)',
			tLoading: 'Loading...'

		}
	};



	$.fn.magnificPopup = function ( options ) {
		_checkInstance();

		var jqEl = $( this );

		// We call some API method of first param is a string
		if ( typeof options === "string" ) {

			if ( options === 'open' ) {
				var items,
						itemOpts = _isJQ ? jqEl.data( 'magnificPopup' ) : jqEl[0].magnificPopup,
						index = parseInt( arguments[1], 10 ) || 0;

				if ( itemOpts.items ) {
					items = itemOpts.items[index];
				} else {
					items = jqEl;
					if ( itemOpts.delegate ) {
						items = items.find( itemOpts.delegate );
					}
					items = items.eq( index );
				}
				mfp._openClick( { mfpEl: items }, jqEl, itemOpts );
			} else {
				if ( mfp.isOpen )
					mfp[options].apply( mfp, Array.prototype.slice.call( arguments, 1 ) );
			}

		} else {
			// clone options obj
			options = $.extend( true, { }, options );

			/*
			 * As Zepto doesn't support .data() method for objects
			 * and it works only in normal browsers
			 * we assign "options" object directly to the DOM element. FTW!
			 */
			if ( _isJQ ) {
				jqEl.data( 'magnificPopup', options );
			} else {
				jqEl[0].magnificPopup = options;
			}

			mfp.addGroup( jqEl, options );

		}
		return jqEl;
	};


//Quick benchmark
	/*
	 var start = performance.now(),
	 i,
	 rounds = 1000;

	 for(i = 0; i < rounds; i++) {

	 }
	 console.log('Test #1:', performance.now() - start);

	 start = performance.now();
	 for(i = 0; i < rounds; i++) {

	 }
	 console.log('Test #2:', performance.now() - start);
	 */


	/*>>core*/

	/*>>inline*/

	var INLINE_NS = 'inline',
			_hiddenClass,
			_inlinePlaceholder,
			_lastInlineElement,
			_putInlineElementsBack = function () {
				if ( _lastInlineElement ) {
					_inlinePlaceholder.after( _lastInlineElement.addClass( _hiddenClass ) ).detach();
					_lastInlineElement = null;
				}
			};

	$.magnificPopup.registerModule( INLINE_NS, {
		options: {
			hiddenClass: 'hide', // will be appended with `mfp-` prefix
			markup: '',
			tNotFound: 'Content not found'
		},
		proto: {
			initInline: function () {
				mfp.types.push( INLINE_NS );

				_mfpOn( CLOSE_EVENT + '.' + INLINE_NS, function () {
					_putInlineElementsBack();
				} );
			},
			getInline: function ( item, template ) {

				_putInlineElementsBack();

				if ( item.src ) {
					var inlineSt = mfp.st.inline,
							el = $( item.src );

					if ( el.length ) {

						// If target element has parent - we replace it with placeholder and put it back after popup is closed
						var parent = el[0].parentNode;
						if ( parent && parent.tagName ) {
							if ( ! _inlinePlaceholder ) {
								_hiddenClass = inlineSt.hiddenClass;
								_inlinePlaceholder = _getEl( _hiddenClass );
								_hiddenClass = 'mfp-' + _hiddenClass;
							}
							// replace target inline element with placeholder
							_lastInlineElement = el.after( _inlinePlaceholder ).detach().removeClass( _hiddenClass );
						}

						mfp.updateStatus( 'ready' );
					} else {
						mfp.updateStatus( 'error', inlineSt.tNotFound );
						el = $( '<div>' );
					}

					item.inlineElement = el;
					return el;
				}

				mfp.updateStatus( 'ready' );
				mfp._parseMarkup( template, { }, item );
				return template;
			}
		}
	} );

	/*>>inline*/

	/*>>ajax*/
	var AJAX_NS = 'ajax',
			_ajaxCur,
			_removeAjaxCursor = function () {
				if ( _ajaxCur ) {
					$( document.body ).removeClass( _ajaxCur );
				}
			},
			_destroyAjaxRequest = function () {
				_removeAjaxCursor();
				if ( mfp.req ) {
					mfp.req.abort();
				}
			};

	$.magnificPopup.registerModule( AJAX_NS, {
		options: {
			settings: null,
			cursor: 'mfp-ajax-cur',
			tError: '<a href="%url%">The content</a> could not be loaded.'
		},
		proto: {
			initAjax: function () {
				mfp.types.push( AJAX_NS );
				_ajaxCur = mfp.st.ajax.cursor;

				_mfpOn( CLOSE_EVENT + '.' + AJAX_NS, _destroyAjaxRequest );
				_mfpOn( 'BeforeChange.' + AJAX_NS, _destroyAjaxRequest );
			},
			getAjax: function ( item ) {

				if ( _ajaxCur ) {
					$( document.body ).addClass( _ajaxCur );
				}

				mfp.updateStatus( 'loading' );

				var opts = $.extend( {
					url: item.src,
					success: function ( data, textStatus, jqXHR ) {
						var temp = {
							data: data,
							xhr: jqXHR
						};

						_mfpTrigger( 'ParseAjax', temp );

						mfp.appendContent( $( temp.data ), AJAX_NS );

						item.finished = true;

						_removeAjaxCursor();

						mfp._setFocus();

						setTimeout( function () {
							mfp.wrap.addClass( READY_CLASS );
						}, 16 );

						mfp.updateStatus( 'ready' );

						_mfpTrigger( 'AjaxContentAdded' );
					},
					error: function () {
						_removeAjaxCursor();
						item.finished = item.loadError = true;
						mfp.updateStatus( 'error', mfp.st.ajax.tError.replace( '%url%', item.src ) );
					}
				}, mfp.st.ajax.settings );

				mfp.req = $.ajax( opts );

				return '';
			}
		}
	} );







	/*>>ajax*/

	/*>>image*/
	var _imgInterval,
			_getTitle = function ( item ) {
				if ( item.data && item.data.title !== undefined )
					return item.data.title;

				var src = mfp.st.image.titleSrc;

				if ( src ) {
					if ( $.isFunction( src ) ) {
						return src.call( mfp, item );
					} else if ( item.el ) {
						return item.el.attr( src ) || '';
					}
				}
				return '';
			};

	$.magnificPopup.registerModule( 'image', {
		options: {
			markup: '<div class="mfp-figure">' +
					'<div class="mfp-close"></div>' +
					'<figure>' +
					'<div class="mfp-img"></div>' +
					'<figcaption>' +
					'<div class="mfp-bottom-bar">' +
					'<div class="mfp-title"></div>' +
					'<div class="mfp-counter"></div>' +
					'</div>' +
					'</figcaption>' +
					'</figure>' +
					'</div>',
			cursor: 'mfp-zoom-out-cur',
			titleSrc: 'title',
			verticalFit: true,
			tError: '<a href="%url%">The image</a> could not be loaded.'
		},
		proto: {
			initImage: function () {
				var imgSt = mfp.st.image,
						ns = '.image';

				mfp.types.push( 'image' );

				_mfpOn( OPEN_EVENT + ns, function () {
					if ( mfp.currItem.type === 'image' && imgSt.cursor ) {
						$( document.body ).addClass( imgSt.cursor );
					}
				} );

				_mfpOn( CLOSE_EVENT + ns, function () {
					if ( imgSt.cursor ) {
						$( document.body ).removeClass( imgSt.cursor );
					}
					_window.off( 'resize' + EVENT_NS );
				} );

				_mfpOn( 'Resize' + ns, mfp.resizeImage );
				if ( mfp.isLowIE ) {
					_mfpOn( 'AfterChange', mfp.resizeImage );
				}
			},
			resizeImage: function () {
				var item = mfp.currItem;
				if ( ! item || ! item.img )
					return;

				if ( mfp.st.image.verticalFit ) {
					var decr = 0;
					// fix box-sizing in ie7/8
					if ( mfp.isLowIE ) {
						decr = parseInt( item.img.css( 'padding-top' ), 10 ) + parseInt( item.img.css( 'padding-bottom' ), 10 );
					}
					item.img.css( 'max-height', mfp.wH - decr );
				}
			},
			_onImageHasSize: function ( item ) {
				if ( item.img ) {

					item.hasSize = true;

					if ( _imgInterval ) {
						clearInterval( _imgInterval );
					}

					item.isCheckingImgSize = false;

					_mfpTrigger( 'ImageHasSize', item );

					if ( item.imgHidden ) {
						if ( mfp.content )
							mfp.content.removeClass( 'mfp-loading' );

						item.imgHidden = false;
					}

				}
			},
			/**
			 * Function that loops until the image has size to display elements that rely on it asap
			 */
			findImageSize: function ( item ) {

				var counter = 0,
						img = item.img[0],
						mfpSetInterval = function ( delay ) {

							if ( _imgInterval ) {
								clearInterval( _imgInterval );
							}
							// decelerating interval that checks for size of an image
							_imgInterval = setInterval( function () {
								if ( img.naturalWidth > 0 ) {
									mfp._onImageHasSize( item );
									return;
								}

								if ( counter > 200 ) {
									clearInterval( _imgInterval );
								}

								counter ++;
								if ( counter === 3 ) {
									mfpSetInterval( 10 );
								} else if ( counter === 40 ) {
									mfpSetInterval( 50 );
								} else if ( counter === 100 ) {
									mfpSetInterval( 500 );
								}
							}, delay );
						};

				mfpSetInterval( 1 );
			},
			getImage: function ( item, template ) {

				var guard = 0,
						// image load complete handler
						onLoadComplete = function () {
							if ( item ) {
								if ( item.img[0].complete ) {
									item.img.off( '.mfploader' );

									if ( item === mfp.currItem ) {
										mfp._onImageHasSize( item );

										mfp.updateStatus( 'ready' );
									}

									item.hasSize = true;
									item.loaded = true;

									_mfpTrigger( 'ImageLoadComplete' );

								}
								else {
									// if image complete check fails 200 times (20 sec), we assume that there was an error.
									guard ++;
									if ( guard < 200 ) {
										setTimeout( onLoadComplete, 100 );
									} else {
										onLoadError();
									}
								}
							}
						},
						// image error handler
						onLoadError = function () {
							if ( item ) {
								item.img.off( '.mfploader' );
								if ( item === mfp.currItem ) {
									mfp._onImageHasSize( item );
									mfp.updateStatus( 'error', imgSt.tError.replace( '%url%', item.src ) );
								}

								item.hasSize = true;
								item.loaded = true;
								item.loadError = true;
							}
						},
						imgSt = mfp.st.image;


				var el = template.find( '.mfp-img' );
				if ( el.length ) {
					var img = document.createElement( 'img' );
					img.className = 'mfp-img';
					if ( item.el && item.el.find( 'img' ).length ) {
						img.alt = item.el.find( 'img' ).attr( 'alt' );
					}
					item.img = $( img ).on( 'load.mfploader', onLoadComplete ).on( 'error.mfploader', onLoadError );
					img.src = item.src;

					// without clone() "error" event is not firing when IMG is replaced by new IMG
					// TODO: find a way to avoid such cloning
					if ( el.is( 'img' ) ) {
						item.img = item.img.clone();
					}

					img = item.img[0];
					if ( img.naturalWidth > 0 ) {
						item.hasSize = true;
					} else if ( ! img.width ) {
						item.hasSize = false;
					}
				}

				mfp._parseMarkup( template, {
					title: _getTitle( item ),
					img_replaceWith: item.img
				}, item );

				mfp.resizeImage();

				if ( item.hasSize ) {
					if ( _imgInterval )
						clearInterval( _imgInterval );

					if ( item.loadError ) {
						template.addClass( 'mfp-loading' );
						mfp.updateStatus( 'error', imgSt.tError.replace( '%url%', item.src ) );
					} else {
						template.removeClass( 'mfp-loading' );
						mfp.updateStatus( 'ready' );
					}
					return template;
				}

				mfp.updateStatus( 'loading' );
				item.loading = true;

				if ( ! item.hasSize ) {
					item.imgHidden = true;
					template.addClass( 'mfp-loading' );
					mfp.findImageSize( item );
				}

				return template;
			}
		}
	} );



	/*>>image*/

	/*>>zoom*/
	var hasMozTransform,
			getHasMozTransform = function () {
				if ( hasMozTransform === undefined ) {
					hasMozTransform = document.createElement( 'p' ).style.MozTransform !== undefined;
				}
				return hasMozTransform;
			};

	$.magnificPopup.registerModule( 'zoom', {
		options: {
			enabled: false,
			easing: 'ease-in-out',
			duration: 300,
			opener: function ( element ) {
				return element.is( 'img' ) ? element : element.find( 'img' );
			}
		},
		proto: {
			initZoom: function () {
				var zoomSt = mfp.st.zoom,
						ns = '.zoom',
						image;

				if ( ! zoomSt.enabled || ! mfp.supportsTransition ) {
					return;
				}

				var duration = zoomSt.duration,
						getElToAnimate = function ( image ) {
							var newImg = image.clone().removeAttr( 'style' ).removeAttr( 'class' ).addClass( 'mfp-animated-image' ),
									transition = 'all ' + ( zoomSt.duration / 1000 ) + 's ' + zoomSt.easing,
									cssObj = {
										position: 'fixed',
										zIndex: 9999,
										left: 0,
										top: 0,
										'-webkit-backface-visibility': 'hidden'
									},
							t = 'transition';

							cssObj['-webkit-' + t] = cssObj['-moz-' + t] = cssObj['-o-' + t] = cssObj[t] = transition;

							newImg.css( cssObj );
							return newImg;
						},
						showMainContent = function () {
							mfp.content.css( 'visibility', 'visible' );
						},
						openTimeout,
						animatedImg;

				_mfpOn( 'BuildControls' + ns, function () {
					if ( mfp._allowZoom() ) {

						clearTimeout( openTimeout );
						mfp.content.css( 'visibility', 'hidden' );

						// Basically, all code below does is clones existing image, puts in on top of the current one and animated it

						image = mfp._getItemToZoom();

						if ( ! image ) {
							showMainContent();
							return;
						}

						animatedImg = getElToAnimate( image );

						animatedImg.css( mfp._getOffset() );

						mfp.wrap.append( animatedImg );

						openTimeout = setTimeout( function () {
							animatedImg.css( mfp._getOffset( true ) );
							openTimeout = setTimeout( function () {

								showMainContent();

								setTimeout( function () {
									animatedImg.remove();
									image = animatedImg = null;
									_mfpTrigger( 'ZoomAnimationEnded' );
								}, 16 ); // avoid blink when switching images

							}, duration ); // this timeout equals animation duration

						}, 16 ); // by adding this timeout we avoid short glitch at the beginning of animation


						// Lots of timeouts...
					}
				} );
				_mfpOn( BEFORE_CLOSE_EVENT + ns, function () {
					if ( mfp._allowZoom() ) {

						clearTimeout( openTimeout );

						mfp.st.removalDelay = duration;

						if ( ! image ) {
							image = mfp._getItemToZoom();
							if ( ! image ) {
								return;
							}
							animatedImg = getElToAnimate( image );
						}


						animatedImg.css( mfp._getOffset( true ) );
						mfp.wrap.append( animatedImg );
						mfp.content.css( 'visibility', 'hidden' );

						setTimeout( function () {
							animatedImg.css( mfp._getOffset() );
						}, 16 );
					}

				} );

				_mfpOn( CLOSE_EVENT + ns, function () {
					if ( mfp._allowZoom() ) {
						showMainContent();
						if ( animatedImg ) {
							animatedImg.remove();
						}
						image = null;
					}
				} );
			},
			_allowZoom: function () {
				return mfp.currItem.type === 'image';
			},
			_getItemToZoom: function () {
				if ( mfp.currItem.hasSize ) {
					return mfp.currItem.img;
				} else {
					return false;
				}
			},
			// Get element postion relative to viewport
			_getOffset: function ( isLarge ) {
				var el;
				if ( isLarge ) {
					el = mfp.currItem.img;
				} else {
					el = mfp.st.zoom.opener( mfp.currItem.el || mfp.currItem );
				}

				var offset = el.offset();
				var paddingTop = parseInt( el.css( 'padding-top' ), 10 );
				var paddingBottom = parseInt( el.css( 'padding-bottom' ), 10 );
				offset.top -= ( $( window ).scrollTop() - paddingTop );


				/*

				 Animating left + top + width/height looks glitchy in Firefox, but perfect in Chrome. And vice-versa.

				 */
				var obj = {
					width: el.width(),
					// fix Zepto height+padding issue
					height: ( _isJQ ? el.innerHeight() : el[0].offsetHeight ) - paddingBottom - paddingTop
				};

				// I hate to do this, but there is no another option
				if ( getHasMozTransform() ) {
					obj['-moz-transform'] = obj['transform'] = 'translate(' + offset.left + 'px,' + offset.top + 'px)';
				} else {
					obj.left = offset.left;
					obj.top = offset.top;
				}
				return obj;
			}

		}
	} );



	/*>>zoom*/

	/*>>iframe*/

	var IFRAME_NS = 'iframe',
			_emptyPage = '//about:blank',
			_fixIframeBugs = function ( isShowing ) {
				if ( mfp.currTemplate[IFRAME_NS] ) {
					var el = mfp.currTemplate[IFRAME_NS].find( 'iframe' );
					if ( el.length ) {
						// reset src after the popup is closed to avoid "video keeps playing after popup is closed" bug
						if ( ! isShowing ) {
							el[0].src = _emptyPage;
						}

						// IE8 black screen bug fix
						if ( mfp.isIE8 ) {
							el.css( 'display', isShowing ? 'block' : 'none' );
						}
					}
				}
			};

	$.magnificPopup.registerModule( IFRAME_NS, {
		options: {
			markup: '<div class="mfp-iframe-scaler">' +
					'<div class="mfp-close"></div>' +
					'<iframe class="mfp-iframe" src="//about:blank" frameborder="0" allowfullscreen></iframe>' +
					'</div>',
			srcAction: 'iframe_src',
			// we don't care and support only one default type of URL by default
			patterns: {
				youtube: {
					index: 'youtube.com',
					id: 'v=',
					src: '//www.youtube.com/embed/%id%?autoplay=1'
				},
				vimeo: {
					index: 'vimeo.com/',
					id: '/',
					src: '//player.vimeo.com/video/%id%?autoplay=1'
				},
				gmaps: {
					index: '//maps.google.',
					src: '%id%&output=embed'
				}
			}
		},
		proto: {
			initIframe: function () {
				mfp.types.push( IFRAME_NS );

				_mfpOn( 'BeforeChange', function ( e, prevType, newType ) {
					if ( prevType !== newType ) {
						if ( prevType === IFRAME_NS ) {
							_fixIframeBugs(); // iframe if removed
						} else if ( newType === IFRAME_NS ) {
							_fixIframeBugs( true ); // iframe is showing
						}
					}// else {
					// iframe source is switched, don't do anything
					//}
				} );

				_mfpOn( CLOSE_EVENT + '.' + IFRAME_NS, function () {
					_fixIframeBugs();
				} );
			},
			getIframe: function ( item, template ) {
				var embedSrc = item.src;
				var iframeSt = mfp.st.iframe;

				$.each( iframeSt.patterns, function () {
					if ( embedSrc.indexOf( this.index ) > - 1 ) {
						if ( this.id ) {
							if ( typeof this.id === 'string' ) {
								embedSrc = embedSrc.substr( embedSrc.lastIndexOf( this.id ) + this.id.length, embedSrc.length );
							} else {
								embedSrc = this.id.call( this, embedSrc );
							}
						}
						embedSrc = this.src.replace( '%id%', embedSrc );
						return false; // break;
					}
				} );

				var dataObj = { };
				if ( iframeSt.srcAction ) {
					dataObj[iframeSt.srcAction] = embedSrc;
				}
				mfp._parseMarkup( template, dataObj, item );

				mfp.updateStatus( 'ready' );

				return template;
			}
		}
	} );



	/*>>iframe*/

	/*>>gallery*/
	/**
	 * Get looped index depending on number of slides
	 */
	var _getLoopedId = function ( index ) {
		var numSlides = mfp.items.length;
		if ( index > numSlides - 1 ) {
			return index - numSlides;
		} else if ( index < 0 ) {
			return numSlides + index;
		}
		return index;
	},
			_replaceCurrTotal = function ( text, curr, total ) {
				return text.replace( /%curr%/gi, curr + 1 ).replace( /%total%/gi, total );
			};

	$.magnificPopup.registerModule( 'gallery', {
		options: {
			enabled: false,
			arrowMarkup: '<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',
			preload: [ 0, 2 ],
			navigateByImgClick: true,
			arrows: true,
			tPrev: 'Previous (Left arrow key)',
			tNext: 'Next (Right arrow key)',
			tCounter: '%curr% of %total%'
		},
		proto: {
			initGallery: function () {

				var gSt = mfp.st.gallery,
						ns = '.mfp-gallery',
						supportsFastClick = Boolean( $.fn.mfpFastClick );

				mfp.direction = true; // true - next, false - prev

				if ( ! gSt || ! gSt.enabled )
					return false;

				_wrapClasses += ' mfp-gallery';

				_mfpOn( OPEN_EVENT + ns, function () {

					if ( gSt.navigateByImgClick ) {
						mfp.wrap.on( 'click' + ns, '.mfp-img', function () {
							if ( mfp.items.length > 1 ) {
								mfp.next();
								return false;
							}
						} );
					}

					_document.on( 'keydown' + ns, function ( e ) {
						if ( e.keyCode === 37 ) {
							mfp.prev();
						} else if ( e.keyCode === 39 ) {
							mfp.next();
						}
					} );
				} );

				_mfpOn( 'UpdateStatus' + ns, function ( e, data ) {
					if ( data.text ) {
						data.text = _replaceCurrTotal( data.text, mfp.currItem.index, mfp.items.length );
					}
				} );

				_mfpOn( MARKUP_PARSE_EVENT + ns, function ( e, element, values, item ) {
					var l = mfp.items.length;
					values.counter = l > 1 ? _replaceCurrTotal( gSt.tCounter, item.index, l ) : '';
				} );

				_mfpOn( 'BuildControls' + ns, function () {
					if ( mfp.items.length > 1 && gSt.arrows && ! mfp.arrowLeft ) {
						var markup = gSt.arrowMarkup,
								arrowLeft = mfp.arrowLeft = $( markup.replace( /%title%/gi, gSt.tPrev ).replace( /%dir%/gi, 'left' ) ).addClass( PREVENT_CLOSE_CLASS ),
								arrowRight = mfp.arrowRight = $( markup.replace( /%title%/gi, gSt.tNext ).replace( /%dir%/gi, 'right' ) ).addClass( PREVENT_CLOSE_CLASS );

						var eName = supportsFastClick ? 'mfpFastClick' : 'click';
						arrowLeft[eName]( function () {
							mfp.prev();
						} );
						arrowRight[eName]( function () {
							mfp.next();
						} );

						// Polyfill for :before and :after (adds elements with classes mfp-a and mfp-b)
						if ( mfp.isIE7 ) {
							_getEl( 'b', arrowLeft[0], false, true );
							_getEl( 'a', arrowLeft[0], false, true );
							_getEl( 'b', arrowRight[0], false, true );
							_getEl( 'a', arrowRight[0], false, true );
						}

						mfp.container.append( arrowLeft.add( arrowRight ) );
					}
				} );

				_mfpOn( CHANGE_EVENT + ns, function () {
					if ( mfp._preloadTimeout )
						clearTimeout( mfp._preloadTimeout );

					mfp._preloadTimeout = setTimeout( function () {
						mfp.preloadNearbyImages();
						mfp._preloadTimeout = null;
					}, 16 );
				} );


				_mfpOn( CLOSE_EVENT + ns, function () {
					_document.off( ns );
					mfp.wrap.off( 'click' + ns );

					if ( mfp.arrowLeft && supportsFastClick ) {
						mfp.arrowLeft.add( mfp.arrowRight ).destroyMfpFastClick();
					}
					mfp.arrowRight = mfp.arrowLeft = null;
				} );

			},
			next: function () {
				mfp.direction = true;
				mfp.index = _getLoopedId( mfp.index + 1 );
				mfp.updateItemHTML();
			},
			prev: function () {
				mfp.direction = false;
				mfp.index = _getLoopedId( mfp.index - 1 );
				mfp.updateItemHTML();
			},
			goTo: function ( newIndex ) {
				mfp.direction = ( newIndex >= mfp.index );
				mfp.index = newIndex;
				mfp.updateItemHTML();
			},
			preloadNearbyImages: function () {
				var p = mfp.st.gallery.preload,
						preloadBefore = Math.min( p[0], mfp.items.length ),
						preloadAfter = Math.min( p[1], mfp.items.length ),
						i;

				for ( i = 1; i <= ( mfp.direction ? preloadAfter : preloadBefore ); i ++ ) {
					mfp._preloadItem( mfp.index + i );
				}
				for ( i = 1; i <= ( mfp.direction ? preloadBefore : preloadAfter ); i ++ ) {
					mfp._preloadItem( mfp.index - i );
				}
			},
			_preloadItem: function ( index ) {
				index = _getLoopedId( index );

				if ( mfp.items[index].preloaded ) {
					return;
				}

				var item = mfp.items[index];
				if ( ! item.parsed ) {
					item = mfp.parseEl( index );
				}

				_mfpTrigger( 'LazyLoad', item );

				if ( item.type === 'image' ) {
					item.img = $( '<img class="mfp-img" />' ).on( 'load.mfploader', function () {
						item.hasSize = true;
					} ).on( 'error.mfploader', function () {
						item.hasSize = true;
						item.loadError = true;
						_mfpTrigger( 'LazyLoadError', item );
					} ).attr( 'src', item.src );
				}


				item.preloaded = true;
			}
		}
	} );

	/*
	 Touch Support that might be implemented some day

	 addSwipeGesture: function() {
	 var startX,
	 moved,
	 multipleTouches;

	 return;

	 var namespace = '.mfp',
	 addEventNames = function(pref, down, move, up, cancel) {
	 mfp._tStart = pref + down + namespace;
	 mfp._tMove = pref + move + namespace;
	 mfp._tEnd = pref + up + namespace;
	 mfp._tCancel = pref + cancel + namespace;
	 };

	 if(window.navigator.msPointerEnabled) {
	 addEventNames('MSPointer', 'Down', 'Move', 'Up', 'Cancel');
	 } else if('ontouchstart' in window) {
	 addEventNames('touch', 'start', 'move', 'end', 'cancel');
	 } else {
	 return;
	 }
	 _window.on(mfp._tStart, function(e) {
	 var oE = e.originalEvent;
	 multipleTouches = moved = false;
	 startX = oE.pageX || oE.changedTouches[0].pageX;
	 }).on(mfp._tMove, function(e) {
	 if(e.originalEvent.touches.length > 1) {
	 multipleTouches = e.originalEvent.touches.length;
	 } else {
	 //e.preventDefault();
	 moved = true;
	 }
	 }).on(mfp._tEnd + ' ' + mfp._tCancel, function(e) {
	 if(moved && !multipleTouches) {
	 var oE = e.originalEvent,
	 diff = startX - (oE.pageX || oE.changedTouches[0].pageX);

	 if(diff > 20) {
	 mfp.next();
	 } else if(diff < -20) {
	 mfp.prev();
	 }
	 }
	 });
	 },
	 */


	/*>>gallery*/

	/*>>retina*/

	var RETINA_NS = 'retina';

	$.magnificPopup.registerModule( RETINA_NS, {
		options: {
			replaceSrc: function ( item ) {
				return item.src.replace( /\.\w+$/, function ( m ) {
					return '@2x' + m;
				} );
			},
			ratio: 1 // Function or number.  Set to 1 to disable.
		},
		proto: {
			initRetina: function () {
				if ( window.devicePixelRatio > 1 ) {

					var st = mfp.st.retina,
							ratio = st.ratio;

					ratio = ! isNaN( ratio ) ? ratio : ratio();

					if ( ratio > 1 ) {
						_mfpOn( 'ImageHasSize' + '.' + RETINA_NS, function ( e, item ) {
							item.img.css( {
								'max-width': item.img[0].naturalWidth / ratio,
								'width': '100%'
							} );
						} );
						_mfpOn( 'ElementParse' + '.' + RETINA_NS, function ( e, item ) {
							item.src = st.replaceSrc( item, ratio );
						} );
					}
				}

			}
		}
	} );

	/*>>retina*/

	/*>>fastclick*/
	/**
	 * FastClick event implementation. (removes 300ms delay on touch devices)
	 * Based on https://developers.google.com/mobile/articles/fast_buttons
	 *
	 * You may use it outside the Magnific Popup by calling just:
	 *
	 * $('.your-el').mfpFastClick(function() {
	 *     console.log('Clicked!');
	 * });
	 *
	 * To unbind:
	 * $('.your-el').destroyMfpFastClick();
	 *
	 *
	 * Note that it's a very basic and simple implementation, it blocks ghost click on the same element where it was bound.
	 * If you need something more advanced, use plugin by FT Labs https://github.com/ftlabs/fastclick
	 *
	 */

	( function () {
		var ghostClickDelay = 1000,
				supportsTouch = 'ontouchstart' in window,
				unbindTouchMove = function () {
					_window.off( 'touchmove' + ns + ' touchend' + ns );
				},
				eName = 'mfpFastClick',
				ns = '.' + eName;


		// As Zepto.js doesn't have an easy way to add custom events (like jQuery), so we implement it in this way
		$.fn.mfpFastClick = function ( callback ) {

			return $( this ).each( function () {

				var elem = $( this ),
						lock;

				if ( supportsTouch ) {

					var timeout,
							startX,
							startY,
							pointerMoved,
							point,
							numPointers;

					elem.on( 'touchstart' + ns, function ( e ) {
						pointerMoved = false;
						numPointers = 1;

						point = e.originalEvent ? e.originalEvent.touches[0] : e.touches[0];
						startX = point.clientX;
						startY = point.clientY;

						_window.on( 'touchmove' + ns, function ( e ) {
							point = e.originalEvent ? e.originalEvent.touches : e.touches;
							numPointers = point.length;
							point = point[0];
							if ( Math.abs( point.clientX - startX ) > 10 ||
									Math.abs( point.clientY - startY ) > 10 ) {
								pointerMoved = true;
								unbindTouchMove();
							}
						} ).on( 'touchend' + ns, function ( e ) {
							unbindTouchMove();
							if ( pointerMoved || numPointers > 1 ) {
								return;
							}
							lock = true;
							e.preventDefault();
							clearTimeout( timeout );
							timeout = setTimeout( function () {
								lock = false;
							}, ghostClickDelay );
							callback();
						} );
					} );

				}

				elem.on( 'click' + ns, function () {
					if ( ! lock ) {
						callback();
					}
				} );
			} );
		};

		$.fn.destroyMfpFastClick = function () {
			$( this ).off( 'touchstart' + ns + ' click' + ns );
			if ( supportsTouch )
				_window.off( 'touchmove' + ns + ' touchend' + ns );
		};
	} )();

	/*>>fastclick*/
	_checkInstance();
} ) );

// Written by S@G@R

/* Utility : Object.create dosen't work all browsers. */
if ( typeof Object.create !== 'function' ) {
	Object.create = function ( obj ) {
		function F() {
		}
		;
		F.prototype = obj;
		return new F();
	};
}

( function ( $, window, document, undefined ) {

	var Tab = {
		init: function ( options, elem ) {
			var self = this;
			self.elem = elem;
			self.$elem = $( elem );

			/* Extend Options */
			self.options = $.extend( { }, $.fn.rtTab.options, options );

			self.rtTabs();
		},
		rtTabs: function () {
			var self = this,
					showTab = self.options.activeTab;

			/* Tab Active */
			self.$elem.find( 'li:nth-child(' + showTab + ')' ).addClass( 'active' );
			self.rtTabContent( activeTabContent = 'yes' );
			self.rtClick();

			// Datahash Variable
			var datahash = ( self.$elem.attr( 'data-hash' ) === 'false' ) ? false : true;

			/* This will keep on same tab as in hashtag */
			if ( datahash === true ) {
				var hashTag = window.location.hash;

				if ( hashTag ) {
					self.$elem.find( 'li' ).find( 'a[href=' + hashTag + ']' ).trigger( 'click' );
				}

				// Detect change in hash value of URL
				$( window ).on( 'hashchange', function () {
					var hashTag = window.location.hash;
					// Iterate over all nav links, setting the "selected" class as-appropriate.
					self.$elem.find( 'li' ).find( 'a[href=' + hashTag + ']' ).trigger( 'click' );
				} );
			}

		},
		rtClick: function () {
			var self = this,
					eachTab = self.$elem.find( 'li' ),
					tabLink = eachTab.find( 'a' );

			tabLink.on( 'click', function ( e ) {
				/* Prevent */
				e.preventDefault();

				/* Remove Active Class From All Tabs */
				eachTab.removeClass( 'active' );

				/* Hide All Tab Contents */
				self.rtTabContent();

				/* Add Active Class to Current Tab */
				$( this ).parent().addClass( 'active' );

				/* Show Active Tab Content */
				var activeTab = $( this ).attr( 'href' );
				$( activeTab ).removeClass( 'hide' );

				// Datahash Variable
				var datahash = ( self.$elem.attr( 'data-hash' ) === 'false' ) ? false : true;

				/* Hash tag in URL */
				if ( datahash === true ) {
					var pos = $( window ).scrollTop();
					location.hash = $( this ).attr( 'href' );
					$( window ).scrollTop( pos );
				}

				/* On complete function */
				if ( typeof self.options.onComplete === 'function' ) {
					self.options.onComplete.apply( self.elem, arguments );
				}

			} );
		},
		rtTabContent: function ( activeTabContent ) {
			var self = this,
					eachTab = self.$elem.find( 'li' ),
					tabLink = eachTab.find( 'a' );

			tabLink.each( function () {
				var link = $( this ),
						tabContent = link.attr( 'href' );
				if ( activeTabContent === 'yes' ) {
					if ( ! link.parent().hasClass( 'active' ) ) {
						$( tabContent ).addClass( 'hide' );
					}
				} else {
					$( tabContent ).addClass( 'hide' );
				}
			} );
		}
	};

	$.fn.rtTab = function ( options ) {
		return this.each( function () {
			var tab = Object.create( Tab );
			tab.init( options, this );

			/* Store Data */
			$.data( this, 'rtTab', tab );
		} );
	};

	$.fn.rtTab.options = {
		activeTab: 1,
		onComplete: null
	};

} )( jQuery, window, document );

var rtMagnificPopup;
var rtm_masonry_container;
function apply_rtMagnificPopup( selector ) {
	jQuery( 'document' ).ready( function ( $ ) {
		var rt_load_more = "";
		if ( typeof ( rtmedia_load_more ) === "undefined" ) {
			rt_load_more = "Loading media";
		} else {
			rt_load_more = rtmedia_load_more;
		}
		if ( typeof(rtmedia_lightbox_enabled) != 'undefined' && rtmedia_lightbox_enabled == '1' ) { // if lightbox is enabled.

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
					arrowMarkup: '', // disabled default arrows
					preload: [ 0, 1 ] // Will preload 0 - before current, and 1 after the current image
				},
				image: {
					tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
					titleSrc: function ( item ) {
						return item.el.attr( 'title' ) + '<small>by Marsel Van Oosten</small>';
					}
				},
				callbacks: {
					ajaxContentAdded: function () {
                
                        mfp = jQuery.magnificPopup.instance;
                        if ( jQuery(mfp.items).size() === 1 ) {
                            jQuery(".mfp-arrow").remove();
                        }
						// When last second media is encountered in lightbox, load more medias if available
						var mfp = jQuery.magnificPopup.instance;
						var current_media = mfp.currItem.el;
						var li = current_media.parent();
						if ( ! li.is( 'li' ) ) {
							li = li.parent();
						}
						if ( li.is( ':nth-last-child(2)' ) ) { // if its last second media
							var last_li = li.next();
							if ( jQuery( '#rtMedia-galary-next' ).css( 'display' ) == 'block' ) { // if more medias are available
								jQuery( '#rtMedia-galary-next' ).click(); // load more
							}
						}

						var items = mfp.items.length;
						if ( mfp.index == ( items - 1 ) && ! ( li.is( ":last-child" ) ) ) {
							current_media.click();
							return;
						}

						$container = this.content.find( '.tagcontainer' );
						if ( $container.length > 0 ) {
							$context = $container.find( 'img' );
							$container.find( '.tagcontainer' ).css(
									{
										'height': $context.css( 'height' ),
										'width': $context.css( 'width' )
									} );

						}
						var settings = { };

						if ( typeof _wpmejsSettings !== 'undefined' )
							settings.pluginPath = _wpmejsSettings.pluginPath;
						$( '.mfp-content .wp-audio-shortcode,.mfp-content .wp-video-shortcode,.mfp-content .bp_media_content video' ).mediaelementplayer( {
							// if the <video width> is not specified, this is the default
							defaultVideoWidth: 480,
							// if the <video height> is not specified, this is the default
							defaultVideoHeight: 270,
							// if set, overrides <video width>
							//videoWidth: 1,
							// if set, overrides <video height>
							//videoHeight: 1
						} );
						$( '.mfp-content .mejs-audio .mejs-controls' ).css( 'position', 'relative' );
						rtMediaHook.call( 'rtmedia_js_popup_after_content_added', [ ] );
					},
					close: function ( e ) {
						//console.log(e);
						rtmedia_init_action_dropdown();
					},
					BeforeChange: function ( e ) {
						//console.log(e);
					}
				}
			} );
		}
	} );
}

var rtMediaHook = {
	hooks: [ ],
	is_break: false,
	register: function ( name, callback ) {
		if ( 'undefined' == typeof ( rtMediaHook.hooks[name] ) )
			rtMediaHook.hooks[name] = [ ]
		rtMediaHook.hooks[name].push( callback )
	},
	call: function ( name, arguments ) {
		if ( 'undefined' != typeof ( rtMediaHook.hooks[name] ) )
			for ( i = 0; i < rtMediaHook.hooks[name].length; ++ i ) {
				if ( true != rtMediaHook.hooks[name][i]( arguments ) ) {
					rtMediaHook.is_break = true;
					return false;
					break;
				}
			}
		return true;
	}
}

//drop-down js
function rtmedia_init_action_dropdown() {
	var all_ul;
	var curr_ul;
	jQuery( '.click-nav > span, .click-nav > div' ).toggleClass( 'no-js js' );
	jQuery( '.click-nav .js ul' ).hide();
	jQuery( '.click-nav .clicker' ).click( function ( e ) {
		all_ul = jQuery( '#rtm-media-options .click-nav .clicker' ).next( 'ul' );
		curr_ul = jQuery( this ).next( 'ul' );
		jQuery.each( all_ul, function ( index, value ) {
			if ( jQuery( value ).html() != curr_ul.html() ) {     // check clicked option with other options
				jQuery( value ).hide();
			}
		} );
		jQuery( curr_ul ).toggle();
		e.stopPropagation();
	} );
}

jQuery( 'document' ).ready( function ( $ ) {

	// Tabs
	$( '.rtm-tabs' ).rtTab();

	// open magnific popup as modal for create album/playlist
	if ( jQuery( '.rtmedia-modal-link' ).length > 0 ) {
		$( '.rtmedia-modal-link' ).magnificPopup( {
			type: 'inline',
			midClick: true, // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href
			closeBtnInside: true,
		} );
	}

	$( "#rt_media_comment_form" ).submit( function ( e ) {
		if ( $.trim( $( "#comment_content" ).val() ) == "" ) {
			alert( rtmedia_empty_comment_msg );
			return false;
		} else {
			return true;
		}

	} )

	//Remove title from popup duplication
	$( "li.rtmedia-list-item p a" ).each( function ( e ) {
		$( this ).addClass( "no-popup" );
	} );

    //Remove title from popup duplication
    $("li.rtmedia-list-item p a").each(function(e) {
        $(this).addClass("no-popup");
    })
    //rtmedia_lightbox_enabled from setting
    if (typeof(rtmedia_lightbox_enabled) != 'undefined' && rtmedia_lightbox_enabled == "1") {
        apply_rtMagnificPopup('.rtmedia-list-media, .rtmedia-activity-container ul.rtmedia-list, #bp-media-list,.bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content, .rtm-bbp-container, .comment-content');
    }

    jQuery.ajaxPrefilter(function(options, originalOptions, jqXHR) {
		try{
	        if (originalOptions.data == null || typeof(originalOptions.data) == "undefined" || typeof(originalOptions.data.action) == "undefined" ) {
	            return true;
	        }
	    }catch(e){
	        return true;
	    }

	    // Handle lightbox in BuddyPress activity loadmore
	    if (originalOptions.data.action == 'activity_get_older_updates') {
		    var orignalSuccess = originalOptions.success;
		    options.success = function(response) {
				orignalSuccess(response);
				apply_rtMagnificPopup('.rtmedia-activity-container ul.rtmedia-list, #bp-media-list, .bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content');
				rtMediaHook.call('rtmedia_js_after_activity_added', []);
		    }
		} else if ( originalOptions.data.action == 'get_single_activity_content' ) {
		    // Handle lightbox in BuddyPress single activity loadmore
		    var orignalSuccess = originalOptions.success;
		    options.success = function ( response ) {
			    orignalSuccess( response );
			    setTimeout( function(){
				    apply_rtMagnificPopup('.rtmedia-activity-container ul.rtmedia-list, #bp-media-list, .bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content');
				    jQuery( 'ul.activity-list li.rtmedia_update:first-child .wp-audio-shortcode, ul.activity-list li.rtmedia_update:first-child .wp-video-shortcode' ).mediaelementplayer( {

					    // if the <video width> is not specified, this is the default
					    defaultVideoWidth: 480,
					    // if the <video height> is not specified, this is the default
					    defaultVideoHeight: 270
					    // if set, overrides <video width>
					    //videoWidth: 1,
					    // if set, overrides <video height>
					    //videoHeight: 1
				    } );
			    }, 900 );
		    }
	    }
    });

	jQuery.ajaxPrefilter( function ( options, originalOptions, jqXHR ) {
		try {
			if ( originalOptions.data == null || typeof ( originalOptions.data ) == "undefined" || typeof ( originalOptions.data.action ) == "undefined" ) {
				return true;
			}
		} catch ( e ) {
			return true;
		}
		if ( originalOptions.data.action == 'activity_get_older_updates' ) {
			var orignalSuccess = originalOptions.success;
			options.success = function ( response ) {
				orignalSuccess( response );
				apply_rtMagnificPopup( '.rtmedia-activity-container ul.rtmedia-list, #bp-media-list, .bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content' );
				rtMediaHook.call( 'rtmedia_js_after_activity_added', [ ] );
			}
		}
	} );

	jQuery( '.rtmedia-container' ).on( 'click', '.select-all', function ( e ) {
		jQuery( this ).toggleClass( 'unselect-all' ).toggleClass( 'select-all' );
		jQuery( this ).attr( 'title', rtmedia_unselect_all_visible );
		jQuery( '.rtmedia-list input' ).each( function () {
			jQuery( this ).prop( 'checked', true );
		} );
		jQuery( '.rtmedia-list-item' ).addClass( 'bulk-selected' );
	} );

	jQuery( '.rtmedia-container' ).on( 'click', '.unselect-all', function ( e ) {
		jQuery( this ).toggleClass( 'select-all' ).toggleClass( 'unselect-all' );
		jQuery( this ).attr( 'title', rtmedia_select_all_visible );
		jQuery( '.rtmedia-list input' ).each( function () {
			jQuery( this ).prop( 'checked', false );
		} );
		jQuery( '.rtmedia-list-item' ).removeClass( 'bulk-selected' );
	} );

	jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-move', function ( e ) {
		jQuery( '.rtmedia-delete-container' ).slideUp();
		jQuery( '.rtmedia-move-container' ).slideToggle();
	} );

	jQuery( '#rtmedia-create-album-modal' ).on( 'click', '#rtmedia_create_new_album', function ( e ) {
		$albumname = jQuery.trim( jQuery( '#rtmedia_album_name' ).val() );
		$context = jQuery.trim( jQuery( '#rtmedia_album_context' ).val() );
		$context_id = jQuery.trim( jQuery( '#rtmedia_album_context_id' ).val() );
		$privacy = jQuery.trim( jQuery( '#rtmedia_select_album_privacy' ).val() );
		$create_album_nonce = jQuery.trim( jQuery( '#rtmedia_create_album_nonce' ).val() );

		if ( $albumname != '' ) {
			var data = {
				action: 'rtmedia_create_album',
				name: $albumname,
				context: $context,
				context_id: $context_id,
				create_album_nonce: $create_album_nonce
			};

			if ( $privacy !== "" ) {
				data[ 'privacy' ] = $privacy;
			}

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$( "#rtmedia_create_new_album" ).attr( 'disabled', 'disabled' );
			var old_val = $( "#rtmedia_create_new_album" ).html();
			$( "#rtmedia_create_new_album" ).prepend( "<img src='" + rMedia_loading_file + "' />" );

			jQuery.post( rtmedia_ajax_url, data, function ( response ) {
				response = response.trim();

				if ( response ) {
					response = response.trim();
					var flag = true;

					jQuery( '.rtmedia-user-album-list' ).each( function () {
						jQuery( this ).children( 'optgroup' ).each( function () {
							if ( jQuery( this ).attr( 'value' ) === $context ) {
								flag = false;

								jQuery( this ).append( '<option value="' + response + '">' + $albumname + '</option>' );

								return;
							}
						} );

						if ( flag ) {
							var label = $context.charAt( 0 ).toUpperCase() + $context.slice( 1 );
							var opt_html = '<optgroup value="' + $context + '" label="' + label + ' Albums"><option value="' + response + '">' + $albumname + '</option></optgroup>';

							jQuery( this ).append( opt_html );
						}
					} );

					jQuery( 'select.rtmedia-user-album-list option[value="' + response + '"]' ).prop( 'selected', true );
					jQuery( '.rtmedia-create-new-album-container' ).slideToggle();
					jQuery( '#rtmedia_album_name' ).val( "" );
					jQuery( "#rtmedia-create-album-modal" ).append( "<div class='rtmedia-success rtmedia-create-album-alert'><b>" + $albumname + "</b>" + rtmedia_album_created_msg + "</div>" );

					setTimeout( function () {
						jQuery( ".rtmedia-create-album-alert" ).remove();
					}, 4000 );

					setTimeout( function () {
						galleryObj.reloadView();
						jQuery( ".close-reveal-modal" ).click();
					}, 2000 );
				} else {
					alert( rtmedia_something_wrong_msg );
				}

				$( "#rtmedia_create_new_album" ).removeAttr( 'disabled' );
				$( "#rtmedia_create_new_album" ).html( old_val );
			} );
		} else {
			alert( rtmedia_empty_album_name_msg );
		}
	} );

	jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-delete-selected', function ( e ) {
		if ( jQuery( '.rtmedia-list :checkbox:checked' ).length > 0 ) {
			if ( confirm( rtmedia_selected_media_delete_confirmation ) ) {
				jQuery( this ).closest( 'form' ).attr( 'action', '../../../media/delete' ).submit();
			}
		} else {
			alert( rtmedia_no_media_selected );
		}
	} );

	jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-move-selected', function ( e ) {
		if ( jQuery( '.rtmedia-list :checkbox:checked' ).length > 0 ) {
			if ( confirm( rtmedia_selected_media_move_confirmation ) ) {
				jQuery( this ).closest( 'form' ).attr( 'action', '' ).submit();
			}
		} else {
			alert( rtmedia_no_media_selected );
		}

	} );

	function rtmedia_media_view_counts() {
		//var view_count_action = jQuery('#rtmedia-media-view-form').attr("action");
		if ( jQuery( '#rtmedia-media-view-form' ).length > 0 ) {
			var url = jQuery( '#rtmedia-media-view-form' ).attr( "action" );
			jQuery.post( url,
					{
					}, function ( data ) {

			} );
		}
	}

	rtmedia_media_view_counts();
	rtMediaHook.register( 'rtmedia_js_popup_after_content_added',
			function () {
				rtmedia_media_view_counts();
				rtmedia_init_media_deleting();
                mfp = jQuery.magnificPopup.instance;
                if ( jQuery(mfp.items).size() > 1 ) {
                    rtmedia_init_popup_navigation();
                }
				
				rtmedia_disable_popup_navigation_comment_focus();
				var height = $( window ).height();
				jQuery( '.rtm-lightbox-container .mejs-video' ).css( { 'height': height * 0.8, 'max-height': height * 0.8, 'over-flow': 'hidden' } );
				jQuery( '.mfp-content .rtmedia-media' ).css( { 'max-height': height * 0.87, 'over-flow': 'hidden' } );
				//mejs-video
				//init the options dropdown menu
				rtmedia_init_action_dropdown();
				//get focus on comment textarea when comment-link is clicked
				jQuery( '.rtmedia-comment-link' ).on( 'click', function ( e ) {
					e.preventDefault();
					jQuery( '#comment_content' ).focus();
				} );

				jQuery( ".rtm-more" ).shorten( { // shorten the media description to 100 characters
					"showChars": 130
				} );

				//show gallery title in lightbox at bottom
				var gal_title = $( '.rtm-gallery-title' ), title = "";
				if ( ! $.isEmptyObject( gal_title ) ) {
					title = gal_title.html();
				} else {
					title = $( '#subnav.item-list-tabs li.selected ' ).html();
				}
				if ( title != "" ) {
					$( '.rtm-ltb-gallery-title .ltb-title' ).html( title );
				}

				//show image counts
				var counts = $( '#subnav.item-list-tabs li.selected span' ).html();
				$( 'li.total' ).html( counts );

				return true;
			}
	);

	function rtmedia_init_popup_navigation() {
		var rtm_mfp = jQuery.magnificPopup.instance;
		jQuery( '.mfp-arrow-right' ).on( 'click', function ( e ) {
			rtm_mfp.next();
		} );
		jQuery( '.mfp-arrow-left' ).on( 'click', function ( e ) {
			rtm_mfp.prev();
		} );

		jQuery( '.mfp-content .rtmedia-media' ).swipe( {
			//Generic swipe handler for all directions
			swipeLeft: function ( event, direction, distance, duration, fingerCount ) 	// bind leftswipe
			{
				rtm_mfp.next();
			},
			swipeRight: function ( event, direction, distance, duration, fingerCount ) 	// bind rightswipe
			{
				rtm_mfp.prev();
			},
			threshold: 0
		} );
	}

	function rtmedia_disable_popup_navigation_comment_focus() {
		jQuery( document ).on( 'focusin', '#comment_content', function () {
			jQuery( document ).unbind( 'keydown' );
		} );
		jQuery( document ).on( 'focusout', '#comment_content', function () {
			var rtm_mfp = jQuery.magnificPopup.instance;
			jQuery( document ).on( 'keydown', function ( e ) {
				if ( e.keyCode === 37 ) {
					rtm_mfp.prev();
				} else if ( e.keyCode === 39 ) {
					rtm_mfp.next();
				}
			} );
		} );
	}

	var dragArea = jQuery( "#drag-drop-area" );
	var activityArea = jQuery( '#whats-new' );
	var content = dragArea.html();
	jQuery( '#rtmedia-upload-container' ).after( "<div id='rtm-drop-files-title'>" + rtmedia_drop_media_msg + "</div>" );
	if ( typeof rtmedia_bp_enable_activity != "undefined" && rtmedia_bp_enable_activity == "1" ) {
		jQuery( '#whats-new-textarea' ).append( "<div id='rtm-drop-files-title'>" + rtmedia_drop_media_msg + "</div>" );
	}
	jQuery( document )
			.on( 'dragover', function ( e ) {
				jQuery( '#rtm-media-gallery-uploader' ).show();
				if ( typeof rtmedia_bp_enable_activity != "undefined" && rtmedia_bp_enable_activity == "1" ) {
					activityArea.addClass( 'rtm-drag-drop-active' );
				}

//            activityArea.css('height','150px');
				dragArea.addClass( 'rtm-drag-drop-active' );
				jQuery( '#rtm-drop-files-title' ).show();
			} )
			.on( "dragleave", function ( e ) {
				e.preventDefault();
				if ( typeof rtmedia_bp_enable_activity != "undefined" && rtmedia_bp_enable_activity == "1" ) {
					activityArea.removeClass( 'rtm-drag-drop-active' );
					activityArea.removeAttr( 'style' );
				}
				dragArea.removeClass( 'rtm-drag-drop-active' );
				jQuery( '#rtm-drop-files-title' ).hide();

			} )
			.on( "drop", function ( e ) {
				e.preventDefault();
				if ( typeof rtmedia_bp_enable_activity != "undefined" && rtmedia_bp_enable_activity == "1" ) {
					activityArea.removeClass( 'rtm-drag-drop-active' );
					activityArea.removeAttr( 'style' );
				}
				dragArea.removeClass( 'rtm-drag-drop-active' );
				jQuery( '#rtm-drop-files-title' ).hide();
			} );


	function rtmedia_init_media_deleting() {
		jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-delete-media', function ( e ) {
			e.preventDefault();
			if ( confirm( rtmedia_media_delete_confirmation ) ) {
				jQuery( this ).closest( 'form' ).submit();
			}
		} );
	}

	jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-delete-album', function ( e ) {
		e.preventDefault();
		if ( confirm( rtmedia_album_delete_confirmation ) ) {
			jQuery( this ).closest( 'form' ).submit();
		}
	} );

	jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-delete-media', function ( e ) {
		e.preventDefault();
		if ( confirm( rtmedia_media_delete_confirmation ) ) {
			jQuery( this ).closest( 'form' ).submit();
		}
	} );

	rtmedia_init_action_dropdown();

	$( document ).click( function () {
		if ( $( '.click-nav ul' ).is( ':visible' ) ) {
			$( '.click-nav ul', this ).hide();
		}
	} );

	//get focus on comment textarea when comment-link is clicked
	jQuery( '.rtmedia-comment-link' ).on( 'click', function ( e ) {
		e.preventDefault();
		jQuery( '#comment_content' ).focus();
	} );

	if ( jQuery( '.rtm-more' ).length > 0 ) {
		$( ".rtm-more" ).shorten( { // shorten the media description to 100 characters
			"showChars": 200
		} );
	}

//    masonry code
	if ( typeof rtmedia_masonry_layout != "undefined" && rtmedia_masonry_layout == "true" && jQuery( '.rtmedia-container .rtmedia-list.rtm-no-masonry' ).length == 0 ) {
		rtm_masonry_container = jQuery( '.rtmedia-container .rtmedia-list' )
		rtm_masonry_container.masonry( {
			itemSelector: '.rtmedia-list-item'
		} );
		setInterval( function () {
			jQuery.each( jQuery( '.rtmedia-list.masonry .rtmedia-item-title' ), function ( i, item ) {
				jQuery( item ).width( jQuery( item ).siblings( '.rtmedia-item-thumbnail' ).children( 'img' ).width() );
			} );
			rtm_masonry_reload( rtm_masonry_container );
		}, 1000 );
		jQuery.each( jQuery( '.rtmedia-list.masonry .rtmedia-item-title' ), function ( i, item ) {
			jQuery( item ).width( jQuery( item ).siblings( '.rtmedia-item-thumbnail' ).children( 'img' ).width() );
		} );
	}

    if( jQuery( '.rtm-uploader-tabs' ).length > 0 ){
        jQuery( '.rtm-uploader-tabs li' ).click( function( e ){
            if( ! jQuery( this ).hasClass( 'active' ) ){
                jQuery( this ).siblings().removeClass( 'active' );
                jQuery( this ).parents( '.rtm-uploader-tabs' ).siblings().hide();
                class_name = jQuery( this ).attr( 'class' );
	            jQuery( this ).parents( '.rtm-uploader-tabs' ).siblings('[data-id="' + class_name + '"]').show();
                jQuery( this ).addClass( 'active' );
            }
        });
    }

	// delete media from gallery page under the user's profile when user clicks the delete button on the gallery item.
	jQuery( '.rtmedia-list-media' ).on( 'click', '.rtm-delete-media', function ( e ) {
		e.preventDefault();
		var confirmation = 'Are you sure you want to delete this media?';

		if( typeof rtmedia_media_delete_confirmation != 'undefined' ){
			confirmation = rtmedia_media_delete_confirmation;
		}

		if ( confirm( confirmation ) ) { // if user confirms, send ajax request to delete the selected media
			var curr_li = jQuery( this ).closest( 'li' );
			var nonce = jQuery( '#rtmedia-upload-container #rtmedia_media_delete_nonce' ).val();

			var data = {
				action: 'delete_uploaded_media',
				nonce: nonce,
				media_id: curr_li.attr( 'id' )
			};

			jQuery.ajax( {
				url: ajaxurl,
				type: 'post',
				data: data,
				success: function ( data ) {
					if ( data == '1' ) {
						//media delete
						curr_li.remove();
						if ( typeof rtmedia_masonry_layout != "undefined" && rtmedia_masonry_layout == "true" && jQuery( '.rtmedia-container .rtmedia-list.rtm-no-masonry' ).length == 0 ) {
							rtm_masonry_reload( rtm_masonry_container );
						}
					} else { // show alert message
						alert( rtmedia_file_not_deleted );
					}
				}
			} );
		}
	} );
});



//Legacy media element for old activities
function bp_media_create_element( id ) {
	return false;
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
( function ( $ ) {
	$.fn.shorten = function ( settings ) {

		var config = {
			showChars: 100,
			ellipsesText: "...",
			moreText: "more",
			lessText: "less"
		};

		if ( settings ) {
			$.extend( config, settings );
		}

		$( document ).off( "click", '.morelink' );

		$( document ).on( { click: function () {

				var $this = $( this );
				if ( $this.hasClass( 'less' ) ) {
					$this.removeClass( 'less' );
					$this.html( config.moreText );
				} else {
					$this.addClass( 'less' );
					$this.html( config.lessText );
				}
				$this.parent().prev().toggle();
				$this.prev().toggle();
				return false;
			}
		}, '.morelink' );

		return this.each( function () {
			var $this = $( this );
			if ( $this.hasClass( "shortened" ) )
				return;

			$this.addClass( "shortened" );
			var content = $this.html();
			if ( content.length > config.showChars ) {
				var c = content.substr( 0, config.showChars );
				var h = content.substr( config.showChars, content.length - config.showChars );
				var html = c + '<span class="moreellipses">' + config.ellipsesText + ' </span><span class="morecontent"><span>' + h + '</span> <a href="#" class="morelink">' + config.moreText + '</a></span>';
				$this.html( html );
				$( ".morecontent span" ).hide();
			}
		} );

	};

} )( jQuery );

function rtmedia_version_compare( left, right ) {
	if ( typeof left + typeof right != 'stringstring' )
		return false;
	var a = left.split( '.' )
			, b = right.split( '.' )
			, i = 0, len = Math.max( a.length, b.length );
	for ( ; i < len; i ++ ) {
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
	setTimeout( function () {
		// we make masonry recalculate the element based on their current state.
		el.masonry( 'reload' );
	}, 250 );
}

window.onload = function () {
	if ( typeof rtmedia_masonry_layout != "undefined" && rtmedia_masonry_layout == "true" && jQuery( '.rtmedia-container .rtmedia-list.rtm-no-masonry' ).length == 0 ) {
		rtm_masonry_reload( rtm_masonry_container );
	}
};

// Get query string parameters from url
function rtmediaGetParameterByName( name ) {
	name = name.replace( /[\[]/, "\\\[" ).replace( /[\]]/, "\\\]" );
	var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
			results = regex.exec( location.search );
	return results == null ? "" : decodeURIComponent( results[1].replace( /\+/g, " " ) );
}
