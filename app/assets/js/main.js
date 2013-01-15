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

    var bp_media_recent_tabs = jQuery('.media-tabs-container');
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
            loggedin_user : bp_media_vars.loggedin_user,
            current_group :	bp_media_vars.current_group
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

    /* Add Featured Image */

    jQuery('.bp-media-featured').live('click',function(e){
        e.preventDefault();
        var post_id = jQuery(this).attr('data-post-id');
        var post_date = new Date();
        var date = post_date.getFullYear()+'-'+(post_date.getMonth() + 1) +'-'+post_date.getDate()+' '+ post_date.getHours()+':'+(post_date.getMinutes() + 1)+':'+(post_date.getSeconds()+1);
        // var post_date = d.getTime();
        var curr_obj = jQuery(this);
        var remove_featured = 0;
        if(jQuery(this).attr('data-remove-featured')){
            remove_featured = jQuery(this).attr('data-remove-featured');
        }
        jQuery.ajax({
            url:"/wp-admin/admin-ajax.php",
            type:'POST',
            data:'action=my_featured_action&post_id='+post_id+'&remove_featured='+remove_featured+'&post_date='+date,
            success:function( results )
            {
                if(remove_featured == 1){
                    curr_obj.text('Featured');
                    curr_obj.attr('data-remove-featured','0');
                } else {
                    curr_obj.text('Remove Featured');
                    curr_obj.attr('data-remove-featured','1');
                }

            }
        });
    });

});