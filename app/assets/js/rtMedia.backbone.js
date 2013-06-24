var galleryObj;
var nextpage = 2;

jQuery(function($) {


	rtMedia = window.rtMedia || {};

		rtMedia = window.rtMedia || {};

	rtMedia.Context = Backbone.Model.extend({
                url: function(){
                    var url = "media/";
                    if(nextpage>0)
                        url += 'page/' + nextpage + '/'
                    return url;
                },
		defaults: {
			"context"		: "post",
			"context_id"	: false
		}
	});

	rtMedia.Media = Backbone.Model.extend({
		defaults: {
			"id"				: 0,
			"blog_id"			: false,
			"media_id"			: false,
			"media_author"		: false,
			"media_title"		: false,
			"album_id"			: false,
			"media_type"		: "photo",
			"activity_id"		: false,
			"privacy"			: 0,
			"views"				: 0,
			"downloads"			: 0,
			"ratings_average"	: 0,
			"ratings_total"		: 0,
			"ratings_count"		: 0,
			"likes"				: 0,
			"dislikes"			: 0,
			"guid"				: false,
			"next"			: -1,
			"prev"			: -1
		}

	});

	rtMedia.Gallery = Backbone.Collection.extend({
		model: rtMedia.Media,
		url: function(){
                    var temp = window.location.pathname;
                    var url='';
                    if(temp.indexOf('media') == -1){
                        url = 'media/';   
                    }else{
                        url = window.location.pathname.substr(0,window.location.pathname.lastIndexOf("page/"));
                    }
                    if(nextpage >1)
                        url += 'page/' + nextpage + '/';
                    
                    return url;
                },

		getNext: function(page) {
			this.fetch({
				data: {
					json: true,
					rt_media_page: nextpage
				},
				success: function(model, response) {
					var galleryViewObj = new rtMedia.GalleryView({
					collection: model,
                        		el: $(".rt-media-list")[0] });
                                        
				}
			});
		},


	});

	rtMedia.MediaView = Backbone.View.extend({
		tagName: 'li',
		className: 'rt-media-list-item',
		template: _.template($("#rt-media-gallery-item-template").html()),
		initialize: function() {
			this.model.bind('change', this.render);
			this.model.bind('remove', this.unrender);
			console.log(this.render());
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
		template: _.template($("#rt-media-gallery-item-template").html()),
		initialize: function() {
			this.render();
		},
		render: function(){
			//$(this.el).html("");
			that = this;
			$.each(this.collection.toJSON(), function(key, media){
				$(that.el).append(that.template(media));
                                   nextpage = media.next;
			});
                        if(nextpage > 1){
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
        $(document).on("click","#rtMedia-galary-next",function(e){
            $(this).hide();
            e.preventDefault();
            
            galleryObj.getNext(nextpage);
        });
        
        
        
        if(window.location.pathname.indexOf('media') != -1){
            var tempNext = window.location.pathname.substring(window.location.pathname.lastIndexOf("page/")+5, window.location.pathname.lastIndexOf("/"));
            if(isNaN(tempNext)=== false){
                nextpage = parseInt(tempNext) + 1;
            }
        }
});