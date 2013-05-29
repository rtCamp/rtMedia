<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaInteraction
 *
 * @author saurabh
 */
class RTMediaInteraction {

	public $slug;

	public $context;

	public $request;

	public $request_type;

	public $type;

	public $media_type;

	public $media_id;

	public $format;

	/**
	 *
	 */
	function __construct($register_endpoint = true, $slug = 'media') {

		$this->slug = $slug;
		$this->setup();
		$this->register_interaction($register_endpoint);
		$this->register_template();
	}

	function register_interaction($register_endpoint){
		if(!$register_endpoint)
			return false;

		add_action( 'init', array( $this, 'endpoint' ) );
	}

	function register_template(){
		if($this->is_interaction_template()){
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
			add_action( 'template_include', array( $this, 'template_include' ) );
		}
	}

	function is_interaction_template(){
		global $wp_query;
		return isset( $wp_query->query_vars[ $this->slug ] );
	}

	function request_type(){
		if(count($_POST)>0){
			$this->request_type = 'post';
		}

		$this->request_type = 'get';

	}

	function setup(){

		global $wp_query, $rt_media;

		// /media/photos, /media/231, /media/231/edit/, /media/231/delete,
		// /upload/, /media/.../upload/
		// /media/.../json/
		$request_url = $wp_query->query_vars[ $this->slug ];

		$this->request = explode('/', $request_url);

		print_r($this->request);

		$this->type = 'view';

		$this->media_type =  'all';

		if(in_array($this->request[0],$rt_media->default_allowed_types)){

			$this->media_type	= $this->request[0];

		}elseif(is_numeric($this->request[0])){

			$this->media_id		= $this->request[0];

		}elseif($this->request[0]===''){

			// do nothing

		}else{

			$this->media_type = false;
		}

		if(in_array('json', $this->request)){
			$this->format = 'json';
		}

		if(in_array('delete', $this->request)){
			$this->type = 'delete';
		}

		if(in_array('edit', $this->request)){
			$this->type = 'edit';
		}

		$this->set_context();

	}

	function set_context(){
		$this->context = new RTMediaContext();
	}

	function endpoint(){
		$slug_constant = "RT_MEDIA_{$this->slug}_SLUG";
		if(defined($slug_constant)){
			$slug = constant($slug_constant);
		}
		add_rewrite_endpoint( $slug, EP_ALL );
	}

	function template_redirect(){

		if(!$this->is_interaction_template()){
			return;
		}

		global $rt_media;

		$rt_media->interaction = &$this;

		print_r($rt_media->interaction);

		do_action('rt_media_template_redirect');

	}

	function template_include(){

		if($this->type == RT_MEDIA_UPLOAD_SLUG) return;

		$template_class = 'RTMedia'.ucfirst($this->slug).'Template';
		$template = new $template_class();
		return $template;

	}

	public function locate_template( $template ) {
		$located = '';
		if ( ! $template )
			return;

		$template_name = $template . '.php';

		if ( file_exists( STYLESHEETPATH . '/buddypress-media/' . $template_name ) ) {
			$located = STYLESHEETPATH . '/buddypress-media/' . $template_name;
		} else if ( file_exists( TEMPLATEPATH . '/buddypress-media/' . $template_name ) ) {
			$located = TEMPLATEPATH . '/buddypress-media/' . $template_name;
		} else {
			$located = BP_MEDIA_PATH . "templates/{$this->slug}/" . $template_name;
		}

		return $located;
	}

}

?>
