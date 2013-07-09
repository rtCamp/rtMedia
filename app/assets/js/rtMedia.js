jQuery('document').ready(function($){
    
    $("#rt_media_comment_form").submit(function(e){
        if($.trim($("#comment_content").val()) == ""){
            alert("Empty Comment is not allowed");
            return false;
        }else{
            return true;
        }
            
    })

if(jQuery('.wp-audio-shortcode, .wp-video-shortcode').length > 0)
    jQuery('.wp-audio-shortcode, .wp-video-shortcode').mediaelementplayer();

    jQuery('.rtmedia-list-media').magnificPopup({
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
        },
		callbacks: {
			ajaxContentAdded: function(){

			$container = this.content.find('.tagcontainer');
			if($container.length>0){
			$context = $container.find('img');
			$container.find('.tagcontainer').css(
				{
					'height': $context.css('height'),
					'width': $context.css('width')
				});

			}
			}
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

    jQuery('.rtmedia-container').on('click','#rtmedia_create_new_album',function(e){
        $albumname = jQuery.trim(jQuery('#rtmedia_album_name').val());
		$context = jQuery.trim(jQuery('#rtmedia_album_context').val());
		$context_id = jQuery.trim(jQuery('#rtmedia_album_context_id').val());
        if ($albumname != '') {
            var data = {
                action: 'rtmedia_create_album',
                name: $albumname,
				context:$context,
				context_id: $context_id
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

