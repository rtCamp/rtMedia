<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaFormHandler
 *
 * @author udit
 */
class RTMediaFormHandler {

	public static function selectBox( $args ) {
		$defaults = array(
			'key'       => '',
			'desc'      => '',
			'default'   => '',
			'show_desc' => false,
			'selects'   => array(),
		);
		$args     = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( ! empty( $key ) ) {
			$args['name'] = 'rtmedia-options[' . $key . ']';
		}

		$args['rtForm_options'] = array();
		if ( ! empty( $selects ) ) {
			foreach ( $selects as $value => $key ) {
				$args['rtForm_options'][] = array(
					$key       => $value,
					'selected' => ( $default === $value ) ? true : false,
				);
			}
		}

		$chkObj = new rtForm();
		$chkObj->display_select( $args );
	}

	/**
	 * Show rtmedia textarea in admin options.
	 *
	 * @access static
	 *
	 * @param  array $args
	 * @param  bool $echo
	 *
	 * @return string $chkObj->get_textarea( $args )
	 */
	public static function textarea( $args, $echo = true ) {
		$defaults = array(
			'key'       => '',
			'desc'      => '',
			'show_desc' => false,
		);
		$args     = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( ! isset( $value ) ) {
			trigger_error( esc_html__( 'Please provide a "value" in the argument.', 'buddypress-media' ) );

			return;
		}

		if ( ! empty( $key ) ) {
			$args['name'] = 'rtmedia-options[' . $key . ']';
		}

		$args['rtForm_options'] = array( array( '' => 1, 'checked' => $value ) );

		$chkObj = new rtForm();

		if ( $echo ) {
			$chkObj->display_textarea( $args );
		} else {
			return $chkObj->get_textarea( $args ); // @codingStandardsIgnoreLine
		}
		return;
	}

	/**
	 * Show rtmedia checkbox in admin options.
	 *
	 * @access static
	 *
	 * @param  array $args
	 * @param  bool $echo
	 *
	 * @return string $chkObj->get_switch( $args )
	 */
	public static function checkbox( $args, $echo = true ) {
		$defaults = array(
			'key'       => '',
			'desc'      => '',
			'show_desc' => false,
		);
		$args     = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( ! isset( $value ) ) {
			trigger_error( esc_html__( 'Please provide a "value" in the argument.', 'buddypress-media' ) );

			return;
		}

		if ( ! empty( $key ) ) {
			$args['name'] = 'rtmedia-options[' . $key . ']';
		}

		$args['rtForm_options'] = array( array( '' => 1, 'checked' => $value ) );

		$chkObj = new rtForm();

		if ( $echo ) {
			$chkObj->display_switch( $args );
		} else {
			return $chkObj->get_switch( $args );
		}
		return;
	}

	/**
	 * Show rtmedia radio in admin options.
	 *
	 * @access static
	 *
	 * @param  array $args
	 *
	 * @return void
	 */
	public static function radio( $args ) {
		$defaults = array(
			'key'       => '',
			'radios'    => array(),
			'default'   => '',
			'show_desc' => false,
		);
		$args     = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( 2 > count( $radios ) ) {
			trigger_error( esc_html__( 'Need to specify atleast two radios, else use a checkbox instead', 'buddypress-media' ) );

			return;
		}

		if ( ! empty( $key ) ) {
			$args['name'] = 'rtmedia-options[' . $key . ']';
		}

		$args['rtForm_options'] = array();
		foreach ( $radios as $value => $key ) {
			$args['rtForm_options'][] = array(
				$key      => $value,
				'checked' => ( $default == $value ) ? true : false,
			);
		}

		$objRad = new rtForm();
		$objRad->display_radio( $args );
	}

	/**
	 * Show rtmedia dimensions in admin options.
	 *
	 * @access static
	 * @return void
	 */
	public static function dimensions( $args ) {
		$dmnObj = new rtDimensions();
		$dmnObj->display_dimensions( $args );
	}

	/**
	 * Show rtmedia number in admin options.
	 *
	 * @access static
	 *
	 * @param  array $args
	 *
	 * @return void
	 */
	public static function number( $args ) {
		global $rtmedia;
		$options  = $rtmedia->options;
		$defaults = array(
			'key'  => '',
			'desc' => '',
		);
		$args     = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( ! isset( $value ) ) {
			trigger_error( esc_html__( 'Please provide a "value" in the argument.', 'buddypress-media' ) );

			return;
		}

		if ( ! empty( $key ) ) {
			$args['name'] = 'rtmedia-options[' . $key . ']';
		}

		$args['value'] = $value;

		$numObj = new rtForm();
		$numObj->display_number( $args );
	}

	/**
	 * Show rtmedia textbox in admin options.
	 *
	 * @access static
	 *
	 * @param  array $args
	 *
	 * @return void
	 */
	public static function textbox( $args ) {
		global $rtmedia;
		$options  = $rtmedia->options;
		$defaults = array(
			'key'  => '',
			'desc' => '',
		);
		$args     = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( ! isset( $value ) ) {
			trigger_error( esc_html__( 'Please provide a "value" in the argument.', 'buddypress-media' ) );

			return;
		}

		if ( ! empty( $key ) ) {
			$args['name'] = 'rtmedia-options[' . $key . ']';
		}

		$args['value'] = $value;

		$numObj = new rtForm();
		$numObj->display_textbox( $args );
	}

	/**
	 * extract settings.
	 *
	 * @access static
	 *
	 * @param  array $options
	 * @param  string $section_name
	 *
	 * @return array  $section
	 */
	static function extract_settings( $section_name, $options ) {
		$section = array();
		foreach ( $options as $key => $value ) {
			$compare = strncmp( $key, $section_name, strlen( $section_name ) );
			if ( 0 === $compare ) {
				$section[ $key ] = $value;
			}
		}

		return $section;
	}

	/**
	 * display render options.
	 *
	 * @access static
	 *
	 * @param  array $options
	 *
	 * @return array  $render
	 */
	static function display_render_options( $options ) {
		$radios               = array();
		$radios['load_more']  = '<strong>' . esc_html__( 'Load More', 'buddypress-media' ) . '</strong>';
		$radios['pagination'] = '<strong>' . esc_html__( 'Pagination', 'buddypress-media' ) . '</strong>';

		if ( is_plugin_active( 'regenerate-thumbnails/regenerate-thumbnails.php' ) ) {
			$regenerate_link = admin_url( '/tools.php?page=regenerate-thumbnails' );
		} elseif ( array_key_exists( 'regenerate-thumbnails/regenerate-thumbnails.php', get_plugins() ) ) {
			$regenerate_link = admin_url( '/plugins.php#regenerate-thumbnails' );
		} else {
			$regenerate_link = wp_nonce_url( admin_url( 'update.php?action=install-plugin&plugin=regenerate-thumbnails' ), 'install-plugin_regenerate-thumbnails' );
		}

		$render = array(
			'general_enableComments' => array(
				'title'    => esc_html__( 'Allow user to comment on uploaded media', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'general_enableComments',
					'value' => $options['general_enableComments'],
					'desc'  => esc_html__( 'This will display the comment form and comment listing on single media pages as well as inside lightbox (if lightbox is enabled).', 'buddypress-media' ),
				),
				'group'    => '10',
			),
			'general_enableGallerysearch' => array(
				'title' => esc_html__( 'Enable gallery media search', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args' => array(
					'key' => 'general_enableGallerysearch',
					'value' => $options['general_enableGallerysearch'],
					'desc' => esc_html__( 'This will enable the search box in gallery page.', 'buddypress-media' ),
				),
				'group' => '14',
			),
			'general_enableLikes' => array(
				'title'    => __( 'Enable likes for media', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args' => array(
					'key' => 'general_enableLikes',
					'value' => $options['general_enableLikes'],
					'desc' => __( 'Enabling this setting will add like feature for media.', 'buddypress-media' ),
				),
				'group' => '11',
			),
			'general_enableLightbox' => array(
				'title'    => esc_html__( 'Use lightbox to display media', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'general_enableLightbox',
					'value' => $options['general_enableLightbox'],
					'desc'  => esc_html__( 'View single media in facebook style lightbox.', 'buddypress-media' ),
				),
				'group'    => '15',
			),
			'general_perPageMedia'   => array(
				'title'    => esc_html__( 'Number of media per page', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'number' ),
				'args'     => array(
					'key'   => 'general_perPageMedia',
					'value' => $options['general_perPageMedia'],
					'class' => array( 'rtmedia-setting-text-box' ),
					'desc'  => esc_html__( 'Number of media items you want to show per page on front end.', 'buddypress-media' ),
					'min'   => 1,
				),
				'group'    => '15',
			),
			'general_display_media'  => array(
				'title'    => esc_html__( 'Media display pagination option', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'radio' ),
				'args'     => array(
					'key'     => 'general_display_media',
					'radios'  => $radios,
					'default' => $options['general_display_media'],
					'desc'    => esc_html__( 'Choose whether you want the load more button or pagination buttons.', 'buddypress-media' ),
					'class'   => array( 'rtmedia-load-more-radio' ),
				),
				'group'    => '15',
			),
			'general_masonry_layout' => array(
				'title'         => esc_html__( 'Enable', 'buddypress-media' ) . ' <a href="http://masonry.desandro.com/" target="_blank">Masonry</a> ' . esc_html__( 'Cascading grid layout', 'buddypress-media' ),
				'callback'      => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'          => array(
					'key'   => 'general_masonry_layout',
					'value' => $options['general_masonry_layout'],
					'desc'  => esc_html__( 'If you enable masonry view, it is advisable to', 'buddypress-media' ) . ' <a href="' . $regenerate_link . '">regenerate thumbnail</a> ' . esc_html__( 'for masonry view.', 'buddypress-media' ),
					'class' => array( 'rtm_enable_masonry_view' ),
				),
				'group'         => '18',
				'after_content' => esc_html__( 'You might need to', 'buddypress-media' ) . ' <a id="rtm-masonry-change-thumbnail-info" href="' . get_admin_url() . 'admin.php?page=rtmedia-settings#rtmedia-sizes">' . esc_html__( 'change thumbnail size', 'buddypress-media' ) . '</a> ' . esc_html__( 'and uncheck the crop box for thumbnails.', 'buddypress-media' ) . '<br /><br />' . esc_html__( 'To set gallery for fixed width, set image height to 0 and width as per your requirement and vice-versa.', 'buddypress-media' ),
			),
			'general_masonry_layout_activity' => array(
				'title'         => esc_html__( 'Enable Masonry Cascading grid layout for activity', 'buddypress-media' ),
				'callback'      => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'          => array(
					'key'   => 'general_masonry_layout_activity',
					'value' => $options['general_masonry_layout_activity'],
					'desc'  => esc_html__( 'If you enable masonry view, it is advisable to', 'buddypress-media' ) . ' <a href="' . $regenerate_link . '">regenerate thumbnail</a> ' . esc_html__( 'for masonry view.', 'buddypress-media' ),
					'class' => array( 'rtm_enable_masonry_view' ),
				),
				'depends'       => 'general_masonry_layout',
				'group'         => '18',
			),
			'general_direct_upload'  => array(
				'title'    => esc_html__( 'Enable Direct Upload', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'general_direct_upload',
					'value' => $options['general_direct_upload'],
					'desc'  => esc_html__( 'Uploading media directly as soon as it gets selected.', 'buddypress-media' ),
				),
				'group'    => '19',
			),
		);

		// If buddypress is not active, then remove the option from rtMedia settings.
		if ( ! is_plugin_active( 'buddypress/bp-loader.php' ) ) {
			unset( $render['general_masonry_layout_activity'] );
		}

		return $render;
	}

	/**
	 * display content.
	 *
	 * @access static
	 *
	 * @param  void
	 *
	 * @return void
	 */
	public static function display_content() {
		global $rtmedia;
		$options			= $rtmedia->options;
		$render_options		= self::display_render_options( $options );
		$render_options		= apply_filters( 'rtmedia_display_content_add_itmes', $render_options, $options );
		$general_group		= array();
		$general_group[10]	= esc_html__( 'Single Media View', 'buddypress-media' );
		$general_group[11]	= esc_html__( 'Media Likes', 'buddypress-media' );
		$general_group[15]	= esc_html__( 'List Media View', 'buddypress-media' );
		$general_group[18]	= esc_html__( 'Masonry View', 'buddypress-media' );
		$general_group[19]	= esc_html__( 'Direct Upload', 'buddypress-media' );
		$general_group[14]	= esc_html__( 'Gallery Media Search', 'buddypress-media' );
		$general_group		= apply_filters( 'rtmedia_display_content_groups', $general_group );
		ksort( $general_group );
		self::render_tab_content( $render_options, $general_group, 20 );
	}

	/**
	 * render general content.
	 *
	 * @access static
	 *
	 * @param  array $options
	 *
	 * @return array $render
	 */
	static function render_general_content( $options ) {
		$render = array(
			'general_AllowUserData' => array(
				'title'    => esc_html__( 'Allow usage data tracking', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'general_AllowUserData',
					'value' => $options['general_AllowUserData'],
					'desc'  => esc_html__( 'To make rtMedia better compatible with your sites, you can help the rtMedia team learn what themes and plugins you are using. No private information about your setup will be sent during tracking.', 'buddypress-media' ),
				),
			),
			'general_showAdminMenu' => array(
				'title'    => esc_html__( 'Admin bar menu integration', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'general_showAdminMenu',
					'value' => $options['general_showAdminMenu'],
					'desc'  => esc_html__( 'Add rtMedia menu to WordPress admin bar for easy access to settings and moderation page (if enabled).', 'buddypress-media' ),
				),
				'group'    => 10,
			), //
			'rtmedia_add_linkback'  => array(
				'title'    => esc_html__( 'Add a link to rtMedia in footer', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'rtmedia_add_linkback',
					'value' => $options['rtmedia_add_linkback'],
					'desc'  => esc_html__( 'Help us promote rtMedia.', 'buddypress-media' ),
				),
				'group'    => 100,
			), //
			'rtmedia_affiliate_id'  => array(
				'title'         => esc_html__( 'Also add my affiliate-id to rtMedia footer link', 'buddypress-media' ),
				'callback'      => array( 'RTMediaFormHandler', 'textbox' ),
				'args'          => array(
					'key'   => 'rtmedia_affiliate_id',
					'value' => $options['rtmedia_affiliate_id'],
					'desc'  => esc_html__( 'Add your affiliate-id along with footer link and get rewarded by our affiliation program.', 'buddypress-media' ),
				),
				'group'         => 100,
				'depends'       => 'rtmedia_add_linkback',
				'after_content' => esc_html__( 'Signup for', 'buddypress-media' ) . ' rtMedia ' . esc_html__( 'affiliate program', 'buddypress-media' ) . ' <a href="https://rtmedia.io/affiliates/">' . esc_html__( 'here', 'buddypress-media' ) . '</a>',
			), //
			'rtmedia_enable_api'    => array(
				'title'         => esc_html__( 'Enable JSON API', 'buddypress-media' ),
				'callback'      => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'          => array(
					'key'   => 'rtmedia_enable_api',
					'value' => $options['rtmedia_enable_api'],
					'desc'  => esc_html__( 'This will allow handling API requests for rtMedia sent through any mobile app.', 'buddypress-media' ),
				),
				'group'         => 80,
				'after_content' => esc_html__( 'You can refer to the API document from', 'buddypress-media' ) . ' <a href="https://rtmedia.io/docs/developers/json-api/">' . esc_html__( 'here', 'buddypress-media' ) . '</a>',
			), //
		);

		return $render;
	}

	/**
	 * Define general_content
	 *
	 * @access static
	 *
	 * @return void
	 */
	static function general_content() {
		global $rtmedia;
		$options            = $rtmedia->options;
		$render_options     = self::render_general_content( $options );
		$render_options     = apply_filters( 'rtmedia_general_content_add_itmes', $render_options, $options );
		$general_group      = array();
		$general_group[10]  = esc_html__( 'Admin Settings', 'buddypress-media' );
		$general_group[80]  = esc_html__( 'API Settings', 'buddypress-media' );
		$general_group[90]  = esc_html__( 'Miscellaneous', 'buddypress-media' );
		$general_group[100] = esc_html__( 'Footer Link', 'buddypress-media' );
		$general_group      = apply_filters( 'rtmedia_general_content_groups', $general_group );
		ksort( $general_group );
		self::render_tab_content( $render_options, $general_group, 90 );
	}

	/**
	 * Get type details
	 *
	 * @access static
	 *
	 * @param  array $allowed_types
	 * @param  string $key
	 *
	 * @return array  $data
	 */
	static function get_type_details( $allowed_types, $key ) {
		foreach ( $allowed_types as $type ) {
			if ( $type['name'] === $key ) {
				$data = array(
					'name' => $type['label'],
					'extn' => $type['extn'],
				);
				if ( isset( $type['settings_visibility'] ) ) {
					$data['settings_visibility'] = $type['settings_visibility'];
				}

				return $data;
			}
		}
		return false;
	}

	/**
	 * Define types_render_options.
	 *
	 * @access static
	 *
	 * @param  array $options
	 *
	 * @return array  $render
	 */
	static function types_render_options( $options ) {
		$render             = array();
		$allowed_media_type = rtmedia_get_allowed_types();

		foreach ( $options as $key => $value ) {
			$data = explode( '_', $key );
			if ( ! isset( $render[ $data[1] ] ) ) {
				$render[ $data[1] ] = self::get_type_details( $allowed_media_type, $data[1] );
			}
		}

		foreach ( $options as $key => $value ) {
			$data                           = explode( '_', $key );
			$render[ $data[1] ][ $data[2] ] = $value;
		}

		return $render;
	}

	/**
	 * Define types_content.
	 *
	 * @access static
	 *
	 * @param  void
	 *
	 * @return void
	 */
	public static function types_content() {
		global $rtmedia;
		$options = self::extract_settings( 'allowedTypes', $rtmedia->options );

		$render_data = self::types_render_options( $options );
		?>
		<div class="rtm-option-wrapper">
			<?php do_action( 'rtmedia_media_type_setting_message' ); ?>

			<h3 class="rtm-option-title">
				<?php esc_html_e( 'Media Types Settings', 'buddypress-media' ); ?>
			</h3>

			<table class="form-table">

				<?php do_action( 'rtmedia_type_settings_before_heading' ); ?>

				<tr>
					<th><strong><?php esc_html_e( 'Media Type', 'buddypress-media' ) ?></strong></th>

					<th>

						<span class="rtm-tooltip bottom">
							<strong class="rtm-title"><?php esc_html_e( 'Allow Upload', 'buddypress-media' ); ?></strong>
							<span class="rtm-tip-top">
								<?php esc_html_e( 'Allows you to upload a particular media type on your post.', 'buddypress-media' ); ?>
							</span>
						</span>
					</th>

					<th>

						<span class="rtm-tooltip bottom">
							<strong class="rtm-title"><?php esc_html_e( 'Set Featured', 'buddypress-media' ); ?></strong>
							<span class="rtm-tip-top">
								<?php esc_html_e( 'Place a specific media as a featured content on the post.', 'buddypress-media' ); ?>
							</span>
						</span>
					</th>

					<?php do_action( 'rtmedia_type_setting_columns_title' ) ?>
				</tr>

				<?php
				do_action( 'rtmedia_type_settings_after_heading' );

				foreach ( $render_data as $key => $section ) {
					if ( isset( $section['settings_visibility'] ) && true === $section['settings_visibility'] ) {
						do_action( 'rtmedia_type_settings_before_body' );

						// allow upload
						$uplaod_args           = array(
							'key'   => 'allowedTypes_' . $key . '_enabled',
							'value' => $section['enabled'],
						);
						$allow_upload_checkbox = self::checkbox( $uplaod_args, $echo = false );
						$allow_upload_checkbox = apply_filters( 'rtmedia_filter_allow_upload_checkbox', $allow_upload_checkbox, $key, $uplaod_args );

						// allow featured
						$featured_args     = array(
							'key'	=> 'allowedTypes_' . $key . '_featured',
							'value'	=> $section['featured'],
						);
						$featured_checkbox = self::checkbox( $featured_args, $echo = false );
						$featured_checkbox = apply_filters( 'rtmedia_filter_featured_checkbox', $featured_checkbox, $key );

						if ( ! isset( $section['extn'] ) || ! is_array( $section['extn'] ) ) {
							$section['extn'] = array();
						}

						$extensions	= implode( ', ', $section['extn'] );
						?>

						<tr>
							<td>
								<?php
								echo esc_html( $section['name'] );

								if ( 'other' !== $key ) {
									?>
									<span class="rtm-tooltip rtm-extensions">
										<i class="dashicons dashicons-info rtmicon"></i>
										<span class="rtm-tip">
											<strong><?php echo esc_html__( 'File Extensions', 'buddypress-media' ); ?></strong><br/>
											<hr/>
											<?php echo esc_html( $extensions ); ?>
										</span>
									</span>
									<?php
								}
								?>
							</td>

							<td>
								<span class="rtm-field-wrap">
									<?php //escaping done into inner function
									// @codingStandardsIgnoreLine
									echo wp_kses( $allow_upload_checkbox, array(
										'span' => array(
											'class'		=> array(),
											'data-on'	=> array(),
											'data-off'	=> array(),
										),
										'label' => array(
											'for'	=> array(),
											'class'	=> array(),
										),
										'input' => array(
											'type'			=> array(),
											'checked'		=> array(),
											'data-toggle'	=> array(),
											'id'			=> array(),
											'name'			=> array(),
											'value'			=> array(),
										),
									) );
									?>
								</span>
							</td>

							<td>
								<?php //escaping done into inner function
								// @codingStandardsIgnoreLine
								echo wp_kses( $featured_checkbox, array(
									'span' => array(
										'class'		=> array(),
										'data-on'	=> array(),
										'data-off'	=> array(),
									),
									'label' => array(
										'for'	=> array(),
										'class'	=> array(),
									),
									'input' => array(
										'type'			=> array(),
										'checked'		=> array(),
										'data-toggle'	=> array(),
										'id'			=> array(),
										'name'			=> array(),
										'value'			=> array(),
									),
								) );
								?>
							</td>

							<?php do_action( 'rtmedia_type_setting_columns_body', $key, $section ) ?>
						</tr>

						<?php do_action( 'rtmedia_other_type_settings_textarea', $key ); ?>

						<?php
						do_action( 'rtmedia_type_settings_after_body', $key, $section );
					} else {
						echo '<tr class="hide">';
						echo '<td colspan="3">';
						echo "<input type='hidden' value='1' name='rtmedia-options[allowedTypes_" . esc_attr( $key ) . "_enabled]'>";
						echo "<input type='hidden' value='0' name='rtmedia-options[allowedTypes_" . esc_html( $key ) . "_featured]'>";
						echo '</td>';
						echo '</tr>';
					}
				}
				?>
			</table>
		</div>
		<?php
		do_action( 'rtmedia_after_bp_settings' );
		do_action( 'rtmedia_after_media_types_settings' );
	}

	/**
	 * Define sizes_render_options.
	 *
	 * @access static
	 *
	 * @param  array $options
	 *
	 * @return array $render
	 */
	static function sizes_render_options( $options ) {
		$render = array();
		foreach ( $options as $key => $value ) {
			$data = explode( '_', $key );
			if ( ! isset( $render[ $data[1] ] ) ) {
				$render[ $data[1] ]          = array();
				$render[ $data[1] ]['title'] = esc_html__( $data[1], 'buddypress-media' );
			}
			if ( ! isset( $render[ $data[1] ][ $data[2] ] ) ) {
				$render[ $data[1] ][ $data[2] ]          = array();
				$render[ $data[1] ][ $data[2] ]['title'] = esc_html__( $data[2], 'buddypress-media' );
			}

			$render[ $data[1] ][ $data[2] ][ $data[3] ] = $value;
		}

		return $render;
	}

	/**
	 * Define sizes_content.
	 *
	 * @access static
	 *
	 * @param  void
	 *
	 * @return void
	 */
	public static function sizes_content() {
		global $rtmedia;
		$options     = self::extract_settings( 'defaultSizes', $rtmedia->options );
		$render_data = self::sizes_render_options( $options );
		?>

		<div class="rtm-option-wrapper rtm-img-size-setting">
			<h3 class="rtm-option-title">
				<?php esc_html_e( 'Media Size Settings', 'buddypress-media' ); ?>
			</h3>

			<table class="form-table">
				<tr>
					<th><strong><?php esc_html_e( 'Category', 'buddypress-media' ) ?></strong></th>
					<th><strong><?php esc_html_e( 'Entity', 'buddypress-media' ); ?></strong></th>
					<th><strong><?php esc_html_e( 'Width', 'buddypress-media' ); ?></strong></th>
					<th><strong><?php esc_html_e( 'Height', 'buddypress-media' ); ?></strong></th>
					<th><strong><?php esc_html_e( 'Crop', 'buddypress-media' ); ?></strong></th>
				</tr>

				<?php
				foreach ( $render_data as $parent_key => $section ) {
					$entities = $section;
					unset( $entities['title'] );
					$count    = 0;
					$row_span = count( $entities ); // @codingStandardsIgnoreLine
					foreach ( $entities as $entity ) {
						?>
						<tr>
							<?php
							if ( 0 === $count ) {
								?>
								<td class="rtm-row-title" rowspan="<?php echo esc_attr( $row_span ); ?>">
									<?php echo esc_html( ucfirst( $section['title'] ) ); ?>
								</td>
								<?php
							}
							?>
							<td>
								<?php echo esc_html( ucfirst( $entity['title'] ) ); ?>
							</td>

							<?php
							$args = array(
								'key' => 'defaultSizes_' . $parent_key . '_' . $entity['title'],
							);
							foreach ( $entity as $child_key => $value ) {
								if ( 'title' !== $child_key ) {
									$args[ $child_key ] = $value;
								}
							}
							self::dimensions( $args );
							?>
						</tr>
						<?php
						$count ++;
					}
				}
				?>
			</table>

		</div>

		<?php
		$options = $rtmedia->options;

		$render_jpeg_image_quality = array(
			'title'		=> esc_html__( 'JPEG/JPG image quality (1-100)', 'buddypress-media' ),
			'callback'	=> array( 'RTMediaFormHandler', 'number' ),
			'args'		=> array(
				'key'	=> 'general_jpeg_image_quality',
				'value'	=> $options['general_jpeg_image_quality'],
				'class'	=> array( 'rtmedia-setting-text-box' ),
				'desc'	=> esc_html__( 'Enter JPEG/JPG Image Quality. Minimum value is 1. 100 is original quality.', 'buddypress-media' ),
				'min'	=> 1,
				'max'	=> 100,
			),
		);
		?>

		<div class="rtm-option-wrapper">
			<?php self::render_option_group( esc_html__( 'Image Quality', 'buddypress-media' ) ); ?>
			<?php self::render_option_content( $render_jpeg_image_quality ); ?>
		</div>

		<?php
	}

	/**
	 * Define custom css content.
	 *
	 * @access static
	 *
	 * @param  void
	 *
	 * @return void
	 */
	public static function custom_css_content() {
		global $rtmedia;
		$options     = self::extract_settings( 'styles', $rtmedia->options );
		$render_data = self::custom_css_render_options( $options );

		$render_groups     = array();
		$render_groups[10] = esc_html__( 'Custom CSS settings', 'buddypress-media' );

		self::render_tab_content( $render_data, $render_groups, 10 );
	}

	/**
	 * Render custom css options.
	 *
	 * @access static
	 *
	 * @param  array $options
	 *
	 * @return array $render
	 */
	static function custom_css_render_options( $options ) {
		$render = array(
			'disable_styles' => array(
				'title'		=> esc_html__( 'rtMedia default styles', 'buddypress-media' ),
				'callback'	=> array( 'RTMediaFormHandler', 'checkbox' ),
				'args'		=> array(
					'id'	=> 'rtmedia-disable-styles',
					'key'	=> 'styles_enabled',
					'value'	=> $options['styles_enabled'],
					'desc'	=> esc_html__( 'Load default rtMedia styles. You need to write your own style for rtMedia if you disable it.', 'buddypress-media' ),
				),
				'group'    => 10,
			),
			'custom_styles'  => array(
				'title'		=> esc_html__( 'Paste your CSS code', 'buddypress-media' ),
				'callback'	=> array( 'RTMediaFormHandler', 'textarea' ),
				'args'		=> array(
					'id'	=> 'rtmedia-custom-css',
					'key'	=> 'styles_custom',
					'value'	=> wp_filter_nohtml_kses( $options['styles_custom'] ),
					'desc'	=> esc_html__( 'Custom rtMedia CSS container', 'buddypress-media' ),
				),
				'group'    => 10,
			),
		);

		return $render;
	}

	/**
	 * Render privacy options.
	 *
	 * @access static
	 *
	 * @param  array $options
	 *
	 * @return array $render
	 */
	static function privacy_render_options( $options ) {
		global $rtmedia;

		$render = array(
			'enable'        => array(
				'title'		=> esc_html__( 'Enable privacy', 'buddypress-media' ),
				'callback'	=> array( 'RTMediaFormHandler', 'checkbox' ),
				'args'		=> array(
					'id'	=> 'rtmedia-privacy-enable',
					'key'	=> 'privacy_enabled',
					'value'	=> $options['privacy_enabled'],
					'desc'	=> esc_html__( 'Enable privacy in rtMedia', 'buddypress-media' ),
				),
				'group'    => 10,
			),
			'default'       => array(
				'title'		=> esc_html__( 'Default privacy', 'buddypress-media' ),
				'callback'	=> array( 'RTMediaFormHandler', 'radio' ),
				'args'		=> array(
					'key'		=> 'privacy_default',
					'radios'	=> $rtmedia->privacy_settings['levels'],
					'default'	=> $options['privacy_default'],
					'desc'		=> esc_html__( 'Set default privacy for media', 'buddypress-media' ),
				),
				'group'    => 10,
				'depends'  => 'privacy_enabled',
			),
			'user_override' => array(
				'title'		=> esc_html__( 'Allow users to set privacy for their content', 'buddypress-media' ),
				'callback'	=> array( 'RTMediaFormHandler', 'checkbox' ),
				'args'		=> array(
					'key'	=> 'privacy_userOverride',
					'value'	=> $options['privacy_userOverride'],
					'desc'	=> esc_html__( 'If you choose this, users will be able to change privacy of their own uploads.', 'buddypress-media' ),
				),
				'group'			=> 10,
				'depends'		=> 'privacy_enabled',
				'after_content'	=> esc_html__( 'For group uploads, BuddyPress groups privacy is used.', 'buddypress-media' ),
			),
		);

		return $render;
	}

	/**
	 * Render privacy content.
	 *
	 * @access static
	 *
	 * @param  void
	 *
	 * @return void
	 */
	public static function privacy_content() {
		global $rtmedia;

		$general_group		= array();
		$general_group[10]	= 'Privacy Settings';
		$general_group		= apply_filters( 'rtmedia_privacy_settings_groups', $general_group );

		$options			= self::extract_settings( 'privacy', $rtmedia->options );
		$render_options		= self::privacy_render_options( $options );
		$render_options		= apply_filters( 'rtmedia_privacy_settings_options', $render_options );

		self::render_tab_content( $render_options, $general_group, 10 );
	}

	/**
	 * Render buddypress options.
	 *
	 * @access static
	 *
	 * @param  array $options
	 *
	 * @return array $render
	 */
	static function buddypress_render_options( $options ) {
		$render = array(
			'rtmedia-enable-on-profile'                => array(
				'title'    => esc_html__( 'Enable media in profile', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'buddypress_enableOnProfile',
					'value' => $options['buddypress_enableOnProfile'],
					'desc'  => esc_html__( 'Enable Media on BuddyPress Profile', 'buddypress-media' ),
				),
				'group'    => 10,
			),
			'rtmedia-enable-on-group'                  => array(
				'title'    => esc_html__( 'Enable media in group', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'buddypress_enableOnGroup',
					'value' => $options['buddypress_enableOnGroup'],
					'desc'  => esc_html__( 'Enable Media on BuddyPress Groups', 'buddypress-media' ),
					'id'    => 'rtmedia-enable-on-group',
				),
				'group'    => 10,
			),
			'rtmedia-enable-on-activity'               => array(
				'title'    => esc_html__( 'Allow upload from activity stream', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'buddypress_enableOnActivity',
					'value' => $options['buddypress_enableOnActivity'],
					'desc'  => esc_html__( 'Allow upload using status update box present on activity stream page', 'buddypress-media' ),
					'id'    => 'rtmedia-bp-enable-activity',
				),
				'group'    => 10,
			),
			'buddypress_enableOnComment'               => array(
				'title'    => esc_html__( 'Enable media in comment', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'buddypress_enableOnComment',
					'value' => $options['buddypress_enableOnComment'],
					'desc'  => esc_html__( 'This will allow users to upload media in comment section for originally uploaded media up to 1 level.', 'buddypress-media' ),
				),
				'group'    => 660,
			),
			'rtmedia_disable_media_in_commented_media' => array(
				'title'    => esc_html__( 'Disable upload in comment media', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'rtmedia_disable_media_in_commented_media',
					'value' => $options['rtmedia_disable_media_in_commented_media'],
					'desc'  => esc_html__( 'Disable upload in comment media', 'buddypress-media' ),
				),
				'group'    => 660,
				'depends'  => 'buddypress_enableOnComment',
			),
			'rtmedia-activity-feed-limit'              => array(
				'title'    => esc_html__( 'Number of media items to show in activity stream', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'number' ),
				'args'     => array(
					'key'   => 'buddypress_limitOnActivity',
					'value' => $options['buddypress_limitOnActivity'],
					'desc'  => esc_html__( 'With bulk uploads activity, the stream may get flooded. You can control the maximum number of media items or files per activity. This limit will not affect the actual number of uploads. This is only for display. "0" means unlimited.', 'buddypress-media' ),
					'class' => array( 'rtmedia-setting-text-box rtmedia-bp-activity-setting' ),
					'min'   => 0,
					'id'    => 'rtmedia-activity-feed-limit',
				),
				'group'    => 10,
			),
			'rtmedia-enable-notification'              => array(
				'title'    => esc_html__( 'Enable media notification', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'buddypress_enableNotification',
					'value' => $options['buddypress_enableNotification'],
					'desc'  => esc_html__( 'This will enable notifications to media authors for media likes and comments.', 'buddypress-media' ),

				),
				'group'    => 10,
			),
			'rtmedia-enable-like-activity'             => array(
				'title'    => esc_html__( 'Create activity for media likes', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'buddypress_mediaLikeActivity',
					'value' => $options['buddypress_mediaLikeActivity'],
					'desc'  => esc_html__( 'Enabling this setting will create BuddyPress activity for media likes.', 'buddypress-media' ),
					'id'    => 'rtmedia-enable-like-activity',

				),
				'group'    => 10,
			),
			'rtmedia-enable-comment-activity'          => array(
				'title'    => esc_html__( 'Create activity for media comments', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'key'   => 'buddypress_mediaCommentActivity',
					'value' => $options['buddypress_mediaCommentActivity'],
					'desc'  => esc_html__( 'Enabling this setting will create BuddyPress activity for media comments.', 'buddypress-media' ),
					'id'    => 'rtmedia-enable-comment-activity',

				),
				'group'    => 10,
			),
			'general_enableAlbums'                     => array(
				'title'    => esc_html__( 'Organize media into albums', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'id'    => 'rtmedia-album-enable',
					'key'   => 'general_enableAlbums',
					'value' => $options['general_enableAlbums'],
					'desc'  => esc_html__( 'This will add \'album\' tab to BuddyPress profile and group depending on the ^above^ settings.', 'buddypress-media' ),
				),
				'group'    => 50,
			),
			'general_enableAlbums_description'         => array(
				'title'    => esc_html__( 'Show album description', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args'     => array(
					'id'    => 'rtmedia-album-description-enable',
					'key'   => 'general_enableAlbums_description',
					'value' => $options['general_enableAlbums_description'],
					'desc'  => esc_html__( 'This will show description of an album under album gallery page.', 'buddypress-media' ),
				),
				'group'    => 50,
				'depends'  => 'general_enableAlbums',
			),
		);

		return $render;
	}

	/**
	 * Define buddypress content.
	 *
	 * @access static
	 *
	 * @param  void
	 *
	 * @return void
	 */
	public static function buddypress_content() {
		global $rtmedia;

		$general_group      = array();
		$general_group[10]  = 'Integration With BuddyPress Features';
		$general_group[660] = 'Comment Media';
		$general_group[50]  = 'Album Settings';
		$general_group      = apply_filters( 'rtmedia_buddypress_setting_group', $general_group );

		$render_options = self::buddypress_render_options( $rtmedia->options );
		// Change option description when 'Activity Streams' component is disabled.
		if ( ! bp_is_active( 'activity' ) ) {
			$render_options['rtmedia-enable-on-activity']['args']['desc']      =
			$render_options['rtmedia-activity-feed-limit']['args']['desc']     =
			$render_options['rtmedia-enable-like-activity']['args']['desc']    =
			$render_options['rtmedia-enable-comment-activity']['args']['desc'] =
				esc_html__( 'Please Enable BuddyPress Activity Streams to update option', 'buddypress-media' );
		}

		// Change option description when 'User Groups' component is disabled.
		if ( ! bp_is_active( 'groups' ) ) {
			$render_options['rtmedia-enable-on-group']['args']['desc'] =
				esc_html__( 'Please Enable BuddyPress User Groups to update option', 'buddypress-media' );
		}
		$render_options = apply_filters( 'rtmedia_album_control_setting', $render_options, $rtmedia->options );

		$render_options = apply_filters( 'rtmedia_buddypress_setting_options', $render_options );

		self::render_tab_content( $render_options, $general_group, 10 );

		do_action( 'rtmedia_buddypress_setting_content' );
		/**
		 * Disable inputs and change background color to differentiate disabled inputs,
		 * if 'Activity Streams' component is disabled in BuddyPress Settings.
		*/
		if ( ! bp_is_active( 'activity' ) ) {
			?>
			<script>
				jQuery( '#rtmedia-bp-enable-activity, #rtmedia-enable-comment-activity, #rtmedia-enable-like-activity' )
					.prop( "disabled", true )
					.next().css( 'background-color', '#808080' );
				jQuery( '#rtmedia-activity-feed-limit' ).prop( "disabled", true );
			</script>
			<?php
		}
		/**
		 * Disable inputs and change background color to differentiate disabled inputs,
		 * if 'User Groups' component is disabled in BuddyPress Settings.
		 */
		if ( ! bp_is_active( 'groups' ) ) {
			?>
			<script>
				jQuery( '#rtmedia-enable-on-group' ).prop( "disabled", true ).next().css( 'background-color', '#808080' );
			</script>
			<?php
		}

	}

	/**
	 * Define rtForm settings tabs content.
	 *
	 * @access static
	 *
	 * @param  string  $page
	 * @param  array $sub_tabs
	 *
	 * @return void
	 */
	public static function rtForm_settings_tabs_content( $page, $sub_tabs ) {
		$args = array(
			'wrapper_class' => array(
				'rtm-settings-tab-container',
			),
		);
		RTMediaAdmin::render_admin_ui( $page, $sub_tabs, $args );
	}

	/**
	 * Define rtForm do_settings_fields.
	 *
	 * @access static
	 *
	 * @param  string $page
	 * @param  string $section
	 *
	 * @return void
	 */
	public static function rtForm_do_settings_fields( $page, $section ) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
			echo '<div class="row">';
			echo '<div class="large-11 columns">';

			if ( isset( $field['args']['label_for'] ) && ! empty( $field['args']['label_for'] ) ) {
				call_user_func( $field['callback'], array_merge( $field['args'], array( 'label' => $field['args']['label_for'] ) ) );
			} else {
				if ( isset( $field['title'] ) && ! empty( $field['title'] ) ) {
					call_user_func( $field['callback'], array_merge( $field['args'], array( 'label' => $field['title'] ) ) );
				} else {
					call_user_func( $field['callback'], $field['args'] );
				}
			}
			echo '</div>';
			echo '</div>';
		}
	}

	/*
	 * render each tab content
	 *
	 * @param array $option
	 * @param array $groups
	 * @param int $default_group
	 */

	public static function render_tab_content( $options, $groups = array(), $default_group = 0 ) {
		if ( ! empty( $groups ) ) {
			foreach ( $groups as $key => $value ) {
				?>
				<div class="rtm-option-wrapper">
					<?php
					self::render_option_group( $value );
					foreach ( $options as $tab => $option ) {
						if ( ! isset( $option['group'] ) ) {
							$option['group'] = $default_group;
						}

						if ( intval( $option['group'] ) !== $key ) {
							continue;
						}
						self::render_option_content( $option );
					}
					?>
				</div>
				<?php
			}
		} else {
			?>
			<div class="rtm-option-wrapper">
				<?php
				foreach ( $options as $tab => $option ) {
					self::render_option_content( $option );
				}
				?>
			</div>
			<?php
		}
	}

	/*
	 * render option group title inside single tab
	 *
	 * @param string $group
	 */

	public static function render_option_group( $group ) {
		?>
		<h3 class="rtm-option-title"><?php echo esc_html( $group ); ?></h3>
		<?php
	}

	/*
	 * render options
	 * @param array $option
	 */

	public static function render_option_content( $option ) {
		?>

		<table class="form-table" <?php if ( isset( $option['depends'] ) && '' !== $option['depends'] ) { echo 'data-depends="' . esc_attr( $option['depends'] ) . '"'; } ?> >
			<tr>
				<th>
					<?php echo wp_kses( $option['title'], array(
						'a' => array(
							'id'		=> array(),
							'href'		=> array(),
							'target'	=> array(),
						),
					) ); ?>
				</th>
				<td>
					<fieldset>
						<span
							class="rtm-field-wrap"><?php call_user_func( $option['callback'], $option['args'] ); ?></span>
						<span class="rtm-tooltip">
							<i class="dashicons dashicons-info rtmicon"></i>
							<span class="rtm-tip">
								<?php echo wp_kses( ( isset( $option['args']['desc'] ) ) ? $option['args']['desc'] : 'NA', array(
									'a' => array(
										'id'		=> array(),
										'href'		=> array(),
										'target'	=> array(),
									),
								) ); ?>
							</span>
						</span>
					</fieldset>
				</td>
			</tr>
		</table>

		<?php
		if ( isset( $option['after_content'] ) && '' !== $option['after_content'] ) {
			?>
			<div class="rtm-message rtm-notice">
				<?php
				echo wp_kses( wpautop( $option['after_content'] ), array(
					'a' => array(
						'id'		=> array(),
						'href'		=> array(),
						'target'	=> array(),
					),
					'p' => array(),
				) );
				?>
			</div>
			<?php
		}
	}
}
