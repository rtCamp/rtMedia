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
                    rt_media_page: nextpage
                },
                success: function(model, response) {
                    nextpage = response.next;
                    var galleryViewObj = new rtMedia.GalleryView({
                        collection: new rtMedia.Gallery(response.data),
                        el: $(".rt-media-list")[0]});
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
        className: 'rt-media-list-item',
        initialize: function() {
            this.template = _.template($("#rt-media-gallery-item-template").html());
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
        className: 'rt-media-list',
        initialize: function() {
            this.template = _.template($("#rt-media-gallery-item-template").html());
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

    $("body").append('<script id="rt-media-gallery-item-template" type="text/template"></script>');

    $("#rt-media-gallery-item-template").load(template_url + "/media-gallery-item.php", {action: 'rt_media_backbone_template', backbone: true}, function(response, status, xhr) {
/**
        $(document).on("click", "#rtMedia-galary-next", function(e) {
            $(this).hide();
            e.preventDefault();

            galleryObj.getNext(nextpage);
        }); **/
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
        initialize: function() {
            _.bindAll(this, "render");
        },
        render: function() {
            //$(this.el).html(this.template());
            return this;
        },
        initUploader: function() {
            var self = this;
            this.uploader = new plupload.Uploader(rtMedia_plupload_config);

            this.uploader.bind('Init', function(up) {
                var target = $("drag-drop-area");

                target.ondragenter = function() {
                    this.className = "dragover";
                };

                target.ondragleave = function() {
                    this.className = "";
                };

                target.ondrop = function() {
                    this.className = "";
                };

            });

            this.uploader.init();

            this.uploader.bind('UploadComplete', function(up, files) {
                activity_id = -1;
                galleryObj.reloadView();
            });

            this.uploader.bind('FilesAdded', function(up, files) {
                $.each(files, function(i, file) {
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
                        self.uploader.removeFile(self.uploader.getFile(file.id));
                        $("#" + file.id).remove();
                        return false;
                    });

                });
            });

            this.uploader.bind('QueueChanged', function(up) {
                self.uploadFiles()

            });

            this.uploader.bind('UploadProgress', function(up, file) {
                $("#" + file.id + " .plupload_file_status").html(file.percent + "%");

            });
            this.uploader.bind('BeforeUpload', function(up, file) {
                up.settings.multipart_params.activity_id = activity_id;

            });

            this.uploader.bind('FileUploaded', function(up, file, res) {

                files = self.uploader.files;
                lastfile = files[files.length - 1];
                try {
                    var rtnObj;
                    rtnObj = JSON.parse(res)
                    activity_id = rtnObj.activity_id;
                } catch (e) {
//									return;
                }
            });




            //The plupload HTML5 code gives a negative z-index making add files button unclickable
            $(".plupload.html5").css({zIndex: 0});
            $("#rtMedia-upload-button   ").css({zIndex: 2});

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
        var uploader = new UploadView();
        uploader.initUploader();
        $("#rtMedia-start-upload").click(function(e) {
            uploader.uploadFiles(e);
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