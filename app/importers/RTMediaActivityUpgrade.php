<?php
/**
 * Handles rtMedia activities
 * User: ritz <ritesh.patel@rtcamp.com>
 * Date: 11/9/14
 * Time: 1:56 PM
 *
 * @package rtMedia
 */

/**
 * Class to update rtMedia activities
 */
class RTMediaActivityUpgrade {

	/**
	 * RTMediaActivityUpgrade constructor.
	 */
	public function __construct() {
		add_filter( 'rtmedia_filter_admin_pages_array', array( $this, 'rtmedia_add_admin_page_array' ), 11, 1 );
		add_action( 'admin_init', array( $this, 'add_admin_notice' ) );
		add_action( 'admin_menu', array( $this, 'menu' ), 10 );
		add_action( 'wp_ajax_rtmedia_activity_upgrade', array( $this, 'rtmedia_activity_upgrade' ) );
		add_action( 'wp_ajax_rtmedia_activity_done_upgrade', array( $this, 'rtmedia_activity_done_upgrade' ) );
	}

	/**
	 * Add Media activity upgrade Menu.
	 */
	public function menu() {

		add_submenu_page(
			'rtmedia-setting',
			esc_html__( 'Media activity upgrade', 'buddypress-media' ),
			esc_html__( 'Media activity upgrade', 'buddypress-media' ),
			'manage_options',
			'rtmedia-activity-upgrade',
			array(
				$this,
				'init',
			)
		);
	}

	/**
	 * Add admin page array.
	 *
	 * @param array $admin_pages Admin pages array.
	 *
	 * @return array
	 */
	public function rtmedia_add_admin_page_array( $admin_pages ) {
		$admin_pages[] = 'rtmedia_page_rtmedia-activity-upgrade';

		return $admin_pages;
	}

	/**
	 * Function to update option after activity upgrade is done.
	 */
	public function rtmedia_activity_done_upgrade() {
		rtmedia_update_site_option( 'rtmedia_activity_done_upgrade', true );
		die();
	}

	/**
	 * Add admin notice for activity upgrade.
	 */
	public function add_admin_notice() {
		$pending      = $this->get_pending_count();
		$upgrade_done = rtmedia_get_site_option( 'rtmedia_activity_done_upgrade' );

		if ( $upgrade_done ) {
			return;
		}

		if ( $pending < 0 ) {
			$pending = 0;
		}

		rtmedia_update_site_option( 'rtmedia_media_activity_upgrade_pending', $pending );
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

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

	/**
	 * Ajax callback for activity upgrade.
	 *
	 * @param int $lastid Last id.
	 * @param int $limit Limit for query.
	 */
	public function rtmedia_activity_upgrade( $lastid = 0, $limit = 1 ) {
		global $wpdb;
		if ( check_ajax_referer( 'rtmedia_media_activity_upgrade_nonce', 'nonce' ) ) {
			$rtmedia_model          = new RTMediaModel();
			$rtmedia_activity_model = new RTMediaActivityModel();
			$activity_sql           = $wpdb->prepare( " SELECT *, max(privacy) as max_privacy FROM {$rtmedia_model->table_name} WHERE activity_id is NOT NULL GROUP BY activity_id ORDER BY id limit %d", $limit ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			$lastid = filter_input( INPUT_POST, 'last_id', FILTER_SANITIZE_NUMBER_INT );

			if ( ! empty( $lastid ) ) {
				$activity_sql = $wpdb->prepare( " SELECT *, max(privacy) as max_privacy FROM {$rtmedia_model->table_name} WHERE activity_id > %d AND activity_id is NOT NULL GROUP BY activity_id ORDER BY id limit %d", $lastid, $limit ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			$activity_data = $wpdb->get_results( $activity_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if ( is_array( $activity_data ) && ! empty( $activity_data ) ) {
				if ( $rtmedia_activity_model->check( $activity_data[0]->activity_id ) ) {
					$rtmedia_activity_model->update(
						array(
							'activity_id' => $activity_data[0]->activity_id,
							'user_id'     => $activity_data[0]->media_author,
							'privacy'     => $activity_data[0]->max_privacy,
						),
						array( 'activity_id' => $activity_data[0]->activity_id )
					);
				} else {
					$rtmedia_activity_model->insert(
						array(
							'activity_id' => $activity_data[0]->activity_id,
							'user_id'     => $activity_data[0]->media_author,
							'privacy'     => $activity_data[0]->max_privacy,
						)
					);
				}
			}
			$this->return_upgrade( $activity_data[0] );
		} else {
			echo '0';
			wp_die();
		}

	}

	/**
	 * Function to get upgraded activity details.
	 *
	 * @param object $activity_data Activity data object.
	 * @param bool   $upgrade Upgrade done or not.
	 */
	public function return_upgrade( $activity_data, $upgrade = true ) {
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

		echo wp_json_encode(
			array(
				'status'      => true,
				'done'        => $done,
				'total'       => $total,
				'pending'     => $pending_time,
				'activity_id' => $activity_data->activity_id,
				'imported'    => $upgrade,
			)
		);
		die();
	}

	/**
	 * Media activity upgrade notice.
	 */
	public function add_rtmedia_media_activity_upgrade_notice() {
		if ( current_user_can( 'manage_options' ) ) {
			?>
			<div class='error rtmedia-activity-upgrade-notice'>
				<p><strong><?php esc_html_e( 'rtMedia', 'buddypress-media' ); ?></strong>
					<?php esc_html_e( ': Database table structure for rtMedia has been updated. Please ', 'buddypress-media' ); ?>
					<a href='<?php echo esc_url( admin_url( 'admin.php?page=rtmedia-activity-upgrade' ) ); ?>'><?php esc_html_e( 'Click Here', 'buddypress-media' ); ?></a>
					<?php esc_html_e( ' to upgrade rtMedia activities.', 'buddypress-media' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Get pending count.
	 *
	 * @param bool|int $activity_id Activity id.
	 *
	 * @return int
	 */
	public function get_pending_count( $activity_id = false ) {
		global $wpdb;
		$rtmedia_activity_model = new RTMediaActivityModel();
		$rtmedia_model          = new RTMediaModel();
		$query_pending          = $wpdb->prepare( " SELECT count( DISTINCT activity_id) as pending from {$rtmedia_model->table_name} where activity_id NOT IN( SELECT activity_id from {$rtmedia_activity_model->table_name} ) AND activity_id > %d  ", 0 ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$last_imported          = $this->get_last_imported();

		if ( $last_imported ) {
			$query_pending .= $wpdb->prepare( ' AND activity_id > %d', intval( $last_imported ) );
		}

		$pending_count = $wpdb->get_results( $query_pending ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $pending_count && count( $pending_count ) > 0 ) {
			return $pending_count[0]->pending;
		}

		return 0;
	}

	/**
	 * Get total count.
	 *
	 * @return int
	 */
	public function get_total_count() {
		global $wpdb;
		$rtmedia_model = new RTMediaModel();
		$total_count   = $wpdb->get_results( $wpdb->prepare( " SELECT count( DISTINCT activity_id) as total FROM {$rtmedia_model->table_name} WHERE activity_id > %d ", 0 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $total_count && count( $total_count ) > 0 ) {
			return $total_count[0]->total;
		}

		return 0;
	}

	/**
	 * Get last imported.
	 *
	 * @return int
	 */
	public function get_last_imported() {
		global $wpdb;
		$rtmedia_activity_model = new RTMediaActivityModel();
		$last_imported          = $wpdb->get_results( $wpdb->prepare( " SELECT activity_id from {$rtmedia_activity_model->table_name} ORDER BY activity_id DESC limit %d ", 1 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $last_imported && count( $last_imported ) > 0 && isset( $last_imported[0] ) && isset( $last_imported[0]->activity_id ) ) {
			return $last_imported[0]->activity_id;
		}

		return 0;
	}

	/**
	 * Initialize activity upgrade.
	 */
	public function init() {
		$prog       = new rtProgress();
		$pending    = $this->get_pending_count();
		$total      = $this->get_total_count();
		$last_id    = $this->get_last_imported();
		$done       = $total - $pending;
		$admin_ajax = admin_url( 'admin-ajax.php' );

		include RTMEDIA_PATH . 'app/importers/templates/activity-upgrade.php';
	}
}
