<?php
/**
 * Handles Media migration.
 *
 * @package    rtMedia
 */

/**
 * Class for rtMedia Media migration functions
 *
 * @author faishal
 */
class RTMediaMigration {

	/**
	 * BMP Table.
	 *
	 * @var string $bmp_table
	 */
	public $bmp_table = '';

	/**
	 * RTMediaMigration constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->bmp_table = $wpdb->base_prefix . 'rt_rtm_media';

		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'wp_ajax_bp_media_rt_db_migration', array( $this, 'migrate_to_new_db' ) );

		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		$hide = filter_input( INPUT_GET, 'hide', FILTER_SANITIZE_STRING );

		if ( isset( $page ) && 'rtmedia-migration' === $page && isset( $hide ) && 'true' === $hide ) {
			$this->hide_migration_notice();
			$http_referer = rtm_get_server_var( 'HTTP_REFERER', 'FILTER_SANITIZE_URL' );
			wp_safe_redirect( esc_url_raw( $http_referer ) );
		}
		if ( false !== rtmedia_get_site_option( 'rt_migration_hide_notice' ) ) {
			return true;
		}

		$force = filter_input( INPUT_GET, 'force', FILTER_SANITIZE_STRING );
		if ( isset( $force ) && 'true' === $force ) {
			$pending = false;
		} else {
			$pending = rtmedia_get_site_option( 'rtMigration-pending-count' );
		}

		if ( false === $pending ) {
			$total   = $this->get_total_count();
			$done    = $this->get_done_count();
			$pending = $total - $done;
			if ( $pending < 0 ) {
				$pending = 0;
			}
			rtmedia_update_site_option( 'rtMigration-pending-count', $pending );
		}
		if ( $pending > 0 ) {
			if ( ! ( isset( $page ) && 'rtmedia-migration' === $page ) ) {
				add_action( 'admin_notices', array( &$this, 'add_migration_notice' ) );
			}
		}
	}

	/**
	 * Hide migration notice.
	 */
	public function hide_migration_notice() {
		rtmedia_update_site_option( 'rt_migration_hide_notice', true );
	}

	/**
	 * Migrate image size fix.
	 */
	public function migrate_image_size_fix() {
		if ( '' === rtmedia_get_site_option( 'rt_image_size_migration_fix', '' ) ) {
			global $wpdb;

			$wpdb->get_row( $wpdb->prepare( "update $wpdb->postmeta set meta_value=replace(meta_value	,%s,%s) where meta_key = '_wp_attachment_metadata';", 'bp_media', 'rt_media' ) );

			update_option( 'rt_image_size_migration_fix', 'fix' );
		}
	}

	/**
	 * Add migration notice.
	 */
	public function add_migration_notice() {
		if ( current_user_can( 'manage_options' ) ) {
			$this->create_notice( '<p><strong>' . esc_html__( 'rtMedia', 'buddypress-media' ) . '</strong>: ' . esc_html__( 'Please Migrate your Database', 'buddypress-media' ) . " <a href='" . esc_url( admin_url( 'admin.php?page=rtmedia-migration&force=true' ) ) . "'>" . esc_html__( 'Click Here', 'buddypress-media' ) . "</a>.  <a href='" . esc_url( admin_url( 'admin.php?page=rtmedia-migration&hide=true' ) ) . "' style='float:right'>" . esc_html__( 'Hide', 'buddypress-media' ) . '</a> </p>' );
		}
	}

	/**
	 * Create notice.
	 *
	 * @param string $message Message text.
	 * @param string $type Message tpe.
	 */
	public function create_notice( $message, $type = 'error' ) {
		?>
		<div class="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $message ); ?></div>
		<?php
	}

	/**
	 * Check if table exists.
	 *
	 * @param string $table Table name.
	 *
	 * @return bool
	 */
	public static function table_exists( $table ) {
		global $wpdb;

		if ( 1 === intval( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add Menu page for migration.
	 */
	public function menu() {
		add_submenu_page(
			'rtmedia-setting',
			esc_html__( 'Migration', 'buddypress-media' ),
			esc_html__( 'Migration', 'buddypress-media' ),
			'manage_options',
			'rtmedia-migration',
			array(
				$this,
				'test',
			)
		);
	}

	/**
	 * Get total count.
	 *
	 * @return int
	 */
	public function get_total_count() {
		global $wpdb;
		if ( function_exists( 'bp_core_get_table_prefix' ) ) {
			$bp_prefix = bp_core_get_table_prefix();
		} else {
			$bp_prefix = '';
		}
		$sql_album_usercount = "select count(*) FROM $wpdb->usermeta where meta_key ='bp-media-default-album' ";

		$_SESSION['migration_user_album'] = $wpdb->get_var( $sql_album_usercount ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$count                            = intval( $_SESSION['migration_user_album'] );

		if ( $this->table_exists( $bp_prefix . 'bp_groups_groupmeta' ) ) {
			$sql_album_groupcount              = $wpdb->prepare( "select count(*) FROM {$bp_prefix}bp_groups_groupmeta where meta_key =%s", 'bp_media_default_album' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$_SESSION['migration_group_album'] = $wpdb->get_var( $sql_album_groupcount ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$count                            += intval( $_SESSION['migration_group_album'] );
		}

		if ( $this->table_exists( $bp_prefix . 'bp_activity' ) ) {
			$sql_bpm_comment_count = "SELECT
                                                    count(id)
                                                FROM
                                                    {$bp_prefix}bp_activity left outer join (select distinct
                                                            a.meta_value
                                                        from
                                                            $wpdb->postmeta a
                                                                left join
                                                            $wpdb->posts p ON (a.post_id = p.ID)
                                                        where
                                                            (NOT p.ID IS NULL)
                                                                and a.meta_key = 'bp_media_child_activity') p
							on  {$bp_prefix}bp_activity.item_id = p.meta_value
                                                where
                                                    type = 'activity_comment'
                                                    and is_spam <>1 and
                                                        not p.meta_value is NULL";

			$_SESSION['migration_activity'] = $wpdb->get_var( $sql_bpm_comment_count ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$count                         += intval( $_SESSION['migration_activity'] );
		}

		$sql = "select count(*)
                from
                    {$wpdb->postmeta} a
                        left join
                    {$wpdb->postmeta} b ON ((a.post_id = b.post_id)
                        and (b.meta_key = 'bp_media_privacy'))
                        left join
                    {$wpdb->postmeta} c ON (a.post_id = c.post_id)
                        and (c.meta_key = 'bp_media_child_activity')
                        left join
                    {$wpdb->posts} p ON (a.post_id = p.ID)
                where
                    a.post_id > 0 and  (NOT p.ID IS NULL)
                        and a.meta_key = 'bp-media-key'";

		$_SESSION['migration_media'] = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$count                      += intval( $_SESSION['migration_media'] );

		return $count;
	}

	/**
	 * Get last imported media id.
	 *
	 * @return bool
	 */
	public function get_last_imported() {
		$album    = rtmedia_get_site_option( 'rtmedia-global-albums' );
		$album_id = $album[0];

		global $wpdb;
		$sql = "select a.post_ID
                from
                    {$wpdb->postmeta} a  left join
                    {$wpdb->posts} p ON (a.post_id = p.ID)
                where
                     a.meta_key = 'bp-media-key' and  (NOT p.ID IS NULL) and a.post_id not in (select media_id
                from {$this->bmp_table} where blog_id = %d and media_id <> %d ) order by a.post_ID";
		$sql = $wpdb->prepare( $sql, get_current_blog_id(), $album_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$row = $wpdb->get_row( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( $row ) {
			return $row->post_ID;
		} else {
			return false;
		}
	}

	/**
	 * Get count for migrated media.
	 *
	 * @param bool $flag Flag to get migrated media count.
	 *
	 * @return mixed
	 */
	public function get_done_count( $flag = false ) {
		global $wpdb;
		$sql = "select count(*)
                from {$this->bmp_table} where blog_id = %d and media_id in (select a.post_id
                from
                    {$wpdb->postmeta} a
                        left join
                    {$wpdb->postmeta} b ON ((a.post_id = b.post_id)
                        and (b.meta_key = 'bp_media_privacy'))
                        left join
                    {$wpdb->postmeta} c ON (a.post_id = c.post_id)
                        and (c.meta_key = 'bp_media_child_activity')
                        left join
                    {$wpdb->posts} p ON (a.post_id = p.ID)
                where
                    a.post_id > 0 and  (NOT p.ID IS NULL)
                        and a.meta_key = 'bp-media-key')";

		$media_count = $wpdb->get_var( $wpdb->prepare( $sql, get_current_blog_id() ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $flag ) {
			return $media_count - 1;
		}

		$state = intval( rtmedia_get_site_option( 'rtmedia-migration', '0' ) );

		if ( 5 === $state ) {
			$album_count  = intval( $_SESSION['migration_user_album'] );
			$album_count += ( isset( $_SESSION['migration_group_album'] ) ) ? intval( $_SESSION['migration_group_album'] ) : 0;
		} else {
			if ( $state > 0 ) {
				if ( function_exists( 'bp_core_get_table_prefix' ) ) {
					$bp_prefix = bp_core_get_table_prefix();
				} else {
					$bp_prefix = '';
				}
				$pending_count = "select count(*) from $wpdb->posts where post_type='bp_media_album' and ( ID in (select meta_value FROM $wpdb->usermeta where meta_key ='bp-media-default-album') ";
				if ( $this->table_exists( $bp_prefix . 'bp_groups_groupmeta' ) ) {
					$pending_count .= " or ID in (select meta_value FROM {$bp_prefix}bp_groups_groupmeta where meta_key ='bp_media_default_album')";
				}
				$pending_count .= ')';
				$pending_count  = $wpdb->get_var( $pending_count ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

				$album_count  = intval( $_SESSION['migration_user_album'] );
				$album_count += ( isset( $_SESSION['migration_group_album'] ) ) ? intval( $_SESSION['migration_group_album'] ) : 0;
				$album_count  = $album_count - intval( $pending_count );
			} else {
				$album_count = 0;
			}
		}

		if ( isset( $_SESSION['migration_activity'] ) && intval( $_SESSION['migration_media'] ) === intval( $media_count ) ) {
			$comment_sql = $_SESSION['migration_activity'];
		} else {
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			$comment_sql = $wpdb->get_var(
				"select count(*) from $wpdb->comments a
                        where a.comment_post_ID in (select b.media_id from $this->bmp_table b  left join
                        {$wpdb->posts} p ON (b.media_id = p.ID) where  (NOT p.ID IS NULL) ) and a.comment_agent=''"
			);
			// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
		}

		return $media_count + $album_count + $comment_sql;
	}

	/**
	 * Return migration data.
	 */
	public function return_migration() {
		$total   = $this->get_total_count();
		$done    = $this->get_done_count();
		$pending = $total - $done;
		if ( $pending < 0 ) {
			$pending = 0;
			$done    = $total;
		}

		if ( $done > $total ) {
			$done = $total;
		}

		if ( $done === $total ) {
			global $wp_rewrite;
			// Call flush_rules() as a method of the $wp_rewrite object.
			$wp_rewrite->flush_rules( true );
		}
		rtmedia_update_site_option( 'rtMigration-pending-count', $pending );
		$pending_time = $this->format_seconds( $pending );

		echo wp_json_encode(
			array(
				'status'  => true,
				'done'    => $done,
				'total'   => $total,
				'pending' => $pending_time,
			)
		);
		die();
	}

	/**
	 * Manage album migration.
	 *
	 * @return bool
	 */
	public function manage_album() {
		$album = rtmedia_get_site_option( 'rtmedia-global-albums' );
		$stage = intval( rtmedia_get_site_option( 'rtmedia-migration', '0' ) );

		$album_rt_id = $album[0];

		$album_post_type = 'rtmedia_album';

		global $wpdb;

		$album_id = $wpdb->get_var( $wpdb->prepare( "select media_id from $this->bmp_table where id = %d", $album_rt_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( function_exists( 'bp_core_get_table_prefix' ) ) {
			$bp_prefix = bp_core_get_table_prefix();
		} else {
			$bp_prefix = '';
		}

		if ( $stage < 1 ) {

			global $wpdb;

			if ( function_exists( 'bp_core_get_table_prefix' ) ) {
				$bp_prefix = bp_core_get_table_prefix();
			} else {
				$bp_prefix = '';
			}

			$sql = $wpdb->prepare( "update {$bp_prefix}bp_activity set content=replace(content,%s,%s) where id > 0;", '<ul class="bp-media-list-media">', '<div class="rtmedia-activity-container"><ul class="rtmedia-list large-block-grid-3">' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->get_row( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( "update {$bp_prefix}bp_activity set content=replace(content,%s,%s) where id > 0;", '</ul>', '</ul></div>' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->get_row( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			$sql_group = "update $wpdb->posts set post_parent='{$album_id}' where post_parent in (select meta_value FROM $wpdb->usermeta where meta_key ='bp-media-default-album') ";

			if ( $this->table_exists( $bp_prefix . 'bp_groups_groupmeta' ) ) {
				$sql_group .= " or post_parent in (select meta_value FROM {$bp_prefix}bp_groups_groupmeta where meta_key ='bp_media_default_album')";
			}

			$wpdb->query( $sql_group ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$stage = 1;
			rtmedia_update_site_option( 'rtmedia-migration', $stage );
			$this->return_migration();
		}

		if ( $stage < 2 ) {

			$results    = $wpdb->get_results( "select * from $wpdb->posts where post_type='bp_media_album' and ID in (select meta_value FROM $wpdb->usermeta where meta_key ='bp-media-default-album') limit 10" );
			$delete_ids = '';
			$sep        = '';

			foreach ( $results as $result ) {
				$this->search_and_replace( $result->guid, trailingslashit( get_rtmedia_user_link( $result->post_author ) ) . RTMEDIA_MEDIA_SLUG . '/' . $album_rt_id );
				$delete_ids .= $sep . $result->ID;
				$sep         = ',';
			}

			if ( '' !== $delete_ids ) {
				// @todo missing prepare
				$wpdb->query( "delete from $wpdb->posts where ID in ({$delete_ids})" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			if ( count( $results ) < 10 ) {
				$stage = 2;
			}

			rtmedia_update_site_option( 'rtmedia-migration', $stage );
			$this->return_migration();
		}

		if ( $stage < 3 ) {

			if ( $this->table_exists( $bp_prefix . 'bp_groups_groupmeta' ) ) {

				$sql_delete = "select * from $wpdb->posts where post_type='bp_media_album' and ID in (select meta_value FROM {$bp_prefix}bp_groups_groupmeta where meta_key ='bp_media_default_album') limit 10";
				$results    = $wpdb->get_results( $sql_delete ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$delete_ids = '';
				$sep        = '';

				if ( $results ) {

					foreach ( $results as $result ) {
						$group_id = abs( intval( get_post_meta( $result->ID, 'bp-media-key', true ) ) );
						$this->search_and_replace( trailingslashit( get_rtmedia_group_link( $group_id ) ) . 'albums/' . $result->ID, trailingslashit( get_rtmedia_group_link( $group_id ) ) . RTMEDIA_MEDIA_SLUG . '/' . $album_rt_id );
						$delete_ids .= $sep . $result->ID;
						$sep         = ',';
					}

					if ( '' !== $delete_ids ) {
						// @todo prepare
						$wpdb->query( "delete from $wpdb->posts where ID in ({$delete_ids})" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					}

					if ( count( $results ) < 10 ) {
						$stage = 3;
					}
				} else {
					$stage = 3;
				}
				rtmedia_update_site_option( 'rtmedia-migration', $stage );
				$this->return_migration();
			} else {
				$stage = 3;
				rtmedia_update_site_option( 'rtmedia-migration', $stage );
				$this->return_migration();
			}
		}

		$sql = "update $wpdb->posts set post_type='{$album_post_type}' where post_type='bp_media_album'";

		if ( false !== $wpdb->query( $sql ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			rtmedia_update_site_option( 'rtmedia-migration', '5' );
			return true;

		}

		return false;
	}

	/**
	 * Test migration.
	 */
	public function test() {
		if ( ! $this->table_exists( $this->bmp_table ) ) {
			$obj                     = new RTDBUpdate( false, RTMEDIA_PATH . 'index.php', RTMEDIA_PATH . 'app/schema/', true );
			$obj->install_db_version = '0';
			$obj->do_upgrade( true );
		}

		global $rtmedia_error;

		if ( isset( $rtmedia_error ) && true === $rtmedia_error ) {
			?>
			<div class="error"><p><?php echo esc_html__( 'Please Resolve create database error before migration.', 'buddypress-media' ); ?></p></div>
			<?php
		}

		$prog  = new rtProgress();
		$total = $this->get_total_count();
		$done  = $this->get_done_count();
		if ( $done >= $total ) {
			$done = $total;
		} else {
			?>
			<div class="error">
				<p>
					<?php
					/* translators: 1: %s gets replaced by '<strong>', 2: %s by '</strong>' */
					printf( esc_html__( 'Please Backup your %1$sDATABASE%2$s and %1$sUPLOAD%2$s folder before Migration.', 'buddypress-media' ), '<strong>', '</strong>' );
					?>
				</p>
			</div>
			<?php
		}
		?>

		<div class="wrap">

			<h2><?php esc_html_e( 'rtMedia Migration', 'buddypress-media' ); ?></h2>

			<h3><?php esc_html_e( 'It will migrate following things', 'buddypress-media' ); ?> </h3>
			<?php
			esc_html_e( 'User Albums : ', 'buddypress-media' );
			echo esc_html( $_SESSION['migration_user_album'] );
			?>
			<br/>
			<?php
			if ( isset( $_SESSION['migration_group_album'] ) ) {
				esc_html_e( 'Groups Albums : ', 'buddypress-media' );
				echo esc_html( $_SESSION['migration_group_album'] );
				?>
				<br/>
				<?php
			}
			esc_html_e( 'Media : ', 'buddypress-media' );
			echo esc_html( $_SESSION['migration_media'] );
			?>
			<br/>
			?>
			<?php
			if ( isset( $_SESSION['migration_activity'] ) ) {
				esc_html_e( 'Comments : ', 'buddypress-media' );
				echo esc_html( $_SESSION['migration_activity'] );
				?>
				<br/>
			<?php } ?>
			<hr/>

			<?php
			echo '<span class="pending">' . esc_html( $this->format_seconds( $total - $done ) ) . '</span><br />';
			echo '<span class="finished">' . esc_html( $done ) . '</span>/<span class="total">' . esc_html( $total ) . '</span>';
			echo '<img src="images/loading.gif" alt="syncing" id="rtMediaSyncing" style="display:none" />';

			$temp = $prog->progress( $done, $total );
			$prog->progress_ui( $temp, true );
			?>
			<script type="text/javascript">
				jQuery(document).ready(function (e) {
					jQuery("#toplevel_page_rtmedia-settings").addClass("wp-has-current-submenu")
					jQuery("#toplevel_page_rtmedia-settings").removeClass("wp-not-current-submenu")
					jQuery("#toplevel_page_rtmedia-settings").addClass("wp-menu-open")
					jQuery("#toplevel_page_rtmedia-settings>a").addClass("wp-menu-open")
					jQuery("#toplevel_page_rtmedia-settings>a").addClass("wp-has-current-submenu")

					if (db_total < 1)
						jQuery("#submit").attr('disabled', "disabled");
				});
				function db_start_migration(db_done, db_total) {


					if (db_done < db_total) {
						jQuery("#rtMediaSyncing").show();
						jQuery.ajax({
							url: rtmedia_admin_ajax,
							type: 'post',
							data: {
								"action": "bp_media_rt_db_migration",
								"done": db_done
							},
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
									db_start_migration(done, total);
								} else {
									alert("Migration completed.");
									jQuery("#rtMediaSyncing").hide();
								}
							},
							error: function () {
								alert("<?php esc_html_e( 'Error During Migration, Please Refresh Page then try again', 'buddypress-media' ); ?>");
								jQuery("#submit").removeAttr('disabled');
							}
						});
					} else {
						alert("Migration completed.");
						jQuery("#rtMediaSyncing").hide();
					}
				}
				var db_done = <?php echo esc_js( $done ); ?>;
				var db_total = <?php echo esc_js( $total ); ?>;
				jQuery(document).on('click', '#submit', function (e) {
					e.preventDefault();

					db_start_migration(db_done, db_total);
					jQuery(this).attr('disabled', 'disabled');
				});
			</script>
			<hr/>
			<?php if ( ! ( isset( $rtmedia_error ) && true === $rtmedia_error ) ) { ?>
				<input type="button" id="submit" value="<?php esc_attr_e( 'Start', 'buddypress-media' ); ?>" class="button button-primary"/>
			<?php } ?>

		</div>
		<?php
	}

	/**
	 * Migrate media to new DB.
	 *
	 * @param int $lastid Last id.
	 * @param int $limit Limit of rows.
	 */
	public function migrate_to_new_db( $lastid = 0, $limit = 1 ) {

		if ( ! isset( $_SESSION['migration_media'] ) ) {
			$this->get_total_count();
		}

		$state = intval( rtmedia_get_site_option( 'rtmedia-migration' ) );
		if ( $state < 5 ) {
			if ( $this->manage_album() ) {
				$this->migrate_encoding_options();
				$this->return_migration();
			}
		}

		if ( intval( $_SESSION['migration_media'] ) >= $this->get_done_count( true ) ) {

			if ( ! $lastid ) {
				$lastid = $this->get_last_imported();
				if ( ! $lastid ) {
					$this->return_migration();
				}
			}
			global $wpdb;
			$sql = "select
                    a.post_id as 'post_id',
                    b.meta_value as 'privacy',
                    a.meta_value as 'context_id',
                    c.meta_value as 'activity_id',
                    p.post_type,
                    p.post_mime_type,
                    p.post_author as 'media_author',
                    p.post_title as 'media_title',
                    p.post_parent as 'parent'
                from
                    {$wpdb->postmeta} a
                        left join
                    {$wpdb->postmeta} b ON ((a.post_id = b.post_id)
                        and (b.meta_key = 'bp_media_privacy'))
                        left join
                    {$wpdb->postmeta} c ON (a.post_id = c.post_id)
                        and (c.meta_key = 'bp_media_child_activity')
                        left join
                    {$wpdb->posts} p ON (a.post_id = p.ID)
                where
                    a.post_id >= %d and (NOT p.ID is NULL)
                        and a.meta_key = 'bp-media-key'
                order by a.post_id
                limit %d";

			$results = $wpdb->get_results( $wpdb->prepare( $sql, $lastid, $limit ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if ( function_exists( 'bp_core_get_table_prefix' ) ) {
				$bp_prefix = bp_core_get_table_prefix();
			} else {
				$bp_prefix = '';
			}
			if ( $results ) {

				foreach ( $results as $result ) {
					$this->migrate_single_media( $result );
				}
			}
		} else {
			global $wp_rewrite;
			// Call flush_rules() as a method of the $wp_rewrite object.
			$wp_rewrite->flush_rules( true );
		}
		$this->return_migration();
	}

	/**
	 * Update migrate encoding options.
	 */
	public function migrate_encoding_options() {
		$encoding_migration_array = array(
			'bp-media-encoding-api-key'          => 'rtmedia-encoding-api-key',
			'bp-media-encoding-usage-limit-mail' => 'rtmedia-encoding-usage-limit-mail',
			'bp-media-encoding-usage'            => 'rtmedia-encoding-usage',
			'bpmedia_encoding_service_notice'    => 'rtmedia-encoding-service-notice',
			'bpmedia_encoding_expansion_notice'  => 'rtmedia-encoding-expansion-notice',
			'bp_media_ffmpeg_options'            => 'rtmedia-ffmpeg-options',
			'bp_media_kaltura_options'           => 'rtmedia-kaltura-options',
		);

		foreach ( $encoding_migration_array as $key => $ma ) {
			$value = rtmedia_get_site_option( $key );

			if ( false !== $value ) {
				rtmedia_update_site_option( $ma, $value );
			}
		}
	}

	/**
	 * Migrate single media.
	 *
	 * @param string      $result Media id.
	 * @param bool|string $album Album name.
	 *
	 * @return mixed
	 */
	public function migrate_single_media( $result, $album = false ) {

		$blog_id = get_current_blog_id();
		$old     = $result;

		if ( function_exists( 'bp_core_get_table_prefix' ) ) {
			$bp_prefix = bp_core_get_table_prefix();
		} else {
			$bp_prefix = '';
		}

		global $wpdb;

		if ( false !== $album && ! ( is_object( $result ) ) ) {

			$id = $wpdb->get_var( $wpdb->prepare( "select ID from {$this->bmp_table} where media_id = %d", $result ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if ( null === $id ) {
				$sql    = "select
                        a.post_id as 'post_id',
                        a.meta_value as 'privacy',
                        b.meta_value as 'context_id',
                        c.meta_value as 'activity_id',
                        p.post_type,
                        p.post_mime_type,
                        p.post_author as 'media_author',
                        p.post_title as 'media_title',
                        p.post_parent as 'parent'
                    from
                        {$wpdb->postmeta} a
                            left join
                        {$wpdb->postmeta} b ON ((a.post_id = b.post_id)
                            and (b.meta_key = 'bp-media-key'))
                            left join
                        {$wpdb->postmeta} c ON (a.post_id = c.post_id)
                            and (c.meta_key = 'bp_media_child_activity')
                            left join
                        {$wpdb->posts} p ON (a.post_id = p.ID)
                    where
                        a.post_id = %d and (NOT p.ID IS NULL)
                            and a.meta_key = 'bp_media_privacy'";
				$result = $wpdb->get_row( $wpdb->prepare( $sql, $result ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			} else {
				return $id;
			}
		}
		if ( ! isset( $result ) || ! isset( $result->post_id ) ) {
			return $old;
		}
		$media_id = $result->post_id;

		if ( intval( $result->context_id ) > 0 ) {
			$media_context = 'profile';
			$prefix        = 'users/' . abs( intval( $result->context_id ) );
		} else {
			$media_context = 'group';
			$prefix        = bp_get_groups_root_slug() . abs( intval( $result->context_id ) );
		}

		$old_type = '';
		if ( 'attachment' !== $result->post_type ) {
			$media_type = 'album';
		} else {
			$mime_type = strtolower( $result->post_mime_type );
			$old_type  = '';
			if ( 0 === strpos( $mime_type, 'image' ) ) {
				$media_type = 'photo';
				$old_type   = 'photos';
			} else {
				if ( 0 === strpos( $mime_type, 'audio' ) ) {
					$media_type = 'music';
					$old_type   = 'music';
				} else {
					if ( 0 === strpos( $mime_type, 'video' ) ) {
						$media_type = 'video';
						$old_type   = 'videos';
					} else {
						$media_type = 'other';
					}
				}
			}
		}

		$activity_data = $wpdb->get_row( $wpdb->prepare( "select * from {$bp_prefix}bp_activity where id= %d", $result->activity_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( 'album' !== $media_type ) {
			$this->importmedia( $media_id, $prefix );
		}

		if ( $this->table_exists( $bp_prefix . 'bp_activity' ) && class_exists( 'BP_Activity_Activity' ) ) {
			$bp_activity = new BP_Activity_Activity();
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			$activity_sql = $wpdb->prepare(
				"SELECT
                            *
                        FROM
                            {$bp_prefix}bp_activity
                        where
                                        id in (select distinct
                                    a.meta_value
                                from
                                    $wpdb->postmeta a
                                        left join
                                    $wpdb->posts p ON (a.post_id = p.ID)
                                where
                                    (NOT p.ID IS NULL) and p.ID = %d
                and a.meta_key = 'bp_media_child_activity')",
				$media_id
			);
			$all_activity = $wpdb->get_results( $activity_sql );
			// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
			remove_all_actions( 'wp_insert_comment' );
			foreach ( $all_activity as $activity ) {
				$comments = $bp_activity->get_activity_comments( $activity->id, $activity->mptt_left, $activity->mptt_right );
				$exclude  = get_post_meta( $media_id, 'rtmedia_imported_activity', true );
				if ( ! is_array( $exclude ) ) {
					$exclude = array();
				}
				if ( $comments ) {
					$this->insert_comment( $media_id, $comments, $exclude );
				}
			}
		}
		if ( 0 !== intval( $result->parent ) ) {
			$album_id = $this->migrate_single_media( $result->parent, true );
		} else {
			$album_id = 0;
		}
		if ( function_exists( 'bp_activity_get_meta' ) ) {
			$likes = bp_activity_get_meta( $result->activity_id, 'favorite_count' );
		} else {
			$likes = 0;
		}

		$wpdb->insert(
			$this->bmp_table,
			array(
				'blog_id'      => $blog_id,
				'media_id'     => $media_id,
				'media_type'   => $media_type,
				'context'      => $media_context,
				'context_id'   => abs( intval( $result->context_id ) ),
				'activity_id'  => $result->activity_id,
				'privacy'      => intval( $result->privacy ) * 10,
				'media_author' => $result->media_author,
				'media_title'  => $result->media_title,
				'album_id'     => $album_id,
				'likes'        => $likes,
			),
			array( '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%d' )
		);

		$last_id = $wpdb->insert_id;

		if ( 'album' !== $media_type && function_exists( 'bp_core_get_user_domain' ) && $activity_data ) {
			if ( function_exists( 'bp_core_get_table_prefix' ) ) {
				$bp_prefix = bp_core_get_table_prefix();
			} else {
				$bp_prefix = '';
			}

			$activity_data->old_primary_link = $activity_data->primary_link;
			$parent_link                     = get_rtmedia_user_link( $activity_data->user_id );
			$activity_data->primary_link     = $parent_link . RTMEDIA_MEDIA_SLUG . '/' . $last_id;
			$this->search_and_replace( $activity_data->old_primary_link, $activity_data->primary_link );
			$activity_data->action  = str_replace( $activity_data->old_primary_link, $activity_data->primary_link, $activity_data->action );
			$activity_data->content = str_replace( $activity_data->old_primary_link, $activity_data->primary_link, $activity_data->content );
			global $last_baseurl, $last_newurl;

			$replace_img = $last_newurl;
			if ( false === strpos( $activity_data->content, $replace_img ) ) {
				$activity_data->content = str_replace( $last_baseurl, $replace_img, $activity_data->content );
			}
			global $wpdb;
			$wpdb->update(
				$bp_prefix . 'bp_activity',
				array(
					'primary_link' => $activity_data->primary_link,
					'action'       => $activity_data->action,
					'content'      => $activity_data->content,
				),
				array( 'id' => $activity_data->id )
			);
		} else {
			if ( 'group' === $media_context ) {
				$activity_data->old_primary_link = $activity_data->primary_link;
				$parent_link                     = get_rtmedia_group_link( abs( intval( $result->context_id ) ) );
				$parent_link                     = trailingslashit( $parent_link );
				$activity_data->primary_link     = trailingslashit( $parent_link . RTMEDIA_MEDIA_SLUG . '/' . $last_id );
				$this->search_and_replace( $activity_data->old_primary_link, $activity_data->primary_link );
			} else {
				$activity_data->old_primary_link = $activity_data->primary_link;
				$parent_link                     = get_rtmedia_user_link( $activity_data->user_id );
				$parent_link                     = trailingslashit( $parent_link );
				$activity_data->primary_link     = trailingslashit( $parent_link . RTMEDIA_MEDIA_SLUG . '/' . $last_id );
				$this->search_and_replace( $activity_data->old_primary_link, $activity_data->primary_link );
			}
		}
		if ( '' !== $old_type ) {
			if ( 'group' === $media_context ) {
				$parent_link = get_rtmedia_group_link( abs( intval( $result->context_id ) ) );
				$parent_link = trailingslashit( $parent_link );
				$this->search_and_replace( trailingslashit( $parent_link . $old_type . '/' . $media_id ), trailingslashit( $parent_link . RTMEDIA_MEDIA_SLUG . '/' . $last_id ) );
			} else {
				$parent_link = get_rtmedia_user_link( $activity_data->user_id );
				$parent_link = trailingslashit( $parent_link );
				$this->search_and_replace( trailingslashit( $parent_link . $old_type . '/' . $media_id ), trailingslashit( $parent_link . RTMEDIA_MEDIA_SLUG . '/' . $last_id ) );
			}
		}

		return $last_id;
	}

	/**
	 * Import media.
	 *
	 * @param int    $id Media id.
	 * @param string $prefix Prefix.
	 */
	public function importmedia( $id, $prefix ) {

		$delete               = false;
		$attached_file        = get_attached_file( $id );
		$attached_file_option = get_post_meta( $id, '_wp_attached_file', true );
		$basename             = wp_basename( $attached_file );
		$file_folder_path     = trailingslashit( str_replace( $basename, '', $attached_file ) );

		$siteurl     = get_option( 'siteurl' );
		$upload_path = trim( get_option( 'upload_path' ) );

		if ( empty( $upload_path ) || 'wp-content/uploads' === $upload_path ) {
			$dir = WP_CONTENT_DIR . '/uploads';
		} elseif ( 0 !== strpos( $upload_path, ABSPATH ) ) {
			// $dir is absolute, $upload_path is (maybe) relative to ABSPATH.
			$dir = path_join( ABSPATH, $upload_path );
		} else {
			$dir = $upload_path;
		}

		$url = get_option( 'upload_url_path' );

		if ( ! empty( $url ) ) {
			if ( empty( $upload_path ) || ( 'wp-content/uploads' === $upload_path ) || ( $upload_path === $dir ) ) {
				$url = WP_CONTENT_URL . '/uploads';
			} else {
				$url = trailingslashit( $siteurl ) . $upload_path;
			}
		}

		// Obey the value of UPLOADS. This happens as long as ms-files rewriting is disabled.
		// We also sometimes obey UPLOADS when rewriting is enabled -- see the next block.
		if ( defined( 'UPLOADS' ) && ! ( is_multisite() && rtmedia_get_site_option( 'ms_files_rewriting' ) ) ) {
			$dir = ABSPATH . UPLOADS;
			$url = trailingslashit( $siteurl ) . UPLOADS;
		}

		// If multisite (and if not the main site in a post-MU network).
		if ( is_multisite() && ! ( is_main_site() && defined( 'MULTISITE' ) ) ) {

			if ( ! rtmedia_get_site_option( 'ms_files_rewriting' ) ) {
				// If ms-files rewriting is disabled (networks created post-3.5), it is fairly straightforward:
				// Append sites/%d if we're not on the main site (for post-MU networks). (The extra directory
				// prevents a four-digit ID from conflicting with a year-based directory for the main site.
				// But if a MU-era network has disabled ms-files rewriting manually, they don't need the extra
				// directory, as they never had wp-content/uploads for the main site.).
				if ( defined( 'MULTISITE' ) ) {
					$ms_dir = '/sites/' . get_current_blog_id();
				} else {
					$ms_dir = '/' . get_current_blog_id();
				}

				$dir .= $ms_dir;
				$url .= $ms_dir;
			} elseif ( defined( 'UPLOADS' ) && ! ms_is_switched() ) {
				// Handle the old-form ms-files.php rewriting if the network still has that enabled.
				// When ms-files rewriting is enabled, then we only listen to UPLOADS when:
				// 1) we are not on the main site in a post-MU network,
				// as wp-content/uploads is used there, and
				// 2) we are not switched, as ms_upload_constants() hardcodes
				// these constants to reflect the original blog ID.
				//
				// Rather than UPLOADS, we actually use BLOGUPLOADDIR if it is set, as it is absolute.
				// (And it will be set, see ms_upload_constants().) Otherwise, UPLOADS can be used, as
				// as it is relative to ABSPATH. For the final piece: when UPLOADS is used with ms-files
				// rewriting in multisite, the resulting URL is /files. (#WP22702 for background.).
				if ( defined( 'BLOGUPLOADDIR' ) ) {
					$dir = untrailingslashit( BLOGUPLOADDIR );
				} else {
					$dir = ABSPATH . UPLOADS;
				}
				$url = trailingslashit( $siteurl ) . 'files';
			}
		}

		$basedir = trailingslashit( $dir );
		$baseurl = trailingslashit( $url );

		$new_file_folder_path = trailingslashit( str_replace( $basedir, $basedir . "rtMedia/$prefix/", $file_folder_path ) );

		$year_month = untrailingslashit( str_replace( $basedir, '', $file_folder_path ) );

		$metadata              = wp_get_attachment_metadata( $id );
		$backup_metadata       = get_post_meta( $id, '_wp_attachment_backup_sizes', true );
		$instagram_thumbs      = get_post_meta( $id, '_instagram_thumbs', true );
		$instagram_full_images = get_post_meta( $id, '_instagram_full_images', true );
		$instagram_metadata    = get_post_meta( $id, '_instagram_metadata', true );
		$encoding_job_id       = get_post_meta( $id, 'bp-media-encoding-job-id', true );
		$ffmpeg_thumbnail_ids  = get_post_meta( $id, 'bp_media_thumbnail_ids', true );
		$ffmpeg_thumbnail      = get_post_meta( $id, 'bp_media_thumbnail', true );
		$ffmpeg_remote_id      = get_post_meta( $id, 'bp_media_ffmpeg_remote_id', true );
		$kaltura_remote_id     = get_post_meta( $id, 'bp_media_kaltura_remote_id', true );

		if ( wp_mkdir_p( $basedir . "rtMedia/$prefix/" . $year_month ) ) {
			if ( copy( $attached_file, str_replace( $basedir, $basedir . "rtMedia/$prefix/", $attached_file ) ) ) {
				$delete = true;

				if ( isset( $metadata['sizes'] ) ) {
					foreach ( $metadata['sizes'] as $size ) {
						if ( ! copy( $file_folder_path . $size['file'], $new_file_folder_path . $size['file'] ) ) {
							$delete = false;
						} else {
							$delete_sizes[] = $file_folder_path . $size['file'];
							$this->search_and_replace( trailingslashit( $baseurl . $year_month ) . $size['file'], trailingslashit( $baseurl . "rtMedia/$prefix/" . $year_month ) . $size['file'] );
						}
					}
				}
				if ( $backup_metadata ) {
					foreach ( $backup_metadata as $backup_images ) {
						if ( ! copy( $file_folder_path . $backup_images['file'], $new_file_folder_path . $backup_images['file'] ) ) {
							$delete = false;
						} else {
							$delete_sizes[] = $file_folder_path . $backup_images['file'];
							$this->search_and_replace( trailingslashit( $baseurl . $year_month ) . $backup_images['file'], trailingslashit( $baseurl . "rtMedia/$prefix/" . $year_month ) . $backup_images['file'] );
						}
					}
				}

				if ( $instagram_thumbs ) {
					foreach ( $instagram_thumbs as $key => $insta_thumb ) {
						try {
							if ( ! copy( str_replace( $baseurl, $basedir, $insta_thumb ), str_replace( $baseurl, $basedir . "rtMedia/$prefix/", $insta_thumb ) ) ) {
								$delete = false;
							} else {
								$delete_sizes[]               = str_replace( $baseurl, $basedir, $insta_thumb );
								$instagram_thumbs_new[ $key ] = str_replace( $baseurl, $baseurl . "rtMedia/$prefix/", $insta_thumb );
								$this->search_and_replace( trailingslashit( $baseurl . $year_month ) . $insta_thumb, trailingslashit( $baseurl . "rtMedia/$prefix/" . $year_month ) . $insta_thumb );
							}
						} catch ( Exception $e ) {
							$delete = false;
						}
					}
				}

				if ( $instagram_full_images ) {
					foreach ( $instagram_full_images as $key => $insta_full_image ) {
						if ( ! copy( $insta_full_image, str_replace( $basedir, $basedir . "rtMedia/$prefix/", $insta_full_image ) ) ) {
							$delete = false;
						} else {
							$delete_sizes[]                    = $insta_full_image;
							$instagram_full_images_new[ $key ] = str_replace( $basedir, $basedir . "rtMedia/$prefix", $insta_full_image );
							$this->search_and_replace( trailingslashit( $baseurl . $year_month ) . $insta_full_image, trailingslashit( $baseurl . "rtMedia/$prefix/" . $year_month ) . $insta_full_image );
						}
					}
				}

				if ( $instagram_metadata ) {
					$instagram_metadata_new = $instagram_metadata;
					foreach ( $instagram_metadata as $wp_size => $insta_metadata ) {
						if ( isset( $insta_metadata['file'] ) ) {
							if ( ! copy( $basedir . $insta_metadata['file'], $basedir . "rtMedia/$prefix/" . $insta_metadata['file'] ) ) {
								$delete = false;
							} else {
								$delete_sizes[]                             = $basedir . $insta_metadata['file'];
								$instagram_metadata_new[ $wp_size ]['file'] = "rtMedia/$prefix/" . $insta_metadata['file'];
								if ( isset( $insta_metadata['sizes'] ) ) {
									foreach ( $insta_metadata['sizes'] as $key => $insta_size ) {
										if ( ! copy( $file_folder_path . $insta_size['file'], $new_file_folder_path . $insta_size['file'] ) ) {
											$delete = false;
										} else {
											$delete_sizes[] = $file_folder_path . $insta_size['file'];
											$this->search_and_replace( trailingslashit( $baseurl . $year_month ) . $insta_size['file'], trailingslashit( $baseurl . "rtMedia/$prefix/" . $year_month ) . $insta_size['file'] );
										}
									}
								}
							}
						}
					}
				}

				if ( $delete ) {
					if ( file_exists( $attached_file ) ) {
						unlink( $attached_file );
					}

					if ( isset( $delete_sizes ) ) {
						foreach ( $delete_sizes as $delete_size ) {
							if ( file_exists( $delete_size ) ) {
								unlink( $delete_size );
							}
						}
					}
					update_post_meta( $id, '_wp_attached_file', "rtMedia/$prefix/" . $attached_file_option );
					if ( isset( $metadata['file'] ) ) {
						$metadata['file'] = "rtMedia/$prefix/" . $metadata['file'];
						wp_update_attachment_metadata( $id, $metadata );
					}
					if ( $instagram_thumbs ) {
						update_rtmedia_meta( $id, '_instagram_thumbs', $instagram_thumbs_new );
					}
					if ( $instagram_full_images ) {
						update_rtmedia_meta( $id, '_instagram_full_images', $instagram_full_images_new );
					}
					if ( $instagram_metadata ) {
						update_rtmedia_meta( $id, '_instagram_metadata', $instagram_metadata_new );
					}
					if ( $encoding_job_id ) {
						update_rtmedia_meta( $id, 'rtmedia-encoding-job-id', $encoding_job_id );
					}
					if ( $ffmpeg_thumbnail_ids ) {
						update_rtmedia_meta( $id, 'rtmedia-thumbnail-ids', $ffmpeg_thumbnail_ids );
					}
					if ( $ffmpeg_thumbnail ) {
						$model = new RTMediaModel();
						$model->update( array( 'cover_art' => $ffmpeg_thumbnail ), array( 'id' => $id ) );
					}
					if ( $ffmpeg_remote_id ) {
						update_rtmedia_meta( $id, 'rtmedia-ffmpeg-remote-id', $ffmpeg_remote_id );
					}
					if ( $kaltura_remote_id ) {
						update_rtmedia_meta( $id, 'rtmedia-kaltura-remote-id', $kaltura_remote_id );
					}

					$attachment         = array();
					$attachment['ID']   = $id;
					$old_guid           = get_post_field( 'guid', $id );
					$attachment['guid'] = str_replace( $baseurl, $baseurl . "rtMedia/$prefix/", $old_guid );
					/**
					 * For Activity
					 */
					global $last_baseurl, $last_newurl;
					$last_baseurl = $baseurl;
					$last_newurl  = $baseurl . "rtMedia/$prefix/";
					$this->search_and_replace( $old_guid, $attachment['guid'] );
					wp_update_post( $attachment );
				}
			}
		}
	}

	/**
	 * Search and replace in activity table.
	 *
	 * @param string $old Old string.
	 * @param string $new New string.
	 */
	public function search_and_replace( $old, $new ) {
		global $wpdb;

		if ( function_exists( 'bp_core_get_table_prefix' ) ) {
			$bp_prefix = bp_core_get_table_prefix();
		} else {
			$bp_prefix = $wpdb->prefix;
		}

		$sql = $wpdb->prepare( "update {$bp_prefix}bp_activity set action=replace(action,%s,%s) ,content=replace(content,%s,%s), primary_link=replace(primary_link,%s,%s) where id > 0;", $old, $new, $old, $new, $old, $new ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->get_row( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Format seconds into time.
	 *
	 * @param float $seconds_left Seconds to format.
	 *
	 * @return string
	 */
	public function format_seconds( $seconds_left ) {

		$minute_in_seconds = 60;
		$hour_in_seconds   = $minute_in_seconds * 60;
		$day_in_seconds    = $hour_in_seconds * 24;

		$days         = floor( $seconds_left / $day_in_seconds );
		$seconds_left = $seconds_left % $day_in_seconds;

		$hours        = floor( $seconds_left / $hour_in_seconds );
		$seconds_left = $seconds_left % $hour_in_seconds;

		$minutes = floor( $seconds_left / $minute_in_seconds );

		$seconds = $seconds_left % $minute_in_seconds;

		$time_components = array();

		if ( $days > 0 ) {
			$time_components[] = $days . esc_html__( ' day', 'buddypress-media' ) . ( $days > 1 ? 's' : '' );
		}

		if ( $hours > 0 ) {
			$time_components[] = $hours . esc_html__( ' hour', 'buddypress-media' ) . ( $hours > 1 ? 's' : '' );
		}

		if ( $minutes > 0 ) {
			$time_components[] = $minutes . esc_html__( ' minute', 'buddypress-media' ) . ( $minutes > 1 ? 's' : '' );
		}

		if ( $seconds > 0 ) {
			$time_components[] = $seconds . esc_html__( ' second', 'buddypress-media' ) . ( $seconds > 1 ? 's' : '' );
		}
		if ( count( $time_components ) > 0 ) {
			$formatted_time_remaining = implode( ', ', $time_components );
			$formatted_time_remaining = trim( $formatted_time_remaining );
		} else {
			$formatted_time_remaining = esc_html__( 'No time remaining.', 'buddypress-media' );
		}

		return $formatted_time_remaining;
	}

	/**
	 * Insert comment.
	 *
	 * @param int    $media_id media id.
	 * @param array  $data Media data.
	 * @param string $exclude Exclude.
	 * @param int    $parent_commnet_id parent comment id.
	 */
	public function insert_comment( $media_id, $data, $exclude, $parent_commnet_id = 0 ) {
		foreach ( $data as $cmnt ) {
			$comment_id = 0;
			if ( ! key_exists( strval( $cmnt->id ), $exclude ) ) {
				$commentdata                    = array(
					'comment_date'         => $cmnt->date_recorded,
					'comment_parent'       => $parent_commnet_id,
					'user_id'              => $cmnt->user_id,
					'comment_content'      => $cmnt->content,
					'comment_author_email' => $cmnt->user_email,
					'comment_post_ID'      => $media_id,
					'comment_author'       => $cmnt->display_name,
					'comment_author_url'   => '',
					'comment_author_IP'    => '',
				);
				$comment_id                     = wp_insert_comment( $commentdata );
				$exclude[ strval( $cmnt->id ) ] = $comment_id;
			} else {
				$comment_id = $exclude[ strval( $cmnt->id ) ];
			}

			update_post_meta( $media_id, 'rtmedia_imported_activity', $exclude );

			if ( is_array( $cmnt->children ) ) {
				$this->insert_comment( $media_id, $cmnt->children, $exclude, $comment_id );
			}
		}
	}
}
