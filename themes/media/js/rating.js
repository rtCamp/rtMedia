/*
 ### jQuery Star Rating Plugin v3.12 - 2009-04-16 ###
 * Home: http://www.fyneworks.com/jquery/star-rating/
 * Code: http://code.google.com/p/jquery-star-rating-plugin/
 *
	* Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 ###
*/

/*# AVOID COLLISIONS #*/
;if(window.jQuery) (function(jQuery){
/*# AVOID COLLISIONS #*/
	
	// IE6 Background Image Fix
	if (jQuery.browser.msie) try { document.execCommand("BackgroundImageCache", false, true)} catch(e) { };
	// Thanks to http://www.visualjquery.com/rating/rating_redux.html
	
	// plugin initialization
	jQuery.fn.rating = function(options){
		if(this.length==0) return this; // quick fail
		
		// Handle API methods
		if(typeof arguments[0]=='string'){
			// Perform API methods on individual elements
			if(this.length>1){
				var args = arguments;
				return this.each(function(){
					jQuery.fn.rating.apply(jQuery(this), args);
    });
			};
			// Invoke API method handler
			jQuery.fn.rating[arguments[0]].apply(this, jQuery.makeArray(arguments).slice(1) || []);
			// Quick exit...
			return this;
		};
		
		// Initialize options for this call
		var options = jQuery.extend(
			{}/* new object */,
			jQuery.fn.rating.options/* default options */,
			options || {} /* just-in-time options */
		);
		
		// Allow multiple controls with the same name by making each call unique
		jQuery.fn.rating.calls++;
		
		// loop through each matched element
		this
		 .not('.star-rating-applied')
			.addClass('star-rating-applied')
		.each(function(){
			
			// Load control parameters / find context / etc
			var control, input = jQuery(this);
			var eid = (this.name || 'unnamed-rating').replace(/\[|\]/g, '_').replace(/^\_+|\_+jQuery/g,'');
			var context = jQuery(this.form || document.body);
			
			// FIX: http://code.google.com/p/jquery-star-rating-plugin/issues/detail?id=23
			var raters = context.data('rating');
			if(!raters || raters.call!=jQuery.fn.rating.calls) raters = { count:0, call:jQuery.fn.rating.calls };
			var rater = raters[eid];
			
			// if rater is available, verify that the control still exists
			if(rater) control = rater.data('rating');
			
			if(rater && control)//{// save a byte!
				// add star to control if rater is available and the same control still exists
				control.count++;
				
			//}// save a byte!
			else{
				// create new control if first star or control element was removed/replaced
				
				// Initialize options for this raters
				control = jQuery.extend(
					{}/* new object */,
					options || {} /* current call options */,
					(jQuery.metadata? input.metadata(): (jQuery.meta?input.data():null)) || {}, /* metadata options */
					{ count:0, stars: [], inputs: [] }
				);
				
				// increment number of rating controls
				control.serial = raters.count++;
				
				// create rating element
				rater = jQuery('<span class="star-rating-control"/>');
				input.before(rater);
				
				// Mark element for initialization (once all stars are ready)
				rater.addClass('rating-to-be-drawn');
				
				// Accept readOnly setting from 'disabled' property
				if(input.attr('disabled')) control.readOnly = true;
				
				// Create 'cancel' button
				rater.append(
					control.cancel = jQuery('<div class="rating-cancel"><a title="' + control.cancel + '">' + control.cancelValue + '</a></div>')
					.mouseover(function(){
						jQuery(this).rating('drain');
						jQuery(this).addClass('star-rating-hover');
						//jQuery(this).rating('focus');
					})
					.mouseout(function(){
						jQuery(this).rating('draw');
						jQuery(this).removeClass('star-rating-hover');
						//jQuery(this).rating('blur');
					})
					.click(function(){
					 jQuery(this).rating('select');
					})
					.data('rating', control)
				);
				
			}; // first element of group
			
			// insert rating star
			var star = jQuery('<div class="star-rating rater-'+ control.serial +'"><a title="' + (this.title || this.value) + '">' + this.value + '</a></div>');
			rater.append(star);
			
			// inherit attributes from input element
			if(this.id) star.attr('id', this.id);
			if(this.className) star.addClass(this.className);
			
			// Half-stars?
			if(control.half) control.split = 2;
			
			// Prepare division control
			if(typeof control.split=='number' && control.split>0){
				var stw = (jQuery.fn.width ? star.width() : 0) || control.starWidth;
				var spi = (control.count % control.split), spw = Math.floor(stw/control.split);
				star
				// restrict star's width and hide overflow (already in CSS)
				.width(spw)
				// move the star left by using a negative margin
				// this is work-around to IE's stupid box model (position:relative doesn't work)
				.find('a').css({ 'margin-left':'-'+ (spi*spw) +'px' })
			};
			
			// readOnly?
			if(control.readOnly)//{ //save a byte!
				// Mark star as readOnly so user can customize display
				star.addClass('star-rating-readonly');
			//}  //save a byte!
			else//{ //save a byte!
			 // Enable hover css effects
				star.addClass('star-rating-live')
				 // Attach mouse events
					.mouseover(function(){
						jQuery(this).rating('fill');
						jQuery(this).rating('focus');
					})
					.mouseout(function(){
						jQuery(this).rating('draw');
						jQuery(this).rating('blur');
					})
					.click(function(){
						jQuery(this).rating('select');
					})
				;
			//}; //save a byte!
			
			// set current selection
			if(this.checked)	control.current = star;
			
			// hide input element
			input.hide();
			
			// backward compatibility, form element to plugin
			input.change(function(){
    jQuery(this).rating('select');
   });
			
			// attach reference to star to input element and vice-versa
			star.data('rating.input', input.data('rating.star', star));
			
			// store control information in form (or body when form not available)
			control.stars[control.stars.length] = star[0];
			control.inputs[control.inputs.length] = input[0];
			control.rater = raters[eid] = rater;
			control.context = context;
			
			input.data('rating', control);
			rater.data('rating', control);
			star.data('rating', control);
			context.data('rating', raters);
  }); // each element
		
		// Initialize ratings (first draw)
		jQuery('.rating-to-be-drawn').rating('draw').removeClass('rating-to-be-drawn');
		
		return this; // don't break the chain...
	};
	
	/*--------------------------------------------------------*/
	
	/*
		### Core functionality and API ###
	*/
	jQuery.extend(jQuery.fn.rating, {
		// Used to append a unique serial number to internal control ID
		// each time the plugin is invoked so same name controls can co-exist
		calls: 0,
		
		focus: function(){
			var control = this.data('rating'); if(!control) return this;
			if(!control.focus) return this; // quick fail if not required
			// find data for event
			var input = jQuery(this).data('rating.input') || jQuery( this.tagName=='INPUT' ? this : null );
   // focus handler, as requested by focusdigital.co.uk
			if(control.focus) control.focus.apply(input[0], [input.val(), jQuery('a', input.data('rating.star'))[0]]);
		}, // jQuery.fn.rating.focus
		
		blur: function(){
			var control = this.data('rating'); if(!control) return this;
			if(!control.blur) return this; // quick fail if not required
			// find data for event
			var input = jQuery(this).data('rating.input') || jQuery( this.tagName=='INPUT' ? this : null );
   // blur handler, as requested by focusdigital.co.uk
			if(control.blur) control.blur.apply(input[0], [input.val(), jQuery('a', input.data('rating.star'))[0]]);
		}, // jQuery.fn.rating.blur
		
		fill: function(){ // fill to the current mouse position.
			var control = this.data('rating'); if(!control) return this;
			// do not execute when control is in read-only mode
			if(control.readOnly) return;
			// Reset all stars and highlight them up to this element
			this.rating('drain');
			this.prevAll().andSelf().filter('.rater-'+ control.serial).addClass('star-rating-hover');
		},// jQuery.fn.rating.fill
		
		drain: function() { // drain all the stars.
			var control = this.data('rating'); if(!control) return this;
			// do not execute when control is in read-only mode
			if(control.readOnly) return;
			// Reset all stars
			control.rater.children().filter('.rater-'+ control.serial).removeClass('star-rating-on').removeClass('star-rating-hover');
		},// jQuery.fn.rating.drain
		
		draw: function(){ // set value and stars to reflect current selection
			var control = this.data('rating'); if(!control) return this;
			// Clear all stars
			this.rating('drain');
			// Set control value
			if(control.current){
				control.current.data('rating.input').attr('checked','checked');
				control.current.prevAll().andSelf().filter('.rater-'+ control.serial).addClass('star-rating-on');
			}
			else
			 jQuery(control.inputs).removeAttr('checked');
			// Show/hide 'cancel' button
			control.cancel[control.readOnly || control.required?'hide':'show']();
			// Add/remove read-only classes to remove hand pointer
			this.siblings()[control.readOnly?'addClass':'removeClass']('star-rating-readonly');
		},// jQuery.fn.rating.draw
		
		select: function(value){ // select a value
			var control = this.data('rating'); if(!control) return this;
			// do not execute when control is in read-only mode
			if(control.readOnly) return;
			// clear selection
			control.current = null;
			// programmatically (based on user input)
			if(typeof value!='undefined'){
			 // select by index (0 based)
				if(typeof value=='number')
 			 return jQuery(control.stars[value]).rating('select');
				// select by literal value (must be passed as a string
				if(typeof value=='string')
					//return 
					jQuery.each(control.stars, function(){
						if(jQuery(this).data('rating.input').val()==value) jQuery(this).rating('select');
					});
			}
			else
				control.current = this[0].tagName=='INPUT' ? 
				 this.data('rating.star') : 
					(this.is('.rater-'+ control.serial) ? this : null);
			
			// Update rating control state
			this.data('rating', control);
			// Update display
			this.rating('draw');
			// find data for event
			var input = jQuery( control.current ? control.current.data('rating.input') : null );
			// click callback, as requested here: http://plugins.jquery.com/node/1655
			if(control.callback) control.callback.apply(input[0], [input.val(), jQuery('a', control.current)[0]]);// callback event
		},// jQuery.fn.rating.select
		
		readOnly: function(toggle, disable){ // make the control read-only (still submits value)
			var control = this.data('rating'); if(!control) return this;
			// setread-only status
			control.readOnly = toggle || toggle==undefined ? true : false;
			// enable/disable control value submission
			if(disable) jQuery(control.inputs).attr("disabled", "disabled");
			else     			jQuery(control.inputs).removeAttr("disabled");
			// Update rating control state
			this.data('rating', control);
			// Update display
			this.rating('draw');
		},// jQuery.fn.rating.readOnly
		
		disable: function(){ // make read-only and never submit value
			this.rating('readOnly', true, true);
		},// jQuery.fn.rating.disable
		
		enable: function(){ // make read/write and submit value
			this.rating('readOnly', false, false);
		}// jQuery.fn.rating.select
		
 });
	
	/*--------------------------------------------------------*/
	
	/*
		### Default Settings ###
		eg.: You can override default control like this:
		jQuery.fn.rating.options.cancel = 'Clear';
	*/
	jQuery.fn.rating.options = { //jQuery.extend(jQuery.fn.rating, { options: {
			cancel: 'Cancel Rating',   // advisory title for the 'cancel' link
			cancelValue: '',           // value to submit when user click the 'cancel' link
			split: 0,                  // split the star into how many parts?
			
			// Width of star image in case the plugin can't work it out. This can happen if
			// the jQuery.dimensions plugin is not available OR the image is hidden at installation
			starWidth: 16//,
			
			//NB.: These don't need to be pre-defined (can be undefined/null) so let's save some code!
			//half:     false,         // just a shortcut to control.split = 2
			//required: false,         // disables the 'cancel' button so user can only select one of the specified values
			//readOnly: false,         // disable rating plugin interaction/ values cannot be changed
			//focus:    function(){},  // executed when stars are focused
			//blur:     function(){},  // executed when stars are focused
			//callback: function(){},  // executed when a star is clicked
 }; //} });
	
	/*--------------------------------------------------------*/
	
	/*
		### Default implementation ###
		The plugin will attach itself to file inputs
		with the class 'multi' when the page loads
	*/
	jQuery(function(){
	 jQuery('input[type=radio].star').rating();
	});
	
	
	
/*# AVOID COLLISIONS #*/
})(jQuery);
/*# AVOID COLLISIONS #*/
