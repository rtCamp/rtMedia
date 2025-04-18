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

		/*
		 * Static property to store the admin pages
		 *
		 * @var array
		 */
		public static $rtmedia_pages = [
			'rtmedia-settings',
			'rtmedia-addons',
			'rtmedia-pro-addons',
			'rtmedia-support',
			'rtmedia-themes',
			'rtmedia-hire-us',
			'rtmedia-license',
			'rtmedia-attributes',
			'rtmedia-moderate',
			'rtmedia-blocked-users',
		];

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

			// Show admin notice to install GoDAM pluing.
			if ( is_multisite() ) {
				add_action( 'network_admin_notices', array( $this, 'install_godam_admin_notice' ) );
			}
			add_action( 'admin_notices', array( $this, 'install_godam_admin_notice' ) );
			add_action( 'wp_ajax_install_godam_hide_admin_notice', array( $this, 'install_godam_hide_admin_notice' ) );

			$rtmedia_option = filter_input( INPUT_POST, 'rtmedia-options', FILTER_DEFAULT, FILTER_SANITIZE_NUMBER_INT );
			if ( isset( $rtmedia_option ) ) {
				if ( isset( $rtmedia_option['general_showAdminMenu'] ) && 1 === intval( $rtmedia_option['general_showAdminMenu'] ) ) {
					add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100, 1 );
				}
			} else {
				if ( is_array( $rtmedia->options ) ) {
					if ( 1 === intval( $rtmedia->options['general_showAdminMenu'] ) ) {
						add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100, 1 );
					}
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

			new RTMediaMediaSizeImporter(); // Do not delete this line. We only need to create object of this class if we are in admin section.
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

				include RTMEDIA_PATH . 'app/admin/templates/notices/transcoder.php';

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

			$page_name = sanitize_text_field( filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );

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
				$action['view'] = sprintf(
					'<a href="%1$s" title="%2$s" rel="permalink">%3$s</a>',
					esc_url( $link ),
					esc_html( sprintf( 'View "%s"', $title ) ),
					esc_html__( 'View', 'buddypress-media' )
				);
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
				$this->rtmedia_update_template_notice();

				if ( ! is_rtmedia_vip_plugin() ) {
					$this->rtmedia_inspirebook_release_notice();
					$this->rtmedia_premium_addon_notice();
					$this->rtmedia_addon_update_notice();
				}
			}
		}

		/**
		 * For rtMedia Pro split release admin notice
		 */
		public function rtmedia_premium_addon_notice() {
			$site_option = rtmedia_get_site_option( 'rtmedia_premium_addon_notice' );

			$premium_addon_notice = apply_filters( 'rt_premium_addon_notice', true );
			if ( ( ! $site_option || 'hide' !== $site_option ) ) {
				if ( true === $premium_addon_notice ) {
					rtmedia_update_site_option( 'rtmedia_premium_addon_notice', 'show' );

					include RTMEDIA_PATH . 'app/admin/templates/notices/premium-addon.php';
				}
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

				include RTMEDIA_PATH . 'app/admin/templates/notices/inspirebook-release.php';
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

			printf(
				'<div class="error rtmedia-permalink-change-notice"><p><b>%1$s</b> %2$s <a href="%3$s">%4$s</a> %5$s</p></div>',
				esc_html__( 'rtMedia:', 'buddypress-media' ),
				esc_html__( ' You must', 'buddypress-media' ),
				esc_url( admin_url( 'options-permalink.php' ) ),
				esc_html__( 'update permalink structure', 'buddypress-media' ),
				esc_html__( 'to something other than the default for it to work.', 'buddypress-media' )
			);
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

				include RTMEDIA_PATH . 'app/admin/templates/notices/addon-update.php';
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

			// Check for rtMedia Instagram version.
			if ( defined( 'RTMEDIA_INSTAGRAM_PATH' ) ) {
				$plugin_info = get_plugin_data( RTMEDIA_INSTAGRAM_PATH . 'index.php' );
				if ( ! empty( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.1.14' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_PHOTO_TAGGING_PATH' ) ) {
				// Check for rtMedia Photo Tagging version.
				$plugin_info = get_plugin_data( RTMEDIA_PHOTO_TAGGING_PATH . 'index.php' );
				if ( ! empty( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.2.14' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_FFMPEG_PATH' ) ) {
				// Check for rtMedia FFPMEG version.
				$plugin_info = get_plugin_data( RTMEDIA_FFMPEG_PATH . 'index.php' );
				if ( ! empty( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.1.14' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_KALTURA_PATH' ) ) {
				// Check for rtMedia Kaltura version.
				$plugin_info = get_plugin_data( RTMEDIA_KALTURA_PATH . 'index.php' );
				if ( ! empty( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '3.0.16' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_PRO_PATH' ) ) {
				// Check for rtMedia Pro version.
				$plugin_info = get_plugin_data( RTMEDIA_PRO_PATH . 'index.php' );
				if ( ! empty( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.6' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_SOCIAL_SYNC_PATH' ) ) {
				// Check for rtMedia Social Sync version.
				$plugin_info = get_plugin_data( RTMEDIA_SOCIAL_SYNC_PATH . 'index.php' );
				if ( ! empty( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '1.3.1' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_MEMBERSHIP_PATH' ) ) {
				// Check for rtMedia Membership version.
				$plugin_info = get_plugin_data( RTMEDIA_MEMBERSHIP_PATH . 'index.php' );
				if ( ! empty( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.1.5' ) ) ) {
					$return_flag = true;
				}
			} elseif ( defined( 'RTMEDIA_WATERMARK_PATH' ) ) {
				// Check for rtMedia Photo Watermark version.
				$plugin_info = get_plugin_data( RTMEDIA_WATERMARK_PATH . 'index.php' );
				if ( ! empty( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '1.1.8' ) ) ) {
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
			$src = RTMEDIA_URL . 'app/assets/admin/img/rtMedia-pro-ad.png';
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

			include RTMEDIA_PATH . 'app/admin/templates/dashboard-widgets/right-now.php';
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
			$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited, WordPress.WP.GlobalVariablesOverride.Prohibited
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

			$settings_link = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( admin_url( 'admin.php?page=rtmedia-settings' ) ),
				esc_html__( 'Settings', 'buddypress-media' )
			);
			array_push( $links, $settings_link );

			$settings_link = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( admin_url( 'admin.php?page=rtmedia-support' ) ),
				esc_html__( 'Support', 'buddypress-media' )
			);
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
						'title'  => esc_html__( 'Premium', 'buddypress-media' ),
						'href'   => admin_url( 'admin.php?page=rtmedia-addons' ),
						'meta'   => array(
							'title'  => esc_html__( 'Premium', 'buddypress-media' ),
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
				esc_html__( 'Premium', 'buddypress-media' ),
				esc_html__( 'Premium', 'buddypress-media' ),
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
		 * @return string
		 */
		public static function get_current_tab() {
			$page_name = sanitize_text_field( filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
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

			$align = is_rtl() ? 'alignleft' : 'alignright';

			?>
			<div class="wrap bp-media-admin <?php echo esc_attr( $this->get_current_tab() ); ?>">
				<div id="icon-buddypress-media" class="icon32"><br></div>
				<div>
					<h2 class="nav-tab-wrapper"><?php $this->rtmedia_tabs(); ?>
						<span class="<?php echo esc_attr( $align ); ?> by">
							<a class="rt-link" href="https://rtmedia.io/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media" target="_blank" title="rtCamp : <?php esc_attr_e( 'Empowering The Web With WordPress', 'buddypress-media' ); ?>">
								<img src="<?php echo esc_url( RTMEDIA_URL . 'app/assets/admin/img/rtcamp-logo.png' ); ?>" alt="rtCamp"/>
							</a>
						</span>
					</h2>
				</div>

				<div class="clearfix rtm-row-container">

					<?php
					$settings_sub_tabs = array();

					if ( 'rtmedia-settings' === $page_name ) {
						$settings_sub_tabs = $this->settings_sub_tabs();
					}

					include RTMEDIA_PATH . 'app/admin/templates/settings/main.php';
					?>

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

				printf(
					'<a id="bp-media" title="%1$s" href="%2$s" class="%3$s">%4$s</a>',
					esc_attr( $tab['title'] ),
					esc_url( $tab['href'] ),
					esc_attr( $tab['class'] ),
					esc_html( $tab['name'] )
				);
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
					'name' => esc_html__( 'Premium', 'buddypress-media' ),
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
			foreach ( $tabs as $tab_data ) {
				$is_current = (bool) ( $tab_data['slug'] === $this->get_current_tab() );
				$tab_class  = $is_current ? $active_class : $idle_class;

				if ( isset( $tab_data['class'] ) && is_array( $tab_data['class'] ) ) {
					$tab_class .= ' ' . implode( ' ', $tab_data['class'] );
				}

				printf(
					'<a href="%1$s" class="%2$s">%3$s</a>',
					esc_url( $tab_data['href'] ),
					esc_attr( $tab_class ),
					esc_html( $tab_data['name'] )
				);
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
			do_action( 'rtmedia_sanitize_settings', wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification, WordPress.Security.NonceVerification.Missing

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

			$rtmedia_current_user = wp_get_current_user();

			// translators: 1. Home url.
			$rtmedia_sidebar_message = sprintf( esc_html__( 'I use @rtMediaWP http://rt.cx/rtmedia on %s', 'buddypress-media' ), home_url() );

			ob_start();

			include RTMEDIA_PATH . 'app/admin/templates/settings/sidebar-addons.php';

			$addons = ob_get_clean();

			new RTMediaAdminWidget( 'spread-the-word', esc_html__( 'Spread the Word', 'buddypress-media' ), $addons );

			ob_start();

			include RTMEDIA_PATH . 'app/admin/templates/settings/sidebar-branding.php';

			$branding = ob_get_clean();

			new RTMediaAdminWidget( 'branding', esc_html__( 'Subscribe', 'buddypress-media' ), $branding );

			do_action( 'rtmedia_after_default_admin_widgets' );
		}

		/**
		 * Function to save linkback.
		 *
		 * @return bool
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

            // permission check.
            if ( ! current_user_can( 'manage_options' ) ) {
	            wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to export settings.', 'buddypress-media' ) ) );
            }

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

			$settings_data_json_string = file_get_contents( $file_path );
			$settings_data_json = json_decode( $settings_data_json_string, true );
			wp_delete_file( $file_path );

			if ( empty( $settings_data_json ) ) {
				$response['rtm_response']     = 'error';
				$response['rtm_response_msg'] = esc_html__( 'Invalid JSON Supplied!', 'buddypress-media' );
				wp_send_json( $response );
			}

			$settings_data = $settings_data_json;
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
			$interested = sanitize_text_field( filter_input( INPUT_POST, 'linkback', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
			$choice     = sanitize_text_field( filter_input( INPUT_POST, 'choice', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
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
							esc_url( 'https://rtmedia.io/' )
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
			$survey_done = filter_input( INPUT_GET, 'survey-done', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
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

				$plugin_meta[] = sprintf(
					'<a href="https://rtmedia.io/products/?utm_source=dashboard&#038;utm_medium=plugin&#038;utm_campaign=buddypress-media" title="%1$s">%1$s</a>',
					esc_attr__( 'Premium Add-ons', 'buddypress-media' )
				);
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

			include RTMEDIA_PATH . 'app/admin/templates/notices/upload-file-types.php';
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

					include RTMEDIA_PATH . 'app/admin/templates/notices/update-template.php';
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

			include RTMEDIA_PATH . 'app/admin/templates/settings/admin-ui.php';
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
			$page_name = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
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

			$page_name = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$args      = array(
				'a' => array(
					'href' => array(),
				),
			);

			if ( ! empty( $page_name ) && 'rtmedia-license' === $page_name ) {
				$my_account  = 'https://rtmedia.io/my-account';
				$license_doc = 'https://rtmedia.io/docs/license/';

				$message = sprintf(
				/* translators: 1$s: Account page and link. 2$s: License documentation page link. */
					__( 'Your license keys can be found on <a href="%1$s">my-account</a> page. For more details, please refer to <a href="%2$s">License documentation</a> page.', 'buddypress-media' ),
					$my_account,
					$license_doc
				); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment

				printf( '<div class="notice"><p>%1$s</p></div>', wp_kses( $message, $args ) );

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
						__( 'We found an invalid or expired license key for rtMedia Premium. Please go to the <a href="%1$s">Licenses page</a> to fix this issue.', 'buddypress-media' ),
						admin_url( 'admin.php?page=rtmedia-license' )
					);
					echo '<div class="error"><p>' . wp_kses( $message, $args ) . '</p></div>';
					break;
				}
			}
		}

		/**
		 * Display GoDAM banner admin notice
		 */
		public function install_godam_admin_notice() {
			// Get the current page from the URL (e.g., ?page=rtmedia-settings)
			$current_page = isset($_GET['page']) ? $_GET['page'] : '';

			// List of pages where the banner should be shown
			$pages = self::$rtmedia_pages;

			// Check if the current page is one of the defined pages
			if (in_array($current_page, $pages, true)) {
				// Check if the banner has been dismissed
				$banner_dismissed = get_user_meta(get_current_user_id(), 'install_godam_hide_notice', true);

				if (!$banner_dismissed) {
					?>
					<div class="notice godam-admin-banner is-dismissible" style="position: relative; display:block; margin: 12px 0; padding: 0; width: 100%;">
						<div class="godam-banner-wrapper">
							<img src="<?php echo esc_url(RTMEDIA_URL . 'app/assets/img/godam-banner-2.png'); ?>" alt="Godam Banner" style="display: block; max-width: 100%; height: auto;">
							<a href="https://godam.io?utm_source=rtmedia&utm_medium=banner&utm_campaign=godam_promo" target="_blank" style="
								position: absolute;
								top: 65%;
								left: 32%;
								width: 160px;
								height: 40px;
								display: block;
								text-indent: -9999px;
								background: rgba(0,0,0,0);
								cursor: pointer;
								outline: none;
								border: none;
								box-shadow: none;
							">
								Check out now
							</a>
						</div>
					</div>

					<script type="text/javascript">
						// Ensure jQuery is loaded
						jQuery(document).ready(function($) {
							// Handle dismissal of the banner
							$(document).on('click', '.godam-admin-banner .notice-dismiss', function() {
								// Send AJAX request to mark the banner as dismissed
								var data = {
									action: 'install_godam_hide_admin_notice', // action hook
									security: '<?php echo wp_create_nonce('install-godam-hide-notice'); ?>' // nonce for security
								};

								// Perform the AJAX request
								$.post(ajaxurl, data, function(response) {
									console.log('Notice dismissed and saved.');
								});
							});
						});
					</script>
					<?php
				}
			}
		}

		/**
		 * AJAX callback to hide GoDAM admin notice
		 */
		public function install_godam_hide_admin_notice() {
			// Verify nonce for security
			if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'install-godam-hide-notice')) {
				wp_die('Nonce verification failed');
			}

			// Update user meta to remember the dismissal
			update_user_meta(get_current_user_id(), 'install_godam_hide_notice', true);

			// Respond back to the AJAX request
			wp_send_json_success('Notice dismissed');
		}

	}

}
