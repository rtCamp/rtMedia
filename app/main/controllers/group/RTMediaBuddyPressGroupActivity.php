<?php
/**
 * Handle/change BuddyPress group activities behaviour.
 *
 * @package rtMedia
 */

/**
 * Class to handle/change BuddyPress group activities behaviour.
 *
 * @author utsavladani
 */
class RTMediaBuddyPressGroupActivity {

	/**
	 * RTMediaBuddyPressGroupActivity constructor.
	 */
	public function __construct() {
		// Allow only media uploading without text in activity.
		add_filter( 'bp_before_groups_post_update_parse_args', array( $this, 'bp_before_groups_post_update_parse_args' ) );
	}

	/**
	 * Filter content before processing in group activity.
	 * It adds the '&nbsp;' if content is empty, when we are only uploading media from activity.
	 *
	 * @param array $args Activity arguments.
	 *
	 * @return array
	 */
	public function bp_before_groups_post_update_parse_args( $args ) {
		// if content is non-breaking space then set it to empty.
		if ( isset( $args['content'] ) && '' === $args['content'] ) {

			if ( ! empty( $_POST['rtMedia_attached_files'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Not needed since we  are only checking if the field exists and it is done already.
				$args['content'] = '&nbsp;';
			}
		}

		return $args;
	}
}
