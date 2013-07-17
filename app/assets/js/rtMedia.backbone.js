var galleryObj;
var nextpage = 2;
var upload_sync = false;
var activity_id = -1;

jQuery(function($) {


    rtMedia = window.rtMedia || {};

    rtMedia = window.rtMedia || {};

    rtMedia.Context = Backbone.Model.extend({
        url: function() {
            var url = "media/";
            if (!upload_sync && nextpage > 0)
                url += 'pg/' + nextpage + '/'
            return url;
        },
        defaults: {
            "context": "post",
            "context_id": false
        }
    });

    rtMedia.Media = Backbone.Model.extend({
        defaults: {
            "id": 0,
            "blog_id": false,
            "media_id": false,
            "media_author": false,
            "media_title": false,
            "album_id": false,
            "media_type": "photo",
            "activity_id": false,
            "privacy": 0,
            "views": 0,
            "downloads": 0,
            "ratings_average": 0,
            "ratings_total": 0,
            "ratings_count": 0,
            "likes": 0,
            "dislikes": 0,
            "guid": false,
            "width": 0,
            "height": 0,
            "rt_permalink": false
        //			"next"			: -1,
        //			"prev"			: -1
        }

    });

    rtMedia.Gallery = Backbone.Collection.extend({
        model: rtMedia.Media,
        url: function() {
            var temp = window.location.pathname;
            var url = '';
            if (temp.indexOf('media') == -1) {
                url = 'media/';
            } else {
                if (temp.indexOf('pg/') == -1)
                    url = temp;
                else
                    url = window.location.pathname.substr(0, window.location.pathname.lastIndexOf("pg/"));
            }
            if (!upload_sync && nextpage > 1) {
                if (url.substr(url.length - 1) != "/")
                    url += "/"
                url += 'pg/' + nextpage + '/';
            }
            return url;
        },
        getNext: function(page) {
            this.fetch({
                data: {
                    json: true,
                    rtmedia_page: nextpage
                },
                success: function(model, response) {
                    nextpage = response.next;
                    var galleryViewObj = new rtMedia.GalleryView({
                        collection: new rtMedia.Gallery(response.data),
                        el: $(".rtmedia-list")[0]
                        });
                }
            });
        },
        reloadView: function() {
            upload_sync = true;
            this.getNext();
        }


    });

    rtMedia.MediaView = Backbone.View.extend({
        tagName: 'li',
        className: 'rtmedia-list-item',
        initialize: function() {
            this.template = _.template($("#rtmedia-gallery-item-template").html());
            this.model.bind('change', this.render);
            this.model.bind('remove', this.unrender);
            this.render();
        },
        render: function() {
            $(this.el).html(this.template(this.model.toJSON()));
            return this.el;
        },
        unrender: function() {
            $(this.el).remove();
        },
        remove: function() {
            this.model.destroy();
        }
    });

    rtMedia.GalleryView = Backbone.View.extend({
        tagName: 'ul',
        className: 'rtmedia-list',
        initialize: function() {
            this.template = _.template($("#rtmedia-gallery-item-template").html());
            this.render();
        },
        render: function() {

            that = this;

            if (upload_sync) {
                $(that.el).html('');
            }

            $.each(this.collection.toJSON(), function(key, media) {
                $(that.el).append(that.template(media));
            });
            if (upload_sync) {
                upload_sync = false;
            }
            if (nextpage > 1) {
                $("#rtMedia-galary-next").show();
            }


        },
        appendTo: function(media) {
            console.log("append");
            var mediaView = new rtMedia.MediaView({
                model: media
            });
            $(this.el).append(mediaView.render().el);
        }
    });


    galleryObj = new rtMedia.Gallery();

    $("body").append('<script id="rtmedia-gallery-item-template" type="text/template"></script>');
    var o_is_album,o_is_edit_allowed;
    if (typeof(is_album) == "undefined"){
        o_is_album=new Array("");
    } else {
        o_is_album = is_album
    }
    if(typeof(is_edit_allowed) == "undefined") {
        o_is_edit_allowed=new Array("")
    } else{
        o_is_edit_allowed = is_edit_allowed;
    }
        $("#rtmedia-gallery-item-template").load(template_url, {
            backbone: true, 
            is_album: o_is_album, 
            is_edit_allowed: o_is_edit_allowed
        }, function(response, status, xhr) {

        $(document).on("click", "#rtMedia-galary-next", function(e) {
                $(this).hide();
                e.preventDefault();

                galleryObj.getNext(nextpage);
            });
        });
    



    if (window.location.pathname.indexOf('media') != -1) {
        var tempNext = window.location.pathname.substring(window.location.pathname.lastIndexOf("page/") + 5, window.location.pathname.lastIndexOf("/"));
        if (isNaN(tempNext) === false) {
            nextpage = parseInt(tempNext) + 1;
        }
    }



    window.UploadView = Backbone.View.extend({
        events: {
            "click #rtMedia-start-upload": "uploadFiles"
        },
        initialize: function(config) {
            this.uploader = new plupload.Uploader(config);
        },
        render: function() {

        },
        initUploader: function() {
            this.uploader.init();
            //The plupload HTML5 code gives a negative z-index making add files button unclickable
            $(".plupload.html5").css({
                zIndex: 0
            });
            $("#rtMedia-upload-button").css({
                zIndex: 2
            });
            $("#rtMedia-upload-button").after("<span>(Max file size is " +  plupload.formatSize (this.uploader.settings.max_file_size) + ")</span>" )

            return this;
        },
        uploadFiles: function(e) {
            if (e != undefined)
                e.preventDefault();
            this.uploader.start();
            return false;
        }

    });



    if ($("#rtMedia-upload-button").length > 0) {
        var uploaderObj = new UploadView(rtMedia_plupload_config);

        uploaderObj.initUploader();

        uploaderObj.uploader.bind('UploadComplete', function(up, files) {
            activity_id = -1;
            galleryObj.reloadView();
        });

        uploaderObj.uploader.bind('FilesAdded', function(up, files) {
            var upload_size_error =false;
            var upload_error = "";
            var upload_error_sep = "";
            $.each(files, function(i, file) {
                if(uploaderObj.uploader.settings.max_file_size<file.size){
                    upload_size_error=true
                    upload_error += upload_error_sep + file.name;
                    upload_error_sep = ",";
                    var tr = "<tr style='background-color:lightpink;color:black' id='" +  file.id + "'><td>" + file.name+ "(" + plupload.formatSize(file.size) + ")" + "</td><td colspan='3'> Max file size is " + plupload.formatSize (uploaderObj.uploader.settings.max_file_size)+ "</td></tr>"
                    $("#rtMedia-queue-list tbody").append(tr)
                    return true;
                }
                tdName = document.createElement("td");
                tdName.innerHTML = file.name;
                tdStatus = document.createElement("td");
                tdStatus.className = "plupload_file_status";
                tdStatus.innerHTML = "0%";
                tdSize = document.createElement("td");
                tdSize.className = "plupload_file_size";
                tdSize.innerHTML = plupload.formatSize(file.size);
                tdDelete = document.createElement("td");
                tdDelete.innerHTML = "X";
                tdDelete.className = "plupload_delete"
                tr = document.createElement("tr");
                tr.id = file.id;
                tr.appendChild(tdName);
                tr.appendChild(tdStatus);
                tr.appendChild(tdSize);
                tr.appendChild(tdDelete);
                $("#rtMedia-queue-list").append(tr);
                //Delete Function
                $("#" + file.id + " td.plupload_delete").click(function(e) {
                    e.preventDefault();
                    uploaderObj.uploader.removeFile(uploader.getFile(file.id));
                    $("#" + file.id).remove();
                    return false;
                });

            });
            if(upload_size_error){
               // alert(upload_error + " because max file size is " + plupload.formatSize(uploaderObj.uploader.settings.max_file_size) );
            }
        });

        uploaderObj.uploader.bind('QueueChanged', function(up) {
            uploaderObj.uploadFiles()

        });

        uploaderObj.uploader.bind('UploadProgress', function(up, file) {
            $("#" + file.id + " .plupload_file_status").html(file.percent + "%");
            if(file.percent == 100){
                $("#" + file.id ).css("background-color","lightgreen");
                $("#" + file.id ).css("color","#000");
            }
        });
        uploaderObj.uploader.bind('BeforeUpload', function(up, file) {
            up.settings.multipart_params.privacy = $("#rtm-file_upload-ui select#privacy").val();
            up.settings.multipart_params.activity_id = activity_id;
            if ($('.rtmedia-user-album-list').length > 0)
                up.settings.multipart_params.album_id = $('.rtmedia-user-album-list').find(":selected").val();
            else if ( $('.rtmedia-current-album').length > 0 )
                up.settings.multipart_params.album_id = $('.rtmedia-current-album').val();
        });

        uploaderObj.uploader.bind('FileUploaded', function(up, file, res) {

            files = up.files;
            lastfile = files[files.length - 1];
            try {
                var rtnObj;
                rtnObj = JSON.parse(res.response);
                uploaderObj.uploader.settings.multipart_params.activity_id = rtnObj.activity_id;
                activity_id= rtnObj.activity_id;
            } catch (e) {
                console.log('Invalid Activity ID');
            }
        });

        $("#rtMedia-start-upload").click(function(e) {
            uploaderObj.uploadFiles(e);
        });
        $("#rtMedia-start-upload").hide();
    }


});
/** History Code for route

 var rtMediaRouter = Backbone.Router.extend({
 routes: {
 "media/*": "getMedia"
 }
 });
 var app_router = new rtMediaRouter;
 app_router.on('route:getMedia', function() {
 // Note the variable in the route definition being passed in here
 });
 Backbone.history.start({pushState: true});

 **/


/** Activity Update Js **/

jQuery(document).ready(function($) {
    if (typeof rtMedia_update_plupload_config == 'undefined'){
        return false;
    }
    var activity_attachemnt_ids = [];
    if ($("#rtmedia-add-media-button-post-update").length > 0) {
        $("#whats-new-options").prepend($("#rtmedia-action-update"));
        if($("#privacy").length > 0 ){
           $("#rtmedia-action-update").append($("#privacy")); 
        }
    }
    $("#whats-new-form").on('click', '#rtmedia-add-media-button-post-update', function(e) {
        $("#div-attache-rtmedia").toggle();
    })
    //whats-new-post-in
    var objUploadView = new UploadView(rtMedia_update_plupload_config);

    objUploadView.uploader.bind('FilesAdded', function(up, files) {
        //$("#aw-whats-new-submit").attr('disabled', 'disabled');
        $.each(files, function(i, file) {
            tdName = document.createElement("span");
            tdName.innerHTML = file.name;
            tdStatus = document.createElement("span");
            tdStatus.className = "plupload_file_status";
            tdStatus.innerHTML = "0%";
            tr = document.createElement("p");
            tr.id = file.id;
            tr.appendChild(tdName);
            tr.appendChild(tdStatus);
            $("#rtMedia-update-queue-list").append(tr);
        });
    });

    objUploadView.uploader.bind('FileUploaded', function(up, file, res) {
        if (res.status == 200) {
            try {
                var objIds = JSON.parse(res.response);
                $.each(objIds, function(key, val) {
                    activity_attachemnt_ids.push(val);
                    if ($("#whats-new-form").find("#rtmedia_attached_id_" + val).length < 1) {
                        $("#whats-new-form").append("<input type='hidden' name='rtMedia_attached_files[]' data-mode='rtMedia-update' id='rtmedia_attached_id_" + val + "' value='"
                            + val + "' />");
                    }
                });
            } catch (e) {

            }
        }
    });
    objUploadView.uploader.bind('BeforeUpload', function(up, files) {

        var object = '';
        var item_id = jq("#whats-new-post-in").val();
        if(item_id==undefined)
            item_id = 0;
        if ( item_id > 0 ) {
            object="group";
        }else{
            object="profile";
        }

        up.settings.multipart_params.context = object;
        up.settings.multipart_params.context_id = item_id;
        up.settings.multipart_params.privacy = jQuery("select#privacy").val();
    });
    objUploadView.uploader.bind('UploadComplete', function(up, files) {
        media_uploading=true;
        $("#aw-whats-new-submit").click();
    //$("#aw-whats-new-submit").removeAttr('disabled');
    });
    objUploadView.uploader.bind('UploadProgress', function(up, file) {
        $("#" + file.id + " .plupload_file_status").html(file.percent + "%");

    });

    $("#rtMedia-start-upload").hide();

    objUploadView.initUploader();
    var change_flag = false
    var media_uploading = false ;
    $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
        // Modify options, control originalOptions, store jqXHR, etc
        if(typeof(originalOptions.data) == "undefined" || typeof(originalOptions.data.action) =="undefined"){
            return true;
        }
        if (originalOptions.data.action == 'post_update') {
            var temp = activity_attachemnt_ids;
            while (activity_attachemnt_ids.length > 0) {
                options.data += "&rtMedia_attached_files[]=" + activity_attachemnt_ids.pop();
            }
            options.data += "&rtmedia-privacy="+jQuery("select#privacy").val();
            activity_attachemnt_ids = temp;
            var orignalSuccess = originalOptions.success ;
            options.beforeSend= function(){
                if($.trim($("#whats-new").val())==""){
                    alert("Please enter some content to post.");
                    $("#aw-whats-new-submit").prop("disabled", true).removeClass('loading');
                    return false;
                }
                if(! media_uploading && objUploadView.uploader.files.length > 0){
                    $("#whats-new-post-in").attr('disabled', 'disabled');
                    $("#rtmedia-add-media-button-post-update").attr('disabled', 'disabled');
                    objUploadView.uploadFiles()
                    media_uploading=true;
                    return false;
                }else{
                    media_uploading=false;
                    return true;
                }


            }
            options.success= function(response){
                orignalSuccess(response);
                if ( response[0] + response[1] == '-1' ) {
                //Error

                }else{
                    jQuery("input[data-mode=rtMedia-update]").remove();
                    while(objUploadView.uploader.files.pop()!= undefined){}
                    objUploadView.uploader.refresh()
                    $('#rtMedia-update-queue-list').html('');
                    $("#div-attache-rtmedia").hide();
                }
                $("#whats-new-post-in").removeAttr('disabled');
                $("#rtmedia-add-media-button-post-update").removeAttr('disabled');

            }
        }
    });
});
/**
 * rtMedia Comment Js
 */
jQuery(document).ready(function($) {
    jQuery(document).on("click",".mfp-content #rt_media_comment_form #rt_media_comment_submit",function(e){
        e.preventDefault();
        if($.trim($("#comment_content").val())==""){
            alert("Empty Comment is not allowed");
            return false;
        }
            
        $(this).attr('disabled', 'disabled');
        
        $.ajax({
                url: jQuery("#rt_media_comment_form").attr("action"),
                type: 'post',
                data: jQuery("#rt_media_comment_form").serialize() + "&rtajax=true",
                success: function (data) {
                    $(".mfp-content #rtmedia_comment_ul").append(data);
                    $("#comment_content").val("");
                    $(".mfp-content #rt_media_comment_form #rt_media_comment_submit").removeAttr('disabled');
                }
            });



        return false;
    });
    
    
    
    $(document).on("click",'.rtmedia-like',function(e){
        e.preventDefault();
        var that = this;
        $(this).attr('disabled', 'disabled');
        var url = $(this).parent().attr("action");
        $(that).prepend("<img src='" + rMedia_loading_file + "' />");
          $.ajax({
                url: url,
                type: 'post',
                data:"json=true",
                success: function (data) {
                    try{
                        data = JSON.parse(data);
                    }catch(e){
                        
                    }
                    $(that).html(data.next);
                    $(that).removeAttr('disabled');
                }
            });

        
    });    
    $(document).on("click",'.rtmedia-featured',function(e){
        e.preventDefault();
        var that = this;
        $(this).attr('disabled', 'disabled');
        var url = $(this).parent().attr("action");
        $(that).prepend("<img src='" + rMedia_loading_file +  "' />");
          $.ajax({
                url: url,
                type: 'post',
                data:"json=true",
                success: function (data) {
                    try{
                        data = JSON.parse(data);
                    }catch(e){
                        
                    }
                    $(that).html(data.next);
                    $(that).removeAttr('disabled');
                }
            });

        
    });    
});