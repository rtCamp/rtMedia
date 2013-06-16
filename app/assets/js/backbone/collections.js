jQuery(function($) {

	rtMedia.Gallery = Backbone.Collection.extend({
		model: rtMedia.Media,
		url: '/media'
	});


});