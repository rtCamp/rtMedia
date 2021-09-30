<?php
/**
 * Handles rtMedia login popup.
 *
 * @package rtMedia
 * @author sanket
 */

/**
 * Class to handle login/register popups.
 */
class RTMediaLoginPopup {

	/**
	 * RTMediaLoginPopup constructor.
	 */
	public function __construct() {
		add_action( 'rtmedia_album_gallery_actions', array( $this, 'rtmedia_add_upload_album_button_popup' ), 99 );
		add_action( 'rtmedia_media_gallery_actions', array( $this, 'rtmedia_add_upload_album_button_popup' ), 99 );
		add_action( 'rtmedia_before_media_gallery', array( $this, 'rtmedia_login_register_modal_popup' ) );
		add_action( 'rtmedia_before_album_gallery', array( $this, 'rtmedia_login_register_modal_popup' ) );
		// remove rtMedia Pro actions.
		add_action( 'rtmedia_before_media_gallery', array( $this, 'remove_rtmedia_media_pro_hooks' ) );
		add_action( 'rtmedia_before_album_gallery', array( $this, 'remove_rtmedia_album_pro_hooks' ) );
	}

	/**
	 * Remove rtmedia pro hooks.
	 */
	public function remove_rtmedia_media_pro_hooks() {
		remove_action( 'rtmedia_media_gallery_actions', 'rtmedia_add_upload_album_button', 99 );
		remove_action( 'rtmedia_before_media_gallery', 'rtmedia_login_register_modal' );
	}

	/**
	 * Remove rtmedia pro album hooks.
	 */
	public function remove_rtmedia_album_pro_hooks() {
		remove_action( 'rtmedia_album_gallery_actions', 'rtmedia_add_upload_album_button', 99 );
		remove_action( 'rtmedia_before_album_gallery', 'rtmedia_login_register_modal' );
	}

	/**
	 * Upload album button popup.
	 */
	public function rtmedia_add_upload_album_button_popup() {
		if ( ! is_user_logged_in() ) {
			echo '<span><a href="#rtmedia-login-register-modal" class="primary rtmedia-upload-media-link rtmedia-modal-link" id="rtmedia-login-register-modal" title="' . esc_attr( apply_filters( 'rtm_album_upload_title_label', __( 'Upload Media', 'buddypress-media' ) ) ) . '"><i class="dashicons dashicons-upload"></i>' . esc_html( apply_filters( 'rtm_album_upload_label', __( 'Upload', 'buddypress-media' ) ) ) . '</a></span>';
		}
	}

	/**
	 * Login/register popup display.
	 */
	public function rtmedia_login_register_modal_popup() {

		if ( ! is_user_logged_in() ) {
			$uri = rtm_get_server_var( 'REQUEST_URI', 'FILTER_SANITIZE_URL' );
			?>
			<div class="rtmedia-popup mfp-hide rtm-modal" id="rtmedia-login-register-modal">
				<div id="rtm-modal-container">
					<h2 class="rtm-modal-title"><?php esc_html_e( 'Please login', 'buddypress-media' ); ?></h2>

					<p><?php esc_html_e( 'You need to be logged in to upload Media or to create Album.', 'buddypress-media' ); ?></p>

					<p>
						<?php echo esc_html__( 'Click', 'buddypress-media' ) . ' <a href="' . esc_url( wp_login_url( $uri ) ) . '" title="' . esc_attr__( 'Login', 'buddypress-media' ) . '">' . esc_html__( 'HERE', 'buddypress-media' ) . '</a>' . esc_html__( ' to login.', 'buddypress-media' ); ?>
					</p>
				</div>
			</div>
			<?php
		}
	}
}
