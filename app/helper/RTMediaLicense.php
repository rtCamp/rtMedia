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
			foreach ( $tabs as $key => $tab ) {
				//$tabs[ $key ]['callback'] = array( 'RTMediaLicense', 'render_license_section' );
			}
			?>
			<div id="rtm-licenses">
				<?php //RTMediaAdmin::render_admin_ui( self::$page, $tabs ); ?>
				<?php
					foreach ( $tabs as $key => $tab ) {
						//$tabs[ $key ]['callback'] = array( 'RTMediaLicense', 'render_license_section' );
						self::render_license_section( self::$page, $tab );
					}
				?>
			</div>
			<?php
		}

		// For add-on which aren't updated with the latest code
		if ( did_action( 'rtmedia_addon_license_details' ) ) {
			$addon_installed = true;
			?>
			<div id="rtm-licenses">
				<?php do_action( 'rtmedia_addon_license_details' ); ?>
			</div>
			<?php
		}
		if ( ! $addon_installed ) {
			?>
			<div class="rtm-license-404">You may be interested in <a
					href="<?php echo esc_url( admin_url( 'admin.php?page=rtmedia-addons' ) ); ?>">rtMedia Addons</a>.
			</div>
			<?php
		}
	}

	static function render_license_section( $page = '', $tab = '' ) {

		$args = $tab['args'];

		$license = ( isset( $args['license_key'] ) ) ? $args['license_key'] : false;
		$status  = ( isset( $args['status'] ) ) ? $args['status'] : false;

		if ( false !== $status && 'valid' === $status ) {
			$status_class = 'activated rtm-success';
			$status_value = esc_attr__( 'Activated', 'buddypress-media' );

		} else {
			$status_class = 'deactivated rtm-warning';
			$status_value = esc_attr__( 'Deactivated', 'buddypress-media' );
		}

		if( $tab['title'] == "rtMedia Photo Tagging" ){

			$ch = curl_init();

			$postData = array(
			    'edd_action' => 'check_license',
			    'item_name' => 'rtMedia Photo Filters',
			    'license' => 'ae6607a7887c363f0838fc3410fbcdc8',
				'url'=> 'http://test.rtcamp.net/'
			);

			curl_setopt_array($ch, array(
			    CURLOPT_URL => 'http://edd.rtcamp.info/',
			    CURLOPT_POST => true,
				CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_POSTFIELDS => $postData,
			));

			//$output = json_decode( curl_exec($ch) );
			//echo date("F jS, Y",strtotime( $output->expires ));
			curl_close($ch);

		}
		$el_id             = $args['addon_id'];
		$license_key_id    = $args['key_id'];
		$license_status_id = $args['status_id'];
		?>
		<!-- style="background-color: #fff; width: 30%; display: inline-block; float: left; padding: 10px;" -->
		<div class="rtm-addon-license" >
			<h4 class="title">
				<span><?php echo $tab['title']; ?></span>
			</h4>

			<!-- <div class="rtm-license-status-wrap <?php echo esc_attr( $status_class ) ?>">
				<span
					class="rtm-addon-license-status-label"><?php esc_html_e( 'Status: ', 'buddypress-media' ); ?></span>
				<span class="rtm-addon-license-status"><?php echo esc_attr( $status_value ); ?></span>
			</div> -->

			<form method="post">
				<!-- <table class="form-table">
					<tbody>
					<tr> -->
						<!-- <th scope="row">
							<?php //esc_html_e( 'License Key', 'buddypress-media' ); ?>
						</th> -->
						<!-- <td> -->
							<input id="<?php echo esc_attr( $license_key_id ) ?>"
							       name="<?php echo esc_attr( $license_key_id ) ?>" type="text"
							       class="regular-text" value="<?php echo esc_attr( $license ); ?>" style="width: 100%;"/>
						<!-- </td> -->
					<!-- </tr> -->

					<?php //if ( false !== $license ) { ?>
						<!-- <tr> -->
							<!-- <th scope="row">
								<?php //esc_html_e( 'Activate / Deactivate License', 'buddypress-media' ); ?>
							</th> -->
							<!-- <td> -->
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
								<input type="submit" class="button-secondary"
								       name="<?php echo esc_attr( $btn_name ); ?>" value="<?php echo esc_attr( $btn_val ); ?>"/>
							<!-- </td>
						</tr> -->
					<?php //} ?>
					<!-- </tbody>
				</table> -->
				<?php //submit_button( 'Save Key' ); ?>
			</form>
		</div>
		<?php
	}
}
