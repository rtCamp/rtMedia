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

    var bp_media_recent_tabs = jQuery('.media-tabs-container-tabs');
    if(bp_media_recent_tabs.length>0){
        jQuery(bp_media_recent_tabs).tabs();
    }

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
    jQuery('.activity-meta').on('click','.bp-media-featured',function(e){
        e.preventDefault();
        var post_id = jQuery(this).attr('data-post-id');
        var album_id = jQuery(this).attr('data-album-id');
        var curr_obj = jQuery(this);
        var data = {
            action: 'bp_media_set_album_cover',
            post_id:post_id,
            album_id:album_id
        };
        jQuery.post(bp_media_vars.ajaxurl,data,function( response )
        {
            curr_obj.text(response);
            curr_obj.attr('title',response);
        }
        );
    });

});