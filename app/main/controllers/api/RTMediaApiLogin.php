<?php
/**
 * Initialize RTDBModel with rtm_api table.
 *
 * @package rtMedia
 * @author Umesh Kumar<umeshsingla05@gmail.com>
 */

if ( ! class_exists( 'RTDBModel' ) ) {
	return;
}

/**
 * Class to initialize RTDBModel with rtm_api table.
 */
class RTMediaApiLogin extends RTDBModel {

	/**
	 * RTMediaApiLogin constructor.
	 */
	public function __construct() {
		parent::__construct( 'rtm_api' );
	}
}
