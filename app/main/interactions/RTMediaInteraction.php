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

		// hook into the WordPress Rewrite Endpoint API
		add_action( 'init', array( $this, 'endpoint' ) );

		// set up interaction and routes
		add_action('template_redirect',array($this, 'init'),99);


	}

	function init(){
		// set up the routes array
		$this->route_slugs();
		$this->set_context();
		$this->set_routers();
		$this->set_query();
	}

	/**
	 * Set up the route slugs' array
	 */
	function route_slugs(){

		// filter to add custom slugs for routes
		$this->slugs = apply_filters('rt_media_default_routes',$this->slugs);

	}

	/**
	 * Just adds the current /{slug}/ to the rewite endpoint
	 */
	function endpoint(){

		foreach($this->slugs as $slug){
			add_rewrite_endpoint( $slug, EP_ALL );
		}

	}

	function set_routers(){


		// set up routes for each slug
		foreach($this->slugs as $slug){
			$this->routes[$slug] = new RTMediaRouter($slug);
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

	function set_query() {
		global $rt_media_query;

		$args = array(
				'context'	=> $this->context->type,
				'context_id'	=> $this->context->id
			);

		$rt_media_query = new RTMediaQuery($args);


	}


}

?>
