jQuery(document).ready( function() {
	jQuery(".widget div#media-list-options a").live('click',
		function() {
                        jQuery('#ajax-loader-media').toggle();
			jQuery(".widget div#media-list-options a").removeClass("selected");
			jQuery(this).addClass('selected');
			jQuery.post( ajaxurl, {
				action: 'widget_media_list',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce-media").val(),
				'max_media': jQuery("input#media_widget_max").val(),
				'filter': jQuery(this).attr('id')
			},
			function(response)
			{
				jQuery('#ajax-loader-media').toggle();
				media_widget_response(response);
			});

			return false;
		}
	);
});

function media_widget_response(response) {
	response = response.substr(0, response.length-1);
	response = response.split('[[SPLIT]]');

	if ( response[0] != "-1" ) {
		jQuery(".widget ul#media-list").fadeOut(200,
			function() {
				jQuery(".widget ul#media-list").html(response[1]);
				jQuery(".widget ul#media-list").fadeIn(200);
			}
		);

	} else {
		jQuery(".widget ul#media-list").fadeOut(200,
			function() {
				var message = '<p>' + response[1] + '</p>';
				jQuery(".widget ul#media-list").html(message);
				jQuery(".widget ul#media-list").fadeIn(200);
			}
		);
	}
}