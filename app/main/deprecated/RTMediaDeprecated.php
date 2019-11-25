<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rtMediaDeprecated
 *
 * @author Udit Desai <udit.desai@rtcamp.com>
 */
class RTMediaDeprecated {
	/**
	 * Deprecate notice.
	 *
	 * @var bool
	 */
	public $deprecate_notice = false;

	/**
	 * Upload shortcode.
	 */
	public static function uploadshortcode() {
		$deprecated       = false;
		$deprecate_notice = '';
	}

	/**
	 * Add notice.
	 *
	 * @param string $method Method.
	 * @param bool   $deprecated Deprecated or not.
	 * @param string $notice Notice.
	 *
	 * @return string
	 */
	public static function generate_notice( $method, $deprecated = false, $notice = '' ) {
		// translators: method.
		return sprintf( esc_html__( 'Deprecated %1$s. Please use %2$s.', 'buddypress-media' ), $deprecated, $method );
	}
}
