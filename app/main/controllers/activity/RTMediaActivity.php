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

    var $media = array( );
    var $activity_text = '';
    var $privacy;

    /**
     *
     */
    function __construct ( $media, $privacy = 0, $activity_text = false ) {
        if ( ! isset ( $media ) )
            return false;
        if ( ! is_array ( $media ) )
            $media = array( $media );

        $this->media = $media;
        $this->activity_text = $activity_text;
        $this->privacy = $privacy;
    }

    function create_activity_html () {


        $html = '';

        $html .='<div class="rtmedia-activity-container">';

        if ( ! empty ( $this->activity_text ) ) {
            $html .= '<div class="rtmedia-activity-text">';
            $html .= $this->activity_text;
            $html .= '</div>';
        }

        global $rtmedia;
	if( isset( $rtmedia->options['buddypress_limitOnActivity'] ) ) {
	    $limitActivityFeed = $rtmedia->options['buddypress_limitOnActivity'];
	} else {
	    $limitActivityFeed = 0;
	}

        $mediaObj = new RTMediaModel();
        $media_details = $mediaObj->get ( array( 'id' => $this->media ) );

        if( intval( $limitActivityFeed ) > 0 )
            $media_details = array_slice( $media_details, 0, $limitActivityFeed, true);
	$rtmedia_activity_ul_class = apply_filters("rtmedia_activity_ul_class","large-block-grid-3");
        $html .= '<ul class="rtmedia-list '.$rtmedia_activity_ul_class.'">';
        foreach ( $media_details as $media ) {
            $html .= '<li class="rtmedia-list-item media-type-' . $media->media_type . '">';
            if ( $media->media_type == 'photo' )
                $html .= '<a href ="' . get_rtmedia_permalink ( $media->id ) . '">';
            $html .= '<div class="rtmedia-item-thumbnail">';

            $html .= $this->media ( $media );

            $html .= '</div>';

            $html .= '<div class="rtmedia-item-title">';
            $html .= '<h4 title="' . $media->media_title . '">';
            if ( $media->media_type != 'photo' )
                $html .= '<a href="' . get_rtmedia_permalink ( $media->id ) . '">';

            $html .= $media->media_title;
            if ( $media->media_type != 'photo' )
                $html .= '</a>';
            $html .= '</h4>';
            $html .= '</div>';
            if ( $media->media_type == 'photo' )
                $html .= '</a>';

            $html .= '<div class="rtmedia-item-actions">';
            $html .= $this->actions ();
            $html .= '</div>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        return $html;
    }

    function actions () {

    }

    function media ( $media ) {
        if ( isset ( $media->media_type ) ) {
//			if ($media->media_type == 'album' ||
//					$media->media_type != 'photo') {
//				$thumbnail_id = get_rtmedia_meta($media->media_id,'cover_art');
//                                if ( $thumbnail_id ) {
//                                    list($src, $width, $height) = wp_get_attachment_image_src($thumbnail_id);
//                                    return '<img src="'.$src.'" />';
//                                }
//			}
	     global $rtmedia;
            if ( $media->media_type == 'photo' ) {
                $thumbnail_id = $media->media_id;
                if ( $thumbnail_id ) {
                    list($src, $width, $height) = wp_get_attachment_image_src ( $thumbnail_id, "rt_media_activity_image" );
                    $html = '<img src="' . $src . '" />';
                }
            } elseif ( $media->media_type == 'video' ) {
                $cover_art = rtmedia_get_cover_art_src($media->id);
                if($cover_art) {
                    $poster = 'poster = "'. $cover_art .'"';
                }
                else {
                    $poster = "";
                }
                $html = '<video '.$poster.' src="' . wp_get_attachment_url ( $media->media_id ) . '" width="' . $rtmedia->options[ "defaultSizes_video_activityPlayer_width" ] . '" height="' . $rtmedia->options[ "defaultSizes_video_activityPlayer_height" ] . '" type="video/mp4" class="wp-video-shortcode" id="rt_media_video_' . $media->id . '" controls="controls" preload="none"></video>';
            } elseif ( $media->media_type == 'music' ) {
                $html = '<audio src="' . wp_get_attachment_url ( $media->media_id ) . '" width="' . $rtmedia->options[ "defaultSizes_music_activityPlayer_width" ] . '" height="0" type="audio/mp3" class="wp-audio-shortcode" id="rt_media_audio_' . $media->id . '" controls="controls" preload="none"></audio>';
            } else {
                $html = false;
            }
        } else {
            $html = false;
        }
        return apply_filters ( 'rtmedia_single_activity_filter', $html, $media, true );
    }

}