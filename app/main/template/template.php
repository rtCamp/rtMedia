<?php

	get_header();
			if ( is_rt_media_gallery() ) {
				$template = 'media-gallery';
			} else if ( is_rt_media_single() ) {
				$template = 'media-single';
			}

	include(BPMediaRtTemplate::locate_template( $template ));

	get_sidebar();

	get_footer();

?>
