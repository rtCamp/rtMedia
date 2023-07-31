jQuery(document).ready(function($) {

    var rtUploaderModel = Backbone.Model.extend( {
        initialize: function( options ) {
            this.renderFiles = options.renderFiles;
            this.files = [];

            this.uploader = new plupload.Uploader( options.config );
            this.uploader.bind( 'FilesAdded', this.filesAdded, this );
            this.uploader.init();
        },

        filesAdded: function( uploader, selectedFiles ) {
            var files = this.files;
            files = files.concat( selectedFiles );

            // remove duplicate files
            files = _.uniq( files, function( file ) {
                return file.name;
            } );

            this.files = files;

            console.log( this.files );

            this.renderFiles();
        }
    });

    var rtFileView = Backbone.View.extend( {
        tagName: 'div',
        className: 'file-item',

        template: _.template(`
              <li class="plupload_file ui-state-default plupload_queue_li" id="<%= id %>" title="">
                <div id="file_thumb_<%= id %>" class="plupload_file_thumb"></div>
                <div class="plupload_file_status">
                <div class="plupload_file_progress ui-widget-header" style="width: 10%;"></div>
                </div>
                <div class="plupload_file_name" title="<%= name %>">
                  <span class="plupload_file_name_wrapper"><%= name %></span>
                  <i class="dashicons dashicons-info"></i>
                </div>
                <div class="plupload_file_action">
                  <div class="plupload_action_icon ui-icon">
                    <span class="remove-from-queue dashicons dashicons-dismiss"></span>
                  </div>
                </div>
                <div class="plupload_file_size">
                  <%= plupload.formatSize(size).toUpperCase() %>
                </div>
                <div class="plupload_file_fields"></div>
              </li>
        `),

        initialize: function( options ) {
            this.file = options.file;

            this.render();
        },

        render: function () {
            this.$el.html( this.template( this.file ) );

            this.setThumbnail();
            this.setProgress( 0 );

            return this;
        },

        setThumbnail: function() {
            var type = this.file.type;
            var media_title = this.file.name;
            var ext = media_title.substring(media_title.lastIndexOf('.') + 1, media_title.length);
            var thumb_element_id = '#file_thumb_' + this.file.id;

            if (/image/i.test(type)) {
                var media_thumbnail = '';

                if (ext === 'gif') {
                    media_thumbnail = this.file.getNative();
                } else {
                    media_thumbnail = URL.createObjectURL( this.file.getNative() );
                }

                $('<img />', {src: media_thumbnail}).appendTo( thumb_element_id );
            } else {
                $.each(rtmedia_exteansions, function (key, value) {
                    if (value.indexOf(ext) >= 0) {

                        var media_thumbnail = '';

                        // Below condition is to show docs and files addon thumbnail.
                        if (rtmedia_media_thumbs[ext]) {
                            media_thumbnail = rtmedia_media_thumbs[ext];
                        } else {
                            media_thumbnail = rtmedia_media_thumbs[key];
                        }

                        $('<img />', {src: media_thumbnail}).appendTo( thumb_element_id );

                        return false;
                    }
                });
            }
        }
    });

    var rtUploaderView = Backbone.View.extend( {
        events: {
            'click .rtm-uploader-tabs li': 'tabSwitch',
            'click #rtMedia-start-upload': 'startUpload'
        },

        initialize: function() {
            var config = rtMedia_plupload.rtMedia_plupload_config;

            config.browse_button = this.$el.find( '#rtMedia-upload-button' ).get(0);

            this.model = new rtUploaderModel( {
                config: config,
                renderFiles: this.renderFiles.bind( this )
            } );

            // this.warningOfExtension();
        },

        renderFiles: function() {
            var list = this.$el.find( '#rtmedia_uploader_filelist' );

            list.empty();

            this.model.files.forEach( function( file ) {
                list.append( new rtFileView( {
                    selectedFile: file
                } ).$el );
            });
        }
    });

    /**
     * Attach View and Model to all uploader instances
     */
    $('.rtmedia-container-wrapper__uploader').each(function() {
        new rtUploaderView( {
            containerId: $(this).attr('id'),
            el: $(this)
        } );
    });
});