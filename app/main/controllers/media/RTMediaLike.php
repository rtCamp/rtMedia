<?php
/**
 * Handles media like operations.
 *
 * @package rtMedia
 * @author saurabh
 */

/**
 * Class to handle media like operations.
 */
class RTMediaLike extends RTMediaUserInteraction {

	/**
	 * Check whether like nonce is already added or not.
	 *
	 * @var boolean
	 */
	private $like_nonce_loaded = false;

	/**
	 * RTMediaLike constructor.
	 */
	public function __construct() {

		$args = array(
			'action'              => 'like',
			'person_label'        => esc_html__( 'person likes this', 'buddypress-media' ),
			'person_plural_label' => esc_html__( 'people like this', 'buddypress-media' ),
			'label'               => esc_html__( 'Like', 'buddypress-media' ),
			'plural'              => esc_html__( 'Likes', 'buddypress-media' ),
			'undo_label'          => esc_html__( 'Unlike', 'buddypress-media' ),
			'privacy'             => 20,
			'countable'           => true,
			'single'              => false,
			'repeatable'          => false,
			'undoable'            => true,
		);

		parent::__construct( $args );

		remove_filter( 'rtmedia_action_buttons_before_delete', array( $this, 'button_filter' ) );
		add_action( 'rtmedia_action_buttons_after_media', array( $this, 'button_filter' ), 12 );
		add_action( 'rtmedia_actions_before_comments', array( $this, 'like_button_filter' ), 10 );
		add_action( 'like_button_no_comments', array( $this, 'like_button_no_comments_callback' ), 10 );
		add_action( 'rtmedia_like_button_filter', array( $this, 'like_button_filter_nonce' ), 10, 1 );

		if ( ! rtmedia_comments_enabled() ) {
			add_action( 'rtmedia_actions_without_lightbox', array( $this, 'like_button_without_lightbox_filter' ) );
		}

		add_filter( 'rtmedia_check_enable_disable_like', array( $this, 'rtmedia_check_enable_disable_like' ), 10, 1 );
	}

	/**
	 * Check Likes for media is enabled or not
	 *
	 * @global type $rtmedia
	 *
	 * @param bool $enable_like Enable like or not.
	 *
	 * @return boolean True if Likes for media is enabled else returns false
	 */
	public function rtmedia_check_enable_disable_like( $enable_like ) {
		global $rtmedia;
		$options = $rtmedia->options;

		if ( ( isset( $options['general_enableLikes'] ) && 1 === intval( $options['general_enableLikes'] ) ) || ! isset( $options['general_enableLikes'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add like button.
	 */
	public function like_button_filter() {
		if ( empty( $this->media ) ) {
			$this->init();
		}
		$button = $this->render();

		if ( $button ) {
			echo '<span>' . wp_kses( $button, RTMedia::expanded_allowed_tags() ) . '</span>';
		}
	}

	/**
	 * This function displays the like button even if comment
	 * section is disabled.
	 */
	public function like_button_no_comments_callback() {

		if ( empty( $this->media ) ) {
			$this->init();
		}

		$button = $this->render();

		if ( $button ) {
			echo '<span>' . wp_kses( $button, RTMedia::expanded_allowed_tags() ) . '</span>';
		}
	}

	/**
	 * Render like button.
	 */
	public function like_button_without_lightbox_filter() {

		if ( empty( $this->media ) ) {
			$this->init();
		}

		$button = $this->render();

		if ( $button ) {
			echo wp_kses( $button, RTMedia::expanded_allowed_tags() );
		}
	}

	/**
	 * Process media like.
	 *
	 * @return int
	 */
	public function process() {

		$actions    = $this->model->get( array( 'id' => $this->action_query->id ) );
		$like_nonce = sanitize_text_field( filter_input( INPUT_POST, 'like_nonce', FILTER_SANITIZE_STRING ) );

		if ( ! wp_verify_nonce( $like_nonce, 'rtm_media_like_nonce' . $this->media->id ) ) {
			die();
		}

		$rtmediainteraction = new RTMediaInteractionModel();
		$user_id            = $this->interactor;
		$media_id           = $this->action_query->id;
		$action             = $this->action;
		$check_action       = $rtmediainteraction->check( $user_id, $media_id, $action );

		if ( $check_action ) {

			$results    = $rtmediainteraction->get_row( $user_id, $media_id, $action );
			$row        = $results[0];
			$curr_value = $row->value;

			if ( 1 === intval( $curr_value ) ) {
				$value          = '0';
				$this->increase = false;
			} else {
				$value          = '1';
				$this->increase = true;
			}

			$update_data   = array( 'value' => $value );
			$where_columns = array(
				'user_id'  => $user_id,
				'media_id' => $media_id,
				'action'   => $action,
			);
			$update        = $rtmediainteraction->update( $update_data, $where_columns );

		} else {

			$value          = '1';
			$columns        = array(
				'user_id'  => $user_id,
				'media_id' => $media_id,
				'action'   => $action,
				'value'    => $value,
			);
			$insert_id      = $rtmediainteraction->insert( $columns );
			$this->increase = true;

		}

		$actionwa = $this->action . 's';

		$return = array();

		$actions = intval( $actions[0]->{$actionwa} );
		if ( true === $this->increase ) {
			$actions ++;
			$return['next'] = apply_filters( 'rtmedia_' . $this->action . '_undo_label_text', $this->undo_label );
			$return['prev'] = apply_filters( 'rtmedia_' . $this->action . '_label_text', $this->label );
		} else {
			$actions --;
			$return['next'] = apply_filters( 'rtmedia_' . $this->action . '_label_text', $this->label );
			$return['prev'] = apply_filters( 'rtmedia_' . $this->action . '_undo_label_text', $this->undo_label );
		}

		$like_html = '<span class="rtmedia-like-counter"></span>';
		if ( $actions > 0 && function_exists( 'rtmedia_who_like_html' ) ) {
			$like_html = rtmedia_who_like_html( $actions, $this->increase );
		}

		// label for "person/people like this" in media popup".
		if ( 1 === $actions ) {
			$return['person_text'] = apply_filters( 'rtmedia_' . $this->action . '_person_label_text', $like_html );
		} else {
			$return['person_text'] = apply_filters( 'rtmedia_' . $this->action . '_person_label_text', $like_html );
		}

		if ( $actions < 0 ) {
			$actions = 0;
		}

		$return['count'] = $actions;
		$this->model->update( array( 'likes' => $actions ), array( 'id' => $this->action_query->id ) );

		global $rtmedia_points_media_id;
		$rtmedia_points_media_id = $this->action_query->id;
		do_action( 'rtmedia_after_like_media', $this );

		$is_json = sanitize_text_field( filter_input( INPUT_POST, 'json', FILTER_SANITIZE_STRING ) );

		if ( ! empty( $is_json ) && 'true' === $is_json ) {
			wp_send_json( $return );
		} else {
			$url = rtm_get_server_var( 'HTTP_REFERER', 'FILTER_SANITIZE_URL' );
			wp_safe_redirect( esc_url_raw( $url ) );
			die();
		}

		return $actions;
	}

	/**
	 * Show like button.
	 *
	 * @param array $buttons Buttons to show.
	 *
	 * @return array|void
	 */
	public function button_filter( $buttons ) {

		if ( empty( $this->media ) ) {
			$this->init();
		}

		$button = $this->render();

		if ( $button ) {
			echo wp_kses( $button, RTMedia::expanded_allowed_tags() );
		}
	}

	/**
	 * Check if user has liked media.
	 *
	 * @param bool|int $media_id Media id.
	 * @param bool|int $user_id User id.
	 *
	 * @return bool
	 */
	public function is_like_migrated( $media_id = false, $user_id = false ) {
		$rtmediainteraction = new RTMediaInteractionModel();

		if ( ! $user_id ) {
			$user_id = $this->interactor;
		}

		if ( ! $media_id ) {
			$media_id = $this->action_query->id;
		}

		$action = $this->action;

		return $rtmediainteraction->check( $user_id, $media_id, $action );
	}

	/**
	 * Get media likes value.
	 *
	 * @param bool|int $media_id Media id.
	 * @param bool|int $user_id User id.
	 *
	 * @return bool
	 */
	public function get_like_value( $media_id = false, $user_id = false ) {

		$rtmediainteraction = new RTMediaInteractionModel();

		if ( ! $user_id ) {
			$user_id = $this->interactor;
		}

		if ( ! $media_id ) {
			$media_id = $this->action_query->id;
		}

		$action  = $this->action;
		$results = $rtmediainteraction->get_row( $user_id, $media_id, $action );
		$row     = $results[0];

		if ( 1 === intval( $row->value ) ) {
			$this->increase = false;

			return true;
		} else {
			$this->increase = true;

			return false;
		}
	}

	/**
	 * Migrate liked media.
	 *
	 * @param int $like_media media id.
	 *
	 * @return int
	 */
	public function migrate_likes( $like_media ) {
		$rtmediainteraction = new RTMediaInteractionModel();
		$user_id            = $this->interactor;
		$media_id           = $this->action_query->id;
		$action             = $this->action;
		$value              = '1';
		$columns            = array(
			'user_id'  => $user_id,
			'media_id' => $media_id,
			'action'   => $action,
			'value'    => $value,
		);
		$insert_id          = $rtmediainteraction->insert( $columns );
		$like_media         = trim( str_replace( ',' . $this->action_query->id . ',', ',', ',' . $like_media . ',' ), ',' );
		// todo user attribute.
		update_user_meta( $this->interactor, 'rtmedia_liked_media', $like_media );

		return $insert_id;
	}

	/**
	 * Check if media is liked by user.
	 *
	 * @param bool|int $media_id Media id.
	 * @param bool|int $interactor Interactor/user id.
	 *
	 * @return bool
	 */
	public function is_liked( $media_id = false, $interactor = false ) {

		if ( ! $interactor ) {
			$interactor = $this->interactor;
		}

		if ( ! $media_id ) {
			$media_id = $this->action_query->id;
		}

		// todo user attribute.
		$like_media = get_user_meta( $interactor, 'rtmedia_liked_media', true );
		if ( $this->is_like_migrated( $media_id, $interactor ) ) {
			return $this->get_like_value( $media_id, $interactor );
		} else {

			if ( strpos( ',' . $like_media . ',', ',' . $media_id . ',' ) === false ) {
				$this->increase = true;

				return false;
			} else {
				$this->migrate_likes( $like_media );
				$this->increase = false;

				return true;
			}
		}
	}

	/**
	 * Code to execute before rendering like button.
	 *
	 * @return bool|void
	 */
	public function before_render() {

		$enable_like = true;
		$enable_like = apply_filters( 'rtmedia_check_enable_disable_like', $enable_like );

		if ( ! $enable_like ) {
			return false;
		}

		if ( $this->is_liked() ) {
			$this->label = $this->undo_label;
		}
	}

	/**
	 * Add nonce in like button.
	 *
	 * @param string $button Button html.
	 *
	 * @return string
	 */
	public function like_button_filter_nonce( $button ) {
		// We create only 1 nonce field for like button.
		if ( empty( $this->like_nonce_loaded ) ) {
			$button .= wp_nonce_field( 'rtm_media_like_nonce' . $this->media->id, 'rtm_media_like_nonce', true, false );

			$this->like_nonce_loaded = true;
		}

		return $button;
	}
}
