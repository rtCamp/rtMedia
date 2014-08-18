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
	    global $rtmedia;
		$options = $rtmedia->options;
		$defaults = array(
			'key' => '',
			'desc' => '',
			'default'  => '',
			'show_desc' => false,
			'selects' => array()
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( ! empty( $key ) ){
			$args['name'] = 'rtmedia-options[' . $key . ']';
		}

		$args['rtForm_options'] = array();
		foreach ( $selects as $value => $key ) {
			$args['rtForm_options'][] = array(
				$key => $value,
				'selected' => ( $default == $value ) ? true : false
			);
		}

		$chkObj = new rtForm();
		echo $chkObj->get_select( $args );
	}

	/**
	 * Show rtmedia textarea in admin options.
	 *
	 * @access static
	 * @param  array  $args
	 * @param  bool   $echo
	 * @return string $chkObj->get_textarea( $args )
	 */
	public static function textarea( $args, $echo = true ) {
	    global $rtmedia;
		$options = $rtmedia->options;
		$defaults = array(
            'id' => '',
			'key' => '',
			'desc' => '',
			'show_desc' => false
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( ! isset( $value ) ){
			trigger_error( __( 'Please provide "value" in the argument.', 'rtmedia' ) );
			return;
		}

		if ( ! empty( $key ) ){
			$args['name'] = 'rtmedia-options[' . $key . ']';
		}

		$args['rtForm_options'] = array( array( '' => 1, 'checked' => $value ) );

		$chkObj = new rtForm();

        if( $echo ){
            echo $chkObj->get_textarea( $args );
        } else {
            return $chkObj->get_textarea( $args );
        }
	}

	/**
	 * Show rtmedia checkbox in admin options.
	 *
	 * @access static
	 * @param  array  $args
	 * @param  bool   $echo
	 * @return string $chkObj->get_switch( $args )
	 */
	public static function checkbox( $args, $echo = true ) {
		global $rtmedia;
		$options = $rtmedia->options;
		$defaults = array(
			'key' => '',
			'desc' => '',
			'show_desc' => false
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( ! isset( $value ) ){
			trigger_error( __( 'Please provide "value" in the argument.', 'rtmedia' ) );
			return;
		}

		if ( ! empty( $key ) ){
			$args['name'] = 'rtmedia-options[' . $key . ']';
		}

		$args['rtForm_options'] = array( array( '' => 1, 'checked' => $value ) );

		$chkObj = new rtForm();
//		echo $chkObj->get_checkbox($args);
        if( $echo ){
            echo $chkObj->get_switch( $args );
		} else {
            return $chkObj->get_switch( $args );
		}
//		echo $chkObj->get_switch_square($args);
	}

	/**
	 * Show rtmedia radio in admin options.
	 *
	 * @access static
	 * @param  array  $args
	 * @return void
	 */
	public static function radio( $args ) {
		global $rtmedia;
        $options = $rtmedia->options;
		$defaults = array(
			'key' => '',
			'radios' => array(),
			'default' => '',
			'show_desc' => false
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( 2 > count( $radios ) ){
			trigger_error( __( 'Need to specify atleast to radios else use a checkbox instead', 'rtmedia' ) );
			return;
		}

		if ( ! empty( $key ) ){
			$args['name'] = 'rtmedia-options[' . $key . ']';
		}

		$args['rtForm_options'] = array();
		foreach ( $radios as $value => $key ) {
			$args['rtForm_options'][] = array(
				$key => $value,
				'checked' => ( $default == $value ) ? true : false
			);
		}

		$objRad = new rtForm();
		echo $objRad->get_radio( $args );
	}

	/**
	 * Show rtmedia dimensions in admin options.
	 *
	 * @access static
	 * @return void
	 */
	public static function dimensions( $args ) {
		$dmnObj = new rtDimensions();
		echo $dmnObj->get_dimensions( $args );
	}

	/**
	 * Show rtmedia number in admin options.
	 *
	 * @access static
	 * @param  array  $args
	 * @return void
	 */
	public static function number( $args ) {
		global $rtmedia;
		$options = $rtmedia->options;
		$defaults = array(
			'key' => '',
			'desc' => ''
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( ! isset( $value ) ){
			trigger_error( __( 'Please provide "value" in the argument.', 'rtmedia' ) );
			return;
		}

		if ( ! empty( $key ) ){
			$args['name'] = 'rtmedia-options[' . $key . ']';
		}

		$args['value'] = $value;

		$numObj = new rtForm();
		echo $numObj->get_number( $args );
	}

	/**
	 * Show rtmedia textbox in admin options.
	 *
	 * @access static
	 * @param  array  $args
	 * @return void
	 */
	public static function textbox( $args ) {
		global $rtmedia;
		$options = $rtmedia->options;
		$defaults = array(
			'key' => '',
			'desc' => ''
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( ! isset( $value ) ){
			trigger_error( __( 'Please provide "value" in the argument.', 'rtmedia' ) );
			return;
		}

		if ( ! empty( $key ) ){
			$args['name'] = 'rtmedia-options[' . $key . ']';
		}

		$args['value'] = $value;

		$numObj = new rtForm();
		echo $numObj->get_textbox( $args );
	}

	/**
	 * extract settings.
	 *
	 * @access static
	 * @param  array  $options
	 * @param  string $section_name
	 * @return array  $section
	 */
	static function extract_settings( $section_name, $options ) {
		$section = array();
		foreach ( $options as $key => $value ) {
			if( strncmp( $key, $section_name, strlen( $section_name ) ) == 0 )
				$section[$key] = $value;
		}

		return $section;
	}

	/**
	 * display render options.
	 *
	 * @access static
	 * @param  array  $options
	 * @return array  $render
	 */
	static function display_render_options( $options ) {
        $radios = array();
        $radios[ 'load_more' ] = "<strong>Load More</strong>";
        $radios[ 'pagination' ] = "<strong>Pagination</strong>";

		if ( is_plugin_active( 'regenerate-thumbnails/regenerate-thumbnails.php' ) ){
			$regenerate_link = admin_url( '/tools.php?page=regenerate-thumbnails' );
		} elseif ( array_key_exists( 'regenerate-thumbnails/regenerate-thumbnails.php', get_plugins() ) ){
			$regenerate_link = admin_url( '/plugins.php#regenerate-thumbnails' );
		} else {
			$regenerate_link = wp_nonce_url( admin_url( 'update.php?action=install-plugin&plugin=regenerate-thumbnails' ), 'install-plugin_regenerate-thumbnails' );
		}

		$render = array(
			'general_enableComments' => array(
				'title' => __( 'Allow user to comment on uploaded media', 'rtmedia' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args' => array(
					'key' => 'general_enableComments',
					'value' => $options['general_enableComments'],
					'desc' => __( 'This will display comment form and comment listing on single media pages as well as inside lightbox (if lightbox is enabled).', 'rtmedia' )
				),
				'group' => "10"
			),
			'general_enableLightbox' => array(
				'title' => __( 'Use lightbox to display media', 'rtmedia' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args' => array(
					'key' => 'general_enableLightbox',
					'value' => $options['general_enableLightbox'],
					'desc' => __( 'View single media in facebook style lightbox.', 'rtmedia' )
				),
				'group' => "15"
			),
			'general_perPageMedia' => array(
				'title' => __( 'Number of media per page', 'rtmedia' ),
				'callback' => array( 'RTMediaFormHandler', 'number' ),
				'args' => array(
					'key' => 'general_perPageMedia',
					'value' => $options['general_perPageMedia'],
					'class' => array( 'rtmedia-setting-text-box' ),
					'desc' => __( 'Number of media you want to show per page on front end.', 'rtmedia' ),
					'min' => 1
				),
				'group' => "15"
			),
			'general_display_media' => array(
				'title' => __( 'Media display pagination option', 'rtmedia' ),
				'callback' => array( 'RTMediaFormHandler', 'radio' ),
				'args' => array(
					'key' => 'general_display_media',
					'radios' => $radios,
					'default' => $options['general_display_media'],
					'desc' => __( 'Choose whether you want load more button or pagination buttons.', 'rtmedia' ),
					'class' => array( 'rtmedia-load-more-radio' )
				),
				'group' => "15"
			),
			'general_masonry_layout' => array(
				'title' => __( 'Enable', 'rtmedia' ) . ' <a href="http://masonry.desandro.com/" target="_blank">Masonry</a> '. __( 'Cascading grid layout', 'rtmedia'),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args' => array(
					'key' => 'general_masonry_layout',
					'value' => $options['general_masonry_layout'],
					'desc' => __( 'Masonry works by placing elements in optimal position based on available vertical space, sort of like a mason fitting stones in a wall.', 'rtmedia' ),
					'class' => array( 'rtm_enable_masonry_view' ),
				),
				'group' => "18",
				'after_content' => __( 'You might need to', 'rtmedia' ) . ' <a id="rtm-masonry-change-thumbnail-info" href="' . get_admin_url() . 'admin.php?page=rtmedia-settings#rtmedia-sizes">' . __( 'change thumbnail size', 'rtmedia' ) . '</a> ' . __( 'and uncheck the crop box for thumbnails.', 'rtmedia' ) . '<br />' . __( 'If you enable masonry view, it is advisable to', 'rtmedia' ) . ' <a href="'.$regenerate_link.'">regenerate thumbnail</a> ' . __( 'for masonry view.', 'rtmedia' ) . '<br />' . __( 'To set gallery for fixed width, set image height to 0 and width as per your requirement and vice-versa.', 'rtmedia' ),
			),
		);

		return $render;
	}

	/**
	 * display content.
	 *
	 * @access static
	 * @param  void
	 * @return void
	 */
	public static function display_content() {
		global $rtmedia;
//		$options = self::extract_settings('general', $rtmedia->options);
		$options = $rtmedia->options;
		$render_options = self::display_render_options( $options );
//		$render_options = apply_filters('rtmedia_general_content_single_view_add_itmes',$render_options, $options);
        $render_options = apply_filters( "rtmedia_display_content_add_itmes", $render_options, $options );
		$general_group = array();
		$general_group[10] = "Single Media View";
		$general_group[15] = "List Media View";
		$general_group[18] = "Masonry View";
		$general_group = apply_filters( "rtmedia_display_content_groups", $general_group );
		ksort( $general_group );
		$html = '';
		foreach ( $general_group as $key => $value ) {
		?>
		    <div class="postbox metabox-holder">
			<h3 class="hndle"><span><?php echo $value; ?></span></h3>
		<?php
		    foreach ( $render_options as $tab => $option ) {

				if( ! isset($option['group']) ){
				    $option['group'] = "20";
				}

				if( $option['group'] != $key ){
				    continue;
				}
			?>
				<div class="row section">
				    <div class="columns large-9">
					<?php echo $option['title']; ?>
				    </div>
					<div class="columns large-3">
					    <?php call_user_func( $option['callback'], $option['args'] ); ?>
					    <span data-tooltip class="has-tip" title="<?php echo ( isset( $option['args']['desc'] ) ) ? $option['args']['desc'] : "NA"; ?>"><i class="rtmicon-info-circle"></i></span>
					</div>
				</div>
		    <?php
				if( isset( $option['after_content'] ) ){
					?>
					<div class="row">
						<div class="columns large-12">
							<p class="rtmedia-info rtmedia-admin-notice">
								<?php echo $option['after_content']; ?>
							</p>
						</div>
					</div>
					<?php
				}
		    }
		    ?>
			</div>
		    <?php
		}

	}

	/**
	 * render general content.
	 *
	 * @access static
	 * @param  array $options
	 * @return array $render
	 */
	static function render_general_content( $options ) {
		$render = array(
			'general_AllowUserData' => array(
				'title' => __( 'Allow usage data tracking', 'rtmedia' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args' => array(
					'key' => 'general_AllowUserData',
					'value' => $options['general_AllowUserData'],
					'desc' => __( 'You can help rtMedia team learn what themes and plugins you are using to make rtMedia better compatible with your sites. No private information about your setup will be sent during tracking.', 'rtmedia' )
				)
			),
			'general_showAdminMenu' => array(
				    'title' => __( 'Admin bar menu integration', 'rtmedia' ),
				    'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				    'args' => array(
					    'key' => 'general_showAdminMenu',
					    'value' => $options['general_showAdminMenu'],
					    'desc' => __( 'Add rtMedia menu to WordPress admin bar for easy access to settings and moderation page (if enabled).', 'rtmedia' )
				    ),
				    'group' => 10
			),//
			'rtmedia_add_linkback' => array(
				    'title' => __( 'Add a link to rtMedia in footer', 'rtmedia' ),
				    'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				    'args' => array(
					    'key' => 'rtmedia_add_linkback',
					    'value' => $options['rtmedia_add_linkback'],
					    'desc' => __( 'Help us to promote rtMedia.', 'rtmedia' )
				    ),
				    'group' => 100
			),//
			'rtmedia_affiliate_id' => array(
				    'title' => __( 'Also add my affiliate-id to rtMedia footer link', 'rtmedia' ),
				    'callback' => array( 'RTMediaFormHandler', 'textbox' ),
				    'args' => array(
					    'key' => 'rtmedia_affiliate_id',
					    'value' => $options['rtmedia_affiliate_id'],
					    'desc' => __( 'Add your affiliate-id along with footer link and get benefited from our affiliation program.', 'rtmedia' )
				    ),
				    'group' => 100,
				    'after_content' => __( 'You can signup for rtMedia affiliate program from <a href="https://rtcamp.com/affiliates">here</a>' ),
			),//
			'rtmedia_enable_api' => array(
				    'title' => __( 'Enable JSON API', 'rtmedia' ),
				    'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				    'args' => array(
					    'key' => 'rtmedia_enable_api',
					    'value' => $options['rtmedia_enable_api'],
					    'desc' => __( 'This will allow handling API requests for rtMedia sent through any mobile app.', 'rtmedia' )
				    ),
				    'group' => 80,
				    'after_content' => __( 'You can refer API document from <a href="https://rtcamp.com/rtmedia/docs/developer/json-api/">here</a>' ),
			),//
		);

		return $render;
	}

	/**
	 * Define general_content
	 *
	 * @access static
	 * @param  array $options
	 * @return void
	 */
	static function general_content( $options ) {
	    global $rtmedia;
//		$options = self::extract_settings('general', $rtmedia->options);
		$options = $rtmedia->options;
		$render_options = self::render_general_content($options);
        $render_options = apply_filters( "rtmedia_general_content_add_itmes", $render_options, $options );
		$general_group = array();
		$general_group[10] = "Admin Settings";
		$general_group[80] = "API Settings";
		$general_group[90] = "Miscellaneous";
		$general_group[100] = "Footer Link";
		$general_group = apply_filters( "rtmedia_general_content_groups", $general_group );
		ksort( $general_group );
		$html = '';
		foreach ( $general_group as $key => $value ) {
		?>
		    <div class="postbox metabox-holder">
			<h3 class="hndle"><span><?php echo $value; ?></span></h3>
		<?php
		    foreach ( $render_options as $tab => $option ) {

				if( ! isset( $option['group'] ) ){
				    $option['group'] = "90";
				}

				if( $option['group'] != $key ){
				    continue;
				}
			?>
				<div class="row section">
				    <div class="columns large-7">
					<?php echo $option['title']; ?>
				    </div>
				    <div class="columns large-5">
					<?php call_user_func( $option['callback'], $option['args'] ); ?>
					<span data-tooltip class="has-tip" title="<?php echo ( isset( $option['args']['desc'] ) ) ? $option['args']['desc'] : "NA"; ?>"><i class="rtmicon-info-circle"></i></span>
				    </div>
				</div>
			    <?php
				if( isset( $option['after_content'] ) ){
			    ?>
				    <div class="row">
					<div class="columns large-12">
					    <p class="rtmedia-info rtmedia-admin-notice">
						<?php echo $option['after_content']; ?>
					    </p>
					</div>
				    </div>
			    <?php
				}
			    ?>
			    <?php
		    }
		    ?>
			</div>
		    <?php
		}
	}

    /**
	 * Get type details
	 *
	 * @access static
	 * @param  array  $allowed_types
	 * @param  string $key
	 * @return array  $data
	 */
    static function get_type_details( $allowed_types, $key ) {
	    foreach ( $allowed_types as $type ) {
		    if( $type['name'] == $key ){
			    $data = array(
				    'name' => $type['label'],
				    'extn' => $type['extn']
			    );
			    if ( isset ( $type['settings_visibility'] ) ){
					$data['settings_visibility'] = $type['settings_visibility'];
			    }
			    return $data;
		    }
	    }
    }

    /**
	 * Define types_render_options.
	 *
	 * @access static
	 * @param  array  $options
	 * @return array  $render
	 */
    static function types_render_options( $options ) {
	    global $rtmedia;

	    $render = array();
	    $allowed_media_type = $rtmedia->allowed_types;
	    $allowed_media_type = apply_filters( "rtmedia_allowed_types", $allowed_media_type );

	    foreach ( $options as $key => $value ) {
		    $data = explode( '_', $key );
		    if( ! isset( $render[$data[1]] ) ){
				$render[$data[1]] = self::get_type_details( $allowed_media_type, $data[1] );
		    }
	    }

	    foreach ( $options as $key => $value ) {
		    $data = explode( '_', $key );
		    $render[$data[1]][$data[2]] = $value;
	    }

	    return $render;
    }

    /**
	 * Define types_content.
	 *
	 * @access static
	 * @param  void
	 * @return void
	 */
    public static function types_content() {
	    global $rtmedia;
	    $options = self::extract_settings( 'allowedTypes', $rtmedia->options );

	    $render_data = self::types_render_options( $options );
		?>
	    <div class="postbox metabox-holder">
		    <h3 class="hndle">
				<span>Media Types Settings</span>
				<?php do_action( "rtmedia_media_type_setting_message" ); ?>
			</h3>
	    </div>
	    <div class="rt-table large-12">
		    <div class="row rt-header">
			<?php do_action( "rtmedia_type_settings_before_heading" ); ?>
			    <div class="columns large-4"><h4><?php _e( "Media Type","rtmedia" ) ?></h4></div>
				<div class="columns large-4"><h4 data-tooltip class="has-tip" title="<?php _e( "Allows you to upload a particular media type on your post.", "rtmedia" ); ?>"><abbr><?php _e( "Allow Upload", "rtmedia" ); ?></abbr></h4></div>
				<div class="columns large-4"><h4 data-tooltip class="has-tip" title="<?php _e( "Put a specific media as a featured content on the post.", "rtmedia" ); ?>"><abbr><?php _e( "Set Featured", "rtmedia" ); ?></abbr></h4></div>

				<?php do_action( "rtmedia_type_settings_after_heading" ); ?>
			</div>

		<?php
		$even = 0;
		foreach ( $render_data as $key => $section ) {
            if( isset( $section['settings_visibility'] ) && $section['settings_visibility'] == true ){

				if( ++$even%2 ){
				    echo '<div class="row rt-odd">';
				} else {
				    echo '<div class="row rt-even">';
				}

				do_action( "rtmedia_type_settings_before_body" );
			    echo '<div class="columns large-4">' . $section['name'] . '</div>';
			    $args = array( 'key' => 'allowedTypes_'.$key.'_enabled', 'value' => $section['enabled'] );
			    echo '<div class="columns large-4">';
			    $allow_upload_checkbox = self::checkbox( $args, $echo = false );
			    $allow_upload_checkbox = apply_filters( "rtmedia_filter_allow_upload_checkbox", $allow_upload_checkbox , $key, $args );
			    echo $allow_upload_checkbox;
			    echo '</div>';
			    $args = array( 'key' => 'allowedTypes_'.$key.'_featured', 'value' => $section['featured'] );
			    echo '<div class="columns large-4">';
			    $featured_checkbox = self::checkbox( $args , $echo = false );
			    $featured_checkbox = apply_filters( "rtmedia_filter_featured_checkbox", $featured_checkbox, $key );
			    echo $featured_checkbox;
			    echo '</div>';

			    if( ! isset( $section['extn'] ) || ! is_array( $section['extn'] ) ){
					$section['extn'] = array();
			    }

			    $extensions = implode( ', ', $section['extn'] );
			    $extensions = apply_filters( "rtmedia_type_settings_filter_extension", $extensions, $key );
				do_action( "rtmedia_type_settings_after_body", $key, $section );
			    echo '</div>';
			    echo '<div class="row rtmedia-file-extension-wrap">';
				echo '<label class="columns large-3">'.__( "File Extensions", "rtmedia" ).':</label>';
				echo '<label class="columns large-9 rtmedia_type_settings_filter_extension">' . $extensions . '</label>';
			    echo '</div>';

            } else {
                echo "<input type='hidden' value='1' name='rtmedia-options[allowedTypes_" . $key . "_enabled]'>";
                echo "<input type='hidden' value='0' name='rtmedia-options[allowedTypes_" . $key . "_featured]'>";
            }
		}
		echo '</div>';
                do_action( "rtmedia_after_bp_settings" );
                do_action( "rtmedia_after_media_types_settings" );
	}

	/**
	 * Define sizes_render_options.
	 *
	 * @access static
	 * @param  array $options
	 * @return array $render
	 */
	static function sizes_render_options( $options ) {
		$render = array();
		foreach ( $options as $key => $value ) {
			$data = explode( '_', $key );
			if( ! isset( $render[$data[1]] ) ){
				$render[$data[1]] = array();
				$render[$data[1]]['title'] = __( $data[1],"rtmedia" );
			}
			if( ! isset( $render[$data[1]][$data[2]] ) ){
				$render[$data[1]][$data[2]] = array();
				$render[$data[1]][$data[2]]['title'] = __( $data[2], "rtmedia" );
			}

			$render[$data[1]][$data[2]][$data[3]] = $value;
		}

		return $render;
	}

	/**
	 * Define sizes_content.
	 *
	 * @access static
	 * @param  void
	 * @return void
	 */
	public static function sizes_content() {
		global $rtmedia;
		$options = self::extract_settings( 'defaultSizes', $rtmedia->options );
		$render_data = self::sizes_render_options( $options );
		?>
	    <div class="postbox metabox-holder">
			<h3 class="hndle">
				<span>Media Size Settings</span>
			</h3>
	    </div>
		<?php
		//container
		echo '<div class="rt-table large-12 rtmedia-size-content-setting">';

		//header
		echo '<div class="rt-header row">';
			echo '<h4 class="columns large-3">' . __( "Category", "rtmedia" ) . '</h4>';
			echo '<h4 class="columns large-3">' . __( "Entity", "rtmedia" ) . '</h4>';
			echo '<h4 class="columns large-6"><span class="large-offset-2">' . __( "Width", "rtmedia" ) . '</span><span class="large-offset-2">' . __( "Height", "rtmedia" ) . '</span><span class="large-offset-2">' . __( "Crop", "rtmedia" ) . '</span></h4>';
		echo'</div>';

		//body
		$even = 0;
		foreach ( $render_data as $parent_key => $section ) {
			if( ++$even%2 ){
				echo '<div class="row rt-odd">';
			} else {
				echo '<div class="row rt-even">';
			}
			echo '<div class="columns large-3">' . ucfirst( $section['title'] ) . '</div>';
			$entities = $section;
			unset( $entities['title'] );
			echo '<div class="columns large-3">';
			foreach ( $entities as $entity ) {
				echo '<div class="row">' . ucfirst( $entity['title'] ) . '</div>';
			}
			echo '</div>';
			echo '<div class="columns large-6">';
			foreach ( $entities as $entity ) {
				$args = array(
					'key' => 'defaultSizes_'.$parent_key.'_'.$entity['title'],
				);
				foreach ( $entity as $child_key => $value ) {
					if( $child_key != 'title' ){
						$args[$child_key] = $value;
					}
				}
				self::dimensions( $args );
			}
			echo '</div>';
			echo '</div>';
		}

		echo '</div>';
		$options = $rtmedia->options;
		$render_video_thumb = array(
                'title' => __( 'Number of thumbnails to generate on video upload', 'rtmedia' ),
                'callback' => array( 'RTMediaFormHandler', 'number' ),
                'args' => array(
                        'key' => 'general_videothumbs',
                        'value' => $options['general_videothumbs'],
						'class' => array( 'rtmedia-setting-text-box' ),
						'desc' => __( ' If you choose more than 1 thumbnail, your users will be able to change thumbnail by going to video "edit" section.', 'rtmedia' ),
						'min' => 1
                )
        );
		?>
		<div class="postbox metabox-holder">
		    <h3 class="hndle"><span>Encoding Settings</span></h3>
		</div>
		<div class="row section">
		    <div class="columns large-9">
			<?php echo $render_video_thumb['title']; ?>
		    </div>
		    <div class="columns large-3">
			<?php call_user_func( $render_video_thumb['callback'], $render_video_thumb['args'] ); ?>
			<span data-tooltip class="has-tip" title="<?php echo ( isset( $render_video_thumb['args']['desc'] ) ) ? $render_video_thumb['args']['desc'] : "NA"; ?>"><i class="rtmicon-info-circle"></i></span>
		    </div>
		</div>
		<?php
	}

    /**
	 * Define custom css content.
	 *
	 * @access static
	 * @param  void
	 * @return void
	 */
    public static function custom_css_content() {
        global $rtmedia;
        $options = self::extract_settings( 'styles', $rtmedia->options );
        $render_data = self::custom_css_render_options( $options );
    ?>
	<div class="postbox metabox-holder">
	    <h3 class="hndle"><span>Custom CSS settings</span></h3>
	</div>
    <?php
        echo '<div class="large-12">';
        foreach ( $render_data as $option ) { ?>

            <div class="row section">
                <?php if( $option['args']['key'] == "styles_custom" ){ ?>
                    <div class="columns large-12 rtm-custom-css">
                        <strong class="<?php echo $option['args']['key'];?>"><?php echo $option['title']; ?></strong>
                        <?php call_user_func( $option['callback'], $option['args'] ); ?>
                        <div><?php _e( "If you want to add some custom CSS code to the plugin and don't want to modify any files, then it's a good place to enter your code at this field." );?></div>
                    </div>
                <?php } else { ?>
                <div class="columns large-6">
                    <strong class="<?php echo $option['args']['key'];?>"><?php echo $option['title']; ?></strong>
                </div>
                <div class="columns large-6">
                    <?php call_user_func( $option['callback'], $option['args'] ); ?>
					<span data-tooltip class="has-tip" title="<?php echo ( isset( $option['args']['desc'] ) ) ? $option['args']['desc'] : "NA"; ?>"><i class="rtmicon-info-circle"></i></span>
                </div>
                <?php } ?>
            </div>
        <?php }
        echo '</div>';

    }

	/**
	 * Render custom css options.
	 *
	 * @access static
	 * @param  array $options
	 * @return array $render
	 */
	static function custom_css_render_options( $options ) {
	    global $rtmedia;

	    $render = array(
	    	'disable_styles' => array(
				'title' => __( "rtMedia default styles", "rtmedia" ),
				'callback' => array( "RTMediaFormHandler", "checkbox" ),
				'args' => array(
					'id' => 'rtmedia-disable-styles',
					'key' => 'styles_enabled',
					'value' => $options['styles_enabled'],
					'desc' => __( 'Load default rtMedia styles. You need to write your own style for rtMedia if you disable it.', 'rtmedia' )
	            )
	        ),
	        'custom_styles' => array(
				'title' => __( "Paste your CSS code", "rtmedia" ),
				'callback' => array( "RTMediaFormHandler", "textarea" ),
				'args' => array(
					'id' => 'rtmedia-custom-css',
					'key' => 'styles_custom',
					'value' => stripcslashes( $options['styles_custom'] ),
					'desc' => __( 'Custom rtMedia CSS container', 'rtmedia' )
	            )
	        )
	    );

	    return $render;
    }

	/**
	 * Render privacy options.
	 *
	 * @access static
	 * @param  array $options
	 * @return array $render
	 */
	static function privacy_render_options( $options ) {
		global $rtmedia;

		$render = array(
			'enable' => array(
				'title' => __( "Enable privacy", "rtmedia" ),
				'callback' => array( "RTMediaFormHandler", "checkbox" ),
				'args' => array(
					'id' => 'rtmedia-privacy-enable',
					'key' => 'privacy_enabled',
					'value' => $options['privacy_enabled'],
					'desc' => __( 'Enable privacy in rtMedia', 'rtmedia' )
				)
			),
			'default' => array(
				'title' => __( "Default privacy", "rtmedia" ),
				'callback' => array( "RTMediaFormHandler", "radio" ),
				'args' => array(
					'key' => 'privacy_default',
					'radios' => $rtmedia->privacy_settings['levels'],
					'default' => $options['privacy_default'],
					'desc' => __( 'Set default privacy for media', 'rtmedia' )
				),
			),
			'user_override' => array(
				'title' => __( "Allow users to set privacy for their content", "rtmedia" ),
				'callback' => array( "RTMediaFormHandler", "checkbox" ),
				'args' => array(
					'key' => 'privacy_userOverride',
					'value' => $options['privacy_userOverride'],
					'desc' => __( 'If you choose this, user will be able to change privacy of their own uploads.', 'rtmedia' )
				),
				'after_content' => __( 'For group uploads, BuddyPress groups privacy is used.', 'rtmedia' )
			)
		);

		return $render;
	}

	/**
	 * Render privacy content.
	 *
	 * @access static
	 * @param  void
	 * @return void
	 */
	public static function privacy_content() {
		global $rtmedia;
		$options = self::extract_settings( 'privacy', $rtmedia->options );

		$render_data = self::privacy_render_options( $options );
		?>
		    <div class="postbox metabox-holder">
			<h3 class="hndle"><span>Privacy Settings</span></h3>
		    </div>
		<?php
		echo '<div class="large-12">';
			foreach ( $render_data as $key => $privacy ) {
				echo '<div class="row section">';
				?>
					<div class="columns large-6">
					    <?php echo $privacy['title']  ?>
					</div>
				<?php
					echo '<div class="columns large-6">';
						if( $key != "enable" ){
							call_user_func( $privacy['callback'], array_merge_recursive( $privacy['args'], array( 'class' => array( "privacy-driven-disable" ) ) ) );
						} else {
							call_user_func( $privacy['callback'], $privacy['args']);
						}
						?>
						    <span data-tooltip class="has-tip" title="<?php echo ( isset( $privacy['args']['desc'] ) ) ? $privacy['args']['desc'] : "NA"; ?>"><i class="rtmicon-info-circle"></i></span>
						<?php
					echo '</div>';
				echo '</div>';

			    if( isset( $privacy['after_content'] ) ){
			?>
				<div class="row">
				    <div class="columns large-12">
					<p class="rtmedia-info rtmedia-admin-notice">
					    <?php echo $privacy['after_content']; ?>
					</p>
				    </div>
				</div>
			<?php
			    }
			}
		echo '</div>';
	}

	/**
	 * Render buddypress options.
	 *
	 * @access static
	 * @param  array $options
	 * @return array $render
	 */
	static function buddypress_render_options( $options ) {
		$render = array(
			'rtmedia-enable-on-profile' => array(
				'title' => __( 'Enable media in profile', 'rtmedia' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args' => array(
					'key' => 'buddypress_enableOnProfile',
					'value' => $options['buddypress_enableOnProfile'],
					'desc' => __( 'Enable Media on BuddyPress Profile', 'rtmedia' )
				)
			),
			'rtmedia-enable-on-group' => array(
				'title' => __( 'Enable media in group', 'rtmedia' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args' => array(
					'key' => 'buddypress_enableOnGroup',
					'value' => $options['buddypress_enableOnGroup'],
					'desc' => __( 'Enable Media on BuddyPress Groups', 'rtmedia' )
				)
			),
			'rtmedia-enable-on-activity' => array(
				'title' => __( 'Allow upload from activity stream', 'rtmedia' ),
				'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				'args' => array(
					'key' => 'buddypress_enableOnActivity',
					'value' => $options['buddypress_enableOnActivity'],
					'desc' => __( 'Allow upload using status update box present on activity stream page', 'rtmedia' ),
					'id' => "rtmedia-bp-enable-activity"
				)
			),
			'rtmedia-activity-feed-limit' => array(
				'title' => __( 'Number of media items to show in activity stream', 'rtmedia' ),
				'callback' => array( 'RTMediaFormHandler', 'number' ),
				'args' => array(
					'key' => 'buddypress_limitOnActivity',
					'value' => $options['buddypress_limitOnActivity'],
					'desc' => __( 'With bulk uploads activity stream may get flooded. You can control maximum number of medias/files per activity. This limit will not affect the actual number of uploads. Only display. <em>0</em> means unlimited.', 'rtmedia' ),
					'class' => array( 'rtmedia-setting-text-box rtmedia-bp-activity-setting' ),
					'min' => 0
				)
			)
		);

		return $render;
	}

	/**
	 * Define buddypress content.
	 *
	 * @access static
	 * @param  void
	 * @return void
	 */
	public static function buddypress_content() {
		global $rtmedia;
		$options = self::extract_settings( 'buddypress', $rtmedia->options );
	    ?>
		<div class="postbox metabox-holder">
		    <h3 class="hndle"><span>Integration With BuddyPress Features</span></h3>
	    <?php
		$render_data = self::buddypress_render_options( $options );

		echo '<div class="large-12">';
		foreach ( $render_data as $option ) { ?>
			<div class="row section">
				<div class="columns large-9">
				    <?php echo $option['title']; ?>
				</div>
				<div class="columns large-3">
				    <?php call_user_func( $option['callback'], $option['args'] ); ?>
				    <span data-tooltip class="has-tip" title="<?php echo ( isset( $option['args']['desc'] ) ) ? $option['args']['desc'] : "NA"; ?>"><i class="rtmicon-info-circle"></i></span>
				</div>
			</div>
		<?php }
		echo '</div>';
	    echo '</div>';
	    ?>
		    <div class="postbox metabox-holder">
		    <h3 class="hndle"><span>Album Settings</span></h3>
		    <?php
		    $options = $rtmedia->options;
		    $render_options = array(
				'general_enableAlbums' => array(
				    'title' => __( 'Organize media into albums', 'rtmedia' ),
				    'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
				    'args' => array(
					    'id' => 'rtmedia-album-enable',
					    'key' => 'general_enableAlbums',
					    'value' => $options['general_enableAlbums'],
					    'desc' => __( 'This will add \'album\' tab to BuddyPress profile and group depending on ^above^ settings.', 'rtmedia' )
				    )
				),
		    );
		    $render_options = apply_filters( "rtmedia_album_control_setting", $render_options, $options );
		    foreach ( $render_options as $tab => $option ) {
		    ?>
			    <div class="row section">
				    <div class="columns large-9">
					<?php echo $option['title']; ?>
				    </div>
				    <div class="columns large-3">
					<?php call_user_func( $option['callback'], $option['args'] ); ?>
					<span data-tooltip class="has-tip" title="<?php echo ( isset( $option['args']['desc'] ) ) ? $option['args']['desc'] : "NA"; ?>"><i class="rtmicon-info-circle"></i></span>
				    </div>
			    </div>
		    <?php
		    }
		    ?>
		</div>
	    <?php
	    do_action( "rtmedia_buddypress_setting_content" );
	}

	/**
	 * Define rtForm settings tabs content.
	 *
	 * @access static
	 * @param  type $page
	 * @param  array $sub_tabs
	 * @return void
	 */
	public static function rtForm_settings_tabs_content( $page, $sub_tabs ) {
	  	//  $rtmedia_admin_ui_handler = "<div class='section-container auto' data-options='deep_linking: true' data-section=''>";
	    //	echo "<div class='clearfix rtm-settings-tab-container'>";
	    $rtmedia_admin_ui_handler = "<div class='clearfix rtm-settings-tab-container horizontal-tabs'><dl class='tabs' data-tab>";
	    $rtmedia_admin_ui_handler = apply_filters( "rtmedia_admin_ui_handler_filter", $rtmedia_admin_ui_handler );
	    echo $rtmedia_admin_ui_handler;
	    $i = 1;
	    $sub_tabs = apply_filters( "rtmedia_pro_settings_tabs_content", $sub_tabs );
		ksort( $sub_tabs );
		foreach ( $sub_tabs as $tab ) {
	        $active_class = '';
	        if( $i == 1 ){
        		$active_class = 'active';
			}
			$i++;
	        if ( isset ( $tab[ 'icon' ] ) && ! empty ( $tab[ 'icon' ] ) ){
	            $icon = '<i class="' . $tab[ 'icon' ] . ' rtmicon-fw"></i>';
			}
	        echo '<dd class="' . $active_class . '"><a id="tab-' . substr ( $tab[ 'href' ], 1 ) . '" title="' . $tab[ 'title' ] . '" href="' . $tab[ 'href' ] . '" class="rtmedia-tab-title ' . sanitize_title ( $tab[ 'name' ] ) . '">' . $icon . $tab[ 'name' ] . '</a></dd>';
		}
            echo "</dl>";
            ?>

                <?php
                $rtmedia_admin_tab_content_handler = "<div class='tabs-content'>";
                $rtmedia_admin_tab_content_handler = apply_filters( "rtmedia_admin_tab_content_handler", $rtmedia_admin_tab_content_handler );
                echo $rtmedia_admin_tab_content_handler;
                $k = 1;
                foreach ( $sub_tabs as $tab ) {
                    $active_class = '';
                    if( $k == 1 ){
                    	$active_class = ' active';
					}
					$k++;
                    if ( isset ( $tab[ 'icon' ] ) && ! empty ( $tab[ 'icon' ] ) ){
                        $icon = '<i class="' . $tab[ 'icon' ] . '"></i>';
					}
                    $tab_without_hash = explode( "#", $tab[ 'href' ] );
                    $tab_without_hash  = $tab_without_hash[1];
                    echo '<div class="content' . $active_class .'" id="' . $tab_without_hash . '">';
					call_user_func( $tab['callback'], $page );
                    echo '</div>';
				}
                echo "</div>";
                ?>
                </div>
                <div class="clearfix"></div>
            <?php
	}

	/**
	 * Define rtForm do_settings_fields.
	 *
	 * @access static
	 * @param  type $page
	 * @param  type $section
	 * @return void
	 */
	public static function rtForm_do_settings_fields( $page, $section ) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[$page] ) || ! isset( $wp_settings_fields[$page][$section] ) ){
			return;
		}

		foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
			echo '<div class="row">';
			echo '<div class="large-11 columns">';

			if ( isset( $field['args']['label_for'] ) && ! empty( $field['args']['label_for'] ) ){
				call_user_func( $field['callback'], array_merge( $field['args'], array( 'label' => $field['args']['label_for'] ) ) );
			} else if ( isset( $field['title'] ) && ! empty( $field['title'] ) ){
				call_user_func( $field['callback'], array_merge( $field['args'], array( 'label' => $field['title'] ) ) );
			} else {
				call_user_func( $field['callback'], $field['args'] );
			}
			echo '</div>';
			echo '</div>';
		}
	}
}