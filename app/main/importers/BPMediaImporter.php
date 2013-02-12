<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaImporter
 *
 * @author saurabh
 */
class BPMediaImporter {

	/**
	 *
	 */
	function __construct() {

	}

	function import(){

		$this->process_media();

	}

	function process_media(){

		$this->import_privacy();
		$this->cleanup();

	}

	function cleanup(){

	}
	
	function import_privacy(){

	}

}

?>
