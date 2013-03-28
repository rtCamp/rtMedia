<?php

class BPMediaHostWordpress {

	/**
	 * Private variables not to be accessible outside this class' member functions
	 */
			protected $id, //id of the entry
			$name, //Name of the entry
			$description, //Description of the entry
			$url, //URL of the entry
			$type, //Type of the entry (Video, Image or Audio)
			$owner, //Owner of the entry
			$delete_url, //The delete url for the media
			$thumbnail_id, //The thumbnail's id
			$album_id, //The album id to which the media belongs
			$edit_url, //The edit page's url for the media
			$group_id; //The group id of the current media file if it belongs to a group

	/**
	 * Constructs a new BP_Media_Host_Wordpress element
	 *
	 * @param mixed $media_id optional Media ID of the element to be initialized if not defined, returns an empty element.
	 *
	 * @since BuddyPress Media 2.0
	 */

	/**
	 *
	 * @param type $media_id
	 */
	function __construct( $media_id = '' ) {
		if ( $media_id != '' ) {
			$this->init( $media_id );
		}
	}

	/**
	 * Initializes the object with the variables from the post
	 *
	 * @param mixed $media_id Media ID of the element to be initialized. Can be the ID or the object of the Media
	 *
	 * @since BuddyPress Media 2.0
	 */

	/**
	 *
	 * @param type $media_id
	 * @return boolean
	 * @throws Exception
	 */
	function init( $media_id = '' ) {
		if ( is_object( $media_id ) ) {
			$media = $media_id;
		} else {
			$media = &get_post( $media_id );
		}
		if ( empty( $media->ID ) )
			throw new Exception( __( 'Sorry, the requested media does not exist.', BP_MEDIA_TXT_DOMAIN ) );
		if ( ! 'bp_media_album' == $media->post_type || ! empty( $media->post_mime_type ) )
			preg_match_all( '/audio|video|image/i', $media->post_mime_type, $result );
		else
			$result[ 0 ][ 0 ] = 'album';
		if ( isset( $result[ 0 ][ 0 ] ) )
			$this->type = $result[ 0 ][ 0 ];
		else
			return false;


		global $bp;
		$this->id = $media->ID;
		$meta_key = get_post_meta( $this->id, 'bp-media-key', true );

		/**
		 * We use bp-media-key to distinguish if the entry belongs to a group or not
		 * if the value is less than 0 it means it the group id to which the media belongs
		 * and if its greater than 0 then it means its the author id of the uploader
		 * But for use in the class, we use group_id as positive integer even though
		 * we use it as negative value in the bp-media-key meta key
		 */
		$this->group_id = $meta_key < 0 ? -$meta_key : 0;

		$this->description = $media->post_content;
		$this->name = $media->post_title;
		$this->owner = $media->post_author;
		$this->album_id = $media->post_parent;
		$this->mime_type = $media->post_mime_type;


		$this->set_permalinks();
	}

	/**
	 * Handles the uploaded media file and creates attachment post for the file.
	 *
	 * @since BuddyPress Media 2.0
	 */

	/**
	 *
	 * @global type $bp
	 * @global type $wpdb
	 * @global type $bp_media_count
	 * @global type $bp_media
	 * @param type $name
	 * @param type $description
	 * @param type $album_id
	 * @param type $group
	 * @param type $is_multiple
	 * @throws Exception
	 * @uses global var $_FILES
	 */
	function add_media( $name, $description, $album_id = 0, $group = 0, $is_multiple = false, $is_activity = false, $files = false ) {
		do_action( 'bp_media_before_add_media' );

		global $bp, $wpdb, $bp_media;
		include_once(ABSPATH . 'wp-admin/includes/file.php');
		include_once(ABSPATH . 'wp-admin/includes/image.php');

		$post_id = $this->check_and_create_album( $album_id, $group );

		if ( ! $files ) {
			$files = $_FILES[ 'bp_media_file' ];
			$file = wp_handle_upload( $files );
		} else {
			$file = wp_handle_sideload( $files );
		}

		if ( isset( $file[ 'error' ] ) || $file === null ) {
			throw new Exception( __( 'Error Uploading File', BP_MEDIA_TXT_DOMAIN ) );
		}

		$type = $file[ 'type' ];
		if ( in_array( $type, array( 'image/gif', 'image/jpeg', 'image/png' ) ) ) {
			$file = $this->exif( $file );
		}

		$attachment = array( );
		$url = $file[ 'url' ];
		$file = $file[ 'file' ];
		$title = $name;
		$content = $description;
		$attachment = array(
			'post_mime_type' => $type,
			'guid' => $url,
			'post_title' => $title,
			'post_content' => $content,
			'post_parent' => $post_id,
		);
		switch ( $type ) {
			case 'video/mp4' :
			case 'video/quicktime' :
				$type = 'video';
				include_once(trailingslashit( BP_MEDIA_PATH ) . 'lib/getid3/getid3.php');
				try {
					$getID3 = new getID3;
					$vid_info = $getID3->analyze( $file );
				} catch ( Exception $e ) {
					unlink( $file );
					$activity_content = false;
					throw new Exception( __( 'MP4 file you have uploaded is corrupt.', BP_MEDIA_TXT_DOMAIN ) );
				}
				if ( is_array( $vid_info ) ) {
					if ( ! array_key_exists( 'error', $vid_info ) && array_key_exists( 'fileformat', $vid_info ) && array_key_exists( 'video', $vid_info ) && array_key_exists( 'fourcc', $vid_info[ 'video' ] ) ) {
						if ( ! ($vid_info[ 'fileformat' ] == 'mp4' && $vid_info[ 'video' ][ 'fourcc' ] == 'avc1') ) {
							unlink( $file );
							$activity_content = false;
							throw new Exception( __( 'The MP4 file you have uploaded is using an unsupported video codec. Supported video codec is H.264.', BP_MEDIA_TXT_DOMAIN ) );
						}
					} else {
						unlink( $file );
						$activity_content = false;
						throw new Exception( __( 'The MP4 file you have uploaded is using an unsupported video codec. Supported video codec is H.264.', BP_MEDIA_TXT_DOMAIN ) );
					}
				} else {
					unlink( $file );
					$activity_content = false;
					throw new Exception( __( 'The MP4 file you have uploaded is not a video file.', BP_MEDIA_TXT_DOMAIN ) );
				}
				break;
			case 'audio/mpeg' :
				include_once(trailingslashit( BP_MEDIA_PATH ) . 'lib/getid3/getid3.php');
				try {
					$getID3 = new getID3;
					$file_info = $getID3->analyze( $file );
				} catch ( Exception $e ) {
					unlink( $file );
					$activity_content = false;
					throw new Exception( __( 'MP3 file you have uploaded is currupt.', BP_MEDIA_TXT_DOMAIN ) );
				}
				if ( is_array( $file_info ) ) {
					if ( ! array_key_exists( 'error', $file_info ) && array_key_exists( 'fileformat', $file_info ) && array_key_exists( 'audio', $file_info ) && array_key_exists( 'dataformat', $file_info[ 'audio' ] ) ) {
						if ( ! ($file_info[ 'fileformat' ] == 'mp3' && $file_info[ 'audio' ][ 'dataformat' ] == 'mp3') ) {
							unlink( $file );
							$activity_content = false;
							throw new Exception( __( 'The MP3 file you have uploaded is using an unsupported audio format. Supported audio format is MP3.', BP_MEDIA_TXT_DOMAIN ) );
						}
					} else {
						unlink( $file );
						$activity_content = false;
						throw new Exception( __( 'The MP3 file you have uploaded is using an unsupported audio format. Supported audio format is MP3.', BP_MEDIA_TXT_DOMAIN ) );
					}
				} else {
					unlink( $file );
					$activity_content = false;
					throw new Exception( __( 'The MP3 file you have uploaded is not an audio file.', BP_MEDIA_TXT_DOMAIN ) );
				}
				$type = 'audio';
				break;
			case 'image/gif' :
			case 'image/jpeg' :
			case 'image/png' :
				$type = 'image';
				break;
			default :
				unlink( $file );
				$activity_content = false;
				throw new Exception( __( 'Media File you have tried to upload is not supported. Supported media files are .jpg, .png, .gif, .mp3, .mov and .mp4.', BP_MEDIA_TXT_DOMAIN ) );
		}
		echo $attachment_id = wp_insert_attachment( $attachment, $file, $post_id );
		if ( ! is_wp_error( $attachment_id ) ) {
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file ) );
		} else {
			unlink( $file );
			throw new Exception( __( 'Error creating attachment for the media file, please try again', BP_MEDIA_TXT_DOMAIN ) );
		}
		$this->id = $attachment_id;
		$this->name = $name;
		$this->description = $description;
		$this->type = $type;
		$this->owner = get_current_user_id();
		$this->album_id = $post_id;
		$this->group_id = $group;
		$this->set_permalinks();
		if ( $group == 0 ) {
			update_post_meta( $attachment_id, 'bp-media-key', get_current_user_id() );
		} else {
			update_post_meta( $attachment_id, 'bp-media-key', (-$group ) );
		}
		do_action( 'bp_media_after_add_media', $this, $is_multiple, $is_activity, $group );
		return $attachment_id;
	}

	function get_media_thumbnail( $size = 'thumbnail' ) {
		$thumb = '';
		if ( in_array( $this->type, array( 'image', 'video', 'audio' ) ) ) {
			if ( $this->thumbnail_id ) {
				$medium_array = image_downsize( $this->thumbnail_id, $size );
				$thumb_url = $medium_array[ 0 ];
			} else {
				$thumb_url = BP_MEDIA_URL . 'app/assets/img/' . $this->type . '_thumb.png';
			}
			$thumb = apply_filters( 'bp_media_video_thumb', $thumb_url, $this->thumbnail_id, $this->type );
			return $thumb;
		}
		return false;
	}

	/**
	 * Fetches the content of the activity of media upload based on its type
	 *
	 */

	/**
	 *
	 * @global type $bp_media_counter
	 * @global type $bp_media_default_excerpts
	 * @global type $bp_media
	 * @return boolean|string
	 */
	function get_media_activity_content() {
		global $bp_media_counter, $bp_media_default_excerpts, $bp_media;
		$attachment_id = $this->id;
		$activity_content = apply_filters( 'bp_media_single_activity_title', '<div class="bp_media_title"><a href="' . $this->url . '" title="' . __( $this->description, BP_MEDIA_TXT_DOMAIN ) . '">' . __( wp_html_excerpt( $this->name, $bp_media_default_excerpts[ 'activity_entry_title' ] ), BP_MEDIA_TXT_DOMAIN ) . '</a></div>' );
		$activity_content .='<div class="bp_media_content">';
		switch ( $this->type ) {
			case 'video' :
				if ( $this->thumbnail_id ) {
					$image_array = image_downsize( $this->thumbnail_id, 'bp_media_activity_image' );
					$activity_content.=apply_filters( 'bp_media_single_activity_filter', '<video poster="' . $image_array[ 0 ] . '" src="' . wp_get_attachment_url( $attachment_id ) . '" width="320" height="240" type="video/mp4" id="bp_media_video_' . $this->id . '_' . $bp_media_counter . '" controls="controls" preload="none"></video></span>', $this, true );
				} else {
					$activity_content.=apply_filters( 'bp_media_single_activity_filter', '<video src="' . wp_get_attachment_url( $attachment_id ) . '" width="320" height="240" type="video/mp4" id="bp_media_video_' . $this->id . '_' . $bp_media_counter . '" controls="controls" preload="none"></video></span>', $this, true );
				}
				break;
			case 'audio' :
				$activity_content.=apply_filters( 'bp_media_single_activity_filter', '<audio src="' . wp_get_attachment_url( $attachment_id ) . '" width="320" type="audio/mp3" id="bp_media_audio_' . $this->id . '_' . $bp_media_counter . '" controls="controls" preload="none" ></audio></span>', $this, true );
				$type = 'audio';
				break;
			case 'image' :
				$image_array = image_downsize( $attachment_id, 'bp_media_activity_image' );
				$activity_content.=apply_filters( 'bp_media_single_activity_filter', '<a href="' . $this->url . '" title="' . __( $this->name, BP_MEDIA_TXT_DOMAIN ) . '"><img src="' . $image_array[ 0 ] . '" id="bp_media_image_' . $this->id . '_' . $bp_media_counter ++ . '" alt="' . __( $this->name, BP_MEDIA_TXT_DOMAIN ) . '" /></a>', $this, true );
				$type = 'image';
				break;
			default :
				return false;
		}
		$activity_content .= '</div>';
		$activity_content .= apply_filters( 'bp_media_single_activity_description', '<div class="bp_media_description">' . wp_html_excerpt( $this->description, $bp_media_default_excerpts[ 'activity_entry_description' ] ) . '</div>' );
		return $activity_content;
	}

	/**
	 * Returns the single media entry's URL
	 */

	/**
	 *
	 * @return boolean
	 */
	function get_media_activity_url() {
		if ( ! bp_is_activity_component() )
			return false;
		$activity_url = $this->url;
		return $activity_url;
	}

	/**
	 * Returns the media activity's action text
	 */

	/**
	 *
	 * @global type $bp_media
	 * @return boolean
	 */
	function get_media_activity_action() {
		global $bp_media;
		if ( ! bp_is_activity_component() )
			return false;
		$activity_action = sprintf( __( "%s uploaded a media.", BP_MEDIA_TXT_DOMAIN ), bp_core_get_userlink( $this->owner ) );
		return $activity_action;
	}

	/**
	 * Returns the HTML for content of the single entry page of the Media Entry
	 */

	/**
	 *
	 * @global type $bp_media_default_excerpts
	 * @global type $bp_media
	 * @return boolean|string
	 */
	function get_media_single_content() {
		global $bp_media_default_excerpts, $bp_media;

		$default_sizes = $bp_media->media_sizes();
		$content = '';
		if ( $this->group_id > 0 ) {

			$content .= '<div class="bp_media_author">' . __( "Uploaded by ", BP_MEDIA_TXT_DOMAIN ) . bp_core_get_userlink( $this->owner ) . '</div>';
		}
		$content .= '<div class="bp_media_content">';
		switch ( $this->type ) {
			case 'video' :
				if ( $this->thumbnail_id ) {
					$image_array = image_downsize( $this->thumbnail_id, 'bp_media_single_image' );
					$content.=apply_filters( 'bp_media_single_content_filter', '<video poster="' . $image_array[ 0 ] . '" src="' . wp_get_attachment_url( $this->id ) . '" width="' . $default_sizes[ 'single_video' ][ 'width' ] . '" height="' . ($default_sizes[ 'single_video' ][ 'height' ] == 0 ? 'auto' : $default_sizes[ 'single_video' ][ 'height' ]) . '" type="video/mp4" id="bp_media_video_' . $this->id . '" controls="controls" preload="none"></video><script>bp_media_create_element("bp_media_video_' . $this->id . '");</script>', $this );
				} else {
					$content.=apply_filters( 'bp_media_single_content_filter', '<video src="' . wp_get_attachment_url( $this->id ) . '" width="' . $default_sizes[ 'single_video' ][ 'width' ] . '" height="' . ($default_sizes[ 'single_video' ][ 'height' ] == 0 ? 'auto' : $default_sizes[ 'single_video' ][ 'height' ]) . '" type="video/mp4" id="bp_media_video_' . $this->id . '" controls="controls" preload="none"></video><script>bp_media_create_element("bp_media_video_' . $this->id . '");</script>', $this );
				}
				break;
			case 'audio' :
				$content.=apply_filters( 'bp_media_single_content_filter', '<audio src="' . wp_get_attachment_url( $this->id ) . '" width="' . $default_sizes[ 'single_audio' ][ 'width' ] . '" type="audio/mp3" id="bp_media_audio_' . $this->id . '" controls="controls" preload="none" ></audio><script>bp_media_create_element("bp_media_audio_' . $this->id . '");</script>', $this );
				break;
			case 'image' :
				$image_array = image_downsize( $this->id, 'bp_media_single_image' );
				$content.=apply_filters( 'bp_media_single_content_filter', '<img src="' . $image_array[ 0 ] . '" id="bp_media_image_' . $this->id . '" />', $this );
				break;
			default :
				return false;
		}
		$content .= '</div>';
		$content .= '<div class="bp_media_description">' . nl2br( $this->description ) . '</div>';
		return $content;
	}

	public function exif( $file ) {
		$exif = read_exif_data( $file[ 'file' ] );
		$exif_orient = isset( $exif[ 'Orientation' ] ) ? $exif[ 'Orientation' ] : 0;
		$rotateImage = 0;

		if ( 6 == $exif_orient ) {
			$rotateImage = 90;
			$imageOrientation = 1;
		} elseif ( 3 == $exif_orient ) {
			$rotateImage = 180;
			$imageOrientation = 1;
		} elseif ( 8 == $exif_orient ) {
			$rotateImage = 270;
			$imageOrientation = 1;
		}

		if ( $rotateImage > 0 ) {
			if ( class_exists( 'Imagick' ) ) {
				$imagick = new Imagick();
				$imagick->readImage( $file[ 'file' ] );
				$imagick->rotateImage( new ImagickPixel(), $rotateImage );
				$imagick->setImageOrientation( $imageOrientation );
				$imagick->writeImage( $file[ 'file' ] );
				$imagick->clear();
				$imagick->destroy();
			} else {
				$rotateImage = -$rotateImage;

				switch ( $file[ 'type' ] ) {
					case 'image/jpeg':
						$source = imagecreatefromjpeg( $file[ 'file' ] );
						$rotate = imagerotate( $source, $rotateImage, 0 );
						imagejpeg( $rotate, $file[ 'file' ] );
						break;
					case 'image/png':
						$source = imagecreatefrompng( $file[ 'file' ] );
						$rotate = imagerotate( $source, $rotateImage, 0 );
						imagepng( $rotate, $file[ 'file' ] );
						break;
					case 'image/gif':
						$source = imagecreatefromgif( $file[ 'file' ] );
						$rotate = imagerotate( $source, $rotateImage, 0 );
						imagegif( $rotate, $file[ 'file' ] );
						break;
					default:
						break;
				}
			}
		}
		return $file;
	}

	/**
	 * Returns the HTML for title of the single entry page of the Media Entry
	 */

	/**
	 *
	 * @global type $bp_media_default_excerpts
	 * @global type $bp_media
	 * @return string
	 */
	function get_media_single_title() {
		global $bp_media_default_excerpts, $bp_media;
		$content = wp_html_excerpt( $this->name, $bp_media_default_excerpts[ 'single_entry_title' ] );
		return $content;
	}

	/**
	 * Returns the HTML for a media entry to be shown in the listing/gallery page
	 */

	/**
	 *
	 * @global type $bp_media
	 * @return boolean
	 */
	function get_media_gallery_content() {
		$attachment = $this->id;
		switch ( $this->type ) {
			case 'video' :
				if ( $this->thumbnail_id ) {
					$medium_array = image_downsize( $this->thumbnail_id, 'thumbnail' );
					$thumb_url = $medium_array[ 0 ];
				} else {
					$thumb_url = BP_MEDIA_URL . 'app/assets/img/video_thumb.png';
				}
				?>
				<li id="bp-media-item-<?php echo $this->id ?>">
					<a href="<?php echo $this->url ?>" title="<?php _e( $this->description, BP_MEDIA_TXT_DOMAIN ); ?>">
						<img src="<?php echo apply_filters( 'bp_media_video_thumb', $thumb_url, $attachment, $this->type ); ?>" />
					</a>
					<h3 title="<?php echo $this->name; ?>"><a href="<?php echo $this->url ?>" title="<?php _e( $this->description, BP_MEDIA_TXT_DOMAIN ); ?>"><?php echo $this->name; ?></a></h3>
				</li>
				<?php
				break;
			case 'audio' :
				if ( $this->thumbnail_id ) {
					$medium_array = image_downsize( $this->thumbnail_id, 'thumbnail' );
					$thumb_url = $medium_array[ 0 ];
				} else {
					$thumb_url = BP_MEDIA_URL . 'app/assets/img/audio_thumb.png';
				}
				?>
				<li id="bp-media-item-<?php echo $this->id ?>">
					<a href="<?php echo $this->url ?>" title="<?php _e( $this->description, BP_MEDIA_TXT_DOMAIN ); ?>">
						<img src="<?php echo $thumb_url ?>" />
					</a>
					<h3 title="<?php echo $this->name; ?>"><a href="<?php echo $this->url ?>" title="<?php _e( $this->description, BP_MEDIA_TXT_DOMAIN ); ?>"><?php echo $this->name ?></a></h3>
					<div class="bp-media-ajax-preloader"></div>
				</li>
				<?php
				break;
			case 'image' :
				$medium_array = image_downsize( $attachment, 'thumbnail' );
				$medium_path = $medium_array[ 0 ];
				?>
				<li id="bp-media-item-<?php echo $this->id ?>">
					<a href="<?php echo $this->url ?>" title="<?php echo $this->description ?>">
						<img src="<?php echo $medium_path ?>" />
					</a>
					<h3 title="<?php echo $this->name ?>"><a href="<?php echo $this->url ?>" title="<?php _e( $this->description, BP_MEDIA_TXT_DOMAIN ); ?>"><?php echo $this->name ?></a></h3>
					<div class="bp-media-ajax-preloader"></div>
				</li>
				<?php
				break;
			default :
				return false;
		}
	}

	function show_comment_form_wordpress() {
		query_posts( 'attachment_id=' . $this->id );
		while ( have_posts() ): the_post();
			add_action( 'comment_form', 'BPMediaFunction::wp_comment_form_mod' );
			comments_template();
		endwhile;
	}

	/**
	 * Outputs the comments and comment form in the single media entry page
	 */

	/**
	 *
	 * @global type $bp_media
	 * @return boolean
	 */
	function show_comment_form() {
		global $bp_media;
		$activity_id = get_post_meta( $this->id, 'bp_media_child_activity', true );
		if ( ! $activity_id || ! function_exists( 'bp_has_activities' ) )
			return false;
		if ( bp_has_activities( array(
					'display_comments' => 'stream',
					'include' => $activity_id,
					'max' => 1
				) ) ) {
			while ( bp_activities() ) {
				bp_the_activity();
				do_action( 'bp_before_activity_entry' );
				?>
				<div class="activity">
					<ul id="activity-stream" class="activity-list item-list">
						<li class="activity activity_update" id="activity-<?php echo $activity_id; ?>">
							<div class="activity-content">
								<?php do_action( 'bp_activity_entry_content' ); ?>
								<?php if ( is_user_logged_in() ) : ?>
									<div class="activity-meta no-ajax">
										<?php if ( bp_activity_can_comment() ) : ?>
											<a href="<?php bp_get_activity_comment_link(); ?>" class="button acomment-reply bp-primary-action" id="acomment-comment-<?php bp_activity_id(); ?>"><?php printf( __( 'Comment <span>%s</span>', BP_MEDIA_TXT_DOMAIN ), bp_activity_get_comment_count() ); ?></a>
										<?php endif; ?>
										<?php if ( bp_activity_can_favorite() ) : ?>
											<?php if ( ! bp_get_activity_is_favorite() ) : ?>
												<a href="<?php bp_activity_favorite_link(); ?>" class="button fav bp-secondary-action" title="<?php esc_attr_e( 'Mark as Favorite', BP_MEDIA_TXT_DOMAIN ); ?>"><?php _e( 'Favorite', BP_MEDIA_TXT_DOMAIN ) ?></a>
											<?php else : ?>
												<a href="<?php bp_activity_unfavorite_link(); ?>" class="button unfav bp-secondary-action" title="<?php esc_attr_e( 'Remove Favorite', BP_MEDIA_TXT_DOMAIN ); ?>"><?php _e( 'Remove Favorite', BP_MEDIA_TXT_DOMAIN ) ?></a>
											<?php endif; ?>
										<?php endif; ?>
										<?php do_action( 'bp_activity_entry_meta' ); ?>
										<?php if ( bp_activity_user_can_delete() ) bp_activity_delete_link(); ?>
									</div>
								<?php endif; ?>
							</div>
							<?php do_action( 'bp_before_activity_entry_comments' ); ?>
							<?php if ( ( is_user_logged_in() && bp_activity_can_comment() ) || bp_activity_get_comment_count() ) : ?>
								<div class="activity-comments">
									<?php bp_activity_comments(); ?>
									<?php if ( is_user_logged_in() ) : ?>
										<form action="<?php bp_activity_comment_form_action(); ?>" method="post" id="ac-form-<?php bp_activity_id(); ?>" class="ac-form"<?php bp_activity_comment_form_nojs_display(); ?>>
											<div class="ac-reply-avatar"><?php bp_loggedin_user_avatar( 'width=' . BP_AVATAR_THUMB_WIDTH . '&height=' . BP_AVATAR_THUMB_HEIGHT ); ?></div>
											<div class="ac-reply-content">
												<div class="ac-textarea">
													<textarea id="ac-input-<?php bp_activity_id(); ?>" class="ac-input" name="ac_input_<?php bp_activity_id(); ?>"></textarea>
												</div>
												<input type="submit" name="ac_form_submit" value="<?php _e( 'Post', BP_MEDIA_TXT_DOMAIN ); ?>" /> &nbsp; <?php _e( 'or press esc to cancel.', BP_MEDIA_TXT_DOMAIN ); ?>
												<input type="hidden" name="comment_form_id" value="<?php bp_activity_id(); ?>" />
											</div>
											<?php do_action( 'bp_activity_entry_comments' ); ?>
											<?php wp_nonce_field( 'new_activity_comment', '_wpnonce_new_activity_comment' ); ?>
										</form>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<?php do_action( 'bp_after_activity_entry_comments' ); ?>
						</li>
					</ul>
				</div>
				<?php
			}
		}
		else {
			?>
			<div class="activity">
				<ul id="activity-stream" class="activity-list item-list">
					<li class="activity activity_update" id="activity-<?php echo $activity_id; ?>">
						<div class="activity-content">
							<?php do_action( 'bp_activity_entry_content' ); ?>
							<?php if ( is_user_logged_in() ) : ?>
								<div class="activity-meta no-ajax">
									<a href="<?php echo $this->get_delete_url(); ?>" class="button item-button bp-secondary-action delete-activity-single confirm" rel="nofollow"><?php _e( "Delete", BP_MEDIA_TXT_DOMAIN ); ?></a>
								</div>
							<?php endif; ?>
						</div>
					</li>
				</ul>
			</div>
			<?php
		}
	}

	/**
	 * Returns the URL of the single media entry page
	 */

	/**
	 *
	 * @return type
	 */
	function get_url() {
		return $this->url;
	}

	/**
	 * Returns the URL of the attached media file
	 */

	/**
	 *
	 * @return type
	 */
	function get_attachment_url() {
		return wp_get_attachment_url( $this->id );
	}

	/**
	 * Updates the media entry
	 *
	 * @param array $args Array with the following keys:<br/>
	 * 'name' <br/>
	 * 'description'<br/>
	 * 'owner'
	 *
	 * @return bool True when the update is successful, False when the update fails
	 */

	/**
	 *
	 * @param type $args
	 * @return type
	 */
	function update_media( $args = array( ) ) {
		$defaults = array(
			'name' => $this->name,
			'description' => $this->description,
			'owner' => $this->owner
		);
		$args = wp_parse_args( $args, $defaults );
		$post = get_post( $this->id, ARRAY_A );
		$post[ 'post_title' ] = esc_html( $args[ 'name' ] );
		$post[ 'post_content' ] = esc_html( $args[ 'description' ] );
		$post[ 'post_author' ] = $args[ 'owner' ];
		$result = wp_update_post( $post );
		$this->init( $this->id );
		do_action( 'bp_media_after_update_media', $this );
		return $result;
	}

	/**
	 * Updates activity content's title and description sync with the editing of Media
	 *
	 */

	/**
	 *
	 * @global type $wpdb
	 * @global type $bp
	 * @global type $current_user
	 * @global type $bp_media
	 */
	function update_media_activity() {
		global $wpdb, $bp, $current_user, $bp_media;
		$q = $wpdb->prepare( "SELECT id FROM {$bp->activity->table_name} WHERE type = %s AND item_id = %d", 'media_upload', $this->id );
		$activities = $wpdb->get_results( $q );
		if ( isset( $activities ) && count( $activities ) > 0 ) {
			$activities_template = new BP_Activity_Template( array(
						'max' => TRUE,
						'user_id' => $current_user,
						'in' => $activities[ 0 ]->id
					) );
			foreach ( $activities_template->activities as $activity ) {
				if ( isset( $_POST[ 'bp_media_group_id' ] ) ) {
					$component = $bp->groups->id;
					$item_id = $_POST[ 'bp_media_group_id' ];
				} else {
					$component = $bp->activity->id;
					$item_id = $this->get_id();
				}
				$args = array(
					'content' => $this->get_media_activity_content(),
					'id' => $activity->id,
					'type' => $component,
					'action' => apply_filters( 'bp_media_added_media', sprintf( __( '%1$s added a %2$s', BP_MEDIA_TXT_DOMAIN ), bp_core_get_userlink( $this->get_author() ), '<a href="' . $this->get_url() . '">' . $this->get_media_activity_type() . '</a>' ) ),
					'primary_link' => $this->get_url(),
					'item_id' => $item_id,
					'recorded_time' => $activity->date_recorded,
					'user_id' => $this->get_author()
				);
				$activity_id = BPMediaFunction::record_activity( $args );
			}
		}
	}

	/**
	 * Deletes the Media Entry
	 */

	/**
	 *
	 * @global type $bp_media_count
	 */
	function delete_media() {
		do_action( 'bp_media_before_delete_media', $this->id );
		wp_delete_attachment( $this->id, true );
		do_action( 'bp_media_after_delete_media', $this->id );
	}

	/**
	 * Function to return the content to be placed in the activity of album
	 */

	/**
	 *
	 * @return boolean|string
	 */
	function get_album_activity_content() {
		$attachment = $this->id;
		switch ( $this->type ) {
			case 'video' :
				if ( $this->thumbnail_id ) {
					$medium_array = image_downsize( $this->thumbnail_id, 'thumbnail' );
					$thumb_url = $medium_array[ 0 ];
				} else {
					$thumb_url = BP_MEDIA_URL . 'app/assets/img/video_thumb.png';
				}
				break;
			case 'audio' :
				if ( $this->thumbnail_id ) {
					$medium_array = image_downsize( $this->thumbnail_id, 'thumbnail' );
					$thumb_url = $medium_array[ 0 ];
				} else {
					$thumb_url = BP_MEDIA_URL . 'app/assets/img/audio_thumb.png';
				}
				break;
			case 'image' :
				$medium_array = image_downsize( $attachment, 'thumbnail' );
				$thumb_url = $medium_array[ 0 ];
				break;
			default :
				return false;
		}
		$content = '<li>';
		$content .= '<a href="' . $this->url . '" title="' . $this->name . '">';
		$content .= '<img src="' . $thumb_url . '" />';
		$content .= '</a>';
		$content .= '</li>';
		return $content;
	}

	/**
	 * Returns the description of the Media Entry
	 */

	/**
	 *
	 * @return type
	 */
	function get_content() {
		return $this->description;
	}

	/**
	 * Returns the owner id of the Media Entry
	 */

	/**
	 *
	 * @return type
	 */
	function get_author() {
		return $this->owner;
	}

	/**
	 * Returns the id of the Media Entry
	 */

	/**
	 *
	 * @return type
	 */
	function get_id() {
		return $this->id;
	}

	/**
	 * Returns the edit url of the Media Entry
	 */

	/**
	 *
	 * @return type
	 */
	function get_edit_url() {
		return $this->edit_url;
	}

	/**
	 * Returns the delete url of the Media Entry
	 */

	/**
	 *
	 * @return type
	 */
	function get_delete_url() {
		return $this->delete_url;
	}

	/**
	 * Returns the type of activity
	 */

	/**
	 *
	 * @return string
	 */
	function get_media_activity_type() {
		switch ( $this->type ) {
			case 'image':
				return BP_MEDIA_IMAGES_LABEL_SINGULAR;
			case 'video':
				return BP_MEDIA_VIDEOS_LABEL_SINGULAR;
			case 'audio':
				return BP_MEDIA_AUDIO_LABEL_SINGULAR;
			default:
				return 'Media';
		}
	}

	/**
	 * Returns the album id
	 */

	/**
	 *
	 * @return type
	 */
	function get_album_id() {
		return $this->album_id;
	}

	/**
	 * Returns the title of the media
	 */

	/**
	 *
	 * @return type
	 */
	function get_title() {
		return $this->name;
	}

	/**
	 * Returns the type of media
	 */

	/**
	 *
	 * @return type
	 */
	function get_type() {
		return $this->type;
	}

	/**
	 * Returns the thumbnail id
	 */
	function get_thumbnail_id() {
		return $this->thumbnail_id;
	}

	/**
	 * Returns the group id of the media, 0 if it does not belong to a group
	 */

	/**
	 *
	 * @return type
	 */
	function get_group_id() {
		return $this->group_id;
	}

	/**
	 * Sets the permalinks of the media depending upon whether its in member directory
	 * or group and acording to the media type
	 */

	/**
	 *
	 * @return boolean
	 */
	protected function set_permalinks() {

		if ( bp_is_active( 'groups' ) && class_exists( 'BP_Group_Extension' ) ) {
			if ( $this->group_id > 0 ) {
				$current_group = new BP_Groups_Group( $this->group_id );
				$pre_url = bp_get_group_permalink( $current_group );
			} else {
				$pre_url = bp_core_get_user_domain( $this->owner );
			}
		} else {
			$pre_url = bp_core_get_user_domain( $this->owner );
		}
		switch ( $this->type ) {
			case 'video' :
				$this->url = trailingslashit( $pre_url . BP_MEDIA_VIDEOS_SLUG . '/' . $this->id );
				$this->edit_url = trailingslashit( $pre_url . BP_MEDIA_VIDEOS_SLUG . '/' . BP_MEDIA_VIDEOS_EDIT_SLUG . '/' . $this->id );
				$this->delete_url = trailingslashit( $pre_url . BP_MEDIA_VIDEOS_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id );
				$this->thumbnail_id = get_post_meta( $this->id, 'bp_media_thumbnail', true );
				break;
			case 'audio' :
				$this->url = trailingslashit( $pre_url . BP_MEDIA_AUDIO_SLUG . '/' . $this->id );
				$this->edit_url = trailingslashit( $pre_url . BP_MEDIA_AUDIO_SLUG . '/' . BP_MEDIA_AUDIO_EDIT_SLUG . '/' . $this->id );
				$this->delete_url = trailingslashit( $pre_url . BP_MEDIA_AUDIO_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id );
				$this->thumbnail_id = get_post_meta( $this->id, 'bp_media_thumbnail', true );
				break;
			case 'image' :
				$this->url = trailingslashit( $pre_url . BP_MEDIA_IMAGES_SLUG . '/' . $this->id );
				$this->edit_url = trailingslashit( $pre_url . BP_MEDIA_IMAGES_SLUG . '/' . BP_MEDIA_IMAGES_EDIT_SLUG . '/' . $this->id );
				$this->delete_url = trailingslashit( $pre_url . BP_MEDIA_IMAGES_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id );
				$image_array = image_downsize( $this->id, 'bp_media_single_image' );
				$this->thumbnail_id = $this->id;
				break;
			case 'album' :
				$this->url = trailingslashit( $pre_url . BP_MEDIA_ALBUMS_SLUG . '/' . $this->id );
				$this->edit_url = trailingslashit( $pre_url . BP_MEDIA_ALBUMS_SLUG . '/' . BP_MEDIA_ALBUMS_EDIT_SLUG . '/' . $this->id );
				$this->delete_url = trailingslashit( $pre_url . BP_MEDIA_ALBUMS_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id );
//                $this->thumbnail_id = get_post_meta($this->id, 'bp_media_thumbnail', true);
				break;
			default :
				return false;
		}
		return true;
	}

	/**
	 * Checks if the album given exists if not, creates a new one according to context
	 */

	/**
	 *
	 * @global type $wpdb
	 * @param type $album_id
	 * @param type $group
	 * @return type
	 */
	function check_and_create_album( $album_id, $group ) {
		global $wpdb;
		$post_wall = __( 'Wall Posts', BP_MEDIA_TXT_DOMAIN );
		$create_new_album_flag = false;
		if ( $album_id != 0 ) {
			$album = get_post( $album_id );
			if ( $album->post_author != get_current_user_id() && $group == 0 ) {
				$create_new_album_flag = true;
			} else {
				$post_id = $album->ID;
			}
		} else {
			$create_new_album_flag = true;
		}
		$current_user = wp_get_current_user();
		if ( $create_new_album_flag ) {
			if ( $group == 0 ) {
				$post_id = $wpdb->get_var(
						"SELECT ID
						FROM $wpdb->posts
						WHERE
							post_title = $post_wall
							AND post_author = '" . get_current_user_id() . "'
							AND post_type='bp_media_album'"
				);
			} else {
				$post_id = $wpdb->get_var(
						"SELECT wp_posts.ID
						FROM $wpdb->posts
						INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
							AND $wpdb->postmeta.meta_key =  'bp-media-key'
							AND $wpdb->postmeta.meta_value = -$group
							AND $wpdb->posts.post_title = '$current_user->display_name\'s Album'" );
			}
			if ( $post_id == null ) {
				$album = new BPMediaAlbum();
				if ( $group == 0 )
					$album->add_album( $post_wall, get_current_user_id(), $group );
				else {
					$album->add_album( $current_user->display_name . '\'s Album', get_current_user_id(), $group );
				}
				$post_id = $album->get_id();
			}
		}
		return $post_id;
	}

	function get_description() {
		return $this->description;
	}

}
?>
