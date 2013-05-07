<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaFeatured
 *
 * @author saurabh
 */
class BPMediaFeatured {

	/**
	 *
	 */
			public $featured,
			$user,
			$settings = array(
				'image' => 0,
				'video' => 0,
				'audio' => 0,
				'sizes' => array(
					'width' => 100,
					'height' => 100,
					'crop' => 1
				)
	);

	function __construct( $user_id = false , $filter=true) {
		$this->init( $user_id );
		if($filter!=false){
			$this->filter();
		}

	}

	function filter(){
		add_filter( 'bp_media_action_buttons', array( $this, 'add_button' ) );
		add_filter( 'wp_ajax_bp_set_featured', array( $this, 'set_featured' ) );
	}

	function init( $user_id = false ) {
		if ( ! $user_id ) {
			$user = bp_displayed_user_id();
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

		$user = bp_loggedin_user_id();

		bp_update_user_meta( $user, 'bp_media_featured_media', $media_id );
		$this->get();
	}

	function get() {
		$this->featured = bp_get_user_meta( $this->user, 'bp_media_featured_media', true );
	}

	function settings() {
		global $bp_media;
		$size_settings = $bp_media->options[ 'sizes' ][ 'media' ][ 'featured' ];
		$this->settings[ 'image' ] = isset( $bp_media->options[ 'featured_image' ] ) ? 1 : 0;
		$this->settings[ 'video' ] = isset( $bp_media->options[ 'featured_video' ] ) ? 1 : 0;
		$this->settings[ 'audio' ] = isset( $bp_media->options[ 'featured_audio' ] ) ? 1 : 0;
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

	function add_button( $action_buttons ) {
		global $bp_media_current_entry;
		if($this->valid_type($bp_media_current_entry->get_type())&& ( $bp_media_current_entry->get_id() != bp_get_user_meta( bp_displayed_user_id(), 'bp_media_featured_media', true )) ) {
			if ( $bp_media_current_entry != NULL ) {
				if ( bp_displayed_user_id() == bp_loggedin_user_id() ) {
					$action_buttons[ ] = '<a href="#" class="button item-button bp-secondary-action bp-media-featured-media-button" title="'
							. __( 'Set as Featured', 'buddypress-media' ) . '">' . __( 'Featured', 'buddypress-media' ) . '</a>';
				}
			}
		}

		return $action_buttons;
	}

	function set_featured() {
		$media_id = $_GET[ 'media_id' ];
		$this->set( $media_id );
		$featured = new BPMediaHostWordpress( $media_id );
		$type = $featured->get_type();
		if($type=='image'){
			$metadata = wp_get_attachment_metadata( $media_id );
			if ( ! isset( $metadata[ 'sizes' ][ 'bp-media-featured' ] ) ) {
				$this->generate_featured_size($media_id);
			}
		}
		die( $media_id );
	}

	function generate_featured_size($media_id) {
		$metadata = wp_get_attachment_metadata( $media_id );
		$resized = image_make_intermediate_size( get_attached_file( $media_id ), $this->settings[ 'width' ], $this->settings[ 'height' ], $this->settings[ 'crop' ] );
		if ( $resized ) {
			$metadata[ 'sizes' ][ 'bp-media-featured' ] = $resized;
			wp_update_attachment_metadata( $media_id, $metadata );
		}
	}

	function content() {
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

}

function bp_media_featured( $user_id = false ) {
	echo bp_media_get_featured( $user_id );
}

function bp_media_get_featured( $user_id = false ) {
	$featured = new BPMediaFeatured( $user_id , false);
	return $featured->content();
}

?>
