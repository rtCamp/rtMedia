/*
 * BuddyPress Media Default JS
 */

function bp_media_create_element(id){
	jQuery('#'+id).mediaelementplayer({
		enableKeyboard: false,
		startVolume: 1,
		success: function(mediaElement,domElement){
			 var $thisMediaElement = (mediaElement.id) ? jQuery("#"+mediaElement.id) : jQuery(mediaElement);
			$thisMediaElement.parents('.mejs-container').find(".mejs-volume-current").css("top","8px");
			$thisMediaElement.parents('.mejs-container').find(".mejs-volume-handle").css("top","5px");
		}
	});
}

jQuery(document).ready(function(){
	var bp_media_news_section = jQuery('#bp_media_latest_news');
	if(bp_media_news_section.length>0){
		jQuery.get(bp_media_news_url,function(data){
			bp_media_news_section.find('.inside').html(data);
		});
	}
    var bp_media_recent_tabs = jQuery('#recent-media-tabs');
    if(bp_media_recent_tabs.length>0){
        jQuery(bp_media_recent_tabs).tabs();
    }

//    var bp_media_popular_tabs = jQuery('#popular-media-tabs');
//    if(bp_media_popular_tabs.length>0){
//        jQuery( bp_media_popular_tabs ).tabs();
//    }

    var tallest = 0;
    jQuery('#recent-media-tabs .bp-media-tab-panel').each(function() {

                var thisHeight = jQuery(this).height();
                console.log(thisHeight);
                if(thisHeight > tallest) {
                        tallest = thisHeight;
                }
    }).height(tallest);


	jQuery('#bp-media-show-more').click(function(e){
		e.preventDefault();
		var data = {
			action: 'bp_media_load_more',
			page:++bp_media_vars.page,
			current_action : bp_media_vars.current_action,
			action_variables : bp_media_vars.action_variables,
			displayed_user : bp_media_vars.displayed_user,
			loggedin_user : bp_media_vars.loggedin_user
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(bp_media_vars.ajaxurl, data, function(response) {
			if(response.length==0)
				jQuery('#bp-media-show-more').parent().remove();
			else
				jQuery('#bp-media-list').append(response);
		});
	});
	setTimeout(function(){
		jQuery('.media album_updated .delete-activity,.media_upload .delete-activity').unbind('click').click(function(e){
			if(confirm('Are you sure you want to delete this activity and associated media?')){
				return true;
			}
			else{
				return false;
			}
		});

	},1000);

});