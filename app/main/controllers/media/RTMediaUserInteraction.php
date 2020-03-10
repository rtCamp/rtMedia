<?php
/**
 * Handles user interactions with media
 *
 * @package rtMedia
 * @author saurabh
 */

/**
 * Class to handle user interactions
 */
class RTMediaUserInteraction {

	/**
	 * The singular action word (like, unlike, view, download, etc)
	 *
	 * @var string
	 */
	public $action;

	/**
	 * The plural of the action (likes, unlikes, etc)
	 *
	 * @var string
	 */
	public $actions;

	/**
	 * Whether the action increases the count or decreases the count
	 *
	 * @var boolean
	 */
	public $increase;


	/**
	 * The action query populated by the default query
	 *
	 * @var object
	 */
	public $action_query;

	/**
	 * The db model
	 *
	 * @var object
	 */
	public $model;

	/**
	 * User id.
	 *
	 * @var int
	 */
	public $interactor;

	/**
	 * Media owner
	 *
	 * @var int $owner
	 */
	public $owner;

	/**
	 * Media details
	 *
	 * @var array|object
	 */
	public $media;

	/**
	 * Privacy setting.
	 *
	 * @var int
	 */
	public $privacy;

	/**
	 * Action button label.
	 *
	 * @var string
	 */
	public $label;

	/**
	 * Remove action button label.
	 *
	 * @var string
	 */
	public $undo_label;

	/**
	 * Privacy setting of user.
	 *
	 * @var int
	 */
	public $interactor_privacy;

	/**
	 * Plural label.
	 *
	 * @var string
	 */
	public $plural;

	/**
	 * Initialise the user interaction
	 *
	 * @param array $args Arguments array.
	 *
	 * @global object $rtmedia_query Default query
	 *
	 * @internal param string $action The user action.
	 * @internal param bool $private Whether other users are allowed the action.
	 * @internal param string $label The label for the button.
	 * @internal param bool $increase Increase or decrease the action count.
	 */
	public function __construct( $args = array() ) {
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

		// filter the default actions with this new one.
		add_filter( 'rtmedia_query_actions', array( $this, 'register' ) );
		// hook into the template for this action.
		add_action( 'rtmedia_pre_action_' . $this->action, array( $this, 'preprocess' ) );
		add_filter( 'rtmedia_action_buttons_before_delete', array( $this, 'button_filter' ) );
	}


	/**
	 * Initialize class.
	 */
	public function init() {
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
	public function set_label() {
		if ( empty( $this->label ) ) {
			$this->label = ucfirst( $this->action );
		}
	}

	/**
	 * Set plural label.
	 */
	public function set_plural() {
		if ( empty( $this->plural ) ) {
			$this->plural = $this->label . 's';
		}
	}

	/**
	 * Set media and owner.
	 */
	public function set_media() {

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

	/**
	 * Set user interactor.
	 */
	public function set_interactor() {
		$this->interactor = false;
		if ( is_user_logged_in() ) {
			$this->interactor = get_current_user_id();
		}
		$this->interactor_privacy = $this->interactor_privacy();
	}

	/**
	 * Set interactor privacy.
	 *
	 * @return int
	 */
	public function interactor_privacy() {

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

	/**
	 * Check if element is visible.
	 *
	 * @return bool
	 */
	public function is_visible() {
		if ( $this->interactor_privacy >= $this->privacy ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current button clickable or not.
	 *
	 * @return bool
	 */
	public function is_clickable() {
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

	/**
	 * Before rendering button.
	 */
	public function before_render() {

	}

	/**
	 * Render buttons.
	 *
	 * @return bool|mixed|string|void
	 */
	public function render() {
		$before_render = $this->before_render();

		if ( false === $before_render ) {
			return false;
		}

		$button = '';

		if ( $this->is_visible() ) {
			$link     = trailingslashit( get_rtmedia_permalink( $this->media->id ) ) . $this->action . '/';
			$disabled = '';
			$icon     = '';

			if ( ! $this->is_clickable() ) {
				$disabled = ' disabled';
			}

			if ( isset( $this->icon_class ) && '' !== $this->icon_class ) {
				$icon = "<i class='" . esc_attr( $this->icon_class ) . "'></i>";
			}

			$button_start = '<form action="' . esc_url( $link ) . '">';
			$button_label = apply_filters( 'rtmedia_' . $this->action . '_label_text', $this->label );
			$button       = '<button type="submit" id="rtmedia-' . esc_attr( $this->action ) . '-button-' . esc_attr( $this->media->id ) . '" class="rtmedia-' . esc_attr( $this->action )
			. ' rtmedia-action-buttons button' . esc_attr( $disabled ) . '" title="' . esc_attr( $button_label ) . '">' . $icon . '<span>' . esc_html( $button_label ) . '</span></button>';

			/**
			 * Like button is displayed more then 1 time on same page, so giving this button ID will result
			 * in HTML warnings.
			 */
			if ( 'like' === $this->action ) {
				$button = '<button type="submit" class="rtmedia-' . esc_attr( $this->action ) . ' rtmedia-action-buttons button' . esc_attr( $disabled ) . '" title="' . esc_attr( $button_label ) . '">' . $icon . '<span>' . esc_html( $button_label ) . '</span></button>';
			}

			// Filter the button as required.
			$button = apply_filters( 'rtmedia_' . $this->action . '_button_filter', $button );

			$button_end = '</form>';

			$button = $button_start . $button . $button_end;

		}

		return $button;
	}

	/**
	 * Add buttons.
	 *
	 * @param array $buttons buttons to show.
	 *
	 * @return array
	 */
	public function button_filter( $buttons ) {

		if ( empty( $this->media ) ) {
			$this->init();
		}
		$buttons[] = $this->render();

		return $buttons;
	}

	/**
	 * Register action.
	 *
	 * @param array $actions The default array of actions.
	 *
	 * @return array $actions Filtered actions array
	 */
	public function register( $actions ) {
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
	 */
	public function preprocess() {
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

		print_r( $result ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

		die();
	}

	/**
	 * Updates count of the action
	 *
	 * @return integer New count
	 */
	public function process() {
		return false;
	}
}
