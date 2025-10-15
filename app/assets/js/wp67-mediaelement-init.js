jQuery(document).ready(function($) {
	// WordPress 6.7 compatibility: Initialize MediaElement if not already done
	if (typeof wp !== 'undefined' && wp.mediaelement && wp.mediaelement.initialize) {
		wp.mediaelement.initialize();
	}

	// Fallback for older MediaElement initialization
	if (typeof $().mediaelementplayer !== 'undefined') {
		$('.wp-audio-shortcode, .wp-video-shortcode').not('.mejs-container').mediaelementplayer({
			success: function(mediaElement, domObject) {
				// MediaElement successfully initialized
			}
		});
	}

	// WordPress 6.7 compatibility: Add console log to verify fixes are working
	if (window.console && console.log) {
		console.log('rtMedia: WordPress 6.7 compatibility mode active');
	}
});