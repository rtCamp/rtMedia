
// ---
// PhotoTagger plugin written by Ben Nadel.
// Copyright www.bennadel.com 2010.
// ---

// Create a self-executing function that will warp the window
// object and translate the jQuery function name.
(function( window, jQuery ){

	// Define the controller class that's going to be doing 
	// the heavy lifitng in the photo tagging system. Each 
	// photo that is getting tagged will be associated with
	// its own instance.
	function PhotoTagger( container, settings ){
		// Create a local reference for closure methods.
		var self = this;
	
		// I am the container object that holds the photo and
		// any tags added to the photo.
		this.container = container;
		
		// I am the settings object used to get AJAX and photo
		// ID information.
		this.settings = settings;
		
		// I am the image object (within in the container).
		this.image = this.container.children( "img" );
                
		
		// I am the message element that will be used to display
		// the note associated with the tags.
		this.message = jQuery( "<div class='" + this.getFullClassName( "message" ) + "'></div>" );
		
		// I am the collection of tags associated with this photo.
		// I am using jQuery to contain the collection so that we
		// can easily filter the collection based on CSS.
		this.tags = jQuery( [] );
		
		// I am the flag to determine if tag creation is enabled.
		this.isTagCreationEnabled = this.settings.isTagCreationEnabled;
		
		// I am the flag to determine if tag deletion is enabled.
		this.isTagDeletionEnabled = this.settings.isTagDeletionEnabled;
		
		// I am the pending tag - I am the one currently being 
		// drawn by the user.
		this.pendingTag = null;
		
		// Check to make sure the container has a position that 
		// will allow us to absolutely position the other elements
		// inside of it.
		if (
			(this.container.css( "position" ) != "relative") &&
			(this.container.css( "position" ) != "absolute") &&
			(this.container.css( "position" ) != "fixed")
			){
			
			// Make this contianer relative.
			//this.container.css( "position", "relative" );
                        //this.container.css( "height", "none !important" );
                        //this.container.css( "width", "none !important" );
                        alert('hello');
			
		}
				
		// Resize the container to be the dimensions of the 
		// image so that we don't have any mouse confusion.
		this.container.width( this.image.width() );
		this.container.height( this.image.height() ); 
                console.log(this.container.children( "img" ).attr('height') );
                console.log('hah'+ this.container.children( "img" ).attr('width')  );
		
		// Hide the message object and add the message to the 
		// contianer.
		this.message
			.hide()
			.appendTo( this.container )
		;
		
		// Strip out the ALT and TITLE tags to prevent mouse over
		// displays in the browser.
		this.container.removeAttr( "title" );
		this.image
			.removeAttr( "title" )
			.removeAttr( "alt" )
		;
		
		// I am a flag to determine if the current container is 
		// active - menaing, is the user moused over it.
		this.isActiveContainer = false;
		
		// I am a flag to determine if the tags need to be loaded
		// from the server.
		this.isLoadingRequired = true;
		
		// Check to see if the user wants to delay the loading
		// of existing tags.
		if (!this.settings.isDelayedLoad){
		
			// Flag that the tags don't need to be loaded.
			this.isLoadingRequired = false;
		
			// Load existing tags immediately.
			this.loadTags();
		
		}
		
		
		// -------------------------------------------------- //
		// -------------------------------------------------- //
		
		
		// Bind to the hover event on the container. When the user
		// hovers over the container, we want to show the tags
		// associated with this photo.
		this.container.hover(
			function( event ){
				// Activate the container.
				self.activateContainer();
			},
			function( event ){
				// If the user is currently drawing a tag, then we
				// don't want to deactivate the container.
				if (!self.isUserCreatingTag()){
					
					// There is no pending activity on the 
					// container, so deactivate it.
					self.deactivateContainer();
					
				}
			}
		);	
		
		
		// Bind to the mouse down even on the container. If the 
		// user clicks down, they *probably* want to start drawing
		// a new tag hotspot.
		this.container.mousedown(
			function( event ){
				// Get the target of the click (if it is an 
				// existing tag, we have some more logic).
				var target = jQuery( event.target );
								
				// Check to see if new tag creation is enabled. 
				// We only want to let the user draw new tags in
				// this state such that new tags are not randomly
				// being created. Also, we don't want the user to
				// start a tag ON an existing tag (this helps us
				// with the delete dble-click).
				if (
					self.isTagCreationEnabled &&
					!target.is( "a." + self.getFullClassName( "tag" ) )
					){
				
					// The user is going to start drawing. Cancel 
					// the default event to make sure the browser 
					// does not try to select the IMG object.
					event.preventDefault();
					
					// Set up the container for manual tag 
					// creation (by the user).
					self.setupPendingTagCreation(
						event.clientX, 
						event.clientY 
					);
						
				}
			}
		);
		
		
		// Bind to the dragstart event (Internet Explorer) to
		// prevent the image from being dragged. This will allow
		// the box to be draw without interuption.
		this.container.bind(
			"dragstart selectstart",
			function( event ){
				// Prevent event.
				return( false );
			}
		);
		
	}
	
	// ------------------------------------------------------ //
	// ------------------------------------------------------ //
	
	PhotoTagger.prototype = {
		
		// I activate the container.
		activateContainer: function(){
			// Flag that the container is active.
			this.isActiveContainer = true;
			
			// Check to see if the tags need to be loaded.
			if (this.isLoadingRequired){
				
				// Since we are about to load, immediately flag 
				// that no more loading is need (so we don't 
				// fire the load more than once).
				this.isLoadingRequired = false;
		
				// Load existing tags immediately.
				this.loadTags();
			
			}
			
			// Show the tags.
			this.showTags();
		},
		
		
		// I add a tag to the 
		addTag: function( id, x, y, width, height, message ){
			var self = this;
			
			// Create the physical tag.
			var tag = this.createTag( x, y, width, height );
			
			// Associate the appropriate data with the tag.
			tag.data( "id", id );
			tag.data( "message", message );
			
			// Bind the mouse over event on this tag (will show 
			// the associated message).
			tag.bind(
				"mouseover.photoTagger",
				function(){
					// Check to see if the user is currently
					// creating a new tag. If so, we don't 
					// want to interfere with that experience.
					if (!self.isUserCreatingTag()){
													
						// Emphasize the given tag visually.
						self.emphasizeTag( tag );
					
					}
				}
			);
			
			// Bind the mouse out event on this tag.
			tag.bind(
				"mouseout.photoTagger",
				function(){
					// Check to see if the user is currently
					// creating a new tag. If so, we don't 
					// want to interfere with that experience.
					if (!self.isUserCreatingTag()){
													
						// Deemphasize the given tag visually.
						self.deemphasizeTag( tag );
					
					}
				}
			);
			
			// Bind the the double-click event to let the tag
			// be deleted.
			tag.bind(
				"dblclick.photoTagger",
				function(){
					// Check to see if tag deletion is enabled.
					if (!self.isTagDeletionEnabled){
						
						// Not tag deletion allowed at this time.
						return;
						
					}
				
					// Confirm that the user wants the tag to
					// be deleted.
					if (confirm( "Delete this tag?" )){
					
						// Delete the tag.
						self.deleteTag( tag );
						
					}					
				}			
			);
			
			// Add the tag to the internal collection.
			this.tags = this.tags.add( tag );
			
			// Add the tag to the container.
			this.container.append( tag );
			
			// Sine the tags start out hidden, let's check to see
			// if we have an active container. If so, let's show 
			// the tag.
			if (this.isActiveContainer){
				
				// Show the active container tag.
				tag.show();
				
			}
		
			// Return the new tag.
			return( tag );
		},
		
		
		// I add a pending tag at the given position and store it 
		// as the global pending tag.
		addPendingTag: function( left, top ){
			// Create the new tag.
			var tag = this.createTag( left, top );
			
			// Set the anchor points for the tag. This is the 
			// point from which the drawing will be made 
			// (regardless of technical position).
			tag.data({
				anchorLeft: left,
				anchorTop: top
			});
			
			// Set it as the pending tag.
			this.pendingTag = tag;
			
			// Add it to the container.
			this.container.append( tag );
			
			// Since tags start out hidden, show this one after 
			// it is added to the container.
			tag.show();
		
			// Return the new, pending tag.
			return( tag );
		},
		
		
		// I create a new tag object with the given position 
		// (but do not append it to the container object.
		createTag: function( left, top, width, height ){
			// Create the new tag.
			var tag = jQuery( "<a class='" + this.getFullClassName( "tag" ) + "'><br /></a>" );
			
			// Set the absolute positon (within the container).
			// By default, the tag will start out hidden.
			tag.css({
				left: (left + "px"),
				top: (top + "px"),
				width: ((width || 1) + "px"),
				height: ((height || 1) + "px"),
				display: "none"
			});
		
			// Return the new tag object.
			return( tag );
		},
		
		
		// I deactivate the container.
		deactivateContainer: function(){
			// Flag that the container is no longer active.
			this.isActiveContainer = false;
				
			// Hide the tags.
			this.hideTags();
		},
		
		
		// I de-emphasize the given tag (bringing it back to the 
		// default state with the other tags).
		deemphasizeTag: function( tag ){
			// Make sure to deselected tag.
			tag.removeClass( 
				this.getFullClassName( "selected-tag" )
			);
			
			// Hide the message (associated with current tag).
			this.message.hide();
			
			// Show all the tags at their normal state.
			this.tags.css( "opacity", 1 );
		},
		
		
		// I delete the given tag.
		deleteTag: function( tag ){
			// Delete the tag record.
			this.deleteTagRecord( tag.data( "id" ) );
			
			// Remove the elements from the collection.
			this.tags = this.tags.not( tag );
			
			// Remove the tag from the container.
			tag.remove();		
		},
		
		
		// I delete the tag with the given ID.
		deleteTagRecord: function( id, onSuccess ){
			var self = this;
			
			// Delete the record using the API.
			jQuery.ajax({
				method: this.settings.deleteMethod,
				url: this.settings.deleteURL,
				data: {
					id: id
				},
				dataType: "json",
				cache: false,
				success: function( response ){
					// Pass off to handler (if it exists).
					if (onSuccess){
						onSuccess(
							// Clean the response before handing
							// it off to the handler.
							self.settings.cleanAJAXResponse(
								"delete",
								response 
							)
						);
					}
				},
				error: function(){
					alert( self.settings.ajaxFailMessage );
				}
			});
		},
		
		
		// I turn off new tag creation.
		disableTagCreation: function(){
			this.isTagCreationEnabled = false;
		},
		
		
		// I turn off existing tag deletion.
		disableTagDeletion: function(){
			this.isTagDeletionEnabled = false;
		},
		
		
		// I emphasize the given tag and show it's message.
		emphasizeTag: function( tag ){
			// Get the current position of the tag.
			var tagPosition = tag.position();
			
			// Set the tag message.
			this.message.text( tag.data( "message" ) );
			
			// Position and show the message.
			this.message
				.css({
					left: (tagPosition.left + "px"),
					top: ((tagPosition.top + tag.outerHeight()) + "px")
				})
				.show()
			;
			
			// Make this the selected tag.
			tag.addClass( 
				this.getFullClassName( "selected-tag" )
			);
			
			// Dim all the tags' opacity to visually bring
			// out the currently selected tag.
			this.tags.css( "opacity", this.settings.minOpacity );
			
			// Visually pop the current tag.
			tag.css( "opacity", 1 );
		},
		
		
		// I turn on new tag creation.
		enableTagCreation: function(){
			this.isTagCreationEnabled = true;
		},
		
		
		// I turn on existing tag deletion.
		enableTagDeletion: function(){
			this.isTagDeletionEnabled = true;
		},
		
		
		// I get the full CSS class name based on the given 
		// convenience name.
		getFullClassName: function( className ){
			// Prepend the CSS namespace.
			return( this.settings.cssNameSpace + className );
		},
		
		
		// I get the contianer-local top / left coordiantes 
		// of the current mouse position based on the given page-
		// level X,Y coordinates.
		getLocalPosition: function( mouseX, mouseY ){
			// Get the current position of the container.
			var containerOffset = this.container.offset();
		
			// Adjust the client coordiates to acocunt for 
			// the offset of the page and the position of the 
			// container.
			var localPosition = {
				left: Math.floor( 
					mouseX - containerOffset.left + window.scrollLeft() 
				),
				top: Math.floor( 
					mouseY - containerOffset.top + window.scrollTop() 
				)
			};
			
			// Return the local position of the mouse.
			return( localPosition );
		},
		
		
		// I hide the tags associated with this photo.
		hideTags: function(){
			this.tags.hide();
		},
		
		
		// I check to see if the given tag size is valid (tags
		// that entirely eclipse another tag are not valid).
		isPendingTagSizeValid: function(){
			// Get the pending tag dimensions.
			var pendingWidth = this.pendingTag.width();
			var pendingHeight = this.pendingTag.height();
			var pendingLeft = this.pendingTag.position().left;
			var pendingTop = this.pendingTag.position().top;
		
			// Loop over the existing tags to see if any of them
			// are being eclipsed by the pending tag size.
			for (var i = 0 ; i < this.tags.size() ; i++){
			
				// Get the current tag.
				var tag = this.tags.eq( i );	
				
				// Get the current tag position.
				var position = tag.position();
				
				// Check to see if the position is too small.
				if (
					(position.top >= pendingTop) &&
					((position.top + tag.height()) <= (pendingTop + pendingHeight)) &&
					(position.left >= pendingLeft) &&
					((position.left + tag.width()) <= (pendingLeft + pendingWidth))					
					){
					
					// Tag is eclipsed, return false.
					return( false );
					
				}
			
			}
			
			// If we made it this far, the tag is valid.
			return( true );		
		},
		
		
		// I determine if the user is currently drawing a 
		// pending tag.
		isUserCreatingTag: function(){
			// If there is a pending tag, return true.
			return( !!this.pendingTag );
		},
		
		
		// I load the tag records from the server.
		loadTagRecords: function( onSuccess ){
			var self = this;
			
			// Load the tag records.
			jQuery.ajax({
				method: "get",
				url: this.settings.loadURL,
				data: {
					photoID: this.settings.getPhotoID( this.container )
				},
				dataType: "json",
				cache: false,
				success: function( response ){
					// Pass off to handler (if it exists).
					if (onSuccess){
						onSuccess(
							// Clean the response before handing
							// it off to the handler.
							self.settings.cleanAJAXResponse(
								"load",
								response 
							)
						);
					}
				},
				error: function(){
					alert( self.settings.ajaxFailMessage );
				}
			});			
		},
		
		
		// I load the tags from the server and translate them into
		// tags in the photo container.
		loadTags: function(){
			var self = this;
			
			// Load the tag records.
			this.loadTagRecords(
				function( response ){
					
					// Loop over the response data to create a
					// tag for each record.
					jQuery.each(
						response,
						function( index, tagData ){
			
							// Add the tag.
							self.addTag(
								tagData.id,
								tagData.x,
								tagData.y,
								tagData.width,
								tagData.height,
								tagData.message
							);
						
						}
					
					);
										
				}
			);
		},
		
		
		// I resize the pending tag based on the given mouse 
		// position.
		resizePendingTag: function( mouseX, mouseY ){
			// Get the local position of the mouse.
			var localPosition = this.getLocalPosition( 
				mouseX, 
				mouseY 
			);
			
			// Get the current anchor position of the tag.
			var anchorLeft = this.pendingTag.data( "anchorLeft" );
			var anchorTop = this.pendingTag.data( "anchorTop" );
	
			// Get the height and width of the pending tag based
			// on its current position plus the position of the 
			// mouse.We're going to allow bi-directional drawing.
			var width = Math.abs(
				(localPosition.left - anchorLeft)	
			);
			
			var height = Math.abs(
				(localPosition.top - anchorTop)
			);
			
			// Set the dimensions of the tag. When doing this, 
			// make sure the tag dimensions are never smaller 
			// than 1x1.
			this.pendingTag.width( Math.max( width, 1 ) );
			this.pendingTag.height( Math.max( height, 1 ) );
			
			// Check to see if the mouse position is greater 
			// than the original anchor position, the move the 
			// tag (this will give us the bi-directional re-size
			// illusion).
			
			// Check for left translation.
			if (localPosition.left < anchorLeft){
				
				// Move left.
				this.pendingTag.css( 
					"left", 
					(localPosition.left + "px") 
				);
				
			}
			
			// Check for top translation.
			if (localPosition.top < anchorTop){
				
				// Move up.
				this.pendingTag.css( 
					"top", 
					(localPosition.top + "px") 
				);
				
			}
		},
		
		
		// I save the given tag.
		saveTag: function( tag ){
			var self = this;
			
			// Get the tag position.
			var position = tag.position();
			
			// Save the tag record.
			this.saveTagRecord(
				tag.data( "id" ),
				position.left,
				position.top,
				tag.width(),
				tag.height(),
				tag.data( "message" ),
				this.settings.getPhotoID( this.container ),
				
				// If the AJAX response comes back successfully,
				// associate the given ID.
				function( id ){
					tag.data( "id", id );
				}
			);
		},
		
		
		// I save the given tag record.
		saveTagRecord: function( id, x, y, width, height, message, photoID, onSuccess ){
			var self = this;
			
			// Delete the record using the API.
			jQuery.ajax({
				method: this.settings.saveMethod,
				url: this.settings.saveURL,
				data: {
					id: id,
					x: x,
					y: y,
					width: width,
					height: height,
					message: message,
					photoID: photoID
				},
				dataType: "json",
				cache: false,
				success: function( response ){
					// Pass off to handler (if it exists).
					if (onSuccess){
						onSuccess( 
							// Clean the response before handing
							// it off to the handler.
							self.settings.cleanAJAXResponse(
								"save",
								response 
							)
						);
					}
				},
				error: function(){
					alert( self.settings.ajaxFailMessage );
				}
			});
		},
		
		
		// I set up the tag creation state when the user 
		// initiates a tag clicking with the given coordiantes.
		setupPendingTagCreation: function( clickX, clickY ){
			var self = this;
			
			// Get the local position of the mouse coordiantes
			// that the user has clicked to anchor the tag.
			var localPosition = this.getLocalPosition( 
				clickX, 
				clickY 
			);
			
			// Add the pending tag at the given local 
			// coordinates.
			this.addPendingTag(
				localPosition.left,
				localPosition.top
			);
		
			// Now that we are drawing a tag, let's bind 
			// the mousemove event to the container. This
			// will allow the user to resize the pending
			// tag hotpsot as they move their mouse.
			this.container.bind(
				"mousemove.photoTagger",
				function( event ){
					// Resize the pending tag.
					self.resizePendingTag( 
						event.clientX, 
						event.clientY 
					);
				}
			);
					
			// Now that we have started drawing, we're 
			// going to need a way to STOP drawing. If 
			// the user mouses-up, then finalize drawing.
			this.container.bind(
				"mouseup.photoTagger",
				function(){
					// Tear down the pending tag creation.
					self.teardownPendingTagCreation();
				}
			);
		},
		
				
		// I show the tags associated with this photo.
		showTags: function(){
			this.tags.show();
		},
		
		
		// I teardown the pending tag creation, translating
		// the pending tag into an actual tag.
		teardownPendingTagCreation: function(){
			var self = this;
			
			// Since we are done with the drawing, we no longer
			// need to keep track of the move movements. Unbind
			// any mouse up and mouse move events on container.
			// events related to tagging.
			this.container.unbind( "mouseup.photoTagger" );
			this.container.unbind( "mousemove.photoTagger" );
			
			// Check to see if the current tag size is valid. If 
			// not, the user is just going to have to redarw.
			if (this.isPendingTagSizeValid()){
			
				// Now that the user has drawn the tag, let's 
				// prompt them for the message to be associated.
				var message = prompt( "Message:", "" );
				
				// Check to see if the message was returned (if 
				// the user cancelled out, then we are going to 
				// cancel the tag creation).
				if (message){
					
					// Create a tag based on our pending tag. We
					// know everything BUT the ID at this point.
					var tag = this.addTag(
						"", 
						this.pendingTag.position().left, 
						this.pendingTag.position().top, 
						this.pendingTag.width(), 
						this.pendingTag.height(), 
						message
					);
					
					// Save this tag (to the server).
					this.saveTag( tag );
					
				}
			
			} else {
			
				// The pending tag size is too large.
				alert( "Your tag is too big." );
			
			}
			
			// Remove the pending tag from the container - it has 
			// no purpose for us anymore (if the user draws again,
			// another pending tag will be created).
			this.pendingTag.remove();
				
			// Regardless of whether or not the tag was created, 
			// we no longer need to keep track of it.
			this.pendingTag = null;
		},
		
		
		// I toggle the tag creation ability.
		toggleTagCreation: function(){
			this.isTagCreationEnabled = !this.isTagCreationEnabled;
		},
		
		
		// I toggle the tag deletion ability.
		toggleTagDeletion: function(){
			this.isTagDeletionEnabled = !this.isTagDeletionEnabled;
		}
		
	};
	
	
	// ------------------------------------------------------ //
	// ------------------------------------------------------ //
	// ------------------------------------------------------ //
	// ------------------------------------------------------ //

	
	// I flag whether or not the CSS has been loaded for this
	// plugin. This will need to be loaded when the plugin is
	// first applied.
	var isCSSLoaded = false;
	
	
	// I apply the plugin to the given collection of elements
	// for the first time.
	var applyPhotoTagger = function( collection, options ){
	
		// Since we are applying the plugin, let's check to see 
		// if the required CSS has been loaded. We only want to 
		// do this if the user hasn't already supplied the CSS.
		if (
			jQuery.fn.photoTagger.defaultOptions.applyCSS &&
			!isCSSLoaded
			){
			
			// Immediately flag the loading as true.
			isCSSLoaded = true;
			
			// Create a string buffer to hold the CSS that we need
			// to build for this module.
			var styleText = [];
			
			// Loop over each CSS selector to create a rule in our
			// style string buffer.
			jQuery.each(
				jQuery.fn.photoTagger.defaultOptions.css,
				function( selector, rule ){
					// Append the start of the rule.
					styleText.push( 
						selector.replace( 
							new RegExp( "\\." ),
							("." + jQuery.fn.photoTagger.defaultOptions.cssNameSpace)
						) + 
						" { " 
					);
				
					// Loop over the rule items.
					jQuery.each(
						rule,
						function( propertyName, value ){
							// Append the property.
							styleText.push(
								propertyName + ": " + value + " ;"
							);
						}
					);
				
					// Append the end of the rule.
					styleText.push( " } " );
				}
			);
			
			// Now that we have built up the CSS rules, let's 
			// create a Style tag, set the text, and append it
			// to the head of the current document.
			jQuery( "<style type='text/css'>" + styleText.join( "\n" ) + "</style>" )
				.appendTo( "html > head" )
			;
		
		}
	
	
		// Create a collection of settings to be used with this
		// set of photo tagging elements.
		var settings = jQuery.extend(
			{},
			jQuery.fn.photoTagger.defaultOptions,
			options
		);
	
		// Loop over each container element and create a photo 
		// tagger service instance for it.
		collection.each(
			function( index, node ){
				// Create a container object.
				var container = jQuery( this );
				
				// Check to make sure  that this element does not
				// already have the photo tagger plugin associated
				// with it.
				if (container.data( "photoTagger" )){
					
					// We don't want to apply this twice, so just
					// return out of this iteration.
					return;
					
				}
				
				// Create a new instance of the photo tagger
				// for the given container and settings combo.
				var photoTagger = new PhotoTagger( 
					container, 
					settings 
				);
				
				// Store the photo tagger service with the 
				// contianer in case it needs to be accessed.
				container.data( "photoTagger", photoTagger );			
			}
		);
		
		// Return the updated collection.
		return( collection );
	};
	
	
	// I execute the given method on elements with an existing
	// PhotoTagger plugin association.
	var applyPhotoTaggerMethod = function( collection, methodName ){
		
		// Loop over each element in the collection so that we
		// can get at the PhotoTagger instance.
		collection.each(
			function( index, node ){
				// Create a container object.
				var container = jQuery( this );
				
				// Try to get the photo tagger instance.
				var photoTagger = container.data( "photoTagger" );
						
				// Before executing the method, make sure that 
				// the photo tagger instance actually exists and
				// that the method is valid.
				if (
					photoTagger && 
					(methodName in photoTagger)
					){
										
					// Execute method on photo tagger instance.
					photoTagger[ methodName ]();
					
				}			
				
			}
		);
	
		// Return the updated collection.
		return( collection );
	};
	
	
	// Define the jQuery plugin for photo tagging. This is meant
	// to be called on photo container (NOT the photo).
	// 
	// NOTE: This function can take more than one type of method
	// signature:
	//
	// - Options: Sets up plugin for the first time.
	// - MethodName: Calls method on existing plugin.
	jQuery.fn.photoTagger = function(){
		
		// Check to see what kind of plugin application we are 
		// going to perform.
		if (typeof( arguments[ 0 ] ) == "string"){
		
			// We're invoking a method on elements with an 
			// existing photo tagger instance.
			return(
				applyPhotoTaggerMethod( this, arguments[ 0 ] )
			);
			
		} else {
		
			// We're applying the plugin for the first time.
			return(
				applyPhotoTagger( this, arguments[ 0 ] )
			);
		
		}
		
	};
	
	
	// ------------------------------------------------------ //
	// ------------------------------------------------------ //
	
	
	// Define the default options for the photo tagging. You can
	// override this here (for global settings) or pass in custom
	// settings when you apply the photo tagger plugin.
	jQuery.fn.photoTagger.defaultOptions = {
	
		// I flag whether or not to load the remote tag data 
		// immediately (upon plugin execution), or wait till the 
		// user mouses over the container for the first time.
		isDelayedLoad: false,
		
		// I flag whether or not new tag creation is enabled
		// when the plugin is first applied (or if it has to
		// be turned on manually).
		isTagCreationEnabled: false,
		
		// I falg whether or not existing tag deletion is
		// enabled when the plugin is first applied (of if it
		// has to be turned on manually).
		isTagDeletionEnabled: false,
		
		// This is the URL used to load the tags (via AJAX).
		loadURL: "",
		
		// This is the URL used to save the tags (via AJAX).
		saveURL: "",
		
		// I am the method (post vs. get) to be used with the
		// save API action.
		saveMethod: "post",
		
		// This is the URL used to delete the tags (via AJAX).
		deleteURL: "",
		
		// I am the method (post vs. get) to be used with the
		// delete API action.
		deleteMethod: "post",
		
		// I am a method to be used to scrub any of the AJAX
		// response data.
		cleanAJAXResponse: function( apiAction, response ){
			return( response );
		},
		
		// I am the message to be used if the AJAX method fails.
		ajaxFailMessage: "There was a problem with the API.",
		
		// I am the minimum opacity of the tags (for use when
		// one of the tags is being emphasized).
		minOpacity: .15,
		
		// This the method used to get the photo ID that is 
		// used in the tag-photo association. It requires that
		// the container be passed-in.
		getPhotoID: function( container ){
			return( container.find( "> img" ).attr( "id" ) );
		},
		
		// I am a flag as to whether or not to automatically apply
		// the CSS to the page. 
		applyCSS: true,
		
		// I am the CSS prefix that will be used when creating
		// the CSS rules (this can be used to prevent name
		// collisitions in CSS).
		cssNameSpace: "photo-tagger-",
		
		// I am the CSS to be used for the plugin. I wil be 
		// written to the page once to create the tags.
		css: {
			"a.tag": {
				"background-image": "url( '../images/transparent.gif' )",
				"border": "1px solid #FFFFFF",
				"display": "block",
				"height": "1px",
				"position": "absolute",
				"width": "1px",
				"z-index": "100",
				"zoom": "1"
			},
			
			"a.selected-tag": {
				"border-color": "#FFFFFF",
				"z-index": "200"
			},
			
			"div.message": {
				"background-color": "#212121",
				"border": "1px solid #000000",
				"color": "#F0F0F0",
				"display": "none",
				"font-family": "verdana",
				"font-size": "12px",
				"margin-top": "4px",
				"padding": "5px 10px 5px 10px",
				"position": "absolute",
				"white-space": "nowrap",
				"z-index": "200"
			}
		}
		
	};

})( jQuery( window ), jQuery );


