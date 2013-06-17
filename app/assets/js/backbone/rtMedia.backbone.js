jQuery(function($) {


	rtMedia = window.rtMedia || {};

//	mediaGallery = new rtMedia.Gallery();
//	mediaModel
//	mediaGallery.add(new rtMedia.Media());
//	mediaGallery.fetch({data:{'json':true}});
//	new rtMedia.GalleryView();
//	media = new rtMedia.Media({ "media_title" : "Test" });
//	mediaView = new rtMedia.MediaView({ model: media});
//	galleryView = new rtMedia.GalleryView();

	rtMedia.galleryObj = new rtMedia.Gallery();
	rtMedia.galleryViewObj = new rtMedia.GalleryView({
		collection: rtMedia.galleryObj
	});
});