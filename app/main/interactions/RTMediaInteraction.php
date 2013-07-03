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

		add_action( 'init', array( $this,'rewrite_rules' ) );
		add_action( 'init', array( $this,'rewrite_tags' ) );
                add_action( 'init', array( $this, 'endpoint' ) );


		// set up interaction and routes
		add_action('template_redirect',array($this, 'init'),99);

		add_filter('wp_title', array($this,'set_title'), 9999, 2);


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
		$this->slugs = apply_filters('rtmedia_default_routes',$this->slugs);

	}

	function rewrite_rules() {
		add_rewrite_rule('^media/([0-9]*)/([^/]*)/?','index.php?media_id=$matches[1]&action=$matches[2]','bottom');
		add_rewrite_rule('^media/([0-9]*)/pg/([0-9]*)/?','index.php?media_id=$matches[1]&pg=$matches[2]','bottom');
		add_rewrite_rule('^media/nonce/([^/]*)/?','index.php?nonce_type=$matches[1]','bottom');
		add_rewrite_rule('^media/([A-Za-z]*)/pg/([0-9]*)/?','index.php?media_type=$matches[1]&pg=$matches[2]','bottom');
		add_rewrite_rule('^media/pg/([0-9]*)/?','index.php?pg=$matches[1]','bottom');
	}

	function rewrite_tags(){
		add_rewrite_tag('%media_id%','([0-9]*)');
		add_rewrite_tag('%action%','([^/]*)');
		add_rewrite_tag('%nonce_type%','([^/]*)');
		add_rewrite_tag('%media_type%','([A-Za-z]*)');
		add_rewrite_tag('%pg%','([0-9]*)');
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
		global $rtmedia_query;

		$args = array(
				'context'	=> $this->context->type,
				'context_id'	=> $this->context->id
			);

		$rtmedia_query = new RTMediaQuery($args);


	}

	function set_title($default,$sep){
		global $wp_query;

		if( ! array_key_exists('media',$wp_query->query_vars)) return $default;

		$title = RTMEDIA_MEDIA_LABEL .' '. $sep .' ';
		global $bp;
		//echo get_post_field('post_title',$this->context->id);
		switch($this->context->type){
			case 'group':
				$title .=  ucfirst($bp->groups->slug);
				break;
			case 'profile':
				if(class_exists('BuddyPress')){
					$title .=  ucfirst($bp->profile->slug);
				}else{
					$title .=  get_query_var('author_name');
				}
				break;
			default:
				$title .= get_post_field('post_title',$this->context->id);

				break;
		}
		$title .= ' '.$sep.' '.get_bloginfo( 'name' )  ;
		return $title;
	}


}

?>
