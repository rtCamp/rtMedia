<?php
/**
 * Handles rtMedia Gallery Shortcode to embed a gallery of media anywhere
 *
 * @package rtMedia
 * @author Udit Desai <udit.desai@rtcamp.com>
 */

/**
 * Class to handle rtMedia Gallery Shortcode to embed a gallery of media anywhere
 */
class RTMediaGalleryShortcode {

	/**
	 * Add shortcode script or not.
	 *
	 * @var bool
	 */
	public static $add_script;

	/**
	 * RTMediaGalleryShortcode constructor.
	 */
	public function __construct() {
		add_shortcode( 'rtmedia_gallery', array( 'RTMediaGalleryShortcode', 'render' ) );
		add_action( 'wp_ajax_rtmedia_get_template', array( &$this, 'ajax_rtmedia_get_template' ) );
		add_action( 'wp_ajax_nopriv_rtmedia_get_template', array( &$this, 'ajax_rtmedia_get_template' ) );
	}

	/**
	 * Get template for json response.
	 */
	public function ajax_rtmedia_get_template() {
		$template = sanitize_text_field( filter_input( INPUT_GET, 'template', FILTER_SANITIZE_STRING ) );

		if ( ! empty( $template ) ) {
			$template_url = RTMediaTemplate::locate_template( $template, 'media/', false );
			require_once $template_url;
		}
		die();
	}

	/**
	 * Register shortcode scripts.
	 */
	public static function register_scripts() {
		$options = get_option( 'rtmedia-options' );
		/**
		 * Check whether user is allowed to upload media without login
		 */
		if ( is_user_logged_in() || ( isset( $options['general_enable_anonymous_bbpress_reply'] ) && 1 === $options['general_enable_anonymous_bbpress_reply'] ) || ( isset( $options['general_enable_anonymous_comment'] ) && 1 === $options['general_enable_anonymous_comment'] ) ) {

			/**
			 * This script handles upload related operations, so load it only when necessary
			 */
			if ( ! wp_script_is( 'plupload-all' ) ) {
				wp_enqueue_script( 'plupload-all' );
			}
		}

		wp_enqueue_script(
			'rtmedia-backbone',
			RTMEDIA_URL . 'app/assets/js/rtMedia.backbone.js',
			array(
				'plupload-all',
				'backbone',
				// Whole rtmedia-backbone file is dependent on rtmedia-main  file (eg. rtMediaHook).
				'rtmedia-main',
			),
			RTMEDIA_VERSION,
			true
		);

		if ( is_rtmedia_album_gallery() ) {

			$album_template_args = apply_filters(
				'album_template_args',
				array(
					'action'   => 'rtmedia_get_template',
					'template' => 'album-gallery-item',
				)
			);

			$template_url = esc_url( add_query_arg( $album_template_args, admin_url( 'admin-ajax.php' ) ), null, '' );
		} else {

			$media_template_args = apply_filters(
				'media_template_args',
				array(
					'action'   => 'rtmedia_get_template',
					'template' => apply_filters( 'rtmedia_backbone_template_filter', 'media-gallery-item' ),
				)
			);

			$template_url = esc_url( add_query_arg( $media_template_args, admin_url( 'admin-ajax.php' ) ), null, '' );
		}
		wp_localize_script( 'rtmedia-backbone', 'rtmedia_template', array( 'template_url' => $template_url ) );

		$request_uri = rtm_get_server_var( 'REQUEST_URI', 'FILTER_SANITIZE_URL' );
		$url         = rtmedia_get_upload_url( $request_uri );

		$upload_max_size = ( wp_max_upload_size() ) / ( 1024 * 1024 ) . 'M';
		$params          = array(
			'url'                 => $url,
			'runtimes'            => 'html5,flash,html4',
			'browse_button'       => apply_filters( 'rtmedia_upload_button_id', 'rtMedia-upload-button' ),
			'container'           => 'rtmedia-upload-container',
			'drop_element'        => 'drag-drop-area',
			'filters'             => apply_filters(
				'rtmedia_plupload_files_filter',
				array(
					array(
						'title'      => esc_html__( 'Media Files', 'buddypress-media' ),
						'extensions' => get_rtmedia_allowed_upload_type(),
					),
				)
			),
			'max_file_size'       => $upload_max_size,
			'multipart'           => true,
			'urlstream_upload'    => true,
			'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
			'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
			'file_data_name'      => 'rtmedia_file', // key passed to $_FILE.
			'multi_selection'     => true,
			'multipart_params'    => apply_filters( // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
				'rtmedia-multi-params',
				array(
					'redirect'             => 'no',
					'redirection'          => 'false',
					'action'               => 'wp_handle_upload',
					'_wp_http_referer'     => $request_uri,
					'mode'                 => 'file_upload',
					'rtmedia_upload_nonce' => RTMediaUploadView::upload_nonce_generator( false, true ),
				)
			),
			'max_file_size_msg'   => apply_filters( 'rtmedia_plupload_file_size_msg', $upload_max_size ),
		);

		$params = apply_filters( 'rtmedia_modify_upload_params', $params );

		wp_localize_script( 'rtmedia-backbone', 'rtMedia_plupload', array( 'rtMedia_plupload_config' => $params ) );
	}

	/**
	 * Helper function to check whether the shortcode should be rendered or not
	 *
	 * @return bool
	 */
	public static function display_allowed() {
		$flag = true;

		$flag = apply_filters( 'before_rtmedia_gallery_display', $flag );

		return $flag;
	}

	/**
	 * Render a shortcode according to the attributes passed with it
	 *
	 * @param bool|array $attr Shortcode attributes.
	 *
	 * @return bool|string
	 */
	public static function render( $attr ) {

		if ( self::display_allowed() ) {

			self::$add_script = true;

			ob_start();
			$authorized_member = true; // by default, viewer is authorized.

			if ( ( ! isset( $attr ) ) || empty( $attr ) ) {
				$attr = true;
			}

			$attr = array(
				'name' => 'gallery',
				'attr' => $attr,
			);

			$attr = apply_filters( 'rtmedia_gallery_shortcode_parameter_pre_filter', $attr );

			global $post, $rtmedia_shortcode_attr;
			if ( isset( $attr ) && isset( $attr['attr'] ) ) {
				if ( ! is_array( $attr['attr'] ) ) {
					$attr['attr'] = array();
				}
				if ( ! isset( $attr['attr']['context_id'] )
					&& isset( $attr['attr']['context'] )
					&& 'profile' === $attr['attr']['context']
				) {
					$attr['attr']['context_id'] = get_current_user_id();
				} elseif ( ! isset( $attr['attr']['context_id'] ) && isset( $post->ID ) ) {
					$attr['attr']['context_id'] = $post->ID;
				}

				// check if context is group, then the gallery should only be visible to users according to the group privacy.
				if ( isset( $attr['attr']['context'] ) && 'group' === $attr['attr']['context'] ) {

					if ( function_exists( 'groups_get_group' ) ) {  // if buddypress group is enabled.
						$group = groups_get_group( array( 'group_id' => $attr['attr']['context_id'] ) );
						if ( isset( $group->status ) && 'public' !== $group->status ) {
							if ( is_user_logged_in() ) {
								$is_member = groups_is_user_member( get_current_user_id(), $attr['attr']['context_id'] );
								if ( ! $is_member ) {
									$authorized_member = false;
									// if user does not have access to the specified group.
								}
							} else {
								$authorized_member = false;
								// if user is  group not logged in and visits group media gallery.
							}
						}
					}
				}

				if ( ! isset( $attr['attr']['context'] ) && isset( $post->post_type ) ) {
					$attr['attr']['context'] = $post->post_type;
				}
			}// End if.

			$rtmedia_shortcode_attr = $attr['attr'];

			// Set template according to media type.
			if ( is_rtmedia_album_gallery() || ( isset( $attr['attr']['media_type'] ) && 'album' === $attr['attr']['media_type'] ) ) {

				$album_template_args = apply_filters(
					'album_template_args',
					array(
						'action'   => 'rtmedia_get_template',
						'template' => 'album-gallery-item',
					)
				);

				$template_url = esc_url( add_query_arg( $album_template_args, admin_url( 'admin-ajax.php' ) ), null, '' );
			} else {

				$media_template_args = apply_filters(
					'media_template_args',
					array(
						'action'   => 'rtmedia_get_template',
						'template' => apply_filters( 'rtmedia_backbone_template_filter', 'media-gallery-item' ),
					)
				);

				$template_url = esc_url( add_query_arg( $media_template_args, admin_url( 'admin-ajax.php' ) ), null, '' );
			}

			wp_localize_script( 'rtmedia-backbone', 'rtmedia_template', array( 'template_url' => $template_url ) );

			/**
			 * Remove search_filter parameter from attr,
			 * Reason: Showing error on Database Errors [ SQL Query ]
			 * Solution: Unset search_filter parameter store value on another variable.
			 */
			if ( isset( $attr['attr']['search_filter'] ) ) {
				if ( 'true' === $attr['attr']['search_filter'] ) {
					$search_filter_status = $attr['attr']['search_filter'];
					unset( $attr['attr']['search_filter'] );
				}
			}

			if ( $authorized_member ) {  // if current user has access to view the gallery (when context is 'group').
				global $rtmedia_query;

				if ( ! $rtmedia_query ) {
					$rtmedia_query = new RTMediaQuery( $attr['attr'] );
				}

				do_action( 'rtmedia_shortcode_action', $attr['attr'] );// do extra stuff with attributes.
				$page_number                         = ( get_query_var( 'pg' ) ) ? get_query_var( 'pg' ) : 1; // get page number.
				$rtmedia_query->action_query->page   = intval( $page_number );
				$rtmedia_query->is_gallery_shortcode = true;// to check if gallery shortcode is executed to display the gallery.

				$template         = new RTMediaTemplate();
				$gallery_template = false;

				if ( isset( $attr['attr']['global'] ) && true === (bool) $attr['attr']['global'] ) {
					add_filter(
						'rtmedia-model-where-query',
						array(
							'RTMediaGalleryShortcode',
							'rtmedia_query_where_filter',
						),
						10,
						3
					);
				}

				$attr['attr']['hide_comment_media'] = false;
				$remove_comment_media               = apply_filters( 'rtmedia_query_where_filter_remove_comment_media', true, 'galleryshortcode' );

				if ( isset( $remove_comment_media ) && ! empty( $remove_comment_media ) ) {
					add_filter( 'rtmedia-model-where-query', array( 'RTMediaGalleryShortcode', 'rtmedia_query_where_filter_remove_comment_media' ), 11, 3 );
					$attr['attr']['hide_comment_media'] = true;
				}

				/**
				 * Set search_filter into attr array, if set search_filter parameter in shortcode.
				 */
				if ( isset( $search_filter_status ) ) {
					$attr['attr']['search_filter'] = $search_filter_status;
				}

				$template->set_template( $gallery_template, $attr );

				if ( isset( $remove_comment_media ) && ! empty( $remove_comment_media ) ) {
					remove_filter( 'rtmedia-model-where-query', array( 'RTMediaGalleryShortcode', 'rtmedia_query_where_filter_remove_comment_media' ), 11 );
				}

				if ( isset( $attr['attr']['global'] ) && true === (bool) $attr['attr']['global'] ) {
					remove_filter(
						'rtmedia-model-where-query',
						array(
							'RTMediaGalleryShortcode',
							'rtmedia_query_where_filter',
						),
						10
					);
				} // End if.
			} else { // if user cannot view the media gallery (when context is 'group'), show message.
				esc_html_e( 'You do not have sufficient privileges to view this gallery', 'buddypress-media' );
				return false;
			} // End if.
			return ob_get_clean();
		} // End if.
	}

	/**
	 * For gallery shortcode remove all comment media reply
	 *
	 * @param string $where Where clause of query.
	 * @param string $table_name Table name for query.
	 * @param string $join Join query part.
	 *
	 * @return string
	 */
	public static function rtmedia_query_where_filter_remove_comment_media( $where, $table_name, $join ) {
		if ( function_exists( 'rtmedia_query_where_filter_remove_comment_media' ) ) {
			$where = rtmedia_query_where_filter_remove_comment_media( $where, $table_name, $join );
		}
		return $where;
	}

	/**
	 * For gallery shortcode having attribute global as true, include all media except ones having context as "group".
	 *
	 * @param string $where Where clause of query.
	 * @param string $table_name Table name for query.
	 * @param string $join Join query part.
	 *
	 * @return string
	 */
	public static function rtmedia_query_where_filter( $where, $table_name, $join ) {
		$where .= ' AND (' . $table_name . '.privacy = "0" OR ' . $table_name . '.privacy is NULL ) ';

		return $where;
	}
}
