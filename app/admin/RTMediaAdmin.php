<?php
/**
 * File contents Admin class.
 *
 * @package    rtMedia
 * @subpackage Admin
 */

if ( ! class_exists( 'RTMediaAdmin' ) ) {

	/**
	 * Class for RTMedia admin functionality.
	 */
	class RTMediaAdmin {

		/**
		 * Check if rtmedia upgrade available
		 *
		 * @var mixed rtmedia_upgrade
		 */
		public $rtmedia_upgrade;

		/**
		 * RTMedia settings.
		 *
		 * @var RTMediaSettings
		 */
		public $rtmedia_settings;

		/**
		 * Encoding.
		 *
		 * @var mixed rtmedia_encoding
		 */
		public $rtmedia_encoding;

		/**
		 * Support class object.
		 *
		 * @var RTMediaSupport
		 */
		public $rtmedia_support;

		/**
		 * Feed for rtMedia.
		 *
		 * @var mixed
		 */
		public $rtmedia_feed;

		/**
		 * Constructor - get the plugin hooked in and ready
		 *
		 * @access public
		 */
		public function __construct() {
			global $rtmedia;

			// Actions and filters.
			add_action( 'init', array( $this, 'video_transcoding_survey_response' ) );
			add_filter( 'plugin_action_links_' . RTMEDIA_BASE_NAME, array( &$this, 'plugin_add_settings_link' ) );

			$this->rtmedia_support = new RTMediaSupport();
			add_action( 'wp_ajax_rtmedia_select_request', array( $this->rtmedia_support, 'get_form' ), 1 );

			add_action( 'wp_ajax_rtmedia_cancel_request', array( $this->rtmedia_support, 'rtmedia_cancel_request' ), 1 );

			add_action( 'wp_ajax_rtmedia_submit_request', array( $this->rtmedia_support, 'submit_request' ), 1 );

			add_action( 'wp_ajax_rtmedia_linkback', array( $this, 'linkback' ), 1 ); // todo: is it being used ?
			add_action( 'wp_ajax_rtmedia_rt_album_deactivate', 'BPMediaAlbumimporter::bp_album_deactivate', 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_import', 'BPMediaAlbumimporter::bpmedia_ajax_import_callback', 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_import_favorites', 'BPMediaAlbumimporter::bpmedia_ajax_import_favorites', 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_import_step_favorites', 'BPMediaAlbumimporter::bpmedia_ajax_import_step_favorites', 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_cleanup', 'BPMediaAlbumimporter::cleanup_after_install' );
			add_action( 'wp_ajax_rtmedia_convert_videos_form', array( $this, 'convert_videos_mailchimp_send' ), 1 ); // todo: is it being used ?
			add_action( 'wp_ajax_rtmedia_correct_upload_filetypes', array( $this, 'correct_upload_filetypes' ), 1 );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_premium_addon_link' ), 1, 2 );
			add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ), 0 );
			add_action( 'wp_ajax_rtmedia_export_settings', array( $this, 'export_settings' ), 10 );
			add_action(
				'wp_ajax_rtmedia_hide_addon_update_notice',
				array(
					$this,
					'rtmedia_hide_addon_update_notice',
				),
				1
			);
			add_filter( 'media_row_actions', array( $this, 'modify_medialibrary_permalink' ), 10, 2 );

			if ( ! isset( $rtmedia->options ) ) {
				$rtmedia->options = rtmedia_get_site_option( 'rtmedia-options' );
			}

			// Show admin notice to install transcoder plugin.
			if ( ! class_exists( 'RT_Transcoder_Admin' ) ) {
				if ( is_multisite() ) {
					add_action( 'network_admin_notices', array( $this, 'install_transcoder_admin_notice' ) );
				}
				add_action( 'admin_notices', array( $this, 'install_transcoder_admin_notice' ) );
				add_action( 'wp_ajax_install_transcoder_hide_admin_notice', array( $this, 'install_transcoder_hide_admin_notice' ) );
			}

			$rtmedia_option = filter_input( INPUT_POST, 'rtmedia-options', FILTER_DEFAULT, FILTER_SANITIZE_NUMBER_INT );
			if ( isset( $rtmedia_option ) ) {
				if ( isset( $rtmedia_option['general_showAdminMenu'] ) && 1 === intval( $rtmedia_option['general_showAdminMenu'] ) ) {
					add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100, 1 );
				}
			} else {
				if ( 1 === intval( $rtmedia->options['general_showAdminMenu'] ) ) {
					add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100, 1 );
				}
			}

			if ( is_admin() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'ui' ) );
				add_action( 'admin_menu', array( $this, 'menu' ), 1 );
				add_action( 'init', array( $this, 'bp_admin_tabs' ) );

				if ( is_multisite() ) {
					add_action( 'network_admin_edit_rtmedia', array( $this, 'save_multisite_options' ) );
				}
			}

			$this->rtmedia_settings = new RTMediaSettings();

			if ( ! class_exists( 'BuddyPress' ) ) {
				add_action( 'admin_init', array( $this, 'check_permalink_admin_notice' ) );
			}

			add_action(
				'wp_ajax_rtmedia_hide_template_override_notice',
				array(
					$this,
					'rtmedia_hide_template_override_notice',
				),
				1
			);
			add_action( 'admin_init', array( $this, 'rtmedia_bp_add_update_type' ) );
			add_action(
				'wp_ajax_rtmedia_hide_inspirebook_release_notice',
				array(
					$this,
					'rtmedia_hide_inspirebook_release_notice',
				),
				1
			);
			add_action(
				'wp_ajax_rtmedia_hide_social_sync_notice',
				array(
					$this,
					'rtmedia_hide_social_sync_notice',
				),
				1
			);
			add_action( 'wp_ajax_rtmedia_hide_premium_addon_notice', array( $this, 'rtmedia_hide_premium_addon_notice' ), 1 );

			new RTMediaMediaSizeImporter(); // do not delete this line. We only need to create object of this class if we are in admin section.
			if ( class_exists( 'BuddyPress' ) ) {
				new RTMediaActivityUpgrade();
			}
			add_action( 'admin_notices', array( $this, 'rtmedia_admin_notices' ) );
			add_action( 'network_admin_notices', array( $this, 'rtmedia_network_admin_notices' ) );
			add_action( 'admin_init', array( $this, 'rtmedia_addon_license_save_hook' ) );
			add_action( 'admin_init', array( $this, 'rtmedia_migration' ) );

			add_filter( 'removable_query_args', array( $this, 'removable_query_args' ), 10, 1 );

			add_action( 'admin_footer', array( $this, 'rtm_admin_templates' ) );

			// Display invalid add-on license notices to admins.
			add_action( 'admin_notices', array( $this, 'rtm_addon_license_notice' ) );
		}

		/**
		 * Display admin notice.
		 */
		public function install_transcoder_admin_notice() {
			$show_notice = get_site_option( 'install_transcoder_admin_notice', 1 );
			if ( '1' === $show_notice || 1 === $show_notice ) :
				?>
			<div class="notice notice-info install-transcoder-notice is-dismissible">
				<?php wp_nonce_field( '_install_transcoder_hide_notice_', 'install_transcoder_hide_notice_nonce' ); ?>
				<p>
					<?php
					$allowed_tags = array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
						),
					);
					echo wp_kses( __( 'Install <a href="https://wordpress.org/plugins/transcoder/" target="_blank">Transcoder plugin</a> to convert audio/video files and thumbnails generation.', 'buddypress-media' ), $allowed_tags );
					?>
				</p>
			</div>
			<script type="text/javascript">
				jQuery( document ).ready( function() {
					jQuery( '.install-transcoder-notice.is-dismissible' ).on( 'click', '.notice-dismiss', function() {
						var data = {
							action: 'install_transcoder_hide_admin_notice',
							install_transcoder_notice_nonce: jQuery('#install_transcoder_hide_notice_nonce').val()
						};
						jQuery.post( ajaxurl, data, function ( response ) {
							jQuery('.install-transcoder-notice').remove();
						});
					});
				});
			</script>
				<?php
			endif;
		}

		/**
		 * Set option to hide admin notice when user click on dismiss button.
		 */
		public function install_transcoder_hide_admin_notice() {
			if ( check_ajax_referer( '_install_transcoder_hide_notice_', 'install_transcoder_notice_nonce' ) ) {
				update_site_option( 'install_transcoder_admin_notice', '0' );
			}
			die();
		}

		/**
		 * Include admin templates.
		 */
		public function rtm_admin_templates() {
			foreach ( glob( RTMEDIA_PATH . 'app/admin/templates/*.php' ) as $filename ) {
				$slug = rtrim( basename( $filename ), '.php' );

				echo '<script type="text/html" id="' . esc_attr( $slug ) . '">';
				include $filename;
				echo '</script>';
			}

			$page_name = sanitize_text_field( filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ) );

			if ( ! empty( $page_name ) && 'rtmedia-settings' === $page_name ) {
				/**
				 * Filter is use to enable comment option in side the media that are being uploaded in the comment section.
				 *
				 * @since 4.3
				 *
				 * @param True to hide the option and false to show the option.
				 */
				$display = apply_filters( 'rtmedia_disable_media_in_commented_media', false );
				if ( $display ) {
					?>
					<style type="text/css">
						.rtm-option-wrapper .form-table[data-depends="buddypress_enableOnComment"] {
							display: none !important;
						}
					</style>
					<?php
				}
			}
		}

		/**
		 * Function to modify media library permalink.
		 *
		 * @param array  $action Action details array.
		 * @param object $post Post Object.
		 *
		 * @return mixed
		 */
		public function modify_medialibrary_permalink( $action, $post ) {
			$rtm_id = rtmedia_id( $post->ID );

			if ( $rtm_id ) {
				$link  = get_rtmedia_permalink( $rtm_id );
				$title = _draft_or_post_title( $post->post_parent );

				// translators: 1. Title.
				$action['view'] = '<a href="' . esc_url( $link ) . '" title="' . esc_attr( sprintf( 'View "%s"', $title ) ) . '" rel="permalink">' . esc_html__( 'View', 'buddypress-media' ) . '</a>';
			}

			return $action;
		}

		/**
		 * Get rtmedia migration object.
		 */
		public function rtmedia_migration() {
			new RTMediaMigration();
		}

		/**
		 * Add-on License save hook.
		 */
		public function rtmedia_addon_license_save_hook() {
			do_action( 'rtmedia_addon_license_save_hook' );
		}

		/**
		 * Show rtmedia network admin notices.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function rtmedia_network_admin_notices() {
			if ( is_multisite() ) {
				$this->upload_filetypes_error();
			}
		}

		/**
		 * Show rtMedia admin notices.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function rtmedia_admin_notices() {
			if ( current_user_can( 'list_users' ) ) {
				$this->upload_filetypes_error();
				$this->rtmedia_addon_update_notice();
				$this->rtmedia_update_template_notice();

				if ( ! is_rtmedia_vip_plugin() ) {
					$this->rtmedia_inspirebook_release_notice();
					$this->rtmedia_premium_addon_notice();
				}
			}
		}

		/**
		 * For rtMedia Pro split release admin notice
		 */
		public function rtmedia_premium_addon_notice() {
			$site_option = rtmedia_get_site_option( 'rtmedia_premium_addon_notice' );

			if ( ( ! $site_option || 'hide' !== $site_option ) ) {
				rtmedia_update_site_option( 'rtmedia_premium_addon_notice', 'show' );
				?>
				<div class="updated rtmedia-pro-split-notice">
					<p>
						<span>
							<?php
							$product_page = 'https://rtmedia.io/products/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media';

							// translators: 1. Product page link.
							$message = sprintf( __( 'Check 30+ premium rtMedia add-ons on our <a href="%s">store</a>.', 'buddypress-media' ), $product_page );
							?>
							<b><?php esc_html_e( 'rtMedia: ', 'buddypress-media' ); ?></b>
							<?php
							echo wp_kses(
								$message,
								array(
									'a' => array(
										'href' => array(),
									),
								)
							);
							?>
						</span>
						<a href="#" onclick="rtmedia_hide_premium_addon_notice('<?php echo esc_js( wp_create_nonce( 'rtcamp_pro_split' ) ); ?>');" style="float:right"><?php esc_html_e( 'Dismiss', 'buddypress-media' ); ?>></a>
					</p>
				</div>
				<script type="text/javascript">
					function rtmedia_hide_premium_addon_notice(nonce) {
						var data = {action: 'rtmedia_hide_premium_addon_notice', _rtm_nonce: nonce };
						jQuery.post(ajaxurl, data, function (response) {
							response = response.trim();

							if (response === "1")
								jQuery('.rtmedia-pro-split-notice').remove();
						});
					}
				</script>
				<?php
			}
		}

		/**
		 * Hide pro split release notice
		 */
		public function rtmedia_hide_premium_addon_notice() {
			if ( check_ajax_referer( 'rtcamp_pro_split', '_rtm_nonce' ) && rtmedia_update_site_option( 'rtmedia_premium_addon_notice', 'hide' ) ) {
				echo '1';
			} else {
				echo '0';
			}
			die();
		}

		/**
		 * Show rtMedia inspirebook release notice.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function rtmedia_inspirebook_release_notice() {
			$site_option = rtmedia_get_site_option( 'rtmedia_inspirebook_release_notice' );
			if ( ( ! $site_option || 'hide' !== $site_option ) && ( 'inspirebook' !== get_stylesheet() ) ) {
				rtmedia_update_site_option( 'rtmedia_inspirebook_release_notice', 'show' );
				?>
				<div class="updated rtmedia-inspire-book-notice">
					<p>
						<span>
							<a href="https://rtmedia.io/products/inspirebook/" target="_blank">
								<b><?php esc_html_e( 'Meet InspireBook', 'buddypress-media' ); ?></b>
							</a>
							<?php esc_html_e( ' - First official rtMedia premium theme.', 'buddypress-media' ); ?>
						</span>
						<a href="#" onclick="rtmedia_hide_inspirebook_notice()" style="float:right">Dismiss</a>
						<?php wp_nonce_field( '_rtmedia_hide_inspirebook_notice_', 'rtmedia_hide_inspirebook_nonce' ); ?>
					</p>
				</div>
				<script type="text/javascript">
					function rtmedia_hide_inspirebook_notice() {
						var data = {
							action: 'rtmedia_hide_inspirebook_release_notice',
							_rtm_nonce: jQuery('#rtmedia_hide_inspirebook_nonce').val()
						};
						jQuery.post(ajaxurl, data, function (response) {
							response = response.trim();
							if (response === "1")
								jQuery('.rtmedia-inspire-book-notice').remove();
						});
					}
				</script>
				<?php
			}
		}

		/**
		 * Hide rtmedia inspirebook release notice.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function rtmedia_hide_inspirebook_release_notice() {

			if ( check_ajax_referer( '_rtmedia_hide_inspirebook_notice_', '_rtm_nonce' ) && rtmedia_update_site_option( 'rtmedia_inspirebook_release_notice', 'hide' ) ) {
				echo '1';
			} else {
				echo '0';
			}
			die();
		}

		/**
		 * Set rtmedia buddypress update type.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function rtmedia_bp_add_update_type() {
			if ( class_exists( 'BuddyPress' ) && function_exists( 'bp_activity_set_action' ) ) {
				bp_activity_set_action( 'rtmedia_update', 'rtmedia_update', 'rtMedia Update' );
			}
		}

		/**
		 * Show rtmedia check permalink admin notice.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function check_permalink_admin_notice() {
			global $wp_rewrite;
			if ( empty( $wp_rewrite->permalink_structure ) ) {
				add_action( 'admin_notices', array( $this, 'rtmedia_permalink_notice' ) );
			}
		}

		/**
		 * Define rtmedia permalink notice.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function rtmedia_permalink_notice() {
			echo '<div class="error rtmedia-permalink-change-notice">
		    <p> <b>' . esc_html__( 'rtMedia:', 'buddypress-media' ) . '</b> ' . esc_html__( ' You must', 'buddypress-media' ) . ' <a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' . esc_html__( 'update permalink structure', 'buddypress-media' ) . '</a> ' . esc_html__( 'to something other than the default for it to work.', 'buddypress-media' ) . ' </p>
		    </div>';
		}

		/**
		 * Define rtmedia addon update notice.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function rtmedia_addon_update_notice() {

			$site_option = rtmedia_get_site_option( 'rtmedia-addon-update-notice-3_8' );
			if ( is_rt_admin() && ( ! $site_option || 'hide' !== $site_option ) ) {

				if ( ! $this->check_for_addon_update_notice() ) {
					return;
				}
				rtmedia_update_site_option( 'rtmedia-addon-update-notice-3_8', 'show' );
				?>
				<div class="error rtmedia-addon-upate-notice">
					<p>
						<strong><?php esc_html_e( 'rtMedia:', 'buddypress-media' ); ?></strong>
						<?php esc_html_e( 'Please update all premium add-ons that you have purchased from', 'buddypress-media' ); ?>
						<a href="https://rtmedia.io/my-account/" target="_blank"><?php esc_html_e( 'your account', 'buddypress-media' ); ?></a>.
						<a href="#" onclick="rtmedia_hide_addon_update_notice()" style="float:right"><?php esc_html_e( 'Dismiss', 'buddypress-media' ); ?></a>
						<?php wp_nonce_field( 'rtmedia-addon-update-notice-3_8', 'rtmedia-addon-notice' ); ?>
					</p>
				</div>
				<script type="text/javascript">
					function rtmedia_hide_addon_update_notice() {
						var data = {
							action: 'rtmedia_hide_addon_update_notice',
							_rtm_nonce: jQuery('#rtmedia-addon-notice').val();
					};
						jQuery.post(ajaxurl, data, function (response) {
							response = response.trim();
							if (response === "1")
								jQuery('.rtmedia-addon-upate-notice').remove();
						});
					}
				</script>
				<?php
			}
		}

		/**
		 * Show rtMedia addon update notice.
		 *
		 * @access public
		 *
		 * @return bool $return_flag
		 */
		public function check_for_addon_update_notice() {
			$return_flag = false;

			// check for rtMedia Instagram version.
			if ( defined( 'RTMEDIA_INSTAGRAM_PATH' ) ) {
				$plugin_info = get_plugin_data( RTMEDIA_INSTAGRAM_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.1.14' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_PHOTO_TAGGING_PATH' ) ) {
				// check for rtMedia Photo Tagging version.
				$plugin_info = get_plugin_data( RTMEDIA_PHOTO_TAGGING_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.2.14' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_FFMPEG_PATH' ) ) {
				// check for rtMedia FFPMEG version.
				$plugin_info = get_plugin_data( RTMEDIA_FFMPEG_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.1.14' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_KALTURA_PATH' ) ) {
				// check for rtMedia Kaltura version.
				$plugin_info = get_plugin_data( RTMEDIA_KALTURA_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '3.0.16' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_PRO_PATH' ) ) {
				// check for rtMedia Pro version.
				$plugin_info = get_plugin_data( RTMEDIA_PRO_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.6' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_SOCIAL_SYNC_PATH' ) ) {
				// check for rtMedia Social Sync version.
				$plugin_info = get_plugin_data( RTMEDIA_SOCIAL_SYNC_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '1.3.1' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_MEMBERSHIP_PATH' ) ) {
				// check for rtMedia Membership version.
				$plugin_info = get_plugin_data( RTMEDIA_MEMBERSHIP_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.1.5' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_WATERMARK_PATH' ) ) {
				// check for rtMedia Photo Watermak version.
				$plugin_info = get_plugin_data( RTMEDIA_WATERMARK_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '1.1.8' ) ) ) {
					$return_flag = true;
				}
			}

			return $return_flag;
		}

		/**
		 * Show buddypress admin tabs.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function bp_admin_tabs() {
			if ( current_user_can( 'manage_options' ) ) {
				add_action( 'bp_admin_tabs', array( $this, 'tab' ) );
			}
		}

		/**
		 * Show rtMedia advertisement.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function rtmedia_advertisement() {
			$src = RTMEDIA_URL . 'app/assets/admin/img/rtMedia-pro-ad.png'
			?>
			<div class='rtmedia-admin-ad'>
				<a href='https://rtmedia.io/products/rtmedia-pro/' target='_blank' title='rtMedia Pro'>
					<img src='<?php echo esc_url( $src ); ?>' alt="<?php esc_attr_e( 'rtMedia Pro is released', 'buddypress-media' ); ?>"/>
				</a>
			</div>
			<?php
		}

		/**
		 * Create the function to output the contents of our Dashboard Widget
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function rtmedia_dashboard_widget_function() {
			?>

			<div class="clearfix">

				<div class="rtm-column alignleft">
					<h4 class="sub"><?php esc_html_e( 'Media Stats', 'buddypress-media' ); ?></h4>

					<table>
						<tbody>
						<?php
						$rtmedia_model = new RTMediaModel();
						global $wpdb;
						$results = wp_cache_get( 'rt-stats', 'rt-dashboard' );
						if ( false === $results ) {
							$results = $wpdb->get_results( $wpdb->prepare( "select media_type, count(id) as count from {$rtmedia_model->table_name} where blog_id=%d group by media_type", get_current_blog_id() ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
							wp_cache_set( 'stats', $results, 'rt-dashboard', HOUR_IN_SECONDS );
						}
						if ( $results ) {
							foreach ( $results as $media ) {
								if ( defined( strtoupper( 'RTMEDIA_' . $media->media_type . '_PLURAL_LABEL' ) ) ) {
									?>
									<tr>
										<td class="b"> <?php echo esc_html( $media->count ); ?> </td>
										<td class="t"><?php echo esc_html( constant( strtoupper( 'RTMEDIA_' . $media->media_type . '_PLURAL_LABEL' ) ) ); ?></td>
									</tr>
									<?php
								}
							}
						}
						?>
						</tbody>
					</table>
				</div>

				<div class="rtm-column alignright">
					<h4 class="sub"><?php esc_html_e( 'Usage Stats', 'buddypress-media' ); ?></h4>

					<table>
						<tbody>
						<?php
						$total_count = wp_cache_get( 'total_count', 'rt-dashboard' );
						if ( false === $total_count ) {
							$total_count = $wpdb->get_var( "select count(*) from {$wpdb->users}" );
							wp_cache_set( 'total_count', $total_count, 'rt-dashboard', HOUR_IN_SECONDS );
						}
						?>
						<tr>
							<td class="b"> <?php echo esc_html( $total_count ); ?> </td>
							<td class="t"><?php esc_html_e( 'Total ', 'buddypress-media' ); ?></td>
						</tr>
						<?php
						$with_media_count = wp_cache_get( 'with_media', 'rt-dashboard' );
						if ( false === $with_media_count ) {
							$with_media_count = $wpdb->get_var( "select count(distinct media_author) from {$rtmedia_model->table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
							wp_cache_set( 'with_media', $with_media_count, 'rt-dashboard', HOUR_IN_SECONDS );
						}
						?>
						<tr>
							<td class="b"> <?php echo esc_html( $with_media_count ); ?> </td>
							<td class="t"><?php esc_html_e( 'With Media', 'buddypress-media' ); ?></td>
						</tr>
						<?php
						$comments = wp_cache_get( 'comments', 'rt-dashboard' );
						if ( false === $comments ) {
							$comments = $wpdb->get_var( "select count(*) from {$wpdb->comments} where comment_post_ID in ( select media_id from {$rtmedia_model->table_name} )" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
							wp_cache_set( 'comments', $comments, 'rt-dashboard', HOUR_IN_SECONDS );
						}
						?>
						<tr>
							<td class="b"> <?php echo esc_html( $comments ); ?> </td>
							<td class="t"><?php esc_html_e( 'Comments ', 'buddypress-media' ); ?></td>
						</tr>
						<?php
						$likes = wp_cache_get( 'likes', 'rt-dashboard' );
						if ( false === $likes ) {
							$likes = $wpdb->get_var( "select sum(likes) from {$rtmedia_model->table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
							wp_cache_set( 'likes', $likes, 'rt-dashboard', HOUR_IN_SECONDS );
						}
						?>
						<tr>
							<td class="b"> <?php echo esc_html( $likes ); ?> </td>
							<td class="t"><?php esc_html_e( 'Likes', 'buddypress-media' ); ?></td>
						</tr>
						</tbody>
					</table>
				</div>

			</div>

			<div class="rtm-meta-container">
				<ul class="rtm-meta-links">
					<li><b><?php esc_html_e( 'rtMedia Links:', 'buddypress-media' ); ?></b></li>
					<li><a href="https://rtmedia.io/"><?php esc_html_e( 'Homepage', 'buddypress-media' ); ?></a></li>
					<li>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=rtmedia-support#rtmedia-general' ) ); ?>"><?php esc_html_e( 'Free Support', 'buddypress-media' ); ?></a>
					</li>
					<li>
						<a href="https://rtmedia.io/products/category/plugins/"><?php esc_html_e( 'Premium Addons', 'buddypress-media' ); ?></a>
					</li>
				</ul>
			</div>
			<?php
		}

		/**
		 * Create the function use in the action hook
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function add_dashboard_widgets() {
			wp_add_dashboard_widget(
				'rtmedia_dashboard_widget',
				esc_html__( 'Right Now in rtMedia', 'buddypress-media' ),
				array(
					&$this,
					'rtmedia_dashboard_widget_function',
				)
			);
			global $wp_meta_boxes;

			// Get the regular dashboard widgets array
			// (which has our new widget already but at the end).
			$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

			// Backup and delete our new dashboard widget from the end of the array.
			$example_widget_backup = array( 'rtmedia_dashboard_widget' => $normal_dashboard['rtmedia_dashboard_widget'] );
			unset( $normal_dashboard['rtmedia_dashboard_widget'] );

			// Merge the two arrays together so our widget is at the beginning.
			$sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );

			// Save the sorted array back into the original metaboxes.
			$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
		}

		/**
		 * Add the plugin settings links
		 *
		 * @access public
		 *
		 * @param  array $links Existing links array.
		 *
		 * @return array $links
		 */
		public function plugin_add_settings_link( $links ) {
			$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=rtmedia-settings' ) ) . '">' . esc_html__( 'Settings', 'buddypress-media' ) . '</a>';
			array_push( $links, $settings_link );
			$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=rtmedia-support' ) ) . '">' . esc_html__( 'Support', 'buddypress-media' ) . '</a>';
			array_push( $links, $settings_link );

			return $links;
		}

		/**
		 * Add admin bar menu
		 *
		 * @access public
		 *
		 * @param  object $admin_bar Admin bar object.
		 *
		 * @return void
		 */
		public function admin_bar_menu( $admin_bar ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$admin_bar->add_menu(
				array(
					'id'    => 'rtMedia',
					'title' => 'rtMedia',
					'href'  => admin_url( 'admin.php?page=rtmedia-settings' ),
					'meta'  => array(
						'title' => esc_html__( 'rtMedia', 'buddypress-media' ),
					),
				)
			);
			$admin_bar->add_menu(
				array(
					'id'     => 'rt-media-dashborad',
					'parent' => 'rtMedia',
					'title'  => esc_html__( 'Settings', 'buddypress-media' ),
					'href'   => admin_url( 'admin.php?page=rtmedia-settings' ),
					'meta'   => array(
						'title'  => esc_html__( 'Settings', 'buddypress-media' ),
						'target' => '_self',
					),
				)
			);
			if ( ! is_rtmedia_vip_plugin() ) {
				$admin_bar->add_menu(
					array(
						'id'     => 'rt-media-addons',
						'parent' => 'rtMedia',
						'title'  => esc_html__( 'Addons', 'buddypress-media' ),
						'href'   => admin_url( 'admin.php?page=rtmedia-addons' ),
						'meta'   => array(
							'title'  => esc_html__( 'Addons', 'buddypress-media' ),
							'target' => '_self',
						),
					)
				);
			}
			$admin_bar->add_menu(
				array(
					'id'     => 'rt-media-support',
					'parent' => 'rtMedia',
					'title'  => esc_html__( 'Support', 'buddypress-media' ),
					'href'   => admin_url( 'admin.php?page=rtmedia-support' ),
					'meta'   => array(
						'title'  => esc_html__( 'Support', 'buddypress-media' ),
						'target' => '_self',
					),
				)
			);
			if ( ! is_rtmedia_vip_plugin() ) {
				$admin_bar->add_menu(
					array(
						'id'     => 'rt-media-themes',
						'parent' => 'rtMedia',
						'title'  => esc_html__( 'Themes', 'buddypress-media' ),
						'href'   => admin_url( 'admin.php?page=rtmedia-themes' ),
						'meta'   => array(
							'title'  => esc_html__( 'Themes', 'buddypress-media' ),
							'target' => '_self',
						),
					)
				);
			}
			if ( ! is_rtmedia_vip_plugin() ) {
				$admin_bar->add_menu(
					array(
						'id'     => 'rt-media-hire-us',
						'parent' => 'rtMedia',
						'title'  => esc_html__( 'Hire Us', 'buddypress-media' ),
						'href'   => admin_url( 'admin.php?page=rtmedia-hire-us' ),
						'meta'   => array(
							'title'  => esc_html__( 'Hire Us', 'buddypress-media' ),
							'target' => '_self',
						),
					)
				);
			}
			if ( has_filter( 'rtmedia_license_tabs' ) || has_action( 'rtmedia_addon_license_details' ) ) {
				$admin_bar->add_menu(
					array(
						'id'     => 'rt-media-license',
						'parent' => 'rtMedia',
						'title'  => esc_html__( 'Licenses', 'buddypress-media' ),
						'href'   => admin_url( 'admin.php?page=rtmedia-license' ),
						'meta'   => array(
							'title'  => esc_html__( 'Licenses', 'buddypress-media' ),
							'target' => '_self',
						),
					)
				);
			}
		}

		/**
		 * Generates the Admin UI.
		 *
		 * @access public
		 *
		 * @param  string $hook Hook name.
		 *
		 * @return void
		 */
		public function ui( $hook ) {
			$admin_pages = array(
				'rtmedia_page_rtmedia-migration',
				'rtmedia_page_rtmedia-kaltura-settings',
				'rtmedia_page_rtmedia-ffmpeg-settings',
				'toplevel_page_rtmedia-settings',
				'rtmedia_page_rtmedia-addons',
				'rtmedia_page_rtmedia-support',
				'rtmedia_page_rtmedia-themes',
				'rtmedia_page_rtmedia-hire-us',
				'rtmedia_page_rtmedia-importer',
				'rtmedia_page_rtmedia-regenerate',
			);

			if ( has_filter( 'rtmedia_license_tabs' ) || has_action( 'rtmedia_addon_license_details' ) ) {
				$admin_pages[] = 'rtmedia_page_rtmedia-license';
			}

			$admin_pages = apply_filters( 'rtmedia_filter_admin_pages_array', $admin_pages );
			$suffix      = ( function_exists( 'rtm_get_script_style_suffix' ) ) ? rtm_get_script_style_suffix() : '.min';

			if ( in_array( $hook, $admin_pages, true ) || strpos( $hook, 'rtmedia-migration' ) ) {

				$admin_ajax = admin_url( 'admin-ajax.php' );

				/* Only one JS file should enqueue */
				if ( '' === $suffix ) {
					wp_enqueue_script( 'rtmedia-admin-tabs', RTMEDIA_URL . 'app/assets/admin/js/vendors/tabs.js', array( 'backbone' ), RTMEDIA_VERSION, true );
					wp_enqueue_script( 'rtmedia-admin-scripts', RTMEDIA_URL . 'app/assets/admin/js/scripts.js', array( 'backbone' ), RTMEDIA_VERSION, true );
					wp_enqueue_script( 'rtmedia-admin', RTMEDIA_URL . 'app/assets/admin/js/settings.js', array( 'backbone', 'wp-util' ), RTMEDIA_VERSION, true );
				} else {
					wp_enqueue_script( 'rtmedia-admin', RTMEDIA_URL . 'app/assets/admin/js/admin.min.js', array( 'backbone', 'wp-util' ), RTMEDIA_VERSION, true );
				}

				wp_localize_script(
					'rtmedia-admin',
					'RTMedia_Admin_Settings_JS',
					array(
						'rtmedia_default_sizes_error_message' => esc_html__( 'Invalid value for [default_size_property].', 'buddypress-media' ),
					)
				);

				$rtmedia_admin_strings = array(
					'no_refresh'                    => esc_html__( 'Please do not refresh this page.', 'buddypress-media' ),
					'something_went_wrong'          => esc_html__( 'Something went wrong. Please ', 'buddypress-media' ) . '<a href onclick="location.reload();">' . esc_html__( 'refresh', 'buddypress-media' ) . '</a>' . esc_html__( ' page.', 'buddypress-media' ),
					'are_you_sure'                  => esc_html__( 'This will subscribe you to the free plan.', 'buddypress-media' ),
					'disable_encoding'              => esc_html__( 'Are you sure you want to disable the encoding service?', 'buddypress-media' ),
					'enable_encoding'               => esc_html__( 'Are you sure you want to enable the encoding service?', 'buddypress-media' ),
					'settings_changed'              => esc_html__( 'Settings have changed, you should save them!', 'buddypress-media' ),
					'video_thumbnail_error'         => esc_html__( 'Number of video thumbnails to be generated should be greater than 0 in media sizes settings. Setting it to default value 2.', 'buddypress-media' ),
					'video_thumbnail_invalid_value' => esc_html__( 'Invalid value for number of video thumbnails in media sizes settings. Setting it to round value', 'buddypress-media' ),
					'jpeg_quality_negative_error'   => esc_html__( 'Number of percentage in JPEG image quality should be greater than 0 in media sizes settings. Setting it to default value 90.', 'buddypress-media' ),
					'jpeg_quality_percentage_error' => esc_html__( 'Number of percentage in JPEG image quality should be less than 100 in media sizes settings. Setting it to 100.', 'buddypress-media' ),
					'jpeg_quality_invalid_value'    => esc_html__( 'Invalid value for percentage in JPEG image quality in media sizes settings. Setting it to round value', 'buddypress-media' ),
					'per_page_media_negative_value' => esc_html__( 'Please enter positive integer value only. Setting number of media per page value to default value 10.', 'buddypress-media' ),
					'per_page_media_positive_error' => esc_html__( 'Please enter positive integer value only. Setting number of media per page value to round value', 'buddypress-media' ),
					'request_failed'                => esc_html__( 'Request failed.', 'buddypress-media' ),
					'wrong_css_input'               => esc_html__( 'You can not use @import statement in custom css', 'buddypress-media' ),
				);

				wp_localize_script(
					'rtmedia-admin',
					'rtmedia_admin',
					array(
						'rtmedia_on_label'             => __( 'ON', 'buddypress-media' ),
						'rtmedia_off_label'            => __( 'OFF', 'buddypress-media' ),
						'rtmedia_admin_ajax'           => $admin_ajax,
						'rtmedia_admin_url'            => admin_url(),
						'rtmedia_fileupload_url'       => RTMEDIA_URL . 'app/helper/rtUploadAttachment.php', /* path for file upload using ajax */
						'settings_url'                 => esc_url( add_query_arg( array( 'page' => 'rtmedia-settings' ), ( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) ) ) ) . '#privacy_enabled',
						'settings_rt_album_import_url' => esc_url( add_query_arg( array( 'page' => 'rtmedia-settings' ), ( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) ) ) ),
						'rtmedia_admin_strings'        => $rtmedia_admin_strings,
					)
				);

				$rtmedia_admin_support_strings = array(
					'wp_admin_username_error' => esc_html__( 'Please enter WP Admin Login.', 'buddypress-media' ),
					'wp_admin_pwd_error'      => esc_html__( 'Please enter WP Admin password.', 'buddypress-media' ),
					'ssh_ftp_host_error'      => esc_html__( 'Please enter SSH / FTP host.', 'buddypress-media' ),
					'ssh_ftp_username_error'  => esc_html__( 'Please enter SSH / FTP login.', 'buddypress-media' ),
					'ssh_ftp_pwd_error'       => esc_html__( 'Please enter SSH / FTP password.', 'buddypress-media' ),
					'all_fields_error'        => esc_html__( 'Please fill all the fields.', 'buddypress-media' ),
				);

				wp_localize_script( 'rtmedia-admin', 'rtmedia_admin_support_strings', $rtmedia_admin_support_strings );

				// Only one CSS file should enqueue.
				wp_enqueue_style( 'rtmedia-admin', RTMEDIA_URL . 'app/assets/admin/css/admin' . $suffix . '.css', '', RTMEDIA_VERSION );
			} else {

				// This CSS is using for "Right Now in rtMedia" Widget section on Dashboard.
				wp_enqueue_style( 'rtmedia-widget', RTMEDIA_URL . 'app/assets/admin/css/widget' . $suffix . '.css', '', RTMEDIA_VERSION );
			}
		}

		/**
		 * Add Admin Menu.
		 *
		 * @access public
		 * @global string 'buddypress-media'
		 *
		 * @return void
		 */
		public function menu() {
			add_menu_page(
				'rtMedia',
				'rtMedia',
				'manage_options',
				'rtmedia-settings',
				array(
					$this,
					'settings_page',
				),
				RTMEDIA_URL . 'app/assets/admin/img/rtmedia-logo.png',
				'40.1111'
			);

			add_submenu_page(
				'rtmedia-settings',
				esc_html__( 'Settings', 'buddypress-media' ),
				esc_html__( 'Settings', 'buddypress-media' ),
				'manage_options',
				'rtmedia-settings',
				array(
					$this,
					'settings_page',
				)
			);

			add_submenu_page(
				'rtmedia-settings',
				esc_html__( 'Addons', 'buddypress-media' ),
				esc_html__( 'Addons', 'buddypress-media' ),
				'manage_options',
				'rtmedia-addons',
				array(
					$this,
					'addons_page',
				)
			);

			add_submenu_page(
				'rtmedia-settings',
				esc_html__( 'Support', 'buddypress-media' ),
				esc_html__( 'Support', 'buddypress-media' ),
				'manage_options',
				'rtmedia-support',
				array(
					$this,
					'support_page',
				)
			);

			if ( ! is_rtmedia_vip_plugin() ) {
				add_submenu_page(
					'rtmedia-settings',
					esc_html__( 'Themes', 'buddypress-media' ),
					esc_html__( 'Themes', 'buddypress-media' ),
					'manage_options',
					'rtmedia-themes',
					array(
						$this,
						'theme_page',
					)
				);
			}

			if ( ! is_rtmedia_vip_plugin() ) {
				add_submenu_page(
					'rtmedia-settings',
					esc_html__( 'Hire Us', 'buddypress-media' ),
					esc_html__( 'Hire Us', 'buddypress-media' ),
					'manage_options',
					'rtmedia-hire-us',
					array(
						$this,
						'hire_us_page',
					)
				);
			}

			if ( has_filter( 'rtmedia_license_tabs' ) || has_action( 'rtmedia_addon_license_details' ) ) {
				add_submenu_page(
					'rtmedia-settings',
					esc_html__( 'Licenses', 'buddypress-media' ),
					esc_html__( 'Licenses', 'buddypress-media' ),
					'manage_options',
					'rtmedia-license',
					array(
						$this,
						'license_page',
					)
				);
			}

		}

		/**
		 * Hide rtMedia addon update notice.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function rtmedia_hide_addon_update_notice() {
			if ( check_ajax_referer( 'rtmedia-addon-update-notice-3_8', '_rtm_nonce' ) && rtmedia_update_site_option( 'rtmedia-addon-update-notice-3_8', 'hide' ) ) {
				echo '1';
			} else {
				echo '0';
			}
			die();
		}

		/**
		 * Render the BuddyPress Media Settings page.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function settings_page() {
			$this->render_page( 'rtmedia-settings', 'buddypress-media' );
		}

		/**
		 * Render the BuddyPress Privacy Settings page.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function privacy_page() {
			$this->render_page( 'rtmedia-privacy' );
		}

		/**
		 * Render the rtmedia Importer Page.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function rt_importer_page() {
			$this->render_page( 'rtmedia-importer' );
		}

		/**
		 * Render the rtmedia convert videos page.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function convert_videos_page() {
			$this->render_page( 'rtmedia-convert-videos' );
		}

		/**
		 * Render the BuddyPress Media Addons page.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function addons_page() {
			$this->render_page( 'rtmedia-addons' );
		}

		/**
		 * Render the BuddyPress Media Support page.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function support_page() {
			$this->render_page( 'rtmedia-support' );
		}

		/**
		 * Render the rtmedia theme page.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function theme_page() {
			$this->render_page( 'rtmedia-themes' );
		}

		/**
		 * Render the rtmedia hire us page.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function hire_us_page() {
			$this->render_page( 'rtmedia-hire-us' );
		}

		/**
		 * Render license page.
		 */
		public function license_page() {
			$this->render_page( 'rtmedia-license' );
		}

		/**
		 * Render the rtmedia hire us page.
		 *
		 * @access static
		 *
		 * @return string | array
		 */
		public static function get_current_tab() {
			$page_name = sanitize_text_field( filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ) );
			return isset( $page_name ) ? $page_name : 'rtmedia-settings';
		}

		/**
		 * Render BPMedia Settings.
		 *
		 * @access public
		 * @global      string 'buddypress-media'
		 *
		 * @param  string $page_name page name to render.
		 * @param  array  $option_group option group.
		 *
		 * @return void
		 */
		public function render_page( $page_name, $option_group = null ) {
			?>

			<div class="wrap bp-media-admin <?php echo esc_attr( $this->get_current_tab() ); ?>">
				<div id="icon-buddypress-media" class="icon32"><br></div>
				<div>
					<h2 class="nav-tab-wrapper"><?php $this->rtmedia_tabs(); ?>
						<span class="alignright by">
							<a class="rt-link"
								href="https://rtmedia.io/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media"
								target="_blank"
								title="rtCamp : <?php esc_attr_e( 'Empowering The Web With WordPress', 'buddypress-media' ); ?>">
								<img src="<?php echo esc_url( RTMEDIA_URL ); ?>app/assets/admin/img/rtcamp-logo.png" alt="rtCamp"/>
							</a>
						</span>
					</h2>
				</div>

				<div class="clearfix rtm-row-container">

					<div id="bp-media-settings-boxes" class="bp-media-settings-boxes-container rtm-setting-container">

						<?php
						if ( 'rtmedia-settings' === $page_name ) {
							?>
							<form id="bp_media_settings_form" name="bp_media_settings_form" method="post" enctype="multipart/form-data">
							<div class="bp-media-metabox-holder">
								<div class="rtm-button-container top">
									<?php
									$is_setting_save = filter_input( INPUT_GET, 'settings-saved', FILTER_VALIDATE_BOOLEAN );
									if ( ! empty( $is_setting_save ) ) {
										?>
										<div class="rtm-success rtm-fly-warning rtm-save-settings-msg">
											<?php esc_html_e( 'Settings saved successfully!', 'buddypress-media' ); ?>
										</div>
									<?php } ?>
									<input type="hidden" name="rtmedia-options-save" value="true">
									<input type="submit" class="rtmedia-settings-submit button button-primary button-big" value="<?php esc_attr_e( 'Save Settings', 'buddypress-media' ); ?>">
								</div>
								<?php
								settings_fields( $option_group );
								if ( 'rtmedia-settings' === $page_name ) {
									echo '<div id="rtm-settings-tabs">';
									$sub_tabs = $this->settings_sub_tabs();
									RTMediaFormHandler::rtForm_settings_tabs_content( $page_name, $sub_tabs );
									echo '</div>';
								} else {
									do_settings_sections( $page_name );
								}
								?>

								<div class="rtm-button-container bottom">
									<div class="rtm-social-links alignleft">
										<a href="http://twitter.com/rtMediaWP" class="twitter" target="_blank">
											<span class="dashicons dashicons-twitter"></span>
										</a>
										<a href="https://www.facebook.com/rtmediawp" class="facebook" target="_blank">
											<span class="dashicons dashicons-facebook"></span>
										</a>
										<a href="http://profiles.wordpress.org/rtcamp" class="wordpress" target="_blank">
											<span class="dashicons dashicons-wordpress"></span>
										</a>
										<a href="https://rtmedia.io/feed/" class="rss" target="_blank">
											<span class="dashicons dashicons-rss"></span>
										</a>
									</div>

									<input type="hidden" name="rtmedia-options-save" value="true">
									<input type="submit" class="rtmedia-settings-submit button button-primary button-big" value="<?php esc_attr_e( 'Save Settings', 'buddypress-media' ); ?>">
								</div>
							</div>
							</form>
							<?php
						} else {
							?>
							<div class="bp-media-metabox-holder">
								<?php
								if ( 'rtmedia-addons' === $page_name ) {
									RTMediaAddon::render_addons( $page_name );
								} elseif ( 'rtmedia-support' === $page_name ) {
									$rtmedia_support = new RTMediaSupport( false );
									$rtmedia_support->render_support( $page_name );
								} elseif ( 'rtmedia-themes' === $page_name ) {
									RTMediaThemes::render_themes( $page_name );
								} else {
									if ( 'rtmedia-license' === $page_name ) {
										RTMediaLicense::render_license( $page_name );
									} else {
										do_settings_sections( $page_name );
									}
								}
								do_action( 'rtmedia_admin_page_insert', $page_name );
								?>
							</div>
							<?php
							do_action( 'rtmedia_admin_page_append', $page_name );
						}
						?>
					</div>

					<div class="metabox-holder bp-media-metabox-holder rtm-sidebar">
						<?php $this->admin_sidebar(); ?>
					</div>

				</div>

			</div><!-- .bp-media-admin -->
			<?php
		}

		/**
		 * Adds a tab for Media settings in the BuddyPress settings page
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function tab() {

			$idle_class   = 'nav-tab';
			$active_class = 'nav-tab nav-tab-active';
			$tabs         = array();

			// Check to see which tab we are on.
			$tab = $this->get_current_tab();
			/* rtMedia */
			$tabs[] = array(
				'href'  => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-settings' ), 'admin.php' ) ) ),
				'title' => esc_html__( 'rtMedia', 'buddypress-media' ),
				'name'  => esc_html__( 'rtMedia', 'buddypress-media' ),
				'class' => ( 'rtmedia-settings' === $tab || 'rtmedia-addons' === $tab || 'rtmedia-support' === $tab || 'rtmedia-importer' === $tab ) ? $active_class : $idle_class,
			);

			foreach ( $tabs as $tab ) {
				echo '<a id="bp-media" title= "' . esc_attr( $tab['title'] ) . '"  href="' . esc_url( $tab['href'] ) . '" class="' . esc_attr( $tab['class'] ) . '">' . esc_html( $tab['name'] ) . '</a>';
			}
		}

		/**
		 * Create core admin tabs.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function rtmedia_tabs() {
			// Declare local variables.
			$idle_class   = 'nav-tab';
			$active_class = 'nav-tab nav-tab-active';

			// Setup core admin tabs.
			$tabs = array(
				array(
					'href' => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-settings' ), 'admin.php' ) ) ),
					'name' => esc_html__( 'Settings', 'buddypress-media' ),
					'slug' => 'rtmedia-settings',
				),
				array(
					'href' => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-addons' ), 'admin.php' ) ) ),
					'name' => esc_html__( 'Addons', 'buddypress-media' ),
					'slug' => 'rtmedia-addons',
				),
			);

			if ( ! is_rtmedia_vip_plugin() ) {
				$tabs[] = array(
					'href' => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-themes' ), 'admin.php' ) ) ),
					'name' => esc_html__( 'Themes', 'buddypress-media' ),
					'slug' => 'rtmedia-themes',
				);

				$tabs[] = array(
					'href' => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-hire-us' ), 'admin.php' ) ) ),
					'name' => esc_html__( 'Hire Us', 'buddypress-media' ),
					'slug' => 'rtmedia-hire-us',
				);
			}

			$tabs[] = array(
				'href' => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-support' ), 'admin.php' ) ) ),
				'name' => esc_html__( 'Support', 'buddypress-media' ),
				'slug' => 'rtmedia-support',
			);

			if ( has_filter( 'rtmedia_license_tabs' ) || has_action( 'rtmedia_addon_license_details' ) ) {
				$tabs[] = array(
					'href' => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-license' ), 'admin.php' ) ) ),
					'name' => esc_html__( 'Licenses', 'buddypress-media' ),
					'slug' => 'rtmedia-license',
				);
			}

			$tabs = apply_filters( 'media_add_tabs', $tabs );

			// Loop through tabs and build navigation.
			foreach ( array_values( $tabs ) as $tab_data ) {
				$is_current = (bool) ( $tab_data['slug'] === $this->get_current_tab() );
				$tab_class  = $is_current ? $active_class : $idle_class;

				if ( isset( $tab_data['class'] ) && is_array( $tab_data['class'] ) ) {
					$tab_class .= ' ' . implode( ' ', $tab_data['class'] );
				}

				echo '<a href="' . esc_url( $tab_data['href'] ) . '" class="' . esc_attr( $tab_class ) . '">' . esc_html( $tab_data['name'] ) . '</a>';
			}
		}

		/**
		 * Create settings content tabs.
		 *
		 * @access public
		 *
		 * @param  string $page_name page name.
		 *
		 * @return void
		 */
		public function settings_content_tabs( $page_name ) {
			global $wp_settings_sections, $wp_settings_fields;

			if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[ $page_name ] ) ) {
				return;
			}

			foreach ( (array) $wp_settings_sections[ $page_name ] as $section ) {
				if ( $section['title'] ) {
					?>
					<h3><?php esc_html( $section['title'] ); ?></h3>
					<?php
				}

				if ( $section['callback'] ) {
					call_user_func( $section['callback'], $section );
				}

				if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page_name ] ) || ! isset( $wp_settings_fields[ $page_name ][ $section['id'] ] ) ) {
					continue;
				}

				echo '<table class="form-table">';
				do_settings_fields( $page_name, $section['id'] );
				echo '</table>';
			}
		}

		/**
		 * Adds a sub tabs to the BuddyPress Media settings page
		 *
		 * @access public
		 *
		 * @return array $tabs
		 */
		public function settings_sub_tabs() {
			$tabs = array();

			// Check to see which tab we are on.
			$tab = $this->get_current_tab();
			/* rtMedia */

			$tabs[7] = array(
				'href'     => '#rtmedia-display',
				'icon'     => 'dashicons-desktop',
				'title'    => esc_html__( 'Display', 'buddypress-media' ),
				'name'     => esc_html__( 'Display', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'display_content' ),
			);

			if ( class_exists( 'BuddyPress' ) ) {
				$tabs[20] = array(
					'href'     => '#rtmedia-bp',
					'icon'     => 'dashicons-groups',
					'title'    => esc_html__( 'rtMedia BuddyPress', 'buddypress-media' ),
					'name'     => esc_html__( 'BuddyPress', 'buddypress-media' ),
					'callback' => array( 'RTMediaFormHandler', 'buddypress_content' ), // change it to BuddyPress Content.
				);
			}

			$tabs[30] = array(
				'href'     => '#rtmedia-types',
				'icon'     => 'dashicons-editor-video',
				'title'    => esc_html__( 'rtMedia Types', 'buddypress-media' ),
				'name'     => esc_html__( 'Types', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'types_content' ),
			);

			$tabs[40] = array(
				'href'     => '#rtmedia-sizes',
				'icon'     => 'dashicons-editor-expand',
				'title'    => esc_html__( 'rtMedia Sizes', 'buddypress-media' ),
				'name'     => esc_html__( 'Media Sizes', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'sizes_content' ),
			);

			$tabs[50] = array(
				'href'     => '#rtmedia-privacy',
				'icon'     => 'dashicons-lock',
				'title'    => esc_html__( 'rtMedia Privacy', 'buddypress-media' ),
				'name'     => esc_html__( 'Privacy', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'privacy_content' ),
			);
			$tabs[60] = array(
				'href'     => '#rtmedia-custom-css-settings',
				'icon'     => 'dashicons-clipboard',
				'title'    => esc_html__( 'rtMedia Custom CSS', 'buddypress-media' ),
				'name'     => esc_html__( 'Custom CSS', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'custom_css_content' ),
			);

			$tabs = apply_filters( 'rtmedia_add_settings_sub_tabs', $tabs, $tab );

			$tabs[] = array(
				'href'     => '#rtmedia-general',
				'icon'     => 'dashicons-admin-tools',
				'title'    => esc_html__( 'Other Settings', 'buddypress-media' ),
				'name'     => esc_html__( 'Other Settings', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'general_content' ),
			);
			// adds export/import tab in rtMedia Settings.
			$tabs[] = array(
				'href'     => '#rtmedia-export-import',
				'icon'     => 'dashicons-image-flip-vertical',
				'title'    => esc_html__( 'Export/Import', 'buddypress-media' ),
				'name'     => esc_html__( 'Export/Import', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'rtm_export_import' ),
			);

			return $tabs;
		}

		/**
		 * Multisite Save Options - http://wordpress.stackexchange.com/questions/64968/settings-api-in-multisite-missing-update-message#answer-72503
		 *
		 * @access public
		 * @global type $rtmedia_admin
		 *
		 * @return void
		 */
		public function save_multisite_options() {
			global $rtmedia_admin;
			do_action( 'rtmedia_sanitize_settings', wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification

			$rtmedia_options = filter_input( INPUT_POST, 'rtmedia_options' );
			if ( isset( $rtmedia_options ) ) {
				// todo: How we can sanitize array?
				rtmedia_update_site_option( 'rtmedia_options', $rtmedia_options );

				// redirect to settings page in network.
				wp_safe_redirect(
					esc_url_raw(
						add_query_arg(
							array(
								'page'    => 'rtmedia-settings',
								'updated' => 'true',
							),
							( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) )
						)
					)
				);
				exit;
			}
		}

		/**
		 * Admin Sidebar
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function admin_sidebar() {
			do_action( 'rtmedia_before_default_admin_widgets' );
			$current_user = wp_get_current_user();
			// translators: 1. Home url.
			$message = sprintf( esc_html__( 'I use @rtMediaWP http://rt.cx/rtmedia on %s', 'buddypress-media' ), home_url() );
			$addons  = '<div id="social" class="rtm-social-share">
						<p><a href="http://twitter.com/home/?status=' . esc_attr( $message ) . '" class="button twitter" target= "_blank" title="' . esc_attr__( 'Post to Twitter Now', 'buddypress-media' ) . '">' . esc_html__( 'Post to Twitter', 'buddypress-media' ) . '<span class="dashicons dashicons-twitter"></span></a></p>
						<p><a href="https://www.facebook.com/sharer/sharer.php?u=https://rtmedia.io/" class="button facebook" target="_blank" title="' . esc_attr__( 'Share on Facebook Now', 'buddypress-media' ) . '">' . esc_html__( 'Share on Facebook', 'buddypress-media' ) . '<span class="dashicons dashicons-facebook"></span></a></p>
						<p><a href="https://wordpress.org/support/plugin/buddypress-media/reviews/#new-post" class="button wordpress" target= "_blank" title="' . esc_attr__( 'Rate rtMedia on Wordpress.org', 'buddypress-media' ) . '">' . esc_html__( 'Rate on Wordpress.org', 'buddypress-media' ) . '<span class="dashicons dashicons-wordpress"></span></a></p>
						<p><a href="' . sprintf( '%s', 'https://rtmedia.io/feed/' ) . '" class="button rss" target="_blank" title="' . esc_attr__( 'Subscribe to our Feeds', 'buddypress-media' ) . '">' . esc_html__( 'Subscribe to our Feeds', 'buddypress-media' ) . '<span class="dashicons dashicons-rss"></span></a></p>
						</div>';

			new RTMediaAdminWidget( 'spread-the-word', esc_html__( 'Spread the Word', 'buddypress-media' ), $addons );

			$branding = '<form action="http://rtcamp.us1.list-manage1.com/subscribe/post?u=85b65c9c71e2ba3fab8cb1950&amp;id=9e8ded4470" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
							<div class="mc-field-group">
								<input type="email" value="' . esc_attr( $current_user->user_email ) . '" name="EMAIL" placeholder="Email" class="required email" id="mce-EMAIL">
								<input style="display:none;" type="checkbox" checked="checked" value="1" name="group[1721][1]" id="mce-group[1721]-1721-0">
								<input type="submit" value="' . esc_attr__( 'Subscribe', 'buddypress-media' ) . '" name="subscribe" id="mc-embedded-subscribe" class="button">
								<div id="mce-responses" class="clear">
									<div class="response" id="mce-error-response" style="display:none"></div>
									<div class="response" id="mce-success-response" style="display:none"></div>
								</div>
							</div>
						</form>';
			new RTMediaAdminWidget( 'branding', esc_html__( 'Subscribe', 'buddypress-media' ), $branding );

			do_action( 'rtmedia_after_default_admin_widgets' );
		}

		/**
		 * Function to save linkback.
		 *
		 * @return mixed
		 */
		public function linkback() {
			// todo: remove code looks like old setting save code new code at app/helper/RTMediaSettings.php.
			$linkback = filter_input( INPUT_POST, 'linkback' );
			if ( isset( $linkback ) && $linkback ) {
				return rtmedia_update_site_option( 'rtmedia-add-linkback', true );
			} else {
				return rtmedia_update_site_option( 'rtmedia-add-linkback', false );
			}
		}

		/**
		 * Export rtMedia Settings
		 *
		 * @access public
		 */
		public function export_settings() {

			$rtmedia_option = get_option( 'rtmedia-options' );

			if ( is_array( $rtmedia_option ) ) {
				$rtmedia_option['rtm_key'] = md5( 'rtmedia-options' );
			}

			wp_send_json( $rtmedia_option );
		}

		/**
		 * Import rtMedia Settings
		 *
		 * @access public
		 *
		 * @param string $file_path path to json file to be imported.
		 */
		public function import_settings( $file_path ) {

			$response = array();

			if ( empty( $file_path ) || validate_file( $file_path ) !== 0 ) {
				$response['rtm_response']     = 'error';
				$response['rtm_response_msg'] = esc_html__( 'Unable to read file!', 'buddypress-media' );
				wp_send_json( $response );
			}

			ob_start();
			include $file_path;

			$settings_data_json = ob_get_clean();
			wp_delete_file( $file_path );

			if ( empty( $settings_data_json ) ) {
				$response['rtm_response']     = 'error';
				$response['rtm_response_msg'] = esc_html__( 'Invalid JSON Supplied!', 'buddypress-media' );
				wp_send_json( $response );
			}

			$settings_data = json_decode( $settings_data_json, true );
			if ( ! is_array( $settings_data ) || empty( $settings_data['rtm_key'] ) ) {
				$response['rtm_response']     = 'error';
				$response['rtm_response_msg'] = esc_html__( 'Invalid JSON Supplied!', 'buddypress-media' );
				wp_send_json( $response );
			}

			if ( md5( 'rtmedia-options' ) !== $settings_data['rtm_key'] ) {
				$response['rtm_response']     = 'error';
				$response['rtm_response_msg'] = esc_html__( 'Invalid JSON Supplied. The JSON you supplied is not exported from rtMedia!', 'buddypress-media' );
				wp_send_json( $response );
			}

			unset( $settings_data['rtm_key'] );
			$new_value = wp_json_encode( $settings_data );
			$old_value = wp_json_encode( get_option( 'rtmedia-options' ) );

			if ( $new_value === $old_value ) {
				$response['rtm_response']     = 'error';
				$response['rtm_response_msg'] = esc_html__( 'Data passed for settings is unchanged!', 'buddypress-media' );
			} else {
				if ( update_option( 'rtmedia-options', $settings_data ) ) {
					$response['rtm_response']     = 'success';
					$response['rtm_response_msg'] = esc_html__( 'rtMedia Settings imported successfully!', 'buddypress-media' );
				} else {
					$response['rtm_response']     = 'error';
					$response['rtm_response_msg'] = esc_html__( 'Could not update rtMedia Settings', 'buddypress-media' );
				}
			}

			wp_send_json( $response );
		}

		/**
		 * Ajax callback function Convert videos mailchimp.
		 */
		public function convert_videos_mailchimp_send() {
			// todo: nonce required.
			$interested = sanitize_text_field( filter_input( INPUT_POST, 'linkback', FILTER_SANITIZE_STRING ) );
			$choice     = sanitize_text_field( filter_input( INPUT_POST, 'choice', FILTER_SANITIZE_STRING ) );
			$url        = filter_input( INPUT_POST, 'url', FILTER_SANITIZE_URL );
			$email      = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_EMAIL );

			if ( 'Yes' === $interested && ! empty( $choice ) ) {
				wp_remote_get(
					esc_url_raw(
						add_query_arg(
							array(
								'rtmedia-convert-videos-form' => 1,
								'choice' => $choice,
								'url'    => $url,
								'email'  => $email,
							),
							'https://rtmedia.io/'
						)
					)
				);
			} else {
				rtmedia_update_site_option( 'rtmedia-survey', 0 );
			}

			esc_html_e( 'Thank you for your time.', 'buddypress-media' );
			wp_die();
		}

		/**
		 * Function to save Video transcoding survey response.
		 */
		public function video_transcoding_survey_response() {
			$survey_done = filter_input( INPUT_GET, 'survey-done', FILTER_SANITIZE_STRING );
			if ( isset( $survey_done ) && ( md5( 'survey-done' ) === $survey_done ) ) {
				rtmedia_update_site_option( 'rtmedia-survey', 0 );
			}
		}

		/**
		 * Premium addon link.
		 *
		 * @param array  $plugin_meta Plugin meta.
		 * @param string $plugin_file Plugin file.
		 *
		 * @return array
		 */
		public function plugin_meta_premium_addon_link( $plugin_meta, $plugin_file ) {
			if ( plugin_basename( RTMEDIA_PATH . 'index.php' ) === $plugin_file ) {
				$plugin_meta[] = '<a href="https://rtmedia.io/products/?utm_source=dashboard&#038;utm_medium=plugin&#038;utm_campaign=buddypress-media" title="' . esc_attr__( 'Premium Add-ons', 'buddypress-media' ) . '">' . esc_html__( 'Premium Add-ons', 'buddypress-media' ) . '</a>';
			}

			return $plugin_meta;
		}

		/**
		 * Show upload file types error.
		 */
		public function upload_filetypes_error() {
			global $rtmedia;
			$upload_filetypes = rtmedia_get_site_option( 'upload_filetypes', 'jpg jpeg png gif' );
			$upload_filetypes = explode( ' ', $upload_filetypes );
			$flag             = false;

			if ( isset( $rtmedia->options['images_enabled'] ) && $rtmedia->options['images_enabled'] ) {
				$not_supported_image = array_diff( array( 'jpg', 'jpeg', 'png', 'gif' ), $upload_filetypes );
				if ( ! empty( $not_supported_image ) ) {
					?>
					<div class="error upload-filetype-network-settings-error">
						<p>
							<?php wp_nonce_field( '_rtm_file_type_error_', 'rtm-file-type-error' ); ?>
							<?php
							// translators: 1. Not supported image types.
							printf( esc_html__( 'You have images enabled on rtMedia but your network allowed filetypes do not permit uploading of %s. Click ', 'buddypress-media' ), esc_html( implode( ', ', $not_supported_image ) ) );
							?>
							<a href='<?php echo esc_url( network_admin_url( 'settings.php#upload_filetypes' ) ); ?>'><?php esc_html_e( 'here', 'buddypress-media' ); ?></a>
							<?php esc_html_e( ' to change your settings manually.', 'buddypress-media' ); ?>
							<br />
							<strong><?php esc_html_e( 'Recommended:', 'buddypress-media' ); ?></strong>
							<input type="button" class="button update-network-settings-upload-filetypes" value="<?php esc_attr_e( 'Update Network Settings Automatically', 'buddypress-media' ); ?>">
							<img style="display:none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" />
						</p>
					</div>
					<?php
					$flag = true;
				}
			}
			if ( isset( $rtmedia->options['videos_enabled'] ) && $rtmedia->options['videos_enabled'] ) {
				if ( ! in_array( 'mp4', $upload_filetypes, true ) ) {
					?>
					<div class="error upload-filetype-network-settings-error">
						<p>
							<?php esc_html_e( 'You have video enabled on BuddyPress Media but your network allowed filetypes do not permit uploading of mp4. Click ', 'buddypress-media' ); ?>
							<a href='<?php echo esc_url( network_admin_url( 'settings.php#upload_filetypes' ) ); ?>'><?php esc_html_e( 'here', 'buddypress-media' ); ?></a>
							<?php esc_html_e( ' to change your settings manually.', 'buddypress-media' ); ?>
							<br />
							<strong><?php esc_html_e( 'Recommended:', 'buddypress-media' ); ?></strong>
							<input type="button" class="button update-network-settings-upload-filetypes" value="<?php esc_attr_e( 'Update Network Settings Automatically', 'buddypress-media' ); ?>">
							<img style="display:none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" />
						</p>
					</div>
					<?php
					$flag = true;
				}
			}
			if ( isset( $rtmedia->options['audio_enabled'] ) && $rtmedia->options['audio_enabled'] ) {
				if ( ! in_array( 'mp3', $upload_filetypes, true ) ) {
					?>
					<div class="error upload-filetype-network-settings-error">
						<p>
							<?php esc_html_e( 'You have audio enabled on BuddyPress Media but your network allowed filetypes do not permit uploading of mp3. Click ', 'buddypress-media' ); ?>
							<a href='<?php echo esc_url( network_admin_url( 'settings.php#upload_filetypes' ) ); ?>'><?php esc_html_e( 'here', 'buddypress-media' ); ?></a>
							<?php esc_html_e( ' to change your settings manually.', 'buddypress-media' ); ?>
							<br />
							<strong><?php esc_html_e( 'Recommended:', 'buddypress-media' ); ?></strong>
							<input type="button" class="button update-network-settings-upload-filetypes" value="<?php esc_attr_e( 'Update Network Settings Automatically', 'buddypress-media' ); ?>">
							<img style="display:none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" />
						</p>
					</div>
					<?php
					$flag = true;
				}
			}
			if ( $flag ) {
				?>
				<script type="text/javascript">
					jQuery('.upload-filetype-network-settings-error').on('click', '.update-network-settings-upload-filetypes', function () {
						jQuery('.update-network-settings-upload-filetypes').siblings('img').show();
						jQuery('.update-network-settings-upload-filetypes').prop('disabled', true);
						jQuery.post(ajaxurl, {action: 'rtmedia_correct_upload_filetypes', _rtm_nonce: jQuery('rtm-file-type-error').val()}, function (response) {
							if (response) {
								jQuery('.upload-filetype-network-settings-error:first').after('<div style="display: none;" class="updated rtmedia-network-settings-updated-successfully"><p><?php esc_html_e( 'Network settings updated successfully.', 'buddypress-media' ); ?></p></div>');
								jQuery('.upload-filetype-network-settings-error').remove();
								jQuery('.bp-media-network-settings-updated-successfully').show();
							}
						});
					});
				</script>
				<?php
			}
		}

		/**
		 * Correct upload filetypes.
		 */
		public function correct_upload_filetypes() {
			if ( ! check_ajax_referer( '_rtm_file_type_error_', '_rtm_nonce' ) ) {
				wp_send_json( false );
			}

			global $rtmedia;
			$upload_filetypes_orig = rtmedia_get_site_option( 'upload_filetypes', 'jpg jpeg png gif' );
			$upload_filetypes      = $upload_filetypes_orig;
			$upload_filetypes      = explode( ' ', $upload_filetypes );

			if ( isset( $rtmedia->options['images_enabled'] ) && $rtmedia->options['images_enabled'] ) {
				$not_supported_image = array_diff( array( 'jpg', 'jpeg', 'png', 'gif' ), $upload_filetypes );
				if ( ! empty( $not_supported_image ) ) {
					$update_image_support = null;
					foreach ( $not_supported_image as $ns ) {
						$update_image_support .= ' ' . $ns;
					}
					if ( $update_image_support ) {
						$upload_filetypes_orig .= $update_image_support;
						rtmedia_update_site_option( 'upload_filetypes', $upload_filetypes_orig );
					}
				}
			}
			if ( isset( $rtmedia->options['videos_enabled'] ) && $rtmedia->options['videos_enabled'] ) {
				if ( ! in_array( 'mp4', $upload_filetypes, true ) ) {
					$upload_filetypes_orig .= ' mp4';
					rtmedia_update_site_option( 'upload_filetypes', $upload_filetypes_orig );
				}
			}
			if ( isset( $rtmedia->options['audio_enabled'] ) && $rtmedia->options['audio_enabled'] ) {
				if ( ! in_array( 'mp3', $upload_filetypes, true ) ) {
					$upload_filetypes_orig .= ' mp3';
					rtmedia_update_site_option( 'upload_filetypes', $upload_filetypes_orig );
				}
			}
			echo true;
			wp_die();
		}

		/**
		 * Update template notice.
		 */
		public function rtmedia_update_template_notice() {
			$site_option = rtmedia_get_site_option( 'rtmedia-update-template-notice-v3_9_4' );

			if ( ! $site_option || 'hide' !== $site_option ) {
				rtmedia_update_site_option( 'rtmedia-update-template-notice-v3_9_4', 'show' );
				if ( is_dir( get_template_directory() . '/rtmedia' ) ) {
					echo '<div class="error rtmedia-update-template-notice"><p>' . esc_html__( 'Please update rtMedia template files if you have overridden the default rtMedia templates in your theme. If not, you can ignore and hide this notice.', 'buddypress-media' ) . '<a href="#" onclick="rtmedia_hide_template_override_notice(\'' . esc_js( wp_create_nonce( 'rtmedia_template_notice' ) ) . '\')" style="float:right">' . esc_html__( 'Hide', 'buddypress-media' ) . '</a></p></div>';
					?>
					<script type="text/javascript">
						function rtmedia_hide_template_override_notice( rtmedia_template_notice_nonce ) {
							var data = {action: 'rtmedia_hide_template_override_notice', _rtm_nonce: rtmedia_template_notice_nonce };
							jQuery.post(ajaxurl, data, function (response) {
								response = response.trim();
								if ('1' === response)
									jQuery('.rtmedia-update-template-notice').remove();
							});
						}
					</script>
					<?php
				}
			}
		}

		/**
		 * Hide template override notice.
		 */
		public function rtmedia_hide_template_override_notice() {

			if ( check_ajax_referer( 'rtmedia_template_notice', '_rtm_nonce' ) && rtmedia_update_site_option( 'rtmedia-update-template-notice-v3_9_4', 'hide' ) ) {
				echo '1';
			} else {
				echo '0';
			}
			die();
		}

		/**
		 * Render Admin UI.
		 *
		 * @param string $page_name Page name.
		 * @param array  $sub_tabs Sub tabs for page.
		 * @param array  $args Arguments to render page.
		 */
		public static function render_admin_ui( $page_name, $sub_tabs, $args = array() ) {

			// wrapper class.
			$wrapper_class = '';
			if ( ! empty( $args['wrapper_class'] ) && is_array( $args['wrapper_class'] ) ) {
				$wrapper_class = implode( ' ', $args['wrapper_class'] );
			}

			// tabs.
			if ( 'rtmedia-settings' === $page_name ) {
				$sub_tabs = apply_filters( 'rtmedia_pro_settings_tabs_content', $sub_tabs );
				ksort( $sub_tabs );
			}
			$tab_position_class = 'rtm-vertical-tabs';
			if ( 'rtmedia-addons' === $page_name ) {
				$tab_position_class = 'rtm-horizotanl-tabs';
			}
			?>

			<div
				class="clearfix <?php echo esc_attr( $tab_position_class ); ?> rtm-admin-tab-container <?php echo esc_attr( $wrapper_class ); ?>">
				<ul class="rtm-tabs">
					<?php
					$i = 1;
					foreach ( $sub_tabs as $tab ) {

						// tab status.
						$active_class = '';
						$error_class  = '';

						if ( ! empty( $tab['args'] ) && ( empty( $tab['args']['status'] ) || 'valid' !== $tab['args']['status'] ) ) {
							$error_class = 'error';
						}
						if ( 1 === $i ) {
							$active_class = 'active';
						}

						?>
						<li class="<?php echo esc_attr( $active_class ); ?> <?php echo esc_attr( $error_class ); ?>">
							<a id="tab-<?php echo esc_attr( substr( $tab['href'], 1 ) ); ?>" title="<?php echo esc_attr( $tab['title'] ); ?>" href="<?php echo esc_url( $tab['href'] ); ?>" class="rtmedia-tab-title <?php echo esc_attr( sanitize_title( $tab['name'] ) ); ?>">
								<?php
								if ( isset( $tab['icon'] ) && ! empty( $tab['icon'] ) ) {
									?>
									<i class="<?php echo esc_attr( $tab['icon'] ); ?> dashicons"></i>
									<?php
								}
								?>
								<span><?php echo esc_html( $tab['name'] ); ?></span>
							</a>
						</li>
						<?php
						$i++;
					}
					?>
				</ul>

				<div class="tabs-content rtm-tabs-content">
					<?php
					$k = 1;
					foreach ( $sub_tabs as $tab ) {
						$active_class = '';
						if ( 1 === $k ) {
							$active_class = ' active';
						}
						$k++;
						if ( isset( $tab['icon'] ) && ! empty( $tab['icon'] ) ) {
							$icon = '<i class="' . esc_attr( $tab['icon'] ) . '"></i>';
						}
						$tab_without_hash = explode( '#', $tab['href'] );
						$tab_without_hash = $tab_without_hash[1];
						echo '<div class="rtm-content' . esc_attr( $active_class ) . '" id="' . esc_attr( $tab_without_hash ) . '">';
						if ( isset( $tab['args'] ) ) {
							call_user_func( $tab['callback'], $page_name, $tab['args'] );
						} else {
							call_user_func( $tab['callback'], $page_name );
						}
						echo '</div>';
					}
					?>
				</div>

			</div>
			<?php
		}

		/**
		 * To remove setting saved parameter from url once satting saved
		 * Add parameter to this array WP will remove variable from Query string
		 *
		 * @param array $removable_query_args arguments.
		 *
		 * @return array $removable_query_args
		 */
		public function removable_query_args( $removable_query_args ) {
			$page_name = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
			if ( isset( $page_name ) && 'rtmedia-settings' === $page_name ) {
				$removable_query_args[] = 'settings-saved';
			}

			return $removable_query_args;
		}

		/**
		 * Display invalid license notice to admins.
		 *
		 * @since 4.1.7
		 *
		 * @return  void
		 */
		public function rtm_addon_license_notice() {

			$page_name = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
			$args      = array(
				'a' => array(
					'href' => array(),
				),
			);

			if ( ! empty( $page_name ) && 'rtmedia-license' === $page_name ) {
				$my_account  = 'https://rtmedia.io/my-account';
				$license_doc = 'https://rtmedia.io/docs/license/';

				// translators: 1. Account page and link.
				$message = sprintf( __( 'Your license keys can be found on <a href="%1$s">my-account</a> page. For more details, please refer to <a href="%2$s">License documentation</a> page.', 'buddypress-media' ), $my_account, $license_doc );
				echo '<div class="notice"><p>' . wp_kses( $message, $args ) . '</p></div>';

				return;
			}

			$addons = apply_filters( 'rtmedia_license_tabs', array() );

			if ( empty( $addons ) ) {
				return;
			}

			foreach ( $addons as $addon ) {
				if ( empty( $addon['args']['status'] ) || 'valid' !== $addon['args']['status'] ) {
					$message = sprintf(
						// translators: 1. License page link.
						__( 'We found an invalid or expired license key for an rtMedia add-on. Please go to the <a href="%1$s">Licenses page</a> to fix this issue.', 'buddypress-media' ),
						admin_url( 'admin.php?page=rtmedia-license' )
					);
					echo '<div class="error"><p>' . wp_kses( $message, $args ) . '</p></div>';
					break;
				}
			}
		}
	}

}
