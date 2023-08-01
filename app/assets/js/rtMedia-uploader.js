jQuery(document).ready(function($) {
    var rtFileModel = Backbone.Model.extend({
        defaults: {
            id: '',
            file: null
        },

        initialize: function () {
            var file = this.get('file');

            // custom properties
            file.description = file.description || '';
            file.title = file.name.substring(0, file.name.lastIndexOf('.')) || '';

            this.set('file', file);
            this.set('id', file.id);

            this.on('edit', this.editFileData, this);
        },

        editFileData: function (newData) {
            this.set('file', _.extend(this.get('file'), newData));

            this.trigger('change');
        }
    });

    var rtFileView = Backbone.View.extend({
        tagName: 'div',
        className: 'rtm-preview-file-item',

        events: {
            'click .rtm-edit-box .rtm-open-edit-box': 'openEditBox',
            'click .rtm-edit-box .rtm-close-edit-box': 'saveEditedData',
            'click .rtm-remove-from-queue': 'removeFile'
        },

        template: _.template(`
              <li class="plupload_file ui-state-default plupload_queue_li" id="<%= id %>" title="">
                <div id="file_thumb_<%= id %>" class="plupload_file_thumb"></div>
                <div class="plupload_file_status">
                    <div class="plupload_file_progress ui-widget-header"></div>
                    </div>
                <div class="plupload_file_name rtm-edit-box">
                  <span class="plupload_file_name_wrapper"><%= title %></span>
                  <i title="<%= rtmedia_backbone_strings.rtm_edit_file_name %>" class="rtm-btn rtm-edit rtm-open-edit-box dashicons dashicons-edit"></i>
                  <i title="<%= rtmedia_backbone_strings.rtm_save_file_name %>" class="rtm-btn rtm-save rtm-close-edit-box dashicons dashicons-yes rtm-file-edit"></i>
                  <i class="rtm-btn rtm-error dashicons dashicons-info"></i>
                  <div class="plupload_file_fields rtm-file-edit">
                    <div class="rtm-upload-edit-title-wrapper">
                        <label for="rtm-file-title-<%= id %>"><%= rtmedia_edit_media_info_upload.title %></label>
                        <input type="text" class="rtm-upload-edit-title" id="rtm-file-title-<%= id %>" value="<%= title %>" />
                    </div>
                    <div class="rtm-upload-edit-desc-wrapper">
                        <label for="rtm-file-desc-<%= id %>"><%= rtmedia_edit_media_info_upload.description %></label>
                        <textarea class="rtm-upload-edit-desc" id="rtm-file-desc-<%= id %>"></textarea>
                    </div>
                  </div>
                </div>
                <div class="plupload_file_action">
                  <div class="plupload_action_icon ui-icon">
                    <span class="rtm-remove-from-queue dashicons dashicons-dismiss"></span>
                  </div>
                </div>
                <div class="plupload_file_size">
                  <%= plupload.formatSize(size).toUpperCase() %>
                </div>
              </li>
        `),

        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.render();
        },

        render: function () {
            var file = this.model.get('file');
            this.$el.html( this.template( file ) );

            this.setThumbnail();
            this.setProgress( file.progress || 0 );
            this.closeEditBox();
            this.setButton();

            // TODO: handle file upload error.

            // if ( error.code == -600 ) {
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

        setThumbnail: function () {
            var file = this.model.get('file');
            var type = file.type;
            var media_title = file.name;
            var ext = media_title.substring(media_title.lastIndexOf('.') + 1, media_title.length);
            var thumbnail = this.$el.find('.plupload_file_thumb');

            if (/image/i.test(type)) {
                var media_thumbnail = '';

                if (ext === 'gif') {
                    media_thumbnail =  file.getNative();
                } else {
                    media_thumbnail = URL.createObjectURL( file.getNative() );
                }

                $("<img alt='thumbnail' >").attr('src', media_thumbnail).appendTo(thumbnail);
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

                        $("<img alt='thumbnail' >").attr('src', media_thumbnail).appendTo(thumbnail);

                        return false;
                    }
                });
            }
        },

        setProgress: function (progress) {
            progress = Number.isNaN(progress) ? 0 : parseInt(progress, 10);

            progress = Math.min(progress, 100);
            progress = Math.max(progress, 0);

            this.$el.find('.plupload_file_progress').css('width', progress + '%');
        },

        setButton: function () {
            var file = this.model.get('file');

            this.$el.find( '.rtm-btn' ).hide();

            if( ! file.error ) {
                this.$el.find('.rtm-edit').show();
                return ;
            }

            var err_msg = '';

            if ( file.error.code === -600 ) {
                var max_file_size = rtMedia_plupload.rtMedia_plupload_config.max_file_size;
                err_msg = rtmedia_max_file_msg + max_file_size;
            } else if ( file.error.code === -601 ) {
                err_msg = rtmedia_file_extension_error_msg + '. ' + window.file_extn_info;
            }

            window.alert( err_msg );
            this.$el.find('.rtm-error').attr('title', err_msg).show();
        },

        closeEditBox: function () {
            this.$el.find('.rtm-edit-box').children().show();
            this.$el.find('.rtm-file-edit').hide();
        },

        openEditBox: function () {
            this.$el.find('.rtm-edit-box').children().hide();
            this.$el.find('.rtm-file-edit').show();

            // load values
            this.$el.find('.rtm-upload-edit-title').val(this.model.get('file').title);
            this.$el.find('.rtm-upload-edit-desc').val(this.model.get('file').description);
        },

        saveEditedData: function () {
            this.closeEditBox();

            var newData = {
                title: this.$el.find('.rtm-upload-edit-title').val(),
                description: this.$el.find('.rtm-upload-edit-desc').val()
            };

            this.model.trigger( 'edit', newData );
        },

        removeFile: function () {
            this.model.collection.remove(this.model);
            this.remove();
        }
    });

    var rtFileCollection = Backbone.Collection.extend({
        model: rtFileModel,

        add: function ( data ) {
            var allNames = this.models.map( function ( model ) {
                return model.get( 'file' ).name;
            });

            if( allNames.indexOf( data.file.name ) === -1 ) {
                Backbone.Collection.prototype.add.call( this, data );
            }
        }
    });

    var rtFileCollectionView = Backbone.View.extend({
        initialize: function () {
            this.listenTo(this.collection, 'add', this.addOne);
            this.listenTo(this.collection, 'reset', this.addAll);
        },

        addOne: function (file) {
            var view = new rtFileView({ model: file });
            this.$el.append(view.render().el);
        },

        addAll: function () {
            this.$el.empty();
            this.collection.forEach(this.addOne, this);
        }
    });

    var rtFileUploader = Backbone.View.extend({
        events: {
            // 'click .rtm-uploader-tabs li': 'tabSwitch',
            'click .start-media-upload': 'startUpload'
        },

        initialize: function () {
            this.collection = new rtFileCollection();
            this.collectionView = new rtFileCollectionView({
                collection: this.collection,
                el: this.$el.find( '#rtmedia_uploader_filelist' )
            });
            this.uploader = null;

            this.listenTo( this.collection, 'add', this.updateUploader );
            this.listenTo( this.collection, 'remove', this.updateUploader );
            this.listenTo( this.collection, 'reset', this.updateUploader );

            this.initUploader();

            // this.collection.add( { file: { name: 'test.dmg', size: 1000, id: 'o_erewfwwe534r34rt43', type: 'file/dmg' } } );
        },

        initUploader: function() {
            var config = rtMedia_plupload.rtMedia_plupload_config;
            config.browse_button = this.$el.find( '#rtMedia-upload-button' ).get(0);

            this.uploader = new plupload.Uploader( config );

            this.uploader.bind('FilesAdded', this.onFilesAdded.bind(this) );
            this.uploader.bind('UploadProgress', this.onUploadProgress.bind(this) );
            this.uploader.bind('Error', this.onUploadError.bind(this) );

            this.uploader.init();
        },

        updateUploader: function () {
            var button = this.$el.find( '.start-media-upload' );

            if ( this.collection.length ) {
                button.show();
            } else {
                button.hide();
            }
        },

        startUpload: function () {
            this.uploader.start();
        },

        onFilesAdded: function( uploader, files ) {
            var self = this;

            files.forEach( function( file ) {
                self.collection.add( { file: file } );
            } );

            return false;
        },

        onUploadProgress: function( uploader, file ) {
            var model = this.collection.findWhere( { id: file.id } );

            if ( model ) {
                model.set( 'file', file );
            }

            console.log( file );
        },

        onUploadError: function( uploader, error ) {
            console.log( error );

            if( error.file ) {
                var model = this.collection.findWhere( { id: error.file.id } );

                if ( model ) {
                    var file = error.file;
                    file.error = error;
                    model.set( 'file', file );
                }
            }

            // jQuery( '.plupload_delete' ).on( 'click', function( e ) {
            //     e.preventDefault();
            //     jQuery( this ).parent().parent( 'li' ).remove();
            // } );
        }

    });

    /**
     * Attach View and Model to all uploader instances
     */
    $('.rtmedia-container-wrapper__uploader').each(function() {
        new rtFileUploader( {
            containerId: $(this).attr('id'),
            el: $(this)
        } );
    });
});