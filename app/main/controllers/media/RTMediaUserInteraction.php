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

		$this->set_label();

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
		do_action( 'rtmedia_pre_process_' . $this->action );

		$this->process();

		do_action( 'rtmedia_post_process_' . $this->action );
	}

	/**
	 * Updates count of the action
	 *
	 * @return integer New count
	 */
	function process() {


		$this->model = new RTMediaModel();
		$actions = $this->model->get( array( 'id' => $this->action_query->id ) );
		$actions = $actions[ 0 ]->$this->actions;
		if ( $this->increase === true ) {
			$actions ++;
		} else {
			$actions --;
		}

		$this->model->update( array( $this->actions => $actions ), array( 'id' => $this->action_query->id ) );
		die( $actions );
	}

}

?>
