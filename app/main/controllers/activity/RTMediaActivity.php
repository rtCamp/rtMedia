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
	 * Function is used to generate HTML of activity/comments.
	 * It combines the rtMedia text and media in the activity content.
	 *
	 * Note: Use the `rtmedia_activity_content_html` filter to modify the output of the activity content.
	 *
	 * @param string $type Type of activity (activity/comment).
	 *
	 * @return string
	 */
	public function create_activity_html( $type = 'activity' ) {
		$activity_container_start = sprintf( '<div class="rtmedia-%s-container">', esc_attr( $type ) );
		$activity_container_end   = '</div>';

		$activity_text = '';

		// Activity text content markup.
		if ( ! empty( $this->activity_text ) && '&nbsp;' !== $this->activity_text ) {
			$activity_text .= sprintf(
				'<div class="rtmedia-%s-text">
					<span>%s</span>
				</div>',
				esc_attr( $type ),
				$this->activity_text
			);
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

		$uploaded_media_types           = [];
		$rtmedia_activity_ul_list_class = 'rtm-activity-mixed-list';

		// Loop through each media and check media type.
		$uploaded_media_types = array_map( function ( $current_media ) {
			return is_object( $current_media ) ? $current_media->media_type : '';
		}, $media_details );

		// Remove empty values from media type list.
		$media_type_list = array_filter( $uploaded_media_types, 'strlen' );

		// Update activity class based on media type.
		if ( ! empty( $media_type_list ) ) {
			if ( count( array_unique( $uploaded_media_types ) ) === 1 ) {
				$current_media_type             = end( $uploaded_media_types );
				$rtmedia_activity_ul_list_class = "rtm-activity-{$current_media_type}-list";
			}
		}

		$media_content = '';
		$count         = 0;
		foreach ( $media_details as $media ) {
			$media_content .= sprintf( '<li class="rtmedia-list-item media-type-%s">', esc_attr( $media->media_type ) );

			if ( 'photo' === $media->media_type ) {
				// Markup for photo media type with anchor tag only on image.
				$media_content .= sprintf(
					'<a href ="%s">
						<div class="rtmedia-item-thumbnail">
							%s
						</div>
						<div class="rtmedia-item-title">
							<h4 title="%s">
								%s
							</h4>
						</div>
					</a>',
					esc_url( get_rtmedia_permalink( $media->id ) ),
					$this->media( $media ),
					esc_attr( $media->media_title ),
					$media->media_title
				);
			} elseif ( 'music' === $media->media_type || 'video' === $media->media_type ) {
				// Markup for audio and video media type with link only on media (title).
				$media_content .= sprintf(
					'<div class="rtmedia-item-thumbnail">
						%s
					</div>
					<div class="rtmedia-item-title">
						<h4 title="%s">
							<a href="%s">
								%s
							</a>
						</h4>
					</div>',
					$this->media( $media ),
					esc_attr( $media->media_title ),
					esc_url( get_rtmedia_permalink( $media->id ) ),
					esc_html( $media->media_title )
				);
			} else {
				// Markup for all the other media linke docs and other files where anchor tag the markup is comming from add-on itself.
				$media_content .= sprintf(
					'<div class="rtmedia-item-thumbnail">
							%s
					</div>
					<div class="rtmedia-item-title">
							<h4 title="%s">
								%s
							</h4>
					</div>',
					$this->media( $media ),
					esc_attr( $media->media_title ),
					esc_html( $media->media_title )
				);
			}

			$media_content .= '</li>';
			$count ++;
		}

		$media_container_start_class = 'rtmedia-list';
		if ( 'activity' !== $type ) {
			$media_container_start_class = sprintf( 'rtmedia-%s-list', $type );
		}

		$media_container_start = sprintf(
			'<ul class="%s %s rtmedia-activity-media-length-%s %s">',
			esc_attr( $media_container_start_class ),
			esc_attr( $rtmedia_activity_ul_class ),
			esc_attr( $count ),
			esc_attr( $rtmedia_activity_ul_list_class )
		);

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
		$activity_content = apply_filters( 'rtmedia_activity_content_html', $activity_text . $media_list, $activity_text, $media_list );

		$activity  = $activity_container_start;
		$activity .= $activity_content;
		$activity .= $activity_container_end;

		$current_max_links = absint( get_option( 'comment_max_links' ) ); // get current number of allowed links.

		// Bypass comment links limit.
		add_filter(
			'option_comment_max_links',
			function ( $values ) use ( $current_max_links ) {
				$rtmedia_attached_files = filter_input( INPUT_POST, 'rtMedia_attached_files', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
				// Check  if files available.
				if ( is_array( $rtmedia_attached_files ) && ! empty( $rtmedia_attached_files[0] ) ) {
					// One url of image and other for anchor tag.
					$values = ( count( $rtmedia_attached_files ) * 3 ) + $current_max_links;
				}
				return $values;
			}
		);

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
