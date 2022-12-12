<?php
/**
 * Template for - RTMediaGroupExtension::edit_screen().
 *
 * @package rtMedia
 */

?>

<div class='rtmedia-group-media-settings'>

	<?php if ( isset( $options['general_enableAlbums'] ) && 1 === intval( $options['general_enableAlbums'] ) ) { // album is enabled. ?>

		<h4><?php esc_html_e( 'Album Creation Control', 'buddypress-media' ); ?></h4>

		<p><?php esc_html_e( 'Who can create Albums in this group?', 'buddypress-media' ); ?></p>

		<div class="radio">
			<label>
				<input name="rt_album_creation_control" type="radio" id="rt_media_group_level_moderators"  value="all"<?php checked( $current_level, 'all', true ); ?>>
				<strong><?php esc_html_e( 'All Group Members', 'buddypress-media' ); ?></strong>
			</label>
			<label>
				<input name="rt_album_creation_control" type="radio" id="rt_media_group_level_moderators" value="moderators" <?php checked( $current_level, 'moderators', true ); ?>>
				<strong><?php esc_html_e( 'Group Admins and Mods only', 'buddypress-media' ); ?></strong>
			</label>
			<label>
				<input name="rt_album_creation_control" type="radio" id="rt_media_group_level_admin" value="admin" <?php checked( $current_level, 'admin', true ); ?>>
				<strong><?php esc_html_e( 'Group Admin only', 'buddypress-media' ); ?></strong>
			</label>
		</div>

		<hr />

	<?php } ?>

	<?php do_action( 'rtmedia_playlist_creation_settings_groups_edit' ); ?>

</div>

<input type="submit" name="save" value="<?php esc_attr_e( 'Save Changes', 'buddypress-media' ); ?>" />
