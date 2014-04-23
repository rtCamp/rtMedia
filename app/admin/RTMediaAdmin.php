<?php
/**
 * Description of RTMediaAdmin
 *
 * @package    RTMedia
 * @subpackage Admin
 *
 */
if ( ! class_exists( 'RTMediaAdmin' ) ){

	class RTMediaAdmin {

		public $rtmedia_upgrade;
		public $rtmedia_settings;
		public $rtmedia_encoding;
		public $rtmedia_support;
		public $rtmedia_feed;

		public function __construct() {
			global $rtmedia;
			add_action( 'init', array( $this, 'video_transcoding_survey_response' ) );
			add_action( 'admin_init', array( $this, 'presstrends_plugin' ) );

			//$rtmedia_feed = new RTMediaFeed();
			add_filter( "plugin_action_links_" . RTMEDIA_BASE_NAME, array( &$this, 'plugin_add_settings_link' ) );
			//add_action ( 'wp_ajax_rtmedia_fetch_feed', array( $rtmedia_feed, 'fetch_feed' ), 1 );
			$this->rtmedia_support = new RTMediaSupport();
			add_action( 'wp_ajax_rtmedia_select_request', array( $this->rtmedia_support, 'get_form' ), 1 );
			add_action( 'wp_ajax_rtmedia_cancel_request', create_function( '', 'do_settings_sections("rtmedia-support"); die();' ), 1 );
			add_action( 'wp_ajax_rtmedia_submit_request', array( $this->rtmedia_support, 'submit_request' ), 1 );
			//add_action ( 'wp_ajax_rtmedia_fetch_feed', array( $rtmedia_feed, 'fetch_feed' ), 1 );
			add_action( 'wp_ajax_rtmedia_linkback', array( $this, 'linkback' ), 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_deactivate', 'BPMediaAlbumimporter::bp_album_deactivate', 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_import', 'BPMediaAlbumimporter::bpmedia_ajax_import_callback', 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_import_favorites', 'BPMediaAlbumimporter::bpmedia_ajax_import_favorites', 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_import_step_favorites', 'BPMediaAlbumimporter::bpmedia_ajax_import_step_favorites', 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_cleanup', 'BPMediaAlbumimporter::cleanup_after_install' );
			add_action( 'wp_ajax_rtmedia_convert_videos_form', array( $this, 'convert_videos_mailchimp_send' ), 1 );
			add_action( 'wp_ajax_rtmedia_correct_upload_filetypes', array( $this, 'correct_upload_filetypes' ), 1 );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_premium_addon_link' ), 1, 4 );
			add_action( 'wp_dashboard_setup', array( &$this, 'add_dashboard_widgets' ), 0 );
			add_filter( "attachment_fields_to_edit", array( $this, "edit_video_thumbnail" ), null, 2 );
			add_filter( "attachment_fields_to_save", array( $this, "save_video_thumbnail" ), null, 2 );
			add_action( 'wp_ajax_rtmedia_hide_video_thumb_admin_notice', array( $this, 'rtmedia_hide_video_thumb_admin_notice' ), 1 );
			add_action( 'wp_ajax_rtmedia_hide_addon_update_notice', array( $this, 'rtmedia_hide_addon_update_notice' ), 1 );
			$obj_encoding = new RTMediaEncoding( true );
			if ( $obj_encoding->api_key ){
				add_filter( "media_row_actions", array( $this, "add_reencode_link" ), null, 2 );
				add_action( 'admin_head-upload.php', array( $this, 'add_bulk_actions_regenerate' ) );
				add_action( 'admin_footer', array( $this, 'rtmedia_regenerate_thumb_js' ) );
				add_action( 'admin_action_bulk_video_regenerate_thumbnails', array( $this, 'bulk_action_handler' ) );
				add_action( 'admin_action_-1', array( $this, 'bulk_action_handler' ) );
			}
			add_action( 'wp_ajax_rt_media_regeneration', array( $this, 'rt_media_regeneration' ), 1 );
			if ( ! isset( $rtmedia->options ) ){
				$rtmedia->options = rtmedia_get_site_option( 'rtmedia-options' );
			}
			if ( isset ( $_POST[ "rtmedia-options" ] ) ){
				if ( isset ( $_POST[ "rtmedia-options" ][ "general_showAdminMenu" ] ) && $_POST[ "rtmedia-options" ][ "general_showAdminMenu" ] == "1" ){
					add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100, 1 );
				}
			} else {
				if ( intval( $rtmedia->options[ "general_showAdminMenu" ] ) == 1 ){
					add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100, 1 );
				}
			}

			if ( is_admin() ){
				add_action( 'admin_enqueue_scripts', array( $this, 'ui' ) );
				//bp_core_admin_hook();
				add_action( 'admin_menu', array( $this, 'menu' ), 1 );
				add_action( 'init', array( $this, 'bp_admin_tabs' ) );
				if ( is_multisite() ){
					add_action( 'network_admin_edit_rtmedia', array( $this, 'save_multisite_options' ) );
				}
			}
			$this->rtmedia_settings = new RTMediaSettings();
			$this->rtmedia_encoding = new RTMediaEncoding();
			//	    show rtmedia advertisement
			//            if(! defined("RTMEDIA_PRO_VERSION") )
			//                add_action ( 'rtmedia_before_default_admin_widgets', array( $this, 'rtmedia_advertisement' ),1);
			if ( ! class_exists( "BuddyPress" ) ){
				add_action( 'admin_init', array( $this, 'check_permalink_admin_notice' ) );
			}

			add_action( 'wp_ajax_rtmedia_hide_template_override_notice', array( $this, 'rtmedia_hide_template_override_notice' ), 1 );
			add_action( 'admin_init', array( $this, 'rtmedia_bp_add_update_type' ) );
			add_action( 'wp_ajax_rtmedia_hide_inspirebook_release_notice', array( $this, 'rtmedia_hide_inspirebook_release_notice' ), 1 );
			$rtmedia_media_import = new RTMediaMediaSizeImporter(); // do not delete this line. We only need to create object of this class if we are in admin section
			add_action( 'admin_notices', array( $this, 'rtmedia_admin_notices' ) );
			add_action( 'network_admin_notices', array( $this, 'rtmedia_network_admin_notices' ) );

		}

		function rtmedia_network_admin_notices() {
			if ( is_multisite() ){
				$this->upload_filetypes_error();
			}
		}

		function rtmedia_admin_notices() {
			$this->upload_filetypes_error();
			$this->rtmedia_regenerate_thumbnail_notice();
			$this->rtmedia_addon_update_notice();
			$this->rtmedia_update_template_notice();
			$this->rtmedia_inspirebook_release_notice();
		}

		function rtmedia_inspirebook_release_notice() {
			$site_option = rtmedia_get_site_option( "rtmedia_inspirebook_release_notice" );
			if ( ( ! $site_option || $site_option != "hide" ) && ( get_stylesheet() != 'inspirebook' ) ){
				rtmedia_update_site_option( "rtmedia_inspirebook_release_notice", "show" );
				?>
				<div class="updated rtmedia-inspire-book-notice">
					<p>
						<span><a href="https://rtcamp.com/store/inspirebook/" target="_blank"><b>Meet
									InspireBook</b></a> - First official rtMedia premium theme.</span>
						<a href="#" onclick="rtmedia_hide_template_override_notice()" style="float:right">Dismiss</a>
					</p>
				</div>
				<script type="text/javascript">
					function rtmedia_hide_template_override_notice() {
						var data = {action: 'rtmedia_hide_inspirebook_release_notice'};
						jQuery.post( ajaxurl, data, function ( response ) {
							response = response.trim();
							if ( response === "1" )
								jQuery( '.rtmedia-inspire-book-notice' ).remove();
						} );
					}
				</script>
			<?php
			}
		}

		function rtmedia_hide_inspirebook_release_notice() {
			if ( rtmedia_update_site_option( "rtmedia_inspirebook_release_notice", "hide" ) ){
				echo "1";
			} else {
				echo "0";
			}
			die();
		}

		function rtmedia_bp_add_update_type() {
			if ( class_exists( 'BuddyPress' ) && function_exists( 'bp_activity_set_action' ) ){
				bp_activity_set_action( 'rtmedia_update', 'rtmedia_update', 'rtMedia Update' );
			}
		}

		function check_permalink_admin_notice() {
			global $wp_rewrite;
			if ( empty( $wp_rewrite->permalink_structure ) ){
				add_action( 'admin_notices', array( $this, 'rtmedia_permalink_notice' ) );
			}
		}

		function rtmedia_permalink_notice() {
			echo '<div class="error rtmedia-permalink-change-notice">
		    <p> <b>' . __( 'rtMedia:' ) . '</b> ' . __( ' You must ' ) . '<a href="' . admin_url( 'options-permalink.php' ) . '">' . __( 'update permalink structure' ) . '</a>' . __( ' to something other than the default for it to work.', 'rtmedia' ) . ' </p>
		    </div>';
		}

		function rtmedia_addon_update_notice() {
			if ( ! $this->check_for_addon_update_notice() ){
				return;
			}
			if ( is_rt_admin() ){
				$site_option = rtmedia_get_site_option( "rtmedia-addon-update-notice" );
				if ( ! $site_option || $site_option != "hide" ){
					rtmedia_update_site_option( "rtmedia-addon-update-notice", "show" );
					echo '<div class="error rtmedia-addon-upate-notice">
		    <p> <b>' . __( 'rtMedia:' ) . '</b> ' . __( 'Please update all premium add-ons that you had purchased from rtCamp from your ', 'rtmedia' ) . ' <a href="https://rtcamp.com/my-account/" target="_blank">' . __( 'account', "rtmedia" ) . '</a>. <a href="#" onclick="rtmedia_hide_addon_update_notice()" style="float:right">Hide</a> </p>
		    </div>';
				}

				?>
				<script type="text/javascript">
					function rtmedia_hide_addon_update_notice() {
						var data = {
							action: 'rtmedia_hide_addon_update_notice'
						};
						jQuery.post( ajaxurl, data, function ( response ) {
							response = response.trim();
							if ( response === "1" )
								jQuery( '.rtmedia-addon-upate-notice' ).remove();
						} );
					}
				</script>
			<?php
			}
		}

		function check_for_addon_update_notice() {
			$return_falg = false;
			if ( defined( 'RTMEDIA_INSTAGRAM_PATH' ) ){
				$plugin_info = get_plugin_data( RTMEDIA_INSTAGRAM_PATH . 'index.php' );
				if ( isset( $plugin_info[ 'Version' ] ) && $plugin_info[ 'Version' ] < "2.1.2" ){
					$return_falg = true;
				}
			} else {
				if ( defined( 'RTMEDIA_PHOTO_TAGGING_PATH' ) ){
					$plugin_info = get_plugin_data( RTMEDIA_PHOTO_TAGGING_PATH . 'index.php' );
					if ( isset( $plugin_info[ 'Version' ] ) && $plugin_info[ 'Version' ] < "2.2.1" ){
						$return_falg = true;
					}
				} else {
					if ( defined( 'RTMEDIA_PRO_PATH' ) ){
						$plugin_info = get_plugin_data( RTMEDIA_PRO_PATH . 'index.php' );
						if ( isset( $plugin_info[ 'Version' ] ) && $plugin_info[ 'Version' ] < "1.8.1" ){
							$return_falg = true;
						}
					} else {
						if ( defined( 'RTMEDIA_FFMPEG_PATH' ) ){
							$plugin_info = get_plugin_data( RTMEDIA_FFMPEG_PATH . 'index.php' );
							if ( isset( $plugin_info[ 'Version' ] ) && $plugin_info[ 'Version' ] < "2.1.1" ){
								$return_falg = true;
							}
						} else {
							if ( defined( 'RTMEDIA_KALTURA_PATH' ) ){
								$plugin_info = get_plugin_data( RTMEDIA_KALTURA_PATH . 'index.php' );
								if ( isset( $plugin_info[ 'Version' ] ) && $plugin_info[ 'Version' ] < "3.0.3" ){
									$return_falg = true;
								}
							}
						}
					}
				}
			}

			return $return_falg;
		}

		function bp_admin_tabs() {
			if ( current_user_can( 'manage_options' ) ){
				add_action( 'bp_admin_tabs', array( $this, 'tab' ) );
			}
		}

		function rtmedia_advertisement() {
			$src = RTMEDIA_URL . "app/assets/img/rtMedia-pro-ad.png"
			?>
			<div class='rtmedia-admin-ad'>
				<a href='http://rtcamp.com/store/rtmedia-pro/' target='_blank' title='rtMedia Pro'>
					<img src='<?php echo $src; ?>' alt="<?php _e( 'rtMedia Pro is released', 'rtmedia' ); ?>"/>
				</a>
			</div>
		<?php
		}

		// Create the function to output the contents of our Dashboard Widget

		function rtMedia_dashboard_widget_function() {
			?>

			<div class="inside">

				<div class="table table_content">
					<p class="sub"><?php _e( "Media Stats" ); ?></p>
					<table>
						<tbody> <?php
						$rtMedia_model = new RTMediaModel();
						$sql = "select media_type, count(id) as count from {$rtMedia_model->table_name} where blog_id='" . get_current_blog_id() . "' group by media_type";
						global $wpdb;
						$results = $wpdb->get_results( $sql );
						if ( $results ){
							foreach ( $results as $media ) {
								if ( defined( strtoupper( 'RTMEDIA_' . $media->media_type . '_PLURAL_LABEL' ) ) ){
									?>
									<tr>
										<td class="b"> <?php echo $media->count; ?> </td>
										<td class="t"><?php echo constant( strtoupper( 'RTMEDIA_' . $media->media_type . '_PLURAL_LABEL' ) ); ?></td>
									</tr>
								<?php
								}
							}
						}
						?>
						</tbody>
					</table>
				</div>
				<div class="table table_discussion">
					<p class="sub"><?php _e( 'Usage Stats', 'rtmedia' ); ?></p>
					<table>
						<tbody> <?php
						$sql = "select count(*) from {$wpdb->users}";
						$results = $wpdb->get_var( $sql );
						?>
						<tr>
							<td class="b"> <?php echo $results; ?> </td>
							<td class="t"><?php _e( 'Total ', 'rtmedia' ) ?></td>
						</tr>
						<?php
						$sql = "select count(distinct media_author) from {$rtMedia_model->table_name}";
						$results = $wpdb->get_var( $sql );
						?>
						<tr>
							<td class="b"> <?php echo $results; ?> </td>
							<td class="t"><?php _e( 'With Media', 'rtmedia' ) ?></td>
						</tr>
						<?php
						$sql = "select count(*) from $wpdb->comments where comment_post_ID in (select media_id from {$rtMedia_model->table_name})";
						$results = $wpdb->get_var( $sql );
						?>
						<tr>
							<td class="b"> <?php echo $results; ?> </td>
							<td class="t"><?php _e( 'Comments ', 'rtmedia' ) ?></td>
						</tr>
						<?php
						$sql = "select sum(likes) from {$rtMedia_model->table_name}";
						$results = $wpdb->get_var( $sql );
						?>
						<tr>
							<td class="b"> <?php echo $results; ?> </td>
							<td class="t"><?php _e( 'Likes', 'rtmedia' ) ?></td>
						</tr>

						</tbody>
					</table>
				</div>
				<div class="versions">
					<p>
						<b>rtMedia Links:</b> <a href="http://rtcamp.com"><?php _e( 'Homepage', 'rtmedia' ); ?></a> | <a
							href="admin.php?page=rtmedia-support#rtmedia-general"><?php _e( 'Free Support', 'rtmedia' ); ?></a>
						| <a href="http://rtcamp.com/rtmedia/addons/"><?php _e( 'Premium Addons', 'rtmedia' ); ?></a>
					</p>
				</div>
			</div>
		<?php
		}

		// Create the function use in the action hook

		function add_dashboard_widgets() {
			wp_add_dashboard_widget( 'rtmedia_dashboard_widget', __( 'Right Now in rtMedia', 'rtmedia' ), array( &$this, 'rtMedia_dashboard_widget_function' ) );
			global $wp_meta_boxes;

			// Get the regular dashboard widgets array
			// (which has our new widget already but at the end)

			$normal_dashboard = $wp_meta_boxes[ 'dashboard' ][ 'normal' ][ 'core' ];

			// Backup and delete our new dashboard widget from the end of the array

			$example_widget_backup = array( 'rtmedia_dashboard_widget' => $normal_dashboard[ 'rtmedia_dashboard_widget' ] );
			unset ( $normal_dashboard[ 'rtmedia_dashboard_widget' ] );

			// Merge the two arrays together so our widget is at the beginning

			$sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );

			// Save the sorted array back into the original metaboxes

			$wp_meta_boxes[ 'dashboard' ][ 'normal' ][ 'core' ] = $sorted_dashboard;
		}

		function plugin_add_settings_link( $links ) {
			$settings_link = '<a href="' . admin_url( 'admin.php?page=rtmedia-settings' ) . '">Settings</a>';
			array_push( $links, $settings_link );
			$settings_link = '<a href="' . admin_url( 'admin.php?page=rtmedia-support' ) . '">Support</a>';
			array_push( $links, $settings_link );

			return $links;
		}

		function add_reencode_link( $actions, $post ) {

			$mime_type_array = explode( "/", $post->post_mime_type );
			if ( is_array( $mime_type_array ) && $mime_type_array != "" && $mime_type_array[ 0 ] == "video" ){
				$actions[ 'reencode' ] = "<a class='submitdelete' onclick='return rtmedia_regenerate_thumbs(" . $post->ID . ")' href='#'>" . __( 'Regenerate Thumbnail', 'rtmedia' ) . "</a>";
			}

			return $actions;
		}

		function bulk_action_handler() {
			if ( $_REQUEST[ 'action' ] == "bulk_video_regenerate_thumbnails" && $_REQUEST[ 'media' ] != "" ){
				wp_safe_redirect( add_query_arg( array( "media_ids" => urlencode( implode( ",", $_REQUEST[ "media" ] ) ) ), admin_url( "admin.php?page=rtmedia-regenerate" ) ) );
				exit;
			}
		}

		function admin_bar_menu( $admin_bar ) {
			if ( ! current_user_can( 'manage_options' ) ){
				return;
			}

			$admin_bar->add_menu( array(
				'id' => 'rtMedia', 'title' => 'rtMedia', 'href' => admin_url( 'admin.php?page=rtmedia-settings' ), 'meta' => array(
					'title' => __( 'rtMedia', 'rtmedia' ),
				),
			) );
			$admin_bar->add_menu( array(
				'id' => 'rt-media-dashborad', 'parent' => 'rtMedia', 'title' => __( 'Settings', 'rtmedia' ), 'href' => admin_url( 'admin.php?page=rtmedia-settings' ), 'meta' => array(
					'title' => __( 'Settings', 'rtmedia' ), 'target' => '_self',
				),
			) );
			$admin_bar->add_menu( array(
				'id' => 'rt-media-addons', 'parent' => 'rtMedia', 'title' => __( 'Addons', "rtmedia" ), 'href' => admin_url( 'admin.php?page=rtmedia-addons' ), 'meta' => array(
					'title' => __( 'Addons', 'rtmedia' ), 'target' => '_self',
				),
			) );
			$admin_bar->add_menu( array(
				'id' => 'rt-media-support', 'parent' => 'rtMedia', 'title' => __( 'Support', 'rtmedia' ), 'href' => admin_url( 'admin.php?page=rtmedia-support' ), 'meta' => array(
					'title' => __( 'Support', 'rtmedia' ), 'target' => '_self',
				),
			) );
			$admin_bar->add_menu( array(
				'id' => 'rt-media-themes', 'parent' => 'rtMedia', 'title' => __( 'Themes', 'rtmedia' ), 'href' => admin_url( 'admin.php?page=rtmedia-themes' ), 'meta' => array(
					'title' => __( 'Themes', 'rtmedia' ), 'target' => '_self',
				),
			) );
			$admin_bar->add_menu( array(
				'id' => 'rt-media-hire-us', 'parent' => 'rtMedia', 'title' => __( 'Hire Us', 'rtmedia' ), 'href' => admin_url( 'admin.php?page=rtmedia-hire-us' ), 'meta' => array(
					'title' => __( 'Hire Us', 'rtmedia' ), 'target' => '_self',
				),
			) );
		}

		/**
		 * Generates the Admin UI.
		 *
		 * @param string $hook
		 */

		/**
		 *
		 * @param type $hook
		 */
		public function ui( $hook ) {
			$admin_pages = array(
				'rtmedia_page_rtmedia-migration', 'rtmedia_page_rtmedia-kaltura-settings', 'rtmedia_page_rtmedia-ffmpeg-settings', 'toplevel_page_rtmedia-settings', 'rtmedia_page_rtmedia-addons', 'rtmedia_page_rtmedia-support', 'rtmedia_page_rtmedia-themes', 'rtmedia_page_rtmedia-hire-us', 'rtmedia_page_rtmedia-importer', 'rtmedia_page_rtmedia-regenerate', 'rtmedia_page_rtmedia-premium'
			);
			$admin_pages = apply_filters( 'rtmedia_filter_admin_pages_array', $admin_pages );
			if ( in_array( $hook, $admin_pages ) || strpos( $hook, 'rtmedia-migration' ) ){
				$admin_ajax = admin_url( 'admin-ajax.php' );

				wp_enqueue_script( 'bootstrap-switch', RTMEDIA_URL . 'app/assets/js/bootstrap-switch.js', array( 'jquery' ), RTMEDIA_VERSION );
				wp_enqueue_script( 'slider-tabs', RTMEDIA_URL . 'app/assets/js/jquery.sliderTabs.min.js', array( 'jquery', 'jquery-effects-core' ), RTMEDIA_VERSION );
				wp_enqueue_script( 'observe-hashchange', RTMEDIA_URL . 'app/assets/js/jquery.observehashchange.pack.js', array( 'jquery' ), RTMEDIA_VERSION );
				wp_enqueue_script( 'rtmedia-admin', RTMEDIA_URL . 'app/assets/js/admin.js', array( 'jquery-ui-dialog' ), RTMEDIA_VERSION );
				wp_localize_script( 'rtmedia-admin', 'rtmedia_on_label', __( 'ON', 'rtmedia' ) );
				wp_localize_script( 'rtmedia-admin', 'rtmedia_off_label', __( 'OFF', 'rtmedia' ) );
				wp_localize_script( 'rtmedia-admin', 'rtmedia_admin_ajax', $admin_ajax );
				wp_localize_script( 'rtmedia-admin', 'rtmedia_admin_url', admin_url() );
				wp_localize_script( 'rtmedia-admin', 'rtmedia_admin_url', admin_url() );
				if ( isset( $_REQUEST[ 'page' ] ) && ( in_array( $_REQUEST[ 'page' ], array( "rtmedia-settings", "rtmedia-addons", "rtmedia-themes", "rtmedia-support", "rtmedia-hire-us" ) ) ) ){
					wp_enqueue_script( 'rtmedia-foundation-modernizr', RTMEDIA_URL . 'lib/foundation/custom.modernizr.js', array( 'jquery' ), RTMEDIA_VERSION );
					wp_enqueue_script( 'rtmedia-foundation', RTMEDIA_BOWER_COMPONENTS_URL . 'js/foundation.js', array( 'jquery' ), RTMEDIA_VERSION );
					//wp_enqueue_script ( 'rtmedia-foundation-section', RTMEDIA_URL . 'lib/foundation/foundation.section.js', array('jquery'), RTMEDIA_VERSION );
				}

				$rtmedia_admin_strings = array(
					'no_refresh' => __( 'Please do not refresh this page.', 'rtmedia' ), 'something_went_wrong' => __( 'Something went wronng. Please <a href onclick="location.reload();">refresh</a> page.', 'rtmedia' ), 'are_you_sure' => __( 'This will subscribe you to the free plan.', 'rtmedia' ), 'disable_encoding' => __( 'Are you sure you want to disable the encoding service? Make sure you note your api key before disabling it incase you want to activate it in future.', 'rtmedia' )
				);
				wp_localize_script( 'rtmedia-admin', 'rtmedia_admin_strings', $rtmedia_admin_strings );
				wp_localize_script( 'rtmedia-admin', 'settings_url', add_query_arg( array( 'page' => 'rtmedia-settings' ), ( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) ) ) . '#privacy_enabled' );
				wp_localize_script( 'rtmedia-admin', 'settings_rt_album_import_url', add_query_arg( array( 'page' => 'rtmedia-settings' ), ( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) ) ) );
				wp_enqueue_style( 'font-awesome', RTMEDIA_URL . 'app/assets/css/font-awesome.min.css', '', RTMEDIA_VERSION );
				wp_enqueue_style( 'bootstrap-switch', RTMEDIA_URL . 'app/assets/css/bootstrap-switch.css', '', RTMEDIA_VERSION );
				wp_enqueue_style( 'slider-tabs', RTMEDIA_URL . 'app/assets/css/jquery.sliderTabs.min.css', '', RTMEDIA_VERSION );
				wp_enqueue_style( 'grid-foundation', RTMEDIA_URL . 'app/assets/css/grid-foundation.css', '', RTMEDIA_VERSION );
				wp_enqueue_style( 'rtmedia-main', RTMEDIA_URL . 'app/assets/css/main.css', '', RTMEDIA_VERSION );
				wp_enqueue_style( 'rtmedia-admin', RTMEDIA_URL . 'app/assets/css/admin.css', '', RTMEDIA_VERSION );
				if ( isset( $_REQUEST[ 'page' ] ) && ( in_array( $_REQUEST[ 'page' ], array( "rtmedia-settings", "rtmedia-addons", "rtmedia-themes", "rtmedia-support", "rtmedia-hire-us" ) ) ) ){
					wp_enqueue_style( 'foundation-admin-css', RTMEDIA_URL . 'app/assets/css/settings.css', '', RTMEDIA_VERSION );
				}
				wp_enqueue_style( 'wp-jquery-ui-dialog' );
			} else {
				wp_enqueue_style( 'rtmedia-widget', RTMEDIA_URL . 'app/assets/css/widget.css', '', RTMEDIA_VERSION );
			}
		}

		/**
		 * Admin Menu
		 *
		 * @global string 'rtmedia'
		 */
		public function menu() {
			add_menu_page( 'rtMedia', 'rtMedia', 'manage_options', 'rtmedia-settings', array( $this, 'settings_page' ), RTMEDIA_URL . "app/assets/img/rtmedia-logo.png", "40.1111" );
			add_submenu_page( 'rtmedia-settings', __( 'Settings', 'rtmedia' ), __( 'Settings', 'rtmedia' ), 'manage_options', 'rtmedia-settings', array( $this, 'settings_page' ) );
			add_submenu_page( 'rtmedia-settings', __( 'Addons', 'rtmedia' ), __( 'Addons', 'rtmedia' ), 'manage_options', 'rtmedia-addons', array( $this, 'addons_page' ) );
			add_submenu_page( 'rtmedia-settings', __( 'Support', 'rtmedia' ), __( 'Support', 'rtmedia' ), 'manage_options', 'rtmedia-support', array( $this, 'support_page' ) );
			add_submenu_page( 'rtmedia-settings', __( 'Themes', 'rtmedia' ), __( 'Themes', 'rtmedia' ), 'manage_options', 'rtmedia-themes', array( $this, 'theme_page' ) );
			add_submenu_page( 'rtmedia-settings', __( 'Hire Us', 'rtmedia' ), __( 'Hire Us', 'rtmedia' ), 'manage_options', 'rtmedia-hire-us', array( $this, 'hire_us_page' ) );
			if ( ! defined( "RTMEDIA_PRO_VERSION" ) ){
				add_submenu_page( 'rtmedia-settings', __( 'Premium', 'rtmedia' ), __( 'Premium ', 'rtmedia' ), 'manage_options', 'rtmedia-premium', array( $this, 'premium_page' ) );
			}

			$obj_encoding = new RTMediaEncoding( true );
			if ( $obj_encoding->api_key ){
				add_submenu_page( 'rtmedia-settings', __( 'Regenerate Thumbnail', 'rtmedia' ), __( 'Regen. Thumbnail ', 'rtmedia' ), 'manage_options', 'rtmedia-regenerate', array( $this, 'rt_regenerate_thumbnail' ) );
			}

			//            add_submenu_page('rtmedia-settings', __('Importer', 'rtmedia'), __('Importer', 'rtmedia'), 'manage_options', 'rtmedia-importer', array($this, 'rt_importer_page'));
			//            if (!BPMediaPrivacy::is_installed()) {
			//          add_submenu_page('rtmedia-settings', __('rtMedia Database Update', 'rtmedia'), __('Update Database', 'rtmedia'), 'manage_options', 'rtmedia-db-update', array($this, 'privacy_page'));
			//            }
		}

		function rt_regenerate_thumbnail() {
			$prog = new rtProgress();
			$done = 0;
			?>
			<div class="wrap">
				<h2> <?php _e( 'Regenerate Video Thumbnails', 'rtmedia' ); ?> </h2>
				<?php
				if ( isset( $_REQUEST[ "media_ids" ] ) && trim( $_REQUEST[ "media_ids" ] ) != "" ){
					$requested = false;
					$media_ids = explode( ',', $_REQUEST[ "media_ids" ] );
					$total     = count( $media_ids );
				} else {
					$media_ids = $this->get_video_without_thumbs();
					$total     = count( $media_ids );
				}
				?>
				<script>
					var rt_thumb_all_media = <?php echo json_encode($media_ids); ?>;
				</script>
				<?php
				if ( ! isset( $requested ) ){
					?>
					<br/> <br/>
					<input type="button" class="button button-primary" id="rt-start-media-regenerate"
						   value="<?php _e( 'Regenerate Pending Thumbnails', 'rtmedia' ); ?>"/>
				<?php } ?>
				<div id="rt-migration-progress">
					<br/> <br/>
					<?php
					$temp = $prog->progress( $done, $total );
					$prog->progress_ui( $temp, true );
					?>
					<p> <?php _e( 'Total Videos', 'rtmedia' ) ?> : <span class='rt-total'><?php echo $total; ?></span>
					</p>

					<p> <?php _e( 'Sent of regenerate thumbails', 'rtmedia' ) ?> : <span class='rt-done'>0</span></p>

					<p> <?php _e( 'Fail to regenerate thumbails', 'rtmedia' ) ?> : <span class='rt-fail'>0</span></p>

				</div>
				<?php

				?>
				<script>

					var db_done = 0;
					var db_fail = 0;
					var db_total = <?php echo $total; ?>;
					var indx = 0;
					function db_start_regenrate() {
						if ( indx < db_total ) {
							jQuery.ajax( {
								url: rtmedia_admin_ajax,
								type: 'post',
								data: {
									"action": "rt_media_regeneration",
									"media_id": rt_thumb_all_media[indx++]
								},
								success: function ( data ) {
									data = JSON.parse( data );

									if ( data.status == false ) {
										handle_regenrate_fail();
									} else {
										db_done++;
										var progw = Math.ceil( (db_done / db_total) * 100 );
										if ( progw > 100 ) {
											progw = 100;
										}
										jQuery( '#rtprogressbar>div' ).css( 'width', progw + '%' );
										jQuery( 'span.rt-done' ).html( db_done );
										db_start_regenrate();
									}
								},
								error: function () {
									handle_regenrate_fail();
								}
							} );
						} else {
							alert( "<?php _e( 'Regenerate Video Thumbnails Done', 'rtmedia' ); ?>" );
						}
					}
					function handle_regenrate_fail() {
						db_fail++;
						jQuery( 'span.rt-fail' ).html( db_fail );
						db_start_regenrate();
					}
					if ( jQuery( "#rt-start-media-regenerate" ).length > 0 ) {
						jQuery( "#rt-migration-progress" ).hide()
						jQuery( "#rt-start-media-regenerate" ).click( function () {
							jQuery( this ).hide();
							jQuery( "#rt-migration-progress" ).show()
							db_start_regenrate();
						} )
					} else {
						db_start_regenrate();
					}

				</script>


			</div> <?php
		}

		function rtmedia_regenerate_thumbnail_notice() {
			$obj_encoding = new RTMediaEncoding( true );
			if ( $obj_encoding->api_key ){
				$site_option = rtmedia_get_site_option( "rtmedia-video-thumb-notice" );
				if ( ! $site_option || $site_option != "hide" ){
					rtmedia_update_site_option( "rtmedia-video-thumb-notice", "show" );
					$videos_without_thumbs = get_video_without_thumbs();
					if ( isset( $videos_without_thumbs ) && is_array( $videos_without_thumbs ) && sizeof( $videos_without_thumbs ) > 0 ){
						echo '<div class="error rtmedia-regenerate-video-thumb-error">
                <p>
                ' . sprintf( __( "You have total %s videos without thumbnails. Click <a href='%s'> here </a> to generate thumbnails. <a href='#' onclick='rtmedia_hide_video_thumb_notice()' style='float:right'>Hide</a>", 'rtmedia' ), sizeof( $videos_without_thumbs ), admin_url( 'admin.php?page=rtmedia-regenerate' ) ) . '
                </p>
                </div>';

						?>
						<script type="text/javascript">
							function rtmedia_hide_video_thumb_notice() {
								var data = {action: 'rtmedia_hide_video_thumb_admin_notice'};
								jQuery.post( ajaxurl, data, function ( response ) {
									response = response.trim();
									if ( response === "1" )
										jQuery( '.rtmedia-regenerate-video-thumb-error' ).remove();
								} );
							}
						</script>
					<?php
					}
				}
			}
		}

		function rtmedia_hide_video_thumb_admin_notice() {
			if ( rtmedia_update_site_option( "rtmedia-video-thumb-notice", "hide" ) ){
				echo "1";
			} else {
				echo "0";
			}
			die();
		}

		function rtmedia_hide_addon_update_notice() {
			if ( rtmedia_update_site_option( "rtmedia-addon-update-notice", "hide" ) ){
				echo "1";
			} else {
				echo "0";
			}
			die();
		}

		function rt_media_regeneration() {
			if ( isset( $_POST[ 'media_id' ] ) ){
				$model      = new RTMediaModel();
				$media      = $model->get_media( array( 'media_id' => $_POST[ 'media_id' ] ), 0, 1 );
				$media_type = $media[ 0 ]->media_type;
				$response   = array();
				if ( $media_type == "video" ){
					$objRTMediaEncoding = new RTMediaEncoding( true );
					$autoformat         = "thumbnails";
					$objRTMediaEncoding->reencoding( intval( $_POST[ 'media_id' ] ), $autoformat );
					$response[ 'status' ] = true;
				} else {
					$response[ 'status' ]  = false;
					$response[ 'message' ] = __( 'not a video ...', 'rtmedia' );
				}
				echo json_encode( $response );
				die();
			}
		}


		function get_video_without_thumbs() {
			$rtmedia_model = new RTMediaModel();
			$sql           = "select media_id from {$rtmedia_model->table_name} where media_type = 'video' and blog_id = '" . get_current_blog_id() . "' and cover_art is null";
			global $wpdb;
			$results = $wpdb->get_col( $sql );

			return $results;
		}

		/**
		 * Render the BuddyPress Media Settings page
		 */
		public function settings_page() {
			$this->render_page( 'rtmedia-settings', 'rtmedia' );
		}

		public function privacy_page() {
			$this->render_page( 'rtmedia-privacy' );
		}

		public function rt_importer_page() {
			$this->render_page( 'rtmedia-importer' );
		}

		public function convert_videos_page() {
			$this->render_page( 'rtmedia-convert-videos' );
		}

		/**
		 * Render the BuddyPress Media Addons page
		 */
		public function addons_page() {
			$this->render_page( 'rtmedia-addons' );
		}

		/**
		 * Render the BuddyPress Media Support page
		 */
		public function support_page() {
			$this->render_page( 'rtmedia-support' );
		}

		public function premium_page() {
			$this->render_page( 'rtmedia-premium' );
		}

		public function theme_page() {
			$this->render_page( 'rtmedia-themes' );
		}

		public function hire_us_page() {
			$this->render_page( 'rtmedia-hire-us' );
		}

		/**
		 *
		 * @return type
		 */
		static function get_current_tab() {
			return isset ( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : "rtmedia-settings";
		}

		/**
		 * Render BPMedia Settings
		 *
		 * @global string 'rtmedia'
		 */

		/**
		 *
		 * @param type $page
		 * @param type $option_group
		 */
		public function render_page( $page, $option_group = null ) {
			?>

			<div class="wrap bp-media-admin <?php echo $this->get_current_tab(); ?>">
				<div id="icon-buddypress-media" class="icon32"><br></div>
				<div>
					<h2 class="nav-tab-wrapper"><?php $this->rtmedia_tabs(); ?>
						<span class="alignright by"><a class="rt-link"
													   href="http://rtcamp.com/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media"
													   target="_blank"
													   title="rtCamp : <?php _e( 'Empowering The Web With WordPress', 'rtmedia' ); ?>"><img
									src="<?php echo RTMEDIA_URL; ?>app/assets/img/rtcamp-logo.png"></a></span>
					</h2>
				</div>


				<?php //settings_errors (); ?>
				<div class="row bp-media-settings-boxes-container">
					<div id="bp-media-settings-boxes" class="columns large-9">

						<?php
						$settings_url = ( is_multisite() ) ? network_admin_url( 'edit.php?action=' . $option_group ) : 'options.php';
						?>
						<?php if ( $option_group ){ //$option_group if ($page == "bp-media-settings") action="<?php echo $settings_url;   ?>
							<form id="bp_media_settings_form" name="bp_media_settings_form" method="post"
								  enctype="multipart/form-data">
							<div class="bp-media-metabox-holder"><?php
								settings_fields( $option_group );
								if ( $page == "rtmedia-settings" ){


									echo '<div id="rtm-settings-tabs">';
									$sub_tabs = $this->settings_sub_tabs();
									RTMediaFormHandler::rtForm_settings_tabs_content( $page, $sub_tabs );
									echo '</div>';
								} else {
									do_settings_sections( $page );
								}
								?>
								<div class="clearfix">&nbsp;</div>
								<div class="row">
									<input type="hidden" name="rtmedia-options-save" value="true">
									<input type="submit" id="rtmedia-settings-submit"
										   class="rtmedia-settings-submit button button-primary button-big"
										   value="<?php _e( 'Save Settings', 'rtmedia' ); ?>">
								</div>
							</div>
							</form><?php
						} else {
							?>
							<div class="bp-media-metabox-holder">

							<?php
							if ( $page == 'rtmedia-addons' ){
								RTMediaAddon::render_addons( $page );
							} else {
								if ( $page == 'rtmedia-support' ){
									$rtmedia_support = new RTMediaSupport( false );
									$rtmedia_support->render_support( $page );
								} else {
									if ( $page == 'rtmedia-themes' ){
										RTMediaThemes::render_themes( $page );
									} else {
										do_settings_sections( $page );
									}
								}
							}
							?>
							<?php
							do_action( 'rtmedia_admin_page_insert', $page );
							?>
							</div><?php
							do_action( 'rtmedia_admin_page_append', $page );
						}
						?>


					</div>
					<!-- .bp-media-settings-boxes -->
					<div class="metabox-holder bp-media-metabox-holder columns large-3">
						<?php $this->admin_sidebar(); ?>
					</div>
				</div>
				<!-- .metabox-holder -->
			</div><!-- .bp-media-admin --><?php
		}

		/**
		 * Adds a tab for Media settings in the BuddyPress settings page
		 *
		 * @global type $bp_media
		 */
		public function tab() {

			$tabs_html    = '';
			$idle_class   = 'nav-tab';
			$active_class = 'nav-tab nav-tab-active';
			$tabs         = array();

			// Check to see which tab we are on
			$tab = $this->get_current_tab();
			/* rtMedia */
			$tabs[ ] = array(
				'href' => get_admin_url( null, add_query_arg( array( 'page' => 'rtmedia-settings' ), 'admin.php' ) ), 'title' => __( 'rtMedia', 'rtmedia' ), 'name' => __( 'rtMedia', 'rtmedia' ), 'class' => ( $tab == 'rtmedia-settings' || $tab == 'rtmedia-addons' || $tab == 'rtmedia-support' || $tab == 'rtmedia-importer' ) ? $active_class : $idle_class
			);


			foreach ( $tabs as $tab ) {
				$tabs_html .= '<a id="bp-media" title= "' . $tab[ 'title' ] . '"  href="' . $tab[ 'href' ] . '" class="' . $tab[ 'class' ] . '">' . $tab[ 'name' ] . '</a>';
			}
			echo $tabs_html;
		}

		public function rtmedia_tabs( $active_tab = '' ) {
			// Declare local variables
			$tabs_html    = '';
			$idle_class   = 'nav-tab';
			$active_class = 'nav-tab nav-tab-active';

			// Setup core admin tabs
			$tabs = array(
				array(
					'href' => get_admin_url( null, add_query_arg( array( 'page' => 'rtmedia-settings' ), 'admin.php' ) ), 'name' => __( 'Settings', 'rtmedia' ), 'slug' => 'rtmedia-settings'
				), array(
					'href' => get_admin_url( null, add_query_arg( array( 'page' => 'rtmedia-addons' ), 'admin.php' ) ), 'name' => __( 'Addons', 'rtmedia' ), 'slug' => 'rtmedia-addons'
				), array(
					'href' => get_admin_url( null, add_query_arg( array( 'page' => 'rtmedia-themes' ), 'admin.php' ) ), 'name' => __( 'Themes', 'rtmedia' ), 'slug' => 'rtmedia-themes'
				), array(
					'href' => get_admin_url( null, add_query_arg( array( 'page' => 'rtmedia-hire-us' ), 'admin.php' ) ), 'name' => __( 'Hire Us', 'rtmedia' ), 'slug' => 'rtmedia-hire-us'
				), array(
					'href' => get_admin_url( null, add_query_arg( array( 'page' => 'rtmedia-support' ), 'admin.php' ) ), 'name' => __( 'Support', 'rtmedia' ), 'slug' => 'rtmedia-support'
				), //              array(
				//                  'href' => get_admin_url(null, add_query_arg(array('page' => 'rtmedia-importer'), 'admin.php')),
				//                  'name' => __('Importer', 'rtmedia'),
				//                  'slug' => 'rtmedia-importer'
				//                        )

			);

			$tabs = apply_filters( 'media_add_tabs', $tabs );

			// Loop through tabs and build navigation
			foreach ( array_values( $tabs ) as $tab_data ) {
				$is_current = ( bool )( $tab_data[ 'slug' ] == $this->get_current_tab() );
				$tab_class  = $is_current ? $active_class : $idle_class;
				if ( isset( $tab_data[ 'class' ] ) && is_array( $tab_data[ 'class' ] ) ){
					$tab_class .= " " . implode( " ", $tab_data[ 'class' ] );
				}
				$tabs_html .= '<a href="' . $tab_data[ 'href' ] . '" class="' . $tab_class . '">' . $tab_data[ 'name' ] . '</a>';
			}

			// Output the tabs
			echo $tabs_html;

			//            // Do other fun things
			//            do_action('bp_media_admin_tabs');
		}

		public function settings_content_tabs( $page ) {
			global $wp_settings_sections, $wp_settings_fields;

			if ( ! isset ( $wp_settings_sections ) || ! isset ( $wp_settings_sections[ $page ] ) ){
				return;
			}

			foreach ( ( array )$wp_settings_sections[ $page ] as $section ) {
				if ( $section[ 'title' ] ){
					echo "<h3>{$section['title']}</h3>\n";
				}

				if ( $section[ 'callback' ] ){
					call_user_func( $section[ 'callback' ], $section );
				}

				if ( ! isset ( $wp_settings_fields ) || ! isset ( $wp_settings_fields[ $page ] ) || ! isset ( $wp_settings_fields[ $page ][ $section[ 'id' ] ] ) ){
					continue;
				}
				echo '<table class="form-table">';
				do_settings_fields( $page, $section[ 'id' ] );
				echo '</table>';
			}
		}

		/**
		 * Adds a sub tabs to the BuddyPress Media settings page
		 *
		 * @global type $bp_media
		 */
		public function settings_sub_tabs() {
			$tabs_html = '';
			$tabs      = array();

			// Check to see which tab we are on
			$tab = $this->get_current_tab();
			/* rtMedia */

			$tabs[ 7 ] = array(
				'href' => '#rtmedia-display', 'icon' => 'rtmicon-desktop', 'title' => __( 'Display', 'rtmedia' ), 'name' => __( 'Display', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'display_content' )
			);


			if ( class_exists( "BuddyPress" ) ){
				$tabs[ 20 ] = array(
					'href' => '#rtmedia-bp', 'icon' => 'rtmicon-group', 'title' => __( 'rtMedia BuddyPress', 'rtmedia' ), 'name' => __( 'BuddyPress', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'buddypress_content' ) //change it to BuddyPress Content
				);
			}

			$tabs[ 30 ] = array(
				'href' => '#rtmedia-types', 'icon' => 'rtmicon-film', 'title' => __( 'rtMedia Types', 'rtmedia' ), 'name' => __( 'Types', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'types_content' )
			);

			$tabs[ 40 ] = array(
				'href' => '#rtmedia-sizes', 'icon' => 'rtmicon-expand', 'title' => __( 'rtMedia Sizes', 'rtmedia' ), 'name' => __( 'Image Sizes', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'sizes_content' )
			);

			$tabs[ 50 ] = array(
				'href' => '#rtmedia-privacy', 'icon' => 'rtmicon-lock', 'title' => __( 'rtMedia Privacy', 'rtmedia' ), 'name' => __( 'Privacy', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'privacy_content' )
			);
			$tabs[ 60 ] = array(
				'href' => '#rtmedia-custom-css-settings', 'icon' => 'rtmicon-css3', 'title' => __( 'rtMedia Custom CSS', 'rtmedia' ), 'name' => __( 'Custom CSS', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'custom_css_content' )
			);

			$tabs = apply_filters( 'rtmedia_add_settings_sub_tabs', $tabs, $tab );

			$tabs[ ] = array(
				'href' => '#rtmedia-general', 'icon' => 'rtmicon-wrench', 'title' => __( 'Other Settings', 'rtmedia' ), 'name' => __( 'Other Settings', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'general_content' )
			);

			return $tabs;
		}

		/*
		 * Updates the media count of all users.
		 */

		/**
		 *
		 * @global type $wpdb
		 * @return boolean
		 */
		public function update_count() {
			global $wpdb;

			$query  = "SELECT
        p.post_author,pmp.meta_value,
        SUM(CASE WHEN post_mime_type LIKE 'image%' THEN 1 ELSE 0 END) as Images,
        SUM(CASE WHEN post_mime_type LIKE 'music%' THEN 1 ELSE 0 END) as Music,
        SUM(CASE WHEN post_mime_type LIKE 'video%' THEN 1 ELSE 0 END) as Videos,
        SUM(CASE WHEN post_type LIKE 'bp_media_album' THEN 1 ELSE 0 END) as Albums
    FROM
        $wpdb->posts p inner join $wpdb->postmeta  pm on pm.post_id = p.id INNER JOIN $wpdb->postmeta pmp
    on pmp.post_id = p.id  WHERE
        pm.meta_key = 'bp-media-key' AND
        pm.meta_value > 0 AND
        pmp.meta_key = 'bp_media_privacy' AND
        ( post_mime_type LIKE 'image%' OR post_mime_type LIKE 'music%' OR post_mime_type LIKE 'video%' OR post_type LIKE 'bp_media_album')
    GROUP BY p.post_author,pmp.meta_value order by p.post_author";
			$result = $wpdb->get_results( $query );
			if ( ! is_array( $result ) ){
				return false;
			}
			$formatted = array();
			foreach ( $result as $obj ) {
				$formatted[ $obj->post_author ][ $obj->meta_value ] = array(
					'image' => $obj->Images, 'video' => $obj->Videos, 'music' => $obj->Music, 'album' => $obj->Albums,
				);
			}

			foreach ( $formatted as $user => $obj ) {
				update_user_meta( $user, 'rtmedia_count', $obj );
			}

			return true;
		}

		/* Multisite Save Options - http://wordpress.stackexchange.com/questions/64968/settings-api-in-multisite-missing-update-message#answer-72503 */

		/**
		 *
		 * @global type $bp_media_admin
		 */
		public function save_multisite_options() {
			global $rtmedia_admin;
			if ( isset ( $_POST[ 'refresh-count' ] ) ){
				$rtmedia_admin->update_count();
			}
			do_action( 'rtmedia_sanitize_settings', $_POST );

			if ( isset ( $_POST[ 'rtmedia_options' ] ) ){
				rtmedia_update_site_option( 'rtmedia_options', $_POST[ 'rtmedia_options' ] );
				//
				//                // redirect to settings page in network
				wp_redirect( add_query_arg( array( 'page' => 'rtmedia-settings', 'updated' => 'true' ), ( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) ) ) );
				exit;
			}
		}

		/* Admin Sidebar */

		/**
		 *
		 * @global type $bp_media
		 */
		public function admin_sidebar() {
			do_action( 'rtmedia_before_default_admin_widgets' );
			$current_user = wp_get_current_user();
			//            echo '<p><a target="_blank" href="http://rtcamp.com/news/buddypress-media-review-contest/?utm_source=dashboard&#038;utm_medium=plugin&#038;utm_campaign=buddypress-media"><img src="' . RTMEDIA_URL . 'app/assets/img/bpm-contest-banner.jpg" alt="BuddyPress Media Review Contest" /></a></p>';
			//                        $contest = '<a target="_blank" href="http://rtcamp.com/news/buddypress-media-review-contest/?utm_source=dashboard&#038;utm_medium=plugin&#038;utm_campaign=buddypress-media"><img src="'.RTMEDIA_URL.'app/assets/img/bpm-contest-banner.jpg" alt="BuddyPress Media Review Contest" /></a>';
			//                        new BPMediaAdminWidget('bpm-contest', __('', 'rtmedia'), $contest);
			$setting_page_url = admin_url( 'admin.php?page=rtmedia-settings#rtmedia-general' );
			$message          = sprintf( __( 'I use @buddypressmedia http://rt.cx/rtmedia on %s', 'rtmedia' ), home_url() );
			$addons           = '<div id="social" class="">
                            <div class="row">
                                <div class="columns large-11">
                                    <p><a href="http://twitter.com/home/?status=' . $message . '" class="button" target= "_blank" title="' . __( 'Post to Twitter Now', 'rtmedia' ) . '">' . __( 'Post to Twitter', 'rtmedia' ) . '</a></p>
                                    <p><a href="https://www.facebook.com/sharer/sharer.php?u=http://rtcamp.com/buddypress-media/" class="button" target="_blank" title="' . __( 'Share on Facebook Now', 'rtmedia' ) . '">' . __( 'Share on Facebook', 'rtmedia' ) . '</a></p>
                                    <p><a href="http://wordpress.org/support/view/plugin-reviews/buddypress-media?rate=5#postform" class="button" target= "_blank" title="' . __( 'Rate rtMedia on Wordpress.org', 'rtmedia' ) . '">' . __( 'Rate on Wordpress.org', 'rtmedia' ) . '</a></p>
                                    <p><a href="' . sprintf( '%s', 'http://feeds.feedburner.com/rtcamp/' ) . '"  title="' . __( 'Subscribe to our feeds', 'rtmedia' ) . '" class="button" target="_blank" title="' . __( 'Subscribe to our Feeds', 'rtmedia' ) . '">' . __( 'Subscribe to our Feeds', 'rtmedia' ) . '</a></p>
                                    <p><a href="' . $setting_page_url . '"  title="' . __( 'Add link to footer', 'rtmedia' ) . '" class="button" title="' . __( 'Add link to footer', 'rtmedia' ) . '">' . __( 'Add link to footer', 'rtmedia' ) . '</a></p>
                                </div>
                            </div>
                        </div>';
			//<li><a href="' . sprintf('%s', 'http://www.facebook.com/rtCamp.solutions/') . '"  title="' . __('Become a fan on Facebook', 'rtmedia') . '" class="bp-media-facebook bp-media-social">' . __('Facebook', 'rtmedia') . '</a></li>
			//<li><a href="' . sprintf('%s', 'https://twitter.com/rtcamp/') . '"  title="' . __('Follow us on Twitter', 'rtmedia') . '" class="bp-media-twitter bp-media-social">' . __('Twitter', 'rtmedia') . '</a></li>  ;
			new RTMediaAdminWidget ( 'spread-the-word', __( 'Spread the Word', 'rtmedia' ), $addons );

			//                        $donate = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			//                           <!-- Identify your business so that you can collect the payments. -->
			//                           <input type="hidden" name="business"
			//                           value="paypal@rtcamp.com">
			//                           <!-- Specify a Donate button. -->
			//                           <input type="hidden" name="cmd" value="_donations">
			//                           <!-- Specify details about the contribution -->
			//                           <input type="hidden" name="item_name" value="BuddyPress Media">
			//                           <label><b>' . __('USD', 'rtmedia') . '</b></label>
			//                         <input type="text" name="amount" size="3">
			//                           <input type="hidden" name="currency_code" value="USD">
			//                           <!-- Display the payment button. -->
			//                           <input type="hidden" name="cpp_header_image" value="' . RTMEDIA_URL . 'app/assets/img/rtcamp-logo.png">
			//                           <input type="image" id="rt-donate-button" name="submit" border="0"
			//                           src="' . RTMEDIA_URL . 'app/assets/img/paypal-donate-button.png"
			//                           alt="PayPal - The safer, easier way to pay online">
			//                       </form><br />
			//                       <center><b>' . __('OR', 'rtmedia') . '</b></center><br />
			//                       <center>' . __('Use <a href="https://rtcamp.com/store/product-category/buddypress/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media">premium add-ons</a> starting from $9', 'rtmedia') . '</center>';
			//                        ;
			//                        new BPMediaAdminWidget('donate', __('Donate', 'rtmedia'), $donate);

			$branding = '<form action="http://rtcamp.us1.list-manage1.com/subscribe/post?u=85b65c9c71e2ba3fab8cb1950&amp;id=9e8ded4470" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                            <div class="mc-field-group">
                                    <input type="email" value="' . $current_user->user_email . '" name="EMAIL" placeholder="Email" class="required email" id="mce-EMAIL">
                                    <input style="display:none;" type="checkbox" checked="checked" value="1" name="group[1721][1]" id="mce-group[1721]-1721-0">
                                    <div id="mce-responses" class="clear">
                                        <div class="response" id="mce-error-response" style="display:none"></div>
                                        <div class="response" id="mce-success-response" style="display:none"></div>
                                    </div>
                                    <input type="submit" value="' . __( 'Subscribe', 'rtmedia' ) . '" name="subscribe" id="mc-embedded-subscribe" class="button">
                            </div>
                        </form>';
			new RTMediaAdminWidget ( 'branding', __( 'Subscribe', 'rtmedia' ), $branding );

			$news = '<img src ="' . admin_url( '/images/wpspin_light.gif' ) . '" /> Loading...';
			//new RTMediaAdminWidget ( 'latest-news', __( 'Latest News', 'rtmedia' ), $news );
			do_action( 'rtmedia_after_default_admin_widgets' );
		}

		public function linkback() {
			if ( isset ( $_POST[ 'linkback' ] ) && $_POST[ 'linkback' ] ){
				return rtmedia_update_site_option( 'rtmedia-add-linkback', true );
			} else {
				return rtmedia_update_site_option( 'rtmedia-add-linkback', false );
			}
			die;
		}

		public function convert_videos_mailchimp_send() {
			if ( $_POST[ 'interested' ] == 'Yes' && ! empty ( $_POST[ 'choice' ] ) ){
				wp_remote_get( add_query_arg( array( 'rtmedia-convert-videos-form' => 1, 'choice' => $_POST[ 'choice' ], 'url' => urlencode( $_POST[ 'url' ] ), 'email' => $_POST[ 'email' ] ), 'http://rtcamp.com/' ) );
			} else {
				rtmedia_update_site_option( 'rtmedia-survey', 0 );
			}
			_e( 'Thank you for your time.', 'rtmedia' );
			die;
		}

		public function video_transcoding_survey_response() {
			if ( isset ( $_GET[ 'survey-done' ] ) && ( $_GET[ 'survey-done' ] == md5( 'survey-done' ) ) ){
				rtmedia_update_site_option( 'rtmedia-survey', 0 );
			}
		}

		public function plugin_meta_premium_addon_link( $plugin_meta, $plugin_file, $plugin_data, $status ) {
			if ( plugin_basename( RTMEDIA_PATH . 'index.php' ) == $plugin_file ){
				$plugin_meta[ ] = '<a href="https://rtcamp.com/store/product-category/buddypress/?utm_source=dashboard&#038;utm_medium=plugin&#038;utm_campaign=buddypress-media" title="' . __( 'Premium Add-ons', 'rtmedia' ) . '">' . __( 'Premium Add-ons', 'rtmedia' ) . '</a>';
			}

			return $plugin_meta;
		}

		public function upload_filetypes_error() {
			global $rtmedia;
			$upload_filetypes = rtmedia_get_site_option( 'upload_filetypes', 'jpg jpeg png gif' );
			$upload_filetypes = explode( ' ', $upload_filetypes );
			$flag             = false;
			if ( isset ( $rtmedia->options[ 'images_enabled' ] ) && $rtmedia->options[ 'images_enabled' ] ){
				$not_supported_image = array_diff( array( 'jpg', 'jpeg', 'png', 'gif' ), $upload_filetypes );
				if ( ! empty ( $not_supported_image ) ){
					echo '<div class="error upload-filetype-network-settings-error">
                        <p>
                        ' . sprintf( __( 'You have images enabled on rtMedia but your network allowed filetypes does not allow uploading of %s. Click <a href="%s">here</a> to change your settings manually.', 'rtmedia' ), implode( ', ', $not_supported_image ), network_admin_url( 'settings.php#upload_filetypes' ) ) . '
                            <br /><strong>' . __( 'Recommended', 'rtmedia' ) . ':</strong> <input type="button" class="button update-network-settings-upload-filetypes" class="button" value="' . __( 'Update Network Settings Automatically', 'rtmedia' ) . '"> <img style="display:none;" src="' . admin_url( 'images/wpspin_light.gif' ) . '" />
                        </p>
                        </div>';
					$flag = true;
				}
			}
			if ( isset ( $rtmedia->options[ 'videos_enabled' ] ) && $rtmedia->options[ 'videos_enabled' ] ){
				if ( ! in_array( 'mp4', $upload_filetypes ) ){
					echo '<div class="error upload-filetype-network-settings-error">
                        <p>
                        ' . sprintf( __( 'You have video enabled on BuddyPress Media but your network allowed filetypes does not allow uploading of mp4. Click <a href="%s">here</a> to change your settings manually.', 'rtmedia' ), network_admin_url( 'settings.php#upload_filetypes' ) ) . '
                            <br /><strong>' . __( 'Recommended', 'rtmedia' ) . ':</strong> <input type="button" class="button update-network-settings-upload-filetypes" class="button" value="' . __( 'Update Network Settings Automatically', 'rtmedia' ) . '"> <img style="display:none;" src="' . admin_url( 'images/wpspin_light.gif' ) . '" />
                        </p>
                        </div>';
					$flag = true;
				}
			}
			if ( isset ( $rtmedia->options[ 'audio_enabled' ] ) && $rtmedia->options[ 'audio_enabled' ] ){
				if ( ! in_array( 'mp3', $upload_filetypes ) ){
					echo '<div class="error upload-filetype-network-settings-error"><p>' . sprintf( __( 'You have audio enabled on BuddyPress Media but your network allowed filetypes does not allow uploading of mp3. Click <a href="%s">here</a> to change your settings manually.', 'rtmedia' ), network_admin_url( 'settings.php#upload_filetypes' ) ) . '
                            <br /><strong>' . __( 'Recommended', 'rtmedia' ) . ':</strong> <input type="button" class="button update-network-settings-upload-filetypes" class="button" value="' . __( 'Update Network Settings Automatically', 'rtmedia' ) . '"> <img style="display:none;" src="' . admin_url( 'images/wpspin_light.gif' ) . '" />
                        </p>
                        </div>';
					$flag = true;
				}
			}
			if ( $flag ){
				?>
				<script type="text/javascript">
					jQuery( '.upload-filetype-network-settings-error' ).on( 'click', '.update-network-settings-upload-filetypes', function () {
						jQuery( '.update-network-settings-upload-filetypes' ).siblings( 'img' ).show();
						jQuery( '.update-network-settings-upload-filetypes' ).prop( 'disabled', true );
						jQuery.post( ajaxurl, {action: 'rtmedia_correct_upload_filetypes'}, function ( response ) {
							if ( response ) {
								jQuery( '.upload-filetype-network-settings-error:first' ).after( '<div style="display: none;" class="updated rtmedia-network-settings-updated-successfully"><p><?php _e( 'Network settings updated successfully.', 'rtmedia' ); ?></p></div>' )
								jQuery( '.upload-filetype-network-settings-error' ).remove();
								jQuery( '.bp-media-network-settings-updated-successfully' ).show();
							}
						} );
					} );</script><?php
			}
		}

		public function correct_upload_filetypes() {
			global $rtmedia;
			$upload_filetypes_orig = $upload_filetypes = rtmedia_get_site_option( 'upload_filetypes', 'jpg jpeg png gif' );
			$upload_filetypes      = explode( ' ', $upload_filetypes );
			if ( isset ( $rtmedia->options[ 'images_enabled' ] ) && $rtmedia->options[ 'images_enabled' ] ){
				$not_supported_image = array_diff( array( 'jpg', 'jpeg', 'png', 'gif' ), $upload_filetypes );
				if ( ! empty ( $not_supported_image ) ){
					$update_image_support = null;
					foreach ( $not_supported_image as $ns ) {
						$update_image_support .= ' ' . $ns;
					}
					if ( $update_image_support ){
						$upload_filetypes_orig .= $update_image_support;
						rtmedia_update_site_option( 'upload_filetypes', $upload_filetypes_orig );
					}
				}
			}
			if ( isset ( $rtmedia->options[ 'videos_enabled' ] ) && $rtmedia->options[ 'videos_enabled' ] ){
				if ( ! in_array( 'mp4', $upload_filetypes ) ){
					$upload_filetypes_orig .= ' mp4';
					rtmedia_update_site_option( 'upload_filetypes', $upload_filetypes_orig );
				}
			}
			if ( isset ( $rtmedia->options[ 'audio_enabled' ] ) && $rtmedia->options[ 'audio_enabled' ] ){
				if ( ! in_array( 'mp3', $upload_filetypes ) ){
					$upload_filetypes_orig .= ' mp3';
					rtmedia_update_site_option( 'upload_filetypes', $upload_filetypes_orig );
				}
			}
			echo true;
			die ();
		}

		function edit_video_thumbnail( $form_fields, $post ) {
			if ( isset( $post->post_mime_type ) ){
				$media_type = explode( "/", $post->post_mime_type );
				if ( is_array( $media_type ) && $media_type[ 0 ] == "video" ){
					$media_id         = $post->ID;
					$thumbnail_array  = get_post_meta( $media_id, "rtmedia_media_thumbnails", true );
					$rtmedia_model    = new RTMediaModel();
					$rtmedia_media    = $rtmedia_model->get( array( "media_id" => $media_id ) );
					$video_thumb_html = "";
					if ( is_array( $thumbnail_array ) ){
						$video_thumb_html .= '<ul> ';
						foreach ( $thumbnail_array as $key => $thumbnail_src ) {
							$checked = checked( $thumbnail_src, $rtmedia_media[ 0 ]->cover_art, false );
							$count   = $key + 1;
							$video_thumb_html .= '<li style="width: 150px;display: inline-block;">
                                    <label for="rtmedia-upload-select-thumbnail-' . $count . '">
                                    <input type="radio" ' . $checked . ' id="rtmedia-upload-select-thumbnail-' . $count . '" value="' . $thumbnail_src . '" name="rtmedia-thumbnail" />
                                    <img src=" ' . $thumbnail_src . '" style="max-height: 120px;max-width: 120px; vertical-align: middle;" />
                                    </label>
                                </li> ';

						}

						$video_thumb_html .= '  </ul>';
						$form_fields[ 'rtmedia_video_thumbnail' ] = array(
							'label' => 'Video Thumbnails', 'input' => 'html', 'html' => $video_thumb_html
						);
					}
				}
			}

			return $form_fields;
		}

		function save_video_thumbnail( $post, $attachment ) {
			if ( isset( $post[ 'rtmedia-thumbnail' ] ) ){
				$rtmedia_model = new RTMediaModel();
				$model         = new RTMediaModel();
				$media         = $model->get( array( "media_id" => $post[ 'ID' ] ) );
				$media_id      = $media[ 0 ]->id;
				$rtmedia_model->update( array( "cover_art" => $post[ 'rtmedia-thumbnail' ] ), array( "media_id" => $post[ 'ID' ] ) );
				update_activity_after_thumb_set( $media_id );
			}

			return $post;
		}

		function rtmedia_regenerate_thumb_js() {
			global $pagenow;

			if ( $pagenow == 'upload.php' ){
				?>
				<script type="text/javascript">
					function rtmedia_regenerate_thumbs( post_id ) {
						if ( post_id != "" ) {
							var data = {
								action: 'rt_media_regeneration',
								media_id: post_id
							};
							jQuery.post( ajaxurl, data, function ( data ) {
								data = JSON.parse( data );
								if ( data.status === true ) {
									alert( "<?php _e('Video is sent to generate thumbnails.', 'rtmedia') ?>" );
								}
								else {
									alert( "<?php _e('Video can\'t be sent to generate thumbnails.', 'rtmedia') ?>" );
								}
							} );
						}
					}
				</script>
			<?php
			}
		}

		function add_bulk_actions_regenerate() {
			?>
			<script type="text/javascript">
				jQuery( document ).ready( function ( $ ) {
					$( 'select[name^="action"] option:last-child' ).before( '<option value="bulk_video_regenerate_thumbnails"><?php esc_attr_e( 'Regenerate Video Thumbnails', 'rtmedia'); ?></option>' );
				} );
			</script>
		<?php
		}

		function presstrends_plugin() {
			global $rtmedia;
			$option = $rtmedia->options;
			if ( ! isset( $option[ 'general_AllowUserData' ] ) ){
				return;
			}
			if ( $option[ 'general_AllowUserData' ] == "0" ){
				return;
			}
			// PressTrends Account API Key
			$api_key = 'djbzu1no2tdz4qq4u2fpgaemuup2zzmtjulb';
			$auth    = 'o3w063qppl7ha022jyc3bjpi7usrmczho';
			// Start of Metrics
			global $wpdb;
			$data = get_transient( 'presstrends_cache_data' );
			if ( ! $data || $data == '' ){
				$api_base       = 'http://api.presstrends.io/index.php/api/pluginsites/update?auth=';
				$url            = $api_base . $auth . '&api=' . $api_key . '';
				$count_posts    = wp_count_posts();
				$count_pages    = wp_count_posts( 'page' );
				$comments_count = wp_count_comments();
				if ( function_exists( 'wp_get_theme' ) ){
					$theme_data = wp_get_theme();
					$theme_name = urlencode( $theme_data->Name );
				} else {
					$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
					$theme_name = $theme_data[ 'Name' ];
				}
				$plugin_name = '&';
				foreach ( get_plugins() as $plugin_info ) {
					$plugin_name .= $plugin_info[ 'Name' ] . '&';
				}
				// CHANGE __FILE__ PATH IF LOCATED OUTSIDE MAIN PLUGIN FILE
				$plugin_data         = get_plugin_data( __FILE__ );
				$posts_with_comments = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' AND comment_count > 0" );
				$data                = array(
					'url' => base64_encode( site_url() ), 'posts' => $count_posts->publish, 'pages' => $count_pages->publish, 'comments' => $comments_count->total_comments, 'approved' => $comments_count->approved, 'spam' => $comments_count->spam, 'pingbacks' => $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'" ), 'post_conversion' => ( $count_posts->publish > 0 && $posts_with_comments > 0 ) ? number_format( ( $posts_with_comments / $count_posts->publish ) * 100, 0, '.', '' ) : 0, 'theme_version' => $plugin_data[ 'Version' ], 'theme_name' => $theme_name, 'site_name' => str_replace( ' ', '', get_bloginfo( 'name' ) ), 'plugins' => count( get_option( 'active_plugins' ) ), 'plugin' => urlencode( $plugin_name ), 'wpversion' => get_bloginfo( 'version' ),
				);
				foreach ( $data as $k => $v ) {
					$url .= '&' . $k . '=' . $v . '';
				}
				wp_remote_get( $url );
				set_transient( 'presstrends_cache_data', $data, 60 * 60 * 24 );
			}
		}

		function rtmedia_update_template_notice() {
			$site_option = rtmedia_get_site_option( "rtmedia-update-template-notice-v3_13" );
			if ( ! $site_option || $site_option != "hide" ){
				rtmedia_update_site_option( "rtmedia-update-template-notice-v3_13", "show" );
				if ( is_dir( get_template_directory() . '/rtmedia' ) ){
					echo '<div class="error rtmedia-update-template-notice"><p>' . __( 'Please update rtMedia template files if you have overridden the default rtMedia templates in your theme. If not, you can ignore and hide this notice.' ) . '<a href="#" onclick="rtmedia_hide_template_override_notice()" style="float:right">' . __( 'Hide', 'rtmedia' ) . '</a>' . ' </p></div>';
					?>
					<script type="text/javascript">
						function rtmedia_hide_template_override_notice() {
							var data = {action: 'rtmedia_hide_template_override_notice'};
							jQuery.post( ajaxurl, data, function ( response ) {
								response = response.trim();
								if ( response === "1" )
									jQuery( '.rtmedia-update-template-notice' ).remove();
							} );
						}
					</script>
				<?php
				}
			}
		}

		function rtmedia_hide_template_override_notice() {

			if ( rtmedia_update_site_option( "rtmedia-update-template-notice-v3_13", "hide" ) ){
				echo "1";
			} else {
				echo "0";
			}
			die();
		}
	}
}
