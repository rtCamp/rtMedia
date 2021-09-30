<?php
/**
 * Media filters.
 *
 * @package rtMedia
 */

/**
 * Creating an album
 *
 * @param array $options Options array.
 *
 * @return array|void
 * @global RTMediaQuery $rtmedia_query
 */
function rtmedia_create_album( $options ) {

	if ( ! is_rtmedia_album_enable() ) {
		return;
	}

	if ( ! rtm_is_album_create_allowed() ) {
		return;
	}

	global $rtmedia_query;

	$user_id            = get_current_user_id();
	$display            = false;
	$context_type_array = array( 'profile', 'group' );

	if ( isset( $rtmedia_query->query['context'] ) && in_array( $rtmedia_query->query['context'], $context_type_array, true ) && 0 !== $user_id ) {
		switch ( $rtmedia_query->query['context'] ) {
			case 'profile':
				if ( $rtmedia_query->query['context_id'] === $user_id ) {
					$display = rtm_is_user_allowed_to_create_album();
				}

				break;
			case 'group':
				$group_id = $rtmedia_query->query['context_id'];

				if ( can_user_create_album_in_group( $group_id ) ) {
					$display = true;
				}

				break;
		}
	}

	if ( true === $display ) {
		add_action( 'rtmedia_before_media_gallery', 'rtmedia_create_album_modal' );

		$options[] = "<a href='#rtmedia-create-album-modal' class='rtmedia-reveal-modal rtmedia-modal-link'  title='" . esc_attr__( 'Create New Album', 'buddypress-media' ) . "'><i class='dashicons dashicons-plus-alt'></i>" . esc_html__( 'Add Album', 'buddypress-media' ) . '</a>';
	}

	return $options;

}
add_filter( 'rtmedia_gallery_actions', 'rtmedia_create_album', 12 );

/**
 * Edit album option
 *
 * @param array $options Options array.
 *
 * @return array|void
 * @global RTMediaQuery $rtmedia_query
 */
function rtmedia_album_edit( $options ) {

	if ( ! is_rtmedia_album() || ! is_user_logged_in() ) {
		return;
	}

	if ( ! is_rtmedia_album_enable() ) {
		return;
	}

	global $rtmedia_query;

	if ( isset( $rtmedia_query->media_query ) && isset( $rtmedia_query->media_query['album_id'] ) && ! in_array( intval( $rtmedia_query->media_query['album_id'] ), array_map( 'intval', rtmedia_get_site_option( 'rtmedia-global-albums' ) ), true ) ) {

		if ( rtmedia_is_album_editable() || is_rt_admin() ) {

			$options[] = "<a href='edit/' class='rtmedia-edit' title='" . esc_attr__( 'Edit Album', 'buddypress-media' ) . "' ><i class='dashicons dashicons-edit'></i>" . esc_html__( 'Edit Album', 'buddypress-media' ) . '</a>';
			$options[] = '<form method="post" class="album-delete-form rtmedia-inline" action="delete/">' . wp_nonce_field( 'rtmedia_delete_album_' . $rtmedia_query->media_query['album_id'], 'rtmedia_delete_album_nonce' ) . '<button type="submit" name="album-delete" class="rtmedia-delete-album" title="' . esc_attr__( 'Delete Album', 'buddypress-media' ) . '"><i class="dashicons dashicons-trash"></i>' . esc_html__( 'Delete Album', 'buddypress-media' ) . '</button></form>';

			if ( is_rtmedia_group_album() ) {
				$album_list = rtmedia_group_album_list();
			} else {
				$album_list = rtmedia_user_album_list();
			}

			if ( $album_list ) {
				$options[] = '<a href="#rtmedia-merge" class="rtmedia-reveal-modal rtmedia-modal-link" title="' . esc_attr__( 'Merge Album', 'buddypress-media' ) . '"><i class="dashicons dashicons-randomize"></i>' . esc_html__( 'Merge Album', 'buddypress-media' ) . '</a>';
			}
		}
	}

	return $options;

}
add_filter( 'rtmedia_gallery_actions', 'rtmedia_album_edit', 11 );

/**
 * Add activity type
 *
 * @param array $actions Actions array.
 *
 * @return array
 */
function rtmedia_bp_activity_get_types( $actions ) {

	$actions['rtmedia_update'] = 'rtMedia update';

	return $actions;

}
add_filter( 'bp_activity_get_types', 'rtmedia_bp_activity_get_types', 10, 1 );

/**
 * Checking if BuddyPress enable
 *
 * @param bool $flag Check if Buddypress enable or not.
 *
 * @return bool
 * @global RTMediaQuery $rtmedia_query
 */
function rtm_is_buddypress_enable( $flag ) {

	global $rtmedia_query;

	if ( (
		isset( $rtmedia_query->query ) && isset( $rtmedia_query->query['context'] )
		&& 'group' === $rtmedia_query->query['context'] && is_rtmedia_group_media_enable()
		) || (
			isset( $rtmedia_query->query ) && isset( $rtmedia_query->query['context'] )
			&& 'profile' === $rtmedia_query->query['context'] && is_rtmedia_profile_media_enable()
		) )
	{
		return $flag;
	}

	return false;

}
add_filter( 'rtm_main_template_buddypress_enable', 'rtm_is_buddypress_enable', 10, 1 );

/**
 * We need to use show title filter when there is a request for template from rtMedia.backbone.js
 *
 * @param bool $flag Show title or not.
 *
 * @return bool
 */
function rtmedia_media_gallery_show_title_template_request( $flag ) {
	$media_title = sanitize_text_field( filter_input( INPUT_POST, 'media_title', FILTER_SANITIZE_STRING ) );

	if ( empty( $media_title ) ) {
		$media_title = sanitize_text_field( filter_input( INPUT_GET, 'media_title', FILTER_SANITIZE_STRING ) );
	}

	if ( ! empty( $media_title ) && 'false' === $media_title ) {
		$flag = false;
	}

	return $flag;

}
add_filter( 'rtmedia_media_gallery_show_media_title', 'rtmedia_media_gallery_show_title_template_request', 10, 1 );

/**
 * We need to use lightbox filter when there is a request for template from rtMedia.backbone.js
 *
 * @param string $class Classes for media div.
 *
 * @return string
 */
function rtmedia_media_gallery_lightbox_template_request( $class ) {

	$lightbox = sanitize_text_field( filter_input( INPUT_POST, 'lightbox', FILTER_SANITIZE_STRING ) );

	if ( empty( $lightbox ) ) {
		$lightbox = sanitize_text_field( filter_input( INPUT_GET, 'lightbox', FILTER_SANITIZE_STRING ) );
	}

	if ( ! empty( $lightbox ) && 'false' === $lightbox ) {
		$class .= ' no-popup';
	}

	return $class;

}
add_filter( 'rtmedia_gallery_list_item_a_class', 'rtmedia_media_gallery_lightbox_template_request', 10, 1 );

/**
 * Fix for BuddyPress multilingual plugin on activity pages
 *
 * @param array $params Array parameters.
 *
 * @return array
 */
function rtmedia_modify_activity_upload_url( $params ) {

	// return original params if BuddyPress multilingual plugin is not active.
	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'buddypress-multilingual/sitepress-bp.php' ) ) {

		if ( class_exists( 'BuddyPress' ) ) {

			// change upload url only if it's activity page and if it's group page than it shouldn't group media page.
			if ( bp_is_activity_component() || ( bp_is_groups_component() && ! is_rtmedia_page() ) ) {

				if ( function_exists( 'bp_get_activity_directory_permalink' ) ) {
					$params['url'] = bp_get_activity_directory_permalink() . 'upload/';
				}
			}
		}
	}

	return $params;

}
add_filter( 'rtmedia_modify_upload_params', 'rtmedia_modify_activity_upload_url', 999, 1 );

/**
 * WordPress filter to change browser title if theme has title-tag support
 *
 * @param array $title Title.
 *
 * @return array
 * @global RTMediaQuery $rtmedia_query
 */
function rtm_modify_document_title_parts( $title = array() ) {

	if ( is_rtmedia_page() ) {
		global $rtmedia_query;

		if ( isset( $rtmedia_query->action_query->media_type ) ) {
			( ! class_exists( 'BuddyPress' ) ) ? array_unshift( $title, ucfirst( $rtmedia_query->action_query->media_type ), apply_filters( 'rtmedia_media_tab_name', RTMEDIA_MEDIA_LABEL ) ) : array_unshift( $title, ucfirst( $rtmedia_query->action_query->media_type ) );
		} else {
			( ! class_exists( 'BuddyPress' ) ) ? array_unshift( $title, apply_filters( 'rtmedia_media_tab_name', RTMEDIA_MEDIA_LABEL ) ) : '';
		}
	}

	return $title;

}
add_filter( 'document_title_parts', 'rtm_modify_document_title_parts', 30, 1 );

/**
 * Replace original src with the transcoded media src
 *
 * @param string $html HTML string.
 * @param object $rtmedia_media RTMedia object.
 *
 * @return string
 */
function replace_src_with_transcoded_file_url( $html, $rtmedia_media ) {

	if ( empty( $rtmedia_media->media_id ) ) {
		return $html;
	}

	$media_type    = '';
	$attachment_id = $rtmedia_media->media_id;

	if ( 'video' === $rtmedia_media->media_type ) {
		$media_type = 'mp4';
	} elseif ( 'music' === $rtmedia_media->media_type ) {
		$media_type = 'mp3';
	} else {
		return $html;
	}

	$medias   = get_post_meta( $attachment_id, '_rt_media_transcoded_files', true );
	$file_url = rtt_is_video_exists( $medias, $media_type );

	if ( ! empty( $file_url ) ) {
		// for WordPress backward compatibility.
		if ( function_exists( 'wp_get_upload_dir' ) ) {
			$uploads = wp_get_upload_dir();
		} else {
			$uploads = wp_upload_dir();
		}

		if ( 0 === strpos( $file_url, $uploads['baseurl'] ) ) {
			$final_file_url = $file_url;
		} else {
			$final_file_url = $uploads['baseurl'] . '/' . $file_url;
		}

		$final_file_url = apply_filters( 'transcoded_file_url', $final_file_url, $attachment_id );
	} else {
		$final_file_url = wp_get_attachment_url( $attachment_id );
	}

	$final_file_url = rtmedia_append_timestamp_in_url( $final_file_url );

	return preg_replace( '/src=["]([^"]+)["]/', 'src="' . $final_file_url . '"', $html );

}
add_filter( 'rtmedia_single_content_filter', 'replace_src_with_transcoded_file_url', 100, 2 );

/**
 * Replace aws url of image with the WordPress attachment url in buddypress activity
 *
 * @param string $html HTML string.
 * @param object $rtmedia_media RTMedia object.
 *
 * @return string
 */
function replace_aws_img_urls_from_activity( $html, $rtmedia_media ) {

	if ( empty( $rtmedia_media ) ) {
		return $html;
	}

	/**
	 * Allow users/plugins to prevent replacing of URL from activity
	 *
	 * @var boolean               Boolean false is passed as a parameter.
	 * @var object $rtmedia_media Object of rtmedia containing media_id, media_type etc.
	 */
	if ( apply_filters( 'replace_aws_img_urls_from_activity', false, $rtmedia_media ) ) {
		return $html;
	}

	if ( empty( $rtmedia_media->media_id ) || empty( $rtmedia_media->media_type ) ) {
		return $html;
	}

	$media_type = $rtmedia_media->media_type;

	if ( 'image' === $media_type && ! empty( $rtmedia_media->guid ) ) {
		/**
		 * Fix for rtAmazon S3 addon
		 * When rtAmazon S3 is disabled we need to restore/replace the attachment URLS with the
		 * original WordPress URL structure
		 */
		if ( ! class_exists( 'RTAWSS3_Class' ) && ! class_exists( 'AS3CF_Utils' ) ) {
			// for WordPress backward compatibility.
			if ( function_exists( 'wp_get_upload_dir' ) ) {
				$uploads = wp_get_upload_dir();
			} else {
				$uploads = wp_upload_dir();
			}

			$baseurl = $uploads['baseurl'];

			if ( 0 === strpos( $rtmedia_media->guid, $uploads['baseurl'] ) ) {
				$thumbnail_url = $rtmedia_media->guid;
			} else {

				$rtmedia_folder_name = apply_filters( 'rtmedia_upload_folder_name', 'rtMedia' );

				$thumbnail_url = explode( $rtmedia_folder_name, $rtmedia_media->guid );

				if ( is_array( $thumbnail_url ) && ! empty( $thumbnail_url[1] ) ) {
					$thumbnail_url = $baseurl . '/' . $rtmedia_folder_name . '/' . ltrim( $thumbnail_url[1], '/' );
				} else {
					$thumbnail_url = $rtmedia_media->guid;
				}
			}

			if ( ! empty( $thumbnail_url ) ) {
				$html = preg_replace( '/src=["]([^"]+)["]/', "src=\"$thumbnail_url\"", $html );
			}
		}
	} // End if.
	return $html;
}
add_filter( 'rtmedia_single_content_filter', 'replace_aws_img_urls_from_activity', 100, 2 );

/**
 * Fix for rtAmazon S3 addon
 * When rtAmazon S3 is disabled we need to restore/replace the attachment URLS with the
 * original WordPress URL structure
 *
 * @param string        $content HTML contents of the activity.
 * @param object|string $activity Activity object.
 *
 * @return string
 * @since 1.0.1
 */
function replace_aws_img_urls_from_activities( $content, $activity = '' ) {

	if ( empty( $content ) || empty( $activity ) ) {
		return $content;
	}

	/**
	 * Allow users/plugins to prevent replacing of URL from activty
	 *
	 * @var boolean          Boolean false is passed as a parameter.
	 * @var object $activity Object of activity.
	 */
	if ( apply_filters( 'replace_aws_img_urls_from_activities', false, $activity ) ) {
		return $content;
	}

	$rt_model  = new RTMediaModel();
	$all_media = $rt_model->get( array( 'activity_id' => $activity->id ) );

	$is_img = false;
	$url    = '';
	$is_img = strpos( $content, '<img ' );

	$search = '/<img.+src=["]([^"]+)["]/';
	preg_match_all( $search, $content, $url );

	if ( ! empty( $is_img ) && ! empty( $url ) && ! empty( $url[1] ) ) {
		/**
		 * Iterate through each image URL found in regex
		 */
		foreach ( $url[1] as $key => $url ) {

			if ( ! class_exists( 'RTAWSS3_Class' ) && ! class_exists( 'AS3CF_Utils' ) ) {

				// Get blog_id of activity from rtMedia table.
				$rt_activity_model = new RTMediaActivityModel();
				$rtmedia_activity  = $rt_activity_model->get_without_blog_id( array( 'activity_id' => $activity->id ) );
				// Switch to activity blog to get correct uploads URL.
				if ( ! empty( $rtmedia_activity[0]->blog_id ) ) {
					switch_to_blog( $rtmedia_activity[0]->blog_id );
				}
				// For WordPress backward compatibility.
				if ( function_exists( 'wp_get_upload_dir' ) ) {
					$uploads = wp_get_upload_dir();
				} else {
					$uploads = wp_upload_dir();
				}
				// Restore current blog if it was switched to other blog.
				if ( ! empty( $rtmedia_activity[0]->blog_id ) ) {
					restore_current_blog();
				}

				$baseurl = $uploads['baseurl'];

				$thumbnail_url = '';
				if ( 0 === strpos( $url, $uploads['baseurl'] ) ) {
					$thumbnail_url = $url;
				} else {
					$rtmedia_folder_name = apply_filters( 'rtmedia_upload_folder_name', 'rtMedia' );

					$thumbnail_url = explode( $rtmedia_folder_name, $url );

					if ( is_array( $thumbnail_url ) && ! empty( $thumbnail_url[1] ) ) {
						$thumbnail_url = $baseurl . '/' . $rtmedia_folder_name . '/' . ltrim( $thumbnail_url[1], '/' );
					} else {
						$thumbnail_url = $url;
					}
				}

				if ( ! empty( $thumbnail_url ) ) {
					$content = str_replace( $url, $thumbnail_url, $content );
				}
			} else {
				/**
				 * Sometimes there's no attachment ID for the URL assigned, so we pass MD5 hash of the URL as a attachment ID
				 */
				$attachment_id = md5( $url );
				if ( ! empty( $all_media ) && ! empty( $all_media[0]->media_id ) ) {
					$attachment_id = $all_media[0]->media_id;
				}
				$image_url = apply_filters( 'rtmedia_filtered_photo_url', $url, $attachment_id );
				$content   = str_replace( $url, $image_url, $content );
			} // End if.
		} // End foreach.
	} // End if.
	return $content;
}
add_filter( 'bp_get_activity_content_body', 'replace_aws_img_urls_from_activities', 99, 2 );

/**
 * Gives the WordPress's default attachment URL if the base URL of the attachment is
 * different than the WordPress's default base URL. e.g following URL
 * https://s3.amazonaws.com/bucket-name/wp-content/uploads/2016/09/attachment.jpg
 * will get replaced with
 * http://www.wordpress-base.url/wp-content/uploads/2016/09/1473432502-small-10-1-16_1.jpg
 *
 * @param int    $thumbnail_id It can be attachment URL or attachment ID.
 * @param string $media_type Media type.
 * @param int    $media_id Attachment ID.
 *
 * @return      string      Attachment URL if attachment URL is provided in the argument
 */
function rtt_restore_og_wp_image_url( $thumbnail_id, $media_type, $media_id ) {

	if ( is_numeric( $thumbnail_id ) ) {
		return $thumbnail_id;
	}

	/**
	 * Allow users/plugins to prevent replacing of URL of album cover
	 *
	 * @var boolean            Boolean false is passed as a parameter.
	 * @var string $media_type Type of the media.
	 */
	if ( apply_filters( 'rtt_restore_og_wp_image_url', false, $media_type ) ) {
		return $thumbnail_id;
	}

	/**
	 * Fix for rtAmazon S3 addon
	 * When rtAmazon S3 is disabled we need to restore/replace the attachment URLS with the
	 * original WordPress URL structure
	 */
	if ( ! class_exists( 'RTAWSS3_Class' ) && ! class_exists( 'AS3CF_Utils' ) ) {
		// for WordPress backward compatibility.
		if ( function_exists( 'wp_get_upload_dir' ) ) {
			$uploads = wp_get_upload_dir();
		} else {
			$uploads = wp_upload_dir();
		}

		if ( 0 !== strpos( $thumbnail_id, $uploads['baseurl'] ) ) {
			$baseurl = $uploads['baseurl'];

			$rtmedia_folder_name = apply_filters( 'rtmedia_upload_folder_name', 'rtMedia' );

			$thumbnail_url = explode( $rtmedia_folder_name, $thumbnail_id );
			if ( is_array( $thumbnail_url ) && ! empty( $thumbnail_url[1] ) ) {
				$thumbnail_url = $baseurl . '/' . $rtmedia_folder_name . '/' . ltrim( $thumbnail_url[1], '/' );
			} else {
				$thumbnail_url = $thumbnail_id;
			}
		}

		if ( ! empty( $thumbnail_url ) ) {
			$thumbnail_id = $thumbnail_url;
		}
	}

	/**
	 * Apply filter to get amazon s3 URL
	 */
	$final_file_url = apply_filters( 'transcoded_file_url', $thumbnail_id, $media_id );

	return $final_file_url;

}
add_filter( 'show_custom_album_cover', 'rtt_restore_og_wp_image_url', 100, 3 );

/**
 * Function to edit comment link for media
 *
 * @param string $link Media comment link.
 * @param object $comment comment data.
 *
 * @return string  $link  media comment link
 */
function rt_get_comment_link_callback( $link, $comment ) {

	$rtmedia_media_id = rtmedia_id( $comment->comment_post_ID );

	if ( get_post_type( $comment->comment_post_ID ) === 'attachment' && is_admin() && ! empty( $rtmedia_media_id ) ) {
		$link = esc_url( get_rtmedia_permalink( $rtmedia_media_id ) ) . '#rtmedia_comment_ul';
	}
	return $link;
}
add_filter( 'get_comment_link', 'rt_get_comment_link_callback', 99, 2 );

/**
 * Function to edit attachment for media
 *
 * @param string $permalink attachment permalink.
 * @param array  $post_id return attachment post id.
 *
 * @return string attachment post permalink
 */
function rtmedia_attachment_link_callback( $permalink, $post_id ) {
	$rtmedia_media_id = rtmedia_id( $post_id );

	if ( is_admin() && ! empty( $rtmedia_media_id ) ) {
		$permalink = esc_url( get_rtmedia_permalink( rtmedia_id( $post_id ) ) ) . '#rtmedia_comment_ul';
	}

	return $permalink;
}
add_filter( 'attachment_link', 'rtmedia_attachment_link_callback', 99, 2 );

/**
 * [rtmedia_edit_media_on_database]
 * Update Media details on database while admin edit reported media
 *
 * @param array $data Image Details.
 * @param int   $post_ID Media ID.
 *
 * @return array  $data
 */
function rtmedia_edit_media_on_database( $data, $post_ID ) {

	$post = get_post( $post_ID );

	$postid  = filter_input( INPUT_POST, 'postid', FILTER_VALIDATE_INT );
	$action  = sanitize_text_field( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) );
	$context = sanitize_text_field( filter_input( INPUT_POST, 'context', FILTER_SANITIZE_STRING ) );

	// @todo need to check why 'context' key is not set in POST when user clicks on scale button on edit image.
	if ( ! empty( $postid ) && 'image-editor' === $action && ! empty( $context ) && 'edit-attachment' === $context ) {

		$media           = new RTMediaModel();
		$media_available = $media->get_media(
			array(
				'media_id' => $postid,
			),
			0,
			1
		);

		$media_id = $media_available[0]->id;

		if ( ! empty( $media_available ) ) {
			$rtmedia_filepath_old = rtmedia_image( 'rt_media_activity_image', $media_id, false );

			if ( isset( $rtmedia_filepath_old ) ) {
				$is_valid_url = preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $rtmedia_filepath_old );

				if ( $is_valid_url && function_exists( 'bp_is_active' ) && bp_is_active( 'activity' ) ) {
					$thumbnailinfo = wp_get_attachment_image_src( $post_ID, 'rt_media_activity_image' );
					$activity_id   = rtmedia_activity_id( $media_id );

					if ( $post_ID && ! empty( $activity_id ) ) {
						global $wpdb, $bp;

						if ( ! empty( $bp->activity ) ) {

							$media->model       = new RTMediaModel();
							$related_media_data = $media->model->get( array( 'activity_id' => $activity_id ) );
							$related_media      = array();

							foreach ( $related_media_data as $activity_media ) {
								$related_media[] = $activity_media->id;
							}

							$activity_text = bp_activity_get_meta( $activity_id, 'bp_activity_text' );

							$activity = new RTMediaActivity( $related_media, 0, $activity_text );

							$activity_content_new = $activity->create_activity_html();

							$activity_content = str_replace( $rtmedia_filepath_old, wp_get_attachment_url( $post_ID ), $activity_content_new );

							$wpdb->update( $bp->activity->table_name, array( 'content' => $activity_content ), array( 'id' => $activity_id ) );
						}
					}
				}
			}
		}
	}

	return $data;
}
add_filter( 'wp_update_attachment_metadata', 'rtmedia_edit_media_on_database', 10, 2 );

if ( ! function_exists( 'rtmedia_media_edit_priv_callback' ) ) {

	/**
	 * Disallow media edit for comment media
	 *
	 * @param bool $value If mmedia edit is allowed or not.
	 *
	 * @return bool
	 */
	function rtmedia_media_edit_priv_callback( $value ) {
		// comment media.
		$rtmedia_id    = rtmedia_id();
		$comment_media = false;

		if ( ! empty( $rtmedia_id ) && function_exists( 'rtmedia_is_comment_media' ) && ! empty( $value ) ) {
			$comment_media = rtmedia_is_comment_media( $rtmedia_id );

			if ( ! empty( $comment_media ) ) {
				$value = false;
			}
		}
		return $value;
	}
}
add_filter( 'rtmedia_media_edit_priv', 'rtmedia_media_edit_priv_callback', 10, 1 );

if ( ! function_exists( 'rtmedia_author_actions_callback' ) ) {

	/**
	 * Disallow media author action
	 *
	 * @param string $value Comment value.
	 *
	 * @return bool
	 */
	function rtmedia_author_actions_callback( $value ) {
		// comment media.
		$rtmedia_id    = rtmedia_id();
		$comment_media = false;

		if ( ! empty( $rtmedia_id ) && function_exists( 'rtmedia_is_comment_media' ) && ! empty( $value ) ) {
			$comment_media = rtmedia_is_comment_media( $rtmedia_id );

			if ( ! empty( $comment_media ) ) {
				$value = false;
			}
		}
		return $value;
	}
}
add_filter( 'rtmedia_author_actions', 'rtmedia_author_actions_callback', 10, 1 );

/**
 * MMultiple like on media show.
 *
 * @param int  $like_count Total likes.
 * @param true $user_like_it Current user liked it or not.
 *
 * @return string
 */
function rtmedia_like_html_you_and_more_like_callback( $like_count, $user_like_it ) {
	if ( $like_count > 1 && $user_like_it ) {
		// if login user has like the comment then less from the total count.
		$like_count--;
	}
	return sprintf( '<span class="rtmedia-like-counter">%s</span>', $like_count );
}
add_filter( 'rtmedia_like_html_you_and_more_like', 'rtmedia_like_html_you_and_more_like_callback', 10, 2 );

/**
 * Update where query for media search
 *
 * @param string $where Where condition query string.
 * @param string $table_name Table name.
 * @param string $join Join query string.
 *
 * @return string
 */
function rtmedia_search_fillter_where_query( $where, $table_name, $join ) {

	if ( function_exists( 'rtmedia_media_search_enabled' ) && rtmedia_media_search_enabled() ) {

		$search                = sanitize_text_field( urldecode( wp_unslash( filter_input( INPUT_GET, 'search', FILTER_SANITIZE_STRING ) ) ) );
		$search_by             = sanitize_text_field( wp_unslash( filter_input( INPUT_GET, 'search_by', FILTER_SANITIZE_STRING ) ) );
		$media_type            = sanitize_text_field( wp_unslash( filter_input( INPUT_GET, 'media_type', FILTER_SANITIZE_STRING ) ) );
		$rtmedia_current_album = sanitize_text_field( wp_unslash( filter_input( INPUT_GET, 'rtmedia-current-album', FILTER_SANITIZE_STRING ) ) );

		if ( '' !== $search ) {
			$author_id   = rtm_select_user( $search );
			$member_type = rtm_fetch_user_by_member_type( $search );

			if ( ! empty( $rtmedia_current_album ) ) {
				$where = '';
			}

			$where .= ' AND ';
			if ( ! empty( $search_by ) ) {

				if ( ! empty( $rtmedia_current_album ) ) {
					$where .= " $table_name.album_id = '" . $rtmedia_current_album . "' AND ";
				}

				if ( ! empty( $media_type ) && empty( $rtmedia_current_album ) ) {
					$where .= " $table_name.media_type = '" . $media_type . "' AND ";
				}

				if ( 'title' === $search_by ) {
					$where .= " $table_name.media_title LIKE '%" . $search . "%' ";
				} elseif ( 'description' === $search_by ) {
					$where .= " post_table.post_content LIKE '%" . $search . "%'";

				} elseif ( 'author' === $search_by ) {
					if ( ! empty( $author_id ) ) {
						$where .= " $table_name.media_author IN  (" . $author_id . ') ';
					}
				} elseif ( 'member_type' === $search_by ) {
					if ( ! empty( $member_type ) ) {
						$where .= " $table_name.media_author IN  (" . $member_type . ') ';
					}
				} else {
					$where .= '2=2';
				}
			} else {

				if ( ! empty( $rtmedia_current_album ) ) {
					$where .= " $table_name.album_id = '" . $rtmedia_current_album . "' AND ";
				}

				if ( ! empty( $media_type ) && empty( $rtmedia_current_album ) ) {
					$where .= " $table_name.media_type = '" . $media_type . "' AND ";
				}
				$where .= ' ( ';
				$where .= " $table_name.media_title LIKE '%" . $search . "%' ";
				if ( ! empty( $author_id ) ) {
					$where .= " OR $table_name.media_author IN  (" . $author_id . ') ';
				}
				if ( ! empty( $member_type ) ) {
					$where .= " OR $table_name.media_author IN  (" . $member_type . ') ';
				}
				$where .= " OR post_table.post_content LIKE '%" . $search . "%'";

				if ( empty( $media_type ) ) {
					$where .= " OR $table_name.media_type = '" . $search . "' ";
				}

				$where .= ' ) ';
			} // End if.
		} else {

			// Reset data for album's media.
			if ( '' !== $search && ! empty( $rtmedia_current_album ) ) {
					$where .= " AND $table_name.album_id = '" . $rtmedia_current_album . "' ";
			}

			// Reset data for particular media type.
			if ( ! empty( $media_type ) && empty( $rtmedia_current_album ) ) {
				$where .= " AND $table_name.media_type = '" . $media_type . "' ";
			}
		} // End if.
	} // End if.

	return $where;
}
add_filter( 'rtmedia-model-where-query', 'rtmedia_search_fillter_where_query', 10, 3 );

/**
 * Update join query for media search
 *
 * @param string $join Join query string.
 * @param string $table_name Table name.
 *
 * @return string
 */
function rtmedia_search_fillter_join_query( $join, $table_name ) {

	if ( function_exists( 'rtmedia_media_search_enabled' ) && rtmedia_media_search_enabled() ) {

		global $wpdb;
		$posts_table              = $wpdb->posts;
		$terms_table              = $wpdb->terms;
		$term_relationships_table = $wpdb->term_relationships;
		$term_taxonomy_table      = $wpdb->term_taxonomy;
		$search                   = sanitize_text_field( wp_unslash( filter_input( INPUT_GET, 'search', FILTER_SANITIZE_STRING ) ) );
		$search_by                = sanitize_text_field( wp_unslash( filter_input( INPUT_GET, 'search_by', FILTER_SANITIZE_STRING ) ) );

		if ( '' !== $search ) {
				$join .= "INNER JOIN $posts_table as post_table ON ( post_table.ID = $table_name.media_id AND post_table.post_type = 'attachment')";

			$request_uri = rtm_get_server_var( 'REQUEST_URI', 'FILTER_SANITIZE_URL' );
			$request_url = explode( '/', $request_uri );
			if ( ! empty( $search_by ) && 'attribute' === $search_by && ! in_array( 'attribute', $request_url, true ) ) {
				$join .= " 	INNER JOIN $posts_table ON ( $posts_table.ID = $table_name.media_id AND $posts_table.post_type = 'attachment' )
		                    INNER JOIN $terms_table ON ( $terms_table.slug IN ('" . $search . "') )
		                    INNER JOIN $term_taxonomy_table ON ( $term_taxonomy_table.term_id = $terms_table.term_id )
		                    INNER JOIN $term_relationships_table ON ( $term_relationships_table.term_taxonomy_id = $term_taxonomy_table.term_taxonomy_id AND $term_relationships_table.object_id = $posts_table.ID ) ";
			}
		}
	}
	return $join;
}
add_filter( 'rtmedia-model-join-query', 'rtmedia_search_fillter_join_query', 11, 2 );

/**
 * Update media type for media search
 *
 * @param array $columns Query columns.
 *
 * @return array
 */
function rtmedia_model_query_columns( $columns ) {
	$search    = sanitize_text_field( wp_unslash( filter_input( INPUT_GET, 'search', FILTER_SANITIZE_STRING ) ) );
	$search_by = sanitize_text_field( wp_unslash( filter_input( INPUT_GET, 'search_by', FILTER_SANITIZE_STRING ) ) );

	if ( ! empty( $search ) ) {
		if ( ! empty( $search_by ) && 'media_type' === $search_by ) {
			if ( isset( $columns['media_type']['value'] ) && is_array( $columns['media_type']['value'] ) ) {
				$columns['media_type']['value'] = array( $search );
			}
		}
	}

	return $columns;
}
add_filter( 'rtmedia-model-query-columns', 'rtmedia_model_query_columns', 10, 1 );

/**
 * Add link for @mentions of the username in the comment or the activity section after the media delete or media is trancoder
 *
 * @param string $retval Meta Value.
 * @param int    $activity_id activity id.
 * @param string $meta_key Meta key.
 * @param bool   $single Single value or not.
 *
 * @return mixed
 */
function rtmedia_bp_activity_get_meta_callback( $retval, $activity_id, $meta_key, $single ) {
	$new_retval = $retval;
	if ( 'bp_activity_text' === $meta_key && true === $single && function_exists( 'bp_activity_at_name_filter' ) ) {
		$new_retval = bp_activity_at_name_filter( $new_retval );
	}
	return $new_retval;
}
add_filter( 'bp_activity_get_meta', 'rtmedia_bp_activity_get_meta_callback', 10, 4 );

if ( ! function_exists( 'rtmedia_gallery_shortcode_parameter_pre_filter_callback' ) ) {

	/**
	 * Remove unwanted attr of sorting when rtmedia-sorting addon is not there
	 *
	 * @param array $attr Attributes array.
	 *
	 * @return mixed
	 */
	function rtmedia_gallery_shortcode_parameter_pre_filter_callback( $attr ) {
		$new_attr = $attr;
		if ( ! class_exists( 'RTMediaSorting' ) && isset( $attr['attr'] ) && isset( $attr['attr']['sort_parameters'] ) ) {
			unset( $new_attr['attr']['sort_parameters'] );
		}
		return $new_attr;
	}
}
add_filter( 'rtmedia_gallery_shortcode_parameter_pre_filter', 'rtmedia_gallery_shortcode_parameter_pre_filter_callback', 10, 1 );

/**
 * Add exporters to queue
 *
 * @param array $exporters Exporter queue.
 *
 * @return array
 */
function register_rtmedia_exporter( $exporters ) {
	$exporters['buddypress-media-shortcode-uploads'] = array(
		'exporter_friendly_name' => esc_html__( 'rtMedia Shortcode Uploads', 'buddypress-media' ),
		'callback'               => 'rtmedia_shortcode_upload_exporter',
	);
	$exporters['buddypress-media-activity']          = array(
		'exporter_friendly_name' => esc_html__( 'rtMedia Activities', 'buddypress-media' ),
		'callback'               => 'rtmedia_activity_exporter',
	);
	$exporters['buddypress-media-comment']           = array(
		'exporter_friendly_name' => esc_html__( 'rtMedia Comments', 'buddypress-media' ),
		'callback'               => 'rtmedia_activity_comments_exporter',
	);
	$exporters['buddypress-media-views']             = array(
		'exporter_friendly_name' => esc_html__( 'rtMedia Media Views', 'buddypress-media' ),
		'callback'               => 'rtmedia_media_view_exporter',
	);
	$exporters['buddypress-media-likes']             = array(
		'exporter_friendly_name' => esc_html__( 'rtMedia Media Likes', 'buddypress-media' ),
		'callback'               => 'rtmedia_media_like_exporter',
	);

	return $exporters;
}
add_filter( 'wp_privacy_personal_data_exporters', 'register_rtmedia_exporter', 10 );

/**
 * Add eraser to queue
 *
 * @param array $erasers Exporter queue.
 *
 * @return array
 */
function register_rtmedia_eraser( $erasers ) {

	$erasers['buddypress-media'] = array(
		'eraser_friendly_name' => esc_html__( 'rtMedia Eraser', 'buddypress-media' ),
		'callback'             => 'rtmedia_eraser',
	);

	$erasers['buddypress-media-likes'] = array(
		'eraser_friendly_name' => esc_html__( 'rtMedia Likes Eraser', 'buddypress-media' ),
		'callback'             => 'rtmedia_like_eraser',
	);

	$erasers['buddypress-media-album'] = array(
		'eraser_friendly_name' => esc_html__( 'rtMedia Album Eraser', 'buddypress-media' ),
		'callback'             => 'rtmedia_album_eraser',
	);

	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'register_rtmedia_eraser', 10 );
