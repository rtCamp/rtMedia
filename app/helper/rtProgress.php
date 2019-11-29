<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 * @package    rtMedia
 */

/**
 * Description of rtProgress
 *
 * @author saurabh
 */
class rtProgress { // phpcs:ignore PEAR.NamingConventions.ValidClassName.StartWithCapital, Generic.Classes.OpeningBraceSameLine.ContentAfterBrace

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Show progress_ui.
	 *
	 * @access public
	 *
	 * @param  float $progress Progress.
	 * @param  bool  $echo Echo.
	 *
	 * @return string $progress_ui
	 */
	public function progress_ui( $progress, $echo = true ) {
		$progress_ui = '
			<div id="rtprogressbar">
				<div style="width:' . esc_attr( $progress ) . '%"></div>
			</div>
			';

		if ( $echo ) {
			echo $progress_ui; // @codingStandardsIgnoreLine
		} else {
			return $progress_ui;
		}
	}

	/**
	 * Calculate progress %.
	 *
	 * @access public
	 *
	 * @param  float $progress Progress.
	 * @param  float $total Total.
	 *
	 * @return float
	 */
	public function progress( $progress, $total ) {
		if ( $total < 1 ) {
			return 100;
		}

		return ( $progress / $total ) * 100;
	}
}
