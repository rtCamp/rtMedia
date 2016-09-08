<?php

/**
 * Created by PhpStorm.
 * User: ritz
 * Date: 18/9/14
 * Time: 5:05 PM
 */
class RTMediaLicense {
	static $page;

	static function render_license( $page = '' ) {
		self::$page = $page;

		$tabs            = apply_filters( 'rtmedia_license_tabs', array() );
		$addon_installed = false;

		if ( ! empty( $tabs ) && is_array( $tabs ) ) {
			$addon_installed = true;

			/**
			 * Grid layout for addon license keys
			 * Design Credits: Pippin Williamson (https://easydigitaldownloads.com/)
			 */
			?>
			<form method="post" class="license-form">
				<div id="rtm-licenses" class="license-row">
					<?php
					foreach ( $tabs as $key => $tab ) {
						self::render_license_section( self::$page, $tab );
					}
					?>
				</div>
				<div class="rtml-submit-wrapper">
					<?php submit_button( 'Save Changes' ); ?>
				</div>
			</form>
			<?php
		}

		// For add-on which aren't updated with the latest code
		if ( did_action( 'rtmedia_addon_license_details' ) ) {
			$addon_installed = true;
			?>
			<div id="rtm-licenses" class="license-row">
				<?php do_action( 'rtmedia_addon_license_details' ); ?>
			</div>
			<?php
		}

		if ( ! $addon_installed ) {
			?>
			<div class="rtm-license-404"><?php esc_html_e( 'You may be interested in', 'buddypress-media' ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=rtmedia-addons' ) ); ?>"><?php esc_html_e( 'rtMedia Addons', 'buddypress-media' ); ?></a>.</div>
			<?php
		}
	}

	static function render_license_section( $page = '', $tab = '' ) {

		$args			   = $tab['args'];
		$license		   = ( isset( $args['license_key'] ) ) ? $args['license_key'] : false;
		$status			   = ( isset( $args['status'] ) ) ? $args['status'] : false;
		$el_id			   = $args['addon_id'];
		$license_key_id	   = $args['key_id'];
		$license_status_id = $args['status_id'];
		$license_data 	   = get_option( 'edd_' . $el_id . '_active', '' );
		?>
		<div class="large-4 medium-6 small-12 license-column">

			<div class="rtm-addon-license">
				<h4 class="title">
					<span><?php echo esc_html( $tab['title'] ); ?></span>
				</h4>

				<div class="license-inner">
					<input id="<?php echo esc_attr( $license_key_id ) ?>" name="<?php echo esc_attr( $license_key_id ) ?>" type="text" class="regular-text" value="<?php echo esc_attr( $license ); ?>" />
					<?php
					$nonce_action = 'edd_' . $el_id . '_nonce';
					$nonce_name   = 'edd_' . $el_id . '_nonce';

					if ( false !== $status && 'valid' === $status ) {
						$btn_name = 'edd_' . $el_id . '_license_deactivate';
						$btn_val  = esc_attr__( 'Deactivate License', 'buddypress-media' );
					} else {
						$btn_name = 'edd_' . $el_id . '_license_activate';
						$btn_val  = esc_attr__( 'Activate License', 'buddypress-media' );
					}

					wp_nonce_field( $nonce_action, $nonce_name );
					?>

					<input type="submit" class="button-secondary" name="<?php echo esc_attr( $btn_name ); ?>" value="<?php echo esc_attr( $btn_val ); ?>" />

					<?php /* ?>
					*** Classes to be append with `license-message` ***

					* warning
					* success
					* info
					* alert

					<?php */ ?>
					<div class="license-message info">
						<?php esc_html_e( 'Your license key expires on October 8, 2017.', 'buddypress-media' ); ?>
					</div>

				</div><!-- End of .license-inner -->
			</div><!-- End of .rtm-addon-license -->
		</div><!-- End of .license-column -->
		<?php
	}
}
