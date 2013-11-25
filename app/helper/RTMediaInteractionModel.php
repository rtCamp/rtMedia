<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaInteractionModel
 *
 * @author ritz
 */
class RTMediaInteractionModel extends RTDBModel {

    function __construct () {
        parent::__construct ( 'rtm_media_interaction', false, 10, true );
    }

    function check($user_id = "", $media_id = "", $action = "") {
        if($user_id == "" || $media_id == "" || $action == "")
            return false;
        $columns = array(
            'user_id' => $user_id,
            'media_id' => $media_id,
            'action' => $action
        );
        $results = $this->get($columns);
        if($results)
            return true;
        else
            return false;
    }

    function get_row($user_id = "", $media_id = "", $action = "") {
        if($user_id == "" || $media_id == "" || $action == "")
            return false;
        $columns = array(
            'user_id' => $user_id,
            'media_id' => $media_id,
            'action' => $action
        );
        $results = $this->get($columns);
        return $results;
    }
}
