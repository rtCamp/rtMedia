/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function(){
	jQuery('body').append('<div id="custom-overlay"></div>');
	jQuery('#custom-overlay').hide();
	jQuery('#bp-media-close').click(function(){
		jQuery('#bp-media-album-prompt').hide();
		jQuery('#custom-overlay').hide();
		jQuery('#bp-media-uploaded-files div').remove();
	});
	jQuery('#selected-btn').click(function() {
		bp_media_album_selected = jQuery('#bp-media-selected-album').val();
		jQuery('#bp-media-album-prompt').hide();
		jQuery('#custom-overlay').hide();
		bp_media_uploader.start();
	});
	jQuery('#create-btn').click(function() {
		jQuery('#custom-overlay').css('z-index', 115000);
		jQuery('#bp-media-album-new').show();
                jQuery('#bp-media-album-new').css({
                        left: ((jQuery(window).width()-jQuery('#bp-media-album-new').width())/2),
                        top: ((jQuery(window).height()-jQuery('#bp-media-album-new').height())/2)
                });
	});
	jQuery('#bp-media-create-album-close').click(function() {
		jQuery('#bp-media-album-new').hide();
		jQuery('#custom-overlay').css('z-index', 105000);
	});
        jQuery(window).resize(function(){
            jQuery('#bp-media-album-prompt').css({
                    left: ((jQuery(window).width()-jQuery('#bp-media-album-prompt').width())/2),
                    top: ((jQuery(window).height()-jQuery('#bp-media-album-prompt').height())/2)
            });
            jQuery('#bp-media-album-new').css({
                    left: ((jQuery(window).width()-jQuery('#bp-media-album-new').width())/2),
                    top: ((jQuery(window).height()-jQuery('#bp-media-album-new').height())/2)
            });
        });
	jQuery('#create-album').click(function() {
		var album_name = jQuery('#bp_media_album_name').val();
		if(album_name.length==0){
			alert('You have not filled the album name');
			return false;
		}
		var data = {
			action: 'bp_media_add_album',
			bp_media_album_name : album_name,
			bp_media_group_id : bp_media_uploader_params.multipart_params.bp_media_group_id
		};
		jQuery.post(bp_media_vars.ajaxurl,data,function(response){
			var album = parseInt(response);
			if(album == 0){
				alert('Sorry you cannot create albums in this group');
			}
			else{
				jQuery('#bp-media-selected-album').append('<option value='+album+' selected="selected">'+jQuery('#bp_media_album_name').val()+'</option>')
				jQuery('#bp-media-album-new').hide();
				bp_media_album_selected = jQuery('#bp-media-selected-album').val();
				jQuery('#bp-media-album-prompt').hide();
				jQuery('#custom-overlay').hide();
				bp_media_uploader.start();
			}
		});
	});

	var bp_media_is_multiple_upload = false;
	if(jQuery('#'+bp_media_uploader_params.container).length==0)
		return false;
	var bp_media_uploader=new plupload.Uploader(bp_media_uploader_params);
	var bp_media_album_selected = false;
	bp_media_uploader.init();
	bp_media_uploader.bind('FilesAdded', function(up, files) {
		//bp_media_is_multiple_upload = files.length==1&&jQuery('.bp-media-progressbar').length==0?false:true;
		bp_media_is_multiple_upload = files.length>1;
		jQuery.each(files, function(i, file) {
			jQuery('#bp-media-uploaded-files').append(
				'<div id="bp-media-progress-'+file.id+'" class="bp-media-progressbar"><div class="bp-media-progress-text">' +
				file.name + ' (' + plupload.formatSize(file.size) + ')(<b></b>)</div><div class="bp-media-progress-completed"></div></div>');
		});
		if(bp_media_album_selected == false){
			jQuery('#bp-media-album-prompt').css({
				left: ((jQuery(window).width()-jQuery('#bp-media-album-prompt').width())/2),
				top: ((jQuery(window).height()-jQuery('#bp-media-album-prompt').height())/2)
                        });
			jQuery('#custom-overlay').show();
			jQuery('#bp-media-album-prompt').show();
		} else {
			bp_media_album_selected = jQuery('#bp-media-selected-album').val();
			bp_media_uploader.start();
		}
		up.refresh(); // Reposition Flash/Silverlight
	});
	bp_media_uploader.bind('UploadProgress', function(up, file) {
		jQuery('#bp-media-progress-'+file.id+' .bp-media-progress-completed').width(file.percent+'%');
		jQuery('#bp-media-progress-'+file.id+' .bp-media-progress-text b').html(file.percent+'%');
	});

	bp_media_uploader.bind('Error', function(up, err) {
		jQuery('#bp-media-uploaded-files').html('<div class="error"><p>Error: ' + err.code +
			', Message: ' + err.message +
			(err.file ? ', File: ' + err.file.name : '') +
			'</p></div>'
			);
		up.refresh();
	});

	bp_media_uploader.bind('FileUploaded', function(up, file) {
		jQuery('#bp-media-progress-'+file.id+' .bp-media-progress-text b').html("100%");
	});
	bp_media_uploader.bind('BeforeUpload',function(up){
		up.settings.multipart_params.is_multiple_upload = bp_media_is_multiple_upload;
		up.settings.multipart_params.bp_media_album_id = bp_media_album_selected;
	});
	bp_media_uploader.bind('UploadComplete',function(){
		var new_location = window.location.href;
		if(new_location.search('/media/')>0){
			new_location = new_location.replace('/media/','/albums/');
			if(bp_media_album_selected>0)
				new_location = new_location.concat(bp_media_album_selected);
			else
				new_location = new_location.concat('0/');
		window.location.replace(new_location);
		}
	});
});