jQuery('document').ready(function(){

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

});

