<?php
/**
 * Created by PhpStorm.
 * User: ritz <ritesh.patel@rtcamp.com>
 * Date: 11/9/14
 * Time: 2:32 PM
 */

if( !class_exists( 'RTDBModel' ) ){
	return;
}

class RTMediaActivityModel extends RTDBModel {

	function __construct () {
		parent::__construct ( 'rtm_activity' );
	}

	public function check( $activity_id = "") {
		if( $activity_id == ""  ){
			return false;
		}

		$columns = array(
			'activity_id' => $activity_id
		);

		$results = $this->get( $columns, false, false, 'activity_id DESC' );

		if( $results ){
			return true;
		} else {
			return false;
		}
	}

} 