<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rtProgress
 *
 * @author saurabh
 */
class rtProgress {

	/**
	 *
	 */
	function __construct() {

	}

	function progress_ui($progress){
		echo '
			<div id="rtprogressbar">
				<div style="width:'.$progress.'%"></div>
			</div>
			';
	}

	function progress($progress,$total){
		return ($progress/$total)*100;
	}

}

?>
