<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaGalleryShortcode
 *
 * @author Udit Desai <udit.desai@rtcamp.com>
 */
class RTMediaGalleryShortcode {
	
	public function __construct() {
		add_shortcode('rtmedia_gallery', array('RTMediaGalleryShortcode', 'render'));
	}

	function render($attr) {
		
		if( !(is_home() || is_post_type_archive()) ) {

			if( (!isset($attr)) || empty($attr) )
				$attr = true;

			$template = new RTMediaTemplate();
			$template->set_template('media-gallery', $attr);
		}
	}
}

?>
