jQuery('document').ready(function(){
    
    
    jQuery('.wp-audio-shortcode, .wp-video-shortcode').mediaelementplayer();
    
    jQuery('.rtmedia-list').magnificPopup({
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

    jQuery('.rtmedia-container').on('click','.select-all', function(e){
        e.preventDefault();
        jQuery('.rtmedia-list input').each(function(){
            jQuery(this).prop('checked',true);
        });
    });

    jQuery('.rtmedia-container').on('click','.unselect-all', function(e){
        e.preventDefault();
        jQuery('.rtmedia-list input').each(function(){
            jQuery(this).prop('checked',false);
        });
    });

    jQuery('.rtmedia-container').on('click','.rtmedia-move',function(e){
        jQuery('.rtmedia-delete-container').slideUp();
        jQuery('.rtmedia-move-container').slideToggle();
    });
    
    jQuery('.rtmedia-container').on('click','.rtmedia-merge',function(e){
        jQuery('.rtmedia-merge-container').slideToggle();
    });
    
    jQuery('.rtmedia-container').on('click','.rtmedia-create-new-album-button',function(e){
        jQuery('.rtmedia-create-new-album-container').slideToggle();
    });
    
    jQuery('.rtmedia-container').on('click','.rtmedia-create-new-album',function(e){
        $albumname = jQuery.trim(jQuery('.rtmedia-new-album-name').val());
        if ($albumname != '') {
            var data = {
                action: 'rtmedia_create_album',
                name: $albumname
            };

            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(rtmedia_ajax_url, data, function(response) {
                if(response){
                    jQuery('.rtmedia-user-album-list').append('<option value="'+response+'">'+$albumname+'</option>');
                    jQuery('select.rtmedia-user-album-list option[value="'+response+'"]').prop('selected', true)
                } else {
                    alert('Something went wrong. Please try again.');
                }
            });
        } else {
            alert('Enter an album name');
        }
    });
    
    jQuery('.rtmedia-container').on('click','.rtmedia-delete-selected',function(e){
        jQuery('.rtmedia-bulk-actions').attr('action','../../../media/delete');
    });
    
    jQuery('.rtmedia-container').on('click','.rtmedia-move-selected',function(e){
        jQuery('.rtmedia-bulk-actions').attr('action','');
    });

});

