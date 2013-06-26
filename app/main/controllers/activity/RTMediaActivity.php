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
	 *
	 */
	function __construct($media, $privacy=0, $activity_text=false) {
		if(!isset($media))
			return false;
		if(!is_array($media))
			$media = array($media);

		$this->media = $media;
		$this->activity_text = $activity_text;
		$this->privacy = $privacy;
	}

	function create_activity_html(){
           
                
		$html = '';

		$html .='<div class="rt-media-activity-container">';

			if(!empty($this->activity_text)) {
				$html .= '<span class="rt-media-activity-text">';
					$html .= $this->activity_text;
				$html .= '</span>';
			}

			$mediaObj = new RTMediaModel();
			$media_details = $mediaObj->get(array('media_id'=> $this->media));

			$html .= '<ul class="rt-media-list large-block-grid-5">';
			foreach ($media_details as $media) {
				$html .= '<li class="rt-media-list-item">';
					$html .= '<div class="rt-media-item-thumbnail">';
						$html .= '<a href ="'. get_rt_media_permalink($media->id) .'">';
							$html .= '<img src="'. $this->image($media) .'" >';
						$html .= '</a>';
					$html .= '</div>';

					$html .= '<div class="rt-media-item-title">';
						$html .= '<h4 title="'. $media->media_title .'">';

							$html .= '<a href="'. get_rt_media_permalink($media->id) .'">';

								$html .= $media->media_title;
							$html .= '</a>';
						$html .= '</h4>';
					$html .= '</div>';

					$html .= '<div class="rt-media-item-actions">';
						$html .= $this->actions();
					$html .= '</div>';
				$html .= '</li>';
			}
			$html .= '</ul>';
		$html .= '</div>';
		return $html;
	}

        function actions(){
            
        }
	function image($media) {
		if (isset($media->media_type)) {
			if ($media->media_type == 'album' ||
					$media->media_type != 'image') {
				$thumbnail_id = get_rtmedia_meta($media->media_id,'cover_art');
			} elseif ( $media->media_type == 'image' ) {
				$thumbnail_id = $media->media_id;
			} else {
				return false;
			}
		} else {
			return false;
		}
		if (!$thumbnail_id)
			return false;
		list($src, $width, $height) = wp_get_attachment_image_src($thumbnail_id);
                return $src;
	}
}

?>
