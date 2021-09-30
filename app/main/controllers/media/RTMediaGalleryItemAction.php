<?php
/**
 * Edits/deletes links on gallery items.
 *
 * @author Ritesh <ritesh.patel@rtcamp.com>
 * @package rtMedia
 */

/**
 * Edit/delete links on gallery items.
 */
class RTMediaGalleryItemAction {

	/**
	 * RTMediaGalleryItemAction constructor.
	 */
	public function __construct() {
		// add edit/delete buttons in media gallery besides thumbnails.
		add_action( 'rtmedia_before_item', array( $this, 'action_buttons_before_media_thumbnail' ), 11 );
		// In load more of media all the data render through backbone template and so we need to avail it in backbone variable.
		add_filter( 'rtmedia_media_array_backbone', array( $this, 'rtmedia_media_actions_backbone' ), 10, 1 );
		// add a custom class to media gallery item if the user on his profile which will be used to show the action buttons on the media gallery item.
		add_filter( 'rtmedia_gallery_class_filter', array( $this, 'add_class_to_rtmedia_gallery' ), 11, 1 );
		// remove rtMedia Pro actions.
		add_action( 'rtmedia_before_media_gallery', array( $this, 'remove_rtmedia_pro_hooks' ) );
	}

	/**
	 * Hook to add button for medias.
	 */
	public function remove_rtmedia_pro_hooks() {
		remove_action( 'rtmedia_before_item', 'add_action_buttons_before_media_thumbnail', 11 );
	}

	/**
	 * Add classes to media gallery.
	 *
	 * @param string $classes CSS classes for media gallery.
	 *
	 * @return string
	 */
	public function add_class_to_rtmedia_gallery( $classes ) {

		global $rtmedia_query;
		$user_id = get_current_user_id();

		if ( is_rt_admin() || ( isset( $rtmedia_query->query['context'] ) && 'profile' === $rtmedia_query->query['context'] && isset( $rtmedia_query->query['context_id'] ) && $rtmedia_query->query['context_id'] === $user_id ) ) {
			$classes .= ' rtm-pro-allow-action';
		}

		if ( isset( $rtmedia_query->query['context'] ) && 'group' === $rtmedia_query->query['context'] ) {

			$group_id = $rtmedia_query->query['context_id'];
			if ( groups_is_user_mod( $user_id, $group_id ) || groups_is_user_admin( $user_id, $group_id ) ) {
				$classes .= ' rtm-pro-allow-action';
			}
		}

		return $classes;

	}

	/**
	 * Add actions buttons before showing thumbnail.
	 */
	public function action_buttons_before_media_thumbnail() {
		// add edit and delete links on single media.
		global $rtmedia_media, $rtmedia_backbone;
		?>
		<?php
		if ( is_user_logged_in() ) {

			if ( $rtmedia_backbone['backbone'] ) {
				echo '<%= media_actions %>';
			} else {
				$context_id = $rtmedia_media->context_id;
				$user_id    = get_current_user_id();

				if ( is_rt_admin()
					|| ( function_exists( 'groups_is_user_mod' ) && groups_is_user_mod( $user_id, $context_id ) )
					|| ( isset( $rtmedia_media ) && isset( $rtmedia_media->media_author ) && get_current_user_id() === intval( $rtmedia_media->media_author ) )
				) {
					?>
					<div class='rtmedia-gallery-item-actions'>
						<a href="<?php rtmedia_permalink(); ?>edit" class='no-popup' target='_blank' title='<?php esc_attr_e( 'Edit this media', 'buddypress-media' ); ?>'>
							<i class='dashicons dashicons-edit'></i><?php esc_html_e( 'Edit', 'buddypress-media' ); ?>
						</a>
						<a href="#" class="no-popup rtm-delete-media" title='<?php esc_attr_e( 'Delete this media', 'buddypress-media' ); ?>'>
							<i class='dashicons dashicons-trash'></i><?php esc_html_e( 'Delete', 'buddypress-media' ); ?>
						</a>
					</div>
					<?php
				}
			}
		}
	}

	/**
	 * Add Div to show media action buttons.
	 *
	 * @param object $media_array Media details.
	 *
	 * @return mixed
	 */
	public function rtmedia_media_actions_backbone( $media_array ) {
		$context_id = $media_array->context_id;
		$user_id    = get_current_user_id();

		$media_array->media_actions = "<div class='rtmedia-gallery-item-actions'><a href='" . esc_url( $media_array->rt_permalink ) . "edit' class='no-popup' target='_blank' title='" . esc_attr__( 'Edit this media', 'buddypress-media' ) . "'><i class='dashicons dashicons-edit'></i>" . esc_html__( 'Edit', 'buddypress-media' ) . "</a><a href='#' class='no-popup rtm-delete-media' title='" . esc_attr__( 'Delete this media', 'buddypress-media' ) . "' ><i class='dashicons dashicons-trash'></i>" . esc_html__( 'Delete', 'buddypress-media' ) . '</a></div>';
		if ( ! ( is_rt_admin() || ( function_exists( 'groups_is_user_mod' ) && groups_is_user_mod( $user_id, $context_id ) ) || intval( $media_array->media_author ) === get_current_user_id() ) ) {
			$media_array->media_actions = '';
		}

		return $media_array;
	}
}
