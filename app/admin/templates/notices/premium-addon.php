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
			// translators: 1. Product page link.
			$message = sprintf(
					__( 'Check 30+ premium rtMedia add-ons on our <a href="%1$s">store</a>.', 'buddypress-media' ),
				'https://rtmedia.io/products/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media'
			);
			?>
			<b><?php esc_html_e( 'rtMedia: ', 'buddypress-media' ); ?></b>
			<?php echo wp_kses( $message, array( 'a' => array( 'href' => array(), ), ) ); ?>
		</span>
	</p>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('.rtmedia-pro-split-notice.is-dismissible').on('click', '.notice-dismiss', function () {
            var data = {action: 'rtmedia_hide_premium_addon_notice', _rtm_nonce: jQuery('#rtm_nonce').val()};
            jQuery.post(ajaxurl, data, function (response) {
                jQuery('.rtmedia-pro-split-notice').remove();
            });
        });
    });
</script>