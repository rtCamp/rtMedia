<?php

	get_header();
			if ( is_rt_media_gallery() ) {
				$template = 'media-gallery';
			} else if ( is_rt_media_single() ) {
				$template = 'media-single';
			}

	include(RTMediaTemplate::locate_template( $template ));

	// print_r($rt_media_query);

	get_sidebar();

	get_footer();

?>