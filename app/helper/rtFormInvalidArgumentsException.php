<?php
/**
 * Handles invalid argument exception.
 *
 * @package rtMedia
 */

if ( ! class_exists( 'rtFormsInvalidArgumentsException' ) ) {

	/**
	 * Class to throw invalid argument exception.
	 *
	 * @author udit
	 */
	class rtFormInvalidArgumentsException extends Exception { // phpcs:ignore PEAR.NamingConventions.ValidClassName.StartWithCapital, Generic.Classes.OpeningBraceSameLine.ContentAfterBrace

		/**
		 * The rtFormInvalidArgumentsException constructor.
		 *
		 * @param string $msg Message.
		 */
		public function __construct( $msg ) {

			parent::__construct( $msg );

			// Error Message.
			// translators: 1: Line number, 2: file.
			$error_msg = sprintf( esc_html__( 'Error on line %1$s in %2$s : ', 'buddypress-media' ), $this->getLine(), $this->getFile() );
			// translators: %s: message.
			$error_msg .= '<b>' . sprintf( esc_html__( 'The method expects an array in arguments for %s provided.', 'buddypress-media' ), $msg ) . '</b>';

			echo wp_kses(
				$error_msg,
				array(
					'b' => array(),
				)
			);
		}
	}
}
