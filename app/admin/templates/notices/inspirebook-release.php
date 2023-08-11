<?php
/**
 * Template for RTMediaAdmin::rtmedia_inspirebook_release_notice().
 *
 * @package rtMedia
 */

?>

<div class="notice is-dismissible updated rtmedia-inspire-book-notice">
	<?php wp_nonce_field( '_install_transcoder_hide_notice_', 'install_transcoder_hide_notice_nonce' ); ?>
	<p>
		<span>
			<a href="https://rtmedia.io/products/inspirebook/" target="_blank">
				<b><?php esc_html_e( 'Meet InspireBook', 'buddypress-media' ); ?></b>
			</a>
			<?php esc_html_e( ' - First official rtMedia premium theme.', 'buddypress-media' ); ?>
		</span>
		<?php wp_nonce_field( '_rtmedia_hide_inspirebook_notice_', 'rtmedia_hide_inspirebook_nonce' ); ?>
	</p>
</div>

<script type="text/javascript">
	jQuery( document ).ready( function() {
		jQuery( '.rtmedia-inspire-book-notice.is-dismissible' ).on( 'click', '.notice-dismiss', function() {
			var data = {
				action: 'rtmedia_hide_inspirebook_release_notice',
				_rtm_nonce: jQuery('#rtmedia_hide_inspirebook_nonce').val()
			};
			jQuery.post( ajaxurl, data, function ( response ) {
				jQuery('.rtmedia-inspire-book-notice').remove();
			});
		});
	});
</script>
