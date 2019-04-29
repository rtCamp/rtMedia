<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaActivity
 *
 * @author saurabh
 */
class RTMediaActivity {

	var $media = array();
	var $activity_text = '';
	var $privacy;

	/**
	 * @param $media
	 * @param int $privacy
	 * @param bool $activity_text
	 */
	function __construct( $media, $privacy = 0, $activity_text = false ) {
		if ( ! isset( $media ) ) {
			return false;
		}
		if ( ! is_array( $media ) ) {
			$media = array( $media );
		}

		$this->media         = $media;
		$this->activity_text = bp_activity_filter_kses( $activity_text );
		$this->privacy       = $privacy;
	}

	/**
	 * Create html of activity/comments using text and media.
	 * 
	 * @param string $type Type of activity (activity/comment).
	 */
	function create_activity_html( $type = 'activity' ) {

		$activity_container_start = '<div class="rtmedia-'. esc_attr( $type ) .'-container">';
		$activity_container_end   = '</div>';

		$activity_text = '';
		if ( ! empty( $this->activity_text ) && '&nbsp;' !== $this->activity_text ) {
			$activity_text .= '<div class="rtmedia-' . esc_attr( $type ) . '-text"><span>';
			$activity_text .= $this->activity_text;
			$activity_text .= '</span></div>';
		}

		global $rtmedia;
		if ( isset( $rtmedia->options['buddypress_limitOnActivity'] ) ) {
			$limit_activity_feed = $rtmedia->options['buddypress_limitOnActivity'];
		} else {
			$limit_activity_feed = 0;
		}

		$rtmedia_model = new RTMediaModel();
		$media_details = $rtmedia_model->get( array( 'id' => $this->media ) );

		if ( intval( $limit_activity_feed ) > 0 ) {
			$media_details = array_slice( $media_details, 0, $limit_activity_feed, true );
		}
		$rtmedia_activity_ul_class = apply_filters( 'rtmedia_' . $type . '_ul_class', 'rtm-activity-media-list' );

		$media_content = '';
		$count         = 0;
		foreach ( $media_details as $media ) {
			$media_content .= '<li class="rtmedia-list-item media-type-' . esc_attr( $media->media_type ) . '">';
			if ( 'photo' === $media->media_type || 'document' === $media->media_type || 'other' === $media->media_type ) {
				$media_content .= '<a href ="' . esc_url( get_rtmedia_permalink( $media->id ) ) . '">';
			}
			$media_content .= '<div class="rtmedia-item-thumbnail">';

			$media_content .= $this->media( $media );

			$media_content .= '</div>';

			$media_content .= '<div class="rtmedia-item-title">';
			$media_content .= '<h4 title="' . esc_attr( $media->media_title ) . '">';
			if ( 'photo' !== $media->media_type && 'document' !== $media->media_type && 'other' !== $media->media_type ) {
				$media_content .= '<a href="' . esc_url( get_rtmedia_permalink( $media->id ) ) . '">';
			}

			$media_content .= $media->media_title;
			if ( 'photo' !== $media->media_type && 'document' !== $media->media_type && 'other' !== $media->media_type ) {
				$media_content .= '</a>';
			}
			$media_content .= '</h4>';
			$media_content .= '</div>';
			if ( 'photo' === $media->media_type || 'document' === $media->media_type || 'other' === $media->media_type ) {
				$media_content .= '</a>';
			}

			$media_content .= '</li>';
			$count ++;
		}
		$media_container_start = '';
		if ( 'activity' === $type ) {
			$media_container_start .= '<ul class="rtmedia-list ' . esc_attr( $rtmedia_activity_ul_class ) . ' rtmedia-activity-media-length-' . esc_attr( $count ) . '">';
		} else {
			$media_container_start .= '<ul class="rtmedia-' . esc_attr( $type ) . '-list ' . esc_attr( $rtmedia_activity_ul_class ) . ' rtmedia-activity-media-length-' . esc_attr( $count ) . '">';
		}
		$media_container_end = '</ul>';

		$media_list  = $media_container_start;
		$media_list .= $media_content;
		$media_list .= $media_container_end;

		/**
		 * Filters the output of the activity contents before save.
		 *
		 * @param string $activity_content Concatination of $activity_text and $media_list.
		 * @param string $activity_text    HTML markup of activity text.
		 * @param string $media_list       HTML markup of media in ul.
		 */
		$activity_content = apply_filters( 'rtmedia_activity_content', $activity_text . $media_list, $activity_text, $media_list );

		$activity  = $activity_container_start;
		$activity .= $activity_content;
		$activity .= $activity_container_end;

		return bp_activity_filter_kses( $activity );
	}

	/**
	 * @fixme me Why this function is required ?
	 */
	function actions() {

	}

	function media( $media, $type = 'activity' ) {
		$html = false;

		if ( isset( $media->media_type ) ) {
			global $rtmedia;
			if ( 'photo' === $media->media_type ) {
				$thumbnail_id = $media->media_id;
				if ( $thumbnail_id ) {
					list( $src, $width, $height ) = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'rtmedia_activity_image_size', 'rt_media_activity_image' ) );
					$html = '<img alt="' . esc_attr( $media->media_title ) . '" src="' . set_url_scheme( $src ) . '" />';
				}
			} elseif ( 'video' === $media->media_type ) {
				$cover_art = rtmedia_get_cover_art_src( $media->id );
				$video_class = 'wp-video-shortcode';
				$youtube_url = get_rtmedia_meta( $media->id, 'video_url_uploaded_from' );
				if ( $cover_art ) {
					$poster = 'poster = "' . esc_url( $cover_art ) . '"';
				} else {
					$poster = '';
				}
				if ( empty( $youtube_url ) ) {
					$html = '<video %s src="%s" width="%d" height="%d" type="video/mp4" class="%s" id="rt_media_video_%s" controls="controls" preload="none"></video>';
					$html = sprintf( $html, $poster, esc_url( wp_get_attachment_url( $media->media_id ) ), esc_attr( $rtmedia->options['defaultSizes_video_activityPlayer_width'] ), esc_attr( $rtmedia->options['defaultSizes_video_activityPlayer_height'] ), $video_class, esc_attr( $media->id ) );
				}
			} elseif ( 'music' === $media->media_type ) {
				//$html = '<audio src="' . esc_url( wp_get_attachment_url( $media->media_id ) ) . '" width="' . esc_attr( $rtmedia->options['defaultSizes_music_activityPlayer_width'] ) . '" height="0" type="audio/mp3" class="wp-audio-shortcode" id="rt_media_audio_' . esc_attr( $media->id ) . '" controls="controls" preload="none"></audio>';
				$html = '<audio src="%s" width="%d" height="0" type="audio/mp3" class="wp-audio-shortcode" id="rt_media_audio_%s" controls="controls" preload="none"></audio>';
				$html = sprintf( $html, esc_url( wp_get_attachment_url( $media->media_id ) ), esc_attr( $rtmedia->options['defaultSizes_music_activityPlayer_width'] ), esc_attr( $media->id ) );
			}
		}

		return apply_filters( 'rtmedia_single_activity_filter', $html, $media, true );
	}
}
