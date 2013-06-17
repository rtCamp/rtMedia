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

	var $rt_media_comment_model;

	public function __construct() {
		$this->rt_media_comment_model = new RTMediaCommentModel();
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

		do_action('rt_media_before_add_comment', $attr);

		$attr['comment_author'] = $this->get_current_author();
		$attr['user_id'] = $this->get_current_id();
		$attr['comment_date'] = current_time('mysql');
		$id = $this->rt_media_comment_model->insert($attr);

		do_action('rt_media_before_add_comment', $attr);

		return $id;
	}

	function remove($id) {

		do_action('rt_media_before_add_comment', $attr);
	}
}

?>
