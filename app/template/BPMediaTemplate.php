<?php

/**
 * Description of BPMediaTemplate
 *
 * @author saurabh
 */
class BPMediaTemplate {

	public function __construct() {
		add_action( 'init', array( $this, 'endpoint' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'template_include', array( $this, 'set_template' ) );
	}

	function endpoint() {
		add_rewrite_endpoint( BP_MEDIA_SLUG, EP_ALL );
	}

	function template_redirect() {
		global $wp_query, $bp;
		if ( ! isset( $wp_query->query_vars[ 'media' ] ) )
			return;

		$this->set_query();
	}

	function set_template() {
		global $rt_media_query;

		if ( isset( $_GET[ 'json' ] ) ) {
			echo json_encode( $rt_media_query );
		} else {
			include(BP_MEDIA_PATH . 'app/main/template/rt-template-functions.php');

			if ( is_rt_media_gallery() ) {
				$template = 'media-gallery';
			} else if ( is_rt_media_single() ) {
				$template = 'media-single';
			}

			$template = apply_filters( 'rt_media_template_include', $this->locate_template( $template ) );

			return $template;
		}
	}

	function locate_template( $template ) {
		$located = '';
		if ( ! $template )
			return;

		$template_name = $template . '.php';

		if ( file_exists( STYLESHEETPATH . '/buddypress-media/' . $template_name ) ) {
			$located = STYLESHEETPATH . '/buddypress-media/' . $template_name;
		} else if ( file_exists( TEMPLATEPATH . '/buddypress-media/' . $template_name ) ) {
			$located = TEMPLATEPATH . '/buddypress-media/' . $template_name;
		} else {
			$located = BP_MEDIA_PATH . 'templates/' . $template_name;
		}

		return $located;
	}

	function set_query() {
		global $rt_media_query;
		$rt_media_query = new RTMediaQuery();
	}

}

?>
