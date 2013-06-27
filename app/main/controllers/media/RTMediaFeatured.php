<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaFeatured
 *
 * @author saurabh
 */
class RTMediaFeatured extends RTMediaUserInteraction{

	/**
	 *
	 */
	function __construct() {
		$label = __('Set as Featured', 'rt-media');
		parent::__construct('featured',false, $label);
	}

	function init( $user_id = false ) {
		if ( ! $user_id ) {
			if(!class_exists('BuddyPress')){
				$user = bp_displayed_user_id();
			}elseif(is_author()){
				$curauth = (get_query_var('author_name')) ?
				get_user_by('slug', get_query_var('author_name')) :
					get_userdata(get_query_var('author'));
				$user = $curauth->ID;
			} else {
				return false;
			}
		} else {
			$user = $user_id;
		}
		$this->user = $user;
		$this->get();
		$this->settings();
	}

	function set( $media_id = false ) {
		if ( ! $media_id ){
			return;
		}

		$user = get_current_user_id();

		update_user_meta( $user, 'rtmedia_featured_media', $media_id );
		$this->get();
	}

	function get() {
		$legacy_featured = bp_get_user_meta( $this->user, 'bp_media_featured_media', true );
		if(!$legacy_featured || $legacy_featured=''){
			$this->set($legacy_featured);
			bp_delete_user_meta($this->user, 'bp_media_featured_media');
		}
		$this->featured = get_user_meta($this->user, 'rtmedia_featured_media', true );
	}

	function settings() {
		global $rt_media;
		$size_settings = $rt_media->options[ 'sizes' ][ 'media' ][ 'featured' ];
		$this->settings[ 'image' ] = isset( $rt_media->options[ 'featured_image' ] ) ? 1 : 0;
		$this->settings[ 'video' ] = isset( $rt_media->options[ 'featured_video' ] ) ? 1 : 0;
		$this->settings[ 'audio' ] = isset( $rt_media->options[ 'featured_audio' ] ) ? 1 : 0;
		$this->settings[ 'width' ] = isset( $size_settings[ 'width' ] ) ? $size_settings[ 'width' ] : 400;
		$this->settings[ 'height' ] = isset( $size_settings[ 'height' ] ) ? $size_settings[ 'height' ] : 300;
		$this->settings[ 'crop' ] = isset( $size_settings[ 'crop' ] ) ? $size_settings[ 'crop' ] : 1;
	}

	function valid_type( $type ) {
		if ( isset($this->settings[ $type ])&&$this->settings[ $type ] > 0 ) {
			return true;
		}
		return false;
	}

	function generate_featured_size($media_id) {
		$metadata = wp_get_attachment_metadata( $media_id );
		$resized = image_make_intermediate_size( get_attached_file( $media_id ), $this->settings[ 'width' ], $this->settings[ 'height' ], $this->settings[ 'crop' ] );
		if ( $resized ) {
			$metadata[ 'sizes' ][ 'rt-media-featured' ] = $resized;
			wp_update_attachment_metadata( $media_id, $metadata );
		}
	}

	function media_exists($id){
		global $wpdb;
		$post_exists = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE id = '" . $id . "'", 'ARRAY_A');
		if ($post_exists)
			return true;
		else
			return false;
	}

	function content() {
		if(!$this->media_exists($this->featured)){
			return false;
		}
		$featured = new BPMediaHostWordpress( $this->featured );
		$type = $featured->get_type();
		if ( ! $this->valid_type( $type ) ) {
			return false;
		}
		$content_xtra = '';
		switch ( $type ) {
			case 'video' :

				if ( $featured->get_thumbnail_id() ) {
					$image_array = image_downsize( $featured->get_thumbnail_id(), 'bp_media_activity_image' );
					$content_xtra = 'poster="' . $image_array[ 0 ] . '" ';
				}
				$content = '<video class="bp-media-featured-media"' . $content_xtra . 'src="' . wp_get_attachment_url( $this->featured ) . '" width="' . $this->settings[ 'width' ] . '" height="' . $this->settings[ 'height' ] . '" type="video/mp4" id="bp_media_video_'.$this->featured.'" controls="controls" preload="none"></video>';
				break;
			case 'audio' :
				$content = '<audio class="bp-media-featured-media"' . $content_xtra . 'src="' . wp_get_attachment_url( $this->featured ) . '" width="' . $this->settings[ 'width' ] . '" type="audio/mp3" id="bp_media_audio_'.$this->featured.'" controls="controls" preload="none"></video>';
				break;
			case 'image' :
				$image_array = image_downsize( $this->featured, 'bp-media-featured' );
				$content = '<img src="' . $image_array[ 0 ] . '" alt="' . $featured->get_title() . '" />';
				break;
			default :
				return false;
		}
		return $content;
	}


	function process(){
		if(!isset($this->action_query->id)) return;

		$this->model = new RTMediaModel();
		$actions = $this->model->get(array('id'=>$this->action_query->id));
		$actions = $actions[0]->$this->actions;
		if($this->increase===true){
			$actions++;
		}else{
			$actions--;
		}

		$this->model->update(array($this->actions=>$actions),array('id'=>$this->action_query->id));
		die($actions);
	}

}

function rt_media_featured( $user_id = false ) {
	echo bp_media_get_featured( $user_id );
}

function rt_media_get_featured( $user_id = false ) {
	$featured = new RTMediaFeatured( $user_id , false);
	return $featured->content();
}

function bp_media_featured( $user_id = false ) {
	echo rt_media_get_featured( $user_id );
}

function bp_media_get_featured( $user_id = false ) {
	return rt_media_get_featured( $user_id );
}


?>
