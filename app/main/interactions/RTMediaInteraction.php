<?php

/**
 * Sets up the routes and the context
 *
 * @author saurabh
 */
class RTMediaInteraction {

	public $context;
	private $slugs = array(
		'media',
		'upload'
	);
	public $routes;

	/**
	 * Initialise the interaction
	 */
	function __construct( ) {

		// set up the routes
		$this->set_routes();

		// set up the context
		$this->set_context();

	}

	/**
	 * Set up the route slugs' array
	 */
	function route_slugs(){

		// filter to add custom slugs for routes
		$this->slugs = apply_filters('rt_media_default_routes',$this->slugs);

	}

	function set_routes(){

		// set up the routes array
		$this->default_routes();

		// set up routes for each slug
		foreach($this->slugs as $slug){
			$this->routes->{$slug} = new RTMediaRouter($slug);
		}
	}

	/**
	 * Sets up the default context
	 */
	function default_context() {

		return new RTMediaContext();
	}

	/**
	 *
	 * @param array $context the context array
	 * @todo the method should also allow objects
	 */
	function set_context( $context = false ) {

		// take the context supplied and replace the context
		if ( is_array( $context ) && isset( $context[ 'type' ] ) && isset( $context[ 'id' ] ) ) {

			$context_object->type = $context[ 'type' ];
			$context_object->id = $context[ 'id' ];

			// if there is no context array supplied, set the default context
		} else {

			$context_object = $this->default_context();
		}

		//set the context property

		$this->context = $context_object;
	}

	/**
	 * Reset the context to the default context after temporarily setting it,
	 * Say, for an upload
	 */

	function rewind_context(){

		$this->context = $this->default_context();

	}


//	function request_type() {
//		if ( count( $_POST ) > 0 ) {
//			$this->request_type = 'post';
//		}
//
//		$this->request_type = 'get';
//	}
//
//	function setup() {
//
//		global $wp_query, $rt_media;
//
//		//print_r($wp_query);
//		// /media/photos, /media/231, /media/231/edit/, /media/231/delete,
//		// /upload/, /media/.../upload/
//		// /media/.../json/
//		$request_url = $wp_query->query_vars[ $this->slug ];
//
//		$this->request_type();
//
//
//		$this->request = explode( '/', $request_url );
//
//
//		$this->type = 'view';
//
//		$this->media_type = '';
//
//		if ( is_array( $this->request ) && $this->request[ 0 ] != '' ) {
//
//
//			if ( in_array( $this->request[ 0 ], $rt_media->allowed_types ) ) {
//				$this->media_type = $this->request[ 0 ];
//			} elseif ( is_numeric( $this->request[ 0 ] ) ) {
//
//				$this->media_id = $this->request[ 0 ];
//			}
//		}
//
//		if ( in_array( 'json', $this->request ) ) {
//			$this->format = 'json';
//		}
//
//		if ( in_array( 'delete', $this->request ) ) {
//			$this->type = 'delete';
//		}
//
//		if ( in_array( 'edit', $this->request ) ) {
//			$this->type = 'edit';
//		}
//
//		$this->set_context();
//	}
//
//
//	function locate_template( $template ) {
//		$located = '';
//		if ( ! $template )
//			return;
//
//		$template_name = $template . '.php';
//
//		if ( file_exists( STYLESHEETPATH . '/buddypress-media/' . $template_name ) ) {
//			$located = STYLESHEETPATH . '/buddypress-media/' . $template_name;
//		} else if ( file_exists( TEMPLATEPATH . '/buddypress-media/' . $template_name ) ) {
//			$located = TEMPLATEPATH . '/buddypress-media/' . $template_name;
//		} else {
//			$located = BP_MEDIA_PATH . "templates/{$this->slug}/" . $template_name;
//		}
//
//		return $located;
//	}

}

?>
