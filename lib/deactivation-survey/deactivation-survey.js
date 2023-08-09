jQuery( document ).ready( function ( $ ) {

    const deactiveLink = "#deactivate-buddypress-media";
    let deactivateHref = '';
    let reasonCheckbox = '';

    // Looping through the reasons and creating the radio input fields.
    let reasons = JSON.parse( rtDeactivate.reasons );
    for( i = 0; i < reasons.length; i++ ) {
        reasonCheckbox += `<input type="radio" name="rt_deactivate_reason" id="reason-${i}" value="${reasons[i]}"> <label for="reason-${i}">${reasons[i]}</label> <br/>`;
    }
    reasonCheckbox += `<input type="radio" name="rt_deactivate_reason" id="reason-others" value="Others"> <label for="reason-others">Others.</label> <br/>`;

    // Creating Deactivating Modal.
    const deactivateModal = function ( url ) {
        const modalDOM = `<div class="rt-modal-wrapper"><div class="rt-deactivate-modal">
        <div class="rt-modal-head"><h2>${rtDeactivate.header_text}</h2></div>
        <div class="rt-modal-body">
            ${reasonCheckbox}
        </div>
        <div class="rt-modal-footer">
            <a href="${url}" class="rt-skip-link js-rt-skip-link">I rather wouldn\'t say</a>
            <div class="rt-actions-button">
                <button class="button button-secondary js-rt-cancel-deactivation">Cancel</button>
                <button class="button button-primary js-rt-submit">Submit & Deactivate</button>
            </div>
        </div>
        </div></div>`;

        return modalDOM;
    }

    // Preventing the deactivation event to trigger and show the popup.
    $( document.body ).on( 'click', deactiveLink, function(e) {
        e.preventDefault();
        deactivateHref = $( this ).attr( 'href' );
        $( '.wrap' ).append( deactivateModal( deactivateHref ) );
    } );


    // Canceling the popup.
    const cancelPopup = '.js-rt-cancel-deactivation';
    $( document.body ).on( 'click', cancelPopup, function(e) {
        e.preventDefault();
        $( '.rt-modal-wrapper' ).remove();
    } )

    // Submit the feedback with ajax.
    const submitBtn = '.js-rt-submit';
    $( document.body ).on( 'click', submitBtn, function(e) {
        e.preventDefault();
        let reasonVal = $( 'input[name="rt_deactivate_reason"]:checked' ).val();

        if ( 'Others' === reasonVal ) {
            reasonVal = $( '.rt-deactivate-others-input' ).val();
        }

        if ( 'undefined' !== typeof( reasonVal ) && '' !== rtDeactivate.home_url && '' !== rtDeactivate.user_name ) {
            $(submitBtn).attr( 'disabled', true );

            $.ajax( {
                url: rtDeactivate.ajax_url,
                type: 'post',
                data: {
                    action: 'rt_send_deactivation_feedback',
                    reason: reasonVal,
                    user: {
                        name: rtDeactivate.user_name,
                        email: rtDeactivate.user_email,
                    },
                    site_url: rtDeactivate.home_url,
                    nonce: rtDeactivate.nonce
                },
                success: function( response ) {
                    $( '.rt-modal-wrapper' ).remove();
                    let deactivateHref = $( '#deactivate-buddypress-media' ).attr( 'href' );
                    location.replace( deactivateHref );
                },
                error: function( error ) {
                    $( '.rt-modal-wrapper' ).remove();
                    let deactivateHref = $( '#deactivate-buddypress-media' ).attr( 'href' );
                    location.replace( deactivateHref );
                }
            })
        }
    } );


    // Others box.
    $( document.body ).on( 'click', 'input[name="rt_deactivate_reason"]', function(e) {
        if( 'Others' === $(this).val() ) {
            let othersInput = `<textarea class="rt-deactivate-others-input" rows="5"></textarea>`;
            $( '.rt-deactivate-others-input' ).remove();
            $( '.rt-modal-wrapper .rt-modal-body' ).append( othersInput );
        } else {
            $( '.rt-deactivate-others-input' ).remove();
        }
    } )
});