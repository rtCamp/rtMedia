jQuery(function($){

	rtMedia.MediaView = Backbone.View.extend({
		tagName: 'li',
		className: 'rt-media-list-item',
		template: _.template($("#rt-media-gallery-item-template").html()),
		initialize: function() {
			this.model.bind('change', this.render);
			this.model.bind('remove', this.unrender);
		},
		render: function() {
			$(this.el).html(this.template(this.model.toJSON()));
			return this;
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
			this.collection = new rtMedia.Media();
			this.collection.bind('add', this.appendItem);
			this.render();
		},
		render: function(){
			var self = this;
//			$(this.el).append("<button id='add'>Add list item</button>");
			$(this.el).append("<ul></ul>");
			_(this.collection.models).each(function(media){ // in case collection is not empty
				self.appendItem(media);
			}, this);
		},
		appendTo: function(media) {
			var mediaView = new rtMedia.MediaView({
        		model: item
      		});
			$(this.el).append(mediaView.render().el);
		}
	});

});