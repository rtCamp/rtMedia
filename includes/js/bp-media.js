/* 
 * BuddyPress Media Default JS
 */


jQuery(document).ready(function(){
	var count=1;
	jQuery('#testbutton').click(function(){
		
		jQuery("#output").append("<video width=\"640\" height=\"360\" src=\"http://bp.madlabs.co.cc/wp-content/uploads/2012/06/echo-hereweare.mp4\" type=\"video/mp4\" id=\"player"+count+"\" controls=\"controls\" preload=\"none\"></video><script>jQuery('#player"+count+"').mediaelementplayer();</script>");
		count++;
		//jQuery('video').mediaelementplayer();
	});
});

