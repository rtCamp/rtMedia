<?php
/**
 * Template for RTMediaAdmin::render_page().
 *
 * @package rtMedia
 */

?>

<div id="bp-media-settings-boxes" class="bp-media-settings-boxes-container rtm-setting-container">

	<?php
	if ( 'rtmedia-settings' === $page_name ) {
		?>
		<form id="bp_media_settings_form" name="bp_media_settings_form" method="post" enctype="multipart/form-data">
			<div class="bp-media-metabox-holder">
				<div class="rtm-button-container top">
					<?php
					$is_setting_save = filter_input( INPUT_GET, 'settings-saved', FILTER_VALIDATE_BOOLEAN );
					if ( ! empty( $is_setting_save ) ) {
						?>
						<div class="rtm-success rtm-fly-warning rtm-save-settings-msg">
							<?php esc_html_e( 'Settings saved successfully!', 'buddypress-media' ); ?>
						</div>
					<?php } ?>
					<input type="hidden" name="rtmedia-options-save" value="true">
					<input type="submit" class="rtmedia-settings-submit button button-primary button-big" value="<?php esc_attr_e( 'Save Settings', 'buddypress-media' ); ?>">
				</div>
				<?php
				settings_fields( $option_group );
				if ( 'rtmedia-settings' === $page_name ) {
					echo '<div id="rtm-settings-tabs">';
					RTMediaFormHandler::rtForm_settings_tabs_content( $page_name, $settings_sub_tabs );
					echo '</div>';
				} else {
					do_settings_sections( $page_name );
				}
				?>

				<div class="rtm-button-container bottom">

					<div class="rtm-social-links alignleft">
						<a href="http://twitter.com/rtMediaWP" class="twitter" target="_blank">
							<span class="dashicons dashicons-twitter"></span>
						</a>
						<a href="https://www.facebook.com/rtmediawp" class="facebook" target="_blank">
							<span class="dashicons dashicons-facebook"></span>
						</a>
						<a href="http://profiles.wordpress.org/rtcamp" class="wordpress" target="_blank">
							<span class="dashicons dashicons-wordpress"></span>
						</a>
						<a href="https://rtmedia.io/feed/" class="rss" target="_blank">
							<span class="dashicons dashicons-rss"></span>
						</a>
					</div>

					<input type="hidden" name="rtmedia-options-save" value="true">
					<input type="submit" class="rtmedia-settings-submit button button-primary button-big" value="<?php esc_attr_e( 'Save Settings', 'buddypress-media' ); ?>">
				</div>
			</div>
		</form>
		<?php
	} else {
		?>
		<div class="bp-media-metabox-holder">
			<?php
			if ( 'rtmedia-addons' === $page_name ) {
				RTMediaAddon::render_addons( $page_name );
			} elseif ( 'rtmedia-support' === $page_name ) {
				$rtmedia_support = new RTMediaSupport( false );
				$rtmedia_support->render_support( $page_name );
			} elseif ( 'rtmedia-themes' === $page_name ) {
				RTMediaThemes::render_themes( $page_name );
			} else {
				if ( 'rtmedia-license' === $page_name ) {
					RTMediaLicense::render_license( $page_name );
				} else {
					do_settings_sections( $page_name );
				}
			}
			do_action( 'rtmedia_admin_page_insert', $page_name );
			?>
		</div>
		<?php
		do_action( 'rtmedia_admin_page_append', $page_name );
	}
	?>
</div>
