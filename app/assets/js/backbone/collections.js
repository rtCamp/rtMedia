jQuery(function($) {

	rtMedia = window.rtMedia || {};

	rtMedia.Gallery = Backbone.Collection.extend({
		model: rtMedia.Media,
		url: '/media/json',
		initialize: function() {
			this.fetch();
		}
	});

});