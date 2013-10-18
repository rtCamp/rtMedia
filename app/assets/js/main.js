/*
 * BuddyPress Media Default JS
 */
//Legacy media element for old activities
function bp_media_create_element(id) {
    return false;
}
var $current;
//window.onbeforeunload= function() { return "Custom message here"; };

jQuery(document).ready(function() {

    jQuery('body').on('mediapreview', '.bp_media_content video,.bp_media_content audio, video.bp-media-featured-media, audio.bp-media-featured-media', function() {
        jQuery(this).mediaelementplayer({
            enableKeyboard: false,
            startVolume: 1,
            // if the <video width> is not specified, this is the default
            defaultVideoWidth: 480,
            // if the <video height> is not specified, this is the default
            defaultVideoHeight: 270,
            // if set, overrides <video width>
            videoWidth: 1,
            // if set, overrides <video height>
            videoHeight: 1,
            success: function(mediaElement, domElement) {
                var $thisMediaElement = (mediaElement.id) ? jQuery("#" + mediaElement.id) : jQuery(mediaElement);
                $thisMediaElement.parents('.mejs-container').find(".mejs-volume-current").css("top", "8px");
                $thisMediaElement.parents('.mejs-container').find(".mejs-volume-handle").css("top", "5px");
            }
        });
    });
    jQuery('.bp_media_content video,.bp_media_content audio, video.bp-media-featured-media, audio.bp-media-featured-media').trigger('mediapreview');

    jQuery('ul#activity-stream').on('DOMNodeInserted', function() {
        jQuery('ul#activity-stream .bp_media_content video,ul#activity-stream .bp_media_content audio').trigger('mediapreview');
    });
    var $id, $idtxt;

    jQuery('body').on('click', '.bp-media-featured-media-button', function(e) {
        e.preventDefault();
        $idtxt = jQuery(this).closest('.bp-media-image').attr('id');
        $id = $idtxt.replace('bp-media-id-', '');
        data = {
            'media_id': $id,
            'action': 'bp_set_featured'
        }
        jQuery.get(ajaxurl, data, function(response) {
            if ($id == response) {
                jQuery('#' + $idtxt).find('.bp-media-featured-media-button').remove();
            }
        });
    })


    jQuery('#item-body').on('click', '#bp-media-upload-button', function() {
        jQuery('#bp-media-move-merge-ui').slideUp();
        jQuery('#bp-media-delete-ui').slideUp();
        jQuery('#bp-media-list input').remove();
        jQuery('#bp-media-show-more').attr('data-move', 0);
        $parent = jQuery('.bp-media-album-actions');
        $wrapper = jQuery('.bp-media-upload-wrapper');
        $description = jQuery('.bp-media-album-description');
        if ($parent.length > 0 && $wrapper.length <= 0) {
            if ($description.length > 0)
                $description.after('<div class="bp-media-action-wrapper bp-media-upload-wrapper"></div>');
            else
                $parent.after('<div class="bp-media-action-wrapper bp-media-upload-wrapper"></div>');
            jQuery('#bp-media-upload-ui').appendTo('.bp-media-upload-wrapper');
        }
        jQuery('#bp-media-upload-ui').slideToggle();
    });

    jQuery('#item-body').on('click', '#bp-media-move-merge-button', function() {
        jQuery('#bp-media-upload-ui').slideUp();
        jQuery('#bp-media-delete-ui').slideUp();
        $parent = jQuery('.bp-media-album-actions');
        $wrapper = jQuery('.bp-media-move-merge-wrapper');
        $description = jQuery('.bp-media-album-description');
        if ($parent.length > 0 && $wrapper.length <= 0) {
            if ($description.length > 0)
                $description.after('<div class="bp-media-action-wrapper bp-media-move-merge-wrapper"></div>');
            else
                $parent.after('<div class="bp-media-action-wrapper bp-media-move-merge-wrapper"></div>');
            jQuery('#bp-media-move-merge-ui').appendTo('.bp-media-move-merge-wrapper');
        }
        jQuery('#bp-media-move-merge-ui').slideToggle('slow', function() {
            if (jQuery(this).css('display') == 'none' || jQuery('#bp-media-move-merge-select option:checked').val() == 'merge') {
                jQuery('#bp-media-list input').remove();
                jQuery('#bp-media-show-more').attr('data-move', 0);
            } else if (!jQuery('#bp-media-list input').length) {
                jQuery('#bp-media-show-more').attr('data-move', 1);
                jQuery('#bp-media-list h3').each(function() {
                    $media_id = jQuery(this).parent().attr('id').replace('bp-media-item-', '');
                    jQuery(this).prepend('<input type="checkbox" name="move" value="' + $media_id + '" />');
                });
            }
        });
    });

    jQuery('#item-body').on('click', '#bp-media-delete-button', function() {
        jQuery('#bp-media-upload-ui').slideUp();
        jQuery('#bp-media-move-merge-ui').slideUp();
        $parent = jQuery('.bp-media-album-actions');
        $wrapper = jQuery('.bp-media-delete-wrapper');
        $description = jQuery('.bp-media-album-description');
        if ($parent.length > 0 && $wrapper.length <= 0) {
            if ($description.length > 0)
                $description.after('<div class="bp-media-action-wrapper bp-media-delete-wrapper"></div>');
            else
                $parent.after('<div class="bp-media-action-wrapper bp-media-delete-wrapper"></div>');
            jQuery('#bp-media-delete-ui').appendTo('.bp-media-delete-wrapper');
        }
        jQuery('#bp-media-delete-ui').slideToggle('slow', function() {
            if (jQuery(this).css('display') == 'none') {
                jQuery('#bp-media-list input').remove();
                jQuery('#bp-media-show-more').attr('data-move', 0);
            } else if (!jQuery('#bp-media-list input').length) {
                jQuery('#bp-media-show-more').attr('data-move', 1);
                jQuery('#bp-media-list h3').each(function() {
                    $media_id = jQuery(this).parent().attr('id').replace('bp-media-item-', '');
                    jQuery(this).prepend('<input type="checkbox" name="move" value="' + $media_id + '" />');
                });
            }
        });
    });

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

    jQuery('#bp-media-move-merge-ui').on('change', '#bp-media-move-merge-select', function() {
        $this = jQuery(this);
        if ($this.val() == 'move') {
            if (!jQuery('#bp-media-list input').length) {
                jQuery('#bp-media-list h3').each(function() {
                    $media_id = jQuery(this).parent().attr('id').replace('bp-media-item-', '');
                    jQuery(this).prepend('<input type="checkbox" name="move" value="' + $media_id + '" />');
                });
            }
            jQuery('#bp-media-show-more').attr('data-move', 1);
            jQuery('.bp-media-move-selected-checks').fadeIn();
        } else if ($this.val() == 'merge') {
            jQuery('.bp-media-move-selected-checks').fadeOut();
            jQuery('#bp-media-list input').remove();
            jQuery('#bp-media-show-more').attr('data-move', 0)
        }
    });

    jQuery('#bp-media-move-merge-ui').on('click', '#bp-media-move-merge-media', function() {
        jQuery(this).siblings('.bp-media-ajax-spinner').show();
        jQuery(this).prop('disabled', true);
        jQuery(this).addClass('disabled');
        $val = jQuery('#bp-media-move-merge-select option:checked').val();
        if ($val == 'merge') {
            if (confirm(bp_media_main_strings.merge_confirmation)) {
                $delete_album = false;
                //                if ( jQuery('.bp-media-can-delete').length ) {
                //                    if(confirm(bp_media_main_strings.delete_after_merge))
                //                        $delete_album = true;
                //                }
                $from = jQuery('#bp-media-selected-album').val();
                $to = jQuery('.bp-media-selected-album-move-merge option:checked').val();
                if ($from && $to) {
                    var data = {
                        action: 'bp_media_merge_album',
                        from: $from,
                        to: $to,
                        delete_album: $delete_album
                    };
                    jQuery.post(bp_media_vars.ajaxurl, data, function(response) {
                        if (response.length == 0) {
                            jQuery('.item-list-tabs:last').after('<div id="message" class="error"><p>' + bp_media_main_strings.something_went_wrong + '</p></div>');
                        } else if (response == 'redirect') {
                            window.location = window.location.href.replace($from, $to);
                        } else {
                            location.reload();
                        }
                    });
                }
            } else {
                jQuery(this).siblings('.bp-media-ajax-spinner').hide();
                jQuery(this).prop('disabled', false);
                jQuery(this).removeClass('disabled');
                return false;
            }
        } else if ($val == 'move') {
            $media = new Array();
            jQuery('input:checkbox[name="move"]:checked').each(function() {
                $media.push(jQuery(this).val());
            });
            if ($media.length) {
                if (confirm(bp_media_main_strings.are_you_sure)) {
                    var data = {
                        action: 'bp_media_move_selected_media',
                        media: $media,
                        parent: jQuery('.bp-media-selected-album-move-merge option:checked').val()
                    };
                    jQuery.post(bp_media_vars.ajaxurl, data, function(response) {
                        if (response.length == 0) {
                            jQuery('.item-list-tabs:last').after('<div id="message" class="error"><p>' + bp_media_main_strings.something_went_wrong + '</p></div>');
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    jQuery(this).siblings('.bp-media-ajax-spinner').hide();
                    jQuery(this).prop('disabled', false);
                    jQuery(this).removeClass('disabled');
                }
            } else {
                alert(bp_media_main_strings.select_media);
                jQuery(this).siblings('.bp-media-ajax-spinner').hide();
                jQuery(this).prop('disabled', false);
                jQuery(this).removeClass('disabled');
            }
        } else {
            alert(bp_media_main_strings.select_action);
            jQuery(this).siblings('.bp-media-ajax-spinner').hide();
            jQuery(this).prop('disabled', false);
            jQuery(this).removeClass('disabled');
            return false;
        }

    });

    jQuery('#bp-media-delete-ui').on('click', '#bp-media-delete-media', function() {
        jQuery(this).siblings('.bp-media-ajax-spinner').show();
        jQuery(this).prop('disabled', true);
        jQuery(this).addClass('disabled');
        $media = new Array();
        jQuery('input:checkbox[name="move"]:checked').each(function() {
            $media.push(jQuery(this).val());
        });
        if ($media.length) {
            if (confirm(bp_media_main_strings.delete_selected_media)) {
                var data = {
                    action: 'bp_media_delete_selected_media',
                    media: $media
                };
                jQuery.post(bp_media_vars.ajaxurl, data, function(response) {
                    if (response.length == 0) {
                        jQuery('.item-list-tabs:last').after('<div id="message" class="error"><p>' + bp_media_main_strings.something_went_wrong + '</p></div>');
                    } else {
                        location.reload();
                    }
                });
            } else {
                jQuery(this).siblings('.bp-media-ajax-spinner').hide();
                jQuery(this).prop('disabled', false);
                jQuery(this).removeClass('disabled');
                return false;
            }
        } else {
            alert(bp_media_main_strings.select_media);
            jQuery(this).siblings('.bp-media-ajax-spinner').hide();
            jQuery(this).prop('disabled', false);
            jQuery(this).removeClass('disabled');
        }
    });

    jQuery('#bp-media-upload-ui').bind('dragover', function(e) {
        jQuery(this).addClass('hover');
        return 0;
    });
    jQuery('#bp-media-upload-ui').bind('dragleave', function(e) {
        jQuery(this).removeClass('hover');
        return 0;
    });

    var bp_media_recent_tabs = jQuery('.media-tabs-container-tabs');
    if (bp_media_recent_tabs.length > 0) {
        jQuery(bp_media_recent_tabs).tabs();
    }

    var tallest = 0;
    jQuery('#recent-media-tabs .bp-media-tab-panel').each(function() {

        var thisHeight = jQuery(this).height();
        if (thisHeight > tallest) {
            tallest = thisHeight;
        }
    }).height(tallest);


    jQuery('#bp-media-show-more').click(function(e) {
        e.preventDefault();
        var data = load_more_data();
        jQuery.get(bp_media_vars.ajaxurl, data, function(response) {
            if (response.length == 0)
                jQuery('#bp-media-show-more').parent().remove();
            else
                jQuery('.bp-media-gallery').append(response);
        });
    });

    jQuery('#bp-media-show-more-sc').click(function(e) {
        e.preventDefault();
        $this = jQuery(this);
        $this.prop("disabled", true);
        var data = {
            action: 'bp_media_load_more_sc',
            page: parseInt($this.attr('data-page')) + 1,
            media: $this.attr('data-media'),
            count: $this.attr('data-count'),
            title: $this.attr('data-title')
        };
        jQuery.get(bp_media_vars.ajaxurl, data, function(response) {
            if (response.length == 0) {
                jQuery('#bp-media-show-more-sc').parent().remove();
            } else {
                $this.prop("disabled", false);
                $this.attr('data-page', parseInt($this.attr('data-page')) + 1);
                jQuery('.bp-media-gallery').append(response);
            }
        });
    });
    setTimeout(function() {
        jQuery('.media album_updated .delete-activity,.media_upload .delete-activity').unbind('click').click(function(e) {
            if (confirm(bp_media_main_strings.delete_activity_media)) {
                return true;
            }
            else {
                return false;
            }
        });

    }, 1000);

    /* Add Featured Image */
    jQuery('.bp-media-image').on('click', '.bp-media-featured', function(e) {
        e.preventDefault();
        var post_id = jQuery(this).attr('data-post-id');
        var album_id = jQuery(this).attr('data-album-id');
        var curr_obj = jQuery(this);
        var data = {
            action: 'bp_media_set_album_cover',
            post_id: post_id,
            album_id: album_id
        };
        jQuery.get(bp_media_vars.ajaxurl, data, function(response)
        {
            curr_obj.text(response);
            curr_obj.attr('title', response);
        }
        );
    });

    if (bp_media_vars.lightbox > 0 && !(/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent))) {

        jQuery('#bp-media-list,.widget-item-listing,.bp-media-sc-list').on('click', 'li a', function(e) {
            e.preventDefault();
            $current = jQuery(this);
            load_media($current);
        });
        jQuery('ul#activity-stream').on('click',
                'li.media.album_updated ul li a,	ul.bp-media-list-media li a, li.activity-item div.activity-content div.activity-inner div.bp_media_content a', function(e) {
            e.preventDefault();
            $current = jQuery(this);
            load_media($current);
        });
        jQuery('body').on('click', 'a.modal-next', function(e) {
            e.preventDefault();
            if (!$current.parent().hasClass('bp_media_content')) {
                $next_current = $current.closest('li').next().find('a');
                if ($next_current.length < 1) {
                    if (jQuery('#bp-media-show-more').length > 0) {
                        var args = load_more_data();
                        var request = jQuery.get(bp_media_vars.ajaxurl, args);
                        chained = request.then(function(data) {
                            if (data.length == 0) {
                                jQuery('#bp-media-show-more').parent().remove();
                                return false;
                            } else {
                                jQuery('#bp-media-list').append(data);
                                return true;
                            }
                        });

                        chained.done(function(truth) {
                            if (truth != false) {
                                $next_current = $current.closest('li').next().find('a');
                                $current = $next_current;
                                transit_media($current);
                            }
                        });
                    }
                } else {
                    $current = $next_current;
                    transit_media($next_current);
                }
            }


        });
        jQuery('body').on('click', 'a.modal-prev', function(e) {
            e.preventDefault();
            if (!$current.parent().hasClass('bp_media_content')) {
                if ($current.closest('li').prev().length > 0 && $current.closest('li').prev().find('#bp-media-upload-ui').length < 1) {
                    $current = $current.closest('li').prev().find('a');

                    transit_media($current);
                }
            }
        });
        jQuery(document.documentElement).keyup(function(event) {
            if (event.keyCode == 37) {
                jQuery('a.modal-prev').trigger('click');
            } else if (event.keyCode == 39) {
                jQuery('a.modal-next').trigger('click');
            }
        });

        function load_media($current) {
            jQuery.get($current.attr('href'), function(response) {
                $mediacontent = jQuery(response).find('.bp-media-single');
                $medialoaded = jQuery('<div class="bp-media-ajax-single"></div>');
                $medialoaded.append($mediacontent);
                jQuery.modal($medialoaded, {
                    'zIndex': 99999,
                    'autoResize': true,
                    'opacity': 90
                });
                do_fixes($medialoaded);
                jQuery('.bp_media_content video,.bp_media_content audio').trigger('mediapreview');
            });
        }
        function transit_media($current) {
            $medialoaded = jQuery('.bp-media-ajax-single');
            $medialoaded.empty();
            $medialoaded.append(jQuery('<div class="lightbox-spinner" />'));
            jQuery.get($current.attr('href'), function(response) {
                $mediacontent = jQuery(response).find('.bp-media-single');
                $medialoaded = jQuery('.bp-media-ajax-single');
                $medialoaded.empty();
                $medialoaded.append($mediacontent);
                do_fixes($medialoaded);
                jQuery('.bp_media_content video,.bp_media_content audio').trigger('mediapreview');
            });
        }

        function do_fixes($medialoaded) {
            $medialoaded.find('.bp-media-content-wrap').append('<a class="modal-prev modal-ctrl"><span class="img-icon"></span></a><a class="modal-next modal-ctrl"><span class="img-icon"></span></a>');
            $medialoaded.find('.bp_media_description').remove();
            $image = $medialoaded.find('.bp-media-content-wrap .bp_media_content img');
            if ($image.length < 1) {
                $image = $medialoaded.find('.bp-media-content-wrap .bp_media_content video');
                $dimensions = adjust_dimensions($image);
                adjust_comment_div($dimensions[0]);
                jQuery.modal.update($dimensions[0], $dimensions[1]);
            }
            $form = $medialoaded.find('form.ac-form');
            if ($form.length > 0) {
                $form.find('.ac-reply-avatar').remove();
                $form.html($form.html().replace('&nbsp; or press esc to cancel.', ''));
            }
            $image.load(function() {
                $dimensions = adjust_dimensions($image);
                adjust_comment_div($dimensions[0]);
                jQuery.modal.update($dimensions[0], $dimensions[1]);
            })
        }

        function adjust_dimensions($image) {
            $height = ($image.height() > 480) ? $image.height() : 480;
            $width = ($image.width() > 640) ? $image.width() : 640;
            $width = $width + 280;
            $image.hide();
            $image.show();
            return [$height, $width];

        }

        function adjust_comment_div($height) {
            $medialoaded.find('.bp-media-meta-content-wrap').css({
                'height': $height,
                'overflow': 'auto'
            });
        }
        function load_more_data() {
            if (jQuery('#bp-media-show-more').attr('data-move') == 1)
                $move = 1;
            else
                $move = 0;
            var data = {
                action: 'bp_media_load_more',
                page: ++bp_media_vars.page,
                current_action: bp_media_vars.current_action,
                action_variables: bp_media_vars.action_variables,
                displayed_user: bp_media_vars.displayed_user,
                loggedin_user: bp_media_vars.loggedin_user,
                current_group: bp_media_vars.current_group,
                move: $move
            };
            return data;
        }

        /**** Activity Comments *******************************************************/

        /* Hide all activity comment forms */
        jQuery('form.ac-form').hide();

        /* Hide excess comments */
        //	if ( jQuery('.activity-comments').length )
        //		bp_legacy_theme_hide_comments();

        jQuery('.bp-media-image-editor').bind('DOMNodeInserted DOMNodeRemoved', function(event) {
            $id = jQuery('.bp-media-image-editor').attr('id').replace('image-editor-', '');
            if (!jQuery('#imgedit-save-target-' + $id).length) {
                jQuery('#imgedit-y-' + $id).after('<p id="imgedit-save-target-' + $id + '" style="display: none;"><input type="checkbox" style="display:none;" checked="checked" name="imgedit-target-' + $id + '" value="all"></p>');
            }
        });

        /* Activity list event delegation */
        jQuery('body').on('click', '.bp-media-ajax-single div.activity', function(event) {
            var target = jQuery(event.target);
            if (target.hasClass('bp-media-featured')) {
                var post_id = target.attr('data-post-id');
                var album_id = target.attr('data-album-id');
                var data = {
                    action: 'bp_media_set_album_cover',
                    post_id: post_id,
                    album_id: album_id
                };
                target.addClass('loading');
                jQuery.get(bp_media_vars.ajaxurl, data, function(response)
                {
                    target.removeClass('loading');
                    target.fadeOut(200, function() {
                        jQuery(this).html(response);
                        jQuery(this).attr('title', response);
                        jQuery(this).fadeIn(200);
                    });

                }
                );
            }

            /* Favoriting activity stream items */
            if (target.hasClass('fav') || target.hasClass('unfav')) {
                event.preventDefault();
                var type = target.hasClass('fav') ? 'fav' : 'unfav';
                var parent = target.closest('.activity_update');
                var parent_id = parent.attr('id').substr(9, parent.attr('id').length);

                target.addClass('loading');

                jQuery.post(ajaxurl, {
                    action: 'activity_mark_' + type,
                    'cookie': encodeURIComponent(document.cookie),
                    'id': parent_id
                },
                function(response) {
                    target.removeClass('loading');

                    target.fadeOut(200, function() {
                        jQuery(this).html(response);
                        jQuery(this).attr('title', 'fav' == type ? BP_DTheme.remove_fav : BP_DTheme.mark_as_fav);
                        jQuery(this).fadeIn(200);
                    });

                    if ('fav' == type) {
                        if (!jQuery('.item-list-tabs #activity-favorites').length)
                            jQuery('.item-list-tabs ul #activity-mentions').before('<li id="activity-favorites"><a href="#">' + BP_DTheme.my_favs + ' <span>0</span></a></li>');

                        target.removeClass('fav');
                        target.addClass('unfav');

                        jQuery('.item-list-tabs ul #activity-favorites span').html(Number(jQuery('.item-list-tabs ul #activity-favorites span').html()) + 1);
                    } else {
                        target.removeClass('unfav');
                        target.addClass('fav');

                        jQuery('.item-list-tabs ul #activity-favorites span').html(Number(jQuery('.item-list-tabs ul #activity-favorites span').html()) - 1);

                        if (!Number(jQuery('.item-list-tabs ul #activity-favorites span').html())) {
                            if (jQuery('.item-list-tabs ul #activity-favorites').hasClass('selected'))
                                bp_activity_request(null, null);

                            jQuery('.item-list-tabs ul #activity-favorites').remove();
                        }
                    }

                    if ('activity-favorites' == jQuery('.item-list-tabs li.selected').attr('id'))
                        target.parent().parent().parent().slideUp(100);
                });

                return false;
            }
            /* Comment / comment reply links */
            if (target.hasClass('acomment-reply') || target.parent().hasClass('acomment-reply')) {
                if (target.parent().hasClass('acomment-reply'))
                    target = target.parent();

                var id = target.attr('id');
                ids = id.split('-');

                var a_id = ids[2]
                var c_id = target.attr('href').substr(10, target.attr('href').length);
                var form = jQuery('.bp-media-ajax-single #ac-form-' + a_id);

                form.css('display', 'none');
                form.removeClass('root');
                jQuery('.ac-form').hide();

                /* Hide any error messages */
                form.children('div').each(function() {
                    if (jQuery(this).hasClass('error'))
                        jQuery(this).hide();
                });


                if (ids[1] != 'comment') {
                    jQuery('.bp-media-ajax-single #acomment-' + c_id).append(form);
                } else {
                    jQuery('.bp-media-ajax-single #activity-' + a_id + ' .activity-comments').append(form);
                }

                if (form.parent().hasClass('activity-comments'))
                    form.addClass('root');

                form.slideDown(200);
                jQuery.scrollTo(form, 500, {
                    offset: -100,
                    easing: 'easeOutQuad'
                });
                jQuery('.bp-media-ajax-single #ac-form-' + ids[2] + ' textarea').focus();

                return false;
            }

            /* Activity comment posting */
            if (target.attr('name') == 'ac_form_submit') {
                var form = target.closest('form');
                var form_parent = form.parent();
                var form_id = form.attr('id').split('-');

                if (!form_parent.hasClass('activity-comments')) {
                    var tmp_id = form_parent.attr('id').split('-');
                    var comment_id = tmp_id[1];
                } else {
                    var comment_id = form_id[2];
                }

                var content = jQuery(target.closest('.ac-reply-content').find('textarea'));
//                var content = jQuery( target.closest())'#' + form.attr('id') + ' textarea' );

                /* Hide any error messages */
                jQuery('.bp-media-ajax-single #' + form.attr('id') + ' div.error').hide();
                target.addClass('loading').prop('disabled', true);
                content.addClass('loading').prop('disabled', true);

                var ajaxdata = {
                    action: 'new_activity_comment',
                    'cookie': encodeURIComponent(document.cookie),
                    '_wpnonce_new_activity_comment': jQuery("#_wpnonce_new_activity_comment").val(),
                    'comment_id': comment_id,
                    'form_id': form_id[2],
                    'content': content.val()
                };

                // Akismet
                var ak_nonce = jQuery('#_bp_as_nonce_' + comment_id).val();
                if (ak_nonce) {
                    ajaxdata['_bp_as_nonce_' + comment_id] = ak_nonce;
                }

                jQuery.post(ajaxurl, ajaxdata, function(response) {
                    target.removeClass('loading');
                    content.removeClass('loading');

                    /* Check for errors and append if found. */
                    if (response[0] + response[1] == '-1') {
                        form.append(jQuery(response.substr(2, response.length)).hide().fadeIn(200));
                    } else {
                        form.fadeOut(200, function() {
                            form_parent_id = jQuery('#' + form.parent().attr('id'));
                            if (0 == form.parent().children('ul').length) {
                                if (form.parent().hasClass('activity-comments')) {
                                    form_parent_id.prepend('<ul></ul>');
                                } else {
                                    form_parent_id.parent().append('<ul></ul>');
                                }
                            }

                            /* Preceeding whitespace breaks output with jQuery 1.9.0 */
                            var the_comment = jQuery.trim(response);
                            //var addnl_comment = jQuery.trim( response );

                            //form.parent().children('ul').append( jQuery( the_comment ).hide().fadeIn( 200 ) );
                            form_parent_id.children('ul').append(jQuery(the_comment).hide().fadeIn(200));

                            form.children('textarea').val('');
                            form.parent().parent().addClass('has-comments');
                        });
                        jQuery('.bp-media-ajax-single #' + form.attr('id') + ' textarea').val('');

                        /* Increase the "Reply (X)" button count */
                        jQuery('.bp-media-ajax-single #activity-' + form_id[2] + ' a.acomment-reply span').html(Number(jQuery('#activity-' + form_id[2] + ' a.acomment-reply span').html()) + 1);
                    }

                    jQuery(target).prop("disabled", false);
                    jQuery(content).prop("disabled", false);
                });

                return false;
            }

            /* Deleting an activity comment */
            if (target.hasClass('acomment-delete')) {
                var link_href = target.attr('href');
                var comment_li = target.parent().parent();
                var form = comment_li.parents('div.activity-comments').children('form');

                var nonce = link_href.split('_wpnonce=');
                nonce = nonce[1];

                var comment_id = link_href.split('cid=');
                comment_id = comment_id[1].split('&');
                comment_id = comment_id[0];

                target.addClass('loading');

                /* Remove any error messages */
                jQuery('.activity-comments ul .error').remove();

                /* Reset the form position */
                comment_li.parents('.activity-comments').append(form);

                jQuery.post(ajaxurl, {
                    action: 'delete_activity_comment',
                    'cookie': encodeURIComponent(document.cookie),
                    '_wpnonce': nonce,
                    'id': comment_id
                },
                function(response) {
                    /* Check for errors and append if found. */
                    if (response[0] + response[1] == '-1') {
                        comment_li.prepend(jQuery(response.substr(2, response.length)).hide().fadeIn(200));
                    } else {
                        var children = jQuery('#' + comment_li.attr('id') + ' ul').children('li');
                        var child_count = 0;
                        jQuery(children).each(function() {
                            if (!jQuery(this).is(':hidden'))
                                child_count++;
                        });
                        comment_li.fadeOut(200);

                        /* Decrease the "Reply (X)" button count */
                        var count_span = jQuery('#' + comment_li.parents('#activity-stream > li').attr('id') + ' a.acomment-reply span');
                        var new_count = count_span.html() - (1 + child_count);
                        count_span.html(new_count);

                        /* If that was the last comment for the item, remove the has-comments class to clean up the styling */
                        if (0 == new_count) {
                            jQuery(comment_li.parents('#activity-stream > li')).removeClass('has-comments');
                        }
                    }
                });

                return false;
            }

            // Spam an activity stream comment
            if (target.hasClass('spam-activity-comment')) {
                var link_href = target.attr('href');
                var comment_li = target.parent().parent();

                target.addClass('loading');

                // Remove any error messages
                jQuery('.activity-comments ul div.error').remove();

                // Reset the form position
                comment_li.parents('.activity-comments').append(comment_li.parents('.activity-comments').children('form'));

                jQuery.post(ajaxurl, {
                    action: 'bp_spam_activity_comment',
                    'cookie': encodeURIComponent(document.cookie),
                    '_wpnonce': link_href.split('_wpnonce=')[1],
                    'id': link_href.split('cid=')[1].split('&')[0]
                },
                function(response) {
                    // Check for errors and append if found.
                    if (response[0] + response[1] == '-1') {
                        comment_li.prepend(jQuery(response.substr(2, response.length)).hide().fadeIn(200));

                    } else {
                        var children = jQuery('#' + comment_li.attr('id') + ' ul').children('li');
                        var child_count = 0;
                        jQuery(children).each(function() {
                            if (!jQuery(this).is(':hidden')) {
                                child_count++;
                            }
                        });
                        comment_li.fadeOut(200);

                        // Decrease the "Reply (X)" button count
                        var parent_li = comment_li.parents('#activity-stream > li');
                        jQuery('#' + parent_li.attr('id') + ' a.acomment-reply span').html(jQuery('#' + parent_li.attr('id') + ' a.acomment-reply span').html() - (1 + child_count));
                    }
                });

                return false;
            }

            /* Showing hidden comments - pause for half a second */
            if (target.parent().hasClass('show-all')) {
                target.parent().addClass('loading');

                setTimeout(function() {
                    target.parent().parent().children('li').fadeIn(200, function() {
                        target.parent().remove();
                    });
                }, 600);

                return false;
            }
        });

    }

    jQuery('.rtmedia-item-thumbnail a').magnificPopup({type: 'ajax'});

});

