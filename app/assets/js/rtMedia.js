jQuery('document').ready(function(){
    
    
    jQuery('.wp-audio-shortcode, .wp-video-shortcode').mediaelementplayer();
    
    jQuery('.rt-media-list').magnificPopup({
        delegate: 'a',
        type: 'ajax',
        tLoading: 'Loading image #%curr%...',
        mainClass: 'mfp-img-mobile',
        preload: [1,3] ,
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
        },
        disableOn: function() {
            if( jQuery(window).width() < 600 ) {
                return false;
            }
            return true;
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

    jQuery('.rt-media-container').on('click','.rt-media-move',function(e){
        jQuery('.rt-media-delete-container').slideUp();
        jQuery('.rt-media-move-container').slideToggle();
    });
    
    jQuery('.rt-media-container').on('click','.rt-media-merge',function(e){
        jQuery('.rt-media-merge-container').slideToggle();
    });
    
    jQuery('.rt-media-container').on('click','.rt-media-create-new-album-button',function(e){
        jQuery('.rt-media-create-new-album-container').slideToggle();
    });
    
    jQuery('.rt-media-container').on('click','.rt-media-create-new-album',function(e){
        $albumname = jQuery.trim(jQuery('.rt-media-new-album-name').val());
        if ($albumname != '') {
            var data = {
                action: 'rt_media_create_album',
                name: $albumname
            };

            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(rt_media_ajax_url, data, function(response) {
                if(response){
                    jQuery('.rt-media-user-album-list').append('<option value="'+response+'">'+$albumname+'</option>');
                    jQuery('select.rt-media-user-album-list option[value="'+response+'"]').prop('selected', true)
                } else {
                    alert('Something went wrong. Please try again.');
                }
            });
        } else {
            alert('Enter an album name');
        }
    });
    
    jQuery('.rt-media-container').on('click','.rt-media-delete-selected',function(e){
        jQuery('.rt-media-bulk-actions').attr('action','../../../media/delete');
    });
    
    jQuery('.rt-media-container').on('click','.rt-media-move-selected',function(e){
        jQuery('.rt-media-bulk-actions').attr('action','');
    });

});

