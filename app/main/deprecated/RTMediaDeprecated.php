<?php
/**
 * Show deprecated functions.
 *
 * @package    rtMedia
 */

/**
 * RTMedia class to show deprecated functions.
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
	 * Add notice for deprecated method.
	 *
	 * @param string $method Method name.
	 * @param bool   $deprecated Deprecated or not.
	 * @param string $notice Notice to show.
	 *
	 * @return string
	 */
	public static function generate_notice( $method, $deprecated = false, $notice = '' ) {
		// translators: 1: function name, 2: method.
		return sprintf( esc_html__( 'Deprecated %1$s. Please use %2$s.', 'buddypress-media' ), $deprecated, $method );
	}
}
