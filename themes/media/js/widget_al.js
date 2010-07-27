jQuery(document).ready( function() {
	jQuery(".widget div#media-allist-options a").live('click',
		function() {
                        jQuery('#ajax-loader-all-media').toggle();
			jQuery(".widget div#media-allist-options a").removeClass("selected");
			jQuery(this).addClass('selected');
			jQuery.post( ajaxurl, {
				action: 'widget_almedia_list',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce-media-all").val(),
				'max_al_media': jQuery("input#almedia_widget_max").val(),
				'filter': jQuery(this).attr('id')
			},
			function(response)
			{
				jQuery('#ajax-loader-all-media').toggle();
				almedia_widget_response(response);
			});

			return false;
		}
	);
});

function almedia_widget_response(response) {
	response = response.substr(0, response.length-1);
	response = response.split('[[SPLIT]]');

	if ( response[0] != "-1" ) {
		jQuery(".widget ul#media-allist").fadeOut(200,
			function() {
				jQuery(".widget ul#media-allist").html(response[1]);
				jQuery(".widget ul#media-allist").fadeIn(200);
			}
		);

	} else {
		jQuery(".widget ul#media-allist").fadeOut(200,
			function() {
				var message = '<p>' + response[1] + '</p>';
				jQuery(".widget ul#media-allist").html(message);
				jQuery(".widget ul#media-allist").fadeIn(200);
			}
		);
	}
}