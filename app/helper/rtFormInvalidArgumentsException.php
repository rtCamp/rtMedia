<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rtException
 *
 * @author udit
 */

if(!class_exists("rtFormsInvalidArgumentsException")) {

	class rtFormInvalidArgumentsException extends Exception {

	    public function __construct($msg) {

	    	//Error Message
	    	$errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile() .
	                        ' : <b>The method expects an array in arguments for ' . $msg . ' provided.</b>';

	        echo $errorMsg;
	    }
	}
}

?>
