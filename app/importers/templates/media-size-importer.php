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
	<script type="text/javascript">
		var fail_id = new Array();
		var ajax_data;
		jQuery(document).ready(function (e) {
			jQuery("#toplevel_page_rtmedia-settings").addClass("wp-has-current-submenu")
			jQuery("#toplevel_page_rtmedia-settings").removeClass("wp-not-current-submenu")
			jQuery("#toplevel_page_rtmedia-settings").addClass("wp-menu-open")
			jQuery("#toplevel_page_rtmedia-settings>a").addClass("wp-menu-open")
			jQuery("#toplevel_page_rtmedia-settings>a").addClass("wp-has-current-submenu")
			if (db_total < 1)
				jQuery("#submit").attr('disabled', "disabled");
		});
		function db_start_migration(db_done, db_total, last_id) {

			if (db_done < db_total) {
				jQuery("#rtMediaSyncing").show();
				ajax_data = {
					"action": "rtmedia_media_size_import",
					"done": db_done,
					"last_id": last_id,
					"nonce": jQuery.trim(jQuery('#rtmedia_media_size_import_nonce').val())
				};
				jQuery.ajax({
					url: rtmedia_admin_ajax,
					type: 'post',
					data: ajax_data,
					success: function (sdata) {

						try {
							data = JSON.parse(sdata);
						} catch (e) {
							jQuery("#submit").attr('disabled', "");
						}
						if (data.status) {
							done = parseInt(data.done);
							total = parseInt(data.total);
							var progw = Math.ceil((done / total) * 100);
							if (progw > 100) {
								progw = 100;
							}
							jQuery('#rtprogressbar>div').css('width', progw + '%');
							jQuery('span.finished').html(done);
							jQuery('span.total').html(total);
							jQuery('span.pending').html(data.pending);
							if (data.imported === false) {
								fail_id.push(data.media_id);
							}
							db_start_migration(done, total, parseInt(data.media_id));
						} else {
							alert("Migration completed.");
							jQuery("#rtMediaSyncing").hide();
						}
					},
					error: function () {
						alert("Error During Migration, Please Refresh Page then try again");
						jQuery("#submit").removeAttr('disabled');
					}
				});
			} else {
				alert("Migration completed.");
				if (fail_id.length > 0) {
					rtm_show_file_error();
				}
				jQuery("#rtMediaSyncing").hide();
			}
		}
		function rtm_show_file_error() {
			jQuery('span.pending').text("Media with ID: " + fail_id.join(', ') + " can not be imported. Please check your server error log for more details. Don't worry, you can end importing media size now :)");
		}
		var db_done = <?php echo esc_js( $done ); ?>;
		var db_total = <?php echo esc_js( $total ); ?>;
		jQuery(document).on('click', '#submit', function (e) {
			e.preventDefault();
			db_start_migration(db_done, db_total, 0);
			jQuery(this).attr('disabled', 'disabled');
		});
	</script>
	<hr/>
	<?php if ( ! ( isset( $rtmedia_error ) && true === $rtmedia_error ) ) { ?>
		<input type="button" id="submit" value="start" class="button button-primary"/>
	<?php } ?>
</div>
