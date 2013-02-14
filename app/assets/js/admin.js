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

});