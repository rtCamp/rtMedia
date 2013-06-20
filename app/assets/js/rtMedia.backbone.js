jQuery(function($) {


	rtMedia = window.rtMedia || {};

		rtMedia = window.rtMedia || {};

	rtMedia.Context = Backbone.Model.extend({
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
			"has_next"			: false,
			"has_prev"			: false
		}

	});


//	var rtMedia = Backbone.Collection.extend({
//		model: rtMediaItem,
//		url: 'media'
//	});

//	media = new rtMedia;

//	media.fetch({data:{'json':true}});
//	media.each(function(medium){
//		medium.fetch();
//	}

	//media.get(15)

//	media.add([{'id':12}]);
//
//
//	setInterval(function() {
//	  media.get(12).fetch({data:{'json':true}});
//	}, 10000);


//	mediaGallery = new rtMedia.Gallery();
//	mediaModel
//	mediaGallery.add(new rtMedia.Media());
//	mediaGallery.fetch({data:{'json':true}});
//	new rtMedia.GalleryView();
//	media = new rtMedia.Media({ "media_title" : "Test" });
//	mediaView = new rtMedia.MediaView({ model: media});
//	galleryView = new rtMedia.GalleryView();



	rtMedia.Gallery = Backbone.Collection.extend({
		model: rtMedia.Media,
		url: 'media/',

		getNext: function(page) {
			this.fetch({
				data: {
					json: true,
					rt_media_page: page
				},
				success: function(model, response) {
					var galleryViewObj = new rtMedia.GalleryView({
						collection: model,
						el: $(".rt-media-list")[0]
					});
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
//			this.collection= galleryColl.models;
//			this.add(new rtMedia.Media());
///			this.collection.on('add', this.appendItem);
			//console.log(this.collection);
			this.render();
		},
		render: function(){
			$(this.el).html("");
			that = this;
			$.each(this.collection.toJSON(), function(key, media){
				$(that.el).append(that.template(media));
			});
			
		},
		appendTo: function(media) {
			console.log("append");
			var mediaView = new rtMedia.MediaView({
        		model: media
      		});
			$(this.el).append(mediaView.render().el);
		}
	});


	var galleryObj = new rtMedia.Gallery();


});