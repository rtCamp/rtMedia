<?php

/**
 * List of user actions
 */
function rtmedia_author_actions() {

	$author_actions = apply_filters( 'rtmedia_author_actions', true );
	if ( isset( $author_actions ) && ! empty( $author_actions ) ) {
		$options_start = $options_end = $option_buttons = $output = '';
		$options       = array();
		$options       = apply_filters( 'rtmedia_author_media_options', $options );

		if ( ! empty( $options ) ) {
			$options_start .= '<div class="click-nav rtm-media-options-list" id="rtm-media-options-list">
					<div class="no-js">
					<button class="clicker rtmedia-media-options rtmedia-action-buttons button">' . esc_html__( 'Options', 'buddypress-media' ) . '</button>
					<ul class="rtm-options">';

			foreach ( $options as $action ) {
				if ( ! empty( $action ) ) {
					$option_buttons .= '<li>' . $action . '</li>';
				}
			}

			$options_end = '</ul></div></div>';

			if ( ! empty( $option_buttons ) ) {
				$output = $options_start . $option_buttons . $options_end;
			}

			if ( ! empty( $output ) ) {
				echo $output; // @codingStandardsIgnoreLine
			}
		}
	}

}

add_action( 'after_rtmedia_action_buttons', 'rtmedia_author_actions' );

/**
 * Adding media edit tab
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @param       string          $type
 */
function rtmedia_image_editor_title( $type = 'photo' ) {

	global $rtmedia_query;

	if ( isset( $rtmedia_query->media[0]->media_type ) && 'photo' === $rtmedia_query->media[0]->media_type && 'photo' === $type ) {
		echo '<li><a href="#panel2" class="rtmedia-modify-image"><i class="dashicons dashicons-format-image rtmicon"></i>' . esc_html__( 'Image', 'buddypress-media' ) . '</a></li>';
	}

}

add_action( 'rtmedia_add_edit_tab_title', 'rtmedia_image_editor_title', 12, 1 );

/**
 * Add the content for the image editor tab
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @param       string          $type
 */
function rtmedia_image_editor_content( $type = 'photo' ) {

	global $rtmedia_query;

	if ( isset( $rtmedia_query->media ) && is_array( $rtmedia_query->media ) && isset( $rtmedia_query->media[0]->media_type ) && 'photo' === $rtmedia_query->media[0]->media_type && 'photo' === $type ) {
		$media_id      = $rtmedia_query->media[0]->media_id;
		$id            = $rtmedia_query->media[0]->id;
		$modify_button = $nonce = '';

		if ( current_user_can( 'edit_posts' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/image-edit.php' );
			$nonce         = wp_create_nonce( "image_editor-$media_id" );
			$modify_button = '<p><input type="button" class="button rtmedia-image-edit" id="imgedit-open-btn-' . esc_attr( $media_id ) . '" onclick="imageEdit.open( \'' . esc_attr( $media_id ) . '\', \'' . esc_attr( $nonce ) . '\' )" value="' . esc_attr__( 'Modify Image', 'buddypress-media' ) . '"> <span class="spinner"></span></p>';
		}

		$image_path = rtmedia_image( 'rt_media_activity_image', $id, false );

		echo '<div class="content" id="panel2">';
		echo '<div class="rtmedia-image-editor-cotnainer" id="rtmedia-image-editor-cotnainer" >';
		echo '<input type="hidden" id="rtmedia-filepath-old" name="rtmedia-filepath-old" value="' . esc_url( $image_path ) . '" />';
		echo '<div class="rtmedia-image-editor" id="image-editor-' . esc_attr( $media_id ) . '"></div>';

		$thumb_url = wp_get_attachment_image_src( $media_id, 'thumbnail', true );

		echo '<div id="imgedit-response-' . esc_attr( $media_id ) . '"></div>';
		echo '<div class="wp_attachment_image" id="media-head-' . esc_attr( $media_id ) . '">' . '<p id="thumbnail-head-' . esc_attr( $media_id ) . '"><img class="thumbnail" src="' . esc_url( set_url_scheme( $thumb_url[0] ) ) . '" alt="' . esc_attr( rtmedia_title() ) . '" /></p>' . $modify_button . '</div>'; // @codingStandardsIgnoreLine
		echo '</div>';
		echo '</div>';
	}

}

add_action( 'rtmedia_add_edit_tab_content', 'rtmedia_image_editor_content', 12, 1 );

/**
 * Provide drop-down to user to change the album of the media in media edit screen
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @param       string          $media_type
 */
function rtmedia_add_album_selection_field( $media_type ) {
	/* if comment media then album option is depend  on the top activity */
	$comment_media = rtmedia_is_comment_media( rtmedia_id() );

	if ( empty( $comment_media ) && is_rtmedia_album_enable() && isset( $media_type ) && 'album' != $media_type && apply_filters( 'rtmedia_edit_media_album_select', true ) ) {
		global $rtmedia_query;

		$curr_album_id = '';

		if ( isset( $rtmedia_query->media[0] ) && isset( $rtmedia_query->media[0]->album_id ) && ! empty( $rtmedia_query->media[0]->album_id ) ) {
			$curr_album_id = $rtmedia_query->media[0]->album_id;
		}
		?>
		<div class="rtmedia-edit-change-album rtm-field-wrap">
			<label for=""><?php esc_html_e( 'Album', 'buddypress-media' ); ?> : </label>
			<?php
			if ( isset( $rtmedia_query->query['context'] ) && 'group' === $rtmedia_query->query['context'] ) {
				//show group album list.
				$album_list = rtmedia_group_album_list( $selected_album_id = $curr_album_id );
			} else {
				//show profile album list
				$album_list = rtmedia_user_album_list( $get_all = false, $selected_album_id = $curr_album_id );
			} ?>
			<select name="album_id" class="rtmedia-merge-user-album-list"><?php echo $album_list; // @codingStandardsIgnoreLine?></select>
		</div>
		<?php
	}

}

add_action( 'rtmedia_add_edit_fields', 'rtmedia_add_album_selection_field', 14, 1 );

/**
 * Rendering gallery options
 */
function rtmedia_gallery_options() {

	$options_start = $options_end = $option_buttons = $output = '';
	$options       = array();
	$options       = apply_filters( 'rtmedia_gallery_actions', $options );

	if ( ! empty( $options ) ) {
		$options_start .= '<div class="click-nav rtm-media-options-list" id="rtm-media-options-list">
				<div class="no-js">
				<div class="clicker rtmedia-action-buttons"><i class="dashicons dashicons-admin-generic rtmicon"></i>' . apply_filters( 'rtm_gallery_option_label', __( 'Options', 'buddypress-media' ) ) . '</div>
				<ul class="rtm-options">';

		foreach ( $options as $action ) {
			if ( ! empty( $action ) ) {
				$option_buttons .= '<li>' . $action . '</li>';
			}
		}

		$options_end = '</ul></div></div>';

		if ( ! empty( $option_buttons ) ) {
			$output = $options_start . $option_buttons . $options_end;
		}

		if ( ! empty( $output ) ) {
			echo $output; // @codingStandardsIgnoreLine
		}
	}

}

add_action( 'rtmedia_media_gallery_actions', 'rtmedia_gallery_options', 80 );
add_action( 'rtmedia_album_gallery_actions', 'rtmedia_gallery_options', 80 );

/**
 * Rendering create an album markup
 *
 * @global      RTMediaQuery    $rtmedia_query
 */
function rtmedia_create_album_modal() {

	global $rtmedia_query, $rtmedia;

	if ( is_rtmedia_album_enable() && isset( $rtmedia_query->query['context_id'] ) && isset( $rtmedia_query->query['context'] ) && ( ! ( isset( $rtmedia_query->is_gallery_shortcode ) && true === $rtmedia_query->is_gallery_shortcode ) ) || apply_filters( 'rtmedia_load_add_album_modal', false ) ) {
		?>
		<div class="mfp-hide rtmedia-popup" id="rtmedia-create-album-modal">
			<div id="rtm-modal-container">
				<?php do_action( 'rtmedia_before_create_album_modal' ); ?>
				<h2 class="rtm-modal-title"><?php esc_html_e( 'Create an Album', 'buddypress-media' ); ?></h2>
				<p>
					<label class="rtm-modal-grid-title-column" for="rtmedia_album_name"><?php esc_html_e( 'Album Title : ', 'buddypress-media' ); ?></label>
					<input type="text" id="rtmedia_album_name" value="" class="rtm-input-medium" />
				</p>
				<p>
					<label class="rtm-modal-grid-title-column" for="rtmedia_album_description"><?php esc_html_e( 'Album Description : ', 'buddypress-media' ); ?></label>
					<textarea type="text" id="rtmedia_album_description" value="" class="rtm-input-medium"></textarea>
				</p>
				<?php do_action( 'rtmedia_add_album_privacy' ); ?>
				<input type="hidden" id="rtmedia_album_context" value="<?php echo esc_attr( $rtmedia_query->query['context'] ); ?>">
				<input type="hidden" id="rtmedia_album_context_id" value="<?php echo esc_attr( $rtmedia_query->query['context_id'] ); ?>">
				<?php wp_nonce_field( 'rtmedia_create_album_nonce', 'rtmedia_create_album_nonce' ); ?>
				<p>
					<button type="button" id="rtmedia_create_new_album"><?php esc_html_e( 'Create Album', 'buddypress-media' ); ?></button>
				</p>
				<?php do_action( 'rtmedia_after_create_album_modal' ); ?>
			</div>
		</div>
		<?php
	}

}

add_action( 'rtmedia_before_media_gallery', 'rtmedia_create_album_modal' );
add_action( 'rtmedia_before_album_gallery', 'rtmedia_create_album_modal' );

/**
 * Rendering merge album markup
 *
 * @global      RTMediaQuery    $rtmedia_query
 */
function rtmedia_merge_album_modal() {

	if ( ! is_rtmedia_album() || ! is_user_logged_in() ) {
		return;
	}

	if ( ! is_rtmedia_album_enable() ) {
		return;
	}

	global $rtmedia_query;

	if ( is_rtmedia_group_album() ) {
		$album_list = rtmedia_group_album_list();
	} else {
		$album_list = rtmedia_user_album_list();
	}

	if ( $album_list && ! empty( $rtmedia_query->media_query['album_id'] ) ) {
		?>
		<div class="rtmedia-merge-container rtmedia-popup mfp-hide" id="rtmedia-merge">
			<div id="rtm-modal-container">
				<h2 class="rtm-modal-title"><?php esc_html_e( 'Merge Album', 'buddypress-media' ); ?></h2>
				<form method="post" class="album-merge-form" action="merge/">
					<p>
						<span><?php esc_html_e( 'Select Album to merge with : ', 'buddypress-media' ); ?></span>
						<?php echo '<select name="album" class="rtmedia-merge-user-album-list">' . $album_list . '</select>';// @codingStandardsIgnoreLine ?>
					</p>
					<?php wp_nonce_field( 'rtmedia_merge_album_' . $rtmedia_query->media_query['album_id'], 'rtmedia_merge_album_nonce' ); ?>
					<input type="submit" class="rtmedia-merge-selected" name="merge-album" value="<?php esc_html_e( 'Merge Album', 'buddypress-media' ); ?>"/>
				</form>
			</div>
		</div>
		<?php
	}

}

add_action( 'rtmedia_before_media_gallery', 'rtmedia_merge_album_modal' );
add_action( 'rtmedia_before_album_gallery', 'rtmedia_merge_album_modal' );

/**
 * Rendering checkboxes to select media
 *
 * @global      RTMediaQuery    $rtmedia_query
 * @global      array           $rtmedia_backbone
 */
function rtmedia_item_select() {

	global $rtmedia_query, $rtmedia_backbone;

	if ( $rtmedia_backbone['backbone'] ) {
		if ( isset( $rtmedia_backbone['is_album'] ) && $rtmedia_backbone['is_album'] && isset( $rtmedia_backbone['is_edit_allowed'] ) && $rtmedia_backbone['is_edit_allowed'] ) {
			echo '<span class="rtm-checkbox-wrap"><input type="checkbox" name="move[]" class="rtmedia-item-selector" value="<%= id %>" /></span>';
		}
	} else {
		if ( is_rtmedia_album() && isset( $rtmedia_query->media_query ) && 'edit' === $rtmedia_query->action_query->action ) {
			if ( isset( $rtmedia_query->media_query['media_author'] ) && get_current_user_id() === intval( $rtmedia_query->media_query['media_author'] ) ) {
				echo '<span class="rtm-checkbox-wrap"><input type="checkbox" class="rtmedia-item-selector" name="selected[]" value="' . esc_attr( rtmedia_id() ) . '" /></span>';
			}
		}
	}
}

add_action( 'rtmedia_before_item', 'rtmedia_item_select' );

/**
 * Album merge action
 *
 * @param       array       $actions
 *
 * @return      array
 */
function rtmedia_album_merge_action( $actions ) {

	$actions['merge'] = esc_html__( 'Merge', 'buddypress-media' );

	return $actions;

}

add_action( 'rtmedia_query_actions', 'rtmedia_album_merge_action' );

/**
 * Add upload button
 */
function add_upload_button() {
	if ( function_exists( 'bp_is_blog_page' ) && ! bp_is_blog_page() ) {
		/**
		 * Add filter to transfer "Upload" string,
		 * issue: http://git.rtcamp.com/rtmedia/rtMedia/issues/133
		 */
		$upload_string = apply_filters( 'rtmedia_upload_button_string', __( 'Upload', 'buddypress-media' ) );

		if ( function_exists( 'bp_is_user' ) && bp_is_user() && function_exists( 'bp_displayed_user_id' ) && bp_displayed_user_id() === get_current_user_id() ) {
			echo '<span class="primary rtmedia-upload-media-link" id="rtm_show_upload_ui" title="' . apply_filters( 'rtm_gallery_upload_title_label', __( 'Upload Media', 'buddypress-media' ) ) . '"><i class="dashicons dashicons-upload rtmicon"></i>' . apply_filters( 'rtm_gallery_upload_label', __( 'Upload', 'buddypress-media' ) ) . '</span>';
		} else {
			if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {
				if ( can_user_upload_in_group() ) {
					echo '<span class="rtmedia-upload-media-link primary" id="rtm_show_upload_ui" title="' . apply_filters( 'rtm_gallery_upload_title_label', __( 'Upload Media', 'buddypress-media' ) ) . '"><i class="dashicons dashicons-upload rtmicon"></i>' . apply_filters( 'rtm_gallery_upload_label', __( 'Upload', 'buddypress-media' ) ) . '</span>';
				}
			}
		}
	}
}

add_action( 'rtmedia_media_gallery_actions', 'add_upload_button', 99 );
add_action( 'rtmedia_album_gallery_actions', 'add_upload_button', 99 );



/**
 * Add music cover art
 *
 * @param       array       $file_object
 * @param       object      $upload_obj
 */
function add_music_cover_art( $file_object, $upload_obj ) {

	$media_obj = new RTMediaMedia();
	$media     = $media_obj->model->get( array(
		'id' => $upload_obj->media_ids[0],
	) );

}

// add_action("rtemdia_after_file_upload_before_activity","add_music_cover_art" ,20 ,2);

/**
 * rtmedia link
 *
 * @global      RTMedia     $rtmedia
 */
function rtmedia_link_in_footer() {

	global $rtmedia;

	$option = $rtmedia->options;
	$link   = ( isset( $option['rtmedia_add_linkback'] ) ) ? $option['rtmedia_add_linkback'] : false;

	if ( $link ) {
		$aff_id = ( '' != $option['rtmedia_affiliate_id'] ) ? '&ref=' . $option['rtmedia_affiliate_id'] : '';
		$href   = 'https://rtmedia.io/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media' . $aff_id;
		?>
		<div class='rtmedia-footer-link'>
			<?php esc_html_e( 'Empowering your community with ', 'buddypress-media' ); ?>
			<a href='<?php echo esc_url( $href ) ?>' title='<?php esc_attr_e( 'The only complete media solution for WordPress, BuddyPress and bbPress', 'buddypress-media' ); ?> '>rtMedia</a>
		</div>
		<?php
	}
}

add_action( 'wp_footer', 'rtmedia_link_in_footer' );

/**
 * Add content before the media in single media page
 *
 * @global      bool    $rt_ajax_request
 */
function rtmedia_content_before_media() {

	global $rt_ajax_request;

	if ( $rt_ajax_request ) {
		?>
		<span class="rtm-mfp-close mfp-close dashicons dashicons-no-alt" title="<?php esc_attr_e( 'Close (Esc)', 'buddypress-media' ); ?>"></span>
		<?php
	}

}

add_action( 'rtmedia_before_media', 'rtmedia_content_before_media', 10 );

/**
 * Rendering custom CSS
 *
 * @global      RTMedia     $rtmedia
 */
function rtmedia_custom_css() {

	global $rtmedia;

	$options = $rtmedia->options;

	if ( ! empty( $options['styles_custom'] ) ) {
		echo "<style type='text/css'> " . stripslashes( wp_filter_nohtml_kses( $options['styles_custom'] ) ) . ' </style>'; // @codingStandardsIgnoreLine
	}

}

add_action( 'wp_head', 'rtmedia_custom_css' );

/**
 * Update the group media privacy according to the group privacy settings when group settings are changed
 *
 * @global      wpdb    $wpdb
 *
 * @param       int     $group_id
 */
function update_group_media_privacy( $group_id ) {

	if ( ! empty( $group_id ) && function_exists( 'groups_get_group' ) ) {
		//get the buddybress group
		$group = groups_get_group( array(
			'group_id' => $group_id,
		) );

		if ( isset( $group->status ) ) {
			global $wpdb;

			$model = new RTMediaModel();

			if ( 'public' !== $group->status ) {
				// when group settings are updated and is private/hidden, set media privacy to 20
				$update_sql = $wpdb->prepare( "UPDATE {$model->table_name} SET privacy = '20' where context='group' AND context_id=%d AND privacy <> 80 ", $group_id ); // @codingStandardsIgnoreLine
			} else {
				// when group settings are updated and is private/hidden, set media privacy to 0
				$update_sql = $wpdb->prepare( "UPDATE {$model->table_name} SET privacy = '0' where context='group' AND context_id=%d AND privacy <> 80 ", $group_id ); // @codingStandardsIgnoreLine
			}

			//update the medias
			$wpdb->query( $update_sql ); // @codingStandardsIgnoreLine
		}
	}

}

add_action( 'groups_settings_updated', 'update_group_media_privacy', 99, 1 );

/**
 * Function for no-popup class for rtmedia media gallery
 *
 * @param       string      $class
 *
 * @return      string
 */
function rtmedia_add_no_popup_class( $class = '' ) {

	return $class .= ' no-popup';

}

/**
 * This function is used in RTMediaQuery.php file for show title filter
 *
 * @param       bool    $flag
 *
 * @return      bool
 */
function rtmedia_gallery_do_not_show_media_title( $flag ) {

	return false;

}

/**
 * Remove all the shortcode related hooks that we had added in RTMediaQuery.php file after gallery is loaded
 */
function rtmedia_remove_media_query_hooks_after_gallery() {

	remove_filter( 'rtmedia_gallery_list_item_a_class', 'rtmedia_add_no_popup_class', 10, 1 );
	remove_filter( 'rtmedia_media_gallery_show_media_title', 'rtmedia_gallery_do_not_show_media_title', 10, 1 );

}

add_action( 'rtmedia_after_media_gallery', 'rtmedia_remove_media_query_hooks_after_gallery' );

/**
 * Sanitize media file name before uploading
 *
 * @param       string      $filename
 *
 * @return      string
 */
function sanitize_filename_before_upload( $filename ) {

	$info          	 = pathinfo( $filename );
	$ext             = empty( $info['extension'] ) ? '' : '.' . $info['extension'];
	$name            = basename( $filename, $ext );
	$final_file_name = $name;
	$special_chars   = array( '?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', chr( 0 ) );
	$special_chars   = apply_filters( 'sanitize_file_name_chars', $special_chars, $final_file_name );
	$string          = str_replace( $special_chars, '-', $final_file_name );
	$string          = preg_replace( '/\+/', '', $string );

	return remove_accents( $string ) . $ext;

}

/**
 * Removing special characters and replacing accent characters with ASCII characters in filename before upload to server
 */
function rtmedia_upload_sanitize_filename_before_upload() {

	add_action( 'sanitize_file_name', 'sanitize_filename_before_upload', 10, 1 );

}

add_action( 'rtmedia_upload_set_post_object', 'rtmedia_upload_sanitize_filename_before_upload', 10 );

/**
 * Admin pages content
 *
 * @param       string      $page
 */
function rtmedia_admin_pages_content( $page ) {

	if ( 'rtmedia-hire-us' === $page ) {
		?>
		<div class="rtm-hire-us-container rtm-page-container">
			<h3 class="rtm-setting-title rtm-show"><?php esc_html_e( 'You can consider rtMedia Team for following :', 'buddypress-media' ); ?></h3>
			<ol class="rtm-hire-points">
				<li><?php esc_html_e( 'rtMedia Customization ( in Upgrade Safe manner )', 'buddypress-media' ); ?></li>
				<li><?php esc_html_e( 'WordPress/BuddyPress Theme Design and Development', 'buddypress-media' ); ?></li>
				<li><?php esc_html_e( 'WordPress/BuddyPress Plugin Development', 'buddypress-media' ); ?></li>
			</ol>
			<div class="clearfix">
				<a href="https://rtmedia.io/contact" class="rtm-button rtm-success" target="_blank"><?php esc_html_e( 'Contact Us', 'buddypress-media' ); ?></a>
			</div>
		</div>
		<?php
	}

}

add_action( 'rtmedia_admin_page_insert', 'rtmedia_admin_pages_content', 99, 1 );

/**
 * Adds delete nonce for all template file before tempalte load
 */
function rtmedia_add_media_delete_nonce() {

	wp_nonce_field( 'rtmedia_' . get_current_user_id(), 'rtmedia_media_delete_nonce' );

}

add_action( 'rtmedia_before_template_load', 'rtmedia_add_media_delete_nonce' );

/**
 * 'rtmedia_before_template_load' will not fire for gallery shortcode
 * To add delete nonce in gallery shortcode use rtmedia_pre_template hook
 *
 * Adds delete nonce for gallery shortcode
 *
 * @global      RTMediaQuery    $rtmedia_query
 */
function rtmedia_add_media_delete_nonce_shortcode() {

	global $rtmedia_query;

	if ( isset( $rtmedia_query->is_gallery_shortcode ) && true === $rtmedia_query->is_gallery_shortcode ) {
		wp_nonce_field( 'rtmedia_' . get_current_user_id(), 'rtmedia_media_delete_nonce' );
	}

}
add_action( 'rtmedia_pre_template', 'rtmedia_add_media_delete_nonce_shortcode' );

/**
 * add function to display pagination on single media page with add_filter
 * By: Yahil
 */

if ( ! function_exists( 'rtmedia_single_media_pagination' ) ) {
	function rtmedia_single_media_pagination() {
		$disable = apply_filters( 'rtmedia_single_media_pagination', false );
		if ( true === $disable ) {
			return;
		}
		if ( rtmedia_id() ) {
			$model = new RTMediaModel();

			$media = $model->get_media( array(
				'id'	=> rtmedia_id(),
			), 0, 1 );

			if ( 'profile' == $media[0]->context ) {
				$media = $model->get_media( array(
					'media_author'	=> $media[0]->media_author,
					'context'		=> $media[0]->context,
				) );
			} else if ( 'group' == $media[0]->context ) {
				$media = $model->get_media( array(
					'media_author'	=> $media[0]->media_author,
					'context'		=> $media[0]->context,
					'context_id'	=> $media[0]->context_id,
				) );
			}

			for ( $i = 0; $i < count( $media ); $i++ ) {
				if ( rtmedia_id() == $media[ $i ]->id ) {
					if ( 0 != $i ) {
						$previous = $media[ $i - 1 ]->id;
					}
					if ( count( $media ) != $i + 1 ) {
						$next = $media[ $i + 1 ]->id;
					}
					break;
				}
			}
		}

		$html = '';

		$pagination_label = array(
			'previous_title'	=> __( 'Previous', 'buddypress-media' ),
			'previous_label'	=> __( 'Previous', 'buddypress-media' ),
			'next_title'		=> __( 'Next', 'buddypress-media' ),
			'next_label'		=> __( 'Next', 'buddypress-media' ),
		);

		$pagination_label = apply_filters( 'rtmedia_media_pagination_label', $pagination_label );

		if ( isset( $previous ) && $previous ) {
			$html .= '<div class="previous-pagination"><a href="' . esc_url( get_rtmedia_permalink( $previous ) ) . '" title="' . esc_html( $pagination_label['previous_title'] ) . '">' . esc_html( $pagination_label['previous_label'] ) . '</a></div>';
		}
		if ( isset( $next ) && $next ) {
			$html .= '<div class="next-pagination"><a href="' . esc_url( get_rtmedia_permalink( $next ) ) . '" title="' . esc_html( $pagination_label['next_title'] ) . '">' . esc_html( $pagination_label['next_label'] ) . '</a></div>';
		}
		echo $html; // @codingStandardsIgnoreLine
	}
}

/**
 * @param $album_id
 *
 * @return array
 */
function rtm_get_album_media_count( $album_id ) {
	global $rtmedia_query;

	$args = array();
	if ( isset( $album_id ) && $album_id ) {
		$args['album_id'] = $album_id;
	}
	if ( isset( $rtmedia_query->query['context'] ) && $rtmedia_query->query['context'] ) {
		$args['context'] = $rtmedia_query->query['context'];
	}
	if ( isset( $rtmedia_query->query['context_id'] ) && $rtmedia_query->query['context_id'] ) {
		$args['context_id'] = $rtmedia_query->query['context_id'];
	}

	$rtmedia_model = new RTMediaModel();
	if ( $args ) {
		$count = $rtmedia_model->get( $args, false, false, 'media_id desc', true );
	}
	return $count;
}

/**
 * HTML markup for displaying Media Count of album in album list gallery
 */
function rtm_album_media_count() {

	$rtmedia_album_count_status = array(
		'status'		=> true,
		'before_string'	=> '',
		'after_string'	=> '',
	);

	/**
	 * rtmedia_string_album_count Filter to update album count status, add string before/after count.
	 *
	 * @since 4.8
	 *
	 * @param array $rtmedia_album_count_status status, before_string, after_string
	 *
	 */
	$rtmedia_album_count_status = apply_filters( 'rtmedia_string_album_count', $rtmedia_album_count_status );

	if ( isset( $rtmedia_album_count_status ) && $rtmedia_album_count_status['status'] ) {
		?>
		<div class="rtmedia-album-media-count" title="<?php echo rtmedia_album_mediacounter() . RTMEDIA_MEDIA_LABEL; ?>">
			<?php echo esc_html( $rtmedia_album_count_status['before_string'] ) . rtmedia_album_mediacounter() . esc_html( $rtmedia_album_count_status['after_string'] ) ?></div>
		<?php
	}
	?>
	<?php
}

add_action( 'rtmedia_after_album_gallery_item', 'rtm_album_media_count' );

/**
 * Get the information ( status, expiry date ) of all the installed addons and store in site option
 */
function rt_check_addon_status() {
	$addons = apply_filters( 'rtmedia_license_tabs', array() );

	if ( empty( $addons ) ) {
		return;
	}

	foreach ( $addons as $addon ) {

		if ( ! empty( $addon['args']['addon_id'] ) ) {

			$addon_id = $addon['args']['addon_id'];
			// If license key is not present, then remove the status from config
			// This forces the `Deactivate License` to `Activate License`.
			if ( empty( $addon['args']['license_key'] ) ) {
				delete_option( 'edd_' . $addon_id . '_license_status' );
			}

			$addon_data = get_option( 'edd_' . $addon_id . '_active' );

			// Remove the license from the addon if black license/input is provided
			// This allows user to remove the license from the addon.
			if ( ! empty( $addon_data ) && is_object( $addon_data ) && empty( $addon['args']['license_key'] ) ) {
				if ( isset( $addon_data->success ) && isset( $addon_data->license ) ) {
					if ( ( isset( $_POST[ 'edd_' . $addon_id . '_license_key' ] ) && '' == $_POST[ 'edd_' . $addon_id . '_license_key' ] ) || '' == $addon_data->success || 'invalid' == $addon_data->license ) {
						delete_option( 'edd_' . $addon_id . '_license_status' );
						delete_option( 'edd_' . $addon_id . '_active' );

					}
				}
			}
		}

		if ( ! empty( $addon['args']['license_key'] ) && ! empty( $addon['name'] ) && ! empty( $addon['args']['addon_id'] ) ) {

			$license = $addon['args']['license_key'];

			$addon_name = $addon['name'];

			$addon_active = get_option( 'edd_' . $addon_id . '_active' );

			/**
			 * Perform action before addon activation or license update
			 *
			 * @since 4.2
			 *
			 * @param array 			$addon 			Array containing the license_key, addon_id
			 *                      	 	 			and addon name
			 * @param object|boolean 	$addon_active 	Detailed license data of the addon, or boolean
			 *                                    	  	false if data is not present
			 */
			do_action( 'rtmedia_before_addon_activate', $addon, $addon_active );

			if ( isset( $addon_active->expires ) && 'lifetime' != $addon_active->expires ) {
				$now        = current_time( 'timestamp' );
				$expiration = strtotime( $addon_active->expires, current_time( 'timestamp' ) );

				// For regularly check for license key is expired from store or not
				// Check if last verification attempt is expired or not
				// Will return false if it is expired
				// It will check for every 6 hours
				if ( is_multisite() ) {
					$dont_check_verification = get_site_transient( 'check_rtmedia_license_verifiction_' . $addon_id );
				} else {
					$dont_check_verification = get_transient( 'check_rtmedia_license_verifiction_' . $addon_id );
				}

				if ( $now > $expiration && ( false === $dont_check_verification ) ) {

					// Get license key  information from the store
					$license_data = rtmedia_activate_addon_license( $addon );
					if ( $license_data ) {
						// Store the data in database
						update_option( 'edd_' . $addon_id . '_active', $license_data );
					}

					// Once license status is checked from the store, then set the transient to check again
					if ( is_multisite() ) {
						set_site_transient( 'check_rtmedia_license_verifiction_' . $addon_id, $license_data, 6 * HOUR_IN_SECONDS );
					} else {
						set_transient( 'check_rtmedia_license_verifiction_' . $addon_id, $license_data, 6 * HOUR_IN_SECONDS );
					}
				}
			}

			// Listen for activate button to be clicked

			// Also check if information about the addon in already fetched from the store
			// If it's already fetched, then don't send the request again for the information

			if ( ! empty( $addon_active ) && ! isset( $_POST[ 'edd_' . $addon_id . '_license_activate' ] ) ) {
				continue;
			}

			// Get license key  information from the store
			$license_data = rtmedia_activate_addon_license( $addon );

			if ( $license_data ) {
				// Store the data in database
				update_option( 'edd_' . $addon_id . '_active', $license_data );
			}
		}// End if().
	}// End foreach().
}

add_action( 'admin_init', 'rt_check_addon_status' );

/**
 * Display admin notices when license is saved
 */
function rtmedia_addons_admin_notice() {

	$screen = get_current_screen();

	if ( 'rtmedia_page_rtmedia-license' === $screen->id ) {

		if ( isset( $_POST ) && count( $_POST ) > 0 ) { ?>

			<div class="notice notice-success is-dismissible">
				<p><?php _e( 'Settings has been saved successfully.', 'buddypress-media' ); ?></p>
			</div>

			<div class="notice notice-error is-dismissible">
				<p><?php _e( 'Refresh the page in case if license data is not showing correct.', 'buddypress-media' ); ?></p>
			</div>
	<?php
		}
	}
}
add_action( 'admin_notices', 'rtmedia_addons_admin_notice' );

/**
 * Function to add buddypress language conversion to Media activities.
 * It allow language conversion for all activity
 * type rtmedia_update".
 */
function rtmedia_activity_register_activity_actions_callback() {
	$bp = buddypress();
	bp_activity_set_action(
		$bp->activity->id,
		'rtmedia_update',
		__( 'Posted a status update', 'buddypress-media' ),
		'bp_activity_format_activity_action_activity_update',
		__( 'rtMedia Updates', 'buddypress-media' ),
		array( 'activity', 'group', 'member', 'member_groups' )
	);
}
add_action( 'bp_activity_register_activity_actions', 'rtmedia_activity_register_activity_actions_callback' );


/**
 * Search Media mockup
 * @param       array       $attr
 *
 * @since  4.4
 */
function add_search_filter( $attr = null ) {

	global $rtmedia, $rtmedia_query;

	// Get media type.
	$media_type = get_query_var( 'media' );

	// Prevent search box from these tabs.
	$notallowed_types = array(
		'likes'    => true,
		'other'    => true,
		'favlist'  => true,
		'playlist' => true,
	);
	if ( function_exists( 'rtmedia_media_search_enabled' ) && rtmedia_media_search_enabled() ) {

		// If found the prevented tab, then stop.
		if ( ! empty( $media_type ) && isset( $notallowed_types[ $media_type ] ) ) {
			return;
		}

		// Prevent showing search box for not allowed media types.
		if ( isset( $rtmedia_query->query['media_type'] ) && isset( $notallowed_types[ $rtmedia_query->query['media_type'] ] ) ) {
			return;
		}

		// Do not show search box if playlist view is enabled.
		if ( ! empty( $rtmedia->options['general_enable_music_playlist_view'] ) && 1 == $rtmedia->options['general_enable_music_playlist_view'] && 'music' === $media_type ) {
			return;
		}

		// Do not show search box if table view is enabled for documents.
		if ( ! empty( $rtmedia->options['general_enable_document_other_table_view'] ) && 1 == $rtmedia->options['general_enable_document_other_table_view'] && 'document' === $media_type ) {
			return;
		}

		$search_value = ( isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '' );

		$html  = "<form method='post' id='media_search_form' class='media_search'>";
		$html .= "<input type='text' id='media_search_input' value='" . esc_attr( $search_value ) . "' class='media_search_input' name='media_search' value='' placeholder='Search Media'>";
		$html .= "<span id='media_fatch_loader'></span>";

		$search_by = '';
		/**
		 * Filters the search by parameter for searching media with specific type.
		 *
		 * @param string $search_by Default is blank.
		 */
		$search_by = apply_filters( 'rtmedia_media_search_by', $search_by );

		if (  isset( $search_by ) && $search_by ) {
			$html .= "<select id='search_by' class='search_by'>";

			if ( ! rtm_check_member_type() || strpos( $_SERVER['REQUEST_URI'], 'members' ) || ( isset( $attr['media_author'] ) && $attr['media_author'] ) ) {
				unset( $search_by['member_type'] );
			}

			if ( strpos( $_SERVER['REQUEST_URI'], 'members' ) ) {
				unset( $search_by['author'] );
			}

			if ( function_exists( 'is_plugin_active' ) && ! is_plugin_active( 'rtmedia-custom-attributes/index.php' ) ) {
				unset( $search_by['attribute'] );
			}

			if ( strpos( $_SERVER['REQUEST_URI'], 'attribute' ) ) {
				unset( $search_by['attribute'] );
			}

			if ( isset( $rtmedia_query->media_query['media_type'] ) && ! is_array( $rtmedia_query->media_query['media_type'] ) ) {
				unset( $search_by['media_type'] );
			}

			if ( isset( $attr['media_author'] ) && $attr['media_author'] ) {
				unset( $search_by['author'] );
			}
			foreach ( $search_by as $key => $value ) {
				$selected = ( isset( $_REQUEST['search_by'] ) && $_REQUEST['search_by'] == $key ? 'selected' : '' );
				if ( $search_by[ $key ] ) {
					$key = esc_attr( $key );
					$search_keyword = str_replace( '_', ' ', $key );

					$html .= "<option value='$key' $selected > " . esc_html__( $search_keyword, 'buddypress-media' ) . "</option>";
				}
			}

			$html .= '</select>';
		}

		$html .= "<span id='media_search_remove' class='media_search_remove search_option'><i class='dashicons dashicons-no rtmicon'></i></span>";
		$html .= "<button type='submit' id='media_search' class='search_option'><i class='dashicons dashicons-search rtmicon'></i></button>";
		$html .= '</form>';

		/**
		 * Filters the html of search form.
		 *
		 * @param string $html HTML content of form.
		 */
		echo apply_filters( 'rtmedia_gallery_search', $html );
	}
}

add_action( 'rtmedia_media_gallery_actions', 'add_search_filter', 99 );


/**
 * Re-save premalink settings on plugin activation or plugin updates
 */
function rtmedia_set_permalink() {
	$is_permalink_reset = get_option( 'is_permalink_reset' );
	if ( '' === $is_permalink_reset || 'no' === $is_permalink_reset ) {
		flush_rewrite_rules();
		update_option( 'is_permalink_reset', 'yes' );
	}
}
add_action( 'admin_init', 'rtmedia_set_permalink' );

/**
 *
 * rtmedia_override_canonical Redirect homepage as per parameters passed to query string.
 * This is added for a page set as a "Front page" in which gallery short-code is there,
 * so pagination for gallery short-code can work properly.
 *
 */
function rtmedia_override_canonical( $redirect_url, $requested_url ) {
	if ( is_front_page() && get_query_var( 'pg' ) ) {
		return $requested_url;
	} else {
		return $redirect_url;
	}
}
add_filter( 'redirect_canonical', 'rtmedia_override_canonical', 10, 2 );

/**
 * rtmedia_gallery_shortcode_json_query_vars Set query vars for json response
 *
 * @param  object $wp_query WP query object
 *
 */
function rtmedia_gallery_shortcode_json_query_vars( $wp_query ) {

	global $wp_query;

	$pagename = '';
	if ( isset( $wp_query->query_vars['pagename'] ) ) {
		$pagename = explode( '/', $wp_query->query_vars['pagename'] );
	}
	if ( ! empty( $pagename ) && isset( $_REQUEST['json'] ) && 'true' === $_REQUEST['json'] && isset( $_REQUEST['rtmedia_shortcode'] ) && 'true' === $_REQUEST['rtmedia_shortcode'] ) {
		$pagename = $pagename[0];
		$wp_query->query_vars['pagename'] = '';
		$wp_query->query['pagename'] = $pagename . '/pg';
		$wp_query->query['media'] = '';
		$wp_query->query_vars['media'] = '';
	}

	return $wp_query;

}
add_action( 'pre_get_posts', 'rtmedia_gallery_shortcode_json_query_vars', 99 );

/**
 *
 * Rule for pagination for rtmedia gallery shortcode
 *
 */
function rtmedia_gallery_shortcode_rewrite_rules() {

	// Rule for pages
	add_rewrite_rule( '([^/?]+)/pg/([0-9]*)/?', 'index.php?pg=$matches[2]&pagename=$matches[1]', 'top' );

	// Rule for Day and name
	add_rewrite_rule( '([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/?]+)/pg/([0-9]*)/?', 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&pg=$matches[5]', 'top' );

	// Rule for Month and name
	add_rewrite_rule( '([0-9]{4})/([0-9]{1,2})/([^/?]+)/pg/([0-9]*)/?', 'index.php?year=$matches[1]&monthnum=$matches[2]&name=$matches[3]&pg=$matches[4]', 'top' );

	// Rule for Numeric
	add_rewrite_rule( 'archives/([0-9]+)/pg/([0-9]*)/?', 'index.php?p=$matches[1]&pg=$matches[2]', 'top' );

	// Rule for posts
	add_rewrite_rule( '(.?.+?)/pg/?([0-9]{1,})/?$', 'index.php?pg=$matches[2]&name=$matches[1]', 'bottom' );

	// Rule for homepage
	add_rewrite_rule( 'pg/([0-9]*)/?', 'index.php?page_id=' . get_option( 'page_on_front' ) . '&pg=$matches[1]', 'top' );

}
add_action( 'rtmedia_add_rewrite_rules', 'rtmedia_gallery_shortcode_rewrite_rules' );


/**
 * update the javascript variable media  view in popup or in single page
 */
function rtmedia_after_media_callback() {
	// comment media
	$rtmedia_id = rtmedia_id();
	$comment_media = false;
	if ( ! empty( $rtmedia_id ) ) {
		$comment_media = rtmedia_is_comment_media( $rtmedia_id );
		if ( ! empty( $comment_media ) ) {
			?>
			<script type="text/javascript">
				comment_media = true;
			</script>
			<?php
		} else {
			?>
			<script type="text/javascript">
				comment_media = false;
			</script>
			<?php
		}
	}
}
add_action( 'rtmedia_after_media', 'rtmedia_after_media_callback', 10 );

/**
 * Adds swipe tooltip on mobile
 */
function rtmedia_after_media_swipe_tooltip() {
	if ( wp_is_mobile() ) {
	?>
			<div id="mobile-swipe-overlay">
				<div class="swipe-icon">
					<img src="<?php echo esc_url( RTMEDIA_URL . '/app/assets/img/swipe-tooltip.png' ); ?>" />
				</div>
				<p class="swipe-tootlip"><?php esc_html_e( 'Please swipe for more media.', 'buddypress-media' ); ?></p>
			</div>
	<?php
	}
}
add_action( 'rtmedia_after_media', 'rtmedia_after_media_swipe_tooltip', 10 );

if ( ! function_exists( 'rtmedia_gallery_after_title_callback' ) ) {
	function rtmedia_gallery_after_title_callback() {
		// show description in album gallery page
		global $rtmedia_query;
		// check if it's an album gallery page and album id is not empty
		if (
			isset( $rtmedia_query->query ) && isset( $rtmedia_query->query['media_type'] ) && 'album' == $rtmedia_query->query['media_type']
			&&
			isset( $rtmedia_query->media_query ) && isset( $rtmedia_query->media_query['album_id'] ) && ! empty( $rtmedia_query->media_query['album_id'] )
			&&
			function_exists( 'rtmedia_get_album_description_setting' ) && rtmedia_get_album_description_setting()
		) {
			$description = rtmedia_get_media_description( $rtmedia_query->media_query['album_id'] );
			if ( ! empty( $description ) ) {
				echo '<div class="gallery-description gallery-album-description">' . $description . '</div>';
			}
		}
	}
}
add_action( 'rtmedia_gallery_after_title', 'rtmedia_gallery_after_title_callback', 11, 1 );

/**
 * Add hidden field for media type.
 * This will used by search functionality.
 */
function rtmedia_hidden_field() {
	// Get media type from query string.
	$media_type = get_query_var( 'media' );
	if ( ! empty( $media_type ) ) {
	?>
		<input type="hidden" name="media_type" value="<?php echo esc_attr( $media_type ); ?>" />
	<?php
	}
}
add_action( 'rtmedia_after_media_gallery_title', 'rtmedia_hidden_field' );
