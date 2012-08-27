/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function(){
	var bp_media_uploader=new plupload.Uploader(bp_media_uploader_params);
	bp_media_uploader.init();
	bp_media_uploader.bind('FilesAdded', function(up, files) {
			jQuery.each(files, function(i, file) {
				jQuery('#bp-media-uploaded-files').append(
					'<div id="bp-media-progress-'+file.id+'" class="bp-media-progressbar"><div class="bp-media-progress-text">' +
					file.name + ' (' + plupload.formatSize(file.size) + ')(<b></b>)</div><div class="bp-media-progress-completed"></div></div>');
			});
			bp_media_uploader.start();
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
});