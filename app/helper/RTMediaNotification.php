<?php
/**
 * File for RTMediaNotification class.
 *
 * @package    rtMedia
 */

/**
 * Media Notification class.
 *
 * @author Jignesh Nakrani <jignesh.nakrani@rtcamp.com>
 */
class RTMediaNotification {

	/**
	 * Component ID.
	 *
	 * @var $component_id
	 */
	public $component_id;

	/**
	 * Component slug.
	 *
	 * @var string$component_slug
	 */
	public $component_slug;

	/**
	 * Component callback.
	 *
	 * @var string $component_callback
	 */
	public $component_callback;

	/**
	 * Component action.
	 *
	 * @var string $component_action
	 */
	public $component_action;

	/**
	 * RTMediaNotification constructor.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args ) {

		foreach ( $args as $key => $val ) {
			$this->{$key} = $val;
		}

		add_action( 'bp_setup_globals', array( $this, 'notifier_setup_globals' ) );
	}

	/**
	 * Register a new component for notifications
	 *
	 * @global object $bp
	 */
	public function notifier_setup_globals() {
		global $bp;
		$component                                      = $this->component_id;
		$bp->{$component}                               = new stdClass();
		$bp->{$component}->id                           = $component;
		$bp->{$component}->slug                         = $this->component_slug;
		$bp->{$component}->notification_callback        = $this->component_callback;
		$bp->active_components[ $bp->{$component}->id ] = $bp->{$component}->id;
	}

	/**
	 * Add notification.
	 *
	 * @param int $post_id Post Id.
	 * @param int $post_author_id Post author id.
	 * @param int $user_id User id.
	 *
	 * @return int|bool  notification id on success or false
	 */
	public function add_notification( $post_id, $post_author_id, $user_id ) {
		global $rtmedia;

		$args_add_noification = array(
			'item_id'           => $post_id,
			'user_id'           => $post_author_id,
			'component_name'    => $this->component_id,
			'component_action'  => $this->component_action . $post_id,
			'secondary_item_id' => $user_id,
			'date_notified'     => bp_core_current_time(),
		);

		if ( isset( $rtmedia->options['buddypress_enableNotification'] ) && 0 !== intval( $rtmedia->options['buddypress_enableNotification'] ) ) {
			return bp_notifications_add_notification( $args_add_noification );
		}

		return false;
	}

	/**
	 * Mark related notification as read once media is visit by user
	 *
	 * @param   int $media_id ID of media to mark notification as read.
	 */
	public function mark_notification_unread( $media_id ) {
		$post_id = rtmedia_media_id( $media_id );
		$user_id = get_current_user_id();
		bp_notifications_mark_notifications_by_type( $user_id, $this->component_id, $this->component_action . $post_id, false );
	}

	/**
	 * Deletes existing media notification of a particular user
	 *
	 * @param   int $post_author_id Author of post.
	 * @param   int $post_id ID of a post to delete related notification.
	 */
	public function delete_notification_by_item_id( $post_author_id, $post_id ) {
		bp_notifications_delete_notifications_by_item_id( $post_author_id, $post_id, $this->component_id, $this->component_action . $post_id );
	}
}
