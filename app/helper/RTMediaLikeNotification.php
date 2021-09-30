<?php
/**
 * File for RTMediaLikeNotification class.
 *
 * @package    rtMedia
 */

/**
 * Class to handle media liked notification
 *
 * @author Jignesh Nakrani <jignesh.nakrani@rtcamp.com>
 */
class RTMediaLikeNotification extends RTMediaNotification {

	/**
	 * Component id.
	 *
	 * @var string
	 */
	public $component_id = 'rt_like_notifier';

	/**
	 * Component action.
	 *
	 * @var string
	 */
	public $component_action = 'new_like_to_media';

	/**
	 * RTMediaLikeNotification constructor.
	 */
	public function __construct() {

		if ( class_exists( 'BuddyPress' ) ) {
			$args = array(
				'component_id'       => 'rt_like_notifier',
				'component_slug'     => 'rt_like',
				'component_callback' => 'like_notifications_callback',
				'component_action'   => $this->component_action,
			);

			parent::__construct( $args );

			add_action( 'bp_init', array( $this, 'init' ) );
		}
	}

	/**
	 *  Hooked to bp_init.
	 */
	public function init() {
		if ( bp_is_active( 'notifications' ) ) {
			add_filter( 'rtmedia_like_notifications', array( $this, 'format_like_notifications' ) );
			add_action( 'rtmedia_after_like_media', array( $this, 'add_like_notify' ) );
			add_action( 'rtmedia_after_media', array( $this, 'mark_notification_unread' ) );
		}
	}

	/**
	 * Add notification using  bp_notifications_add_notification function
	 * delete and merge likes if already same notification exist on a media
	 *
	 * @param RTMediaLike $like_args RTMediaLike class object.
	 */
	public function add_like_notify( $like_args ) {
		$action         = $like_args->increase;
		$user_id        = $like_args->interactor;
		$post_author_id = $like_args->owner;
		$post_id        = $like_args->media->media_id;
		$like_count     = intval( get_rtmedia_like( $post_id ) );

		if ( intval( $post_author_id ) === intval( $user_id ) ) {
			return;
		}
		if ( true === $action ) {
			$this->delete_notification_by_item_id( $post_author_id, $post_id );
			$this->add_notification( $post_id, $post_author_id, $user_id );
		} elseif ( 0 === intval( $like_count ) ) {
			$this->delete_notification_by_item_id( $post_author_id, $post_id );
		}
	}

	/**
	 * Format string and media url for notification
	 *
	 * @param   array $params array ('action', 'post_id', 'initiator_id', 'total_items', 'format' ).
	 *
	 * @return string|array     format notification as $params['format'] request
	 */
	public function format_like_notifications( $params ) {
		$action  = $params['action'];
		$post_id = intval( $params['post_id'] );

		if ( $this->component_action . $post_id === $action ) {

			$initiator_id = $params['initiator_id'];
			$total_items  = $params['total_items'];
			$format       = $params['format'];

			$rtmedia_id = rtmedia_id( $post_id );
			$liked_list = $this->fetch_media_like_stats( $rtmedia_id );
			$liked_by   = '';

			if ( isset( $liked_list[0]->user_id ) ) {
				$liked_by = bp_core_get_username( $liked_list[0]->user_id );
			}

			$like_count = get_rtmedia_like( $post_id );
			$link       = esc_url( get_rtmedia_permalink( $rtmedia_id ) );
			$media_type = rtmedia_type( $rtmedia_id );
			$text       = null;

			if ( 0 === intval( $like_count ) ) {
				$this->delete_notification_by_item_id( $initiator_id, $post_id );
			} elseif ( 1 === intval( $like_count ) ) {
				$text = $liked_by . ' ' . __( 'liked your', 'buddypress-media' ) . ' ' . $media_type;
			} elseif ( 2 === intval( $like_count ) ) {
				$text = $liked_by . ' ' . __( 'and one more friend liked your', 'buddypress-media' ) . ' ' . $media_type;
			} else {
				$count = $like_count - 1;
				$text  = $liked_by . ' ' . __( 'and', 'buddypress-media' ) . ' ' . $count . ' ' . __( 'other friends liked your', 'buddypress-media' ) . ' ' . $media_type;
			}

			$return = apply_filters(
				'rtmedia_before_like_notification',
				array(
					'link' => $link,
					'text' => $text,
				),
				(int) $total_items
			);

			if ( 'string' === $format ) {
				return '<a href="' . esc_url( $return['link'] ) . '">' . esc_html( $return['text'] ) . '</a>';
			} else {
				return $return;
			}
		}
	}

	/**
	 * Get like count of a media
	 *
	 * @param   int $media_id MediaID to count likes.
	 *
	 * @return  array|int Total like count on success or false
	 */
	public function fetch_media_like_stats( $media_id ) {

		if ( empty( $media_id ) ) {
			return false;
		}

		$rtmedia_interaction_model = new RTMediaInteractionModel();

		$media_like_cols = array(
			'media_id' => $media_id,
			'action'   => 'like',
			'value'    => 1,
		);
		$media_likes     = $rtmedia_interaction_model->get( $media_like_cols, false, false, 'action_date' );

		if ( 0 === count( $media_likes ) ) {
			return false;
		}

		return $media_likes;
	}
}

/**
 * This is callback function for rt_like_notifier component dont call this callback method manually
 *
 * @param int    $action action of component for notification.
 * @param int    $post_id ID of a post to notification.
 * @param int    $initiator_id secondary_item_id used in 'bp_notifications_add_notification'.
 * @param int    $total_items number of notification for same component.
 * @param String $format string or array.
 *
 * @return  String/Array formatted notification
 */
function like_notifications_callback( $action, $post_id, $initiator_id, $total_items, $format = 'string' ) {
	$params = array(
		'action'       => $action,
		'post_id'      => $post_id,
		'initiator_id' => $initiator_id,
		'total_items'  => $total_items,
		'format'       => $format,
	);

	return apply_filters( 'rtmedia_like_notifications', $params );
}
