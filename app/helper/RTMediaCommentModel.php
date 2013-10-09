<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaCommentModel
 *
 * @author Udit Desai <udit.desai@rtcamp.com>
 */
class RTMediaCommentModel {

	public function __construct() {
		//initialization
	}

	function insert($attr) {
		return wp_insert_comment($attr);
	}

	function update($attr) {

		return wp_update_comment($attr, ARRAY_A);
	}

	function get($where) {

		return get_comments($where);
	}

	function get_by_id($id) {

		return get_comment($id);
	}

	function delete($id) {

		return wp_delete_comment($id, true);
	}
}
