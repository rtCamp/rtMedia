//
//jQuery(document).ready(function(){
//jQuery('body').append('<!-- :::::::: PIROBOX :::::::::: --><div class="pre"></div><div class="bg_thumbs" title="close"></div><div class="box_next"><a href="#" class="next" title="next image">next</a></div><div class="box_previous"><a href="#" class="previous" title="previous image">prev</a></div><div id="gallery" class="thumbs"><div class="img_box"><div class="box_next_in"><a href="#" class="next_in" title="next image">next</a></div><div class="box_previous_in"><a href="#" class="previous_in" title="previous image">prev</a></div><span class="thumbs_close" title="close"></span><div class="caption"><p title="caption"></p></div></div></div><!-- :::::::: END PIROBOX :::::::::: -->');
//});



(function(jQuery) {

	jQuery.fn.piroBox = function(opt) {
        
		//set this options in your html page, it's easyer for you	 

		opt = jQuery.extend({
			border: '10', 
			borderColor : '#ffffff',
			mySpeed: 700,
			close_speed : 500,
			bg_alpha: 0.5,
			cap_op_start : 0.6,
			cap_op_end: 0.8,
			pathLoader : null, 
			gallery : null, 
			gallery_li : null,
			single : null,
			next_class : null,
			previous_class : null,
			gloabal : true
		}, opt);


return this.each(function() {
		
		/*___________________	START PIROBOX	    ________________________*/
	
			var div_load = '<div class="loader" title="close" style="background:'+opt.pathLoader+'"></div>';
			var next_out = jQuery('.box_next').width();
			//alert(next_out)
			var idPiro = jQuery(this).attr('id');
			var b_size = (opt.border)+2;

			
			
				if ( jQuery.browser.msie ) {
					opt.mySpeed = opt.mySpeed/1.2;
					jQuery('head').append('<!--[if lte IE 6]><style type="text/css">@media screen{* html{overflow-y: hidden;}* html body{height: 100%;overflow: auto;}}</style><![endif]-->');
					//alert('ie')
				} else {
					(opt.mySpeed);
					//alert('ff')
				}

			jQuery('.bg_thumbs, .thumbs, .thumbs_close ').hide();
			jQuery('.caption').css({'opacity':'0','visibility':'hidden'});
			var caption = jQuery('.caption').height();

			jQuery('.img_box').hover(function(){
				jQuery('.caption').stop().fadeTo(500,(opt.cap_op_end));
				},
				function(){
				jQuery('.caption').stop().fadeTo(500,(opt.cap_op_start));
										 
				});
			
			jQuery(window).resize(function(){
				var new_w_bg = jQuery(window).height();
				jQuery('.bg_thumbs').css({'visibility':'visible','height':+ new_w_bg+30 +'px'});
			});	
			
			var w_bg = jQuery(window).height();
	
				 
			jQuery('.bg_thumbs').css({'visibility':'hidden','height':+ w_bg+30 +'px'});
	
		
		/*___________________	LAUNCH GALLERY     ________________________*/
		
			jQuery(opt.gallery + ',' + opt.single).bind('click',function() {
				jQuery(this).parent('li').parent('ul').prepend('<li  class="begin"></li>');
				jQuery(this).parent('li').parent('ul').append('<li  class="end"></li>');
				jQuery('.pre').append(div_load).hide();
				jQuery('.img_box').prepend('<div class="caption"><p title="caption"></p></div>');
			
			jQuery(opt.next_class+','+opt.previous_class).css({'visibility':'hidden'});
				
			jQuery('.img_box').css('border-color',(opt.borderColor));
			//alert(opt.borderColor)		
			//alert(jQuery.image)
			
		/*___________________SINGLE, NEXT AND PREVIOUS PREPARE    ________________________*/				
				//alert(caption)
				
				//jQuery((opt.previous_class)).show().css({'visibility':'hidden','opacity':0});
						
					if(jQuery(this).parent().next('li').is('.end') || jQuery(this).parent('span').is('.single')){
		
						jQuery((opt.next_class)).css('right','-'+next_out-30+'px');
						jQuery(this).parent().next('li').removeClass('start');
								
					} else {
								
						jQuery((opt.next_class)).css('visibility','hidden').animate({
						right : '0px'																	  
						},1500);
						jQuery(this).parent().next('li').addClass('start');
							
					}
					
					if(jQuery(this).parent().prev('li').is('.begin') || jQuery(this).parent('span').is('.single')){
								//alert('begin')
								
						jQuery((opt.previous_class)).css('left','-'+next_out-30+'px');
								
								
					} else {
								
						jQuery((opt.previous_class)).css('visibility','hidden').animate({
						left : '0px'																	  
						},1500);
						jQuery(this).parent().prev('li').addClass('back');
							
					}
	
				jQuery('.img_box img').remove('img');
					jQuery(window).resize(function(){
												  
						var new_w_bg = jQuery(window).height();
						jQuery('.bg_thumbs').css({'visibility':'visible','height':+ new_w_bg+30 +'px'});
				  
					});
					var w_bg = jQuery(window).height();
					
				

				jQuery('.pre').css('visibility','visible').show().append(div_load);
						
				var pathImg = jQuery(this).attr('href');
				var titleImg = jQuery(this).attr('title');
				var myImg = new Image(); 
		
					jQuery(myImg).load(function() {
		
						var imgH = myImg.height;
						var imgW = myImg.width;	
						var w_H = jQuery(window).height();
						var w_W = jQuery(window).width();
		
						jQuery('#' + idPiro + ' .img_box').append(this);
							if(imgH+100 > w_H || imgW+100 > w_W){
								var new_img_W = imgW;
								var new_img_H = imgH;
								var _x = (imgW + 100)/w_W;
								var _y = (imgH + 100)/w_H;

								if ( _y > _x ){
								new_img_W = Math.round(imgW * (0.9/_y));
								new_img_H = Math.round(imgH * (0.9/_y));
								} else {
								new_img_W = Math.round(imgW * (0.9/_x));
								new_img_H = Math.round(imgH * (0.9/_x));
								}
								imgH += new_img_H;
								imgW += new_img_W;

								jQuery('.thumbs').show();
								jQuery('.bg_thumbs').show().css({'opacity':'0','visibility':'visible','height':+ w_bg +'px'}).fadeTo(300,(opt.bg_alpha));
								jQuery('.img_box img').css('visibility','hidden').hide();
								jQuery('.img_box').css({'visibility':'visible'}).animate({
									borderWidth : (opt.border),
									height : (new_img_H) + 'px' ,
									width : (new_img_W) + 'px' , 
									marginLeft : '-' +((new_img_W)/2  + b_size ) +'px',
									marginTop : '-' +((new_img_H)/2 + b_size) +'px'
									},1200);

		
								jQuery('.img_box').queue(function(){
									jQuery(myImg).height(new_img_H).width(new_img_W).css('opacity',0);
									jQuery('.img_box img').css('visibility','visible').show().fadeTo(300,1);
									jQuery('.img_box ').addClass('unloader');
									jQuery('.loader').remove(div_load);
									jQuery(opt.next_class+','+opt.previous_class).css({'visibility':'visible'});
									jQuery('.thumbs_close').show().css({'opacity':'0','visibility':'visible'}).fadeTo(300,1);
								jQuery('.img_box').dequeue()
											 
									if(titleImg == ""){							
										jQuery('.caption').hide();
										jQuery('.thumbs_close').fadeTo(200,1);
									}else{	
												
									jQuery('.caption').css({'visibility':'visible','width':+ new_img_W-8+'px'}).show().fadeTo(400,(opt.cap_op_start));
									jQuery('.caption p').html(titleImg);
									jQuery('.thumbs_close').fadeTo(200,1);

			
									}
		
								});
										
							} else {
										
								jQuery('.thumbs').show();
								jQuery('.bg_thumbs').show().css({'opacity':'0','visibility':'visible','height':+ w_bg +'px'}).fadeTo(300,(opt.bg_alpha));
								jQuery('.img_box img').css('visibility','hidden').hide();
								jQuery('.img_box').css({'visibility':'visible'}).animate({
									borderWidth : (opt.border),
									height : (imgH) + 'px' ,
									width : (imgW) + 'px' , 
									marginLeft : '-' +((imgW)/2  + b_size) +'px',
									marginTop : '-' +((imgH)/2 + b_size) +'px'
								},1200);
								//alert(b_size)
								jQuery('.img_box').queue(function(){
																	 
									jQuery(myImg).height(imgH).width(imgW).css('opacity',0);

									jQuery('.img_box img').css('visibility','visible').show().fadeTo(300,1);
									jQuery('.img_box ').addClass('unloader');
									jQuery('.loader').remove(div_load);
									jQuery(opt.next_class+','+opt.previous_class).css({'visibility':'visible'});
									jQuery('.thumbs_close').show().css({'opacity':'0','visibility':'visible'}).fadeTo(300,1);
								jQuery('.img_box').dequeue()
										 
									if(titleImg == ""){							
										jQuery('.caption').hide();
										jQuery('.thumbs_close').fadeTo(200,1);
									}else{
											
										jQuery('.caption').css({'visibility':'visible','width':+ imgW-8+'px'}).show().fadeTo(400,(opt.cap_op_start));
										jQuery('.caption p').html(titleImg);
										jQuery('.thumbs_close').fadeTo(200,1);

		//alert(caption)
									}
		
								});

							}

					});

		


				jQuery(myImg).attr('src', pathImg);
				return false;

			});
		/*___________________	NEXT    ________________________*/





			jQuery((opt.next_class)).bind('click',function() {
		
				jQuery('.thumbs_close').css({'opacity':'0'});
				jQuery('.img_box img').remove('img');
				jQuery('.pre').css('visibility','visible').show();
				jQuery('.caption').css({'opacity':'0','visibility':'hidden'}).show();
				jQuery('.pre').append(div_load);
						
				var pathImg = jQuery('.start>a').attr('href');
								
				var titleImg = jQuery('.start>a').attr('title');
				
				//var myId = jQuery('.start>a').attr('id');
				
				jQuery('.start').next('li').addClass('start');
		
				jQuery('.start').queue(function(){
					jQuery(this).prev('li').removeAttr('class');
					jQuery((opt.gallery_li)).removeClass('back');
					jQuery('.start').prev('li').prev('li').addClass('back');
					jQuery((opt.previous_class)).animate({left: '0px'},300);
				jQuery('.start').dequeue();
				});
		
					jQuery((opt.next_class)).animate({right: '-'+next_out+'px'},400);
						
				var myImg = new Image(); 
		
				jQuery(myImg).load(function() {
		
					var imgH = myImg.height;
					var imgW = myImg.width;	
					var w_H = jQuery(window).height();
					var w_W = jQuery(window).width();
		
		
						jQuery('#' + idPiro + ' .img_box').append(this);
							if(imgH+100 > w_H || imgW+100 > w_W){
								var new_img_W = imgW;
								var new_img_H = imgH;
								var _x = (imgW + 100)/w_W;
								var _y = (imgH + 100)/w_H;

								if ( _y > _x ){
								new_img_W = Math.round(imgW * (0.9/_y));
								new_img_H = Math.round(imgH * (0.9/_y));
								} else {
								new_img_W = Math.round(imgW * (0.9/_x));
								new_img_H = Math.round(imgH * (0.9/_x));
								}
								imgH += new_img_H;
								imgW += new_img_W;
							jQuery('.thumbs').show();
							jQuery('.img_box img').css('visibility','hidden').hide();
								jQuery('.img_box').css({'visibility':'visible'}).animate({
									borderWidth : (opt.border),
									height : (new_img_H) + 'px' ,
									width : (new_img_W) + 'px' , 
									marginLeft : '-' +((new_img_W)/2 + b_size) +'px',
									marginTop : '-' +((new_img_H)/2 + b_size) +'px'
								},(opt.mySpeed));
									  
							jQuery('.img_box').queue(function(){
								jQuery(myImg).height(new_img_H).width(new_img_W).css('opacity',0);
								jQuery('.img_box img').css('visibility','visible').show().fadeTo(200,1)
								jQuery('.img_box ').addClass('unloader');
								if(jQuery('.start ').is('li.end')){
									jQuery((opt.next_class)).animate({right: '-'+next_out+'px'},400);
									jQuery('.end').removeClass('start');
									//alert('end');
								}else{						
								jQuery((opt.next_class)).animate({right: '0px'},400);
								}
								jQuery('.loader').remove(div_load);
							jQuery('.img_box').dequeue()
		
		
									if(titleImg == ""){							
										jQuery('.caption').hide();
										jQuery('.thumbs_close').fadeTo(200,1);
									}else{
											
										jQuery('.caption').css({'visibility':'visible','width':+ new_img_W-8+'px'}).show().fadeTo(400,(opt.cap_op_start));
										jQuery('.caption p').html(titleImg);
										jQuery('.thumbs_close').fadeTo(200,1);

		//alert(caption)
									}										
								
							});
		
						} else {
		
							jQuery('.thumbs').show();
							jQuery('.img_box img').css('visibility','hidden').hide();
								jQuery('.img_box').css({'visibility':'visible'}).animate({
									top : '50%',															  
									borderWidth : (opt.border),
									height : (imgH) + 'px' ,
									width : (imgW) + 'px' , 
									marginLeft : '-' +((imgW)/2  + b_size) +'px',
									marginTop : '-' +((imgH)/2 + b_size) +'px'
								},(opt.mySpeed));
										 
							jQuery('.img_box').queue(function(){
								jQuery(myImg).height(imgH).width(imgW).css('opacity',0);
								
								jQuery('.img_box img').css('visibility','visible').show().fadeTo(200,1);
								jQuery('.img_box ').addClass('unloader');
																	 
								jQuery('.loader').remove(div_load);
								if(jQuery('.start ').is('li.end')){
									jQuery((opt.next_class)).animate({right: '-'+next_out+'px'},400);
									//alert('end')
									jQuery('.end').removeClass('start');
								}else{						
								jQuery((opt.next_class)).animate({right: '0px'},400);
								}
							jQuery('.img_box').dequeue()
		
									if(titleImg == ""){							
										jQuery('.caption').hide();
										jQuery('.thumbs_close').fadeTo(200,1);
									}else{
											
										jQuery('.caption').css({'visibility':'visible','width':+ imgW-8+'px'}).show().fadeTo(400,(opt.cap_op_start));
										jQuery('.caption p').html(titleImg);
										jQuery('.thumbs_close').fadeTo(200,1);

		//alert(caption)
									}
		
							});
		
						}
		
					});
		
					jQuery(myImg).attr('src', pathImg);
					
					return false;
		
			});
			 
		/*___________________	PREVIOUS   ________________________*/
		
		
			jQuery((opt.previous_class)).bind('click',function() {
		
		
				jQuery('.thumbs_close').css({'opacity':'0'});
				jQuery('.img_box img').remove('img');
				jQuery('.pre').css('visibility','visible').show();
				jQuery('.caption').css({'opacity':'0','visibility':'hidden'}).show();
				jQuery('.pre').append(div_load);
						
				var pathImg = jQuery('.back>a').attr('href');
								
				var titleImg = jQuery('.back>a').attr('title');
				
				//var myId = jQuery('.back>a').attr('id');
		
		
				jQuery((opt.gallery_li)).removeClass('start');
					jQuery((opt.gallery_li)).queue(function(){
						jQuery('.back').next('li').addClass('start');
					jQuery((opt.gallery_li)).dequeue();
					});
					//jQuery('.start').prev('li').addClass('back');
					jQuery('.back').queue(function(){
						jQuery((opt.gallery_li)).removeClass('back');
						jQuery('.start').prev('li').prev('li').addClass('back');
						jQuery((opt.next_class)).animate({right: '0px'},300);
					jQuery('.back').dequeue();
					});
				
		
					jQuery((opt.previous_class)).animate({left: '-'+next_out+'px'},400);

					
					var myImg = new Image(); 
		
					jQuery(myImg).load(function() {
		
						var imgH = myImg.height;
						var imgW = myImg.width;	
						var w_H = jQuery(window).height();
						var w_W = jQuery(window).width();
		
		
						jQuery('#' + idPiro + ' .img_box').append(this);
							if(imgH+100 > w_H || imgW+100 > w_W){
								var new_img_W = imgW;
								var new_img_H = imgH;
								var _x = (imgW + 100)/w_W;
								var _y = (imgH + 100)/w_H;

								if ( _y > _x ){
								new_img_W = Math.round(imgW * (0.9/_y));
								new_img_H = Math.round(imgH * (0.9/_y));
								} else {
								new_img_W = Math.round(imgW * (0.9/_x));
								new_img_H = Math.round(imgH * (0.9/_x));
								}
								imgH += new_img_H;
								imgW += new_img_W;
								jQuery('.thumbs').show();
								jQuery('.img_box img').css('visibility','hidden').hide();
								jQuery('.img_box').css({'visibility':'visible'}).animate({
									borderWidth : (opt.border),
									height : (new_img_H) + 'px' ,
									width : (new_img_W) + 'px' , 
									marginLeft : '-' +((new_img_W)/2  + b_size) +'px',
									marginTop : '-' +((new_img_H)/2 + b_size) +'px'
								},(opt.mySpeed));
															  
								jQuery('.img_box').queue(function(){
									jQuery(myImg).height(new_img_H).width(new_img_W).css('opacity',0);
									jQuery('.img_box img').css('visibility','visible').show().fadeTo(200,1)
									jQuery('.img_box ').addClass('unloader');
									jQuery('.loader').remove(div_load);
								if(jQuery('.back').is('li.begin')){
									jQuery((opt.previous_class)).animate({left: '-'+next_out+'px'},400);
									jQuery('.begin').removeClass('back');
									jQuery((opt.gallery_li)).removeClass('start');
									jQuery((opt.gallery_li)).queue(function(){
										jQuery('.begin').next('li').next('li').addClass('start');
									jQuery((opt.gallery_li)).dequeue()
									});
									//alert('begin')
								} else{	
								jQuery((opt.previous_class)).animate({left: '0px'},400);
								}							
								jQuery('.img_box').dequeue()
		
		
									if(titleImg == ""){							
										jQuery('.caption').hide();
										jQuery('.thumbs_close').fadeTo(200,1);
									}else{
											
										jQuery('.caption').css({'visibility':'visible','width':+ new_img_W-8+'px'}).show().fadeTo(400,(opt.cap_op_start));
										jQuery('.caption p').html(titleImg);
										jQuery('.thumbs_close').fadeTo(200,1);

		//alert(caption)
									}
								
								});
		
						} else {
		
							jQuery('.thumbs').show();
							jQuery('.img_box img').css('visibility','hidden').hide();
							jQuery('.img_box').css({'visibility':'visible'}).animate({
								top : '50%',															  
								borderWidth : (opt.border),
								height : (imgH) + 'px' ,
								width : (imgW) + 'px' , 
								marginLeft : '-' +((imgW)/2  + b_size) +'px',
								marginTop : '-' +((imgH)/2 + b_size) +'px'
							},(opt.mySpeed));
															 
							jQuery('.img_box').queue(function(){
								jQuery(myImg).height(imgH).width(imgW).css('opacity',0);
								jQuery('.img_box img').css('visibility','visible').show().fadeTo(200,1);
								jQuery('.img_box ').addClass('unloader');
								jQuery('.loader').remove(div_load);
								if(jQuery('.back').is('li.begin')){
									jQuery((opt.previous_class)).animate({left: '-'+next_out+'px'},400);
									jQuery('.begin').removeClass('back');
									jQuery((opt.gallery_li)).removeClass('start');
									jQuery((opt.gallery_li)).queue(function(){
										jQuery('.begin').next('li').next('li').addClass('start');
									jQuery((opt.gallery_li)).dequeue()
									});
									//alert('begin')
								} else{	
								jQuery((opt.previous_class)).animate({left: '0px'},400);
								}									
							jQuery('.img_box').dequeue()
		
		
									if(titleImg == ""){							
										jQuery('.caption').hide();
										jQuery('.thumbs_close').fadeTo(200,1);
									}else{
											
										jQuery('.caption').css({'visibility':'visible','width':+ imgW-8+'px'}).show().fadeTo(400,(opt.cap_op_start));
										jQuery('.caption p').html(titleImg);
										jQuery('.thumbs_close').fadeTo(200,1);

		//alert(caption)
									}
								
							});
								
						}
		
				});
		
				jQuery(myImg).attr('src', pathImg);
				return false;
		
			});				
		/*___________________	CLOSE IMAGE    ________________________*/

			jQuery('.bg_thumbs, .thumbs_close').bind('click',function(){
				jQuery('.pre').hide();
				jQuery('.caption').remove();
				jQuery('li.begin').remove();
				jQuery('li.end').remove();
				jQuery((opt.next_class)).animate({
				right :'-'+next_out+'px'
				},900);
				jQuery((opt.previous_class)).animate({
				left :'-'+next_out+'px'
				},900);
				jQuery((opt.gallery_li)).removeClass('start');
				jQuery((opt.gallery_li)).removeClass('back');
				jQuery('.loader').fadeTo(300,0);
				jQuery('.loader').queue(function(){

					jQuery('.bg_thumbs').fadeTo(500,0);
					jQuery('.img_box img').remove();
					jQuery('.img_box ').queue(function(){
					jQuery('.img_box').css({'borderWidth' : '0','top':'50%','height' : '50px' ,'width' : '50px' , 'marginLeft' : '-25px','marginTop' : '-18px','visibility':'hidden'}).removeClass('unloader');
						jQuery('.bg_thumbs,.thumbs_close').hide().css('visibility','hidden');
						jQuery('.thumbs').hide();
					jQuery('.img_box').dequeue();
					});
				jQuery('.loader').dequeue().remove(div_load);
				});
		

				jQuery('.img_box img, .thumbs_close').fadeTo(400,0);
				jQuery('.img_box img').queue(function(){
					jQuery('.img_box').animate({
					borderWidth : '0',
					top:'50%',
					height : '50px' ,
					width : '50px' , 
					marginLeft : '-25px',
					marginTop : '-18px'
					},(opt.close_speed));     
					jQuery('.img_box img').remove();
					jQuery('.img_box').removeClass('unloader');
					jQuery('.bg_thumbs').fadeTo(500,0);
						jQuery('.img_box ').queue(function(){
							jQuery('.bg_thumbs,.thumbs_close').hide().css('visibility','hidden');
							jQuery('.thumbs').css('display','none');
						jQuery('.img_box').css('visibility','hidden').dequeue()
				
						});
				jQuery('.img_box img').dequeue();
				});


			});
		
		});

	}   

})(jQuery);