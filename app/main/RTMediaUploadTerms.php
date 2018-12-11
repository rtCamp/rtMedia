<?php
/**
 * This file applies the admin settings and handle feature of Upload Terms at front-end side.
 *
 * @package rtMedia
 * @author  Malav Vasita <malav.vasita@rtcamp.com>
 */

/**
 * We will first check if rtmedia-upload-terms plugin is activate before putting this code in action.
 * RTMediaUploadTerms class would come in picture if and only if rtmedia-upload-terms plugin is deactivated.
 */
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( ! class_exists( 'RTMediaUploadTerms' ) && ! is_plugin_active( 'rtmedia-upload-terms/index.php' ) ) {

	/**
	 * Class for Upload terms as per applied settings in rtMedia settings.
	 */
	class RTMediaUploadTerms {

		/**
		 * Enquing scripts and styles along with data to be rendered in user side.
		 */
		public function __construct() {
			$this->load_translation();
			// Enqueue js and css files.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ), 999 );

			// Filter into uploader to show checkbox.
			add_filter(
				'rtmedia_uploader_before_start_upload_button', array(
					$this,
					'show_terms_and_service_checkbox',
				), 199, 1
			);
			// Filter into activity uploader to show checkbox.
			add_filter(
				'rtmedia_uploader_after_activity_upload_button', array(
					$this,
					'show_terms_and_service_checkbox_activity',
				), 199, 1
			);
		}

		/**
		 * Loads translation
		 */
		public function load_translation() {
			load_plugin_textdomain( 'rtmedia', false, basename( RTMEDIA_PATH ) . '/languages/' );
		}

		/**
		 * Loads styles and scripts
		 *
		 * @global object $rtmedia
		 */
		public function enqueue_scripts_styles() {
			global $rtmedia;

			$suffix                             = ( function_exists( 'rtm_get_script_style_suffix' ) ) ? rtm_get_script_style_suffix() : '.min';
			$general_upload_terms_error_message = apply_filters( 'rtmedia_upload_terms_check_terms_message', $rtmedia->options['general_upload_terms_error_message'] );

			if ( ! ( isset( $rtmedia->options ) && isset( $rtmedia->options['styles_enabled'] ) && 0 === $rtmedia->options['styles_enabled'] ) ) {
				wp_enqueue_style( 'rtmedia-upload-terms-main', RTMEDIA_URL . 'app/assets/css/rtm-upload-terms' . $suffix . '.css', '', RTMEDIA_VERSION );
			}

			if ( ! empty( $rtmedia->options['general_enable_upload_terms'] ) || ! empty( $rtmedia->options['activity_enable_upload_terms'] ) ) {
				wp_enqueue_script( 'rtmedia-upload-terms-main', RTMEDIA_URL . 'app/assets/js/rtm-upload-terms' . $suffix . '.js', array( 'jquery' ), RTMEDIA_VERSION, true );
				wp_localize_script(
					'rtmedia-upload-terms-main',
					'rtmedia_upload_terms_data',
					array(
						'message'                => esc_js( $general_upload_terms_error_message ),
						'activity_terms_enabled' => ( ! empty( $rtmedia->options['activity_enable_upload_terms'] ) ) ? esc_js( 'true' ) : esc_js( 'false' ),
						'uploader_terms_enabled' => ( ! empty( $rtmedia->options['general_enable_upload_terms'] ) ) ? esc_js( 'true' ) : esc_js( 'false' ),
					)
				);

				wp_localize_script( 'rtmedia-main', 'rtmedia_upload_terms_check_terms_message', esc_js( $general_upload_terms_error_message ) );
			}
		}

		/**
		 * Render terms and service checkbox in media upload tab.
		 *
		 * @global object $rtmedia
		 * @param  string $content Content after I agree checkbox.
		 * @return string
		 */
		public function show_terms_and_service_checkbox( $content ) {
			global $rtmedia;

			$options       = $rtmedia->options;
			$terms_content = '';

			if ( ( ! empty( $options['general_enable_upload_terms'] ) && '0' !== $options['general_enable_upload_terms'] ) && ( ! empty( $options['general_upload_terms_page_link'] ) && '' !== $options['general_upload_terms_page_link'] ) ) {
				$terms_content = $this->terms_and_service_checkbox_html( $options );
			}

			return $content . $terms_content;
		}

		/**
		 * Adding checkbox on user activity screen.
		 *
		 * @param  string $content Content for adding checkbox on user activity screen.
		 * @return string
		 */
		public function show_terms_and_service_checkbox_activity( $content ) {
			global $rtmedia;

			$options       = $rtmedia->options;
			$terms_content = '';

			if ( ( ! empty( $options['activity_enable_upload_terms'] ) && '0' !== $options['activity_enable_upload_terms'] ) && ( ! empty( $options['general_upload_terms_page_link'] ) && '' !== $options['general_upload_terms_page_link'] ) ) {
				$terms_content = $this->terms_and_service_checkbox_html( $options );
			}

			return $content . $terms_content;
		}

		/**
		 * Checkbox of agree terms and condition at front-end.
		 *
		 * @param array $options Options set from rtMedia settings.
		 *
		 * @return string
		 */
		public function terms_and_service_checkbox_html( $options ) {
			$general_upload_terms_page_link = $options['general_upload_terms_page_link'];
			$general_upload_terms_message   = $options['general_upload_terms_message'];
			$content                        = '';
			ob_start();
			?>
			<div class="rtmedia-upload-terms">
				<input type="checkbox" name="rtmedia_upload_terms_conditions" id="rtmedia_upload_terms_conditions" />
				<label for="rtmedia_upload_terms_conditions">
				<?php echo esc_html( apply_filters( 'rtmedia_upload_terms_service_agree_label', __( 'I agree to', 'buddypress-media' ) ) ); ?>
				<a href='<?php echo esc_url( $general_upload_terms_page_link ); ?>' target='_blank'>
				<?php echo esc_html( apply_filters( 'rtmedia_upload_terms_service_link_label', $general_upload_terms_message ) ); ?>
				</a>
				</label>
			</div>
			<?php

			$content = ob_get_clean();
			return $content;
		}
	}
	// Instantiate object.
	new RTMediaUploadTerms();
}
