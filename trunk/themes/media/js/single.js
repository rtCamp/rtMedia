jQuery(document).ready(function(){
//alert('Hello');
//removing cancel rating button
    jQuery(".rating-cancel").remove();

// general show/hide options
            jQuery('.confirm').hide();
            var a = jQuery("#current-media-id").val();
//            alert(a);
//updating view counter
            var data = {action: 'media_view_update',image_id: jQuery('#current-media-id').val()};
            jQuery.post(ajaxurl, data, function(response) {
                    var new_url = response;
                    jQuery('#url').val(new_url);
                    jQuery('#url').val(new_url);
                });

 //JQUERY STAR RATING

                var timesClicked = 0;

                jQuery('.star').rating();
                jQuery('.star').children('a').bind('click',function(event){
                var k = this.innerHTML;
                var url = jQuery('#url').val();
                jQuery(".rt-thanks").addClass('m-loading');
                var view = jQuery('.view').text();

                var data = {
                    action: 'media_user_rating',
                    rating:k,
                    image_id:jQuery('#current-media-id').val(),
                    user_id:jQuery('#current-user-id').val()
                };
                     jQuery.post(ajaxurl, data, function(response) {
                         jQuery('.star').rating('readOnly');
                         jQuery(".rt-thanks").removeClass('m-loading');
                         jQuery('.rt-thanks').prepend(response);
                });
                timesClicked++;
                if (timesClicked >= 1) {
                jQuery('.star').children('a').unbind(event);
                }
            });

                //END STAR RATING

 //general show hide
 jQuery('.delete').click(function(){
            jQuery('.confirm').show(200);
        });
        jQuery('#cancel').click(function(){
            jQuery('.confirm').hide(200);
        });
// Delete from database click function
     jQuery('#no').click(function(){
                    var url = jQuery('#url').val();
                           var answer = confirm("Really want to delete?")
	if (answer){
                    jQuery(".rt-thanks").addClass('m-loading');
                    var data ={action:'media_delete_local', media_id:jQuery('#current-media-id').val()};
                     jQuery.post(ajaxurl, data, function(response) {
                      jQuery(".rt-thanks").removeClass('m-loading');
                       alert(response);
                       jQuery('div#user-title h2').text("Please Wait while ur being redirected...");
                       jQuery('.rt-picture-single').slideUp(1000,function(){
                        window.location=url+"/media";
                       });
                });
        }
        });



// Delete from database + kaltura server click function

            jQuery('#yes').click(function(){
                var url = jQuery('#url').val();
                var answer = confirm("Really want to delete?")
	if (answer){
	       jQuery(".rt-thanks").addClass('m-loading');
               var data = {action: 'media_delete_server', media_id:jQuery('#current-media-id').val()};
               jQuery.post(ajaxurl, data, function(response) {
                   jQuery(".rt-thanks").removeClass('m-loading');
                   alert(response);
                   jQuery('div#user-title h2').text("Please Wait while ur being redirected...");
                   jQuery('.rt-picture-single').slideUp(1000,function(){
                        window.location=url+"/media";
                   });
                       
                    });
        }
             });





    jQuery(".del").click(function(){
            jQuery(".rt-thanks").append('<img src="<?php echo WP_PLUGIN_URL; ?>/buddypress-media/themes/media/images/ajax-loader.gif" />');
        });
//    jQuery("#user-title p").each(function(i){
//        setClickable(this, i);
//    //        console.log('i m clicked');
//    });
//    jQuery("#pic-description p").each(function(i){
//        setClickable2(this, i);
//    //        console.log('i m clicked');
//    });

    jQuery('#current-url').val(window.location);

    //Changing album from the single page function
        var default_selected = jQuery("#change-album option:selected").text();
//        alert(default_selected);
        
    jQuery('#change-album').change(function(){
        var select_option = jQuery("#change-album option:selected").text();
//        alert(select_option);

        /* PRASAD CODE
            I think that we need not give condition if(!(select_option == default_selected))
            since it disallows the album to change to original value.
         PRASAD CODE END */

        if(!(select_option == default_selected))
        {
             jQuery( 'div.rt-media-album').children('span.ajax-loader' ).show();
             var data = {action: 'rt_album_update',image_id: jQuery('#current-media-id').val(), album_id:jQuery("#change-album option:selected").val()};
            jQuery.post(ajaxurl, data, function(response) {
                        alert(response + select_option);
                        jQuery( 'div.rt-media-album').children('span.ajax-loader' ).hide();
                    
                });
        }

    });
setClickable_title();
setClickable_desc();

});


function setClickable_title() {
jQuery('#user-title p').click(function() {
var textarea = '<div><textarea rows="2" cols="60">'+jQuery(this).html()+'</textarea>';
var button = '<div><input type="button" value="SAVE" class="saveButton" /> OR <input type="button" value="CANCEL" class="cancelButton" /></div></div>';
var revert = jQuery(this).html();
jQuery(this).after(textarea+button).remove();
jQuery('.saveButton').click(function(){saveChanges_title(this, false);});
jQuery('.cancelButton').click(function(){saveChanges_title(this, revert);});
})
.mouseover(function() {
jQuery(this).addClass("editable");
})
.mouseout(function() {
jQuery(this).removeClass("editable");
});
}

function saveChanges_title(obj, cancel) {
if(!cancel) {
jQuery('#user-title').addClass('m-loading');
var t = jQuery(obj).parent().siblings(0).val();
var new_title = jQuery(obj).parent().siblings(0).val();
        var id = jQuery('#current-media-id').val();
        var data = {action: 'media_change_title','id': id,new_title: new_title};
        var newt = '<p>'+new_title+'</p>';
        jQuery(obj).parent().parent().parent().html(newt);
        jQuery.post(ajaxurl,data,function(txt){
            jQuery('#user-title h2 p' ).text(txt);
            jQuery('#user-title').removeClass('m-loading');
        });
}
else {
var t = cancel;
}
if(t=='') t='(click to add text)';
jQuery(obj).parent().parent().parent().after('<h2 class = "rt-mediatitle"  title ="Click here to add title"><p>'+t+'</p></h2>').remove();
setClickable_title();
}



function setClickable_desc() {
jQuery('#pic-description p').click(function() {
var textarea = '<div><textarea rows="10" cols="60">'+jQuery(this).html()+'</textarea>';
var button = '<div><input type="button" value="SAVE" class="saveButton" /> OR <input type="button" value="CANCEL" class="cancelButton" /></div></div>';
var revert = jQuery(this).html();
jQuery(this).after(textarea+button).remove();
jQuery('.saveButton').click(function(){saveChanges_desc(this, false);});
jQuery('.cancelButton').click(function(){saveChanges_desc(this, revert);});
})
.mouseover(function() {
jQuery(this).addClass("editable");
})
.mouseout(function() {
jQuery(this).removeClass("editable");
});
}

function saveChanges_desc(obj, cancel) {
if(!cancel) {
    jQuery(".rt-thanks").addClass('m-loading');
var t = jQuery(obj).parent().siblings(0).val();
        var new_desc = jQuery(obj).parent().siblings(0).val();
        var id = jQuery('#current-media-id').val();
        var data = {action: 'media_change_description','id': id,new_desc: new_desc};
        var newt = '<p>'+new_desc+'</p>';
        jQuery(obj).parent().parent().parent().html(newt);
        jQuery.post(ajaxurl,data,function(txt){
            jQuery('#pic-description p' ).text(txt);
            jQuery(".rt-thanks").removeClass('m-loading');
        });

}
else {
var t = cancel;
}
if(t=='') t='(click to add text)';
jQuery(obj).parent().parent().parent().after('<h3><p>'+t+'</p></h3>').remove();
setClickable_desc();
}

//function setClickable(obj, i) {
//    jQuery(obj).click(function() {
//        var textarea = '<textarea rows="4" cols="60">'+jQuery(this).html()+'</textarea>';
//        var button	 = '<div><input type="button" value="SAVE" class="saveButton" /> OR <input type="button" value="CANCEL" class="cancelButton" /></div>';
//        var revert = jQuery(obj).html();
//        jQuery(obj).after(textarea+button).remove();
//        jQuery('.saveButton').click(function(){
//             jQuery('#user-title p' ).text('please Wait');
//            saveChanges(this, false, i);
//        });
//        jQuery('.cancelButton').click(function(){
//            saveChanges(this, revert, i);
//        });
//    })
//    .mouseover(function() {
//        jQuery(obj).addClass("editable");
//    })
//    .mouseout(function() {
//        jQuery(obj).removeClass("editable");
//    });
//}//end of function setClickable
//
//function saveChanges(obj, cancel, n) {
//    if(!cancel) {
//        var new_title = jQuery(obj).parent().siblings(0).val();
//        var id = jQuery('#current-media-id').val();
////        console.log(id);
//        var data = {
//            action: 'media_change_title',
//            'id': id,
//            new_title: new_title
//        };
//        var newt = '<p>'+new_title+'</p>';
//        jQuery(obj).parent().parent().html(newt);
//        jQuery('#user-title h2 p' ).addClass('load');
//
//        jQuery.post(ajaxurl,data,function(txt){
//            jQuery('#user-title p' ).text(txt);
//            jQuery('#user-title p').removeClass('load');
//        });
//
//    }
//    else {
//        var t = cancel;
//    }
//    if(t=='') t='(click to add text)';
//    jQuery(obj).parent().parent().after('<h2><p>'+t+'</p></h2>').remove();
//    setClickable(jQuery("p").get(n), n);
//}
//
//
//function setClickable2(obj, i) {
//    jQuery(obj).click(function() {
//        var textarea = '<textarea rows="4" cols="60">'+jQuery(this).html()+'</textarea>';
//        var button	 = '<div><input type="button" value="SAVE" class="saveButton" /> OR <input type="button" value="CANCEL" class="cancelButton" /></div>';
//        var revert = jQuery(obj).html();
//        jQuery(obj).after(textarea+button).remove();
//        jQuery('.saveButton').click(function(){
//             jQuery('#pic-description p' ).text('please Wait');
//            saveChanges2(this, false, i);
//        });
//        jQuery('.cancelButton').click(function(){
//            saveChanges2(this, revert, i);
//        });
//    })
//    .mouseover(function() {
//        jQuery(obj).addClass("editable");
//    })
//    .mouseout(function() {
//        jQuery(obj).removeClass("editable");
//    });
//}//end of function setClickable
//
//function saveChanges2(obj, cancel, n) {
////    console.log(n);
//    if(!cancel) {
//        var new_desc = jQuery(obj).parent().siblings(0).val();
//        var id = jQuery('#current-media-id').val();
////        console.log(id);
//        var data = {
//            action: 'media_change_description',
//            'id': id,
//            new_desc: new_desc
//        };
//
//        var newt = '<p>'+new_desc+'</p>';
//        jQuery(obj).parent().parent().html(newt);
//        jQuery('#pic-description h3 p' ).addClass('load');
//        jQuery.post(ajaxurl,data,function(txt){
//            jQuery('#pic-description p' ).text(txt);
//            jQuery('#pic-description p' ).removeClass('load');
//        });
//    }
//    else {
//        var t = cancel;
//    }
//    if(t=='') t='(click to add text)';
//    jQuery(obj).parent().parent().after('<h3><p>'+t+'</p></h3>').remove();
//    setClickable2(jQuery("p").get(n), n);
//}
