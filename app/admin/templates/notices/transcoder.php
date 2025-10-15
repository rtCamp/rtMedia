<?php
/**
 * Template for RTMediaAdmin::install_transcoder_admin_notice().
 *
 * @package rtMedia
 */

// Include plugin.php if not already loaded.
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// If GoDAM is active right now, set a permanent flag.
if ( is_plugin_active( 'godam/godam.php' ) ) {
	update_option( 'godam_plugin_activated_once', true );
}

// If the permanent flag is set, never show the notice.
if ( get_option( 'godam_plugin_activated_once' ) ) {
	return;
}

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

