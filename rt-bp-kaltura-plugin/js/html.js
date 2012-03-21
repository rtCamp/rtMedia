/* pirolab november 2008 */

jQuery(document).ready(function(){
						   
jQuery('body').prepend('<!-- :::::::: contact :::::::::: --><div class="bg_html"></div><div class="html_box"><div class="html_close"></div></div><!-- :::::::: end contact :::::::::: -->');
					   

	jQuery('.nav li a').css('opacity',0.9).hover(function(){
		jQuery(this).stop().animate({
			textIndent : '8px'
		},200);
	},
	function(){
		jQuery(this).stop().animate({
			textIndent :'0px'
		},100);
	});
	jQuery('.nav img').hover(function(){
		jQuery(this).stop().animate({
			opacity : 0.6
		},300);
	},
	function(){
		jQuery(this).stop().animate({
			opacity : 1
		},300);
	});
	jQuery('.contact a').bind('click',function(){
		var html = jQuery(this).attr('href');
		jQuery(window).resize(function(){
			var new_window_bg = jQuery(window).height();
			jQuery('.bg_thumbs').css({'visibility':'visible','height':+ new_window_bg +'px'});
		});	
		var window_bg =jQuery(window).height();
		jQuery(".bg_html").show().css({"opacity":"0","visibility":"visible","height":+ window_bg +"px"}).fadeTo(300,0.5);
		jQuery('.html_close').show();
		jQuery('.thumbs').show();
		jQuery('.html_box').css({'visibility':'visible','height':'300px','width':'430px','opacity':1,'margin-top':'-155px','margin-left':'-215px'}).prepend('<iframe frameborder=\"0\"></iframe>');
		jQuery('.html_box').show()
		jQuery('iframe').css({'visibility':'visible','height':'300px','width':'430px','overflow':'auto'}).attr('src',html);
	return false;
	
	});
	jQuery('.bg_html, .html_close').bind('click',function(){
		jQuery('iframe').remove();
		jQuery('.html_box').css({
		'borderWidth' : '1px',
		'top':'50%',
		'height' : '10px' ,
		'width' : '10px' , 
		'marginLeft' : '-5px',
		'marginTop' : '-5px',
		'opacity' : 0});   
		jQuery('.bg_html, .html_close').hide();
	});
			
			
});
