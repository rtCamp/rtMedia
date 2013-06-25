jQuery('document').ready(function(){

    jQuery('.rt-media-list').magnificPopup({
        delegate: 'a',
        type: 'ajax',
        tLoading: 'Loading image #%curr%...',
        mainClass: 'mfp-img-mobile',
        gallery: {
            enabled: true,
            navigateByImgClick: true,
            preload: [0,1] // Will preload 0 - before current, and 1 after the current image
        },
        image: {
            tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
            titleSrc: function(item) {
                return item.el.attr('title') + '<small>by Marsel Van Oosten</small>';
            }
        }
    });
        
    jQuery('.rt-media-container').on('click','.select-all', function(e){
        e.preventDefault();
        jQuery('.rt-media-list input').each(function(){
            jQuery(this).prop('checked',true);
        });
    });

    jQuery('.rt-media-container').on('click','.unselect-all', function(e){
        e.preventDefault();
        jQuery('.rt-media-list input').each(function(){
            jQuery(this).prop('checked',false);
        });
    });



});