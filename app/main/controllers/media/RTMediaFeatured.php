<?php
/**
 * Handles users featured media related operations.
 *
 * @package rtMedia
 */

/**
 * Class to Handle users featured media related operations
 *
 * @author saurabh
 */
class RTMediaFeatured extends RTMediaUserInteraction {

	/**
	 * User id.
	 *
	 * @var bool|int
	 */
	public $user_id;

	/**
	 * Featured media
	 *
	 * @var string
	 */
	public $featured;

	/**
	 * Array of settings.
	 *
	 * @var array
	 */
	public $settings;

	/**
	 * RTMediaFeatured constructor.
	 *
	 * @param bool|int $user_id User id.
	 * @param bool     $flag Show featured media button or not.
	 */
	public function __construct( $user_id = false, $flag = true ) {
		$args = array(
			'action'     => 'featured',
			'label'      => esc_html__( 'Set as Featured', 'buddypress-media' ),
			'plural'     => '',
			'undo_label' => esc_html__( 'Remove Featured', 'buddypress-media' ),
			'privacy'    => 60,
			'countable'  => false,
			'single'     => true,
			'repeatable' => false,
			'undoable'   => true,
			'icon_class' => 'dashicons dashicons-star-filled',
		);

		$this->user_id = $user_id;
		parent::__construct( $args );
		$this->settings();

		remove_filter( 'rtmedia_action_buttons_before_delete', array( $this, 'button_filter' ) );
		if ( $flag ) {
			add_filter( 'rtmedia_addons_action_buttons', array( $this, 'button_filter' ) );
			add_filter( 'rtmedia_author_media_options', array( $this, 'button_filter' ), 12, 1 );
		}
		add_action( 'rtmedia_featured_button_filter', array( $this, 'featured_button_filter_nonce' ), 10, 1 );
	}

	/**
	 * Before rendering featured media.
	 *
	 * @return bool|void
	 */
	public function before_render() {
		$this->get();

		if ( ( ! ( isset( $this->settings[ $this->media->media_type ] ) && $this->settings[ $this->media->media_type ] ) ) || ( isset( $this->media->context ) && ( 'profile' !== $this->media->context ) ) ) {
			return false;
		}

		if ( isset( $this->action_query ) && isset( $this->action_query->id ) && intval( $this->action_query->id ) === intval( $this->featured ) ) {
			$this->label = $this->undo_label;
		}
	}

	/**
	 * Set featured media to current user.
	 *
	 * @param bool|int $media_id Media id.
	 */
	public function set( $media_id = false ) {
		if ( false === $media_id ) {
			return;
		}
		if ( false === $this->user_id ) {
			$this->user_id = get_current_user_id();
		}

		// todo user attribute.
		update_user_meta( $this->user_id, 'rtmedia_featured_media', $media_id );
	}

	/**
	 * Get featured media for user.
	 *
	 * @return mixed
	 */
	public function get() {
		if ( false === $this->user_id ) {
			if ( function_exists( 'bp_displayed_user_id' ) ) {
				$this->user_id = bp_displayed_user_id();
			}
			if ( ! $this->user_id ) {
				$this->user_id = get_current_user_id();
			}
		}
		// todo user attribute.
		$this->featured = get_user_meta( $this->user_id, 'rtmedia_featured_media', true );

		if ( empty( $this->featured ) ) {
			$this->featured = get_user_meta( $this->user_id, 'bp_media_featured_media', true );
		}

		return $this->featured;
	}

	/**
	 * Set media settings.
	 */
	public function settings() {
		global $rtmedia;

		$this->settings['photo']  = isset( $rtmedia->options['allowedTypes_photo_featured'] ) ? $rtmedia->options['allowedTypes_photo_featured'] : 0;
		$this->settings['video']  = isset( $rtmedia->options['allowedTypes_video_featured'] ) ? $rtmedia->options['allowedTypes_video_featured'] : 0;
		$this->settings['music']  = isset( $rtmedia->options['allowedTypes_music_featured'] ) ? $rtmedia->options['allowedTypes_music_featured'] : 0;
		$this->settings['width']  = isset( $rtmedia->options['defaultSizes_featured_default_width'] ) ? $rtmedia->options['defaultSizes_featured_default_width'] : 400;
		$this->settings['height'] = isset( $rtmedia->options['defaultSizes_featured_default_height'] ) ? $rtmedia->options['defaultSizes_featured_default_height'] : 300;
		$this->settings['crop']   = isset( $rtmedia->options['defaultSizes_featured_default_crop'] ) ? $rtmedia->options['defaultSizes_featured_default_crop'] : 1;
	}

	/**
	 * Check if media type is valid.
	 *
	 * @param string $type Media type.
	 *
	 * @return bool
	 */
	public function valid_type( $type ) {
		if ( isset( $this->settings[ $type ] ) && $this->settings[ $type ] > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Generate featured size for given media.
	 *
	 * @param int $media_id Media id.
	 */
	public function generate_featured_size( $media_id ) {
		$metadata = wp_get_attachment_metadata( $media_id );
		$resized  = image_make_intermediate_size( get_attached_file( $media_id ), $this->settings['width'], $this->settings['height'], $this->settings['crop'] );

		if ( $resized ) {
			$metadata['sizes']['rt_media_featured_image'] = $resized;
			wp_update_attachment_metadata( $media_id, $metadata );
		}
	}

	/**
	 * Check if provided media exists.
	 *
	 * @param int $id Media id.
	 *
	 * @return bool
	 */
	public function media_exists( $id ) {
		global $wpdb;

		$post_exists = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE id = %d", $id ), 'ARRAY_A' );

		if ( $post_exists ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get featured video/audio/image content.
	 *
	 * @return bool|mixed|void
	 */
	public function content() {
		$this->get();
		$actions = $this->model->get( array( 'id' => $this->featured ) );
		if ( ! $actions ) {
			return false;
		}

		$featured      = $actions[0];
		$type          = $featured->media_type;
		$content_extra = '';

		switch ( $type ) {
			case 'video':
				$this->generate_featured_size( $this->featured );
				if ( $featured->media_id ) {
					$image_array   = image_downsize( $featured->media_id, 'rt_media_thumbnail' );
					$content_extra = ' poster="' . esc_url( $image_array[0] ) . '" ';
				}
				$content = '<video class="bp-media-featured-media wp-video-shortcode"' . $content_extra . 'src="' . esc_url( wp_get_attachment_url( $featured->media_id ) ) . '" width="' . esc_attr( $this->settings['width'] ) . '" height="' . esc_attr( $this->settings['height'] ) . '" type="video/mp4" id="bp_media_video_' . esc_attr( $this->featured ) . '" controls="controls" preload="true"></video>';
				break;
			case 'music':
				$content = '<audio class="bp-media-featured-media wp-audio-shortcode"' . $content_extra . 'src="' . esc_url( wp_get_attachment_url( $featured->media_id ) ) . '" width="' . esc_attr( $this->settings['width'] ) . '" type="audio/mp3" id="bp_media_audio_' . esc_attr( $this->featured ) . '" controls="controls" preload="none"></audio>';
				break;
			case 'photo':
				$this->generate_featured_size( $featured->media_id );
				$image_array = image_downsize( $featured->media_id, 'rt_media_featured_image' );
				$content     = '<img src="' . esc_url( $image_array[0] ) . '" alt="' . esc_attr( $featured->media_title ) . '" />';
				break;
			default:
				return false;
		}

		return apply_filters( 'rtmedia_featured_media_content', $content );
	}

	/**
	 * Process featured media request.
	 *
	 * @return int|void
	 */
	public function process() {
		if ( ! isset( $this->action_query->id ) ) {
			return;
		}

		$nonce = sanitize_text_field( filter_input( INPUT_POST, 'featured_nonce', FILTER_SANITIZE_STRING ) );

		if ( ! wp_verify_nonce( $nonce, 'rtm_media_featured_nonce' . $this->media->id ) ) {
			$return['nonce'] = true;
			wp_send_json( $return );
		}

		$return          = array();
		$return['nonce'] = false;
		$this->model     = new RTMediaModel();
		$actions         = $this->model->get( array( 'id' => $this->action_query->id ) );
		$this->get();

		if ( 1 === intval( $this->settings[ $actions[0]->media_type ] ) ) {
			if ( $this->action_query->id === $this->featured ) {
				$this->set( 0 );
				$return['next']   = $this->label;
				$return['action'] = false;
			} else {
				$this->set( $this->action_query->id );
				$return['next']   = $this->undo_label;
				$return['action'] = true;
			}

			$return['status'] = true;
			global $rtmedia_points_media_id;
			$rtmedia_points_media_id = $this->action_query->id;
			do_action( 'rtmedia_after_set_featured', $this );

		} else {
			$return['status'] = false;
			$return['error']  = esc_html__( 'Media type is not allowed', 'buddypress-media' );
		}

		$is_json = sanitize_text_field( filter_input( INPUT_POST, 'json', FILTER_SANITIZE_STRING ) );

		if ( ! empty( $is_json ) && 'true' === $is_json ) {
			wp_send_json( $return );
		} else {
			$url = rtm_get_server_var( 'HTTP_REFERER', 'FILTER_SANITIZE_URL' );
			wp_safe_redirect( esc_url_raw( $url ) );
		}
	}

	/**
	 * Add media featured nonce to button.
	 *
	 * @param string $button Button html.
	 *
	 * @return string
	 */
	public function featured_button_filter_nonce( $button ) {
		$button .= wp_nonce_field( 'rtm_media_featured_nonce' . $this->media->id, 'rtm_media_featured_nonce', true, false );

		return $button;
	}
}

/**
 * Get featured media for user.
 *
 * @param bool|int $user_id User ID.
 */
function rtmedia_featured( $user_id = false ) {
	echo wp_kses( rtmedia_get_featured( $user_id ), RTMedia::expanded_allowed_tags() );
}

/**
 * Get featured media for user.
 *
 * @param bool|int $user_id User ID.
 *
 * @return bool|mixed|void
 */
function rtmedia_get_featured( $user_id = false ) {
	$featured = new RTMediaFeatured( $user_id, false );

	return $featured->content();
}

if ( ! function_exists( 'bp_media_featured' ) ) {

	/**
	 * Get featured media for user.
	 *
	 * @param bool|int $user_id User ID.
	 */
	function bp_media_featured( $user_id = false ) {
		echo wp_kses( rtmedia_get_featured( $user_id ), RTMedia::expanded_allowed_tags() );
	}

	/**
	 * Get featured media for user.
	 *
	 * @param bool|int $user_id User ID.
	 *
	 * @return bool|mixed|void
	 */
	function bp_media_get_featured( $user_id = false ) {
		return rtmedia_get_featured( $user_id );
	}
}
