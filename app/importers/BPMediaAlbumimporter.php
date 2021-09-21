<?php
/**
 * Handles BuddyPress media album import
 *
 * @package rtMedia
 */

/**
 * Class for BuddyPress media album importer
 *
 * @author saurabh
 */
class BPMediaAlbumimporter extends BPMediaImporter {

	/**
	 * BPMediaAlbumimporter constructor.
	 */
	public function __construct() {
		global $wpdb;
		parent::__construct();
		$table = "{$wpdb->base_prefix}bp_album";
		if ( BPMediaImporter::table_exists( $table ) && self::_active( 'bp-album/loader.php' ) !== - 1 && ! $this->column_exists( 'import_status' ) ) {
			$this->update_table();
		}
	}

	/**
	 * Update bp_album table.
	 *
	 * @return false|int|void
	 */
	public function update_table() {
		if ( $this->column_exists( 'import_status' ) ) {
			return;
		}
		global $wpdb;

		return $wpdb->query(
			"ALTER TABLE {$wpdb->base_prefix}bp_album
                            ADD COLUMN import_status BIGINT (20) NOT NULL DEFAULT 0,
                            ADD COLUMN old_activity_id BIGINT (20) NOT NULL DEFAULT 0,
                            ADD COLUMN new_activity_id BIGINT (20) NOT NULL DEFAULT 0,
                            ADD COLUMN favorites TINYINT (1) NOT NULL DEFAULT 0"
		);
	}

	/**
	 * Function to check if column exists.
	 *
	 * @param string $column Column name to check.
	 *
	 * @return mixed
	 */
	public function column_exists( $column ) {
		global $wpdb;

		return $wpdb->query( $wpdb->prepare( "SHOW COLUMNS FROM {$wpdb->base_prefix}bp_album LIKE %s limit 1", $column ) );
	}

	/**
	 * UI for Media importer.
	 */
	public function ui() {
		global $wpdb;
		$bp_album_active = BPMediaImporter::_active( 'bp-album/loader.php' );
		$table           = "{$wpdb->base_prefix}bp_album";
		if ( BPMediaImporter::table_exists( $table ) ) {

			$this->progress            = new rtProgress();
			$total                     = self::get_total_count();
			$remaining_comments        = $this->get_remaining_comments();
			$finished                  = self::get_completed_media( $total );
			$finished_users            = self::get_completed_users();
			$finished_comments         = $this->get_finished_comments();
			$total_comments            = (int) $finished_comments + (int) $remaining_comments;
			$completed_users_favorites = (int) get_site_option( 'bp_media_bp_album_favorite_import_status', 0 );
			$users                     = count_users();

			echo '<div id="bpmedia-bpalbumimporter">';
			wp_nonce_field( 'bpmedia-bpalbumimporter', 'bpaimporter_wpnonce' );

			if ( ( $finished[0]->media !== $total[0]->media ) || ( $users['total_users'] > $completed_users_favorites ) ) {

				if ( 1 !== $bp_album_active ) {
					echo '<div id="setting-error-bp-album-importer" class="error settings-error below-h2">
						<p><strong>' . esc_html__( 'Warning!', 'buddypress-media' ) . '</strong> ' .
						esc_html__( 'This import process is irreversible. Although everything is tested, please take a ', 'buddypress-media' ) .
						'<a target="_blank" href="http://codex.wordpress.org/WordPress_Backups">' . esc_html__( 'backup of your database and files', 'buddypress-media' ) . '</a>' .
						esc_html__( ', before proceeding. If you don\'t know your way around databases and files, consider ', 'buddypress-media' ) .
						'<a target="_blank" href="http://rtmedia.io/contact/?purpose=buddypress&utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media">' . esc_html__( 'hiring us', 'buddypress-media' ) . '</a>' .
						esc_html__( ', or another professional.', 'buddypress-media' ) .
						'</p>';
					echo '<p>' . esc_html__( 'If you have set "WP_DEBUG" in you wp-config.php file, please make sure it is set to "false", so that it doesn\'t conflict with the import process.', 'buddypress-media' ) . '</p></div>';
					echo '<div class="bp-album-import-accept"><p><strong><label for="bp-album-import-accept"><input type="checkbox" value="accept" name="bp-album-import-accept" id="bp-album-import-accept" /> ' . esc_html__( 'I have taken a backup of the database and files of this site.', 'buddypress-media' ) . '</label></strong></p></div>';
					echo '<button id="bpmedia-bpalbumimport" class="button button-primary">';
					esc_html__( 'Start Import', 'buddypress-media' );
					echo '</button>';
					echo '<div class="bp-album-importer-wizard">';
					echo '<div class="bp-album-users">';
					echo '<strong>';
					echo esc_html__( 'Users', 'buddypress-media' ) . ': <span class="finished">' . esc_html( $finished_users[0]->users ) . '</span> / <span class="total">' . esc_html( $total[0]->users ) . '</span>';
					echo '</strong>';
					if ( 0 !== $total[0]->users ) {
						$users_progress = $this->progress->progress( $finished_users[0]->users, $total[0]->users );
						$this->progress->progress_ui( $users_progress );
					}
					echo '</div>';
					echo '<br />';
					echo '<div class="bp-album-media">';
					echo '<strong>';
					echo esc_html__( 'Media', 'buddypress-media' ) . ': <span class="finished">' . esc_html( $finished[0]->media ) . '</span> / <span class="total">' . esc_html( $total[0]->media ) . '</span>';
					echo '</strong>';
					$progress = 100;

					if ( 0 !== $total[0]->media ) {
						$todo     = $total[0]->media - $finished[0]->media;
						$steps    = ceil( $todo / 5 );
						$laststep = $todo % 5;
						$progress = $this->progress->progress( $finished[0]->media, $total[0]->media );
						echo '<input type="hidden" value="' . esc_attr( $finished[0]->media ) . '" name="finished"/>';
						echo '<input type="hidden" value="' . esc_attr( $total[0]->media ) . '" name="total"/>';
						echo '<input type="hidden" value="' . esc_attr( $todo ) . '" name="todo"/>';
						echo '<input type="hidden" value="' . esc_attr( $steps ) . '" name="steps"/>';
						echo '<input type="hidden" value="' . esc_attr( $laststep ) . '" name="laststep"/>';
						$this->progress->progress_ui( $progress );
					}

					echo '</div>';
					echo '<br>';
					echo '<div class="bp-album-comments">';

					if ( 0 !== $total_comments ) {
						echo '<strong>';
						echo esc_html__( 'Comments', 'buddypress-media' ) . ': <span class="finished">' . esc_html( $finished_comments ) . '</span> / <span class="total">' . esc_html( $total_comments ) . '</span>';
						echo '</strong>';
						$comments_progress = $this->progress->progress( $finished_comments, $total_comments );
						$this->progress->progress_ui( $comments_progress );
						echo '<br />';
					} else {
						echo '<p><strong>' . esc_html__( 'Comments: 0/0 (No comments to import)', 'buddypress-media' ) . '</strong></p>';
					}
					echo '</div>';

					if ( 0 !== $completed_users_favorites ) {
						echo '<br />';
						echo '<div class="bp-album-favorites">';
						echo '<strong>';
						echo esc_html__( 'User\'s Favorites', 'buddypress-media' ) . ': <span class="finished">' . esc_html( $completed_users_favorites ) . '</span> / <span class="total">' . esc_html( $users['total_users'] ) . '</span>';
						echo '</strong>';
						$favorites_progress = $this->progress->progress( $completed_users_favorites, $users['total_users'] );
						$this->progress->progress_ui( $favorites_progress );
						echo '</div>';
					}
					echo '</div>';
				} else {
					$deactivate_link = wp_nonce_url( admin_url( 'plugins.php?action=deactivate&amp;plugin=' . rawurlencode( $this->path ) ), 'deactivate-plugin_' . $this->path );
					echo '<p>' . esc_html__( 'BP-Album is active on your site and will cause problems with the import.', 'buddypress-media' ) . '</p>';
					echo '<p><a class="button button-primary deactivate-bp-album" href="' . esc_url( $deactivate_link ) . '">' . esc_html__( 'Click here to deactivate BP-Album and continue importing', 'buddypress-media' ) . '</a></p>';
				}
			} else {
				$corrupt_media = self::get_corrupt_media();
				if ( $corrupt_media ) {
					echo '<div class="error below-h2">';
					echo '<p><strong>' . esc_html__( 'Some of the media failed to import. The file might be corrupt or deleted.', 'buddypress-media' ) . '</strong></p>';
					// translators: %d: Media.
					echo '<p>' . sprintf( esc_html__( 'The following %d BP Album Media id\'s could not be imported', 'buddypress-media' ), count( $corrupt_media ) ) . ': </p>';
					$corrupt_prefix_path = str_replace( '/wp-content', '', WP_CONTENT_URL );
					foreach ( $corrupt_media as $corrupt ) {
						echo '<p>' . esc_html( $corrupt->id ) . ' => <a href="' . esc_url( $corrupt_prefix_path . $corrupt->pic_org_url ) . '">' . esc_html( $corrupt->title ) . '</a></p>';
					}
					echo '</div>';
				} else {
					echo '<div class="bp-album-import-accept i-accept">';
					echo '<p class="info">';
					// translators: %s: URL.
					$message = sprintf( esc_html__( 'I just imported bp-album to @rtMediaWP http://rt.cx/rtmedia on %s', 'buddypress-media' ), home_url() );
					echo '<strong>' . esc_html__( 'Congratulations!', 'buddypress-media' ) . '</strong> ' . esc_html__( 'All media from BP Album has been imported.', 'buddypress-media' );
					echo ' <a href="http://twitter.com/home/?status=' . esc_url( $message ) . '" class="button button-import-tweet" target= "_blank">' . esc_html__( 'Tweet this', 'buddypress-media' ) . '</a>';
					echo '</p>';
					echo '</div>';
				}
				echo '<p>' . esc_html__( 'However, a lot of unnecessary files and a database table are still eating up your resources. If everything seems fine, you can clean this data up.', 'buddypress-media' ) . '</p>';
				echo '<br />';
				echo '<button id="bpmedia-bpalbumimport-cleanup" class="button btn-warning">';
				esc_html_e( 'Clean up Now', 'buddypress-media' );
				echo '</button>';
				echo ' <a href="' . esc_url( add_query_arg( array( 'page' => 'bp-media-settings' ), ( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) ) ) ) . '" id="bpmedia-bpalbumimport-cleanup-later" class="button">';
				esc_html_e( 'Clean up Later', 'buddypress-media' );
				echo '</a>';
				echo '<br />';
				echo '<br />';
				echo '<br />';
				echo '<strong>' . esc_html__( 'Why don\'t you try adding some Instagram like effects to your images?', 'buddypress-media' ) . '</strong>';
				echo '<div class="bp-media-addon">
					<a href="https://rtmedia.io/products/rtmedia-photo-filters/?utm_source=dashboard&amp;utm_medium=plugin&amp;utm_campaign=buddypress-media&amp;utm_content=bp-album-importer" title="rtMedia Photo Filters" target="_blank">
						<img width="240" height="184" title="rtMedia Photo Filters" alt="rtMedia Photo Filters" src="' . esc_url( $img_src ) . 'BuddyPressMedia-Instagram.png?ref=bp-album-importer">
					</a>
	                <h4><a href="https://rtmedia.io/products/rtmedia-photo-filters/?utm_source=dashboard&amp;utm_medium=plugin&amp;utm_campaign=buddypress-media&amp;utm_content=bp-album-importer" title="rtMedia Photo Filters" target="_blank">rtMedia Photo Filters</a></h4>
	                <div class="product_desc">
	                    <p>' . esc_html__( 'rtMedia Photo Filters adds Instagram like filters to images uploaded with BuddyPress Media.', 'buddypress-media' ) . '</p>
	                    <p><strong>' . esc_html__( 'Important', 'buddypress-media' ) . ':</strong> ' . esc_html__( 'You need to have ImageMagick installed on your server for this addon to work.', 'buddypress-media' ) . '</p>
	                </div>
	                <div class="product_footer">
	                    <span class="price alignleft"><span class="amount">$19</span></span>
	                    <a class="add_to_cart_button  alignright product_type_simple" href="https://rtmedia.io/products/?utm_source=dashboard&amp;utm_medium=plugin&amp;utm_campaign=buddypress-media&amp;utm_content=bp-album-importer&amp;add-to-cart=34379" target="_blank">' . esc_html__( 'Buy Now', 'buddypress-media' ) . '</a>
	                    <a class="alignleft product_demo_link" href="http://demo.rtmedia.io/rtmedia/?utm_source=dashboard&amp;utm_medium=plugin&amp;utm_campaign=buddypress-media&amp;utm_content=bp-album-importer" title="rtMedia Photo Filters" target="_blank">' . esc_html__( 'Live Demo', 'buddypress-media' ) . '</a>
	                </div></div>';
			}
			echo '</div>';
		} else {
			echo '<p>' . esc_html__( 'Looks like you don\'t use BP Album. Is there any other BuddyPress Plugin you want an importer for?', 'buddypress-media' ) . '</p>';
			echo '<p><a href="https://github.com/rtCamp/rtMedia/issues/new">' . esc_html__( 'Create an issue', 'buddypress-media' ) . '</a>';
			echo esc_html__( ' on GitHub requesting the same.', 'buddypress-media' ) . '</p>';
		}
	}

	/**
	 * Create album.
	 *
	 * @param string $author_id Author id.
	 * @param string $album_name Album name.
	 *
	 * @return mixed
	 */
	public function create_album( $author_id, $album_name = 'Imported Media' ) {
		global $bp_media, $wpdb;

		if ( array_key_exists( 'bp_album_import_name', $bp_media->options ) ) {
			if ( '' !== $bp_media->options['bp_album_import_name'] ) {
				$album_name = $bp_media->options['bp_album_import_name'];
			}
		}

		$result = $wpdb->get_results( $wpdb->prepare( "SELECT ID from $wpdb->posts WHERE post_type='bp_media_album' AND post_status = 'publish' AND post_author = %d AND post_title LIKE %s limit 1", $author_id, $album_name ) );
		if ( count( $result ) < 1 ) {
			$album = new BPMediaAlbum();
			$album->add_album( $album_name, $author_id );
			$album_id = $album->get_id();
		} else {
			$album_id = $result[0]->ID;
		}
		$wpdb->update( $wpdb->base_prefix . 'bp_activity', array( 'secondary_item_id' => - 999 ), array( 'id' => get_post_meta( $album_id, 'bp_media_child_activity', true ) ) );

		return $album_id;
	}

	/**
	 * Get total count.
	 *
	 * @return int
	 */
	public static function get_total_count() {
		global $wpdb;
		$table = $wpdb->base_prefix . 'bp_album';
		if ( self::table_exists( $table ) ) {
			return $wpdb->get_results( "SELECT COUNT(DISTINCT owner_id) as users, COUNT(id) as media FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		return 0;
	}

	/**
	 * Get remaining comments count.
	 *
	 * @return int
	 */
	public function get_remaining_comments() {
		global $wpdb;
		$bp_album_table = $wpdb->base_prefix . 'bp_album';
		$activity_table = $wpdb->base_prefix . 'bp_activity';
		if ( $this->table_exists( $bp_album_table ) ) {
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			return $wpdb->get_var(
				"SELECT SUM( b.count ) AS total
                                        FROM (
                                            SELECT (
                                                SELECT COUNT( a.id )
                                                FROM {$activity_table} a
                                                WHERE a.item_id = activity.id
                                                AND a.component =  'activity'
                                                AND a.type =  'activity_comment'
                                            ) AS count
                                            FROM $activity_table AS activity
                                            INNER JOIN $bp_album_table AS album ON ( album.id = activity.item_id )
                                            WHERE activity.component =  'album'
                                            AND activity.type =  'bp_album_picture'
                                            AND album.import_status =0
                                        )b"
			); // phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
		}

		return 0;
	}

	/**
	 * Get finished comments count.
	 *
	 * @return int
	 */
	public function get_finished_comments() {
		global $wpdb;
		$bp_album_table = $wpdb->base_prefix . 'bp_album';

		if ( $this->table_exists( $bp_album_table ) ) {
			return $wpdb->get_var(
				"SELECT COUNT( activity.id ) AS count
                        FROM {$wpdb->base_prefix}bp_activity AS activity
                        INNER JOIN {$wpdb->base_prefix}bp_album AS album ON ( activity.item_id = album.import_status )
                        WHERE activity.component =  'activity'
                        AND activity.type =  'activity_comment'"
			);
		}

		return 0;
	}

	/**
	 * Get completed users count.
	 *
	 * @return int
	 */
	public static function get_completed_users() {
		global $wpdb;
		$table = $wpdb->base_prefix . 'bp_album';
		if ( self::table_exists( $table ) ) {

			return $wpdb->get_results(
				"SELECT COUNT( DISTINCT owner_id ) AS users
                    FROM {$wpdb->base_prefix}bp_album
                    WHERE owner_id NOT
                    IN (
                        SELECT a.owner_id
                        FROM {$wpdb->base_prefix}bp_album a
                        WHERE a.import_status =0
                    )"
			);

		}

		return 0;
	}

	/**
	 * Get completed media count.
	 *
	 * @return int
	 */
	public static function get_completed_media() {
		global $wpdb;
		$table = $wpdb->base_prefix . 'bp_album';
		if ( self::table_exists( $table ) ) {
			return $wpdb->get_results( "SELECT COUNT(id) as media FROM {$wpdb->base_prefix}bp_album WHERE import_status!=0" );
		}

		return 0;
	}

	/**
	 * Get corrupt media data.
	 *
	 * @return int
	 */
	public static function get_corrupt_media() {
		global $wpdb;
		$table = $wpdb->base_prefix . 'bp_album';
		if ( self::table_exists( $table ) ) {
			return $wpdb->get_results( "SELECT id,title,pic_org_url FROM {$wpdb->base_prefix}bp_album WHERE import_status=-1" );
		}

		return 0;
	}

	/**
	 * Batch import albums.
	 *
	 * @param int $count Count for albums to import.
	 *
	 * @return mixed
	 */
	public static function batch_import( $count = 5 ) {
		global $wpdb;

		$table = $wpdb->base_prefix . 'bp_album';
		if ( self::table_exists( $table ) ) {
			$bp_album_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->base_prefix}bp_album WHERE import_status = 0 ORDER BY owner_id LIMIT %d", $count ) );
			return $bp_album_data;
		}

		return array();
	}

	/**
	 * Ajax import callback function.
	 */
	public static function bpmedia_ajax_import_callback() {

		if ( ! check_ajax_referer( 'bpmedia-bpalbumimporter', 'rtm_wpnonce' ) ) {
			wp_send_json( false );
		}

		$page          = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT );
		$count         = filter_input( INPUT_GET, 'count', FILTER_SANITIZE_NUMBER_INT );
		$page          = isset( $page ) ? $page : 1;
		$count         = isset( $count ) ? $count : 5;
		$bp_album_data = self::batch_import( $count );

		global $wpdb;

		$table               = $wpdb->base_prefix . 'bp_album';
		$activity_table      = $wpdb->base_prefix . 'bp_activity';
		$activity_meta_table = $wpdb->base_prefix . 'bp_activity_meta';
		$comments            = 0;

		foreach ( $bp_album_data as &$bp_album_item ) {

			if ( get_site_option( 'bp_media_bp_album_importer_base_path' ) === '' ) {
				$base_path = pathinfo( $bp_album_item->pic_org_path );
				update_site_option( 'bp_media_bp_album_importer_base_path', $base_path['dirname'] );
			}
			$bpm_host_wp = new BPMediaHostWordpress();
			$bpm_host_wp->check_and_create_album( 0, 0, $bp_album_item->owner_id );
			$album_id          = self::create_album( $bp_album_item->owner_id, 'Imported Media' );
			$imported_media_id = BPMediaImporter::add_media( $album_id, $bp_album_item->title, $bp_album_item->description, $bp_album_item->pic_org_path, $bp_album_item->privacy, $bp_album_item->owner_id, 'Imported Media' );
			$wpdb->update( $table, array( 'import_status' => ( $imported_media_id ) ? $imported_media_id : - 1 ), array( 'id' => $bp_album_item->id ), array( '%d' ), array( '%d' ) );
			if ( $imported_media_id ) {
				$comments += (int) self::update_recorded_time_and_comments( $imported_media_id, $bp_album_item->id, "{$wpdb->base_prefix}bp_album" );

				$bp_album_media_id = $wpdb->get_var( "SELECT activity.id from $activity_table as activity INNER JOIN $table as album ON ( activity.item_id = album.id ) WHERE activity.item_id = $bp_album_item->id AND activity.component = 'album' AND activity.type='bp_album_picture'" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->update( $table, array( 'old_activity_id' => $bp_album_media_id ), array( 'id' => $bp_album_item->id ), array( '%d' ), array( '%d' ) );
				$bp_new_activity_id = $wpdb->get_var( "SELECT id from $activity_table WHERE item_id = $imported_media_id AND component = 'activity' AND type='activity_update' AND secondary_item_id=0" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->update( $table, array( 'new_activity_id' => $bp_new_activity_id ), array( 'id' => $bp_album_item->id ), array( '%d' ), array( '%d' ) );

				if ( $wpdb->update(
					$activity_meta_table,
					array( 'activity_id' => $bp_new_activity_id ),
					array(
						'activity_id' => $bp_album_media_id,
						'meta_key'    => 'favorite_count',
					),
					array( '%d' ),
					array( '%d' )
				)
				) {
					$wpdb->update( $table, array( 'favorites' => 1 ), array( 'id' => $bp_album_item->id ), array( '%d' ), array( '%d' ) );
				}
			}
		}

		$finished_users = self::get_completed_users();

		echo wp_json_encode(
			array(
				'page'     => $page,
				'users'    => $finished_users[0]->users,
				'comments' => $comments,
			)
		);

		die();
	}

	/**
	 * Ajax import favorites callback.
	 */
	public static function bpmedia_ajax_import_favorites() {

		if ( ! check_ajax_referer( 'bpmedia-bpalbumimporter', 'rtm_wpnonce' ) ) {
			wp_send_json( array( 'status' => false ) );
		}

		global $wpdb;
		$table = $wpdb->base_prefix . 'bp_album';
		$users = count_users();

		echo wp_json_encode(
			array(
				'favorites' => $wpdb->get_var( "SELECT COUNT(id) from $table WHERE favorites != 0" ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				'users'     => $users['total_users'],
				'offset'    => (int) get_site_option( 'bp_media_bp_album_favorite_import_status', 0 ),
			)
		);

		die();
	}

	/**
	 * Ajax import step favorites callback.
	 */
	public static function bpmedia_ajax_import_step_favorites() {

		if ( ! check_ajax_referer( 'bpmedia-bpalbumimporter', 'rtm_wpnonce' ) ) {
			wp_send_json( array( 'status' => false ) );
		}

		$offset   = filter_input( INPUT_GET, 'offset', FILTER_SANITIZE_NUMBER_INT );
		$redirect = filter_input( INPUT_GET, 'redirect', FILTER_SANITIZE_URL );
		$offset   = isset( $offset ) ? $offset : 0;
		$redirect = isset( $redirect ) ? $redirect : false;

		global $wpdb;

		$table     = $wpdb->base_prefix . 'bp_album';
		$blogusers = get_users(
			array(
				'meta_key' => 'bp_favorite_activities',
				'offset'   => $offset,
				'number'   => 1,
			)
		);

		if ( $blogusers ) {
			foreach ( $blogusers as $user ) {

				$favorite_activities = get_user_meta( $user->ID, 'bp_favorite_activities', true );

				if ( $favorite_activities ) {

					$new_favorite_activities = $favorite_activities;
					foreach ( $favorite_activities as $key => $favorite ) {
						$new_act = $wpdb->get_var( $wpdb->prepare( "SELECT new_activity_id from $table WHERE old_activity_id = %d limit 1", $favorite ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						if ( ! empty( $new_act ) ) {
							$new_favorite_activities[ $key ] = $new_act;
						}
					}
					update_user_meta( $user->ID, 'bp_favorite_activities', $new_favorite_activities );
				}
				$completed_users_favorites = (int) get_site_option( 'bp_media_bp_album_favorite_import_status', 0 ) + 1;
				update_site_option( 'bp_media_bp_album_favorite_import_status', $completed_users_favorites );
			}
		}

		echo esc_url( $redirect );
		die();
	}

	/**
	 * Cleanup after install.
	 */
	public static function cleanup_after_install() {
		global $wpdb;

		if ( ! check_ajax_referer( 'bpmedia-bpalbumimporter', 'rtm_wpnonce' ) ) {
			wp_send_json( array( 'status' => false ) );
		}

		$table = $wpdb->base_prefix . 'bp_album';
		$dir   = get_site_option( 'bp_media_bp_album_importer_base_path' );
		BPMediaImporter::cleanup( $table, $dir );
		die();
	}

	/**
	 * Update recorded time and comments.
	 *
	 * @param object $media Media.
	 * @param int    $bp_album_id Album id.
	 * @param string $table Table.
	 *
	 * @return bool|int
	 */
	public static function update_recorded_time_and_comments( $media, $bp_album_id, $table ) {
		global $wpdb;
		if ( function_exists( 'bp_activity_add' ) ) {
			if ( ! is_object( $media ) ) {
				try {
					$media = new BPMediaHostWordpress( $media );
				} catch ( exception $e ) {
					return false;
				}
			}

			$activity_id = get_post_meta( $media->get_id(), 'bp_media_child_activity', true );
			$comments    = 0;

			if ( $activity_id ) {
				$date_uploaded   = $wpdb->get_var( $wpdb->prepare( "SELECT date_uploaded from $table WHERE id = %d", $bp_album_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$old_activity_id = $wpdb->get_var( $wpdb->prepare( "SELECT id from {$wpdb->base_prefix}bp_activity WHERE component = 'album' AND type = 'bp_album_picture' AND item_id = %d", $bp_album_id ) );
				if ( $old_activity_id ) {
					$comments = $wpdb->get_results( $wpdb->prepare( "SELECT id,secondary_item_id from {$wpdb->base_prefix}bp_activity WHERE component = 'activity' AND type = 'activity_comment' AND item_id = %d", $old_activity_id ) );
					foreach ( $comments as $comment ) {
						$update = array( 'item_id' => $activity_id );
						if ( $comment->secondary_item_id === $old_activity_id ) {
							$update['secondary_item_id'] = $activity_id;
						}
						$wpdb->update( $wpdb->base_prefix . 'bp_activity', $update, array( 'id' => $comment->id ) );
						BP_Activity_Activity::rebuild_activity_comment_tree( $activity_id );
					}
				}
				$wpdb->update( $wpdb->base_prefix . 'bp_activity', array( 'date_recorded' => $date_uploaded ), array( 'id' => $activity_id ) );

				return count( $comments );
			}

			return 0;
		}
	}

	/**
	 * BPAlbum plugin deactivate.
	 */
	public static function bp_album_deactivate() {
		if ( ! check_ajax_referer( 'bpmedia-bpalbumimporter', 'rtm_wpnonce' ) ) {
			deactivate_plugins( 'bp-album/loader.php' );
		}
		die( true );
	}
}
