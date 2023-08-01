jQuery(document).ready(function($) {

    var rtUploaderModel = Backbone.Model.extend( {
        initialize: function( options ) {
            this.renderFiles = options.renderFiles;
            this.files = [
                {
                    id: 'o_asdfghjldasdawq123123',
                    name: '37456_Hold-On.mp3',
                    size: 154200
                },
                {
                    id: 'o_1h6nsvh421iqb2fgvmn11uhcvuj',
                    name: 'pexels-angelica-reyn-7116676.jpg',
                    size: 5314178
                }
            ];

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
                    <div class="plupload_file_progress ui-widget-header"></div>
                    </div>
                <div class="plupload_file_name" title="<%= name %>">
                  <span class="plupload_file_name_wrapper"><%= name %></span>
                  <i class="dashicons"></i>
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
            this.error = this.file.error;
            this.uploader = options.uploader;

            this.render();
        },

        render: function () {
            this.$el.html( this.template( this.file ) );

            this.setThumbnail();
            this.setProgress( 40 );
            this.setActionButton();

            // TODO: handle file upload error.

            // if ( ! this.error ) {
            //     icon = '<span id="label_' + file.id + '" class="dashicons dashicons-edit" title="' + rtmedia_backbone_strings.rtm_edit_file_name + '"></span>';
            // } else if ( error.code == -600 ) {
            //     alert( rtmedia_max_file_msg + uploader.settings.max_file_size );
            //     err_msg = ( uploader != '' ) ? rtmedia_max_file_msg + uploader.settings.max_file_size :  window.file_size_info;
            //     title = 'title=\'' + err_msg + '\'';
            //     icon = '<i class="dashicons dashicons-info" ' + title + '></i>';
            // } else if ( error.code == -601 ) {
            //     alert( error.message + '. ' + window.file_extn_info );
            //     err_msg = error.message + '. ' + window.file_extn_info;
            //     title = 'title=\'' + err_msg + '\'';
            //     icon = '<i class="dashicons dashicons-info" ' + title + '></i>';
            // }

            return this;
        },

        setThumbnail: function() {
            var type = this.file.type;
            var media_title = this.file.name;
            var ext = media_title.substring(media_title.lastIndexOf('.') + 1, media_title.length);
            var thumbnail = this.$el.find( '.plupload_file_thumb' );

            if (/image/i.test(type)) {
                var media_thumbnail = '';

                if (ext === 'gif') {
                    media_thumbnail = this.file.getNative();
                } else {
                    media_thumbnail = URL.createObjectURL( this.file.getNative() );
                }

                $( "<img alt='thumbnail' >" ).attr( 'src', media_thumbnail ).appendTo( thumbnail );
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

                        $( "<img alt='thumbnail' >" ).attr( 'src', media_thumbnail ).appendTo( thumbnail );

                        return false;
                    }
                });
            }
        },

        setProgress: function ( progress ) {
            progress = Number.isNaN( progress ) ? 0 : parseInt( progress, 10 );

            progress = Math.min( progress, 100 );
            progress = Math.max( progress, 0 );

            this.$el.find( '.plupload_file_progress' ).css( 'width', progress + '%' );
        },

        setActionButton: function() {
            var button = this.$el.find( '.plupload_file_name i' );

            if ( ! this.error ) {
                button.addClass( 'dashicons-edit' );
                button.attr( 'title', rtmedia_backbone_strings.rtm_edit_file_name );
            } else if ( this.error.code === -600 ) {
                button.addClass( 'dashicons-info' );
                button.attr( 'title', rtmedia_max_file_msg + this.uploader.settings.max_file_size );
            } else if ( this.error.code === -601 ) {
                button.addClass( 'dashicons-info' );
                button.attr( 'title', this.error.message + '. ' + window.file_extn_info );
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
            this.renderFiles();
        },

        renderFiles: function() {
            var list = this.$el.find( '#rtmedia_uploader_filelist' );
            var that = this;

            list.empty();

            this.model.files.forEach( function( file ) {
                list.append( new rtFileView( {
                    file: file,
                    uploader: that.model.uploader
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