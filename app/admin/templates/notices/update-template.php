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
		<a href="#" id="rtmedia-hide-template-notice" data-nonce="<?php echo esc_js( wp_create_nonce( 'rtmedia_template_notice' ) ); ?>" style="float:right">
			<?php esc_html_e( 'Hide', 'buddypress-media' ); ?>
		</a>
	</p>
</div>

