jQuery( document ).ready( function ( $ ) {

	jQuery( '.rt-form-checkbox' ).each( function () {

		//This object
		var self = jQuery( this );
		var obj = self.find( 'label' );

		self.addClass( 'rtm-switch-options' );
		obj.append( '<span class="switch_enable"> ON </span><span class="switch_disable"> OFF </span>' );

		var enb = obj.children( '.switch_enable' ); //cache first element, this is equal to ON
		var dsb = obj.children( '.switch_disable' ); //cache first element, this is equal to OFF
		var input = obj.children( 'input' ); //cache the element where we must set the value
		var input_val = input.is( ':checked' ); //cache the element where we must set the value

		/* Check selected */
		if ( false === input_val ) {
			dsb.addClass( 'selected' );
		}
		else if ( true === input_val ) {
			enb.addClass( 'selected' );
		}

		//Action on user's click(ON)
		enb.on( 'click', function () {
			$( dsb ).removeClass( 'selected' ); //remove "selected" from other elements in this object class(OFF)
			$( this ).addClass( 'selected' ); //add "selected" to the element which was just clicked in this object class(ON)
			//$( input ).val( true ).change(); //Finally change the value to 1
		} );

		//Action on user's click(OFF)
		dsb.on( 'click', function () {
			$( enb ).removeClass( 'selected' ); //remove "selected" from other elements in this object class(ON)
			$( this ).addClass( 'selected' ); //add "selected" to the element which was just clicked in this object class(OFF)
			//$( input ).val( false ).change(); // //Finally change the value to 0
		} );

	} );

} );