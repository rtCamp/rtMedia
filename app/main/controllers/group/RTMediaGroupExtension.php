<?php
/**
 * Description of BPMediaGroupLoader
 *
 * @author faishal
 */
if ( class_exists( 'BP_Group_Extension' ) ) :// Recommended, to prevent problems during upgrade or when Groups are disabled

	class RTMediaGroupExtension extends BP_Group_Extension {

		function __construct() {
			$this->name = RTMEDIA_MEDIA_LABEL;
			$this->slug = RTMEDIA_MEDIA_SLUG . '-setting';
			$this->create_step_position = 21;
			$this->enable_nav_item = false;
		}

		function create_screen( $group_id = null ) {

			if ( ! bp_is_group_creation_step( $this->slug ) ) {
				return false; }
			// HOOK to add PER GROUP MEDIA enable/diable option in rtMedia PRO
			do_action( 'rtmedia_group_media_control_create' );

			global $rtmedia;
			$options = $rtmedia->options; ?>
			<div class='rtmedia-group-media-settings'>
			<?php if ( isset( $options['general_enableAlbums'] ) && 1 === intval( $options['general_enableAlbums'] ) ) {   // album is enabled ?>

					<h4><?php esc_html_e( 'Album Creation Control', 'buddypress-media' ); ?></h4>
					<p><?php esc_html_e( 'Who can create Albums in this group?', 'buddypress-media' ); ?></p>
					<div class="radio">
						<label>
							<input name="rt_album_creation_control" type="radio" id="rt_media_group_level_all" checked="checked" value="all">
							<strong><?php esc_html_e( 'All Group Members', 'buddypress-media' ); ?></strong>
						</label>
						<label>
							<input name="rt_album_creation_control" type="radio" id="rt_media_group_level_moderators" value="moderators">
							<strong><?php esc_html_e( 'Group Admins and Mods only', 'buddypress-media' ); ?></strong>
						</label>
						<label>
							<input name="rt_album_creation_control" type="radio" id="rt_media_group_level_admin" value="admin">
							<strong><?php esc_html_e( 'Group Admin only', 'buddypress-media' ); ?></strong>
						</label>
					</div>

				<?php } ?>

				<?php do_action( 'rtmedia_playlist_creation_settings_create_group' ); ?>
			</div>
			<?php
			wp_nonce_field( 'groups_create_save_' . $this->slug );
		}

		/**
		 *
		 * @global type $bp
		 */
		function create_screen_save( $group_id = null ) {
			global $bp;

			check_admin_referer( 'groups_create_save_' . $this->slug );

			/**
			 * add playlist Save functionality
			 * By: Yahil
			 */
			$rt_album_creation_control = filter_input( INPUT_POST, 'rt_album_creation_control', FILTER_SANITIZE_STRING );
			$rtmp_playlist_creation_control = filter_input( INPUT_POST, 'rtmp_playlist_creation_control', FILTER_SANITIZE_STRING );

			/**
			 * save details 'ALBUM CREATION CONTROL' and 'PLAYLIST CREATION CONTROL'
			 * By: Yahil
			 */
			if ( isset( $rt_album_creation_control ) && ! empty( $rt_album_creation_control ) ) {
				groups_update_groupmeta( $bp->groups->new_group_id, 'rt_media_group_control_level', $rt_album_creation_control );
			}

			if ( isset( $rtmp_playlist_creation_control ) && ! empty( $rtmp_playlist_creation_control ) ) {
				groups_update_groupmeta( $bp->groups->new_group_id, 'rtmp_create_playlist_control_level', $rtmp_playlist_creation_control );
			}

				do_action( 'rtmedia_create_save_group_media_settings' , $_POST );
		}

		/**
		 *
		 * @param null/int $group_id
		 *
		 * @return bool
		 */
		function edit_screen( $group_id = null ) {
			if ( ! bp_is_group_admin_screen( $this->slug ) ) {
				return false; }
			$current_level = groups_get_groupmeta( bp_get_current_group_id(), 'rt_media_group_control_level' );
			if ( empty( $current_level ) ) {
				$current_level = 'all';
			}

			// HOOK to add PER GROUP MEDIA enable/diable option in rtMedia PRO
			do_action( 'rtmedia_group_media_control_edit' ); ?>

							<div class='rtmedia-group-media-settings'>

								<?php global $rtmedia;
								$options = $rtmedia->options;
								if ( isset( $options['general_enableAlbums'] ) && 1 === intval( $options['general_enableAlbums'] ) ) {  // album is enabled ?>

									<h4><?php esc_html_e( 'Album Creation Control', 'buddypress-media' ); ?></h4>
									<p><?php esc_html_e( 'Who can create Albums in this group?', 'buddypress-media' ); ?></p>
									<div class="radio">
										<label>
											<input name="rt_album_creation_control" type="radio" id="rt_media_group_level_moderators"  value="all"<?php checked( $current_level, 'all', true ) ?>>
											<strong><?php esc_html_e( 'All Group Members', 'buddypress-media' ); ?></strong>
										</label>
										<label>
											<input name="rt_album_creation_control" type="radio" id="rt_media_group_level_moderators" value="moderators" <?php checked( $current_level, 'moderators', true ) ?>>
											<strong><?php esc_html_e( 'Group Admins and Mods only', 'buddypress-media' ); ?></strong>
										</label>
										<label>
											<input name="rt_album_creation_control" type="radio" id="rt_media_group_level_admin" value="admin" <?php checked( $current_level, 'admin', true ) ?>>
											<strong><?php esc_html_e( 'Group Admin only', 'buddypress-media' ); ?></strong>
										</label>
									</div>
									<hr>
								<?php } ?>

								<?php do_action( 'rtmedia_playlist_creation_settings_groups_edit' ); ?>
							</div>
							<input type="submit" name="save" value="<?php esc_attr_e( 'Save Changes', 'buddypress-media' ); ?>" />
							<?php
							wp_nonce_field( 'groups_edit_save_' . $this->slug );
		}

		/**
		 *
		 * @param null/int $group_id
		 *
		 * @return bool
		 */
		function edit_screen_save( $group_id = null ) {
			global $bp;

			$is_save = filter_input( INPUT_POST, 'save', FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL );

			/**
			 * updated the following condition
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
			$rt_album_creation_control		= filter_input( INPUT_POST, 'rt_album_creation_control', FILTER_SANITIZE_STRING );
			$rtmp_playlist_creation_control	= filter_input( INPUT_POST, 'rtmp_playlist_creation_control', FILTER_SANITIZE_STRING );

			check_admin_referer( 'groups_edit_save_' . $this->slug );

			if ( isset( $rt_album_creation_control ) && ! empty( $rt_album_creation_control )  ) {
				$success = groups_update_groupmeta( bp_get_current_group_id(), 'rt_media_group_control_level', $rt_album_creation_control );
				do_action( 'rtmedia_edit_save_group_media_settings' , $_POST );
				$success = true;
			} else {
				$success = false;
			}

			if ( isset( $rtmp_playlist_creation_control ) && ! empty( $rtmp_playlist_creation_control ) ) {
				$success = groups_update_groupmeta( bp_get_current_group_id(), 'rtmp_create_playlist_control_level', $rtmp_playlist_creation_control );
				do_action( 'rtmedia_edit_save_group_media_settings' , $_POST );
				$success = true;
			}

			/* To post an error/success message to the screen, use the following */
			if ( ! $success ) {
				bp_core_add_message( esc_html__( 'There was an error saving, please try again', 'buddypress-media' ), 'error' );
			} else {
				bp_core_add_message( esc_html__( 'Settings saved successfully', 'buddypress-media' ) );
			}

				bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
		}

		/**
		 * The display method for the extension
		 *
		 * @since BuddyPress Media 2.3
		 */

		/**
		 *
		 * @global type $bp_media
		 */
		function widget_display() {
			?>
				<div class="info-group" >
					<h4><?php echo esc_html( $this->name ) ?></h4>
					<p>
					<?php esc_html_e( 'You could display a small snippet of information from your group extension here. It will show on the group home screen.', 'buddypress-media' ); ?>
					</p>
				</div>
			<?php
		}
	}

endif; // class_exists( 'BP_Group_Extension' )
