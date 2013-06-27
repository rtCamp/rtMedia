<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaUserInteractions
 *
 * @author saurabh
 */
class RTMediaUserInteraction {

	/**
	 *
	 * @var string The singular action word (like, unlike, view, download, etc)
	 */
	public $action;

	/**
	 *
	 * @var string The plural of the action (likes, unlikes, etc)
	 */
	public $actions;

	/**
	 *
	 * @var boolean Whether the action increases the count or decreases the count
	 */
	public $increase;

	/**
	 *
	 * @var object The action query populated by the default query
	 */
	public $action_query;

	/**
	 *
	 * @var object The db model
	 */
	public $model;

	public $interactor;

	public $owner;

	public $media;

	public $others;

	public $private;

	/**
	 * Initialise the user interaction
	 *
	 * @global object $rt_media_query Default query
	 * @param string $action The user action
	 * @param boolean $others Whether other users are allowed the action
	 * @param string $label The label for the button
	 * @param boolean $increase Increase or decrease the action count
	 */
	function __construct( $action, $others = false, $label = false, $increase = true ) {

		$this->action = $action;
		$this->actions = $action . 's';
		$this->label = $label;
		$this->increase = $increase;
		$this->others = $others;

		$this->model = new RTMediaModel();

		$this->set_label();
		$this->set_media();
		$this->set_interactor();
		$this->set_owner();

		// filter the default actions with this new one
		add_filter( 'rt_media_query_actions', array( $this, 'register' ) );

		// hook into the template for this action
		add_action( 'rtmedia_pre_action_' . $action, array( $this, 'preprocess' ) );
	}

	/**
	 * Checks if there's a label, if not creates from the action name
	 */
	function set_label() {
		if ( $this->label === false ) {
			$this->label = ucfirst( $this->action );
		}
	}

	function set_media(){
		global $rt_media_query;

		$this->action_query = $rt_media_query->action_query;

		if ( ! isset( $this->action_query->id ) )
			$this->media = false;
		else
			$this->media = $this->action_query->id;
	}

	function set_interactor(){
		$this->interactor = get_current_user_id();
	}

	function set_owner(){
		$this->owner = false;
		$user = $this->model->get(array('id'=>$this->media));
		if(!empty($user)){
			$user = $user[0];
			$this->owner = $user->media_author;
		}

	}

	function set_privacy(){
		$this->private = false;
		if($this->owner === $this->interactor&& !$this->others){
			$this->private = true;
		}

	}

	/**
	 *
	 * @param array $actions The default array of actions
	 * @return array $actions Filtered actions array
	 */
	function register( $actions ) {


		$actions[ $this->action ] = array($this->label,$this->others);
		return $actions;
	}

	/**
	 * Checks if an id is set
	 * Creates pre and post process hooks for the action
	 * Calls the process
	 *
	 */
	function preprocess() {
		global $rt_media_query;
		$this->action_query = $rt_media_query->action_query;

		if ( $this->action_query->action != $this->action )
			return false;

		if ( ! isset( $this->action_query->id ) )
			return false;
		$result= false;

		do_action( 'rtmedia_pre_process_' . $this->action );

		if(!$this->private) $result = $this->process();

		do_action( 'rtmedia_post_process_' . $this->action, $result );

		print_r($result);

		die();
	}

	/**
	 * Updates count of the action
	 *
	 * @return integer New count
	 */
	function process() {
		$actions = $this->model->get( array( 'id' => $this->action_query->id ) );
		$actionwa = $this->actions;
		$actions = $actions[ 0 ]->$actionwa;
		if ( $this->increase === true ) {
			$actions ++;
		} else {
			$actions --;
		}

		$this->model->update( array( $this->actions => $actions ), array( 'id' => $this->action_query->id ) );
		return $actions;
	}

}

?>
