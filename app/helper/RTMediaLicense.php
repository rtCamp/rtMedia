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
			// foreach ( $tabs as $key => $tab ) {
				//$tabs[ $key ]['callback'] = array( 'RTMediaLicense', 'render_license_section' );
			// }
			?>
			<form method="post" class="license-form">
				<div id="rtm-licenses" class="license-row">
					<?php //RTMediaAdmin::render_admin_ui( self::$page, $tabs ); ?>

					<?php
					foreach ( $tabs as $key => $tab ) {
						// $tabs[ $key ]['callback'] = array( 'RTMediaLicense', 'render_license_section' );
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

		$args		= $tab['args'];

		$license	= ( isset( $args['license_key'] ) ) ? $args['license_key'] : false;
		$status		= ( isset( $args['status'] ) ) ? $args['status'] : false;

		if ( false !== $status && 'valid' === $status ) {
			$status_class = 'activated rtm-success';
			$status_value = esc_attr__( 'Activated', 'buddypress-media' );

		} else {
			$status_class = 'deactivated rtm-warning';
			$status_value = esc_attr__( 'Deactivated', 'buddypress-media' );
		}

		// if ( 'rtMedia Photo Tagging' === $tab['title'] ) {

		// 	$ch = curl_init();

		// 	$postData = array(
		// 		'edd_action'	=> 'check_license',
		// 		'item_name'		=> 'rtMedia Photo Filters',
		// 		'license'		=> 'ae6607a7887c363f0838fc3410fbcdc8',
		// 		'url'			=> 'http://test.rtcamp.net/',
		// 	);

		// 	curl_setopt_array($ch, array(
		// 		CURLOPT_URL				=> 'http://edd.rtcamp.info/',
		// 		CURLOPT_POST			=> true,
		// 		CURLOPT_RETURNTRANSFER	=> true,
		// 		CURLOPT_POSTFIELDS		=> $postData,
		// 	));

		// 	// $output = json_decode( curl_exec( $ch ) );
		// 	// echo date( 'F jS, Y', strtotime( $output->expires ) );

		// 	curl_close( $ch );

		// }

		$el_id             = $args['addon_id'];
		$license_key_id    = $args['key_id'];
		$license_status_id = $args['status_id'];
		?>
		<?php
		/* style="background-color: #fff; width: 30%; display: inline-block; float: left; padding: 10px;" */
		?>
		<div class="large-4 medium-6 small-12 license-column">

			<div class="rtm-addon-license">
				<h4 class="title">
					<span><?php echo esc_html( $tab['title'] ); ?></span>
				</h4>

				<div class="license-inner">

				<?php /* ?>
				<div class="rtm-license-status-wrap <?php echo esc_attr( $status_class ) ?>">
					<span
						class="rtm-addon-license-status-label"><?php esc_html_e( 'Status: ', 'buddypress-media' ); ?></span>
					<span class="rtm-addon-license-status"><?php echo esc_attr( $status_value ); ?></span>
				</div>
				<?php */ ?>


					<?php /* ?>
					<table class="form-table">
					<tbody>
					<tr>
					<th scope="row">
					<?php esc_html_e( 'License Key', 'buddypress-media' ); ?>
					</th>
					<td>
					<?php */ ?>
					<input id="<?php echo esc_attr( $license_key_id ) ?>" name="<?php echo esc_attr( $license_key_id ) ?>" type="text" class="regular-text" value="<?php echo esc_attr( $license ); ?>" />
					<?php /* ?>
					</td>
					</tr>
					<?php */ ?>
					<?php /*
					if ( false !== $license ) { ?>
					<tr>
					<th scope="row">
					<?php esc_html_e( 'Activate / Deactivate License', 'buddypress-media' ); ?>
					</th>
					<td>
					<?php */ ?>

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
					?>
					<?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
					<input type="submit" class="button-secondary" name="<?php echo esc_attr( $btn_name ); ?>" value="<?php echo esc_attr( $btn_val ); ?>" />

					<?php /* ?>
					</td>
					</tr>
					<?php } ?>
					</tbody>
					</table>
					<?php submit_button( 'Save Key' );
					*/
					?>

					<?php /* ?>
					*** Classes to be append with `license-message` ***

					* warning
					* success
					* info
					* alert

					<?php */ ?>
					<div class="license-message info"><?php esc_html_e( 'Your license key expires on October 8, 2017.', 'buddypress-media' ); ?></div>

				</div><!-- End of .license-inner -->
			</div><!-- End of .rtm-addon-license -->
		</div><!-- End of .license-column -->
		<?php
	}
}
