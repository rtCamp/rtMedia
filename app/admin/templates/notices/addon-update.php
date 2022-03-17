<?php
/**
 * Template for RTMediaAdmin::rtmedia_addon_update_notice().
 *
 * @package rtMedia
 */

?>

<div class="error rtmedia-addon-upate-notice">
	<p>
		<strong><?php esc_html_e( 'rtMedia:', 'buddypress-media' ); ?></strong>
		<?php esc_html_e( 'Please update all premium add-ons that you have purchased from', 'buddypress-media' ); ?>
		<a href="https://rtmedia.io/my-account/" target="_blank"><?php esc_html_e( 'your account', 'buddypress-media' ); ?></a>.
		<a href="#" onclick="rtmedia_hide_addon_update_notice()" style="float:right"><?php esc_html_e( 'Dismiss', 'buddypress-media' ); ?></a>
		<?php wp_nonce_field( 'rtmedia-addon-update-notice-3_8', 'rtmedia-addon-notice' ); ?>
	</p>
</div>

<script type="text/javascript">
    function rtmedia_hide_addon_update_notice() {
        var data = {
            action: 'rtmedia_hide_addon_update_notice',
            _rtm_nonce: jQuery('#rtmedia-addon-notice').val(),
        };
        jQuery.post(ajaxurl, data, function (response) {
            response = response.trim();
            if (response === "1")
                jQuery('.rtmedia-addon-upate-notice').remove();
        });
    }
</script>
