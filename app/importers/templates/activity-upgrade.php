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
					"action": "rtmedia_activity_upgrade",
					"done": db_done,
					"last_id": last_id,
					"nonce": jQuery.trim(jQuery('#rtmedia_media_activity_upgrade_nonce').val())
				};
				jQuery.ajax({
					url: '<?php echo esc_url( $admin_ajax ); ?>',
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
								fail_id.push(data.activity_id);
							}
							db_start_migration(done, total, parseInt(data.activity_id));
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
				data = {
					action: 'rtmedia_activity_done_upgrade'
				};
				jQuery.post('<?php echo esc_sql( $admin_ajax ); ?>', data, function () {
					alert("Database upgrade completed.");
				});
				if (fail_id.length > 0) {
					rtm_show_file_error();
				}
				jQuery("#rtMediaSyncing").hide();
			}
		}
		function rtm_show_file_error() {
			jQuery('span.pending').html("Some activities are failed to upgrade, Don't worry about that.");
		}
		var db_done = <?php echo esc_js( $done ); ?>;
		var db_total = <?php echo esc_js( $total ); ?>;
		var last_id = <?php echo esc_js( $last_id ); ?>;
		jQuery(document).on('click', '#submit', function (e) {
			e.preventDefault();
			db_start_migration(db_done, db_total, last_id);
			jQuery(this).attr('disabled', 'disabled');
		});
	</script>
	<hr/>
	<?php if ( ! ( isset( $rtmedia_error ) && true === $rtmedia_error ) ) { ?>
		<input type="button" id="submit" value="start" class="button button-primary"/>
	<?php } ?>
</div>
