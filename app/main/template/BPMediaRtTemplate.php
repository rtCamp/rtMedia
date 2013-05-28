<?php

/**
 * Description of BPMediaTemplate
 *
 * @author saurabh
 */

//if (class_exists('BPMediaRtTemplate')) exit;

class BPMediaRtTemplate {


	function __construct() {
		add_action( 'init', array( $this, 'endpoint' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'template_include', array( $this, 'set_template' ) );
	}
	function is_media_template(){
		global $wp_query;
		return isset( $wp_query->query_vars[ 'media' ] );
	}

	function endpoint() {
		add_rewrite_endpoint( BP_MEDIA_SLUG, EP_ALL );
	}

	function template_redirect() {

		if(!$this->is_media_template())
			return;


		$this->set_query();
	}

	function set_template($template) {
		if(!$this->is_media_template())
			return $template;

		global $rt_media_query;

		if ( isset( $_GET[ 'json' ] ) ) {
			echo json_encode( $rt_media_query );
		} else {
			include(BP_MEDIA_PATH . 'app/main/template/rt-template-functions.php');


			return $this->get_template();
		}
	}


	function get_template( ){

		return apply_filters( 'rt_media_template_include', BP_MEDIA_PATH . 'app/main/template/template.php');

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
			$located = BP_MEDIA_PATH . 'templates/media/' . $template_name;
		}

		return $located;
	}

	function set_query() {
		global $rt_media_query;
		$rt_media_query = '';//new RTMediaQuery();
	}

}
?>