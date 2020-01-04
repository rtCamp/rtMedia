<?php
/**
 * Adds class to Show progress-bar
 *
 * @package rtMedia
 */

/**
 * Class to show progress-bar.
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
	 * Show progress-bar ui.
	 *
	 * @access public
	 *
	 * @param  float $progress Current progress.
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
			echo wp_kses( $progress_ui, RTMedia::expanded_allowed_tags() );
		} else {
			return $progress_ui;
		}
	}

	/**
	 * Calculate progress %.
	 *
	 * @access public
	 *
	 * @param  float $progress Current progress.
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
