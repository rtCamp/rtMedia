<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaGalleryShortcode
 * 
 * rtMedia Gallery Shortcode to embedd a gallery of media anywhere
 *
 * @author Udit Desai <udit.desai@rtcamp.com>
 */
class RTMediaGalleryShortcode {
	
	/**
	 * 
	 */
	public function __construct() {
		add_shortcode('rtmedia_gallery', array('RTMediaGalleryShortcode', 'render'));
	}

	/**
	 * Helper function to check whether the shortcode should be rendered or not
	 * 
	 * @return type
	 */
	static function display_allowed() {
		
		$flag = !(is_home() || is_post_type_archive());
		$flag = apply_filters('before_rtmedia_gallery_display', $flag);
		return $flag;
	}
	
	/**
	 * Render a shortcode according to the attributes passed with it
	 * 
	 * @param boolean $attr
	 */
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
