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

	static function display_allowed() {
		return !(is_home() || is_post_type_archive());
	}
	
	function render($attr) {
		
		if( self::display_allowed() ) {

			if( (!isset($attr)) || empty($attr) )
				$attr = true;

			$template = new RTMediaTemplate();
			$template->set_template('media-gallery', $attr);
		}
	}
}

?>
