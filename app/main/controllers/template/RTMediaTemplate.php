<?php

/**
 * Description of RTMediaTemplate
 * 
 * Template to display rtMedia Gallery.
 * A stand alone template that renders the gallery/uploader on the page.
 *
 * @author saurabh
 */

class RTMediaTemplate {

	public $media_args;

	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this,'enqueue_scripts') );

	}

	/**
	 * Enqueues required scripts on the page
	 */
	function enqueue_scripts(){
		wp_enqueue_script('backbone');
		wp_enqueue_script('rtmediamodel', RT_MEDIA_URL.'app/assets/js/backbone/models.js');
	}

	/**
	 * redirects to the template according to the page request
	 * Pass on the shortcode attributes to the template so that the shortcode can berendered accordingly.
	 * 
	 * Also handles the json request coming from the AJAX calls for the media
	 * 
	 * @global type $rt_media_query
	 * @global type $rt_media_interaction
	 * @param type $template
	 * @param type $shortcode_attr
	 * @return type
	 */
	function set_template($template, $shortcode_attr = false) {

		global $rt_media_query;

		$media_array = '';

		if ( $rt_media_query->format != 'json' ) {
			
			/* Includes db specific wrapper functions required to render the template */
			include(RT_MEDIA_PATH . 'app/main/controllers/template/rt-template-functions.php');
			
			if(!$shortcode_attr)
				return $this->get_template($template);
			else {
				// $shortcode_attr processing for gallery
				include $this->locate_template($template);
			}

		} else {


			if($rt_media_query->media){
				foreach($rt_media_query->media as $media){
					$media_array[] = $media;
				}
			}
			echo json_encode( $media_array );
			return;
		}
	}

	/**
	 * filter to change the template path independent of the plugin
	 * 
	 * @return type
	 */
	function get_template( ){

		return apply_filters( 'rt_media_media_template_include', RT_MEDIA_PATH . 'app/main/controllers/template/template.php');

	}

	/**
	 * Template Locator
	 * 
	 * @param type $template
	 * @return string
	 */
	static function locate_template( $template ) {
		$located = '';
		if ( ! $template )
			return;

		$template_name = $template . '.php';

		if ( file_exists( STYLESHEETPATH . '/rt-media/' . $template_name ) ) {
			$located = STYLESHEETPATH . '/rt-media/' . $template_name;
		} else if ( file_exists( TEMPLATEPATH . '/rt-media/' . $template_name ) ) {
			$located = TEMPLATEPATH . '/rt-media/' . $template_name;
		} else {
			$located = RT_MEDIA_PATH . 'templates/media/' . $template_name;
		}

		return $located;
	}



}
?>