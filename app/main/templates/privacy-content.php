<?php
/**
 * Template for - RTMediaPrivacy::content().
 *
 * @package rtMedia
 */

?>

<form method="post">

	<div class="rtm_bp_default_privacy">

		<?php wp_nonce_field( 'rtmedia_member_settings_privacy', 'rtmedia_member_settings_privacy' ); ?>

		<div class="section">

			<div class="rtm-title"><h3><?php esc_html_e( 'Default Privacy', 'buddypress-media' ); ?></h3></div>

			<div class="rtm-privacy-levels">

				<?php foreach ( $rtmedia->privacy_settings['levels'] as $level => $data ) { ?>
					<label>
						<input type="radio" value="<?php echo esc_attr( $level ); ?>" name="rtmedia-default-privacy" <?php checked( intval( $default_privacy ), $level, true ); ?> />
						<?php echo esc_html( $data ); ?>
					</label>
					<br/>
				<?php } ?>

			</div>

		</div>

	</div>

	<div class="submit">
		<input type="submit" name="submit" value="<?php esc_attr_e( 'Save Changes', 'buddypress-media' ); ?>" id="submit" class="auto">
	</div>

</form>
