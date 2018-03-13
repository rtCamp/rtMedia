<?php

/**
 * Description of RTMediaUploadShortcode
 *
 * rtMedia uploader shortcode
 *
 * @author joshua
 */
class RTMediaUploadShortcode {

	static $add_sc_script = false;
	var $deprecated = false;
	static $uploader_displayed = false;

	/**
	 *
	 */
	public function __construct() {

		add_shortcode( 'rtmedia_uploader', array( 'RTMediaUploadShortcode', 'pre_render' ) );
		$method_name = strtolower( str_replace( 'RTMedia', '', __CLASS__ ) );

		if ( is_callable( "RTMediaDeprecated::{$method_name}", true, $callable_name ) ) {
			$this->deprecated = RTMediaDeprecated::$method_name ();
		}
	}

	/**
	 * Helper function to check whether the shortcode should be rendered or not
	 *
	 * @return bool
	 */
	static function display_allowed() {
		global $rtmedia_query;
		$media_enabled = ( is_rtmedia_upload_music_enabled() || is_rtmedia_upload_photo_enabled()
			|| is_rtmedia_upload_video_enabled() || is_rtmedia_upload_document_enabled()
			|| is_rtmedia_upload_other_enabled() );
		$flag          = ( ! ( is_home() || is_post_type_archive() || is_author() ) )
		&& is_user_logged_in()
		&& ( $media_enabled )
		// Added condition to disable upload when media is disabled in profile/group but user visits media tab.
		&& ( ( isset( $rtmedia_query->is_upload_shortcode ) && true === $rtmedia_query->is_upload_shortcode )
				|| ( is_rtmedia_bp_profile() && is_rtmedia_profile_media_enable() )
				|| ( is_rtmedia_bp_group() && is_rtmedia_group_media_enable() ) );

		$flag = apply_filters( 'before_rtmedia_uploader_display', $flag );
		return $flag;
	}

	/**
	 * Render the uploader shortcode and attach the uploader panel
	 *
	 * @param mixed $attr
	 *
	 * @return string|void
	 */
	static function pre_render( $attr ) {
		if ( rtmedia_is_uploader_view_allowed( true, 'uploader_shortcode' ) ) {
			global $post;
			global $rtmedia_query;
			if ( ! $rtmedia_query ) {
				$rtmedia_query = new RTMediaQuery(); }
			if ( ! isset( $attr['is_up_shortcode'] ) || false !== $attr['is_up_shortcode'] ) {
				$rtmedia_query->is_upload_shortcode = true;// set is_upload_shortcode in rtmedia query as true
			} else {
				$rtmedia_query->is_upload_shortcode = false;// set is_upload_shortcode in rtmedia query as true
			}

			if ( isset( $attr['media_type'] ) ) {
				global $rtmedia;
				$allowed_media_type = $rtmedia->allowed_types;
				if ( isset( $allowed_media_type[ $attr['media_type'] ] ) ) {
					wp_localize_script( 'rtmedia-backbone', 'rtmedia_upload_type_filter', $allowed_media_type[ $attr['media_type'] ]['extn'] );
				}
			}

			if ( isset( $attr ) && ! empty( $attr ) ) {
				if ( ! is_array( $attr ) ) {
					$attr = array();
				}
				if ( ! isset( $attr['context'] ) && isset( $post->post_type ) ) {
					$attr['context'] = $post->post_type;
				}
			}
			$attr = apply_filters( 'rtmedia_media_uploader_attributes', $attr );

			if ( self::display_allowed() || ( isset( $attr['allow_anonymous'] ) && true === $attr['allow_anonymous'] ) ) {
				if ( ! _device_can_upload() ) {
					echo '<p>' . esc_html_e( 'The web browser on your device cannot be used to upload files.', 'buddypress-media' ) . '</p>';
					return;
				}
				ob_start();

				self::$add_sc_script = true;
				RTMediaUploadTemplate::render( $attr );

				self::$uploader_displayed = true;
				return ob_get_clean();
			}
		} else {
			echo "<div class='rtmedia-upload-not-allowed'>" . wp_kses( apply_filters( 'rtmedia_upload_not_allowed_message', esc_html__( 'You are not allowed to upload/attach media.','buddypress-media' ), 'uploader_shortcode' ), RTMediaUpload::$wp_kses_allowed_tags ) . '</div>';
		}
	}
}
