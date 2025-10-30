<?php
/**
 * Template for Activity Upgrade - RTMediaMediaSizeImporter::init().
 *
 * @package rtMedia
 */

?>

<div class="wrap">
	<h2><?php esc_html_e( 'rtMedia: Import Media Size', 'buddypress-media' ); ?></h2>
	<?php
	wp_nonce_field( 'rtmedia_media_size_import_nonce', 'rtmedia_media_size_import_nonce' );

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
	?>
	<div id="rtm-importer-root"
		data-importer="media-size"
		data-done="<?php echo esc_attr( $done ); ?>"
		data-total="<?php echo esc_attr( $total ); ?>"
		data-last-id="0"
		data-nonce-field-id="rtmedia_media_size_import_nonce">
	</div>
	<hr/>
	<?php if ( ! ( isset( $rtmedia_error ) && true === $rtmedia_error ) ) { ?>
		<input type="button" id="submit" value="start" class="button button-primary"/>
	<?php } ?>
</div>
