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
					<?php self::render_license_message( $license_data, $tab['title'] ); ?>
				</div><!-- End of .license-inner -->
			</div><!-- End of .rtm-addon-license -->
		</div><!-- End of .license-column -->
		<?php
	}

	static function render_license_message( $license = '', $addon_name = '' ) {

		$addon_name = isset( $license->item_name ) ? esc_html( $license->item_name ) : esc_html( $addon_name );
		$messages = array();

		if ( ! empty( $license ) && is_object( $license ) ) {

			// activate_license 'invalid' on anything other than valid, so if there was an error capture it
			if ( false === $license->success ) {

				switch ( $license->error ) {

					case 'expired' :

						$class = 'alert';
						$messages[] = sprintf(
							__( 'Your license key expired on %1$s. Please renew your license key.', 'buddypress-media' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'revoked' :

						$class = 'alert';
						$messages[] = __( 'Your license key has been disabled. Please contact support for more information.', 'buddypress-media' );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'missing' :

						$class = 'alert';
						$messages[] = sprintf(
							__( 'Invalid license. Please <a href="%s" target="_blank">visit your account page</a> and verify it.', 'buddypress-media' ), 'https://rtmedia.io/my-account/'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'invalid' :
					case 'site_inactive' :

						$class = 'alert';
						$messages[] = sprintf(
							__( 'Your %1$s is not active for this URL. Please <a href="%2$s" target="_blank">visit your account page</a> to manage your license key URLs.', 'buddypress-media' ),
							$addon_name,
							'https://rtmedia.io/my-account/'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'item_name_mismatch' :

						$class = 'alert';
						$messages[] = sprintf( __( 'This appears to be an invalid license key for %s.', 'buddypress-media' ), $addon_name );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'no_activations_left':

						$class = 'alert';
						$messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'buddypress-media' ), 'https://rtmedia.io/my-account/' );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'license_not_activable':

						$class = 'alert';
						$messages[] = sprintf( __( 'Your license is not activable, please visit <a href="%s">your account page</a>.', 'buddypress-media' ), 'https://rtmedia.io/my-account/' );

						$license_status = 'license-' . $class . '-notice';

						break;

					default :

						$class = 'alert';
						$messages[] = __( 'To receive updates, please enter your valid license key.', 'buddypress-media' );

						$license_status = 'license-' . $class . '-notice';
						break;
				}
			} else {

				switch ( $license->license ) {

					case 'valid' :
					default:

						$class = 'success';

						$now        = current_time( 'timestamp' );
						$expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

						if ( 'lifetime' === $license->expires ) {

							$messages[] = __( 'License key never expires.', 'buddypress-media' );

							$license_status = 'license-lifetime-notice';

						} elseif ( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {

							$class = 'warning';

							$messages[] = sprintf(
								__( 'Your license key expires soon! It expires on %1$s. Renew your license key.', 'buddypress-media' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
							);

							$license_status = 'license-expires-soon-notice';

						} else {

							$class = 'info';

							$messages[] = sprintf(
								__( 'Your license key expires on %s.', 'buddypress-media' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
							);

							$license_status = 'license-expiration-date-notice';

						}

						break;

				}
			}
		} else {
			$class = 'alert';

			$messages[] = __( 'To receive updates, please enter your valid license key.', 'buddypress-media' );

			$license_status = null;
		}

		$html = '';

		if ( ! empty( $messages ) ) {
			foreach ( $messages as $message ) {

				$html .= '<div class="license-message ' . esc_attr( $class ) . ' ' . esc_attr( $license_status ) . '">' . $message . '</div>';

			}
		}

		echo $html; // Please ignore PHPCS warning for $html
	}
}
