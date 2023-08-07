<?php
/**
 * Handles rtMedia support
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>, Joshua Abenazer <joshua.abenazer@rtcamp.com>
 *
 * @package    rtMedia
 */

if ( ! class_exists( 'RTMediaSupport' ) ) {

	/**
	 * Class to handle rtMedia support.
	 */
	class RTMediaSupport {

		/**
		 * Debug info.
		 *
		 * @var $debug_info
		 */
		public $debug_info;

		/**
		 * Current sub tab.
		 *
		 * @var mixed
		 */
		public $curr_sub_tab;

		/**
		 * Current page
		 *
		 * @var string
		 */
		public static $page;

		/**
		 * RTMediaSupport Constructor
		 *
		 * @access public
		 *
		 * @param  bool $init init.
		 *
		 * @return mixed
		 */
		public function __construct( $init = true ) {

			if ( ! is_admin() ) {
				return;
			}

			$this->curr_sub_tab = 'support';
			$tab                = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			if ( isset( $tab ) ) {
				$this->curr_sub_tab = $tab;
			}

			// Check if download debug info request is made or not.
			$nonce = filter_input( INPUT_POST, 'download_debuginfo_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$info  = filter_input( INPUT_POST, 'download_debuginfo', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			if ( isset( $info ) && '1' === $info && is_admin() ) {
				if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'rtmedia-download-debuginfo' ) ) {

					wp_die(
						sprintf(
							'<h1>%1$s</h1><p>%2$s</p>',
							esc_html__( 'Cheatin\' uh?', 'buddypress-media' ),
							esc_html__( 'Can not verify request source.', 'buddypress-media' )
						)
					);

				} else {
					// download the debug info.
					$this->download_debuginfo_as_text();
				}
			}
		}

		/**
		 * Get support content.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function get_support_content() {
			$tabs = array();
			global $rtmedia_admin;
			$tabs[] = array(
				'title'    => esc_html__( 'Support', 'buddypress-media' ),
				'name'     => esc_html__( 'Support', 'buddypress-media' ),
				'href'     => '#support',
				'icon'     => 'dashicons-businessman',
				'callback' => array( $this, 'call_get_form' ),
			);
			$tabs[] = array(
				'title'    => esc_html__( 'Debug Info', 'buddypress-media' ),
				'name'     => esc_html__( 'Debug Info', 'buddypress-media' ),
				'href'     => '#debug',
				'icon'     => 'dashicons-admin-tools',
				'callback' => array( $this, 'debug_info_html' ),
			);

			// if any un-migrated media is there.
			if ( $this->is_migration_required() ) {
				$tabs[] = array(
					'title'    => esc_html__( 'Migration', 'buddypress-media' ),
					'name'     => esc_html__( 'Migration', 'buddypress-media' ),
					'href'     => '#migration',
					'callback' => array( $this, 'migration_html' ),
				);
			}
			?>
			<div id="rtm-support">
				<?php RTMediaAdmin::render_admin_ui( self::$page, $tabs ); ?>
			</div>
			<?php
		}

		/**
		 * Cancel request.
		 */
		public function rtmedia_cancel_request() {
			do_settings_sections( 'rtmedia-support' );
			die();
		}

		/**
		 * Mail content type.
		 *
		 * @return string
		 */
		public function rtmedia_mail_content_type() {
			return 'text/html';
		}

		/**
		 * Render support page.
		 *
		 * @access public
		 *
		 * @param string $page Page name.
		 *
		 * @return void
		 */
		public function render_support( $page = '' ) {

			self::$page = $page;

			global $wp_settings_sections, $wp_settings_fields;

			if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[ $page ] ) ) {
				return;
			}

			foreach ( (array) $wp_settings_sections[ $page ] as $section ) {

				if ( $section['callback'] ) {
					call_user_func( $section['callback'], $section );
				}

				if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
					continue;
				}

				echo '<table class="form-table">';
				do_settings_fields( $page, $section['id'] );
				echo '</table>';
			}
		}

		/**
		 * Define Service Selector.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function service_selector() {
			// todo: nonce required.
			$form = filter_input( INPUT_POST, 'form', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			include RTMEDIA_PATH . 'app/helper/templates/service-sector.php';
		}

		/**
		 * Call rtmedia admin support form.
		 *
		 * @param string $page Page.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function call_get_form( $page = '' ) {
			$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			if ( isset( $page ) && 'rtmedia-support' === $page ) {
				if ( 'support' === $this->curr_sub_tab ) {
					echo "<div id='rtmedia_service_contact_container' class='rtm-support-container'><form name='rtmedia_service_contact_detail' method='post'>";
					$this->get_form( 'premium_support' );
					echo '</form></div>';
				}
			}
		}

		/**
		 * Get plugin_info.
		 *
		 * @access public
		 *
		 * @return array|bool $rtmedia_plugins
		 */
		public function get_plugin_info() {
			include_once ABSPATH . '/wp-admin/includes/plugin.php';
			$active_plugins = (array) get_option( 'active_plugins', array() );
			if ( is_multisite() ) {
				$active_plugins = array_merge( $active_plugins, rtmedia_get_site_option( 'active_sitewide_plugins', array() ) );
			}
			$rtmedia_plugins = array();
			foreach ( $active_plugins as $plugin ) {
				$plugin_data    = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
				$version_string = '';
				if ( ! empty( $plugin_data['Name'] ) ) {
					$rtmedia_plugins[] = esc_html( $plugin_data['Name'] ) . ' ' . esc_html__( 'by', 'buddypress-media' ) . ' ' . $plugin_data['Author'] . ' ' . esc_html__( 'version', 'buddypress-media' ) . ' ' . $plugin_data['Version'] . $version_string;
				}
			}
			if ( 0 === count( $rtmedia_plugins ) ) {
				return false;
			} else {
				return implode( ', <br/>', $rtmedia_plugins );
			}
		}

		/**
		 * Scan the rtmedia template files.
		 *
		 * @access public
		 *
		 * @param  string $template_path Template path.
		 *
		 * @return array  $result
		 */
		public function rtmedia_scan_template_files( $template_path ) {
			$files  = scandir( $template_path );
			$result = array();
			if ( $files ) {
				foreach ( $files as $key => $value ) {
					if ( ! in_array( $value, array( '.', '..' ), true ) ) {
						if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
							$sub_files = $this->rtmedia_scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
							foreach ( $sub_files as $sub_file ) {
								$rt_to_dir_paths = RTMediaTemplate::locate_template( str_replace( '.php', '', $sub_file ) );
								$rt_to_dir_path  = str_replace( '//', '/', $rt_to_dir_paths );
								$result[]        = str_replace( ABSPATH . 'wp-content/', '', $rt_to_dir_path );
							}
						} else {
							if ( 'main.php' !== $value ) {
								$result[] = $value;
							}
						}
					}
				}
			}

			return $result;
		}

		/**
		 * Show debug info.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function debug_info() {
			global $wpdb, $wp_version, $bp;
			$debug_info               = array();
			$debug_info['Home URL']   = esc_url( home_url() );
			$debug_info['Site URL']   = esc_url( site_url() );
			$debug_info['PHP']        = esc_html( PHP_VERSION );
			$debug_info['MYSQL']      = esc_html( $wpdb->db_version() );
			$debug_info['WordPress']  = esc_html( $wp_version );
			$debug_info['BuddyPress'] = esc_html( ( isset( $bp->version ) ) ? $bp->version : '-NA-' );
			$debug_info['rtMedia']    = esc_html( RTMEDIA_VERSION );
			$debug_info['OS']         = esc_html( PHP_OS );
			if ( extension_loaded( 'imagick' ) ) {
				$imagickobj = new Imagick();
				$imagick    = preg_replace( " #((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#i", "'<a href=\"$1\" target=\"_blank\">$3</a>$4'", $imagickobj->getversion() );
				$message    = $imagick;
			} else {
				$imagick['versionString'] = 'Not Installed';
			}
			$debug_info['Imagick'] = $imagick['versionString'];
			if ( extension_loaded( 'gd' ) ) {
				$gd = gd_info();
			} else {
				$gd['GD Version'] = 'Not Installed';
			}
			$debug_info['GD']                            = esc_html( $gd['GD Version'] );
			$debug_info['[php.ini] post_max_size']       = esc_html( ini_get( 'post_max_size' ) );
			$debug_info['[php.ini] upload_max_filesize'] = esc_html( ini_get( 'upload_max_filesize' ) );
			$debug_info['[php.ini] memory_limit']        = esc_html( ini_get( 'memory_limit' ) );
			$debug_info['Installed Plugins']             = $this->get_plugin_info();
			$active_theme                                = wp_get_theme();
			$debug_info['Theme Name']                    = esc_html( $active_theme->Name ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar, WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$debug_info['Theme Version']                 = esc_html( $active_theme->Version ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar, WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$debug_info['Author URL']                    = esc_url( $active_theme->{'Author URI'} );
			$debug_info['Template Overrides']            = implode( ', <br/>', $this->rtmedia_scan_template_files( RTMEDIA_PATH . '/templates/' ) );

			global $wpdb;
			$rtmedia_model = new RTMediaModel();
			$results       = $wpdb->get_results( $wpdb->prepare( "select media_type, count(id) as count from {$rtmedia_model->table_name} where blog_id = %d group by media_type limit 100", get_current_blog_id() ) ); // phpcs:ignore

			if ( $results ) {
				foreach ( $results as $media ) {
					$debug_info[ 'Total ' . ucfirst( $media->media_type ) . 's' ] = $media->count;
				}
			}

			// get all rtMedia Settings.
			$rtmedia_options = get_option( 'rtmedia-options' );
			if ( is_array( $rtmedia_options ) ) {
				foreach ( $rtmedia_options as $option => $value ) {
					$debug_info[ ucwords( str_replace( '_', ' ', $option ) ) ] = $value;
				}
			}

			$this->debug_info = $debug_info;
		}

		/**
		 * Generate debug_info html.
		 *
		 * @access public
		 *
		 * @param string $page Page name.
		 *
		 * @return void
		 */
		public function debug_info_html( $page = '' ) {
			$this->debug_info();
			$allowed_html = array(
				'a'  => array(
					'href' => array(),
				),
				'br' => array(),
			);

			$debug_info = $this->debug_info;

			include RTMEDIA_PATH . 'app/helper/templates/debug-info.php';
		}

		/**
		 * Check for migration_required.
		 *
		 * @access public
		 *
		 * @return bool
		 */
		public function is_migration_required() {
			$pending_rtmedia_migrate = rtmedia_get_site_option( 'rtMigration-pending-count' );
			if ( ( false === $pending_rtmedia_migrate || 0 === intval( $pending_rtmedia_migrate ) ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Generate migration_html.
		 *
		 * @access public
		 *
		 * @param  string $page Page.
		 */
		public function migration_html( $page = '' ) {
			$pending_rtmedia_migrate = rtmedia_get_site_option( 'rtMigration-pending-count' );

			$content = ' ';
			$flag    = true;
			if ( ( false === $pending_rtmedia_migrate || 0 === intval( $pending_rtmedia_migrate ) ) ) {
				$content .= esc_html__( 'There is no media found to migrate.', 'buddypress-media' );
				$flag     = false;
			}
			$content = apply_filters( 'rtmedia_migration_content_filter', $content );
			if ( $flag ) {

				$content .= sprintf(
					'<div class="rtmedia-migration-support"><p>%1$s <a href="%2$s">%3$s</a> %4$s</p></div>',
					esc_html__( 'Click', 'buddypress-media' ),
					esc_url( get_admin_url() ) . 'admin.php?page=rtmedia-migration',
					esc_html__( 'here', 'buddypress-media' ),
					esc_html__( 'to migrate media from rtMedia 2.x to rtMedia 3.0+.', 'buddypress-media' )
				);
			}
			?>
			<div id="rtmedia-migration-html">
				<?php echo wp_kses( $content, RTMedia::expanded_allowed_tags() ); ?>
			</div>
			<?php
		}

		/**
		 * Generate rtmedia admin form.
		 *
		 * @param  string $form From.
		 *
		 * @return void
		 */
		public function get_form( $form = '' ) {
			// todo: nonce required.
			if ( empty( $form ) ) {
				$form = filter_input( INPUT_POST, 'form' . FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$form = isset( $form ) ? $form : 'premium_support';
			}
			$meta_title = '';
			switch ( $form ) {
				case 'bug_report':
					$meta_title = esc_html__( 'Submit a Bug Report', 'buddypress-media' );
					break;
				case 'new_feature':
					$meta_title = esc_html__( 'Submit a New Feature Request', 'buddypress-media' );
					break;
				case 'premium_support':
					$meta_title = esc_html__( 'Submit Support Request', 'buddypress-media' );
					break;
			}

			if ( 'premium_support' === $form ) {
				if ( ! has_filter( 'rtmedia_license_tabs' ) && ! has_action( 'rtmedia_addon_license_details' ) ) {

					$content = sprintf( '<h3 class="rtm-option-title">%1$s</h3>', esc_html( $meta_title ) );

					$content .= sprintf(
						'<p>%1$s <a target="_blank" href="https://rtmedia.io/support/">%2$s</a></p>',
						esc_html__( 'If your site has some issues due to rtMedia and you want support, feel free to create a support topic on', 'buddypress-media' ),
						esc_html__( 'rtMedia Support Page', 'buddypress-media' )
					);

					$content .= sprintf(
						'<p>%1$s <a target="_blank" href="https://github.com/rtMediaWP/rtmedia/issues/new">%2$s</a></p>',
						esc_html__( 'If you have any suggestions, enhancements or bug reports, then you can open a new issue on', 'buddypress-media' ),
						esc_html__( 'GitHub', 'buddypress-media' )
					);

					echo wp_kses( $content, RTMedia::expanded_allowed_tags() );
				} else {
					$website         = filter_input( INPUT_POST, 'website', FILTER_SANITIZE_URL );
					$subject         = filter_input( INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
					$details         = filter_input( INPUT_POST, 'details', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
					$server_addr     = rtm_get_server_var( 'SERVER_ADDR', 'FILTER_VALIDATE_IP' );
					$remote_addr     = rtm_get_server_var( 'REMOTE_ADDR', 'FILTER_VALIDATE_IP' );
					$server_software = rtm_get_server_var( 'SERVER_SOFTWARE', 'FILTER_SANITIZE_FULL_SPECIAL_CHARS' );
					$http_user_agent = rtm_get_server_var( 'HTTP_USER_AGENT', 'FILTER_SANITIZE_FULL_SPECIAL_CHARS' );

					include RTMEDIA_PATH . 'app/helper/templates/support-form.php';
				}
			}
		}

		/**
		 * Now submit request.
		 *
		 * @return void
		 */
		public function submit_request() {
			$nonce = filter_input( INPUT_POST, 'support_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'rtmedia-support-request' ) ) {

				wp_die(
					sprintf(
						'<h1>%1$s</h1><p>%2$s</p>',
						esc_html__( 'Cheatin\' uh?', 'buddypress-media' ),
						esc_html__( 'Can not verify request source.', 'buddypress-media' )
					)
				);
			}

			$this->debug_info();
			$form_data = filter_input( INPUT_POST, 'form_data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$form_data = wp_parse_args( $form_data );
			foreach ( $form_data as $key => $formdata ) {
				if ( '' === $formdata && 'phone' !== $key ) {
					echo 'false';
					die();
				}
			}
			if ( 'premium_support' === sanitize_text_field( $form_data['request_type'] ) ) {
				$mail_type = 'Premium Support';
				$title     = esc_html__( 'rtMedia Premium Support Request from', 'buddypress-media' );
			} elseif ( 'new_feature' === sanitize_text_field( $form_data['request_type'] ) ) {
				$mail_type = 'New Feature Request';
				$title     = esc_html__( 'rtMedia New Feature Request from', 'buddypress-media' );
			} elseif ( 'bug_report' === sanitize_text_field( $form_data['request_type'] ) ) {
				$mail_type = 'Bug Report';
				$title     = esc_html__( 'rtMedia Bug Report from', 'buddypress-media' );
			} else {
				$mail_type = 'Bug Report';
				$title     = esc_html__( 'rtMedia Contact from', 'buddypress-media' );
			}

			ob_start();

			include RTMEDIA_PATH . 'app/helper/templates/submit-request.php';

			$message = ob_get_clean();

			add_filter( 'wp_mail_content_type', array( $this, 'rtmedia_mail_content_type' ) );

			$debuglog_temp_path = sanitize_text_field( $form_data['debuglog_temp_path'] );
			// set attachment path for sending into mail.
			$attachment_file = ( ! empty( $debuglog_temp_path ) ) ? $debuglog_temp_path : '';
			$attachments     = array( $attachment_file );

			$headers       = 'From: ' . $form_data['name'] . ' <' . $form_data['email'] . '>' . "\r\n";
			$support_email = 'support@rtcamp.com';
			if ( wp_mail(
				$support_email,
				'[rtmedia] ' . $mail_type . ' from ' . str_replace(
					array(
						'http://',
						'https://',
					),
					'',
					$form_data['website']
				),
				stripslashes( $message ),
				$headers,
				$attachments
			) ) {
				// delete file after sending it to mail.
				if ( ! empty( $attachment_file ) ) {
					unlink( $attachment_file );
				}
				echo '<div class="rtmedia-success" style="margin:10px 0;">';

				if ( 'new_feature' === sanitize_text_field( $form_data['request_type'] ) ) {

					printf( '<p>%1$s</p>', esc_html__( 'Thank you for your Feedback/Suggestion.', 'buddypress-media' ) );

				} else {

					printf(
						'<p>%1$s</p><p>%2$s</p>',
						esc_html__( 'Thank you for posting your support request.', 'buddypress-media' ),
						esc_html__( 'We will get back to you shortly.', 'buddypress-media' )
					);
				}

				echo '</div>';

			} else {

				echo '<div class="rtmedia-error">';

				printf(
					'<p>%1$s</p><p>%2$s</p>',
					esc_html__( 'Your server failed to send an email.', 'buddypress-media' ),
					esc_html__( 'Kindly contact your server support to fix this.', 'buddypress-media' )
				);

				printf(
					'<p>%1$s <a target="_blank" href="https://rtmedia.io/premium-support/">%2$s</a></p>',
					esc_html__( 'You can alternatively create a support request', 'buddypress-media' ),
					esc_html__( 'here', 'buddypress-media' )
				);

				echo '</div>';
			}
			die();
		}

		/**
		 * Write debug info as a text file and download it.
		 *
		 * @return void
		 */
		public function download_debuginfo_as_text() {

			header( 'Content-disposition: attachment; filename=debuginfo.txt' );
			header( 'Content-type: text/plain' );

			global $wpdb, $wp_version, $bp;

			$debug_info               = array();
			$debug_info['Home URL']   = esc_url( home_url() );
			$debug_info['Site URL']   = esc_url( site_url() );
			$debug_info['PHP']        = esc_html( PHP_VERSION );
			$debug_info['MYSQL']      = esc_html( $wpdb->db_version() );
			$debug_info['WordPress']  = esc_html( $wp_version );
			$debug_info['BuddyPress'] = esc_html( ( isset( $bp->version ) ) ? $bp->version : '-NA-' );
			$debug_info['rtMedia']    = esc_html( RTMEDIA_VERSION );
			$debug_info['OS']         = esc_html( PHP_OS );
			if ( extension_loaded( 'imagick' ) ) {
				$imagickobj = new Imagick();
				$imagick    = preg_replace( " #((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#i", "'<a href=\"$1\" target=\"_blank\">$3</a>$4'", $imagickobj->getversion() );
				$message    = $imagick;
			} else {
				$imagick['versionString'] = 'Not Installed';
			}
			$debug_info['Imagick'] = $imagick['versionString'];
			if ( extension_loaded( 'gd' ) ) {
				$gd = gd_info();
			} else {
				$gd['GD Version'] = 'Not Installed';
			}
			$debug_info['GD']                            = esc_html( $gd['GD Version'] );
			$debug_info['[php.ini] post_max_size']       = esc_html( ini_get( 'post_max_size' ) );
			$debug_info['[php.ini] upload_max_filesize'] = esc_html( ini_get( 'upload_max_filesize' ) );
			$debug_info['[php.ini] memory_limit']        = esc_html( ini_get( 'memory_limit' ) );
			$plugin_info                                 = explode( ',', $this->get_plugin_info() );
			$debug_info['Installed Plugins']             = implode( ', ' . PHP_EOL . str_repeat( ' ', 49 ), $plugin_info );
			$active_theme                                = wp_get_theme();
			$debug_info['Theme Name']                    = esc_html( $active_theme->Name ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar, WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$debug_info['Theme Version']                 = esc_html( $active_theme->Version ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar, WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$debug_info['Author URL']                    = esc_url( $active_theme->{'Author URI'} );
			$debug_info['Template Overrides']            = implode( ', ' . PHP_EOL . str_repeat( ' ', 50 ), $this->rtmedia_scan_template_files( RTMEDIA_PATH . '/templates/' ) );
			$rtmedia_options                             = get_option( 'rtmedia-options' );
			$rtmedia_options                             = array_merge( $debug_info, $rtmedia_options );

			if ( ! empty( $rtmedia_options ) ) {
				echo '==============================================================================' . PHP_EOL;
				echo '================================== Debug Info ================================' . PHP_EOL;
				echo '==============================================================================' . PHP_EOL . PHP_EOL . PHP_EOL;

				foreach ( $rtmedia_options as $option => $value ) {
					echo wp_kses_post( ucwords( str_replace( '_', ' ', $option ) ) . str_repeat( ' ', 50 - strlen( $option ) ) . wp_strip_all_tags( $value ) . PHP_EOL );
				}

				readfile( 'debuginfo.txt' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_readfile
				exit();
			}

		}

	}

}
