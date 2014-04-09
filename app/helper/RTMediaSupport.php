<?php
/**
 * Description of RTMediaSupport
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if ( ! class_exists( 'RTMediaSupport' ) ){

	class RTMediaSupport {

		var $debug_info;
		var $curr_sub_tab;

		public function __construct( $init = true ) {

			if ( ! is_admin() ){
				return;
			}

			$this->curr_sub_tab = "support";
			if ( isset( $_REQUEST[ 'tab' ] ) ){
				$this->curr_sub_tab = $_REQUEST[ 'tab' ];
			}
			//	    if($init) {
			//		$this->debug_info();
			//		if($this->curr_sub_tab == "debug") {
			//		    add_action('rtmedia_admin_page_insert', array($this, 'debug_info_html'), 20);
			//		}
			//		if($this->curr_sub_tab == "migration") {
			//		    add_action('rtmedia_admin_page_insert', array($this, 'migration_html'), 20);
			//		}
			//	    }
			//add_action('admin_init', array($this,'load_service_form'),99);
		}

		public function get_support_content() {
			$tabs = array();
			global $rtmedia_admin;
			$tabs[ ] = array(
				'title' => __( 'Premium Support', 'rtmedia' ), 'name' => __( 'Premium Support', 'rtmedia' ), 'href' => '#support', 'callback' => array( $this, 'call_get_form' )
			);
			$tabs[ ] = array(
				'title' => __( 'Debug Info', 'rtmedia' ), 'name' => __( 'Debug Info', 'rtmedia' ), 'href' => '#debug', 'callback' => array( $this, 'debug_info_html' )
			);
			if ( $this->is_migration_required() ){ //if any un-migrated media is there
				$tabs[ ] = array(
					'title' => __( 'Migration', 'rtmedia' ), 'name' => __( 'Migration', 'rtmedia' ), 'href' => '#migration', 'callback' => array( $this, 'migration_html' )
				);
			}
			?>
			<div id="rtm-support">
				<div class="horizontal-tabs">
					<dl class='tabs' data-tab>
						<?php
						$i = 1;
						foreach ( $tabs as $tab ) {
							$active_class = '';
							if ( $i == 1 ){
								$active_class = 'active';
							}
							$i ++;
							?>
							<dd class="<?php echo $active_class ?>">
								<a id="tab-<?php echo substr( $tab[ 'href' ], 1 ) ?>"
								   title="<?php echo $tab[ 'title' ] ?>" href="<?php echo $tab[ 'href' ] ?>"
								   class="rtmedia-tab-title <?php echo sanitize_title( $tab[ 'name' ] ) ?>"><?php echo $tab[ 'name' ] ?></a>
							</dd>
						<?php
						}
						?>
					</dl>
					<?php
					$k = 1;
					$active_class = '';
					echo "<div class='tabs-content'>";
					foreach ( $tabs as $tab ) {
						$active_class = '';
						if ( $k == 1 ){
							$active_class = ' active';
						}
						$k ++;
						if ( isset ( $tab[ 'icon' ] ) && ! empty ( $tab[ 'icon' ] ) ){
							$icon = '<i class="' . $tab[ 'icon' ] . '"></i>';
						}
						$tab_without_hash = explode( "#", $tab[ 'href' ] );
						$tab_without_hash = $tab_without_hash[ 1 ];
						echo '<div class="row content' . $active_class . '" id="' . $tab_without_hash . '">';
						echo '<div class="large-12 columns">';
						call_user_func( $tab[ 'callback' ] );
						echo '</div>';
						echo '</div>';
					}
					echo "</div>";
					?>
				</div>
			</div>
		<?php

		}

		public function render_support( $page = '' ) {
			global $wp_settings_sections, $wp_settings_fields;

			if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[ $page ] ) ){
				return;
			}

			foreach ( (array)$wp_settings_sections[ $page ] as $section ) {

				if ( $section[ 'callback' ] ){
					call_user_func( $section[ 'callback' ], $section );
				}

				if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section[ 'id' ] ] ) ){
					continue;
				}

				echo '<table class="form-table">';
				do_settings_fields( $page, $section[ 'id' ] );
				echo '</table>';
			}
		}

		public function service_selector() {
			?>
			<div>
				<form name="rtmedia_service_select_form" method="post">
					<p>
						<label class="bp-media-label" for="select_support"><?php _e( 'Service', 'rtmedia' ); ?>:</label>
						<select name="rtmedia_service_select">
							<option
								value="premium_support" <?php if ( $_POST[ 'form' ] == "premium_support" ){
								echo "selected";
							} ?>><?php _e( 'Premium Support', 'rtmedia' ); ?></option>
							<option
								value="bug_report" <?php if ( $_POST[ 'form' ] == "bug_report" ){
								echo "selected";
							} ?>><?php _e( 'Bug Report', 'rtmedia' ); ?></option>
							<option
								value="new_feature" <?php if ( $_POST[ 'form' ] == "new_feature" ){
								echo "selected";
							} ?>><?php _e( 'New Feature', 'rtmedia' ); ?></option>
						</select>
						<input name="support_submit" value="<?php esc_attr_e( 'Submit', 'rtmedia' ); ?>" type="submit"
							   class="button"/>
					</p>
				</form>
			</div>
			<?php
			//$this->get_form("premium_support");
		}

		//	public function get_current_sub_tab() {
		//	    return isset ( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : "support";
		//	}

		//	public function rtmedia_support_sub_tabs ( $active_tab = '' ) {
		//            // Declare local variables
		//            $tabs_html = '';
		//            $idle_class = 'nav-tab';
		//            $active_class = 'nav-tab nav-tab-active';
		//
		//            // Setup core admin tabs
		//            $tabs = array(
		//		array(
		//                    'href' => get_admin_url ( null, add_query_arg ( array( 'page' => 'rtmedia-support' ), 'admin.php' ) ) . "&tab=support",
		//                    'name' => __ ( 'Premium Support', 'rtmedia' ),
		//                    'slug' => 'rtmedia-support&tab=support'
		//                ),
		//                array(
		//                    'href' => get_admin_url ( null, add_query_arg ( array( 'page' => 'rtmedia-support' ), 'admin.php' ) ) . "&tab=debug",
		//                    'name' => __ ( 'Debug Info', 'rtmedia' ),
		//                    'slug' => 'rtmedia-support&tab=debug'
		//                ),
		//		array(
		//                    'href' => get_admin_url ( null, add_query_arg ( array( 'page' => 'rtmedia-support' ), 'admin.php' ) ) . "&tab=migration",
		//                    'name' => __ ( 'Migration', 'rtmedia' ),
		//                    'slug' => 'rtmedia-support&tab=migration'
		//                )
		//            );
		//	    $tabs = apply_filters ( 'rtmedia_support_add_sub_tabs', $tabs );
		//	    // Loop through tabs and build navigation
		//	    $tabs_html = "";
		//            foreach ( array_values ( $tabs ) as $tab_data ) {
		//                $is_current = (bool) ( $tab_data[ 'slug' ] == (RTMediaAdmin::get_current_tab()."&tab=".$this->get_current_sub_tab () ) );
		//		$tab_class = $is_current ? $active_class : $idle_class;
		//                $tabs_html .= '<a href="' . $tab_data[ 'href' ] . '" class="' . $tab_class . '">' . $tab_data[ 'name' ] . '</a>';
		//            }
		//            // Output the tabs
		//            return $tabs_html;
		//
		////            // Do other fun things
		////            do_action('bp_media_admin_tabs');
		//        }

		function call_get_form() {
			if ( isset( $_REQUEST[ 'page' ] ) && $_REQUEST[ 'page' ] == 'rtmedia-support' ){
				//echo "<h2 class='nav-tab-wrapper'>".$this->rtmedia_support_sub_tabs()."</h2>";
				if ( $this->curr_sub_tab == "support" ){
					echo "<div id='rtmedia_service_contact_container'><form name='rtmedia_service_contact_detail' method='post'>";
					$this->get_form( "premium_support" );
					echo "</form></div>";
				}
			}
		}

		//	public function load_service_form() {
		//	    if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'rtmedia-support') {
		//		add_action('rtmedia_admin_page_insert', array($this,'call_get_form'),11);
		//	    }
		//	}

		public function get_plugin_info() {
			$active_plugins = (array)get_option( 'active_plugins', array() );
			if ( is_multisite() ){
				$active_plugins = array_merge( $active_plugins, rtmedia_get_site_option( 'active_sitewide_plugins', array() ) );
			}
			$rtmedia_plugins = array();
			foreach ( $active_plugins as $plugin ) {
				$plugin_data    = @get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
				$version_string = '';
				if ( ! empty( $plugin_data[ 'Name' ] ) ){
					$rtmedia_plugins[ ] = $plugin_data[ 'Name' ] . ' ' . __( 'by', 'rtmedia' ) . ' ' . $plugin_data[ 'Author' ] . ' ' . __( 'version', 'rtmedia' ) . ' ' . $plugin_data[ 'Version' ] . $version_string;
				}
			}
			if ( sizeof( $rtmedia_plugins ) == 0 ){
				return false;
			} else {
				return implode( ', <br/>', $rtmedia_plugins );
			}
		}

		function rtmedia_scan_template_files( $template_path ) {
			$files  = scandir( $template_path );
			$result = array();
			if ( $files ){
				foreach ( $files as $key => $value ) {
					if ( ! in_array( $value, array( ".", ".." ) ) ){
						if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ){
							$sub_files = $this->rtmedia_scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
							foreach ( $sub_files as $sub_file ) {
								$result[ ] = str_replace( ABSPATH . "wp-content/", "", RTMediaTemplate::locate_template( substr( $sub_file, 0, ( sizeof( $sub_file ) - 5 ) ) ) );
								//$result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
							}
						} else {
							if ( $value != "main.php" ){
								$result[ ] = $value;
							}
						}
					}
				}
			}

			return $result;
		}

		public function debug_info() {
			global $wpdb, $wp_version, $bp;
			$debug_info                 = array();
			$debug_info[ 'Home URL' ]   = home_url();
			$debug_info[ 'Site URL' ]   = site_url();
			$debug_info[ 'PHP' ]        = PHP_VERSION;
			$debug_info[ 'MYSQL' ]      = $wpdb->db_version();
			$debug_info[ 'WordPress' ]  = $wp_version;
			$debug_info[ 'BuddyPress' ] = ( isset( $bp->version ) ) ? $bp->version : '-NA-';
			$debug_info[ 'rtMedia' ]    = RTMEDIA_VERSION;
			$debug_info[ 'OS' ]         = PHP_OS;
			if ( extension_loaded( 'imagick' ) ){
				$imagickobj = new Imagick();
				$imagick    = $message = preg_replace( " #((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie", "'<a href=\"$1\" target=\"_blank\">$3</a>$4'", $imagickobj->getversion() );
			} else {
				$imagick[ 'versionString' ] = 'Not Installed';
			}
			$debug_info[ 'Imagick' ] = $imagick[ 'versionString' ];
			if ( extension_loaded( 'gd' ) ){
				$gd = gd_info();
			} else {
				$gd[ 'GD Version' ] = 'Not Installed';
			}
			$debug_info[ 'GD' ]                            = $gd[ 'GD Version' ];
			$debug_info[ '[php.ini] post_max_size' ]       = ini_get( 'post_max_size' );
			$debug_info[ '[php.ini] upload_max_filesize' ] = ini_get( 'upload_max_filesize' );
			$debug_info[ '[php.ini] memory_limit' ]        = ini_get( 'memory_limit' );
			$debug_info[ 'Installed Plugins' ]             = $this->get_plugin_info();
			$active_theme                                  = wp_get_theme();
			$debug_info[ 'Theme Name' ]                    = $active_theme->Name;
			$debug_info[ 'Theme Version' ]                 = $active_theme->Version;
			$debug_info[ 'Author URL' ]                    = $active_theme->{'Author URI'};
			$debug_info[ 'Template Overrides' ]            = implode( ', <br/>', $this->rtmedia_scan_template_files( RTMEDIA_PATH . "/templates/" ) );

			$rtMedia_model = new RTMediaModel();
			$sql           = "select media_type, count(id) as count from {$rtMedia_model->table_name} where blog_id = '" . get_current_blog_id() . "' group by media_type";
			global $wpdb;
			$results = $wpdb->get_results( $sql );
			if ( $results ){
				foreach ( $results as $media ) {
					$debug_info[ "Total " . ucfirst( $media->media_type ) . "s" ] = $media->count;
				}
			}
			$this->debug_info = $debug_info;
		}

		public function debug_info_html() {
			$this->debug_info();
			?>
			<div id="debug-info">

			<table class="form-table">
				<tbody><?php
				if ( $this->debug_info ){
					foreach ( $this->debug_info as $configuration => $value ) {
						?>
						<tr valign="top">
						<th scope="row"><?php echo $configuration; ?></th>
						<td><?php echo $value; ?></td>
						</tr><?php
					}
				}
				?>
				</tbody>
			</table>
			</div><?php

		}

		public function is_migration_required() {
			$pending_rtmedia_migrate = rtmedia_get_site_option( "rtMigration-pending-count" );
			if ( ( $pending_rtmedia_migrate === false || $pending_rtmedia_migrate == 0 ) ){
				return false;
			}

			return true;
		}

		public function migration_html( $page = '' ) {
			$pending_rtmedia_migrate = rtmedia_get_site_option( "rtMigration-pending-count" );

			$content = " ";
			$flag    = true;
			if ( ( $pending_rtmedia_migrate === false || $pending_rtmedia_migrate == 0 ) ){
				$content .= __( 'There is no media found to migrate.', 'rtmedia' );
				$flag = false;
			}
			$content = apply_filters( "rtmedia_migration_content_filter", $content );
			if ( $flag ){
				$content .= ' <div class="rtmedia-migration-support">';
				$content .= ' <p>' . __( 'Click', 'rtmedia' ) . ' <a href="' . get_admin_url() . 'admin.php?page=rtmedia-migration">' . __( 'here', 'rtmedia' ) . '</a>' . __( 'here to migrate media from rtMedia 2.x to rtMedia 3.0+.', 'rtmedia' ) . '</p>';
				$content .= '</div>';
			}
			?>
			<div id="rtmedia-migration-html">
				<?php echo $content; ?>
			</div>
		<?php
		}

		/**
		 *
		 * @global type $current_user
		 *
		 * @param type  $form
		 */
		public function get_form( $form = '' ) {
			if ( empty( $form ) ){
				$form = ( isset( $_POST[ 'form' ] ) ) ? $_POST[ 'form' ] : '';
			}
			if ( $form == "" ){
				$form = "premium_support";
			}
			global $current_user;
			switch ( $form ) {
				case "bug_report":
					$meta_title = __( 'Submit a Bug Report', 'rtmedia' );
					break;
				case "new_feature":
					$meta_title = __( 'Submit a New Feature Request', 'rtmedia' );
					break;
				case "premium_support":
					$meta_title = __( 'Submit a Premium Support Request', 'rtmedia' );
					break;
			}

			if ( $form == "premium_support" ){
				if ( ! defined( "RTMEDIA_PRO_VERSION" ) ){
					$content = '<p>' . __( 'If your site has some issues due to BuddyPress Media and you want one on one support then you can create a support topic on the <a target="_blank" href="http://rtcamp.com/groups/buddypress-media/forum/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media">rtCamp Support Forum</a>.', 'rtmedia' ) . '</p>';
					$content .= '<p>' . __( 'If you have any suggestions, enhancements or bug reports, then you can open a new issue on <a target="_blank" href="https://github.com/rtCamp/buddypress-media/issues/new">GitHub</a>.', 'rtmedia' ) . '</p>';

					echo $content;

				} else {
					?>
					<h3><?php echo $meta_title; ?></h3>
					<div id="support-form" class="bp-media-form">
						<ul>
							<li>
								<label class="bp-media-label" for="name"><?php _e( 'Name', 'rtmedia' ); ?>
									:</label><input class="bp-media-input" id="name" type="text" name="name"
													value="<?php echo ( isset( $_REQUEST[ 'name' ] ) ) ? esc_attr( stripslashes( trim( $_REQUEST[ 'name' ] ) ) ) : $current_user->display_name; ?>"
													required/>
							</li>
							<li>
								<label class="bp-media-label" for="email"><?php _e( 'Email', 'rtmedia' ); ?>
									:</label><input id="email" class="bp-media-input" type="text" name="email"
													value="<?php echo ( isset( $_REQUEST[ 'email' ] ) ) ? esc_attr( stripslashes( trim( $_REQUEST[ 'email' ] ) ) ) : get_option( 'admin_email' ); ?>"
													required/>
							</li>
							<li>
								<label class="bp-media-label" for="website"><?php _e( 'Website', 'rtmedia' ); ?>
									:</label><input id="website" class="bp-media-input" type="text" name="website"
													value="<?php echo ( isset( $_REQUEST[ 'website' ] ) ) ? esc_attr( stripslashes( trim( $_REQUEST[ 'website' ] ) ) ) : get_bloginfo( 'url' ); ?>"
													required/>
							</li>
							<li>
								<label class="bp-media-label" for="phone"><?php _e( 'Phone', 'rtmedia' ); ?>
									:</label><input class="bp-media-input" id="phone" type="text" name="phone"
													value="<?php echo ( isset( $_REQUEST[ 'phone' ] ) ) ? esc_attr( stripslashes( trim( $_REQUEST[ 'phone' ] ) ) ) : ''; ?>"/>
							</li>
							<li>
								<label class="bp-media-label" for="subject"><?php _e( 'Subject', 'rtmedia' ); ?>
									:</label><input id="subject" class="bp-media-input" type="text" name="subject"
													value="<?php echo ( isset( $_REQUEST[ 'subject' ] ) ) ? esc_attr( stripslashes( trim( $_REQUEST[ 'subject' ] ) ) ) : ''; ?>"
													required/>
							</li>
							<li>
								<label class="bp-media-label" for="details"><?php _e( 'Details', 'rtmedia' ); ?>
									:</label><textarea id="details" class="bp-media-textarea" type="text" name="details"
													   required/><?php echo ( isset( $_REQUEST[ 'details' ] ) ) ? esc_textarea( stripslashes( trim( $_REQUEST[ 'details' ] ) ) ) : ''; ?></textarea>
							</li>
							<input type="hidden" name="request_type" value="<?php echo $form; ?>"/>
							<input type="hidden" name="request_id"
								   value="<?php echo wp_create_nonce( date( 'YmdHis' ) ); ?>"/>
							<input type="hidden" name="server_address"
								   value="<?php echo $_SERVER[ 'SERVER_ADDR' ]; ?>"/>
							<input type="hidden" name="ip_address" value="<?php echo $_SERVER[ 'REMOTE_ADDR' ]; ?>"/>
							<input type="hidden" name="server_type"
								   value="<?php echo $_SERVER[ 'SERVER_SOFTWARE' ]; ?>"/>
							<input type="hidden" name="user_agent"
								   value="<?php echo $_SERVER[ 'HTTP_USER_AGENT' ]; ?>"/>

						</ul>
					</div><!-- .submit-bug-box --><?php if ( $form == 'bug_report' ){ ?>
						<h3><?php _e( 'Additional Information', 'rtmedia' ); ?></h3>
						<div id="support-form" class="bp-media-form">
							<ul>

								<li>
									<label class="bp-media-label"
										   for="wp_admin_username"><?php _e( 'Your WP Admin Login:', 'rtmedia' ); ?></label><input
										class="bp-media-input" id="wp_admin_username" type="text"
										name="wp_admin_username"
										value="<?php echo ( isset( $_REQUEST[ 'wp_admin_username' ] ) ) ? esc_attr( stripslashes( trim( $_REQUEST[ 'wp_admin_username' ] ) ) ) : $current_user->user_login; ?>"/>
								</li>
								<li>
									<label class="bp-media-label"
										   for="wp_admin_pwd"><?php _e( 'Your WP Admin password:', 'rtmedia' ); ?></label><input
										class="bp-media-input" id="wp_admin_pwd" type="password" name="wp_admin_pwd"
										value="<?php echo ( isset( $_REQUEST[ 'wp_admin_pwd' ] ) ) ? esc_attr( stripslashes( trim( $_REQUEST[ 'wp_admin_pwd' ] ) ) ) : ''; ?>"/>
								</li>
								<li>
									<label class="bp-media-label"
										   for="ssh_ftp_host"><?php _e( 'Your SSH / FTP host:', 'rtmedia' ); ?></label><input
										class="bp-media-input" id="ssh_ftp_host" type="text" name="ssh_ftp_host"
										value="<?php echo ( isset( $_REQUEST[ 'ssh_ftp_host' ] ) ) ? esc_attr( stripslashes( trim( $_REQUEST[ 'ssh_ftp_host' ] ) ) ) : ''; ?>"/>
								</li>
								<li>
									<label class="bp-media-label"
										   for="ssh_ftp_username"><?php _e( 'Your SSH / FTP login:', 'rtmedia' ); ?></label><input
										class="bp-media-input" id="ssh_ftp_username" type="text" name="ssh_ftp_username"
										value="<?php echo ( isset( $_REQUEST[ 'ssh_ftp_username' ] ) ) ? esc_attr( stripslashes( trim( $_REQUEST[ 'ssh_ftp_username' ] ) ) ) : ''; ?>"/>
								</li>
								<li>
									<label class="bp-media-label"
										   for="ssh_ftp_pwd"><?php _e( 'Your SSH / FTP password:', 'rtmedia' ); ?></label><input
										class="bp-media-input" id="ssh_ftp_pwd" type="password" name="ssh_ftp_pwd"
										value="<?php echo ( isset( $_REQUEST[ 'ssh_ftp_pwd' ] ) ) ? esc_attr( stripslashes( trim( $_REQUEST[ 'ssh_ftp_pwd' ] ) ) ) : ''; ?>"/>
								</li>
							</ul>
						</div><!-- .submit-bug-box --><?php } ?>

					<?php submit_button( 'Submit', 'primary', 'rtmedia-submit-request', false ); ?>
					<?php submit_button( 'Cancel', 'secondary', 'cancel-request', false ); ?>
				<?php
				}
			}

			//            if (DOING_AJAX) {
			//                die();
			//            }
		}

		/**
		 *
		 * @global type $rtmedia
		 */
		public function submit_request() {
			$this->debug_info();
			global $rtmedia;
			$form_data = wp_parse_args( $_POST[ 'form_data' ] );
			foreach ( $form_data as $key => $formdata ) {
				if ( $formdata == "" && $key != "phone" ){
					echo "false";
					die();
				}
			}
			if ( $form_data[ 'request_type' ] == 'premium_support' ){
				$mail_type = 'Premium Support';
				$title     = __( 'rtMedia Premium Support Request from', 'rtmedia' );
			} elseif ( $form_data[ 'request_type' ] == 'new_feature' ) {
				$mail_type = 'New Feature Request';
				$title     = __( 'rtMedia New Feature Request from', 'rtmedia' );
			} elseif ( $form_data[ 'request_type' ] == 'bug_report' ) {
				$mail_type = 'Bug Report';
				$title     = __( 'rtMedia Bug Report from', 'rtmedia' );
			} else {
				$mail_type = 'Bug Report';
				$title     = __( 'rtMedia Contact from', 'rtmedia' );
			}
			$message = '<html>
                            <head>
                                    <title>' . $title . get_bloginfo( 'name' ) . '</title>
                            </head>
                            <body>
				<table>
                                    <tr>
                                        <td>Name</td><td>' . strip_tags( $form_data[ 'name' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Email</td><td>' . strip_tags( $form_data[ 'email' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Website</td><td>' . strip_tags( $form_data[ 'website' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Phone</td><td>' . strip_tags( $form_data[ 'phone' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Subject</td><td>' . strip_tags( $form_data[ 'subject' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Details</td><td>' . strip_tags( $form_data[ 'details' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Request ID</td><td>' . strip_tags( $form_data[ 'request_id' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Server Address</td><td>' . strip_tags( $form_data[ 'server_address' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>IP Address</td><td>' . strip_tags( $form_data[ 'ip_address' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Server Type</td><td>' . strip_tags( $form_data[ 'server_type' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>User Agent</td><td>' . strip_tags( $form_data[ 'user_agent' ] ) . '</td>
                                    </tr>';
			if ( $form_data[ 'request_type' ] == 'bug_report' ){
				$message .= '<tr>
                                        <td>WordPress Admin Username</td><td>' . strip_tags( $form_data[ 'wp_admin_username' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>WordPress Admin Password</td><td>' . strip_tags( $form_data[ 'wp_admin_pwd' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>SSH FTP Host</td><td>' . strip_tags( $form_data[ 'ssh_ftp_host' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>SSH FTP Username</td><td>' . strip_tags( $form_data[ 'ssh_ftp_username' ] ) . '</td>
                                    </tr>
                                    <tr>
                                        <td>SSH FTP Password</td><td>' . strip_tags( $form_data[ 'ssh_ftp_pwd' ] ) . '</td>
                                    </tr>
                                    ';
			}
			$message .= '</table>';
			if ( $this->debug_info ){
				$message .= '<h3>' . __( 'Debug Info', 'rtmedia' ) . '</h3>';
				$message .= '<table>';
				foreach ( $this->debug_info as $configuration => $value ) {
					$message .= '<tr>
                                    <td style="vertical-align:top">' . $configuration . '</td><td>' . $value . '</td>
                                </tr>';
				}
				$message .= '</table>';
			}
			$message .= '</body>
                </html>';
			add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
			$headers = 'From: ' . $form_data[ 'name' ] . ' <' . $form_data[ 'email' ] . '>' . "\r\n";
			if ( isset( $rtmedia->support_email ) ){
				$support_email = $rtmedia->support_email;
			} else {
				$support_email = "support@rtcamp.com";
			}
			$support_email = "support@rtcamp.com";
			if ( wp_mail( $support_email, '[rtmedia] ' . $mail_type . ' from ' . str_replace( array( 'http://', 'https://' ), '', $form_data[ 'website' ] ), $message, $headers ) ){
				echo '<div class="rtmedia-success" style="margin:10px 0;">';
				if ( $form_data[ 'request_type' ] == 'new_feature' ){
					echo '<p>' . __( 'Thank you for your Feedback/Suggestion.', 'rtmedia' ) . '</p>';
				} else {
					echo '<p>' . __( 'Thank you for posting your support request.', 'rtmedia' ) . '</p>';
					echo '<p>' . __( 'We will get back to you shortly.', 'rtmedia' ) . '</p>';
				}
				echo '</div>';
			} else {
				echo '<div class="rtmedia-error">';
				echo '<p>' . __( 'Your server failed to send an email.', 'rtmedia' ) . '</p>';
				echo '<p>' . __( 'Kindly contact your server support to fix this.', 'rtmedia' ) . '</p>';
				echo '<p>' . sprintf( __( 'You can alternatively create a support request <a href="%s">here</a>', 'rtmedia' ), $rtmedia->support_url ) . '</p>';
				echo '</div>';
			}
			die();
		}

	}

}