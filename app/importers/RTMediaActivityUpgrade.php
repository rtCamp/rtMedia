<?php

/**
 * Created by PhpStorm.
 * User: ritz <ritesh.patel@rtcamp.com>
 * Date: 11/9/14
 * Time: 1:56 PM
 */
class RTMediaActivityUpgrade {

	function __construct() {
		add_filter( 'rtmedia_filter_admin_pages_array', array( $this, 'rtmedia_add_admin_page_array' ), 11, 1 );
		add_action( 'admin_init', array( $this, 'add_admin_notice' ) );
		add_action( 'admin_menu', array( $this, 'menu' ), 10 );
		add_action( 'wp_ajax_rtmedia_activity_upgrade', array( $this, 'rtmedia_activity_upgrade' ) );
		add_action( 'wp_ajax_rtmedia_activity_done_upgrade', array( $this, 'rtmedia_activity_done_upgrade' ) );
	}

	function menu() {
		add_submenu_page( 'rtmedia-setting', esc_html__( 'Media activity upgrade', 'buddypress-media' ), esc_html__( 'Media activity upgrade', 'buddypress-media' ), 'manage_options', 'rtmedia-activity-upgrade', array(
			$this,
			'init',
		) );
	}

	function rtmedia_add_admin_page_array( $admin_pages ) {
		$admin_pages[] = 'rtmedia_page_rtmedia-activity-upgrade';

		return $admin_pages;
	}

	function rtmedia_activity_done_upgrade() {
		rtmedia_update_site_option( 'rtmedia_activity_done_upgrade', true );
		die();
	}

	function add_admin_notice() {
		$pending      = $this->get_pending_count();
		$upgrade_done = rtmedia_get_site_option( 'rtmedia_activity_done_upgrade' );
		if ( $upgrade_done ) {
			return;
		}
		if ( $pending < 0 ) {
			$pending = 0;
		}
		rtmedia_update_site_option( 'rtmedia_media_activity_upgrade_pending', $pending );
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		if ( $pending > 0 ) {
			if ( ! ( isset( $page ) && 'rtmedia-activity-upgrade' === $page ) ) {
				$site_option = get_site_option( 'rtmedia_activity_upgrade_notice' );
				if ( ! $site_option || 'hide' !== $site_option ) {
					rtmedia_update_site_option( 'rtmedia_activity_upgrade_notice', 'show' );
					add_action( 'admin_notices', array( &$this, 'add_rtmedia_media_activity_upgrade_notice' ) );
				}
			}
		} else {
			rtmedia_update_site_option( 'rtmedia_activity_done_upgrade', true );
		}
	}

	function rtmedia_activity_upgrade( $lastid = 0, $limit = 1 ) {
		global $wpdb;
		if ( check_ajax_referer( 'rtmedia_media_activity_upgrade_nonce', 'nonce' ) ) {
			$rtmedia_model          = new RTMediaModel();
			$rtmedia_activity_model = new RTMediaActivityModel();
			$activity_sql           = $wpdb->prepare( " SELECT *, max(privacy) as max_privacy FROM {$rtmedia_model->table_name} WHERE activity_id is NOT NULL GROUP BY activity_id ORDER BY id limit %d", $limit ); // @codingStandardsIgnoreLine

			$lastid = filter_input( INPUT_POST, 'last_id', FILTER_SANITIZE_NUMBER_INT );
			if ( ! empty( $lastid ) ) {
				$activity_sql = $wpdb->prepare( " SELECT *, max(privacy) as max_privacy FROM {$rtmedia_model->table_name} WHERE activity_id > %d AND activity_id is NOT NULL GROUP BY activity_id ORDER BY id limit %d", $lastid, $limit ); // @codingStandardsIgnoreLine
			}
			$activity_data = $wpdb->get_results( $activity_sql ); // @codingStandardsIgnoreLine
			if ( is_array( $activity_data ) && ! empty( $activity_data ) ) {
				$rtmedia_activity_model->insert( array(
					'activity_id' => $activity_data[0]->activity_id,
					'user_id'     => $activity_data[0]->media_author,
					'privacy'     => $activity_data[0]->max_privacy,
				) );
			}
			$this->return_upgrade( $activity_data[0] );
		} else {
			echo '0';
			wp_die();
		}

	}

	function return_upgrade( $activity_data, $upgrade = true ) {
		$total   = $this->get_total_count();
		$pending = $this->get_pending_count( $activity_data->activity_id );
		$done    = $total - $pending;
		if ( $pending < 0 ) {
			$pending = 0;
			$done    = $total;
		}
		if ( $done > $total ) {
			$done = $total;
		}
		rtmedia_update_site_option( 'rtmedia_media_activity_upgrade_pending', $pending );
		$pending_time = rtmedia_migrate_formatseconds( $pending ) . ' (estimated)';
		echo wp_json_encode( array(
			'status'      => true,
			'done'        => $done,
			'total'       => $total,
			'pending'     => $pending_time,
			'activity_id' => $activity_data->activity_id,
			'imported'    => $upgrade,
		) );
		die();
	}

	function add_rtmedia_media_activity_upgrade_notice() {
		if ( current_user_can( 'manage_options' ) ) {
			?>
			<div class='error rtmedia-activity-upgrade-notice'>
				<p><strong><?php esc_html_e( 'rtMedia', 'buddypress-media' ); ?></strong>
					<?php esc_html_e( ': Database table structure for rtMedia has been updated. Please ', 'buddypress-media' ); ?>
					<a href='<?php echo esc_url( admin_url( 'admin.php?page=rtmedia-activity-upgrade' ) ); ?>'><?php esc_html_e( 'Click Here', 'buddypress-media' ) ?></a>
					<?php esc_html_e( ' to upgrade rtMedia activities.', 'buddypress-media' ); ?>
				</p>
			</div>
			<?php
		}
	}

	function get_pending_count( $activity_id = false ) {
		global $wpdb;
		$rtmedia_activity_model = new RTMediaActivityModel();
		$rtmedia_model          = new RTMediaModel();
		$query_pending          = $wpdb->prepare( " SELECT count( DISTINCT activity_id) as pending from {$rtmedia_model->table_name} where activity_id NOT IN( SELECT activity_id from {$rtmedia_activity_model->table_name} ) AND activity_id > %d  ", 0 ); // @codingStandardsIgnoreLine
		$last_imported          = $this->get_last_imported();
		if ( $last_imported ) {
			$query_pending .= $wpdb->prepare( ' AND activity_id > %d', intval( $last_imported ) );
		}
		$pending_count = $wpdb->get_results( $query_pending ); // @codingStandardsIgnoreLine
		if ( $pending_count && count( $pending_count ) > 0 ) {
			return $pending_count[0]->pending;
		}

		return 0;
	}

	function get_total_count() {
		global $wpdb;
		$rtmedia_model = new RTMediaModel();
		$query_total   = $wpdb->prepare( " SELECT count( DISTINCT activity_id) as total FROM {$rtmedia_model->table_name} WHERE activity_id > %d ", 0 ); // @codingStandardsIgnoreLine
		$total_count   = $wpdb->get_results( $query_total ); // @codingStandardsIgnoreLine
		if ( $total_count && count( $total_count ) > 0 ) {
			return $total_count[0]->total;
		}

		return 0;
	}

	function get_last_imported() {
		global $wpdb;
		$rtmedia_activity_model = new RTMediaActivityModel();
		$last_query             = $wpdb->prepare( " SELECT activity_id from {$rtmedia_activity_model->table_name} ORDER BY activity_id DESC limit %d ", 1 ); // @codingStandardsIgnoreLine
		$last_imported          = $wpdb->get_results( $last_query ); // @codingStandardsIgnoreLine
		if ( $last_imported && count( $last_imported ) > 0 && isset( $last_imported[0] ) && isset( $last_imported[0]->activity_id ) ) {
			return $last_imported[0]->activity_id;
		}

		return 0;
	}

	function init() {
		$prog       = new rtProgress();
		$pending    = $this->get_pending_count();
		$total      = $this->get_total_count();
		$last_id    = $this->get_last_imported();
		$done       = $total - $pending;
		$admin_ajax = admin_url( 'admin-ajax.php' );
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'rtMedia: Upgrade rtMedia activity', 'buddypress-media' ); ?></h2>
			<?php
			wp_nonce_field( 'rtmedia_media_activity_upgrade_nonce', 'rtmedia_media_activity_upgrade_nonce' );
			echo '<span class="pending">' . sprintf( esc_html__( '%s (estimated)', 'buddypress-media' ), esc_html( rtmedia_migrate_formatseconds( $total - $done ) ) ) . '</span><br />';
			echo '<span class="finished">' . esc_html( $done ) . '</span>/<span class="total">' . esc_html( $total ) . '</span>';
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
		<?php
	}
}
