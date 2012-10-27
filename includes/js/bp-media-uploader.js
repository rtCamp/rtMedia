/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function(){
	var bp_media_is_multiple_upload = false;
	var bp_media_uploader=new plupload.Uploader(bp_media_uploader_params);
	bp_media_uploader.init();
	bp_media_uploader.bind('FilesAdded', function(up, files) {
		bp_media_is_multiple_upload = files.length==1&&jQuery('.bp-media-progressbar').length==0?false:true;
		jQuery.each(files, function(i, file) {
			jQuery('#bp-media-uploaded-files').append(
				'<div id="bp-media-progress-'+file.id+'" class="bp-media-progressbar"><div class="bp-media-progress-text">' +
				file.name + ' (' + plupload.formatSize(file.size) + ')(<b></b>)</div><div class="bp-media-progress-completed"></div></div>');
		});
		bp_media_album_select.dialog('option','buttons',{
				'Select': function() {
					bp_media_uploader.start();
					jQuery(this).dialog("close");
				},
				'Create New': function(){
					bp_media_new_album.dialog('option','buttons',{
						'Create' : function(){
							var album_name = jQuery('#bp_media_album_name').val();
							if(album_name.length==0){
								alert('You have not filled the album name');
								return false;
							}
							var data = {
								action: 'bp_media_add_album',
								bp_media_album_name : album_name
							};
							jQuery.post(bp_media_vars.ajaxurl,data,function(response){
								var album = parseInt(response);
								if(album == 0){
									alert('There was some error creating album');
								}
								else{
									jQuery('#bp-media-selected-album').append('<option value='+album+' selected="selected">'+jQuery('#bp_media_album_name').val()+'</option>')
									bp_media_new_album.dialog('close');
								}
								console.log(response);
							});
							console.log(jQuery('#bp_media_album_name').val());
						}
					});
					bp_media_new_album.dialog('open');
				}
		})
		bp_media_album_select.dialog('open');
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

		up.refresh(); // Reposition Flash/Silverlight
	});

	bp_media_uploader.bind('FileUploaded', function(up, file) {
		console.log('done');
		jQuery('#bp-media-progress-'+file.id+' .bp-media-progress-text b').html("100%");
	});
	bp_media_uploader.bind('BeforeUpload',function(up){
		up.settings.multipart_params.is_multiple_upload = bp_media_is_multiple_upload;
	});
	var bp_media_album_select =jQuery('#bp-media-album-prompt').dialog({
		autoOpen:false,
		draggable:false,
		modal:true,
		resizable:false,
		closeOnEscape:false
	});
	var bp_media_new_album = jQuery('#bp-media-album-new').dialog({
		autoOpen:false,
		draggable:false,
		modal:true,
		resizable:false,
		closeOnEscape:false
	});
});