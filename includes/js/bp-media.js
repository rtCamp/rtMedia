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