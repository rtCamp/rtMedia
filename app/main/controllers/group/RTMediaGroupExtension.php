<?php
/**
 * Handles Group albums settings.
 *
 * @package rtMedia
 * @author faishal
 */

if ( class_exists( 'BP_Group_Extension' ) ) :// Recommended, to prevent problems during upgrade or when Groups are disabled.

	/**
	 * Class to handle Media group albums.
	 */
	class RTMediaGroupExtension extends BP_Group_Extension {

		/**
		 * Media settings tab name.
		 *
		 * @var void|string
		 */
		public $name;

		/**
		 * Media setting slug.
		 *
		 * @var string
		 */
		public $slug;

		/**
		 * Group creation step position.
		 *
		 * @var int
		 */
		public $create_step_position;

		/**
		 * Enable navigation.
		 *
		 * @var bool
		 */
		public $enable_nav_item;

		/**
		 * RTMediaGroupExtension constructor.
		 */
		public function __construct() {
			$this->name                 = apply_filters( 'rtmedia_media_tab_name', RTMEDIA_MEDIA_LABEL );
			$this->slug                 = RTMEDIA_MEDIA_SLUG . '-setting';
			$this->create_step_position = 21;
			$this->enable_nav_item      = false;
		}

		/**
		 * Group album creation screen div.
		 *
		 * @param int $group_id Group id to create media group.
		 *
		 * @return bool
		 */
		public function create_screen( $group_id = null ) {

			if ( ! bp_is_group_creation_step( $this->slug ) ) {
				return false;
			}

			// HOOK to add PER GROUP MEDIA enable/diable option in rtMedia PRO.
			do_action( 'rtmedia_group_media_control_create' );

			global $rtmedia;
			$options = $rtmedia->options;

			include RTMEDIA_PATH . 'app/main/templates/media-group-create-screen.php';

			wp_nonce_field( 'groups_create_save_' . $this->slug );
		}

		/**
		 * Save group media details.
		 *
		 * @param int $group_id Group id to save details.
		 */
		public function create_screen_save( $group_id = null ) {
			global $bp;

			check_admin_referer( 'groups_create_save_' . $this->slug );

			/**
			 * Add playlist Save functionality
			 * By: Yahil
			 */
			$rt_album_creation_control      = sanitize_text_field( filter_input( INPUT_POST, 'rt_album_creation_control', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
			$rtmp_playlist_creation_control = sanitize_text_field( filter_input( INPUT_POST, 'rtmp_playlist_creation_control', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );

			/**
			 * Save details 'ALBUM CREATION CONTROL' and 'PLAYLIST CREATION CONTROL'
			 * By: Yahil
			 */
			if ( isset( $rt_album_creation_control ) && ! empty( $rt_album_creation_control ) ) {
				groups_update_groupmeta( $bp->groups->new_group_id, 'rt_media_group_control_level', $rt_album_creation_control );
			}

			if ( isset( $rtmp_playlist_creation_control ) && ! empty( $rtmp_playlist_creation_control ) ) {
				groups_update_groupmeta( $bp->groups->new_group_id, 'rtmp_create_playlist_control_level', $rtmp_playlist_creation_control );
			}

			do_action( 'rtmedia_create_save_group_media_settings', $_POST );
		}

		/**
		 * Edit media group screen.
		 *
		 * @param null|int $group_id Group id to edit.
		 *
		 * @return bool
		 */
		public function edit_screen( $group_id = null ) {
			if ( ! bp_is_group_admin_screen( $this->slug ) ) {
				return false; }
			$current_level = groups_get_groupmeta( bp_get_current_group_id(), 'rt_media_group_control_level' );
			if ( empty( $current_level ) ) {
				$current_level = 'all';
			}

			// HOOK to add PER GROUP MEDIA enable/diable option in rtMedia PRO.
			do_action( 'rtmedia_group_media_control_edit' );

			global $rtmedia;
			$options = $rtmedia->options;

			include RTMEDIA_PATH . 'app/main/templates/media-group-edit-screen.php';

			wp_nonce_field( 'groups_edit_save_' . $this->slug );
		}

		/**
		 * Save edited group media.
		 *
		 * @param null/int $group_id Group id to save.
		 *
		 * @return bool
		 */
		public function edit_screen_save( $group_id = null ) {
			global $bp;

			$is_save = sanitize_text_field( filter_input( INPUT_POST, 'save', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_EMPTY_STRING_NULL ) );

			/**
			 * Updated the following condition
			 * if ( ! empty( $is_save ) ) {
			 * it was returning false even when $is_save variable was not empty
			 * remove this comment after sometime
			 */
			if ( empty( $is_save ) ) {
				return false;
			}

			/**
			 * Remove The ' ' [ syntax mistake ]
			 * Add PLAYLIST CREATION CONTROL save functionality
			 * By: Yahil
			 */
			$rt_album_creation_control      = sanitize_text_field( filter_input( INPUT_POST, 'rt_album_creation_control', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
			$rtmp_playlist_creation_control = sanitize_text_field( filter_input( INPUT_POST, 'rtmp_playlist_creation_control', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );

			check_admin_referer( 'groups_edit_save_' . $this->slug );

			if ( isset( $rt_album_creation_control ) && ! empty( $rt_album_creation_control ) ) {
				$success = groups_update_groupmeta( bp_get_current_group_id(), 'rt_media_group_control_level', $rt_album_creation_control );
				do_action( 'rtmedia_edit_save_group_media_settings', $_POST );
				$success = true;
			} else {
				$success = false;
			}

			if ( isset( $rtmp_playlist_creation_control ) && ! empty( $rtmp_playlist_creation_control ) ) {
				$success = groups_update_groupmeta( bp_get_current_group_id(), 'rtmp_create_playlist_control_level', $rtmp_playlist_creation_control );
				do_action( 'rtmedia_edit_save_group_media_settings', $_POST );
				$success = true;
			}

			// To post an error/success message to the screen, use the following.
			if ( ! $success ) {
				bp_core_add_message( esc_html__( 'There was an error saving, please try again', 'buddypress-media' ), 'error' );
			} else {
				bp_core_add_message( esc_html__( 'Settings saved successfully', 'buddypress-media' ) );
			}

			if ( isset( $bp->version ) && version_compare( $bp->version, '12.0.0', 'ge' ) ) {
				$group_permalink = bp_get_group_url( $bp->groups->current_group );
			} else {
				$group_permalink = bp_get_group_permalink( $bp->groups->current_group );
			}

			bp_core_redirect( $group_permalink . '/admin/' . $this->slug );
		}

		/**
		 * The display method for the extension
		 *
		 * @since BuddyPress Media 2.3
		 *
		 * @global type $bp_media
		 */
		public function widget_display() {
			?>
				<div class="info-group" >
					<h4><?php echo esc_html( $this->name ); ?></h4>
					<p>
					<?php esc_html_e( 'You could display a small snippet of information from your group extension here. It will show on the group home screen.', 'buddypress-media' ); ?>
					</p>
				</div>
			<?php
		}
	}

endif; // end class_exists 'BP_Group_Extension'.
