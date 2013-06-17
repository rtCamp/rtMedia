<?php

/**
 * Description of RTMediaTemplate
 *
 * Template to display rtMedia Gallery.
 * A stand alone template that renders the gallery/uploader on the page.
 *
 * @author saurabh
 */

class RTMediaTemplate {

	public $media_args;

	function __construct() {
//		add_action( 'wp_enqueue_scripts', array( $this,'enqueue_scripts') );

	}

	/**
	 * Enqueues required scripts on the page
	 */
	function enqueue_scripts(){
		wp_enqueue_script('backbone');
		wp_enqueue_script('rtmedia-models', RT_MEDIA_URL.'app/assets/js/backbone/models.js', array('backbone'));
		wp_enqueue_script('rtmedia-collections', RT_MEDIA_URL.'app/assets/js/backbone/collections.js', array('backbone','rtmedia-models'));
		wp_enqueue_script('rtmedia-views', RT_MEDIA_URL.'app/assets/js/backbone/views.js', array('backbone','rtmedia-collections'));
		wp_enqueue_script('rtmedia-backbone', RT_MEDIA_URL.'app/assets/js/backbone/rtMedia.backbone.js',array('rtmedia-models','rtmedia-collections','rtmedia-views'));
	}

	/**
	 * redirects to the template according to the page request
	 * Pass on the shortcode attributes to the template so that the shortcode can berendered accordingly.
	 *
	 * Also handles the json request coming from the AJAX calls for the media
	 *
	 * @global type $rt_media_query
	 * @global type $rt_media_interaction
	 * @param type $template
	 * @param type $shortcode_attr
	 * @return type
	 */
	function set_template($template, $shortcode_attr = false) {

		global $rt_media_query;

		$media_array = '';

		/* Includes db specific wrapper functions required to render the template */
		include(RT_MEDIA_PATH . 'app/main/controllers/template/rt-template-functions.php');
		$this->enqueue_scripts();

		if( $rt_media_query->action_query->action == 'comments'
				&& isset($rt_media_query->action_query->media_type)
				&& !count($_POST) ) {
			/**
			 * /media/comments [GET]
			 *
			 */
			if($rt_media_query->media) {
				foreach($rt_media_query->media as $media){
					$media_array[] = $media;
				}
			}
			echo json_encode( $media_array );
			return;
		} else if($rt_media_query->action_query->action == 'comments'
				&& isset($rt_media_query->action_query->media_type)
				&& count($_POST)) {
			/**
			 * /media/comments [POST]
			 * Post a comment to the album by post id
			 */

			$nonce = $_REQUEST['rt_media_comment_nonce'];
            if (wp_verify_nonce($nonce, 'rt_media_comment_nonce')) {
				$comment = new RTMediaComment();
				$comment->add($_POST);
			}
			else {
				echo "Ooops !!! Invalid access. No nonce was found !!";
			}
			return $this->get_template($template);

		} else if ( $rt_media_query->format != 'json' ) {

			if(!$shortcode_attr)
				return $this->get_template($template);
			else {
				if($shortcode_attr['name'] == 'gallery') {
					$valid = $this->sanitize_gallery_attributes($shortcode_attr['attr']);

					if($valid) {
						if(is_array($shortcode_attr['attr']))
							$this->update_global_query($shortcode_attr['attr']);
						include $this->locate_template($template);
					} else {
						echo 'Invalid attribute passed for rtmedia_gallery shortcode.';
						return false;
					}

				}
			}

		} else {


			if($rt_media_query->media) {
				foreach($rt_media_query->media as $media){
					$media_array[] = $media;
				}
			}
			echo json_encode( $media_array );
			return;
		}
	}

	/**
	 * Helper method to fetch allowed media types from each section
	 *
	 * @param type $allowed_type
	 * @return type
	 */
	function get_allowed_type_name($allowed_type) {
		return $allowed_type['name'];
	}

	/**
	 * Validates all the attributes for gallery shortcode
	 *
	 * @global type $rt_media
	 * @param string $attr
	 * @return type
	 */
	function sanitize_gallery_attributes(&$attr) {
		global $rt_media;

		$flag = true;

		if( isset($attr['media_type']) ) {
			$allowed_type_names = array_map(array($this, 'get_allowed_type_name'), $rt_media->allowed_types );

			if(strtolower($attr['media_type']) == 'all') {
				$flag = $flag && true;
				unset($attr['media_type']);
			} else
				$flag = $flag && in_array($attr['media_type'], $allowed_type_names);
		}

		if( isset($attr['order_by']) ) {

			$allowed_columns = array('date', 'views', 'downloads', 'ratings', 'likes', 'dislikes');
			$allowed_columns = apply_filters('filter_allowed_sorting_columns', $allowed_columns);

			$flag = $flag && in_array($attr['order_by'], $allowed_columns);

			if(strtolower($attr['order_by']) == 'date' )
				$attr['order_by'] = 'media_id';
		}

		if( isset($attr['order']) ) {
			$flag = $flag && strtolower($attr['order']) == 'asc' || strtolower($attr['order']) == 'desc';
		}

		return $flag;
	}

	function update_global_query($attr) {

		global $rt_media_query;

		$rt_media_query->query($attr);
	}

	/**
	 * filter to change the template path independent of the plugin
	 *
	 * @return type
	 */
	function get_template( ){

		return apply_filters( 'rt_media_media_template_include', RT_MEDIA_PATH . 'app/main/controllers/template/template.php');

	}

	/**
	 * Template Locator
	 *
	 * @param type $template
	 * @return string
	 */
	static function locate_template( $template ) {
		$located = '';
		if ( ! $template )
			return;

		$template_name = $template . '.php';

		if ( file_exists( STYLESHEETPATH . '/rt-media/' . $template_name ) ) {
			$located = STYLESHEETPATH . '/rt-media/' . $template_name;
		} else if ( file_exists( TEMPLATEPATH . '/rt-media/' . $template_name ) ) {
			$located = TEMPLATEPATH . '/rt-media/' . $template_name;
		} else {
			$located = RT_MEDIA_PATH . 'templates/media/' . $template_name;
		}

		return $located;
	}



}
?>