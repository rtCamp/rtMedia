<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaLike
 *
 * @author saurabh
 */
class RTMediaLike extends RTMediaUserInteraction {
    function __construct() {
	    $args = array(
	    'action' => 'like',
	    'label' => __( 'Like', 'rtmedia' ),
	    'plural' => __( 'Likes', 'rtmedia' ),
	    'undo_label' => __( 'Unlike', 'rtmedia' ),
	    'privacy' => 20,
	    'countable' => true,
	    'single' => false,
	    'repeatable' => false,
	    'undoable' => true,
            'icon_class' => 'rtmicon-thumbs-up'
	);
	parent::__construct($args);
        remove_filter( 'rtmedia_action_buttons_before_delete', array($this,'button_filter') );
        add_action('rtmedia_action_buttons_after_media',array($this, 'button_filter'),12);
        add_action('rtmedia_actions_before_comments',array($this, 'like_button_filter'),10);

    }

    function like_button_filter() {
        if(empty($this->media)){
                $this->init();
        }
        $button = $this->render();

        if($button)
            echo "<span>" . $button . "</span>";
    }

    function process() {
	$actions = $this->model->get( array( 'id' => $this->action_query->id ) );


	$rtmediainteraction = new RTMediaInteractionModel();
	$user_id = $this->interactor;
	$media_id = $this->action_query->id;
	$action = $this->action;
	$check_action = $rtmediainteraction->check($user_id, $media_id, $action);
	if($check_action) {
	    $results = $rtmediainteraction->get_row($user_id, $media_id, $action);
            $row = $results[0];
            $curr_value = $row->value;
	    if($curr_value == "1") {
		$value = "0";
		$this->increase =false;
	    } else {
		$value = "1";
		$this->increase =true;
	    }
	    $update_data = array('value' => $value);
	    $where_columns = array(
                'user_id' =>  $user_id,
                'media_id' => $media_id,
                'action' => $action,
            );
            $update = $rtmediainteraction->update($update_data, $where_columns);
	} else {
	    $value = "1";
	    $columns = array(
                'user_id' =>  $user_id,
                'media_id' => $media_id,
                'action' => $action,
                'value' => $value
            );
	    $insert_id = $rtmediainteraction->insert($columns);
	    $this->increase =true;
	}

	$actionwa = $this->action.'s';

	$return = array();

	$actions = intval($actions[ 0 ]->{$actionwa});
	if ( $this->increase === true ) {
		$actions ++;
		$return["next"] = $this->undo_label;
	} else {
		$actions --;
		$return["next"] = $this->label;
	}
	if($actions <0)
	    $actions = 0;

	$return["count"] = $actions;
	$this->model->update( array( $this->plural => $actions ), array( 'id' => $this->action_query->id ) );
	global $rtmedia_points_media_id;
	$rtmedia_points_media_id = $this->action_query->id;
	do_action("rtmedia_after_like_media", $this);
	if(isset($_REQUEST["json"]) && $_REQUEST["json"]=="true"){
	    echo json_encode($return);
	    die();
	}
	else{
	    wp_safe_redirect ($_SERVER["HTTP_REFERER"]);
	    die();
	}
	return $actions;
    }

    function button_filter( $buttons ){

        if(empty($this->media)){
                $this->init();
        }
        $button = $this->render();

        if($button)
           // echo "<li>" . $button . "</li>";
            echo  $button ;
    }

    function is_like_migrated( ) {
	$rtmediainteraction = new RTMediaInteractionModel();
	$user_id = $this->interactor;
	$media_id = $this->action_query->id;
	$action = $this->action;
	return $rtmediainteraction->check($user_id, $media_id, $action);
    }

    function get_like_value( ) {
	$rtmediainteraction = new RTMediaInteractionModel();
	$user_id = $this->interactor;
	$media_id = $this->action_query->id;
	$action = $this->action;
	$results = $rtmediainteraction->get_row($user_id, $media_id, $action);
	$row = $results[0];
	if( $row->value == "1" ) {
	    $this->increase = false;
	    return true;
	} else {
	    $this->increase = true;
	    return false;
	}
    }

    function migrate_likes( $like_media ) {
	$rtmediainteraction = new RTMediaInteractionModel();
	$user_id = $this->interactor;
	$media_id = $this->action_query->id;
	$action = $this->action;
	$value = "1";
	$columns = array(
                'user_id' =>  $user_id,
                'media_id' => $media_id,
                'action' => $action,
                'value' => $value
            );
	$insert_id = $rtmediainteraction->insert($columns);
	$like_media = trim(str_replace("," . $this->action_query->id . ",", ",",",". $like_media .","), ",");
	update_user_meta($this->interactor,'rtmedia_liked_media',$like_media);
	return $insert_id;
    }

    function is_liked() {
	$like_media = get_user_meta($this->interactor, "rtmedia_liked_media", true);
	if ( $this->is_like_migrated( ) ) {
	    return $this->get_like_value( );
	}
	else {
	    if (strpos("," . $like_media . ",", "," . $this->action_query->id . ",") === false) {
		$this->increase = true;
		return false;
	    } else {
		$this->migrate_likes( $like_media );
		$this->increase = false;
		return true;
	    }
	}
    }

    function before_render(){
	$enable_like = true;
	$enable_like = apply_filters('rtmedia_check_enable_disable_like',$enable_like);
	if(!$enable_like)
	    return false;
	if($this->is_liked()){
	    $this->label =  $this->undo_label;
	}
//	$actions = $this->model->get( array( 'id' => $this->action_query->id ) );
//	if(isset($actions[ 0 ]->likes)){
//	    $actions = intval($actions[ 0 ]->likes);
//	}else{
//	    $actions = 0;
//	}
	//$this->label =  "<span class='like-count'>" .$actions ."</span>" . $this->label;

    }
}
