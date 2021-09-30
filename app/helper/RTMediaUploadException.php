<?php
/**
 * Upload exception.
 *
 * @package    rtMedia
 */

/**
 * Class to handle media upload exceptions.
 *
 * @author joshua
 */
class RTMediaUploadException extends Exception {

	/**
	 * Exception for Invalid context while uploading any media
	 *
	 * @var int
	 */
	public $upload_err_invalid_context = 9;

	/**
	 * Constructs the class.
	 *
	 * @param string      $code Code.
	 * @param string|bool $msg Message.
	 */
	public function __construct( $code, $msg = false ) {
		$message = $this->codeToMessage( $code, $msg );
		parent::__construct( $message, $code );
	}

	/**
	 * Error specific Message generated for the exception depending upon the code passed.
	 * Native Error Codes defined in PHP core module are used for uploading a standard file
	 *
	 * @param string $code Code.
	 * @param string $msg Message.
	 *
	 * @return string
	 */
	private function codeToMessage( $code, $msg ) {
		switch ( $code ) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$message = apply_filters( 'bp_media_file_size_error', esc_html__( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'buddypress-media' ) );
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = apply_filters( 'bp_media_file_null_error', esc_html__( 'No file was uploaded', 'buddypress-media' ) );
				break;
			case UPLOAD_ERR_PARTIAL:
			case UPLOAD_ERR_NO_TMP_DIR:
			case UPLOAD_ERR_CANT_WRITE:
				$message = apply_filters( 'bp_media_file_internal_error', esc_html__( 'Uploade failed due to internal server error.', 'buddypress-media' ) );
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = apply_filters( 'bp_media_file_extension_error', esc_html__( 'File type not allowed.', 'buddypress-media' ) );
				break;

			case $this->upload_err_invalid_context:
				$message = apply_filters( 'rtmedia_invalid_context_error', esc_html__( 'Invalid Context for upload.', 'buddypress-media' ) );
				break;
			default:
				$msg     = $msg ? $msg : esc_html__( 'Unknown file upload error.', 'buddypress-media' );
				$message = apply_filters( 'bp_media_file_unknown_error', $msg );
				break;
		}

		return $message;
	}
}
