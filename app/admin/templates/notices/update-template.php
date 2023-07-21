<?php
/**
 * Template for RTMediaAdmin::rtmedia_update_template_notice().
 *
 * @package rtMedia
 */

?>

<div class="error rtmedia-update-template-notice">
	<p>
		<?php esc_html_e( 'Please update rtMedia template files if you have overridden the default rtMedia templates in your theme. If not, you can ignore and hide this notice.', 'buddypress-media' ); ?>
		<a href="#" onclick="rtmedia_hide_template_override_notice('<?php echo esc_js( wp_create_nonce( 'rtmedia_template_notice' ) ); ?>')" style="float:right">
			<?php esc_html_e( 'Hide', 'buddypress-media' ); ?>
		</a>
	</p>
</div>

<script type="text/javascript">
	function rtmedia_hide_template_override_notice( rtmedia_template_notice_nonce ) {
		var data = {action: 'rtmedia_hide_template_override_notice', _rtm_nonce: rtmedia_template_notice_nonce };
		jQuery.post(ajaxurl, data, function (response) {
			response = response.trim();
			if ('1' === response)
				jQuery('.rtmedia-update-template-notice').remove();
		});
	}
</script>
