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

		add_action( 'wp_enqueue_scripts', array( $this,'enqueue_scripts') );
		add_action('init', array($this,'enqueue_image_editor_scripts'));
	}

	/**
	 * Enqueues required scripts on the page
	 */
	function enqueue_scripts(){
		wp_enqueue_script('rtmedia-backbone');
	}

	function enqueue_image_editor_scripts() {
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script('wp-ajax-response');
		wp_enqueue_script('rt-media-image-edit', admin_url("js/image-edit$suffix.js"), array('jquery', 'json2', 'imgareaselect'), false, 1);
		wp_enqueue_style('rt-media-image-edit', RTMEDIA_URL . 'app/assets/css/image-edit.css');
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

		global $rt_media_query, $rt_media_interaction, $rt_media_media;

		$media_array = array();

		if(in_array($rt_media_interaction->context->type, array("profile","group"))) {

			if ($rt_media_query->format == 'json') {
				if ($rt_media_query->media) {
					foreach ($rt_media_query->media as $key => $media) {
						$media_array[$key] = $media;
						list($src,$width,$height) = wp_get_attachment_image_src($media->media_id,'thumbnail');
						$media_array[$key]->guid = $src;
					}
				}
				echo json_encode($media_array);
				die;
			}
			else
				return $this->get_default_template();
		} else if($rt_media_interaction->context->type=="activity") {
			echo 'Activity Handling';
		} else if( $rt_media_query->action_query->action == 'comments' ) {

			if(isset($rt_media_query->action_query->media_type) && !count($_POST) ) {
				/**
				 * /media/comments [GET]
				 *
				 */
				$media_array = array();
				if($rt_media_query->media) {
					foreach($rt_media_query->media as $media){
						$media_array[] = $media;
					}
				}
				echo json_encode( $media_array );
				die;
			} else if( isset($rt_media_query->action_query->id) && count($_POST)) {
				/**
				 * /media/comments [POST]
				 * Post a comment to the album by post id
				 */

				$nonce = $_REQUEST['rt_media_comment_nonce'];
				if (wp_verify_nonce($nonce, 'rt_media_comment_nonce')) {
					$comment = new RTMediaComment();
					$attr = $_POST;
					if(!isset($attr['comment_post_ID']))
						$attr['comment_post_ID'] = $rt_media_query->action_query->id;
					$comment->add($attr);
				}
				else {
					echo "Ooops !!! Invalid access. No nonce was found !!";
				}
			}
			return $this->get_default_template();

		} else if($rt_media_query->action_query->action == 'edit' && count($_POST)) {
			/**
			 * /media/id/edit [POST]
			 * save details of media
			 *
			 */
			if(is_rt_media_single()) {

				$nonce = $_REQUEST['rt_media_media_nonce'];
				if (wp_verify_nonce($nonce, 'rt_media_media_nonce')) {
					$data = $_POST;
					unset($data['rt_media_media_nonce']);
					unset($data['_wp_http_referer']);
					$media = new RTMediaMedia();
					$media->update($rt_media_query->action_query->id, $data, $rt_media_query->media[0]->media_id);
					$rt_media_query->query(false);
				} else{
					echo "Ooops !!! Invalid access. No nonce was found !!";
				}
			} else {
				 echo "media album update handling.";
			}
			return $this->get_default_template();

		} else if($rt_media_query->action_query->action == 'delete') {
			/**
			 * /media/id/delete [POST]
			 */
			if(is_rt_media_single()) {

				$nonce = $_REQUEST['rt_media_media_nonce'];
				if (wp_verify_nonce($nonce, 'rt_media_media_nonce')) {
					$id = $_POST;
					unset($id['rt_media_media_nonce']);
					unset($id['_wp_http_referer']);
					$media = new RTMediaMedia();

                                        wp_delete_attachment($rt_media_query->media[0]->media_id,true);

					$post = get_post($rt_media_query->media[0]->post_parent);

					$link = get_site_url() . '/' . $post->post_name . '/media';

					wp_redirect($link);
				} else{
					echo "Ooops !!! Invalid access. No nonce was found !!";
				}
			}else {
				echo "media album delete handling";
			}
			return $this->get_default_template();
		} else if($rt_media_query->action_query->action == 'upload') {
			$upload = new RTMediaUploadEndpoint();
			$upload->template_redirect();
		} else if ( $rt_media_query->format == 'json' ) {

			$media_array = array();
			if($rt_media_query->media) {
				foreach($rt_media_query->media as $key => $media){
					$media_array[$key] = $media;
					list($src,$width,$height) = wp_get_attachment_image_src($media->media_id,'thumbnail');
					$media_array[$key]->guid = $src;
					$post = get_post($media->post_parent);
					$media_array[$key]->rt_permalink = get_site_url() . '/' . $post->post_name . '/media/' . $media->id;
				}
			}
            $return_array['data'] = $media_array;
			$return_array['prev'] = rt_media_page()-1;
			$return_array['next'] = (rt_media_offset()+ rt_media_per_page_media() < rt_media_count())?(rt_media_page()+1): -1 ;

			echo json_encode($return_array);
			die;

		} else if(!$shortcode_attr)
			return $this->get_default_template();
		else if($shortcode_attr['name'] == 'gallery') {
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
	function get_default_template() {

		return apply_filters( 'rt_media_media_template_include', RTMEDIA_PATH . 'app/main/controllers/template/template.php');

	}

	/**
	 * Template Locator
	 *
	 * @param type $template
	 * @return string
	 */
	static function locate_template( $template, $context=false ) {
		$located = '';
		if ( ! $template )
			return;

		$template_name = $template . '.php';

		if(!$context) $context = 'rt-media';

		$path = '/'.$context.'/';
		$ogpath = 'templates/media/';


		if ( file_exists( STYLESHEETPATH . $path . $template_name ) ) {
			$located = STYLESHEETPATH . $path . $template_name;
		} else if ( file_exists( TEMPLATEPATH . $path . $template_name ) ) {
			$located = TEMPLATEPATH . $path . $template_name;
		} else {
			$located = RTMEDIA_PATH . $ogpath . $template_name;
		}

		return $located;
	}



}
?>
