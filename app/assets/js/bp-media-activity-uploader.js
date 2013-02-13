/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function(){
    if ( jQuery('#bp-media-activity-upload-ui').length > 0 ) {
        //Activity Uploader
        var bp_media_activity_is_multiple_upload = false;
        var bp_media_activity_uploader=new plupload.Uploader(bp_media_uploader_params);
        var bp_media_activity_album_selected = false;
        bp_media_activity_uploader.init();

        bp_media_activity_uploader.bind('FilesAdded', function(up, files) {
            //bp_media_is_multiple_upload = files.length==1&&jQuery('.bp-media-progressbar').length==0?false:true;
            bp_media_activity_is_multiple_upload = files.length>1;
            jQuery.each(files, function(i, file) {
                var bp_media_activity_extension = file.name.substr( (file.name.lastIndexOf('.') +1) );
                jQuery('#bp-media-activity-uploaded-files').append('<div id="bp-media-activity-progress-'+file.id+'" class="bp-media-progressbar"><div class="bp-media-progress-text">' + file.name + ' (' + plupload.formatSize(file.size) + ')(<b>0%</b>)</div><div class="bp-media-progress-completed"></div></div>');
            });
            //                bp_media_activity_album_selected = jQuery('#bp-media-activity-selected-album').val();
            bp_media_activity_album_selected = '535';
            bp_media_activity_uploader.start();
            up.refresh(); // Reposition Flash/Silverlight
        });
        bp_media_activity_uploader.bind('UploadProgress', function(up, file) {
            jQuery('input#aw-whats-new-submit').prop('disabled',true);
            jQuery('input#bp-media-aw-whats-new-submit').prop('disabled',true);
            jQuery('#bp-media-activity-progress-'+file.id+' .bp-media-progress-completed').width(file.percent+'%');
            jQuery('#bp-media-activity-progress-'+file.id+' .bp-media-progress-text b').html(file.percent+'%');
        });

        bp_media_activity_uploader.bind('Error', function(up, err) {
            jQuery('#bp-media-activity-uploaded-files').html('<div class="error"><p>Error: ' + err.code +
                ', Message: ' + err.message +
                (err.file ? ', File: ' + err.file.name : '') +
                '</p></div>'
                );
            up.refresh();
        });

        bp_media_activity_uploader.bind('FileUploaded', function(up, file,response) {
            jQuery('input#aw-whats-new-submit').after('<input type="submit" name="bp-media-aw-whats-new-submit" id="bp-media-aw-whats-new-submit" value="Post Media Update">').remove()
            jQuery('#bp-media-aw-whats-new-submit').prop('disabled',true);
            jQuery('#bp-media-activity-progress-'+file.id+' .bp-media-progress-text b').html("100%");
            jQuery('#bp-media-activity-post-update-append').append('<span>'+response.response+'</span>');
            jQuery('#bp-media-aw-whats-new-submit').prop('disabled',false);
        });
        bp_media_activity_uploader.bind('BeforeUpload',function(up){
            up.settings.multipart_params.is_multiple_upload = bp_media_activity_is_multiple_upload;
            up.settings.multipart_params.bp_media_album_id = bp_media_activity_album_selected;
            up.settings.multipart_params.is_activity = true;
        });
        bp_media_activity_uploader.bind('UploadComplete',function(response){
            });
        
        /* New posts */
        jQuery('#whats-new-submit').on('click', 'input#aw-whats-new-submit', function() {
            var button = jQuery(this);
            var form = button.parent().parent().parent().parent();

            form.children().each( function() {
                if ( jQuery.nodeName(this, "textarea") || jQuery.nodeName(this, "input") )
                    jQuery(this).prop( 'disabled', true );
            });

            /* Remove any errors */
            jQuery('div.error').remove();
            button.addClass('loading');
            button.prop('disabled', true);

            /* Default POST values */
            var object = '';
            var item_id = jQuery("#whats-new-post-in").val();
            var content = jQuery("textarea#whats-new").val();

            /* Set object for non-profile posts */
            if ( item_id > 0 ) {
                object = jQuery("#whats-new-post-object").val();
            }

            jQuery.post( ajaxurl, {
                action: 'post_update',
                'cookie': encodeURIComponent(document.cookie),
                '_wpnonce_post_update': jQuery("input#_wpnonce_post_update").val(),
                'content': content,
                'object': object,
                'item_id': item_id,
                '_bp_as_nonce': jQuery('#_bp_as_nonce').val() || ''
            },
            function(response) {

                form.children().each( function() {
                    if ( jQuery.nodeName(this, "textarea") || jQuery.nodeName(this, "input") ) {
                        jQuery(this).prop( 'disabled', false );
                    }
                });

                /* Check for errors and append if found. */
                if ( response[0] + response[1] == '-1' ) {
                    form.prepend( response.substr( 2, response.length ) );
                    jQuery( 'form#' + form.attr('id') + ' div.error').hide().fadeIn( 200 );
                } else {
                    if ( 0 == jQuery("ul.activity-list").length ) {
                        jQuery("div.error").slideUp(100).remove();
                        jQuery("div#message").slideUp(100).remove();
                        jQuery("div.activity").append( '<ul id="activity-stream" class="activity-list item-list">' );
                    }

                    jQuery("ul#activity-stream").prepend(response);
                    jQuery("ul#activity-stream li:first").addClass('new-update');

                    if ( 0 != jQuery("#latest-update").length ) {
                        var l = jQuery("ul#activity-stream li.new-update .activity-content .activity-inner p").html();
                        var v = jQuery("ul#activity-stream li.new-update .activity-content .activity-header p a.view").attr('href');

                        var ltext = jQuery("ul#activity-stream li.new-update .activity-content .activity-inner p").text();

                        var u = '';
                        if ( ltext != '' )
                            u = l + ' ';

                        u += '<a href="' + v + '" rel="nofollow">' + BP_DTheme.view + '</a>';

                        jQuery("#latest-update").slideUp(300,function(){
                            jQuery("#latest-update").html( u );
                            jQuery("#latest-update").slideDown(300);
                        });
                    }

                    jQuery("li.new-update").hide().slideDown( 300 );
                    jQuery("li.new-update").removeClass( 'new-update' );
                    jQuery("textarea#whats-new").val('');
                }

                jQuery("#whats-new-options").animate({
                    height:'0px'
                });
                jQuery("form#whats-new-form textarea").animate({
                    height:'20px'
                });
                jQuery("#aw-whats-new-submit").prop("disabled", true).removeClass('loading');
            });

            return false;
        });
        
        jQuery('#whats-new-submit').on('click', '#bp-media-aw-whats-new-submit', function(e){
            var button = jQuery(this);
            button.addClass('loading');
            var form = button.parent().parent().parent().parent();
            /* Default POST values */
            var object = '';
            var item_id = jQuery("#whats-new-post-in").val();

            /* Set object for non-profile posts */
            if ( item_id > 0 ) {
                object = jQuery("#whats-new-post-object").val();
            }
            var media_id = false;
            var multiple = 0;
            jQuery('#bp-media-activity-post-update-append span').each(function(){
                if (media_id){
                    media_id=media_id+'-'+jQuery(this).text();
                    multiple = 1;
                }else{
                    media_id=jQuery(this).text();
                }
            });
            var data = {
                action: 'bp_media_post_update',
                '_wpnonce_post_update': jQuery("input#_wpnonce_post_update").val(),
                'content': jQuery('#whats-new').val(),
                'object': object,
                'item_id': item_id,
                'media_id': media_id,
                'multiple': multiple,
                '_bp_as_nonce': jQuery('#_bp_as_nonce').val() || ''
            };
            jQuery.post(activity_ajax_url,data, function (response) {
                jQuery('#bp-media-activity-uploaded-files').html('');
                jQuery('#bp-media-activity-post-update-append').html('');
                jQuery('#bp-media-aw-whats-new-submit').after('<input type="submit" name="aw-whats-new-submit" id="aw-whats-new-submit" value="Post Update">').remove();
                form.children().each( function() {
                    if ( jQuery.nodeName(this, "textarea") || jQuery.nodeName(this, "input") ) {
                        jQuery(this).prop( 'disabled', false );
                    }
                });

                /* Check for errors and append if found. */
                if ( response[0] + response[1] == '-1' ) {
                    form.prepend( response.substr( 2, response.length ) );
                    jQuery( 'form#' + form.attr('id') + ' div.error').hide().fadeIn( 200 );
                } else {
                    if ( 0 == jQuery("ul.activity-list").length ) {
                        jQuery("div.error").slideUp(100).remove();
                        jQuery("div#message").slideUp(100).remove();
                        jQuery("div.activity").append( '<ul id="activity-stream" class="activity-list item-list">' );
                    }

                    jQuery("ul#activity-stream").prepend(response);
                    jQuery("ul#activity-stream li:first").addClass('new-update');

                    if ( 0 != jQuery("#latest-update").length ) {
                        var l = jQuery("ul#activity-stream li.new-update .activity-content .activity-inner p").html();
                        var v = jQuery("ul#activity-stream li.new-update .activity-content .activity-header p a.view").attr('href');

                        var ltext = jQuery("ul#activity-stream li.new-update .activity-content .activity-inner p").text();

                        var u = '';
                        if ( ltext != '' )
                            u = l + ' ';

                        u += '<a href="' + v + '" rel="nofollow">' + BP_DTheme.view + '</a>';

                        jQuery("#latest-update").slideUp(300,function(){
                            jQuery("#latest-update").html( u );
                            jQuery("#latest-update").slideDown(300);
                        });
                    }

                    jQuery("li.new-update").hide().slideDown( 300 );
                    jQuery("li.new-update").removeClass( 'new-update' );
                    jQuery("textarea#whats-new").val('');
                }

                jQuery("#whats-new-options").animate({
                    height:'0px'
                });
                jQuery("form#whats-new-form textarea").animate({
                    height:'20px'
                });
                jQuery("#aw-whats-new-submit").prop("disabled", true).removeClass('loading');
                jQuery("input#aw-whats-new-submit").bind('click');
            });
            e.preventDefault();
        });
    }
});