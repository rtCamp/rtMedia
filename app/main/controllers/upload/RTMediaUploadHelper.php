<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaPLUploadHelper
 *
 * @author Udit Desai <udit.desai@rtcamp.com>
 */
class RTMediaUploadHelper {

	public function __construct() {

	}
	
	static function file_upload() {

		$end_point = new RTMediaUploadEndpoint();
		$end_point->template_redirect();
	}
}



?>
