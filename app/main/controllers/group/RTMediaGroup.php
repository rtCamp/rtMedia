<?php
/**
 * Handles rtMedia media group related tasks.
 *
 * @package rtMedia
 */

/**
 * Class RTMediaGroup to handle rtMedia media group related tasks.
 */
class RTMediaGroup {
	/**
	 * Media setting slug.
	 *
	 * @var string
	 */
	public $create_slug = 'media-setting';

	/**
	 * RTMediaGroup constructor.
	 */
	public function __construct() {
		global $rtmedia;
		$options = $rtmedia->options;

		if ( isset( $options['buddypress_enableOnGroup'] ) && 1 === intval( $options ['buddypress_enableOnGroup'] ) ) {
			// return.
			$extension = true;

			if ( isset( $options['general_enableAlbums'] ) && 0 === intval( $options['general_enableAlbums'] ) ) {
				$extension = false;
			}

			$extension = apply_filters( 'rtmedia_group_media_extension', $extension );
			if ( ! $extension ) {
				return;
			}
		} else {
			return;
		}

		if ( function_exists( 'bp_is_active' ) && bp_is_active( 'groups' ) && class_exists( 'BP_Group_Extension' ) ) {
			bp_register_group_extension( 'RTMediaGroupExtension' );
		}

	}

	/**
	 * Update group last activity.
	 *
	 * @param int $group_id Group id to get last activity.
	 */
	public static function update_last_active( $group_id ) {
		groups_update_last_activity( $group_id );
	}
}
