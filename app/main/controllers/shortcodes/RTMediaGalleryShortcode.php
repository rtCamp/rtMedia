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

	static $add_script;

	/**
	 *
	 */
	public function __construct() {

		add_shortcode('rtmedia_gallery', array('RTMediaGalleryShortcode', 'render'));
		add_action('init', array($this, 'register_scripts'));
		add_action('wp_footer', array($this, 'print_script'));
	}

	function register_scripts() {
                wp_enqueue_script('plupload-all');
		//wp_register_script('rtmedia-models', RT_MEDIA_URL . 'app/assets/js/backbone/models.js', array('backbone'));
		//wp_register_script('rtmedia-collections', RT_MEDIA_URL . 'app/assets/js/backbone/collections.js', array('backbone', 'rtmedia-models'));
		//wp_register_script('rtmedia-views', RT_MEDIA_URL . 'app/assets/js/backbone/views.js', array('backbone', 'rtmedia-collections'));
		wp_register_script('rtmedia-backbone', RT_MEDIA_URL . 'app/assets/js/rtMedia.backbone.js', array('plupload','backbone'));
                
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
	static function render($attr) {
		if (self::display_allowed()) {
			self::$add_script = true;

			ob_start();

			if ((!isset($attr)) || empty($attr))
				$attr = true;

			$attr = array('name' => 'gallery', 'attr' => $attr);

			$template = new RTMediaTemplate();
			$template->set_template('media-gallery', $attr);

			return ob_get_clean();
		}
	}

	static function print_script() {
		if (!self::$add_script)
			return;
		if (!wp_script_is('rtmedia-backbone')){
			wp_print_scripts('rtmedia-backbone');
		}
	}

}

?>