<?php
/**
 * Description of RTMediaSupport
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if ( ! class_exists( 'RTMediaSupport' ) ) {

	class RTMediaSupport {

		var $debug_info;
		var $curr_sub_tab;
		// current page
		public static $page;

		/**
		 * Constructor
		 *
		 * @access public
		 *
		 * @param  bool $init
		 *
		 * @return void
		 */
		public function __construct( $init = true ) {

			if ( ! is_admin() ) {
				return;
			}

			$this->curr_sub_tab = 'support';
			$tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
			if ( isset( $tab ) ) {
				$this->curr_sub_tab = $tab;
			}

			/* Check if download debug info request is made or not */
			$nonce = filter_input( INPUT_POST, 'download_debuginfo_wpnonce', FILTER_SANITIZE_STRING );
			if( isset( $_POST['download_debuginfo'] ) && '1' === $_POST['download_debuginfo'] && is_admin() ) {
				if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'rtmedia-download-debuginfo' ) ) {
					wp_die(
						'<h1>' . esc_html__( 'Cheatin\' uh?','buddypress-media' ) . '</h1>' .
						'<p>' . esc_html__( 'Can not verify request source.','buddypress-media' ) . '</p>'
					);
				} else {
					/* download the debug info */
					$this->download_debuginfo_as_text();
				}

			}
		}

		/**
		 * Get support content.
		 *
		 * @access public
		 *
		 * @param  void
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

			//if any un-migrated media is there
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

		public function rtmedia_cancel_request() {
		    do_settings_sections( 'rtmedia-support' );
		    die();
		}

		public function rtmedia_mail_content_type() {
		    return 'text/html';
		}

		/**
		 * Render support.
		 *
		 * @access public
		 *
		 * @param  type $page
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
		 * @param  void
		 *
		 * @return void
		 */
		public function service_selector() {
			//todo: nonce required
			$form = filter_input( INPUT_POST, 'form', FILTER_SANITIZE_STRING ); ?>
			<div>
				<form name="rtmedia_service_select_form" method="post">
					<p>
						<label class="bp-media-label"
						       for="select_support"><?php esc_html_e( 'Service', 'buddypress-media' ); ?>:</label>
						<select name="rtmedia_service_select">
							<option
								value="premium_support" <?php
								if ( 'premium_support' === $form ) {
									echo 'selected';
								}
							?>><?php esc_html_e( 'Premium Support', 'buddypress-media' ); ?></option>
							<option
								value="bug_report" <?php
								if ( 'bug_report' === $form ) {
									echo 'selected';
								}
							?>><?php esc_html_e( 'Bug Report', 'buddypress-media' ); ?></option>
							<option
								value="new_feature" <?php
								if ( 'new_feature' === $form ) {
									echo 'selected';
								}
							?>><?php esc_html_e( 'New Feature', 'buddypress-media' ); ?></option>
						</select>
						<input name="support_submit" value="<?php esc_attr_e( 'Submit', 'buddypress-media' ); ?>"
						       type="submit" class="button"/>
					</p>
				</form>
			</div>
			<?php
		}

		/**
		 * Call rtmedia admin support form.
		 *
		 * @access public
		 *
		 * @param  void
		 *
		 * @return void
		 */
		public function call_get_form( $page = '' ) {
			$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
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
		 * @param  void
		 *
		 * @return array $rtmedia_plugins
		 */
		public function get_plugin_info() {
			include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
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
		 * @param  string $template_path
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
								$rt_to_dir_paths	= RTMediaTemplate::locate_template( substr( $sub_file, 0, ( count( $sub_file ) - 5 ) ) );
								$rt_to_dir_path		= str_replace( '//', '/', $rt_to_dir_paths );
								$result[]			= str_replace( ABSPATH . 'wp-content/', '', $rt_to_dir_path );
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
		 * Show debug_info.
		 *
		 * @access public
		 *
		 * @param  void
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
				$imagick    = $message = preg_replace( " #((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#i", "'<a href=\"$1\" target=\"_blank\">$3</a>$4'", $imagickobj->getversion() );
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
			$debug_info['Theme Name']                    = esc_html( $active_theme->Name );
			$debug_info['Theme Version']                 = esc_html( $active_theme->Version );
			$debug_info['Author URL']                    = esc_url( $active_theme->{'Author URI'} );
			$debug_info['Template Overrides']            = implode( ', <br/>', $this->rtmedia_scan_template_files( RTMEDIA_PATH . '/templates/' ) );

			global $wpdb;
			$rtMedia_model = new RTMediaModel();
			$results = $wpdb->get_results( $wpdb->prepare( "select media_type, count(id) as count from {$rtMedia_model->table_name} where blog_id = %d group by media_type limit 100", get_current_blog_id() ) ); // @codingStandardsIgnoreLine
			if ( $results ) {
				foreach ( $results as $media ) {
					$debug_info[ 'Total ' . ucfirst( $media->media_type ) . 's' ] = $media->count;
				}
			}

			/* get all rtMedia Settings */
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
		 * @param  void
		 *
		 * @return void
		 */
		public function debug_info_html( $page = '' ) {
			$this->debug_info();
			$allowed_html = array(
				'a' => array(
					'href' => array(),
					),
				'br' => array(),
				);
			?>
			<div id="debug-info" class="rtm-option-wrapper">
			<h3 class="rtm-option-title"><?php esc_html_e( 'Debug Info', 'buddypress-media' ); ?></h3>
			<table class="form-table rtm-debug-info">
				<tbody>
				<?php
				if ( $this->debug_info ) {
					foreach ( $this->debug_info as $configuration => $value ) {
						?>
						<tr>
						<th scope="row"><?php echo esc_html( $configuration ); ?></th>
						<td><?php echo wp_kses( $value, $allowed_html ); ?></td>
						</tr><?php
					}
				}
				?>
				</tbody>
			</table>
			<div class="rtm-download-debuginfo">
				<form action="<?php echo admin_url( 'admin.php?page=rtmedia-support#debug' ); ?>" method="post">
					<?php wp_nonce_field( 'rtmedia-download-debuginfo','download_debuginfo_wpnonce' ); ?>
					<input type="hidden" name="download_debuginfo" id="download_debuginfo" value="1" />
					<input type="submit" value="<?php esc_html_e( 'Download Debug Info', 'buddypress-media' ); ?>" class="button button-primary" />
				</form>
			</div>
			</div><?php
		}

		/**
		 * Check for migration_required.
		 *
		 * @access public
		 *
		 * @param  void
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
		 * @param  type $page
		 *
		 * @return bool
		 */
		public function migration_html( $page = '' ) {
			$pending_rtmedia_migrate = rtmedia_get_site_option( 'rtMigration-pending-count' );

			$content = ' ';
			$flag    = true;
			if ( ( false === $pending_rtmedia_migrate || 0 === intval( $pending_rtmedia_migrate ) ) ) {
				$content .= esc_html__( 'There is no media found to migrate.', 'buddypress-media' );
				$flag = false;
			}
			$content = apply_filters( 'rtmedia_migration_content_filter', $content );
			if ( $flag ) {
				$content .= ' <div class="rtmedia-migration-support">';
				$content .= ' <p>' . esc_html__( 'Click', 'buddypress-media' ) . ' <a href="' . esc_url( get_admin_url() ) . 'admin.php?page=rtmedia-migration">' . esc_html__( 'here', 'buddypress-media' ) . '</a>' . esc_html__( 'here to migrate media from rtMedia 2.x to rtMedia 3.0+.', 'buddypress-media' ) . '</p>';
				$content .= '</div>';
			}
			?>
			<div id="rtmedia-migration-html">
				<?php echo $content; // @codingStandardsIgnoreLine ?>
			</div>
			<?php
		}

		/**
		 * Generate rtmedia admin form.
		 *
		 * @global type $current_user
		 *
		 * @param  string $form
		 *
		 * @return void
		 */
		public function get_form( $form = '' ) {
			//todo: nonce required
			if ( empty( $form ) ) {
				$form = filter_input( INPUT_POST, 'form' . FILTER_SANITIZE_STRING );
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
					$content = '<h3 class="rtm-option-title">' . esc_html( $meta_title ) . '</h3>';
					$content .= '<p>' .
						sprintf(
							esc_html__( 'If your site has some issues due to rtMedia and you want support, feel free to create a support topic on %s', 'buddypress-media' ),
							'<a target="_blank" href="http://community.rtcamp.com/c/rtmedia/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media">' . esc_html__( 'Community Forum', 'buddypress-media' ) . '</a>.'
						) .
						'</p>';

					$content .= '<p>' .
						sprintf(
							esc_html__( 'If you have any suggestions, enhancements or bug reports, then you can open a new issue on %s', 'buddypress-media' ),
							'<a target="_blank" href="https://github.com/rtCamp/rtmedia/issues/new">' . esc_html__( 'GitHub', 'buddypress-media' ) . '</a>.'
						) .
						'</p>';

					echo $content; // @codingStandardsIgnoreLine
				} else {
					$website = filter_input( INPUT_POST, 'website', FILTER_SANITIZE_URL );
					$subject = filter_input( INPUT_POST, 'subject', FILTER_SANITIZE_STRING );
					$details = filter_input( INPUT_POST, 'details', FILTER_SANITIZE_STRING );
					$server_addr = rtm_get_server_var( 'SERVER_ADDR', 'FILTER_VALIDATE_IP' );
					$remote_addr = rtm_get_server_var( 'REMOTE_ADDR', 'FILTER_VALIDATE_IP' );
					$server_software = rtm_get_server_var( 'SERVER_SOFTWARE', 'FILTER_SANITIZE_STRING' );
					$http_user_agent = rtm_get_server_var( 'HTTP_USER_AGENT', 'FILTER_SANITIZE_STRING' );
					?>
					<h3 class="rtm-option-title"><?php echo esc_html( $meta_title ); ?></h3>
					<div id="support-form" class="bp-media-form rtm-support-form rtm-option-wrapper">

						<div class="rtm-form-filed clearfix">
							<label class="bp-media-label"
							       for="name"><?php esc_html_e( 'Name', 'buddypress-media' ); ?></label>
							<input class="bp-media-input" id="name" type="text" name="name" value="" required/>
							<span class="rtm-tooltip">
								<i class="dashicons dashicons-info rtmicon"></i>
								<span class="rtm-tip">
									<?php esc_html_e( 'Use actual user name which used during purchased.', 'buddypress-media' ); ?>
								</span>
							</span>
						</div>

						<div class="rtm-form-filed clearfix">
							<label class="bp-media-label"
							       for="email"><?php esc_html_e( 'Email', 'buddypress-media' ); ?></label>
							<input id="email" class="bp-media-input" type="text" name="email" value="" required/>
							<span class="rtm-tooltip">
								<i class="dashicons dashicons-info rtmicon"></i>
								<span class="rtm-tip">
									<?php esc_html_e( 'Use email id which used during purchased', 'buddypress-media' ); ?>
								</span>
							</span>
						</div>

						<div class="rtm-form-filed clearfix">
							<label class="bp-media-label"
							       for="website"><?php esc_html_e( 'Website', 'buddypress-media' ); ?></label>
							<input id="website" class="bp-media-input" type="text" name="website"
							       value="<?php echo esc_url( isset( $website ) ? $website : get_bloginfo( 'url' ) ); ?>"
							       required/>
						</div>

						<div class="rtm-form-filed clearfix">
							<label class="bp-media-label"
							       for="subject"><?php esc_html_e( 'Subject', 'buddypress-media' ); ?></label>
							<input id="subject" class="bp-media-input" type="text" name="subject"
							       value="<?php echo esc_attr( isset( $subject ) ? esc_attr( $subject ) : '' ); ?>"
							       required/>
						</div>

						<div class="rtm-form-filed clearfix">
							<label class="bp-media-label"
							       for="details"><?php esc_html_e( 'Details', 'buddypress-media' ); ?></label>
							<textarea id="details" class="bp-media-textarea" name="details"
							          required><?php echo esc_html( isset( $details ) ? esc_textarea( $details ) : '' ); ?></textarea>

							<input type="hidden" name="request_type" value="<?php echo esc_attr( $form ); ?>"/>
							<input type="hidden" name="request_id"
							       value="<?php echo esc_attr( wp_create_nonce( date( 'YmdHis' ) ) ); ?>"/>
							<input type="hidden" name="server_address" value="<?php echo esc_attr( $server_addr ); ?>"/>
							<input type="hidden" name="ip_address" value="<?php echo esc_attr( $remote_addr ); ?>"/>
							<input type="hidden" name="server_type" value="<?php echo esc_attr( $server_software ); ?>"/>
							<input type="hidden" name="user_agent" value="<?php echo esc_attr( $http_user_agent ); ?>"/>
							<input type="hidden" name="debuglog_temp_path" id="debuglog_temp_path" />
						</div>

						<div class="rtm-form-filed clearfix">
							<label class="bp-media-label"
							       for="subject"><?php esc_html_e( 'Attachement', 'buddypress-media' ); ?></label>
							<input id="debuglog" class="bp-media-input" type="file" name="debuglog" />
							<span class="rtm-tooltip">
								<i class="dashicons dashicons-info rtmicon"></i>
								<span class="rtm-tip">
									<?php esc_html_e( 'Allowed file types are : images, documents and texts.', 'buddypress-media' ); ?>
								</span>
							</span>
						</div>
					</div><!-- .submit-bug-box -->

					<div class="rtm-form-filed rtm-button-wrapper clearfix">
						<?php wp_nonce_field( 'rtmedia-support-request','support_wpnonce' ); ?>
						<?php submit_button( 'Submit', 'primary', 'rtmedia-submit-request', false ); ?>
						<?php submit_button( 'Cancel', 'secondary', 'cancel-request', false ); ?>
					</div>

					<?php
				}
			}
		}

		/**
		 * Now submit request.
		 *
		 * @global type $rtmedia
		 *
		 * @param       void
		 *
		 * @return void
		 */
		public function submit_request() {
			$nonce = filter_input( INPUT_POST, 'support_wpnonce', FILTER_SANITIZE_STRING );
			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'rtmedia-support-request' ) ) {
				wp_die(
					'<h1>' . esc_html__( 'Cheatin\' uh?','buddypress-media' ) . '</h1>' .
					'<p>' . esc_html__( 'Can not verify request source.','buddypress-media' ) . '</p>'
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
			} else if ( 'new_feature' === sanitize_text_field( $form_data['request_type'] ) ) {
				$mail_type = 'New Feature Request';
				$title     = esc_html__( 'rtMedia New Feature Request from', 'buddypress-media' );
			} else if ( 'bug_report' === sanitize_text_field( $form_data['request_type'] ) ) {
				$mail_type = 'Bug Report';
				$title     = esc_html__( 'rtMedia Bug Report from', 'buddypress-media' );
			} else {
				$mail_type = 'Bug Report';
				$title     = esc_html__( 'rtMedia Contact from', 'buddypress-media' );
			}
			$message = '<html>
				<head>
					<title>' . strip_tags( $title . get_bloginfo( 'name' ) ) . '</title>
				</head>
				<body>
					<table>
						<tr>
							<td>Name</td><td>' . strip_tags( $form_data['name'] ) . '</td>
						</tr>
						<tr>
							<td>Email</td><td>' . strip_tags( $form_data['email'] ) . '</td>
						</tr>
						<tr>
							<td>Website</td><td>' . strip_tags( $form_data['website'] ) . '</td>
						</tr>
						<tr>
							<td>Subject</td><td>' . strip_tags( $form_data['subject'] ) . '</td>
						</tr>
						<tr>
							<td>Details</td><td>' . strip_tags( $form_data['details'] ) . '</td>
						</tr>
						<tr>
							<td>Request ID</td><td>' . strip_tags( $form_data['request_id'] ) . '</td>
						</tr>
						<tr>
							<td>Server Address</td><td>' . strip_tags( $form_data['server_address'] ) . '</td>
						</tr>
						<tr>
							<td>IP Address</td><td>' . strip_tags( $form_data['ip_address'] ) . '</td>
						</tr>
						<tr>
							<td>Server Type</td><td>' . strip_tags( $form_data['server_type'] ) . '</td>
						</tr>
						<tr>
							<td>User Agent</td><td>' . strip_tags( $form_data['user_agent'] ) . '</td>
						</tr>';
			if ( 'bug_report' === sanitize_text_field( $form_data['request_type'] ) ) {
				$message .= '<tr>
									<td>WordPress Admin Username</td><td>' . strip_tags( $form_data['wp_admin_username'] ) . '</td>
								</tr>
								<tr>
									<td>WordPress Admin Password</td><td>' . strip_tags( $form_data['wp_admin_pwd'] ) . '</td>
								</tr>
								<tr>
									<td>SSH FTP Host</td><td>' . strip_tags( $form_data['ssh_ftp_host'] ) . '</td>
								</tr>
								<tr>
									<td>SSH FTP Username</td><td>' . strip_tags( $form_data['ssh_ftp_username'] ) . '</td>
								</tr>
								<tr>
									<td>SSH FTP Password</td><td>' . strip_tags( $form_data['ssh_ftp_pwd'] ) . '</td>
								</tr>
									';
			}
			$message .= '</table>';
			$message .= '</body>
				</html>';

			add_filter( 'wp_mail_content_type', array( $this, 'rtmedia_mail_content_type' ) );

			$debuglog_temp_path = sanitize_text_field( $form_data['debuglog_temp_path'] );
			/* set attachment path for sending into mail */
			$attachment_file = ( ! empty( $debuglog_temp_path ) ) ? $debuglog_temp_path : '' ;
			$attachments = array( $attachment_file );

			$headers       = 'From: ' . $form_data['name'] . ' <' . $form_data['email'] . '>' . "\r\n";
			$support_email = 'support@rtcamp.com';
			if ( wp_mail( $support_email, '[rtmedia] ' . $mail_type . ' from ' . str_replace( array(
					'http://',
					'https://',
			), '', $form_data['website'] ), stripslashes( $message ), $headers, $attachments ) ) {
				/* delete file after sending it to mail. */
				if ( ! empty( $attachment_file ) ) {
					unlink( $attachment_file );
				}
				echo '<div class="rtmedia-success" style="margin:10px 0;">';
				if ( 'new_feature' === sanitize_text_field( $form_data['request_type'] ) ) {
					echo '<p>' . esc_html__( 'Thank you for your Feedback/Suggestion.', 'buddypress-media' ) . '</p>';
				} else {
					echo '<p>' . esc_html__( 'Thank you for posting your support request.', 'buddypress-media' ) . '</p>';
					echo '<p>' . esc_html__( 'We will get back to you shortly.', 'buddypress-media' ) . '</p>';
				}
				echo '</div>';
			} else {
				echo '<div class="rtmedia-error">';
				echo '<p>' . esc_html__( 'Your server failed to send an email.', 'buddypress-media' ) . '</p>';
				echo '<p>' . esc_html__( 'Kindly contact your server support to fix this.', 'buddypress-media' ) . '</p>';
				echo '<p>' .
					sprintf(
						esc_html__( 'You can alternatively create a support request %s', 'buddypress-media' ),
						'<a target="_blank" href="https://rtmedia.io/premium-support/">' . esc_html__( 'here', 'buddypress-media' ) . '</a>.'
					) .
					'</p>';
				echo '</div>';
			}
			die();
		}

		/**
		 * Write debug info as a text file and download it.
		 *
		 * @param void
		 *
		 * @return void
		 */
		public function download_debuginfo_as_text() {

			header('Content-disposition: attachment; filename=debuginfo.txt');
			header('Content-type: text/plain');
			global $wpdb, $wp_version, $bp;
			$debug_info = array();
			$debug_info['Home URL'] = esc_url( home_url() );
			$debug_info['Site URL'] = esc_url( site_url() );
			$debug_info['PHP'] = esc_html( PHP_VERSION );
			$debug_info['MYSQL'] = esc_html( $wpdb->db_version() );
			$debug_info['WordPress'] = esc_html( $wp_version );
			$debug_info['BuddyPress'] = esc_html( ( isset( $bp->version ) ) ? $bp->version : '-NA-' );
			$debug_info['rtMedia'] = esc_html( RTMEDIA_VERSION );
			$debug_info['OS'] = esc_html( PHP_OS );
			if ( extension_loaded( 'imagick' ) ) {
				$imagickobj = new Imagick();
				$imagick    = $message = preg_replace( " #((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#i", "'<a href=\"$1\" target=\"_blank\">$3</a>$4'", $imagickobj->getversion() );
			} else {
				$imagick['versionString'] = 'Not Installed';
			}
			$debug_info['Imagick'] = $imagick['versionString'];
			if ( extension_loaded( 'gd' ) ) {
				$gd = gd_info();
			} else {
				$gd['GD Version'] = 'Not Installed';
			}
			$debug_info['GD'] = esc_html( $gd['GD Version'] );
			$debug_info['[php.ini] post_max_size'] = esc_html( ini_get( 'post_max_size' ) );
			$debug_info['[php.ini] upload_max_filesize'] = esc_html( ini_get( 'upload_max_filesize' ) );
			$debug_info['[php.ini] memory_limit'] = esc_html( ini_get( 'memory_limit' ) );
			$plugin_info = explode( ',', $this->get_plugin_info() );
			$debug_info['Installed Plugins']      = implode( ', '. PHP_EOL . str_repeat( ' ', 49 ) , $plugin_info );
			$active_theme = wp_get_theme();
			$debug_info['Theme Name'] = esc_html( $active_theme->Name );
			$debug_info['Theme Version'] = esc_html( $active_theme->Version );
			$debug_info['Author URL'] = esc_url( $active_theme->{'Author URI'} );
			$debug_info['Template Overrides'] = implode( ', '. PHP_EOL . str_repeat( ' ', 50 ) , $this->rtmedia_scan_template_files( RTMEDIA_PATH . '/templates/' ) );
			$rtmedia_options = get_option( 'rtmedia-options' );
			$rtmedia_options = array_merge( $debug_info, $rtmedia_options );
			$i=0;
			if( ! empty( $rtmedia_options ) ) {
				echo '==============================================================================' . PHP_EOL;
				echo '================================== Debug Info ================================' . PHP_EOL;
				echo '==============================================================================' . PHP_EOL . PHP_EOL . PHP_EOL;

				foreach ( $rtmedia_options as $option => $value ) {
					echo ucwords( str_replace( '_', ' ', $option ) ) . str_repeat( ' ', 50 - strlen($option) ) . wp_strip_all_tags( $value ) . PHP_EOL;
				}

				readfile( "debuginfo.txt" );
				exit();
			}

		}

	}

}
