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
	public $privacy;

	/**
	 * Initialise the user interaction
	 *
	 * @param array $args
	 *
	 * @global object $rtmedia_query Default query
	 *
	 * @internal param string $action The user action
	 * @internal param bool $private Whether other users are allowed the action
	 * @internal param string $label The label for the button
	 * @internal param bool $increase Increase or decrease the action count
	 */
	function __construct( $args = array() ) {
		$defaults = array(
			'action'     => '',
			'label'      => '',
			'plural'     => '',
			'undo_label' => '',
			'privacy'    => 60,
			'countable'  => false,
			'single'     => false,
			'repeatable' => false,
			'undoable'   => false,
			'icon_class' => '',
		);

		$args = wp_parse_args( $args, $defaults );
		foreach ( $args as $key => $val ) {
			$this->{$key} = $val;
		}

		$this->init();

		// filter the default actions with this new one
		add_filter( 'rtmedia_query_actions', array( $this, 'register' ) );
		// hook into the template for this action
		add_action( 'rtmedia_pre_action_' . $this->action, array( $this, 'preprocess' ) );
		add_filter( 'rtmedia_action_buttons_before_delete', array( $this, 'button_filter' ) );
	}


	function init() {
		$this->model = new RTMediaModel();
		global $rtmedia_query;
		if ( ! isset( $rtmedia_query->action_query ) ) {
			return;
		}
		if ( ! isset( $rtmedia_query->action_query->id ) ) {
			return;
		}

		$this->set_label();
		$this->set_plural();
		$this->set_media();
		$this->set_interactor();

	}

	/**
	 * Checks if there's a label, if not creates from the action name
	 */
	function set_label() {
		if ( empty( $this->label ) ) {
			$this->label = ucfirst( $this->action );
		}
	}

	function set_plural() {
		if ( empty( $this->plural ) ) {
			$this->plural = $this->label . 's';
		}
	}

	function set_media() {

		$media_id    = false;
		$this->media = false;

		global $rtmedia_query;
		$this->action_query = $rtmedia_query->action_query;

		if ( isset( $this->action_query->id ) ) {
			$media_id = $this->action_query->id;
			$media    = $this->model->get( array( 'id' => $media_id ) );
			if ( ! empty( $media ) ) {
				$this->media = $media[0];
				$this->owner = $this->media->media_author;
			}
		}

	}

	function set_interactor() {
		$this->interactor = false;
		if ( is_user_logged_in() ) {
			$this->interactor = get_current_user_id();
		}
		$this->interactor_privacy = $this->interactor_privacy();
	}

	function interactor_privacy() {

		if ( ! isset( $this->interactor ) ) {
			return 0;
		}
		if ( false === $this->interactor ) {
			return 0;
		}
		if ( intval( $this->interactor ) === intval( $this->owner ) ) {
			return 60;
		}

		$friends = new RTMediaFriends();
		$friends = $friends->get_friends_cache( $this->interactor );

		if ( $friends && in_array( intval( $this->owner ), array_map( 'intval', $friends ), true ) ) {
			return 40;
		}

		return 20;
	}

	function is_visible() {
		if ( $this->interactor_privacy >= $this->privacy ) {
			return true;
		}

		return false;
	}

	function is_clickable() {
		$clickable = false;
		if ( $this->repeatable ) {
			$clickable = true;
			if ( $this->undoable ) {
				$clickable = true;
			}
		} else {
			if ( $this->undoable ) {
				$clickable = true;
			}
		}

		return $clickable;
	}

	function before_render() {

	}

	function render() {
		$before_render = $this->before_render();
		if ( false === $before_render ) {
			return false;
		}
		$button = $button_start = $button_end = '';
		if ( $this->is_visible() ) {
			$link     = trailingslashit( get_rtmedia_permalink( $this->media->id ) ) .
			            $this->action . '/';
			$disabled = $icon = '';
			if ( ! $this->is_clickable() ) {
				$disabled = ' disabled';
			}

			if ( isset( $this->icon_class ) && '' !== $this->icon_class ) {
				$icon = "<i class='" . esc_attr( $this->icon_class ) . "'></i>";
			}
			$button_start = '<form action="' . esc_url( $link ) . '">';
			$button       = '<button type="submit" id="rtmedia-' . esc_attr( $this->action ) . '-button-' . esc_attr( $this->media->id ) . '" class="rtmedia-' . esc_attr( $this->action )
			                . ' rtmedia-action-buttons button' . esc_attr( $disabled ) . '">' . $icon . '<span>' . apply_filters( 'rtmedia_' . $this->action . '_label_text', esc_html( $this->label ) ) . '</span></button>';

			//filter the button as required
			$button = apply_filters( 'rtmedia_' . $this->action . '_button_filter', $button );

			$button_end = '</form>';

			$button = $button_start . $button . $button_end;

		}

		return $button;
	}

	function button_filter( $buttons ) {
		if ( empty( $this->media ) ) {
			$this->init();
		}
		$buttons[] = $this->render();

		return $buttons;
	}

	/**
	 *
	 * @param array $actions The default array of actions
	 *
	 * @return array $actions Filtered actions array
	 */
	function register( $actions ) {
		if ( empty( $this->media ) ) {
			$this->init();
		}

		$actions[ $this->action ] = array( $this->label, false );

		return $actions;
	}

	/**
	 * Checks if an id is set
	 * Creates pre and post process hooks for the action
	 * Calls the process
	 *
	 */
	function preprocess() {
		global $rtmedia_query;
		$this->action_query = $rtmedia_query->action_query;

		if ( $this->action_query->action !== $this->action ) {
			return false;
		}

		if ( ! isset( $this->action_query->id ) ) {
			return false;
		}
		$result = false;

		do_action( 'rtmedia_pre_process_' . $this->action );
		if ( empty( $this->media ) ) {
			$this->init();
		}

		if ( $this->interactor_privacy >= $this->privacy ) {
			$result = $this->process();
		}

		do_action( 'rtmedia_post_process_' . $this->action, $result );

		print_r( $result );

		die();
	}

	/**
	 * Updates count of the action
	 *
	 * @return integer New count
	 */
	function process() {
		return false;
	}
}
