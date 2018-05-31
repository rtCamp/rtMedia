<?php
/**
 * Author: Malav Vasita <malav.vasita@rtcamp.com> *
 */
class RTMediaUploadTerms {

	function __construct() {
		$this->load_translation();
		// enqueue js and css files
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ), 999 );

		// filter into uploader to show checkbox
		add_filter( 'rtmedia_uploader_before_start_upload_button', array(
			$this,
			'show_terms_and_service_checkbox',
		), 199, 1 );
		// Filter into activity uploader to show checkbox
		add_filter( 'rtmedia_uploader_after_activity_upload_button', array(
			$this,
			'show_terms_and_service_checkbox_activity',
		), 199, 1 );
	}

	/**
	 * loads translation
	 */
	public function load_translation() {
		load_plugin_textdomain( 'rtmedia', false, basename( RTMEDIA_PATH ) . '/languages/' );
	}

	/**
	 * loads styles and scripts
	 * @global type $rtmedia
	 */
	function enqueue_scripts_styles() {
		global $rtmedia;

		$suffix = ( function_exists( 'rtm_get_script_style_suffix' ) ) ? rtm_get_script_style_suffix() : '.min';

		if ( ! ( isset( $rtmedia->options ) && isset( $rtmedia->options['styles_enabled'] ) && $rtmedia->options['styles_enabled'] == 0 ) ) {
			wp_enqueue_style( 'rtmedia-upload-terms-main', RTMEDIA_URL . 'app/assets/css/rtm-upload-terms' . $suffix . '.css', '', RTMEDIA_VERSION );
		}
		wp_enqueue_script( 'rtmedia-upload-terms-main', RTMEDIA_URL . 'app/assets/js/rtm-upload-terms' . $suffix . '.js', array( 'jquery' ), RTMEDIA_VERSION, true );
		wp_localize_script( 'rtmedia-upload-terms-main', 'rtmedia_upload_terms_check_terms_message', apply_filters( 'rtmedia_upload_terms_check_terms_message', esc_html__( $rtmedia->options['general_upload_terms_error_message'], 'buddypress-media' ) ) );
		wp_localize_script( 'rtmedia-upload-terms-main', 'rtmedia_upload_terms_check_terms_default_message', apply_filters( 'rtmedia_upload_terms_check_terms_default_message', esc_html__( 'Please check Terms of Service.', 'buddypress-media' ) ) );
	}

	/**
	 * render terms and service checkbox in media upload tab
	 * @global type $rtmedia
	 *
	 * @param type $content
	 *
	 * @return type
	 */
	function show_terms_and_service_checkbox( $content ) {
		global $rtmedia;

		$options       = $rtmedia->options;
		$terms_content = '';

		if ( ( isset( $options['general_enable_upload_terms'] ) && '0' != $options['general_enable_upload_terms'] ) && ( isset( $options['general_upload_terms_page_link'] ) && '' != $options['general_upload_terms_page_link'] ) ) {
			$terms_content = $this->terms_and_service_checkbox_html( $options );
		}

		return $content . $terms_content;
	}

	/**
	 * @param $content
	 *
	 * @return string
	 */
	function show_terms_and_service_checkbox_activity( $content ) {
		global $rtmedia;

		$options       = $rtmedia->options;
		$terms_content = '';

		if ( ( isset( $options['activity_enable_upload_terms'] ) && '0' != $options['activity_enable_upload_terms'] ) && ( isset( $options['general_upload_terms_page_link'] ) && '' != $options['general_upload_terms_page_link'] ) ) {
			$terms_content = $this->terms_and_service_checkbox_html( $options );
		}

		return $content . $terms_content;
	}

	/**
	 * @return string
	 */
	function terms_and_service_checkbox_html( $options ) {
		$content = '<div class="rtmedia-upload-terms"> '
		           . '<input type="checkbox" name="rtmedia_upload_terms_conditions" id="rtmedia_upload_terms_conditions" /> '
		           . '<label for="rtmedia_upload_terms_conditions">'
		           . apply_filters( 'rtmedia_upload_terms_service_agree_label', esc_html__( 'I agree to', 'buddypress-media' ) )
		           . '&nbsp;'
		           . "<a href='" . $options['general_upload_terms_page_link'] . "' target='_blank'>"
		           // . apply_filters( 'rtmedia_upload_terms_service_link_label', __( 'Terms of Service', 'rtmedia' ) )
		           . apply_filters( 'rtmedia_upload_terms_service_link_label', esc_html__( $options['general_upload_terms_message'], 'buddypress-media' ) )
		           . '</a>'
		           . '</label>'
		           . '</div>';

		return $content;
	}
}
// Instantiate object
new RTMediaUploadTerms();