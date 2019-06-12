<?php
/**
 * Description of RTMediaUploadView
 *
 * @author joshua
 */
class RTMediaUploadView {

	private $attributes;

	/**
	 *
	 * @param type $attr
	 */
	function __construct( $attr ) {
		$this->attributes = $attr;
	}

	static function upload_nonce_generator( $echo = true, $only_nonce = false ) {

		if ( $echo ) {
			wp_nonce_field( 'rtmedia_upload_nonce', 'rtmedia_upload_nonce' );
		} else {
			if ( $only_nonce ) {
				return wp_create_nonce( 'rtmedia_upload_nonce' );
			}
			$token = array(
				'action' => 'rtmedia_upload_nonce',
				'nonce'  => wp_create_nonce( 'rtmedia_upload_nonce' ),
			);

			return wp_json_encode( $token );
		}
	}

	/**
	 * Render the uploader shortcode and attach the uploader panel
	 *
	 * @param type $template_name
	 */
	public function render( $template_name ) {

		global $rtmedia_query;
		$album = '';
		if ( apply_filters( 'rtmedia_render_select_album_upload', true ) ) {
			if ( $rtmedia_query && isset( $rtmedia_query->media_query ) && isset( $rtmedia_query->media_query['album_id'] ) && is_rtmedia_album( $rtmedia_query->media_query['album_id'] ) ) {
				$album = '<input class="rtmedia-current-album" type="hidden" name="rtmedia-current-album" value="' . esc_attr( $rtmedia_query->media_query['album_id'] ) . '" />';
			} elseif ( is_rtmedia_album_enable() && $rtmedia_query && is_rtmedia_gallery() ) {

				if ( isset( $rtmedia_query->query['context'] ) && 'profile' === $rtmedia_query->query['context'] ) {
					$album = '<span> <label> <i class="dashicons dashicons-format-gallery"></i>' . esc_html__( 'Album', 'buddypress-media' ) . ': </label><select name="album" class="rtmedia-user-album-list">' . rtmedia_user_album_list() . '</select></span>';
				}
				if ( isset( $rtmedia_query->query['context'] ) && 'group' === $rtmedia_query->query['context'] ) {
					$album = '<span> <label> <i class="dashicons dashicons-format-gallery"></i>' . esc_html__( 'Album', 'buddypress-media' ) . ': </label><select name="album" class="rtmedia-user-album-list">' . rtmedia_group_album_list() . '</select></span>';
				}
			}
		}
		$up_privacy = $privacy = ''; //uploader privacy dropdown for uploader under rtMedia Media tab.
		if ( is_rtmedia_privacy_enable()
			&& ( ( ! isset( $rtmedia_query->is_upload_shortcode ) || false === $rtmedia_query->is_upload_shortcode ) )
			|| ( isset( $rtmedia_query->is_upload_shortcode ) && ! isset( $this->attributes['privacy'] ) )
		) {
			if ( ( isset( $rtmedia_query->query['context'] ) && 'group' === $rtmedia_query->query['context'] ) || ( function_exists( 'bp_is_groups_component' ) && bp_is_groups_component() ) ) {
				// bp_get_group_status() is always reurning NULL.
				// So fetched data from DB and assigned to media.
				global $wpdb;

				// BP Groups tablename.
				$table_name = $wpdb->prefix . 'bp_groups';
				// Will fetch ID of current group.
				$group_id = bp_get_current_group_id();

				// Use cached data for group status.
				$group_status = get_transient( 'group_status_' . $group_id );

				if ( false === $group_status ) {
					// Query to fetch current group's privacy status.
					$group_status = $wpdb->get_var( 'SELECT `status` FROM ' . $table_name . ' WHERE id = ' . $group_id );

					// Set data in transient for better performance.
					set_transient( 'group_status_' . $group_id, $group_status );
				}

				$privacy_levels_array = array(
					'public'  => 0,
					'private' => 20,
					'hidden'  => 20,
				);
				$privacy_levels_array = apply_filters( 'rtmedia_group_privacy_levels', $privacy_levels_array );
				$privacy_val          = $privacy_levels_array[ $group_status ];

				$up_privacy = $privacy = "<input type='hidden' name='privacy' value='" . esc_attr( $privacy_val ) . "' />";
			} else {
				$up_privacy = new RTMediaPrivacy( false );
				$up_privacy = $up_privacy->select_privacy_ui( false, 'rtSelectPrivacy' );
				if ( $up_privacy ) {
					$privacy = "<span> <label for='privacy'> <i class='dashicons dashicons-visibility'></i>" . esc_html__( 'Privacy:', 'buddypress-media' ) . '</label>' . $up_privacy . '</span>';
				}
			}
		}

		$upload_tabs = array(
			'file_upload' => array(
				'title'   => esc_html__( 'File Upload', 'buddypress-media' ),
				'class'   => array( 'rtm-upload-tab', 'active' ),
				'content' => '<div class="rtm-upload-tab-content" data-id="rtm-upload-tab">'
					. apply_filters( 'rtmedia_uploader_before_select_files', '' )
					. '<div class="rtm-select-files"><input id="' . apply_filters( 'rtmedia_upload_button_id', 'rtMedia-upload-button' ) . '" value="' . esc_attr__( 'Select your files', 'buddypress-media' ) . '" type="button" class="rtmedia-upload-input rtmedia-file" />'
					. '<span class="rtm-seperator">' . esc_html__( 'or', 'buddypress-media' ) . '</span><span class="drag-drop-info">' . esc_html__( 'Drop your files here', 'buddypress-media' ) . '</span> <i class="rtm-file-size-limit dashicons dashicons-info"></i></div>'
					. apply_filters( 'rtmedia_uploader_after_select_files', '' )
					. '</div>',
			),
		);

		$upload_tabs = apply_filters( 'rtmedia_uploader_tabs', $upload_tabs );

		if ( is_array( $upload_tabs ) && ! empty( $upload_tabs ) ) {
			if ( 1 === count( $upload_tabs ) && isset( $upload_tabs['file_upload'] ) ) {
				$upload_tab_html = $upload_tabs['file_upload']['content'];
			} else {
				$upload_tab_html = '<div class="rtm-uploader-main-wrapper"><div class="rtm-uploader-tabs"><ul>';
				foreach ( $upload_tabs as $single_tab ) {
					$upload_tab_html .= '<li class="' . implode( ' ', $single_tab['class'] ) . '">' . $single_tab['title'] . '</li>';
				}
				$upload_tab_html .= '</ul></div>';
				foreach ( $upload_tabs as $single_tab ) {
					$upload_tab_html .= $single_tab['content'];
				}
				$upload_tab_html .= '</div>';
			}
		} else {
			$upload_tab_html = '';
		}
		global $rtmedia;

		$rtmedia_comment_main = "rtmedia-comment-action-update";
		$rtmedia_comment_container = "rtmedia-comment-media-upload-container";
		$rtmedia_comment_button = "rtmedia-comment-media-upload";
		$rtmedia_comment_filelist = "rtmedia_uploader_filelist";
		$rtmedia_comment_context = "activity";
		if(
			( isset( $this->attributes['upload_parent_id'] )  && ! empty( $this->attributes['upload_parent_id'] )  )
			&&
			( isset( $this->attributes['upload_parent_id_type'] )  && ! empty( $this->attributes['upload_parent_id_type'] )  )
		){
			$main_id =  '-'.$this->attributes['upload_parent_id_type'].'-'.$this->attributes['upload_parent_id'];
			$rtmedia_comment_main .= $main_id;
			$rtmedia_comment_container .= $main_id;
			$rtmedia_comment_button .= $main_id;
			$rtmedia_comment_filelist .= $main_id;
			$up_privacy = $privacy = "<input type='hidden' name='privacy' value='" . esc_attr( 0 ) . "' />";
			if( isset( $this->attributes['upload_parent_id_context'] ) ){
				$rtmedia_comment_context = $this->attributes['upload_parent_id_context'];
			}
		}

		$upload_button = '<input type="button" class="start-media-upload" value="' . esc_attr__( 'Start upload', 'buddypress-media' ) . '"/>';
		$tabs          = array(
			'file_upload' => array(
				'default'  => array(
					'title'   => esc_html__( 'File Upload', 'buddypress-media' ),
					'content' =>
						'<div id="rtmedia-upload-container" >'
						. '<div id="drag-drop-area" class="drag-drop clearfix">'
						. apply_filters( 'rtmedia_uploader_before_album_privacy', '' )
						. "<div class='rtm-album-privacy'>" . $album . $privacy . '</div>'
						. $upload_tab_html
						. apply_filters( 'rtmedia_uploader_before_start_upload_button', '' )
						. $upload_button
						. apply_filters( 'rtmedia_uploader_after_start_upload_button', '' )
						. '</div>'
						. '<div class="clearfix">'
						. '<ul class="plupload_filelist_content ui-sortable rtm-plupload-list clearfix" id="rtmedia_uploader_filelist"></ul>'
						. '</div>'
						. '</div>',
				),
				'activity' => array(
					'title'   => esc_html__( 'File Upload', 'buddypress-media' ),
					'content' =>
						'<div class="rtmedia-plupload-container rtmedia-container clearfix">'
						. '<div id="rtmedia-action-update" class="clearfix">'
						. '<div class="rtm-upload-button-wrapper">'
						. '<div id="rtmedia-whts-new-upload-container">'
						. '</div>'
						. '<button type="button" class="rtmedia-add-media-button" id="' . apply_filters( 'rtmedia_upload_button_id', 'rtmedia-add-media-button-post-update' ) . '" title="' . apply_filters( 'rtmedia_attach_media_button_title', esc_attr__( 'Attach Media', 'buddypress-media' ) ) . '">'
						. '<span class="dashicons dashicons-admin-media"></span>'
						. apply_filters( 'rtmedia_attach_file_message', '' )
						. '</button>'
						. '</div>'
						. $up_privacy
						. '</div>'
						. '</div>'
						. apply_filters( 'rtmedia_uploader_after_activity_upload_button', '' )
						. '<div class="rtmedia-plupload-notice">'
						. '<ul class="plupload_filelist_content ui-sortable rtm-plupload-list clearfix" id="rtmedia_uploader_filelist"></ul>'
						. '</div>',
				),
				'comment' => array(
					'title'   => esc_html__( 'File Upload', 'buddypress-media' ),
					'content' =>
						'<div class="rtmedia-plupload-container rtmedia-comment-media-main rtmedia-container clearfix">'
							. '<div id="'.$rtmedia_comment_main.'" class="clearfix">'
								. '<div class="rtm-upload-button-wrapper">'
									. '<div id="'.$rtmedia_comment_container.'">'
									. '</div>'
									. '<button type="button" class="rtmedia-comment-media-upload" data-media_context="'.$rtmedia_comment_context.'" id="' . $rtmedia_comment_button . '" title="' . apply_filters( 'rtmedia_comment_attach_media_button_title', esc_attr__( 'Attach Media', 'buddypress-media' ) ) . '">'
										. '<span class="dashicons dashicons-admin-media"></span>'
										. apply_filters( 'rtmedia_attach_file_message', '' )
									. '</button>'
								. '</div>'
								. $up_privacy
							. '</div>'
						. '</div>'
						. apply_filters( 'rtmedia_uploader_after_comment_upload_button', '' )
						. '<div class="rtmedia-plupload-notice">'
							. '<ul class="plupload_filelist_content ui-sortable rtm-plupload-list clearfix" id="'. $rtmedia_comment_filelist .'"></ul>'
						. '</div>',
				),
			),
			'link_input'  => array(
				'title'   => esc_html__( 'Insert from URL', 'buddypress-media' ),
				'content' => '<input type="url" name="bp-media-url" class="rtmedia-upload-input rtmedia-url" />',
			),
		);
		$tabs          = apply_filters( 'rtmedia_upload_tabs', $tabs );

		$attr = $this->attributes;
		$mode = filter_input( INPUT_GET, 'mode', FILTER_SANITIZE_STRING );
		if ( is_null( $mode ) || false === $mode || ! array_key_exists( $mode, $tabs ) ) {
			$mode = 'file_upload';
		}
		if ( $attr && is_array( $attr ) ) {
			foreach ( $attr as $key => $val ) {
				$selector = "id";
				if( ( $key == 'upload_parent_id'  && ! empty( $key ) ) || ( $key == 'upload_parent_id_type'  && ! empty( $key ) ) ){
					$selector = "class";
				}
				?>
				<input type='hidden' <?php echo $selector; ?>="rt_upload_hf_<?php echo esc_attr( $key ); ?>"
				       value='<?php echo esc_attr( $val ); ?>'
				       name='<?php echo esc_attr( $key ); ?>'/>
				<?php
			}
		}
		$upload_type = 'default';
		if ( isset( $attr['activity'] ) && $attr['activity'] ) {
			$upload_type = 'activity';
		}

		if ( isset( $attr['comment'] ) && $attr['comment'] ) {
			$upload_type = 'comment';
		}

		$upload_helper = new RTMediaUploadHelper();
		include $this->locate_template( $template_name );
	}

	/**
	 * Template Locator
	 *
	 * @param type $template
	 *
	 * @return string
	 */
	protected function locate_template( $template ) {
		$located = '';

		$template_name = $template . '.php';

		if ( ! $template_name ) {
			$located = false;
		}
		if ( file_exists( STYLESHEETPATH . '/rtmedia/upload/' . $template_name ) ) {
			$located = STYLESHEETPATH . '/rtmedia/upload/' . $template_name;
		} else if ( file_exists( TEMPLATEPATH . '/rtmedia/upload/' . $template_name ) ) {
			$located = TEMPLATEPATH . '/rtmedia/upload/' . $template_name;
		} else {
			$located = RTMEDIA_PATH . 'templates/upload/' . $template_name;
		}

		return $located;
	}
}
