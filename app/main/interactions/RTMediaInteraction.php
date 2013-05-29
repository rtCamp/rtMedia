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

	public $register_endpoint;

	public $context;

	public $request;

	public $collection;

	public $controllers;

	public $views;

	/**
	 *
	 */
	function __construct($register_endpoint = false, $slug = false) {
		$this->register_endpoint = $register_endpoint;
		$this->slug = $slug;
		$this->set_context();
		$this->register_interaction();
		$this->register_template();
	}

	function register_interaction(){
		if(!$this->register_endpoint)
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
			$this->request->type = 'post';
		}

		$this->request->type = 'get';

	}

	function request_format(){
		if(isset($_GET['json']) || request_url_format('json')){
			$this->request->format = 'json';
		}
	}

	function request_url_format($format_slug){
		return false;
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

	}

	function template_include(){

	}

}

?>
