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

			ob_start();

			if( (!isset($attr)) || empty($attr) )
				$attr = true;

			$attr = array('name'=>'gallery', 'attr'=>$attr);

			$template = new RTMediaTemplate();
			$template->set_template('media-gallery', $attr);

			return ob_get_clean();
		}
	}
}

?>