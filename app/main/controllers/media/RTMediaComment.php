<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaComment
 *
 * @author udit
 */
class RTMediaComment {

	var $rtmedia_comment_model;

	public function __construct() {
		$this->rtmedia_comment_model = new RTMediaCommentModel();
	}

	static function comment_nonce_generator($echo = true) {
		if($echo) {
			wp_nonce_field('rtmedia_comment_nonce','rtmedia_comment_nonce');
		} else {
			$token = array(
				'action' => 'rtmedia_comment_nonce',
				'nonce' => wp_create_nonce('rtmedia_comment_nonce')
			);

			return json_encode($token);
		}
	}

	/**
	 * returns user_id of the current logged in user in wordpress
	 *
	 * @global type $current_user
	 * @return type
	 */
	function get_current_id() {

		global $current_user;
		get_currentuserinfo();
		return $current_user->ID;
	}

	/**
	 * returns user_id of the current logged in user in wordpress
	 *
	 * @global type $current_user
	 * @return type
	 */
	function get_current_author() {

		global $current_user;
		get_currentuserinfo();
		return $current_user->user_login;
	}

	function add($attr) {

		do_action('rtmedia_before_add_comment', $attr);

		$attr['comment_author'] = $this->get_current_author();
		$attr['user_id'] = $this->get_current_id();
		$attr['comment_date'] = current_time('mysql');
		$id = $this->rtmedia_comment_model->insert($attr);

		do_action('rtmedia_after_add_comment', $attr);

		return $id;
	}

	function remove($id) {

		do_action('rtmedia_before_remove_comment', $attr);
	}
}

?>
