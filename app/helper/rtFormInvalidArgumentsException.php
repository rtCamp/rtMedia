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

if ( ! class_exists( 'rtFormsInvalidArgumentsException' ) ) {

	class rtFormInvalidArgumentsException extends Exception {

		public function __construct( $msg ) {

			//Error Message
			$errorMsg = sprintf( esc_html__( 'Error on line %s in %s : ', 'buddypress-media' ), $this->getLine(), $this->getFile() );
			$errorMsg .= '<b>' . sprintf( esc_html__( 'The method expects an array in arguments for %s provided.', 'buddypress-media' ), $msg ) . '</b>';

			echo $errorMsg; // @codingStandardsIgnoreLine
		}
	}
}
