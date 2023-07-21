<?php
/**
 * Template for RTMediaAdmin::rtmedia_premium_addon_notice().
 *
 * @package rtMedia
 */

?>

<div class="notice is-dismissible updated rtmedia-pro-split-notice">
	<?php wp_nonce_field( 'rtcamp_pro_split', 'rtm_nonce' ); ?>
	<p>
		<span>
			<?php
			$product_page = esc_url( 'https://rtmedia.io/products/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media' );

			$message = apply_filters(
				'rt_premium_addon_notice_message',
				sprintf(
						/* translators: %s: Product page link. */
					__( 'Check 30+ premium rtMedia add-ons on our <a href="%s">store</a>.', 'buddypress-media' ),
					$product_page
				),
				$product_page
			);
			?>
			<b><?php esc_html_e( 'rtMedia: ', 'buddypress-media' ); ?></b>
			<?php echo wp_kses( $message, array( 'a' => array( 'href' => array() ) ) ); ?>
		</span>
	</p>
</div>

<script type="text/javascript">
	jQuery( document ).ready( function() {
		jQuery( '.rtmedia-pro-split-notice.is-dismissible' ).on( 'click', '.notice-dismiss', function() {
			var data = {action: 'rtmedia_hide_premium_addon_notice', _rtm_nonce: jQuery('#rtm_nonce').val() };
			jQuery.post( ajaxurl, data, function ( response ) {
				jQuery('.rtmedia-pro-split-notice').remove();
			});
		});
	});
</script>
