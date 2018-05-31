<?php

/**
 * Author: Malav Vasita <malav.vasita@rtcamp.com> *
 */
class RTMediaUploadTermsAdmin {
	public $upload_terms_message;
	public $upload_terms_error_message;

	public function __construct() {
		$this->upload_terms_message = esc_html__( 'terms of services.', 'buddypress-media' );
		$this->upload_terms_error_message = esc_html__( 'Please check terms of service.', 'buddypress-media' );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ), 999 );
		add_filter( 'rtmedia_general_content_default_values', array( $this, 'add_admin_option_default_value' ), 10, 1 );
		add_filter( 'rtmedia_general_content_groups', array( $this, 'admin_setting_add_terms_section' ), 10, 1 );
		add_filter( 'rtmedia_general_content_add_itmes', array( $this, 'admin_setting_add_terms_option' ), 10, 2 );
	}

	/**
	 * loads styles and scripts
	 * @global type $rtmedia
	 */
	function enqueue_scripts_styles() {
		global $rtmedia;

		$suffix = ( function_exists( 'rtm_get_script_style_suffix' ) ) ? rtm_get_script_style_suffix() : '.min';

		wp_enqueue_script( 'rtmedia-upload-terms-main', RTMEDIA_URL . 'app/assets/js/admin-upload-terms' . $suffix . '.js', array( 'jquery' ), RTMEDIA_VERSION, true );
	}

	/**
	 * default option value for admin settings
	 * @param type $defaults
	 * @return array defaults
	 */
	function add_admin_option_default_value( $defaults ) {

		$defaults['general_enable_upload_terms'] = 0;
		$defaults['activity_enable_upload_terms'] = 0;
		$defaults['general_upload_terms_page_link'] = '';

		/**
		 * if `Terms of Service Message` and `Error Message` and not set from admin setting then set default value
		 */
		global $rtmedia;
		if (  empty( $rtmedia->options ) ) {
			$rtmedia->options = rtmedia_get_site_option( 'rtmedia-options' );
		}

		if ( ! empty( $rtmedia->options ) ) {
			$update = 0;
			if ( empty( $rtmedia->options['general_upload_terms_message'] )  ) {
				$rtmedia->options['general_upload_terms_message'] = $this->upload_terms_message;
				$update = 1;
			}
			if ( empty( $rtmedia->options['general_upload_terms_error_message'] ) ) {
				$rtmedia->options['general_upload_terms_error_message'] = $this->upload_terms_error_message;
				$update = 1;
			}

			if ( 1 == $update ) {
				rtmedia_update_site_option( 'rtmedia-options', $rtmedia->options );
			}
		}
		return $defaults;
	}

	/**
	 * add setting option in rtmedia settings
	 * @param array $general_group
	 * @return array $general_group
	 */
	function admin_setting_add_terms_section( $general_group ) {
		$general_group[40] = esc_html__( 'Ask users to agree to your terms', 'buddypress-media' );

		return $general_group;
	}

	/**
	 * configure admin options to render
	 * @param type $render_options
	 * @param type $options
	 * @return array $render_option
	 */
	function admin_setting_add_terms_option( $render_options, $options ) {
		$render_options['general_enable_upload_terms'] = array(
			'title' => __( 'Show "Terms of Service" checkbox on upload screen', 'buddypress-media' ),
			'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
			'args' => array(
				'key' => 'general_enable_upload_terms',
				'value' => $options['general_enable_upload_terms'],
				'desc' => __( 'User have to check the terms and conditions before uploading the media.', 'buddypress-media' ),
			),
			'group' => 40,
			'class' => 'aaaa',
		);
		$render_options['activity_enable_upload_terms'] = array(
			'title' => __( 'Show "Terms of Service" checkbox on activity screen', 'buddypress-media' ),
			'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
			'args' => array(
				'key' => 'activity_enable_upload_terms',
				'value' => $options['activity_enable_upload_terms'],
				'desc' => __( 'User have to check the terms and conditions before uploading the media.', 'buddypress-media' ),
			),
			'group' => 40,
		);
		$render_options['general_upload_terms_page_link'] = array(
			'title' => __( 'Link for "Terms of Service" page', 'buddypress-media' ),
			'callback' => array( 'RTMediaFormHandler', 'textbox' ),
			'args' => array(
				'key' => 'general_upload_terms_page_link',
				'value' => $options['general_upload_terms_page_link'],
				'desc' => __( 'Link to the terms and condition page where user can read terms and conditions.', 'buddypress-media' ),
			),
			'group' => 40,
		);
		// add extra field for admin setting
		$render_options['general_upload_terms_message'] = array(
			'title' => __( 'Terms of Service Message', 'buddypress-media' ),
			'callback' => array( 'RTMediaFormHandler', 'textbox' ),
			'args' => array(
				'key' => 'general_upload_terms_message',
				'value' => isset( $options['general_upload_terms_message'] ) ? $options['general_upload_terms_message'] : $this->upload_terms_message,
				'desc' => __( 'Add Terms of Service Message.', 'buddypress-media' ),
			),
			'group' => 40,
		);
		$render_options['general_upload_terms_error_message'] = array(
			'title' => __( 'Error Message', 'buddypress-media' ),
			'callback' => array( 'RTMediaFormHandler', 'textbox' ),
			'args' => array(
				'key' => 'general_upload_terms_error_message',
				'value' => isset( $options['general_upload_terms_error_message'] ) ? $options['general_upload_terms_error_message'] : $this->upload_terms_error_message,
				'desc' => __( 'Display Error Message When User Upload Media Without selecting checkbox .', 'buddypress-media' ),
			),
			'group' => 40,
		);

		return $render_options;
	}
}

// Instantiate object
new RTMediaUploadTermsAdmin();
