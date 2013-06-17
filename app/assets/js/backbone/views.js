jQuery(function($){

	rtMedia = window.rtMedia || {};

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
		initialize: function() {
//			this.collection= galleryColl.models;
//			this.add(new rtMedia.Media());
///			this.collection.on('add', this.appendItem);
			console.log(this.collection);
			this.render();
		},
		render: function(){

//			$(this.el).append("<button id='add'>Add list item</button>");
			var that = this;
			console.log(this.collection.models);
			this.collection.each(function(media){ // in case collection is not empty
				console.log(media);
				that.appendItem(media);
			}, this);
			console.log(this.$el);
		},
		appendTo: function(media) {
			console.log("append");
			var mediaView = new rtMedia.MediaView({
        		model: media
      		});
			$(this.el).append(mediaView.render().el);
		}
	});

});