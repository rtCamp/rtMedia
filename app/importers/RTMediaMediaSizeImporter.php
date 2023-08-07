<?php
/**
 * Handles media size import
 *
 * @package rtMedia
 */

/**
 * Class for Media size importer functions
 *
 * @author ritz
 */
class RTMediaMediaSizeImporter {


	/**
	 * RTMediaMediaSizeImporter constructor.
	 */
	public function __construct() {
		add_filter( 'rtmedia_filter_admin_pages_array', array( $this, 'rtmedia_add_admin_page_array' ), 11, 1 );
		add_action( 'wp_ajax_rtmedia_media_size_import', array( $this, 'rtmedia_media_size_import' ) );
		add_action( 'admin_init', array( $this, 'add_admin_notice' ) );
		add_action( 'admin_menu', array( $this, 'menu' ), 10 );
		add_action(
			'wp_ajax_rtmedia_hide_media_size_import_notice',
			array(
				$this,
				'rtmedia_hide_media_size_import_notice',
			)
		);
	}

	/**
	 * Register MMedia size import Menu.
	 */
	public function menu() {
		add_submenu_page(
			'rtmedia-setting',
			esc_html__( 'Media Size Import', 'buddypress-media' ),
			esc_html__( 'Media Size Import', 'buddypress-media' ),
			'manage_options',
			'rtmedia-migration-media-size-import',
			array(
				$this,
				'init',
			)
		);
	}

	/**
	 * Add admin page array.
	 *
	 * @param array $admin_pages admin pages.
	 *
	 * @return array
	 */
	public function rtmedia_add_admin_page_array( $admin_pages ) {
		$admin_pages[] = 'rtmedia_page_rtmedia-media-size-import';

		return $admin_pages;
	}

	/**
	 * Hide media size import notice.
	 */
	public function rtmedia_hide_media_size_import_notice() {
		if ( rtmedia_update_site_option( 'rtmedia_hide_media_size_import_notice', true ) ) {
			echo '1';
		} else {
			echo '0';
		}
		wp_die();
	}

	/**
	 * Add admin notice.
	 */
	public function add_admin_notice() {
		$pending = $this->get_pending_count();
		if ( $pending < 0 ) {
			$pending = 0;
		}
		rtmedia_update_site_option( 'rtmedia_media_size_import_pending_count', $pending );
		$hide_admin_option = rtmedia_get_site_option( 'rtmedia_hide_media_size_import_notice' );
		if ( $hide_admin_option ) {
			return;
		}
		if ( $pending > 0 ) {
			$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			if ( ! ( isset( $page ) && 'rtmedia-migration-media-size-import' === $page ) ) {
				$site_option = get_site_option( 'rtmedia_media_size_import_notice' );
				if ( ! $site_option || 'hide' !== $site_option ) {
					rtmedia_update_site_option( 'rtmedia_media_size_import_notice', 'show' );
					add_action( 'admin_notices', array( &$this, 'add_rtmedia_media_size_import_notice' ) );
				}
			}
		}
	}

	/**
	 * Media size import notice.
	 */
	public function add_rtmedia_media_size_import_notice() {
		if ( current_user_can( 'manage_options' ) ) {
			$this->create_notice(
				sprintf(
					'<p><strong>rtMedia</strong>: %1$s <a href="%2$s">%3$s</a> %4$s. <a href="#" onclick="rtmedia_hide_media_size_import_notice()" style="float: right;">%5$s</a></p>',
					esc_html__( ': Database table structure for rtMedia has been updated. Please', 'buddypress-media' ),
					esc_url( admin_url( 'admin.php?page=rtmedia-migration-media-size-import&force=true' ) ),
					esc_html__( 'Click Here', 'buddypress-media' ),
					esc_html__( 'to import media sizes', 'buddypress-media' ),
					esc_html__( 'Hide', 'buddypress-media' )
				)
			);

			?>
			<script type="text/javascript">
				function rtmedia_hide_media_size_import_notice() {
					var data = {action: 'rtmedia_hide_media_size_import_notice'};
					jQuery.post(ajaxurl, data, function (response) {
						response = response.trim();
						if (response === '1')
							jQuery('.rtmedia-media-size-import-error').remove();
					});
				}
			</script>
			<?php
		}
	}

	/**
	 * Create notice.
	 *
	 * @param string $message Message to show.
	 * @param string $type Type of message.
	 */
	public function create_notice( $message, $type = 'error' ) {

		$allowed_html = array(
			'p'      => array(),
			'a'      => array(
				'href'    => array(),
				'onclick' => array(),
				'style'   => array(),
			),
			'strong' => array(),
		);

		printf(
			'<div class="%1$s rtmedia-media-size-import-error">%2$s</div>',
			esc_attr( $type ),
			wp_kses( $message, $allowed_html )
		);
	}

	/**
	 * Initialize media size import.
	 */
	public function init() {
		$prog    = new rtProgress();
		$pending = $this->get_pending_count();
		$total   = $this->get_total_count();
		$done    = $total - $pending;

		include RTMEDIA_PATH . 'app/importers/templates/media-size-importer.php';
	}

	/**
	 * Get pending import count.
	 *
	 * @param bool $media_id Media id.
	 *
	 * @return int
	 */
	public function get_pending_count( $media_id = false ) {
		global $wpdb;
		$rtmedia_model = new RTMediaModel();
		$query_pending = "SELECT COUNT(*) as pending from {$rtmedia_model->table_name} where file_size IS NULL AND media_type in ('photo','video','document','music','other')";

		if ( $media_id ) {
			$media_id      = intval( $media_id );
			$query_pending = $wpdb->prepare( "SELECT COUNT(*) as pending from {$rtmedia_model->table_name} where file_size IS NULL AND media_type in ('photo','video','document','music','other') AND id > %d", $media_id );
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
		$query_total   = "SELECT COUNT(*) as total from {$rtmedia_model->table_name} where media_type in ('photo','video','document','music','other') ";
		$total_count   = $wpdb->get_results( $query_total ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $total_count && count( $total_count ) > 0 ) {
			return $total_count[0]->total;
		}

		return 0;
	}

	/**
	 * Media size import.
	 *
	 * @param int $lastid Last id.
	 * @param int $limit Limit of rows.
	 */
	public function rtmedia_media_size_import( $lastid = 0, $limit = 1 ) {
		global $wpdb;
		if ( check_ajax_referer( 'rtmedia_media_size_import_nonce', 'nonce' ) ) {
			$rtmedia_model = new RTMediaModel();
			$get_media_sql = $wpdb->prepare( "SELECT * from {$rtmedia_model->table_name} where file_size is NULL and media_type in ('photo','video','document','music','other') order by id limit %d", $limit );
			$lastid        = filter_input( INPUT_POST, 'last_id', FILTER_SANITIZE_NUMBER_INT );
			if ( ! empty( $lastid ) ) {
				$get_media_sql = $wpdb->prepare( "SELECT * from {$rtmedia_model->table_name} where id > %d AND file_size is NULL and media_type in ('photo','video','document','music','other') order by id limit %d", $lastid, $limit );
			}
			$result = $wpdb->get_results( $get_media_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( $result && count( $result ) > 0 ) {
				$migrate = $this->migrate_single_media( $result[0] );
			}
			$this->return_migration( $result[0], $migrate );
		} else {
			echo '0';
			wp_die();
		}
	}

	/**
	 * Migrate single media.
	 *
	 * @param object $result Object of media.
	 *
	 * @return bool
	 */
	public function migrate_single_media( $result ) {
		global $wpdb;
		$rtmedia_model = new RTMediaModel();
		$attached_file = get_attached_file( $result->media_id );
		$return        = true;
		if ( file_exists( $attached_file ) ) {
			$file_size = filesize( $attached_file );
		} else {
			error_log( 'rtMedia size importer: file not exist. Media ID: ' . esc_html( $result->id ) . ', File: ' . esc_html( $attached_file ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return false;
		}
		$post      = get_post( $result->media_id );
		$post_date = $post->post_date;
		$rtmedia_model->update(
			array(
				'upload_date' => $post_date,
				'file_size'   => $file_size,
			),
			array( 'id' => $result->id )
		);

		return $return;
	}

	/**
	 * Return migration data.
	 *
	 * @param bool|object $media Media object.
	 * @param bool        $migrate Migrate done or not.
	 */
	public function return_migration( $media = false, $migrate = true ) {

		$total   = $this->get_total_count();
		$pending = $this->get_pending_count( $media->id );
		$done    = $total - $pending;

		if ( $pending < 0 ) {
			$pending = 0;
			$done    = $total;
		}

		if ( $done > $total ) {
			$done = $total;
		}

		rtmedia_update_site_option( 'rtmedia_media_size_import_pending_count', $pending );
		$pending_time = rtmedia_migrate_formatseconds( $pending ) . ' (estimated)';

		echo wp_json_encode(
			array(
				'status'   => true,
				'done'     => $done,
				'total'    => $total,
				'pending'  => $pending_time,
				'media_id' => $media->id,
				'imported' => $migrate,
			)
		);
		die();
	}
}
