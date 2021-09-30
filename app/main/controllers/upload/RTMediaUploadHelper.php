<?php
/**
 * Helper class for PL Upload - Upload files via AJAX Request
 *
 * @package rtMedia
 * @author Udit Desai <udit.desai@rtcamp.com>
 */

/**
 * Helper class for PL Upload - Upload files via AJAX Request
 */
class RTMediaUploadHelper {

	/**
	 * RTMediaUploadHelper constructor.
	 */
	public function __construct() {}

	/**
	 * Function to perform file upload.
	 */
	public static function file_upload() {

		$end_point = new RTMediaUploadEndpoint();
		$end_point->template_redirect();
	}
}
