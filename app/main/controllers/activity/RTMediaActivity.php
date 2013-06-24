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
	function __construct($media, $activity_text=false, $privacy=0) {
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

			$html .= '<ul class="rt-media-list">';
			foreach ($media_details as $media) {
				$html .= '<li class="rt-media-list-item">';
					$html .= '<div class="rt-media-item-thumbnail">';
						$html .= '<a href ="'. $this->permalink($media) .'">';
							$html .= '<img src="'. $this->image($media) .'" >';
						$html .= '</a>';
					$html .= '</div>';

					$html .= '<div class="rt-media-item-title">';
						$html .= '<h4 title="'. $media->media_title .'">';
							$html .= '<a href="'. $this->permalink($media) .'">';
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

		list($src, $width, $height) = wp_get_attachment_image_src($thumbnail_id, $size);

		if ($return == "src")
			return $src;
	}

	function permalink($media) {
		$parent_link = '';

		if(function_exists('bp_core_get_user_domain')) {
			$parent_link = bp_core_get_user_domain($media->media_author);
		} else {
			$parent_link = get_author_posts_url($media->media_author);
		};

		$link = $parent_link . 'media/' . $rt_media_media->id;

		return $link;
	}

}

?>
