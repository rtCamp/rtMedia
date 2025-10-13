<?php
/**
 * Template for RTMediaAdmin::rtmedia_addon_update_notice().
 *
 * @package rtMedia
 */

?>

<div class="notice error is-dismissible rtmedia-addon-update-notice">
	<p>
		<?php
		$message = apply_filters( 'rt_addon_update_notice', sprintf( __( ' rtMedia Premium update is available. Please update it from the plugins or download it from <a href = "https://rtmedia.io/my-account/" target="_blank" >your account</a>', 'buddypress-media' ) ) );
		?>
		<b><?php esc_html_e( 'rtMedia: ', 'buddypress-media' ); ?></b>
		<?php
		echo wp_kses(
			$message,
			array(
				'a' => array(
					'href' => array(),
					'target' => array(),
				),
			)
		);
		?>
		<?php wp_nonce_field( 'rtmedia-addon-update-notice-3_8', 'rtmedia-addon-notice' ); ?>
	</p>
</div>
