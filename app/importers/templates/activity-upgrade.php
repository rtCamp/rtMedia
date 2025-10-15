<?php
/**
 * Template for Activity Upgrade - RTMediaActivityUpgrade::init().
 *
 * @package rtMedia
 */

?>

<div class="wrap">
	<h2><?php esc_html_e( 'rtMedia: Upgrade rtMedia activity', 'buddypress-media' ); ?></h2>
	<?php
	wp_nonce_field( 'rtmedia_media_activity_upgrade_nonce', 'rtmedia_media_activity_upgrade_nonce' );

	printf(
		'<span class="pending">%1$s %2$s</span><br />',
		esc_html( rtmedia_migrate_formatseconds( $total - $done ) ),
		esc_html__( '(estimated)', 'buddypress-media' )
	);

	printf(
		'<span class="finished">%1$s</span>/<span class="total">%2$s</span>',
		esc_html( $done ),
		esc_html( $total )
	);

	echo '<img src="images/loading.gif" alt="syncing" id="rtMediaSyncing" style="display:none" />';

	$temp = $prog->progress( $done, $total );
	$prog->progress_ui( $temp, true );
	// No a security issue, so keeping the style here.
	?>
	<style type="text/css">
		#rtprogressbar {
			background-color: #444;
			border-radius: 13px;
			margin-bottom: 10px;
			padding: 3px;
		}

		#rtprogressbar div {
			background-color: #fb6003;
			border-radius: 10px;
			height: 20px;
			width: 0;
		}
	</style>
	<div id="rtm-importer-root"
		data-importer="activity-upgrade"
		data-done="<?php echo esc_attr( $done ); ?>"
		data-total="<?php echo esc_attr( $total ); ?>"
		data-last-id="<?php echo esc_attr( $last_id ); ?>"
		data-admin-ajax="<?php echo esc_url( $admin_ajax ); ?>"
		data-nonce-field-id="rtmedia_media_activity_upgrade_nonce">
	</div>
	<hr/>
	<?php if ( ! ( isset( $rtmedia_error ) && true === $rtmedia_error ) ) { ?>
		<input type="button" id="submit" value="start" class="button button-primary"/>
	<?php } ?>
</div>
