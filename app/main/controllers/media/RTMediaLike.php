<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaLike
 *
 * @author saurabh
 */
class RTMediaLike extends RTMediaUserInteraction {

	function __construct() {
		$args = array(
			'action'                => 'like',
			'person_label'          => esc_html__( 'person likes this', 'buddypress-media' ),
			'person_plural_label'   => esc_html__( 'people like this', 'buddypress-media' ),
			'label'      => esc_html__( 'Like', 'buddypress-media' ),
			'plural'     => esc_html__( 'Likes', 'buddypress-media' ),
			'undo_label' => esc_html__( 'Unlike', 'buddypress-media' ),
			'privacy'    => 20,
			'countable'  => true,
			'single'     => false,
			'repeatable' => false,
			'undoable'   => true,
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
	 * check Likes for media is enabled or not
	 * @global type $rtmedia
	 * @param type $enable_like
	 * @return boolean True if Likes for media is enabled else returns false
	 */
	function rtmedia_check_enable_disable_like( $enable_like ) {
		global $rtmedia;
		$options = $rtmedia->options;
		if ( ( isset( $options['general_enableLikes'] ) && '1' == $options['general_enableLikes'] ) || ! isset( $options['general_enableLikes'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	function like_button_filter() {
		if ( empty( $this->media ) ) {
			$this->init();
		}
		$button = $this->render();

		if ( $button ) {
			echo '<span>' . $button . '</span>'; // @codingStandardsIgnoreLine
		}
	}

	/**
	 * This function displays the like button even if comment
	 * section is disabled.
	 */
	function like_button_no_comments_callback() {
		if ( empty( $this->media ) ) {
			$this->init();
		}
		$button = $this->render();

		if ( $button ) {
			echo '<span>' . $button . '</span>'; // @codingStandardsIgnoreLine
		}
	}

	function like_button_without_lightbox_filter() {
		if ( empty( $this->media ) ) {
			$this->init();
		}
		$button = $this->render();
		if ( $button ) {
			echo $button; // @codingStandardsIgnoreLine
		}
	}

	function process() {
		$actions    = $this->model->get( array( 'id' => $this->action_query->id ) );
		$like_nonce = filter_input( INPUT_POST, 'like_nonce', FILTER_SANITIZE_STRING );
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
		if ( $actions > 0  && function_exists( 'rtmedia_who_like_html' ) ) {
			$like_html = rtmedia_who_like_html( $actions, $this->increase );
		}

		/* label for "person/people like this" in media popup" */
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
		$is_json = filter_input( INPUT_POST, 'json', FILTER_SANITIZE_STRING );

		if ( ! empty( $is_json ) && 'true' === $is_json ) {
			wp_send_json( $return );
		} else {
			$url = rtm_get_server_var( 'HTTP_REFERER', 'FILTER_SANITIZE_URL' );
			wp_safe_redirect( esc_url_raw( $url ) );
			die();
		}

		return $actions;
	}

	function button_filter( $buttons ) {

		if ( empty( $this->media ) ) {
			$this->init();
		}
		$button = $this->render();

		if ( $button ) {
			echo $button; // @codingStandardsIgnoreLine
		}
	}

	function is_like_migrated( $media_id = false, $user_id = false ) {
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

	function get_like_value( $media_id = false, $user_id = false ) {
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

	function migrate_likes( $like_media ) {
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
		//todo user attribute
		update_user_meta( $this->interactor, 'rtmedia_liked_media', $like_media );

		return $insert_id;
	}

	function is_liked( $media_id = false, $interactor = false ) {
		if ( ! $interactor ) {
			$interactor = $this->interactor;
		}
		if ( ! $media_id ) {
			$media_id = $this->action_query->id;
		}
		//todo user attribute
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

	function before_render() {
		/* is comment media */
		// $comment_media = rtmedia_is_comment_media( rtmedia_id() );
		$enable_like = true;
		$enable_like = apply_filters( 'rtmedia_check_enable_disable_like', $enable_like );

		/* if is comment media then return false */
		// if ( ! $enable_like || ! empty( $comment_media ) ) {
		if ( ! $enable_like ) {
			return false;
		}

		if ( $this->is_liked() ) {
			$this->label = $this->undo_label;
		}
	}

	function like_button_filter_nonce( $button ) {
		$button .= wp_nonce_field( 'rtm_media_like_nonce' . $this->media->id, 'rtm_media_like_nonce', true, false );
		return $button;
	}
}
