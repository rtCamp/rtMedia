<?php

/**
 * @package rtMedia
 * @subpackage URL Manipulation
 */

/**
 * Handles routing in WordPress for /media/ and /upload/ endpoints
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 */
class RTMediaRouter {

	/**
	 *
	 * @var string The slug for the route
	 */
	public $slug;

	/**
	 *
	 * @var array The query variables passed to our route
	 */

	public $query_vars;

	/**
	 * Initialise the route
	 * @param string $slug The slug for which the route needs to be registered, for eg, /media/
	 */
	function __construct( $slug = 'media' ) {

		//set up the slug for the route
		$this->slug($slug);

                
		$this->template_redirect();

		//
		add_filter('template_include', array($this,'template_include'),0,1);
		add_action('wp_ajax_rtmedia_include_gallery_item',array('RTMediaTemplate','include_gallery_item'));

	}


	/**
	 * Check if there is a constant defined for this route and use that instead
	 * So, this can be overridden by defining RTMEDIA_MEDIA_SLUG in wp-config.php
	 *
	 * @param string $slug The slug string passed for the route, in the constructor
	 */
	function slug($slug){

		// create the slug constant name
		$slug_constant = 'RTMEDIA_' . strtoupper( $slug ) . '_SLUG';

		// check if the constant is defined
		if ( defined( $slug_constant ) ){

			// assign the value of the constant instead
			$slug = constant($slug_constant);

		}

		// set the slug property
		$this->slug = $slug;

	}


	/**
	 * Checks if the route has been requested
	 *
	 * @global object $wp_query
	 * @return boolean
	 */

	function is_template() {
		global $wp_query;

		$return = isset( $wp_query->query_vars[ $this->slug ] );
		if($return){
			if(isset($wp_query->query_vars['action']) && $wp_query->query_vars['action']== 'bp_avatar_upload')
                            $return = false;
		}
                
                if($return){
                    $wp_query->is_404 = false;
                }
		return $return;
	}

	/**
	 * Hook into the template redirect action to populate the global objects
	 *
	 */

	function template_redirect() {
            
		// if it is not our route, return early
		if(!$this->is_template())return;
                
		status_header( 200 );
		//set up the query variables
		$this->set_query_vars();
                

		// otherwise provide a hook for only this route,
		// pass the slug to the function hooking here
		do_action("rtmedia_".$this->slug."_redirect");

	}

	/**
	 * Hook into the template_include filter to load custom template files
	 *
	 * @param string $template Template file path of the default template
	 * @return string File path of the template file to be loaded
	 */

	function template_include($template){

		// if it is not our route, return the default template early
		if(!$this->is_template())return $template;
                
		// otherwise, apply a filter to the template,
		// pass the template  and slug to the function hooking here
		// so it can load a custom template


		$template_load = new RTMediaTemplate();

		$template = $template_load->set_template($template);


		$template = apply_filters("rtmedia_".$this->slug."_include",$template);

		// return the template for inclusion in the theme

		return $template;

	}

	/**
	 * Break the request URL into an array of variables after the route slug
	 *
	 * @global object $wp_query
	 */

	function set_query_vars() {

		global $wp_query;
		$query_vars_array = explode('/',$wp_query->query_vars[ $this->slug ]);

		$this->query_vars = apply_filters('rtmedia_query_vars',$query_vars_array);

	}

}

?>
