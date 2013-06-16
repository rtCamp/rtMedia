jQuery(function($){

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
			"guid"				: false
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
});