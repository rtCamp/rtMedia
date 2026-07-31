<?php
/**
 * Template for RTMediaAdmin::rtmedia_addon_update_notice().
 *
 * @package rtMedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="notice error is-dismissible rtmedia-addon-update-notice">
	<p>
		<?php
		$message = apply_filters( 'rt_addon_update_notice', sprintf( __( ' rtMedia Premium update is available. Please update it from the plugins or download it from <a href = "https://rtmedia.io/my-account/" target="_blank" >your account</a>', 'buddypress-media' ) ) ); /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
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
