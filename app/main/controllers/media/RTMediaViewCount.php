<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaViewCount
 *
 * @author ritz
 */
class RTMediaViewCount extends RTMediaUserInteraction {
    function __construct () {
	$args = array(
	    'action' => 'view',
	    'label' => 'view',
	    'privacy' => 0
	    );
	parent::__construct ($args);
    }

    function render () {
	    $link = trailingslashit(get_rtmedia_permalink($this->media->id)).$this->action.'/';
	    echo '<form action="'. $link .'" id="rtmedia-media-view-form"></form>';
    }

    function process() {
	$user_id = $this->interactor;
	if(!$user_id)
	    $user_id = -1;
        $media_id = $this->action_query->id;
	$action = $this->action_query->action;
	$model_meta = new RTMediaMeta();
	$curr_count = $model_meta->get_meta($media_id, $action);
	if(!$curr_count)
	    $curr_count=1;
	else
	    $curr_count++;
	$model_meta->update_meta($media_id, $action, $curr_count, false);

	$rtmediainteraction = new RTMediaInteractionModel();
	$check_action = $rtmediainteraction->check($user_id, $media_id, $action);
	if($check_action) {
	    $results = $rtmediainteraction->get_row($user_id, $media_id, $action);
	    $row = $results[0];
            $curr_value = $row->value;
            $update_data = array('value' => ++$curr_value);
            $where_columns = array(
                'user_id' =>  $user_id,
                'media_id' => $media_id,
                'action' => $action
            );
            $update = $rtmediainteraction->update($update_data, $where_columns);
	}
	else {
	    $columns = array(
                'user_id' =>  $user_id,
                'media_id' => $media_id,
                'action' => $action,
                'value' => "1"
            );
            $insert_id = $rtmediainteraction->insert($columns);

	}
	die();
    }
}
?>
