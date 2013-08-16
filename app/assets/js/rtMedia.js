var rtMagnificPopup;
var rtMediaHook = {
    hooks: [],
    register: function(name, callback) {
        if ('undefined' == typeof(rtMediaHook.hooks[name]))
            rtMediaHook.hooks[name] = []
        rtMediaHook.hooks[name].push(callback)
    },
    call: function(name, arguments) {
        if ('undefined' != typeof(rtMediaHook.hooks[name]))
            for (i = 0; i < rtMediaHook.hooks[name].length; ++i)
                if (true != rtMediaHook.hooks[name][i](arguments)) {
                    break;
                }
    }
}
jQuery('document').ready(function($) {

    $("#rt_media_comment_form").submit(function(e) {
        if ($.trim($("#comment_content").val()) == "") {
            alert("Empty Comment is not allowed");
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
        rtMagnificPopup = jQuery('.rtmedia-list-media, .rtmedia-activity-container ul.rtmedia-list, #bp-media-list,.widget-item-listing,.bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content').magnificPopup({
            delegate: 'a:not(".no-popup")',
            type: 'ajax',
            tLoading: 'Loading media #%curr%...',
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
                    console.log(e);
                },
                BeforeChange: function(e) {
                    console.log(e);
                }
            }
        });
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

    jQuery('.rtmedia-container').on('click', '.rtmedia-merge', function(e) {
        jQuery('.rtmedia-merge-container').slideToggle();
    });

    jQuery('.rtmedia-container').on('click', '.rtmedia-create-new-album-button', function(e) {
        jQuery('.rtmedia-create-new-album-container').slideToggle();
    });

    jQuery('.rtmedia-container').on('click', '#rtmedia_create_new_album', function(e) {
        $albumname = jQuery.trim(jQuery('#rtmedia_album_name').val());
        $context = jQuery.trim(jQuery('#rtmedia_album_context').val());
        $context_id = jQuery.trim(jQuery('#rtmedia_album_context_id').val());
        if ($albumname != '') {
            var data = {
                action: 'rtmedia_create_album',
                name: $albumname,
                context: $context,
                context_id: $context_id
            };

            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            $("#rtmedia_create_new_album").attr('disabled', 'disabled');
            var old_val = $("#rtmedia_create_new_album").html();
            $("#rtmedia_create_new_album").prepend("<img src='" + rMedia_loading_file + "'/>");
            jQuery.post(rtmedia_ajax_url, data, function(response) {
                if (response) {
                    jQuery('.rtmedia-user-album-list').append('<option value="' + response + '">' + $albumname + '</option>');
                    jQuery('select.rtmedia-user-album-list option[value="' + response + '"]').prop('selected', true);
                    jQuery('.rtmedia-create-new-album-container').slideToggle();
                    jQuery('#rtmedia_album_name').val("");
                    jQuery(".rtmedia-create-new-album-button").after("<span class='rtmedia-success rtmedia-create-album-alert'><b>" + $albumname + "</b> album created.</span>");
                    setTimeout(function() {
                        jQuery(".rtmedia-create-album-alert").remove()
                    }, 4000);

                } else {
                    alert('Something went wrong. Please try again.');
                }
                $("#rtmedia_create_new_album").removeAttr('disabled');
                $("#rtmedia_create_new_album").html(old_val);
            });
        } else {
            alert('Enter an album name');
        }
    });

    jQuery('.rtmedia-container').on('click', '.rtmedia-delete-selected', function(e) {
        jQuery('.rtmedia-bulk-actions').attr('action', '../../../media/delete');
    });

    jQuery('.rtmedia-container').on('click', '.rtmedia-move-selected', function(e) {
        jQuery('.rtmedia-bulk-actions').attr('action', '');
    });

});

//Legacy media element for old activities
function bp_media_create_element(id) {
    return false;
}