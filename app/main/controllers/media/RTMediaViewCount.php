<?php
/**
 * Handles view count of media.
 *
 * @package rtMedia
 * @author ritz
 */

/**
 * Class to handle media view count.
 */
class RTMediaViewCount extends RTMediaUserInteraction {

	/**
	 * RTMediaViewCount constructor.
	 */
	public function __construct() {
		$args = array(
			'action'  => 'view',
			'label'   => 'view',
			'privacy' => 0,
		);
		parent::__construct( $args );
		remove_filter( 'rtmedia_action_buttons_before_delete', array( $this, 'button_filter' ) );
		add_filter( 'rtmedia_action_buttons_after_delete', array( $this, 'button_filter' ), 99 );
	}

	/**
	 * Renders media view form.
	 *
	 * @return bool|mixed|string|void
	 */
	public function render() {
		/**
		 * We were using session to store view count for a media by a particular user.
		 * Session will no more use in rtmedia.
		 *
		 * All Media View reports will be genrated using rtmedia_interaction table only
		 */

		$link = trailingslashit( get_rtmedia_permalink( $this->media->id ) ) . $this->action . '/';
		echo '<form action="' . esc_url( $link ) . '" id="rtmedia-media-view-form"></form>';
		do_action( 'rtmedia_view_media_counts', $this );
	}

	/**
	 * Process updating view count.
	 *
	 * @return int|void
	 */
	public function process() {
		$user_id = $this->interactor;

		if ( ! $user_id ) {
			$user_id = - 1;
		}

		$media_id           = $this->action_query->id;
		$action             = $this->action_query->action;
		$rtmediainteraction = new RTMediaInteractionModel();
		$check_action       = $rtmediainteraction->check( $user_id, $media_id, $action );

		if ( $check_action ) {
			$results       = $rtmediainteraction->get_row( $user_id, $media_id, $action );
			$row           = $results[0];
			$curr_value    = $row->value;
			$update_data   = array( 'value' => ++ $curr_value );
			$where_columns = array(
				'user_id'  => $user_id,
				'media_id' => $media_id,
				'action'   => $action,
			);
			$update        = $rtmediainteraction->update( $update_data, $where_columns );
		} else {
			$columns   = array(
				'user_id'  => $user_id,
				'media_id' => $media_id,
				'action'   => $action,
				'value'    => '1',
			);
			$insert_id = $rtmediainteraction->insert( $columns );
		}

		// todo update `views` column in media index table, might be need to write migration as well for old media.
		global $rtmedia_points_media_id;
		$rtmedia_points_media_id = $this->action_query->id;
		do_action( 'rtmedia_after_view_media', $this );
		die();
	}
}
