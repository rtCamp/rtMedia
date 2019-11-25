<?php

/**
 * Description of RTMediaGroupFeatured
 *
 * @author ritz <ritesh.patel@rtcamp.com>
 */
class RTMediaGroupFeatured extends RTMediaUserInteraction {

	public $group_id;
	public $featured;
	public $settings;

	function __construct( $group_id = false, $flag = true ) {
		$args = array(
			'action'     => 'group-featured',
			'label'      => esc_html__( 'Set as Featured', 'buddypress-media' ),
			'plural'     => '',
			'undo_label' => esc_html__( 'Remove Featured', 'buddypress-media' ),
			'privacy'    => 20,
			'countable'  => false,
			'single'     => true,
			'repeatable' => false,
			'undoable'   => true,
			'icon_class' => 'dashicons dashicons-star-filled',
		);

		$this->group_id = $group_id;
		parent::__construct( $args );
		$this->settings();
		remove_filter( 'rtmedia_action_buttons_before_delete', array( $this, 'button_filter' ) );
		if ( $flag ) {
			add_filter( 'rtmedia_addons_action_buttons', array( $this, 'button_filter' ) );
			add_filter( 'rtmedia_author_media_options', array( $this, 'button_filter' ), 12, 1 );
		}
	}

	function before_render() {

		if ( ! class_exists( 'BuddyPress' ) || ! bp_is_active( 'groups' ) ) {
			return false;
		}

		$this->get();

		// if group id is not set, don't render "Set featured"
		if ( empty( $this->group_id ) ) {
			return false;
		}

		$user_id = get_current_user_id();

		// if current is not group moderator or group admin, don't render "Set featured"
		if ( ! groups_is_user_mod( $user_id, $this->group_id ) && ! groups_is_user_admin( $user_id, $this->group_id ) && ! is_rt_admin() ) {
			return false;
		}

		// if current media is not any group media, don't render "Set featured"
		if ( ( ! ( isset( $this->settings[ $this->media->media_type ] ) && $this->settings[ $this->media->media_type ] ) ) || ( isset( $this->media->context ) && ( 'group' !== $this->media->context ) ) ) {
			return false;
		}

		if ( isset( $this->action_query ) && isset( $this->action_query->id ) && intval( $this->action_query->id ) === intval( $this->featured ) ) {
			$this->label = $this->undo_label;
		}
	}

	function set( $media_id = false ) {
		if ( false === $media_id ) {
			return;
		}
		if ( false === $this->group_id ) {
			return;
		}
		groups_update_groupmeta( $this->group_id, 'rtmedia_group_featured_media', $media_id );
	}

	function get() {
		if ( false === $this->group_id ) {
			global $groups_template;
			if ( ! empty( $groups_template->group ) ) {
				$group_id = bp_get_group_id();
				if ( ! empty( $group_id ) ) {
					$this->group_id = $group_id;
				}
			} else if ( isset( $this->media ) && isset( $this->media->context_id ) ) {
				$this->group_id = $this->media->context_id;
			} else {
				return false;
			}
		}
		$this->featured = groups_get_groupmeta( $this->group_id, 'rtmedia_group_featured_media', true );

		return $this->featured;
	}

	function settings() {
		global $rtmedia;
		$this->settings['photo']  = isset( $rtmedia->options['allowedTypes_photo_featured'] ) ? $rtmedia->options['allowedTypes_photo_featured'] : 0;
		$this->settings['video']  = isset( $rtmedia->options['allowedTypes_video_featured'] ) ? $rtmedia->options['allowedTypes_video_featured'] : 0;
		$this->settings['music']  = isset( $rtmedia->options['allowedTypes_music_featured'] ) ? $rtmedia->options['allowedTypes_music_featured'] : 0;
		$this->settings['width']  = isset( $rtmedia->options['defaultSizes_featured_default_width'] ) ? $rtmedia->options['defaultSizes_featured_default_width'] : 400;
		$this->settings['height'] = isset( $rtmedia->options['defaultSizes_featured_default_height'] ) ? $rtmedia->options['defaultSizes_featured_default_height'] : 300;
		$this->settings['crop']   = isset( $rtmedia->options['defaultSizes_featured_default_crop'] ) ? $rtmedia->options['defaultSizes_featured_default_crop'] : 1;
	}

	function valid_type( $type ) {
		if ( isset( $this->settings[ $type ] ) && $this->settings[ $type ] > 0 ) {
			return true;
		}

		return false;
	}

	function get_last_media() {

	}

	function generate_featured_size( $media_id ) {
		$metadata = wp_get_attachment_metadata( $media_id );
		$resized  = image_make_intermediate_size( get_attached_file( $media_id ), $this->settings['width'], $this->settings['height'], $this->settings['crop'] );
		if ( $resized ) {
			$metadata['sizes']['rt_media_featured_image'] = $resized;
			wp_update_attachment_metadata( $media_id, $metadata );
		}
	}

	function media_exists( $id ) {
		global $wpdb;
		$post_exists = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE id = %d", $id ), 'ARRAY_A' );
		if ( $post_exists ) {
			return true;
		} else {
			return false;
		}
	}

	function content() {
		$this->get();
		$actions = $this->model->get( array( 'id' => $this->featured ) );
		if ( ! $actions ) {
			return false;
		}

		$featured = $actions[0];
		$type     = $featured->media_type;

		$content_xtra = '';
		switch ( $type ) {
			case 'video' :
				$this->generate_featured_size( $this->featured );
				if ( $featured->media_id ) {
					$image_array  = image_downsize( $featured->media_id, 'rt_media_thumbnail' );
					$content_xtra = 'poster="' . esc_url( $image_array[0] ) . '" ';
				}
				$content = '<video class="bp-media-featured-media wp-video-shortcode"' . esc_attr( $content_xtra ) . 'src="' . esc_url( wp_get_attachment_url( $featured->media_id ) ) . '" width="' . esc_attr( $this->settings['width'] ) . '" height="' . esc_attr( $this->settings['height'] ) . '" type="video/mp4" id="bp_media_video_' . esc_attr( $this->featured ) . '" controls="controls" preload="true"></video>';
				break;
			case 'music' :
				$content = '<audio class="bp-media-featured-media wp-audio-shortcode" src="' . esc_url( wp_get_attachment_url( $featured->media_id ) ) . '" width="' . esc_attr( $this->settings['width'] ) . '" type="audio/mp3" id="bp_media_audio_' . esc_attr( $this->featured ) . '" controls="controls" preload="none"></audio>';
				break;
			case 'photo' :
				$this->generate_featured_size( $featured->media_id );
				$image_array = image_downsize( $featured->media_id, 'rt_media_featured_image' );
				$content     = '<img src="' . esc_url( $image_array[0] ) . '" alt="' . esc_attr( $featured->media_title ) . '" />';
				break;
			default :
				return false;
		}

		return apply_filters( 'rtmedia_featured_media_content', $content );
	}

	function process() {
		if ( ! isset( $this->action_query->id ) ) {
			return;
		}
		$return      = array();
		$this->model = new RTMediaModel();
		$actions     = $this->model->get( array( 'id' => $this->action_query->id ) );
		$this->get();
		if ( 1 === intval( $this->settings[ $actions[0]->media_type ] ) ) {
			if ( $this->action_query->id === $this->featured ) {
				$this->set( 0 );
				$return['next'] = $this->label;
			} else {
				$this->set( $this->action_query->id );
				$return['next'] = $this->undo_label;
			}
			$return['status'] = true;
			global $rtmedia_points_media_id;
			$rtmedia_points_media_id = $this->action_query->id;
			do_action( 'rtmedia_after_set_featured', $this );
		} else {
			$return['status'] = false;
			$return['error']  = esc_html__( 'Media type is not allowed', 'buddypress-media' );
		}
		$is_json = filter_input( INPUT_POST, 'json', FILTER_SANITIZE_STRING );
		if ( isset( $is_json ) && 'true' === $is_json ) {
			wp_send_json( $return );
		} else {
			$url = rtm_get_server_var( 'HTTP_REFERER', 'FILTER_SANITIZE_URL' );
			wp_safe_redirect( esc_url_raw( $url ) );
		}
	}
}

function rtmedia_group_featured( $group_id = false ) {
	echo rtmedia_get_group_featured( $group_id ); // @codingStandardsIgnoreLine
}

function rtmedia_get_group_featured( $group_id = false ) {
	$featured = new RTMediaGroupFeatured( $group_id, false );

	return $featured->content();
}
