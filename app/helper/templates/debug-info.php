<?php
/**
 * Template for debug info - RTMediaSupport::debug_info_html().
 *
 * @package rtMedia
 */

?>

<div id="debug-info" class="rtm-option-wrapper">

	<h3 class="rtm-option-title"><?php esc_html_e( 'Debug Info', 'buddypress-media' ); ?></h3>

	<table class="form-table rtm-debug-info">
		<tbody>
		<?php
		if ( $debug_info ) {
			foreach ( $debug_info as $configuration => $value ) {
				?>
				<tr>
					<th scope="row"><?php echo esc_html( $configuration ); ?></th>
					<td><?php echo wp_kses( $value, $allowed_html ); ?></td>
				</tr>
				<?php
			}
		}
		?>
		</tbody>
	</table>

	<div class="rtm-download-debuginfo">
		<form action="<?php echo esc_url( admin_url( 'admin.php?page=rtmedia-support#debug' ) ); ?>" method="post">
			<?php wp_nonce_field( 'rtmedia-download-debuginfo', 'download_debuginfo_wpnonce' ); ?>
			<input type="hidden" name="download_debuginfo" id="download_debuginfo" value="1" />
			<input type="submit" value="<?php esc_html_e( 'Download Debug Info', 'buddypress-media' ); ?>" class="button button-primary" />
		</form>
	</div>

</div>
