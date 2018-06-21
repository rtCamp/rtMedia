<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 * @package    rtMedia
 */

if ( ! class_exists( 'rtFormsInvalidArgumentsException' ) ) {

	/**
	 * Description of rtException
	 *
	 * @author udit
	 */
	class rtFormInvalidArgumentsException extends Exception {

		/**
		 * The rtFormInvalidArgumentsException constructor.
		 *
		 * @param string $msg Message.
		 */
		public function __construct( $msg ) {

			// Error Message.
			// translators: Line number and file.
			$error_msg = sprintf( esc_html__( 'Error on line %1$s in %2$s : ', 'buddypress-media' ), $this->getLine(), $this->getFile() );
			// translators: message.
			$error_msg .= '<b>' . sprintf( esc_html__( 'The method expects an array in arguments for %s provided.', 'buddypress-media' ), $msg ) . '</b>';

			echo $error_msg; // @codingStandardsIgnoreLine
		}
	}
}
