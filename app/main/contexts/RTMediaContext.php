<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaContext
 *
 * @author saurabh
 */
class RTMediaContext {

	public $type,$id;

	function __construct() {
		$this->set_context();
		return $this;
	}

	function set_context() {
        if (class_exists('BuddyPress')) {
            $this->set_bp_context();
        } else {
            $this->set_wp_context();
        }
    }

    function set_wp_context() {
        global $post;
		print_r($post);
		//if(isset($post->post_type)){
			$this->type = $post->post_type;
			$this->id = $post->ID;
		//}
    }

    function set_bp_context() {
        if (bp_is_blog_page()) {
            $this->set_wp_context();
        } else {
            $this->set_bp_component_context();
        }
    }

    function set_bp_component_context() {
        $this->type = bp_current_component();
        $this->id = $this->get_current_bp_component_id();
    }

    function get_current_bp_component_id() {
        switch (bp_current_component()) {
            case 'groups': return bp_get_current_group_id();
                break;
            default:
                return bp_loggedin_user_id();
                break;
        }
    }


}

?>
