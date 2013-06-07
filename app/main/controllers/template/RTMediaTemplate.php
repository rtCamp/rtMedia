<?php

/**
 * Description of BPMediaTemplate
 *
 * @author saurabh
 */

class RTMediaTemplate {

	public $media_args;

	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this,'enqueue_scripts') );

	}




	function enqueue_scripts(){
		wp_enqueue_script('backbone');
		wp_enqueue_script('rtmediamodel', RT_MEDIA_URL.'app/assets/js/backbone/models.js');
	}

	function set_template($template, $shortcode_attr = false) {

		global $rt_media_query,$rt_media_interaction;
		//print_r($rt_media_query);
		$media_array = '';

		if ( $rt_media_query->format != 'json' ) {
			echo $shortcode_attr;

			include(RT_MEDIA_PATH . 'app/main/controllers/template/rt-template-functions.php');
			
			if(!$shortcode_attr)
				return $this->get_template($template);
			else {
				// $shortcode_attr processing
				echo $template;
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


	function get_template( ){

		return apply_filters( 'rt_media_media_template_include', RT_MEDIA_PATH . 'app/main/controllers/template/template.php');

	}

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