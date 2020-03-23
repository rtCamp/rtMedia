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
		 * Enqueuing scripts and styles along with data to be rendered in user side.
		 */
		public function __construct() {
			$this->load_translation();
			// Enqueue js and css files.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ), 999 );

			// Filter into uploader to show checkbox.
			add_filter(
				'rtmedia_uploader_before_start_upload_button',
				array(
					$this,
					'show_terms_and_service_checkbox',
				),
				199,
				1
			);
			// Filter into activity uploader to show checkbox.
			add_filter(
				'rtmedia_uploader_after_activity_upload_button',
				array(
					$this,
					'show_terms_and_service_checkbox_activity',
				),
				199,
				1
			);
			// Detect whether terms condition checkbox is displayed in a widget.
			add_filter(
				'dynamic_sidebar_params',
				array(
					$this,
					'dynamic_sidebar_params',
				),
				199,
				1
			);
		}

		/**
		 * Set global var whether it's inside rtMedia Sidebar Uploader Widget widget.
		 *
		 * @param array $params Widget display arguments.
		 * @return array Modified Widget display arguments.
		 */
		public function dynamic_sidebar_params( $params ) {
			if ( ! empty( $params ) && is_array( $params ) ) {
				// Iterate params and find widget_name.
				foreach ( $params as $key => $value ) {
					// If widget is sidebar uploader widget, set global.
					if ( ! empty( $value['widget_name'] ) && 'rtMedia Sidebar Uploader Widget' === $value['widget_name'] ) {
						global $rtmedia_uploader_widget;
						$rtmedia_uploader_widget = true;

						break;
					}
				}
			}

			return $params;
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

				wp_localize_script( 'rtmedia-main', 'rtmedia_upload_terms_check_terms_message', $general_upload_terms_error_message );
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
			$id = 'rtmedia_upload_terms_conditions';

			global $rtmedia_uploader_widget;
			// Set different terms condition checkbox ID if this is in a widget.
			if ( $rtmedia_uploader_widget ) {
				$id = 'rtmedia_widget_upload_terms_conditions';
				// Set global to false to stop this change for checkboxes on other place.
				$rtmedia_uploader_widget = false;
			}

			$general_upload_terms_page_link = $options['general_upload_terms_page_link'];
			$general_upload_terms_message   = $options['general_upload_terms_message'];
			ob_start();
			?>
			<div class="rtmedia-upload-terms">
				<input type="checkbox" name="rtmedia_upload_terms_conditions" id="<?php echo esc_attr( $id ); ?>" />
				<label for="<?php echo esc_attr( $id ); ?>">
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
