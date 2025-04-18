<?php
/**
 * Template for RTMediaAdmin::install_transcoder_admin_notice().
 *
 * @package rtMedia
 */

?>

<div class="notice notice-info install-transcoder-notice is-dismissible">
	<?php wp_nonce_field( '_install_transcoder_hide_notice_', 'install_transcoder_hide_notice_nonce' ); ?>
	<p>
		<?php
		$allowed_tags = array(
			'a' => array(
				'href'   => array(),
				'target' => array(),
			),
		);
		echo wp_kses( __( 'Install <a href="https://godam.io" target="_blank">GoDAM plugin</a> which includes powerful Digital Asset Management features along with video transcoding services.', 'buddypress-media' ), $allowed_tags );
		?>
	</p>
</div>
<script type="text/javascript">
	jQuery( document ).ready( function() {
		jQuery( '.install-transcoder-notice.is-dismissible' ).on( 'click', '.notice-dismiss', function() {
			var data = {
				action: 'install_transcoder_hide_admin_notice',
				install_transcoder_notice_nonce: jQuery('#install_transcoder_hide_notice_nonce').val()
			};
			jQuery.post( ajaxurl, data, function ( response ) {
				jQuery('.install-transcoder-notice').remove();
			});
		});
	});
</script>
