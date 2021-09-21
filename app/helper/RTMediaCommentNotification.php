<?php
/**
 * Media comment notification class.
 *
 * @package    rtMedia
 */

/**
 * Class to handle Media comment notifications
 *
 * @author  Jignesh Nakrani <jignesh.nakrani@rtcamp.com>
 */
class RTMediaCommentNotification extends RTMediaNotification {

	/**
	 * Component id.
	 *
	 * @var string
	 */
	public $component_id = 'rt_comment_notifier';

	/**
	 * Component action.
	 *
	 * @var string
	 */
	public $component_action = 'new_comment_to_media';

	/**
	 * RTMediaCommentNotification constructor.
	 */
	public function __construct() {

		if ( class_exists( 'BuddyPress' ) ) {
			$args = array(
				'component_id'       => 'rt_comment_notifier',
				'component_slug'     => 'rt_comment',
				'component_callback' => 'rt_comment_notifications_callback',
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
			add_filter( 'rtmedia_comment_notifications', array( $this, 'format_comment_notifications' ) );
			add_action( 'rtmedia_after_add_comment', array( $this, 'add_comment_notify' ) );
			add_action( 'rtmedia_after_media', array( $this, 'mark_notification_unread' ) );
			add_action( 'rtmedia_before_remove_comment', array( $this, 'remove_comment_notification' ) );
		}
	}

	/**
	 * Format the new notification in String or array
	 *
	 * @param array $params parameters.
	 *
	 * @return array|string  As per $format
	 */
	public function format_comment_notifications( $params ) {
		$action  = $params['action'];
		$post_id = intval( $params['post_id'] );

		if ( $this->component_action . $post_id === $action ) {
			$initiator_id = $params['initiator_id'];
			$total_items  = $params['total_items'];
			$format       = $params['format'];

			$comment_author = bp_core_get_username( $initiator_id );
			$media_url      = get_rtmedia_permalink( rtmedia_id( $post_id ) );
			$media_type     = rtmedia_type( rtmedia_id( $post_id ) );

			if ( 1 === $total_items ) {
				$text = $comment_author . ' ' . __( 'commented on your', 'buddypress-media' ) . ' ' . $media_type;
			} else {
				$text = $total_items . ' ' . __( 'new comments on your', 'buddypress-media' ) . ' ' . $media_type;
			}
			$link = esc_url( $media_url );

			$return = apply_filters(
				'rtmedia_before_comment_notification',
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
	 * Add a notification for a author of media on new comment on media
	 *
	 * @global mixed $bp
	 *
	 * @param array $args contains comment descriptions.
	 */
	public function add_comment_notify( $args ) {

		$post_author_id = get_post( $args['comment_post_ID'] )->post_author;
		$post_id        = $args['comment_post_ID'];
		$comment_id     = $args['comment_id'];
		$user_id        = $args['user_id'];

		if ( intval( $post_author_id ) === intval( $user_id ) ) {
			return;
		}

		$comment_notification_id = $this->add_notification( $post_id, $post_author_id, $args['user_id'] );
		if ( false !== $comment_notification_id ) {
			add_comment_meta( $comment_id, 'comment_notification_id', $comment_notification_id );
		}
	}

	/**
	 * Delete notification of a comment particular comment
	 *
	 * @param   int $comment_id Comment id.
	 */
	public function remove_comment_notification( $comment_id ) {

		$comment_notification_id = (int) get_comment_meta( $comment_id, 'comment_notification_id', true );

		if ( $comment_notification_id > 0 ) {
			BP_Notifications_Notification::delete( array( 'id' => $comment_notification_id ) );
			delete_comment_meta( $comment_id, 'comment_notification_id' );
		}

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
function rt_comment_notifications_callback( $action, $post_id, $initiator_id, $total_items, $format = 'string' ) {
	$params = array(
		'action'       => $action,
		'post_id'      => $post_id,
		'initiator_id' => $initiator_id,
		'total_items'  => $total_items,
		'format'       => $format,
	);

	return apply_filters( 'rtmedia_comment_notifications', $params );
}
