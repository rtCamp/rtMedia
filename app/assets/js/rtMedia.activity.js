( function() {
    if( bp && bp.Nouveau && bp.Nouveau.inject ) {
        callback = bp.Nouveau.inject;

        // Overwrite the inject function and apply mediaelement library player after adding activity.
        bp.Nouveau.inject = function( selector, content, method ) {
            callback.bind(this)( selector, content, method );

            if ( 'function' === typeof rtmedia_on_activity_add ) {
                rtmedia_on_activity_add();
            }
        };
    }
})();