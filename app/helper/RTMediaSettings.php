<?php

/**
 * Description of RTMediaSettings
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if ( ! class_exists( 'RTMediaSettings' ) ) {

	class RTMediaSettings {

		/**
		 * Constructor
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			//todo: nonce required
			if ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				add_action( 'admin_init', array( $this, 'settings' ) );

				$rtmedia_option_save = filter_input( INPUT_POST, 'rtmedia-options-save', FILTER_SANITIZE_STRING );
				if ( isset( $rtmedia_option_save ) ) {
					add_action( 'init', array( $this, 'settings' ) );
				}
			}
		}

		/**
		 * Get default options.
		 *
		 * @access public
		 * @global string 'buddypress-media'
		 *
		 * @param  void
		 *
		 * @return array  $defaults
		 */
		public function get_default_options() {
			global $rtmedia;
			$options = $rtmedia->options;

			$defaults = array(
				'general_enableAlbums'              => 1,
				'general_enableAlbums_description'  => 0,
				'general_enableComments'            => 0,
				'general_enableGallerysearch'       => 0,
				'general_enableLikes'               => 1,
				'general_downloadButton'            => 0,
				'general_enableLightbox'            => 1,
				'general_perPageMedia'              => 10,
				'general_display_media'             => 'load_more',
				'general_enableMediaEndPoint'       => 0,
				'general_showAdminMenu'             => 0,
				'general_videothumbs'               => 2,
				'general_jpeg_image_quality'        => 90,
				'general_uniqueviewcount'           => 0,
				'general_viewcount'                 => 0,
				'general_AllowUserData'             => 0,
				'rtmedia_add_linkback'              => 0,
				'rtmedia_affiliate_id'              => '',
				'rtmedia_enable_api'                => 0,
				'general_masonry_layout'            => 0,
				'general_masonry_layout_activity'   => 0,
				'general_direct_upload'             => 0,
			);

			foreach ( $rtmedia->allowed_types as $type ) {
				// invalid keys handled in sanitize method.
				$defaults[ 'allowedTypes_' . $type['name'] . '_enabled' ]  = 0;
				$defaults[ 'allowedTypes_' . $type['name'] . '_featured' ] = 0;
			}

			/* Previous Sizes values from buddypress is migrated */
			foreach ( $rtmedia->default_sizes as $type => $type_value ) {
				foreach ( $type_value as $size => $size_value ) {
					foreach ( $size_value as $dimension => $value ) {
						$defaults[ 'defaultSizes_' . $type . '_' . $size . '_' . $dimension ] = 0;
					}
				}
			}

			/* Privacy */
			$defaults['privacy_enabled']      = 0;
			$defaults['privacy_default']      = 0;
			$defaults['privacy_userOverride'] = 0;

			$defaults['buddypress_enableOnGroup']        = 1;
			$defaults['buddypress_enableOnActivity']     = 1;
			$defaults['buddypress_enableOnComment']      = 1;
			$defaults['buddypress_enableOnProfile']      = 1;
			$defaults['buddypress_limitOnActivity']      = 0;
			$defaults['buddypress_enableNotification']   = 0;
			$defaults['buddypress_mediaLikeActivity']    = 0;
			$defaults['buddypress_mediaCommentActivity'] = 0;
			$defaults['styles_custom']                   = '';
			$defaults['styles_enabled']                  = 1;

			/* default value for add media in comment media */
			$defaults['rtmedia_disable_media_in_commented_media']      = 1;

			if ( isset( $options['general_videothumbs'] ) && is_numeric( $options['general_videothumbs'] ) && intval( $options['general_videothumbs'] ) > 10 ) {
				$defaults['general_videothumbs'] = 10;
			}

			if ( isset( $options['general_jpeg_image_quality'] ) ) {
				if ( is_numeric( $options['general_jpeg_image_quality'] ) ) {
					if ( $options['general_jpeg_image_quality'] > 100 ) {
						$defaults['general_jpeg_image_quality'] = 100;
					} else if ( $options['general_jpeg_image_quality'] < 1 ) {
						$defaults['general_jpeg_image_quality'] = 90;
					}
				} else {
					$defaults['general_jpeg_image_quality'] = 90;
				}
			}

			$defaults = apply_filters( 'rtmedia_general_content_default_values', $defaults );

			return $defaults;
		}

		/**
		 * Register Settings.
		 *
		 * @access public
		 *
		 * @param  type $options
		 *
		 * @return type $options
		 */
		public function sanitize_options( $options ) {
			$defaults = $this->get_default_options();
			$options  = wp_parse_args( $options, $defaults );

			return $options;
		}

		/**
		 * Sanitize before saving the options.
		 *
		 * @access public
		 *
		 * @param  type $options
		 *
		 * @return type $options
		 */
		public function sanitize_before_save_options( $options ) {
			$defaults = $this->get_default_options();

			foreach ( $defaults as $key => $value ) {
				if ( ! isset( $options[ $key ] ) ) {
					$options[ $key ] = '0';
				}
			}

			/* Check if @import is inserted into css or not. If yes then remove that line before save. */
			if ( isset( $options['styles_custom'] ) && ! empty( $options['styles_custom'] ) ) {
				$css = $options['styles_custom'];

				/**
				 * Filters css validation status whether apply it or not.
				 * Return true if you want to validate css.
				 *
				 * @param bool false By default do not apply validation.
				 */
				$apply_css_validation = apply_filters( 'rtmedia_css_validation', false );

				if ( true === $apply_css_validation && preg_match( '/@import\s*(url)?\s*\(?([^;]+?)\)?;/', $css, $matches ) ) {
					$removable_line = $matches[0];
					if ( ! empty( $removable_line ) ) {
						$options['styles_custom'] = str_replace( $removable_line, '', $css );
					}
				}
			}

			if ( isset( $options['general_videothumbs'] ) && intval( $options['general_videothumbs'] ) > 10 ) {
				$options['general_videothumbs'] = 10;
			}

			// Checking if video_thumbnails value is less then 0
			if ( isset( $options['general_videothumbs'] ) && intval( $options['general_videothumbs'] ) <= 0 ) {
				$options['general_videothumbs'] = 2;
			}

			// Checking if number of media perpage is integer or not
			if ( isset( $options['general_perPageMedia'] ) ) {
				if ( intval( $options['general_perPageMedia'] ) < 1 ) {
					$options['general_perPageMedia'] = 10;
				} else if ( ! is_int( $options['general_perPageMedia'] ) ) {
					$options['general_perPageMedia'] = round( $options['general_perPageMedia'] );
				}
			}

			return $options;
		}

		/**
		 * rtmedia settings.
		 *
		 * @access public
		 * @global BPMediaAddon $rtmedia_addon
		 *
		 * @param               void
		 *
		 * @return void
		 */
		public function settings() {
			//todo: nonce required
			global $rtmedia, $rtmedia_addon, $rtmedia_save_setting_single;
			$options          = rtmedia_get_site_option( 'rtmedia-options' );
			$options          = $this->sanitize_options( $options );
			$rtmedia->options = $options;
			// Save Settings first then proceed.
			$rtmedia_option_save = filter_input( INPUT_POST, 'rtmedia-options-save', FILTER_SANITIZE_STRING );
			if ( isset( $rtmedia_option_save ) && current_user_can( 'manage_options' ) ) {
				$options               = filter_input( INPUT_POST, 'rtmedia-options', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
				$options               = $this->sanitize_before_save_options( $options );
				$options               = apply_filters( 'rtmedia_pro_options_save_settings', $options );
				$is_rewrite_rule_flush = apply_filters( 'rtmedia_flush_rewrite_rule', false );
				rtmedia_update_site_option( 'rtmedia-options', $options );
				do_action( 'rtmedia_save_admin_settings', $options );
				if ( $is_rewrite_rule_flush ) {
					flush_rewrite_rules( false );
				}
				$settings_saved = '';
				$setting_save = filter_input( INPUT_GET, 'settings-saved', FILTER_SANITIZE_STRING );
				if ( ! isset( $setting_save ) ) {
					$settings_saved = '&settings-saved=true';
				}

				$http_referer = rtm_get_server_var( 'HTTP_REFERER', 'FILTER_SANITIZE_URL' );
				if ( isset( $http_referer ) ) {
					wp_redirect( $http_referer . $settings_saved );
				}
				global $rtmedia;
				$rtmedia->options = $options;
			}

			if ( function_exists( 'add_settings_section' ) ) {
				$rtmedia_addon = new RTMediaAddon();
				add_settings_section( 'rtm-addons', esc_html__( 'BuddyPress Media Addons for Photos', 'buddypress-media' ), array(
					$rtmedia_addon,
					'get_addons',
				), 'rtmedia-addons' );
				$rtmedia_support = new RTMediaSupport( false );
				add_settings_section( 'rtm-support', esc_html__( 'Support', 'buddypress-media' ), array(
					$rtmedia_support,
					'get_support_content',
				), 'rtmedia-support' );
				$rtmedia_themes = new RTMediaThemes();
				add_settings_section( 'rtm-themes', esc_html__( 'rtMedia Themes', 'buddypress-media' ), array(
					$rtmedia_themes,
					'get_themes',
				), 'rtmedia-themes' );
			}

			if ( ! isset( $rtmedia_save_setting_single ) ) {
				$rtmedia_save_setting_single = true;
			}
		}

		/**
		 * Show network notices.
		 *
		 * @access public
		 *
		 * @param  void
		 *
		 * @return void
		 */
		public function network_notices() {
			$flag = 1;
			if ( rtmedia_get_site_option( 'rtm-media-enable', false ) ) {
				echo '<div id="setting-error-bpm-media-enable" class="error"><p><strong>' . esc_html( rtmedia_get_site_option( 'rtm-media-enable' ) ) . '</strong></p></div>';
				delete_site_option( 'rtm-media-enable' );
				$flag = 0;
			}
			if ( rtmedia_get_site_option( 'rtm-media-type', false ) ) {
				echo '<div id="setting-error-bpm-media-type" class="error"><p><strong>' . esc_html( rtmedia_get_site_option( 'rtm-media-type' ) ) . '</strong></p></div>';
				delete_site_option( 'rtm-media-type' );
				$flag = 0;
			}
			if ( rtmedia_get_site_option( 'rtm-media-default-count', false ) ) {
				echo '<div id="setting-error-bpm-media-default-count" class="error"><p><strong>' . esc_html( rtmedia_get_site_option( 'rtm-media-default-count' ) ) . '</strong></p></div>';
				delete_site_option( 'rtm-media-default-count' );
				$flag = 0;
			}

			if ( rtmedia_get_site_option( 'rtm-recount-success', false ) ) {
				echo '<div id="setting-error-bpm-recount-success" class="updated"><p><strong>' . esc_html( rtmedia_get_site_option( 'rtm-recount-success' ) ) . '</strong></p></div>';
				delete_site_option( 'rtm-recount-success' );
				$flag = 0;
			} elseif ( rtmedia_get_site_option( 'rtm-recount-fail', false ) ) {
				echo '<div id="setting-error-bpm-recount-fail" class="error"><p><strong>' . esc_html( rtmedia_get_site_option( 'rtm-recount-fail' ) ) . '</strong></p></div>';
				delete_site_option( 'rtm-recount-fail' );
				$flag = 0;
			}

			if ( get_site_option( 'rtm-settings-saved' ) && $flag ) {
				echo '<div id="setting-error-bpm-settings-saved" class="updated"><p><strong>' . esc_html( get_site_option( 'rtm-settings-saved' ) ) . '</strong></p></div>';
			}

			delete_site_option( 'rtm-settings-saved' );
		}

		/**
		 * Show allowed types.
		 *
		 * @access public
		 *
		 * @param  void
		 *
		 * @return void
		 */
		public function allowed_types() {
			$allowed_types = rtmedia_get_site_option( 'upload_filetypes', 'jpg jpeg png gif' );
			$allowed_types = explode( ' ', $allowed_types );
			$allowed_types = implode( ', ', $allowed_types );
			echo '<span class="description">' .
				sprintf(
					esc_html__( 'Currently your network allows uploading of the following file types. You can change the settings %s', 'buddypress-media' ),
					'<a href="' . esc_url( network_admin_url( 'settings.php#upload_filetypes' ) ) . '">' . esc_html__( 'here', 'buddypress-media' ) . '</a><br /><code>' . esc_html( $allowed_types ) . '</code>'
				) .
				'</span>';
		}

		/**
		 * Sanitizes the settings
		 *
		 * @access public
		 * @global type $rtmedia_admin
		 *
		 * @param  type $input
		 *
		 * @return type $input
		 */
		public function sanitize( $input ) {
			$rtmedia_options = filter_input( INPUT_POST, 'rtmedia-options', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

			if ( ! isset( $rtmedia_options['videos_enabled'] ) && ! isset( $rtmedia_options['audio_enabled'] ) && ! isset( $rtmedia_options['images_enabled'] ) ) {
				if ( is_multisite() ) {
					rtmedia_update_site_option( 'rtm-media-type', esc_html__( 'Atleast one Media Type Must be selected', 'buddypress-media' ) );
				} else {
					add_settings_error( esc_html__( 'Media Type', 'buddypress-media' ), 'rtm-media-type', esc_html__( 'Atleast one Media Type Must be selected', 'buddypress-media' ) );
				}
				$input['images_enabled'] = 1;
			}

			$input['default_count'] = intval( $rtmedia_options['default_count'] );

			if ( ! is_int( $input['default_count'] ) || ( $input['default_count'] < 0 ) || empty( $input['default_count'] ) ) {
				if ( is_multisite() ) {
					rtmedia_update_site_option( 'rtm-media-default-count', esc_html__( '"Number of media" count value should be numeric and greater than 0.', 'buddypress-media' ) );
				} else {
					add_settings_error( esc_html__( 'Default Count', 'buddypress-media' ), 'rtm-media-default-count', esc_html__( '"Number of media" count value should be numeric and greater than 0.', 'buddypress-media' ) );
				}
				$input['default_count'] = 10;
			}
			if ( is_multisite() ) {
				rtmedia_update_site_option( 'rtm-settings-saved', esc_html__( 'Settings saved.', 'buddypress-media' ) );
			}
			do_action( 'rtmedia_sanitize_settings', $_POST, $input ); // @codingStandardsIgnoreLine

			return $input;
		}

		/**
		 * Show image settings intro.
		 *
		 * @access public
		 *
		 * @param  void
		 *
		 * @return void
		 */
		public function image_settings_intro() {
			if ( is_plugin_active( 'regenerate-thumbnails/regenerate-thumbnails.php' ) ) {
				$regenerate_link = admin_url( '/tools.php?page=regenerate-thumbnails' );
			} else if ( array_key_exists( 'regenerate-thumbnails/regenerate-thumbnails.php', get_plugins() ) ) {
				$regenerate_link = admin_url( '/plugins.php#regenerate-thumbnails' );
			} else {
				$regenerate_link = wp_nonce_url( admin_url( 'update.php?action=install-plugin&plugin=regenerate-thumbnails' ), 'install-plugin_regenerate-thumbnails' );
			}
			echo '<span class="description">' . esc_html__( 'If you make changes to width, height or crop settings, you must use ', 'buddypress-media' ) .
				'<a href="' . esc_url( $regenerate_link ) . '">' . esc_html__( 'Regenerate Thumbnail Plugin', 'buddypress-media' ) . '</a>' .
				esc_html__( ' to regenerate old images.', 'buddypress-media' ) .
				'</span>';
			echo '<div class="clearfix">&nbsp;</div>';
		}

		/**
		 * Output a checkbox for privacy_notice.
		 *
		 * @access public
		 *
		 * @param  void
		 *
		 * @return string $notice
		 */
		public function privacy_notice() {
			if ( current_user_can( 'create_users' ) ) {
				$url = add_query_arg(
					array(
						'page' => 'rtmedia-privacy',
					),
					( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) )
				);

				echo '<div class="error"><p>' . esc_html__( 'BuddyPress Media 2.6 requires a database upgrade. ', 'buddypress-media' ) .
					'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Update Database', 'buddypress-media' ) . '.</a></p></div>';
			}
		}

		/**
		 * Output rtmedia_support_intro.
		 *
		 * @access public
		 *
		 * @param  void
		 *
		 * @return void
		 */
		public function rtmedia_support_intro() {
			echo '<p>' . esc_html__( 'If your site has some issues due to rtMedia and you want one on one support then you can create a support topic on the ', 'buddypress-media' ) .
				'<a target="_blank" href="https://rtmedia.io/support/">' . esc_html__( 'rtMedia Support Page', 'buddypress-media' ) . '</a>.' .
				'</p>';
			echo '<p>' . esc_html__( 'If you have any suggestions, enhancements or bug reports, then you can open a new issue on ', 'buddypress-media' ) .
				'<a target="_blank" href="https://github.com/rtMediaWP/rtmedia/issues/new">' . esc_html__( 'GitHub', 'buddypress-media' ) . '</a>.' .
				'</p>';
		}
	}

}
