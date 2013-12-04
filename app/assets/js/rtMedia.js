var rtMagnificPopup;
function apply_rtMagnificPopup(selector){
    jQuery('document').ready(function($) {
	var rt_load_more = "";
	if(typeof(rtmedia_load_more) === "undefined") {
	    rt_load_more = "Loading media";
	} else {
	    rt_load_more = rtmedia_load_more;
	}
        rtMagnificPopup = jQuery(selector).magnificPopup({
            delegate: 'a:not(".no-popup")',
            type: 'ajax',
            tLoading: rt_load_more + ' #%curr%...',
            mainClass: 'mfp-img-mobile',
            preload: [1, 3],
            closeOnBgClick: false,
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                preload: [0, 1] // Will preload 0 - before current, and 1 after the current image
            },
            image: {
                tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
                titleSrc: function(item) {
                    return item.el.attr('title') + '<small>by Marsel Van Oosten</small>';
                }
            },
            disableOn: function() {
                if (jQuery(window).width() < 600) {
                    return false;
                }
                return true;
            },
            callbacks: {
                ajaxContentAdded: function() {

                    $container = this.content.find('.tagcontainer');
                    if ($container.length > 0) {
                        $context = $container.find('img');
                        $container.find('.tagcontainer').css(
                                {
                                    'height': $context.css('height'),
                                    'width': $context.css('width')
                                });

                    }
                    var settings = {};

                    if (typeof _wpmejsSettings !== 'undefined')
                        settings.pluginPath = _wpmejsSettings.pluginPath;

                    $('.mfp-content .wp-audio-shortcode,.mfp-content .wp-video-shortcode,.mfp-content .bp_media_content video').mediaelementplayer(settings);
                    $('.mfp-content .mejs-audio .mejs-controls').css('position', 'relative');
                    rtMediaHook.call('rtmedia_js_popup_after_content_added', []);
                },
                close: function(e) {
                    //console.log(e);
                },
                BeforeChange: function(e) {
                    //console.log(e);
                }
            }
        });

	if (jQuery(window).width() < 600) {
	    jQuery('#whats-new').focus( function(){
		jQuery("#whats-new-options").animate({
		    height:'100px'
		});
	    });
	    jQuery('#whats-new').blur( function(){
		jQuery("#whats-new-options").animate({
		    height:'100px'
		});
	    });
	}
    });
}
var rtMediaHook = {
    hooks: [],
    is_break : false,
    register: function(name, callback) {
        if ('undefined' == typeof(rtMediaHook.hooks[name]))
            rtMediaHook.hooks[name] = []
        rtMediaHook.hooks[name].push(callback)
    },
    call: function(name, arguments) {
        if ('undefined' != typeof(rtMediaHook.hooks[name]))
            for (i = 0; i < rtMediaHook.hooks[name].length; ++i){
                if (true != rtMediaHook.hooks[name][i](arguments)) {
                    rtMediaHook.is_break=true;
                    return false;
                    break;
                }
            }
            return true;
    }
}
jQuery('document').ready(function($) {

    $("#rt_media_comment_form").submit(function(e) {
        if ($.trim($("#comment_content").val()) == "") {
            alert( rtmedia_empty_comment_msg );
            return false;
        } else {
            return true;
        }

    })

    //Remove title from popup duplication
    $("li.rtmedia-list-item p a").each(function(e) {
        $(this).addClass("no-popup");
    })
    //rtmedia_lightbox_enabled from setting
    if (typeof(rtmedia_lightbox_enabled) != 'undefined' && rtmedia_lightbox_enabled == "1") {
        apply_rtMagnificPopup('.rtmedia-list-media, .rtmedia-activity-container ul.rtmedia-list, #bp-media-list,.widget-item-listing,.bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content, .rtm-bbp-container');
    }

    jQuery('.rtmedia-container').on('click', '.select-all', function(e) {
        e.preventDefault();
        jQuery('.rtmedia-list input').each(function() {
            jQuery(this).prop('checked', true);
        });
    });

    jQuery('.rtmedia-container').on('click', '.unselect-all', function(e) {
        e.preventDefault();
        jQuery('.rtmedia-list input').each(function() {
            jQuery(this).prop('checked', false);
        });
    });

    jQuery('.rtmedia-container').on('click', '.rtmedia-move', function(e) {
        jQuery('.rtmedia-delete-container').slideUp();
        jQuery('.rtmedia-move-container').slideToggle();
    });

//    jQuery('.rtmedia-container').on('click', '.rtmedia-merge', function(e) {
//        jQuery('.rtmedia-merge-container').slideToggle();
//    });

//    jQuery('.rtmedia-container').on('click', '.rtmedia-create-new-album-button', function(e) {
//        jQuery('.rtmedia-create-new-album-container').slideToggle();
//    });

    jQuery('.rtmedia-container').on('click', '#rtmedia_create_new_album', function(e) {
        $albumname = jQuery.trim(jQuery('#rtmedia_album_name').val());
        $context = jQuery.trim(jQuery('#rtmedia_album_context').val());
        $context_id = jQuery.trim(jQuery('#rtmedia_album_context_id').val());
	$privacy = jQuery.trim(jQuery('#rtmedia_select_album_privacy').val());
        if ($albumname != '') {
            var data = {
                action: 'rtmedia_create_album',
                name: $albumname,
                context: $context,
                context_id: $context_id
            };
	   if($privacy !== "") {
	       data['privacy'] = $privacy;
	   }
            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            $("#rtmedia_create_new_album").attr('disabled', 'disabled');
            var old_val = $("#rtmedia_create_new_album").html();
            $("#rtmedia_create_new_album").prepend("<img src='" + rMedia_loading_file + "'/>");
            jQuery.post(rtmedia_ajax_url, data, function(response) {
		response = response.trim();
                if (response) {
		    response = response.trim();
		    var flag = true;
		    jQuery('.rtmedia-user-album-list').each(function() {
			jQuery(this).children('optgroup').each(function(){
			    if(jQuery(this).attr('value') === $context) {
				flag = false;
				jQuery(this).append('<option value="' + response + '">' + $albumname + '</option>');
				return;
			    }
			});
			if(flag) {
			    var label = $context.charAt(0).toUpperCase() + $context	.slice(1);
			    var opt_html = '<optgroup value="' + $context + '" label="' + label + ' Albums"><option value="' + response + '">' + $albumname + '</option></optgroup>';
			    jQuery(this).append(opt_html);
			}

		    });
                    jQuery('select.rtmedia-user-album-list option[value="' + response + '"]').prop('selected', true);
                    jQuery('.rtmedia-create-new-album-container').slideToggle();
                    jQuery('#rtmedia_album_name').val("");
                    jQuery("#rtmedia-create-album-modal").append("<span class='rtmedia-success rtmedia-create-album-alert'><b>" + $albumname + "</b>" + rtmedia_album_created_msg + "</span>");
                    setTimeout(function() {
                        jQuery(".rtmedia-create-album-alert").remove();
                    }, 4000);
                    setTimeout(function() {
                        galleryObj.reloadView();
                        jQuery(".close-reveal-modal").click();
                    }, 2000);

                } else {
                    alert(rtmedia_something_wrong_msg);
                }
                $("#rtmedia_create_new_album").removeAttr('disabled');
                $("#rtmedia_create_new_album").html(old_val);
            });
        } else {
            alert(rtmedia_empty_album_name_msg);
        }
    });

    jQuery('.rtmedia-container').on('click', '.rtmedia-delete-selected', function(e) {
        jQuery('.rtmedia-bulk-actions').attr('action', '../../../media/delete');
    });

    jQuery('.rtmedia-container').on('click', '.rtmedia-move-selected', function(e) {
        jQuery('.rtmedia-bulk-actions').attr('action', '');
    });

    function rtmedia_media_view_counts() {
	//var view_count_action = jQuery('#rtmedia-media-view-form').attr("action");
	if(jQuery('#rtmedia-media-view-form').length > 0 ) {
	    var url = jQuery('#rtmedia-media-view-form').attr("action");
	    jQuery.post(url,
		{

		},function(data){

		});
	}
    }

    rtmedia_media_view_counts();
    rtMediaHook.register('rtmedia_js_popup_after_content_added',
	    function() {
		rtmedia_media_view_counts();
                rtmedia_init_media_deleting();
		return true;
	    }
    );
   var dragArea = jQuery("#drag-drop-area");
   var activityArea = jQuery('#whats-new');
   var content = dragArea.html();
   jQuery('#rtmedia-upload-container').after("<h2 id='rtm-drop-files-title'>" + rtmedia_drop_media_msg + "</h2>");
   jQuery('#whats-new-textarea').after("<h2 id='rtm-drop-files-title'>" + rtmedia_drop_media_msg + "</h2>");
   jQuery(document)
           .on('dragover', function(e) {
               jQuery('#rtm-media-gallery-uploader').show();
                activityArea.addClass('rtm-drag-drop-active');
                activityArea.css('height','150px');
                dragArea.addClass('rtm-drag-drop-active');
                jQuery('#rtm-drop-files-title').css('display', 'block');
                })
           .on("dragleave", function(e){
               e.preventDefault();
               activityArea.removeClass('rtm-drag-drop-active');
               activityArea.removeAttr('style');
               dragArea.removeClass('rtm-drag-drop-active');
                jQuery('#rtm-drop-files-title').hide();

                })
           .on("drop", function(e){
                e.preventDefault();
                 activityArea.removeClass('rtm-drag-drop-active');
                 activityArea.removeAttr('style');
                 dragArea.removeClass('rtm-drag-drop-active');
                jQuery('#rtm-drop-files-title').hide();
                });


    function rtmedia_init_media_deleting() {
        jQuery('.rtmedia-container').on('click', '.rtmedia-delete-media', function(e) {
            e.preventDefault();
            if(confirm(rtmedia_media_delete_confirmation)) {
                jQuery(this).closest('form').submit();
            }
        });
       }

       jQuery('.rtmedia-container').on('click', '.rtmedia-delete-album' , function(e) {
        e.preventDefault();
        if(confirm(rtmedia_album_delete_confirmation)) {
            jQuery(this).closest('form').submit();
        }
       });

       jQuery('.rtmedia-container').on('click', '.rtmedia-delete-media', function(e) {
        e.preventDefault();
        if(confirm(rtmedia_media_delete_confirmation)) {
            jQuery(this).closest('form').submit();
        }
    });

       jQuery(document).on('click', '.rtmedia-reveal-modal', function(e){
        e.preventDefault();
        var modalId = jQuery(this).data('reveal-id');
        jQuery('.reveal-modal-bg').css('display', 'block');
        jQuery('.reveal-modal-bg').css('opacity', '0.5');
        jQuery("#"+modalId).foundation('reveal', 'open');
    });

    jQuery(document).on('click', '.close-reveal-modal', function(e){
        e.preventDefault();
        var modalId = jQuery(this).parent('.reveal-modal');
        jQuery(modalId).foundation('reveal', 'close');
        jQuery('.reveal-modal-bg').fadeOut();
    });

//    jQuery(document).on('click', '#rtm_show_upload_ui', function(){
//        jQuery('#rtm-media-gallery-uploader').slideToggle();
//    });

});



//Legacy media element for old activities
function bp_media_create_element(id) {
    return false;
}
