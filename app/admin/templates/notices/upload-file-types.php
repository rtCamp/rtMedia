<?php
/**
 * Template for RTMediaAdmin::upload_filetypes_error().
 *
 * @package rtMedia
 */

if ( ! empty( $rtmedia->options['images_enabled'] ) ) {
	$not_supported_image = array_diff( array( 'jpg', 'jpeg', 'png', 'gif' ), $upload_filetypes );
	if ( ! empty( $not_supported_image ) ) {
		?>
		<div class="error upload-filetype-network-settings-error">
			<p>
				<?php wp_nonce_field( '_rtm_file_type_error_', 'rtm-file-type-error' ); ?>
				<?php
				// translators: 1. Not supported image types.
				printf( esc_html__( 'You have images enabled on rtMedia but your network allowed filetypes do not permit uploading of %s. Click ', 'buddypress-media' ), esc_html( implode( ', ', $not_supported_image ) ) );
				?>
				<a href="<?php echo esc_url( network_admin_url( 'settings.php#upload_filetypes' ) ); ?>">
					<?php esc_html_e( 'here', 'buddypress-media' ); ?>
				</a>
				<?php esc_html_e( ' to change your settings manually.', 'buddypress-media' ); ?>
				<br />
				<strong><?php esc_html_e( 'Recommended:', 'buddypress-media' ); ?></strong>
				<input type="button" class="button update-network-settings-upload-filetypes" value="<?php esc_attr_e( 'Update Network Settings Automatically', 'buddypress-media' ); ?>">
				<img style="display:none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" />
			</p>
		</div>
		<?php
		$flag = true;
	}
}

if ( ! empty( $rtmedia->options['videos_enabled'] ) ) {
	if ( ! in_array( 'mp4', $upload_filetypes, true ) ) {
		?>
		<div class="error upload-filetype-network-settings-error">
			<p>
				<?php esc_html_e( 'You have video enabled on BuddyPress Media but your network allowed filetypes do not permit uploading of mp4. Click ', 'buddypress-media' ); ?>
				<a href="<?php echo esc_url( network_admin_url( 'settings.php#upload_filetypes' ) ); ?>">
					<?php esc_html_e( 'here', 'buddypress-media' ); ?>
				</a>
				<?php esc_html_e( ' to change your settings manually.', 'buddypress-media' ); ?>
				<br />
				<strong><?php esc_html_e( 'Recommended:', 'buddypress-media' ); ?></strong>
				<input type="button" class="button update-network-settings-upload-filetypes" value="<?php esc_attr_e( 'Update Network Settings Automatically', 'buddypress-media' ); ?>">
				<img style="display:none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" />
			</p>
		</div>
		<?php
		$flag = true;
	}
}

if ( ! empty( $rtmedia->options['audio_enabled'] ) ) {
	if ( ! in_array( 'mp3', $upload_filetypes, true ) ) {
		?>
		<div class="error upload-filetype-network-settings-error">
			<p>
				<?php esc_html_e( 'You have audio enabled on BuddyPress Media but your network allowed filetypes do not permit uploading of mp3. Click ', 'buddypress-media' ); ?>
				<a href="<?php echo esc_url( network_admin_url( 'settings.php#upload_filetypes' ) ); ?>">
					<?php esc_html_e( 'here', 'buddypress-media' ); ?>
				</a>
				<?php esc_html_e( ' to change your settings manually.', 'buddypress-media' ); ?>
				<br />
				<strong><?php esc_html_e( 'Recommended:', 'buddypress-media' ); ?></strong>
				<input type="button" class="button update-network-settings-upload-filetypes" value="<?php esc_attr_e( 'Update Network Settings Automatically', 'buddypress-media' ); ?>">
				<img style="display:none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" />
			</p>
		</div>
		<?php
		$flag = true;
	}
}

if ( $flag ) {
	?>
	<script type="text/javascript">
		jQuery('.upload-filetype-network-settings-error').on('click', '.update-network-settings-upload-filetypes', function () {
			jQuery('.update-network-settings-upload-filetypes').siblings('img').show();
			jQuery('.update-network-settings-upload-filetypes').prop('disabled', true);
			jQuery.post(ajaxurl, {action: 'rtmedia_correct_upload_filetypes', _rtm_nonce: jQuery('rtm-file-type-error').val()}, function (response) {
				if (response) {
					jQuery('.upload-filetype-network-settings-error:first').after('<div style="display: none;" class="updated rtmedia-network-settings-updated-successfully"><p><?php esc_html_e( 'Network settings updated successfully.', 'buddypress-media' ); ?></p></div>');
					jQuery('.upload-filetype-network-settings-error').remove();
					jQuery('.bp-media-network-settings-updated-successfully').show();
				}
			});
		});
	</script>
	<?php
}
