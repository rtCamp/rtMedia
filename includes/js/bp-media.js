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
});