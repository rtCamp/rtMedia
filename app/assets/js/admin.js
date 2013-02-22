jQuery(document).ready(function(){

	/* Linkback */
	jQuery('#spread-the-word').on('click','#bp-media-add-linkback',function(){
		var data = {
			action: 'bp_media_linkback',
			linkback: jQuery('#bp-media-add-linkback:checked').length
		};
		jQuery.post(bp_media_admin_ajax,data,function(response){
		});
	})

	/* Fetch Feed */
	var bp_media_news_section = jQuery('#latest-news');
	if(bp_media_news_section.length>0){
		var data = {
			action: 'bp_media_fetch_feed'
		};
		jQuery.post(bp_media_admin_ajax,data,function(response){
			bp_media_news_section.find('.inside').html(response);
		});
	}

	/* Select Request */
	jQuery('#bp-media-settings-boxes').on('change', '#select-request', function(){
		if(jQuery(this).val()){
			jQuery('#bp_media_settings_form .bp-media-metabox-holder').html()
			jQuery('#bp_media_settings_form .bp-media-metabox-holder').html('<div class="support_form_laoder"></div>');
			var data = {
				action: 'bp_media_select_request',
				form: jQuery(this).val()
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#bp_media_settings_form .bp-media-metabox-holder').html()
				jQuery('#bp_media_settings_form .bp-media-metabox-holder').html(response).fadeIn('slow');
			});
		}
	});

	/* Cancel Request */
	jQuery('#bp-media-settings-boxes').on('click', '#cancel-request', function(){
		if(jQuery(this).val()){
			jQuery('#bp_media_settings_form .bp-media-metabox-holder').html()
			jQuery('#bp_media_settings_form .bp-media-metabox-holder').html('<div class="support_form_laoder"></div>');
			var data = {
				action: 'bp_media_cancel_request'
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#bp_media_settings_form .bp-media-metabox-holder').html()
				jQuery('#bp_media_settings_form .bp-media-metabox-holder').html(response).fadeIn('slow');
			});
		}
	});

	/* Submit Request */
	jQuery('.bp-media-support').on('submit', '#bp_media_settings_form', function(e){
		e.preventDefault();
		var data = {
			action: 'bp_media_submit_request',
			form_data: jQuery('form').serialize()
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			jQuery('#bp_media_settings_form .bp-media-metabox-holder').html()
			jQuery('#bp_media_settings_form .bp-media-metabox-holder').html(response).fadeIn('slow');
		});
	});

	function fireRequest(data) {
		return jQuery.post(ajaxurl, data, function(response){
			if(response != 0){
                                var redirect = false;
				var progw = Math.ceil((((parseInt(response)*20)+parseInt(data.values['finished']))/parseInt(data.values['total'])) *100);
				console.log(progw);
				if(progw>100){progw=100;redirect=true};
				jQuery('#rtprogressbar>div').css('width',progw+'%');
				finished = jQuery('#rtprivacyinstaller span.finished').html();
				jQuery('#rtprivacyinstaller span.finished').html(parseInt(finished)+data.count);
                                if ( redirect ) {
                                    jQuery.post(ajaxurl, { action: 'bp_media_privacy_redirect' }, function(response){
                                        window.location = settings_url;
                                    });
                                }
			} else {
				jQuery('#map_progress_msgs').html('<div class="map_mapping_failure">Row '+response+' failed.</div>');
			}
		});
	}

	jQuery('#rtprivacyinstall').click(function(e){
		e.preventDefault();
		$progress_parent = jQuery('#rtprivacyinstaller');
		$progress_parent.find('.rtprivacytype').each(function(){
			$type=jQuery(this).attr('id');
			if($type=='total'){
				$values=[];
				jQuery(this).find('input').each(function(){

					$values [jQuery(this).attr('name')]=[jQuery(this).val()];

				});
				$data = {};
				for(var i=1;i<=$values['steps'][0];i++ ){
					$count=20;
					if(i==$values['steps'][0]){
						$count=parseInt($values['laststep'][0]);
						if($count==0){$count=20};
					}
					newvals = {
						'page':i,
						'action':'bp_media_privacy_install',
						'count':$count,
						'values':$values
						}
				$data[i] = newvals;
			}
			var $startingpoint = jQuery.Deferred();
			$startingpoint.resolve();
			jQuery.each($data, function(i, v){
				$startingpoint = $startingpoint.pipe( function() {
					return fireRequest(v);
				});
			});


		}
	});
});

jQuery('#bp-media-settings-boxes').on('click','.interested',function(){
    jQuery('.interested-container').removeClass('hidden');
    jQuery('.choice-free').attr('required','required');
});
jQuery('#bp-media-settings-boxes').on('click','.not-interested',function(){
    jQuery('.interested-container').addClass('hidden');
    jQuery('.choice-free').removeAttr('required');
});

jQuery('#video-transcoding-main-container').on('click','.video-transcoding-survey',function(e){
    e.preventDefault();
    var data = {
        action: 'bp_media_convert_videos_form',
        email: jQuery('.email').val(),
        url: jQuery('.url').val(),
        choice: jQuery('input[name="choice"]:checked').val(),
        interested: jQuery('input[name="interested"]:checked').val()
    }
    jQuery.post(ajaxurl, data, function(response){
        jQuery('#video-transcoding-main-container').html('<p><strong>'+response+'</strong></p>');
    });
    return false;
});

});
