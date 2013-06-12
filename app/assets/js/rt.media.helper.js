jQuery(document).ready(function($) {
	/* PL UPLOADER */

    /*var mode = $("#rt-media-uploader-form input[name='mode']").val();
    var nonce = $("#rt_media_file_upload_nonce").val();
    var context = $("#rt-media-uploader-form input[name='context']").val();
    var context_id = $("#rt-media-uploader-form input[name='context_id']").val();
    var privacy = $("#rt-media-uploader-form input[name='privacy']").val();
    var album_id = $("#rt-media-uploader-form input[name='album_id']").val();
    $("#rt-media-uploader").pluploadQueue({
		url: ajaxurl,
        runtimes: 'html5',
        unique_names: true,
        file_data_name: 'rt_media_file',
        multipart_params: {
            action: 'rt_file_upload',
            mode : mode,
            rt_media_file_upload_nonce : nonce,
            context : ( context !== undefined ) ? context : '',
            context_id : ( context_id !== undefined ) ? context_id : '',
            privacy : ( privacy !== undefined ) ? privacy : '',
            album_id : ( album_id !== undefined ) ? album_id : ''
        }
    });

    $("#rt-media-uploader-form").submit(function(e) {
        var uploader = $('#uploader').pluploadQueue();
        // Files in queue upload them first
        if (uploader.files.length > 0) {
            // When all files are uploaded submit form
            uploader.bind('StateChanged', function() {
                if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
                    $('#rt-media-uploader-form')[0].submit();
                }
            });
            uploader.start();
        } else {
            alert('You must queue at least one file.');
        }
        return false;
    });*/

});