<?php

/**
 * Description of BPMediaTemplate
 *
 * @author saurabh
 */

class RTMediaTemplate {

	public $media_args;

	function __construct() {
		add_action( 'rt_media_template_redirect', array( $this, 'template_redirect' ) );
		add_filter( 'rt_media_template_include', array( $this, 'set_template' ) );
		add_action( 'wp_enqueue_scripts', array( $this,'enqueue_scripts') );
	}




	function enqueue_scripts(){
		wp_enqueue_script('backbone');
		wp_enqueue_script('rtmediamodel', RT_MEDIA_URL.'app/assets/js/backbone/models.js');
	}

	function template_redirect() {
		$this->set_query();
	}

	function set_template($template) {

		global $rt_media_query,$rt_media;
		print_r($rt_media_query);
		$media_array = '';

		if ( $rt_media->interaction->format != 'json' ) {

			include(RT_MEDIA_PATH . 'app/main/template/rt-template-functions.php');
			return $this->get_template();

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

		return apply_filters( 'rt_media_media_template_include', RT_MEDIA_PATH . 'app/main/template/template.php');

	}

	static function locate_template( $template ) {
		$located = '';
		if ( ! $template )
			return;

		$template_name = $template . '.php';

		if ( file_exists( STYLESHEETPATH . '/buddypress-media/' . $template_name ) ) {
			$located = STYLESHEETPATH . '/buddypress-media/' . $template_name;
		} else if ( file_exists( TEMPLATEPATH . '/buddypress-media/' . $template_name ) ) {
			$located = TEMPLATEPATH . '/buddypress-media/' . $template_name;
		} else {
			$located = RT_MEDIA_PATH . 'templates/media/' . $template_name;
		}

		return $located;
	}


	function set_query() {
		global $rt_media_query, $rt_media;


		$interaction = $rt_media->interaction;
		$args = array(
				'id'=> $interaction->media_id,
				'media_type' => $interaction->media_type,
				'context'	=> $interaction->context->type,
				'context_id'	=> $interaction->context->id
			);

		$rt_media_query = new RTMediaQuery($args);

	}



}
?>