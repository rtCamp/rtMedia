<?php

/**
 * Created by PhpStorm.
 * User: ritz
 * Date: 18/9/14
 * Time: 5:05 PM
 */
class RTMediaLicense {

	static function render_license( $page = '' ){
		global $wp_actions;

		if ( has_action( 'rtmedia_addon_license_details' ) ){
			?>
			<div id="rtm-licenses">
		<?php
		}

		do_action( 'rtmedia_addon_license_details' );

		if ( has_action( 'rtmedia_addon_license_details' ) ){
			?>
			</div>
		<?php
		} else {
			?>
			<div>You may be interested in <a href="<?php echo admin_url( 'admin.php?page=rtmedia-addons' ) ?>">rtMedia Addons</a>.</div>
		<?php
		}
	}

} 