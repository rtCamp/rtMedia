<?php
/**
 * The main rtMedia Class file.
 *
 * @package rtMedia
 * @subpackage Main
 *
 * @author Faishal <faishal.saiyed@rtcamp.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main rtMedia Class. This is where everything starts.
 */
class RTMedia {

	/**
	 * Default thumbnail url fallback for all media types
	 *
	 * @var string $default_thumbnail
	 */
	private $default_thumbnail;

	/**
	 * Allowed media types array
	 *
	 * @var array $allowed_types
	 */
	public $allowed_types;

	/**
	 * Array of privacy settings
	 *
	 * @var array $privacy_settings
	 */
	public $privacy_settings;

	/**
	 * Default media sizes
	 *
	 * @var array $default_sizes
	 */
	public $default_sizes;

	/**
	 * Default application wide privacy levels
	 *
	 * @var array $default_privacy
	 */
	public $default_privacy = array(
		'0'  => 'Public',
		'20' => 'Users',
		'40' => 'Friends',
		'60' => 'Private',
	);

	/**
	 * Support forum url
	 *
	 * @var string $support_url
	 */
	public $support_url = 'https://rtmedia.io/support/';

	/**
	 * Number of media items to show in one view.
	 *
	 * @var int $posts_per_page
	 */
	public $posts_per_page = 10;

	/**
	 * The types of activity BuddyPress Media creates
	 *
	 * @var array $activity_types
	 */
	public $activity_types = array(
		'media_upload',
		'album_updated',
		'album_created',
	);

	/**
	 * Array of site options.
	 *
	 * @var array $options
	 */
	public $options;

	/**
	 * Render site options.
	 *
	 * @var array $render_options
	 */
	public $render_options; // todo:remove if unused.

	/**
	 * Constructs the class
	 * Defines constants and excerpt lengths, initiates admin notices,
	 * loads and initiates the plugin, loads translations.
	 * Initialises media counter
	 *
	 * @global int $bp_media_counter Media counter
	 */
	public function __construct() {
		$this->default_thumbnail = apply_filters( 'rtmedia_default_thumbnail', RTMEDIA_URL . 'app/assets/admin/img/thumb_default.png' );
		add_action( 'init', array( $this, 'check_global_album' ) );
		add_action( 'plugins_loaded', array( $this, 'admin_init' ) );
		add_action( 'plugins_loaded', array( $this, 'load_translation' ), 10 );
		add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
		add_action( 'wp_enqueue_scripts', array( 'RTMediaGalleryShortcode', 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts_styles' ), 999 );

		// Reset group status cache.
		add_action( 'groups_settings_updated', array( $this, 'group_status_reset_cache' ) );
		add_action( 'groups_delete_group', array( $this, 'group_status_reset_cache' ) );

		// Including core functions, actions & filters.
		include RTMEDIA_PATH . 'app/main/controllers/template/rtmedia-functions.php';
		include RTMEDIA_PATH . 'app/main/controllers/template/rtmedia-actions.php';
		include RTMEDIA_PATH . 'app/main/controllers/template/rtmedia-ajax-actions.php';
		include RTMEDIA_PATH . 'app/main/controllers/template/rtmedia-filters.php';

		add_filter( 'intermediate_image_sizes_advanced', array( $this, 'filter_image_sizes_details' ) );
		add_filter( 'intermediate_image_sizes', array( $this, 'filter_image_sizes' ) );
		add_filter( 'site_option_upload_filetypes', array( &$this, 'filter_allow_mime_type_mu' ), 1, 1 );
		add_filter( 'image_size_names_choose', array( $this, 'rtmedia_custom_image_sizes_choose' ) );
	}

	/**
	 * Delete group status cache.
	 *
	 * @param int $group_id Group ID.
	 */
	public function group_status_reset_cache( $group_id ) {
		delete_transient( 'group_status_' . $group_id );
	}

	/**
	 * Change allowed types for media.
	 *
	 * @param array $options Array of options.
	 *
	 * @return string
	 */
	public function filter_allow_mime_type_mu( $options ) {
		$allowed_types       = array();
		$this->allowed_types = apply_filters( 'rtmedia_allowed_types', $this->allowed_types );
		foreach ( $this->allowed_types as $type ) {
			if ( '' !== $type['extn'] && call_user_func( 'is_rtmedia_upload_' . $type['name'] . '_enabled' ) ) {
				foreach ( $type['extn'] as $extn ) {
					$allowed_types[] = $extn;
				}
			}
		}
		$ext     = apply_filters(
			'rtmedia_plupload_files_filter',
			array(
				array(
					'title'      => esc_html__( 'Media Files', 'buddypress-media' ),
					'extensions' => implode( ',', $allowed_types ),
				),
			)
		);
		$ext_arr = explode( ',', $ext[0]['extensions'] );
		$options = trim( $options );
		foreach ( $ext_arr as $f_ext ) {
			if ( $f_ext && strpos( $options, $f_ext ) === false ) {
				$options .= ' ' . $f_ext;
			}
		}

		return $options;
	}

	/**
	 * Fix parents ids for global albums.
	 */
	public function fix_parent_id() {
		$site_global = rtmedia_get_site_option( 'rtmedia-global-albums' );
		if ( $site_global && is_array( $site_global ) && isset( $site_global[0] ) ) {
			$model     = new RTMediaModel();
			$album_row = $model->get_by_id( $site_global[0] );
			if ( isset( $album_row['result'] ) && count( $album_row['result'] ) > 0 ) {
				global $wpdb;
				$row = $album_row['result'][0];
				if ( isset( $row['media_id'] ) ) {
					// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
					$sql = $wpdb->prepare(
						"update $wpdb->posts p
                                left join
                            $model->table_name r ON ( p.ID = r.media_id and blog_id = %d )
                        set
                            post_parent = %d
                        where
                            p.guid like %s
                                and (p.post_parent = 0 or p.post_parent is NULL)
                                and not r.id is NULL
                                and r.media_type <> 'album'",
						get_current_blog_id(),
						$row['media_id'],
						'%/rtMedia/%'
					);
					$wpdb->query( $sql );
					// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
				}
			}
		}
	}

	/**
	 * Function to change privacy in rtm_media table.
	 */
	public function fix_privacy() {
		global $wpdb;
		$model      = new RTMediaModel();
		$update_sql = "UPDATE {$model->table_name} SET privacy = '80' where privacy = '-1' ";
		$wpdb->query( $update_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Update media privacy of the medias having context=group
	 * update privacy of groups medias according to the privacy of the group 0->public, 20-> private/hidden
	 */
	public function fix_group_media_privacy() {
		// if buddypress is active and groups are enabled.
		global $wpdb;
		$table_exist = false;
		if ( $wpdb->query( "SHOW TABLES LIKE '{$wpdb->prefix}bp_groups'" ) ) {
			$table_exist = true;
		}
		if ( class_exists( 'BuddyPress' ) && $table_exist ) {
			$model     = new RTMediaModel();
			$sql_group = " UPDATE $model->table_name m join {$wpdb->prefix}bp_groups bp on m.context_id = bp.id SET m.privacy = 0 where m.context = 'group' and bp.status = 'public' and m.privacy <> 80 ";
			$wpdb->query( $sql_group ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql_group = " UPDATE $model->table_name m join {$wpdb->prefix}bp_groups bp on m.context_id = bp.id SET m.privacy = 20 where m.context = 'group' and ( bp.status = 'private' OR bp.status = 'hidden' ) and m.privacy <> 80 ";
			$wpdb->query( $sql_group ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	/**
	 * Function to fix DB collection by altering DB tables character set.
	 */
	public function fix_db_collation() {
		global $wpdb;
		$model             = new RTMediaModel();
		$interaction_model = new RTMediaInteractionModel();
		$update_media_sql  = 'ALTER TABLE ' . $model->table_name . ' CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci';
		$wpdb->query( $update_media_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$update_media_meta_sql = 'ALTER TABLE ' . $wpdb->base_prefix . $model->meta_table_name . ' CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci';
		$wpdb->query( $update_media_meta_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$update_media_interaction_sql = 'ALTER TABLE ' . $interaction_model->table_name . ' CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci';
		$wpdb->query( $update_media_interaction_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Set site options for rtMedia.
	 */
	public function set_site_options() {

		$rtmedia_options  = rtmedia_get_site_option( 'rtmedia-options' );
		$bp_media_options = rtmedia_get_site_option( 'bp_media_options' );

		if ( false === $rtmedia_options ) {
			$this->init_site_options();
		} else {
			// if new options added via filter then it needs to be updated.
			$this->options = $rtmedia_options;
		}
		$this->add_image_sizes();
		$this->set_image_quality();
		$this->comment_auto_complete();
	}

	/**
	 * Add filter that is being given in the buddypress plugin which add the JS and CSS for autocomplete
	 *
	 * More details on: http://hookr.io/functions/bp_activity_maybe_load_mentions_scripts/
	 *
	 * @param bool $rtmedia_options RTMedia options.
	 * @param bool $bp_media_options BuddyPress media options.
	 */
	public function comment_auto_complete( $rtmedia_options = false, $bp_media_options = false ) {
		add_filter( 'bp_activity_maybe_load_mentions_scripts', array( $this, 'rtmedia_bp_activity_maybe_load_mentions_scripts_callback' ), 10001, 2 );
	}

	/**
	 * Always set to true when call on any of the Buddypress Pages
	 *
	 * @param bool $load_mentions Load mentions script callback.
	 * @param bool $mentions_enabled Weather mentions enabled or not.
	 *
	 * @return      bool     true
	 */
	public function rtmedia_bp_activity_maybe_load_mentions_scripts_callback( $load_mentions, $mentions_enabled ) {
		$load_mentions = true;
		return $load_mentions;
	}

	/**
	 * Function to set JPEG image quality.
	 */
	public function set_image_quality() {
		add_filter( 'jpeg_quality', array( $this, 'rtmedia_jpeg_quality' ) );
	}

	/**
	 * Function to get JPEG image quality.
	 *
	 * @param int $quality Image quality.
	 *
	 * @return int
	 */
	public function rtmedia_jpeg_quality( $quality ) {
		$quality = isset( $this->options['general_jpeg_image_quality'] ) ? $this->options['general_jpeg_image_quality'] : 90;

		return $quality;
	}

	/**
	 * Get image sizes for different purposes.
	 *
	 * @return array
	 */
	public function image_sizes() {
		$image_sizes              = array();
		$image_sizes['thumbnail'] = array(
			'width'  => $this->options['defaultSizes_photo_thumbnail_width'],
			'height' => $this->options['defaultSizes_photo_thumbnail_height'],
			'crop'   => ( 0 === intval( $this->options['defaultSizes_photo_thumbnail_crop'] ) ) ? false : true,
		);
		$image_sizes['activity']  = array(
			'width'  => $this->options['defaultSizes_photo_medium_width'],
			'height' => $this->options['defaultSizes_photo_medium_height'],
			'crop'   => ( 0 === intval( $this->options['defaultSizes_photo_medium_crop'] ) ) ? false : true,
		);
		$image_sizes['single']    = array(
			'width'  => $this->options['defaultSizes_photo_large_width'],
			'height' => $this->options['defaultSizes_photo_large_height'],
			'crop'   => ( 0 === intval( $this->options['defaultSizes_photo_large_crop'] ) ) ? false : true,
		);
		$image_sizes['featured']  = array(
			'width'  => $this->options['defaultSizes_featured_default_width'],
			'height' => $this->options['defaultSizes_featured_default_height'],
			'crop'   => ( 0 === intval( $this->options['defaultSizes_featured_default_crop'] ) ) ? false : true,
		);

		return $image_sizes;
	}

	/**
	 * Add different image sizes.
	 */
	public function add_image_sizes() {
		$bp_media_sizes = $this->image_sizes();
		add_image_size( 'rt_media_thumbnail', $bp_media_sizes['thumbnail']['width'], $bp_media_sizes['thumbnail']['height'], $bp_media_sizes['thumbnail']['crop'] );
		add_image_size( 'rt_media_activity_image', $bp_media_sizes['activity']['width'], $bp_media_sizes['activity']['height'], $bp_media_sizes['activity']['crop'] );
		add_image_size( 'rt_media_single_image', $bp_media_sizes['single']['width'], $bp_media_sizes['single']['height'], $bp_media_sizes['single']['crop'] );
		add_image_size( 'rt_media_featured_image', $bp_media_sizes['featured']['width'], $bp_media_sizes['featured']['height'], $bp_media_sizes['featured']['crop'] );
		add_action( 'wp_head', array( &$this, 'custom_style_for_image_size' ) );
	}

	/**
	 * Add custom style for image size.
	 */
	public function custom_style_for_image_size() {
		if ( apply_filters( 'rtmedia_custom_image_style', true ) ) {
			?>
			<style type="text/css">
				<?php
				$this->custom_style_for_activity_image_size();
				global $rtmedia;
				if ( isset( $rtmedia->options['general_masonry_layout'] ) && 1 === intval( $rtmedia->options['general_masonry_layout'] ) ) {
					$this->custom_style_for_gallery_image_size_masonry();
				} else {
					$this->custom_style_for_gallery_image_size();
				}
				do_action( 'rtmedia_custom_styles' );
				?>
			</style>
			<?php
		}
	}

	/**
	 * Add custom style for activity image size.
	 */
	public function custom_style_for_activity_image_size() {

		// Get width from rtMedia settings.
		$width = ( isset( $this->options['defaultSizes_photo_medium_width'] ) ) ? $this->options['defaultSizes_photo_medium_width'] : '0';
		// Get height from rtMedia settings.
		$height = ( isset( $this->options['defaultSizes_photo_medium_height'] ) ) ? $this->options['defaultSizes_photo_medium_height'] : '0';

		$media_height = $height . 'px';
		$media_width  = $width . 'px';

		// If height or width given zero, then show it's original height or with.
		if ( '0' === $height ) {
			$media_height = 'auto';
		}

		if ( '0' === $width ) {
			$media_width = 'auto';
		}

		$video_height = ( isset( $this->options['defaultSizes_video_activityPlayer_height'] ) ? $this->options['defaultSizes_video_activityPlayer_height'] : '240' );
		$video_width  = ( isset( $this->options['defaultSizes_video_activityPlayer_width'] ) ? $this->options['defaultSizes_video_activityPlayer_width'] : '320' );

		if ( '0' === $video_height ) {
			$video_height = '240';
		}

		if ( '0' === $video_width ) {
			$video_width = '320';
		}

		$music_width = ( isset( $this->options['defaultSizes_music_activityPlayer_width'] ) ? $this->options['defaultSizes_music_activityPlayer_width'] : '320' );

		if ( '0' === $music_width ) {
			$music_width = '320';
		}

		?>
			.rtmedia-activity-container ul.rtm-activity-media-list{
			overflow: auto;
			}

			div.rtmedia-activity-container ul.rtm-activity-media-list li.media-type-document,
			div.rtmedia-activity-container ul.rtm-activity-media-list li.media-type-other{
			margin-left: 0.6em !important;
			}

			.rtmedia-activity-container li.media-type-video{
			height: <?php echo esc_attr( $video_height ); ?>px !important;
			width: <?php echo esc_attr( $video_width ); ?>px !important;
			}

			.rtmedia-activity-container li.media-type-video div.rtmedia-item-thumbnail,
			.rtmedia-activity-container li.media-type-photo a{
			width: 100% !important;
			height: 98% !important;
			}

			.rtmedia-activity-container li.media-type-video div.rtmedia-item-thumbnail video{
			width: 100% !important;
			height: 100% !important;
			}

			.rtmedia-activity-container li.media-type-video div.rtmedia-item-thumbnail .mejs-video,
			.rtmedia-activity-container li.media-type-video div.rtmedia-item-thumbnail .mejs-video video,
			.rtmedia-activity-container li.media-type-video div.rtmedia-item-thumbnail .mejs-video .mejs-overlay-play{
			width: 100% !important;
			height: 100% !important;
			}

			.rtmedia-activity-container li.media-type-music{
			width: <?php echo esc_attr( $music_width ); ?>px !important;
			}

			.rtmedia-activity-container li.media-type-music .rtmedia-item-thumbnail,
			.rtmedia-activity-container li.media-type-music .rtmedia-item-thumbnail .mejs-audio,
			.rtmedia-activity-container li.media-type-music .rtmedia-item-thumbnail audio{
			width: 100% !important;
			}

			.rtmedia-activity-container li.media-type-photo{
			width: <?php echo esc_attr( $media_width ); ?> !important;
			height: <?php echo esc_attr( $media_height ); ?> !important;
			}

			.rtmedia-activity-container .media-type-photo .rtmedia-item-thumbnail,
			.rtmedia-activity-container .media-type-photo .rtmedia-item-thumbnail img {
			width: 100% !important;
			height: 100% !important;
			overflow: hidden;
			}
		<?php
		global $rtmedia;
		if ( rtmedia_check_comment_media_allow() && ! rtmedia_check_comment_in_commented_media_allow() ) {
			?>
				#buddypress ul.activity-list li.activity-item .activity-comments ul li form.ac-form .rtmedia-comment-media-upload,#buddypress ul.activity-list li.activity-item .activity-comments ul li form.ac-form .rtmedia-container {
					display: none !important
				}
			<?php
		}
	}

	/**
	 * Add custom style for gallery image size.
	 */
	public function custom_style_for_gallery_image_size() {
		// Get width from rtMedia settings.
		$width = ( isset( $this->options['defaultSizes_photo_thumbnail_width'] ) ) ? $this->options['defaultSizes_photo_thumbnail_width'] : '0';
		// Get height from rtMedia settings.
		$height = ( isset( $this->options['defaultSizes_photo_thumbnail_height'] ) ) ? $this->options['defaultSizes_photo_thumbnail_height'] : '0';

		$media_height = $height . 'px';
		$media_width  = $width . 'px';

		// If height or width given zero, then show it's original height or with.
		if ( '0' === $height ) {
			$media_height = 'auto';
		}

		if ( '0' === $width ) {
			$media_width = 'auto';
		}
		?>
		.rtmedia-container ul.rtmedia-list li.rtmedia-list-item div.rtmedia-item-thumbnail {
		width: <?php echo esc_attr( $media_width ); ?>;
		height: <?php echo esc_attr( $media_height ); ?>;
		line-height: <?php echo esc_attr( $media_height ); ?>;
		}
		.rtmedia-container ul.rtmedia-list li.rtmedia-list-item div.rtmedia-item-thumbnail img {
		max-width: <?php echo esc_attr( $media_width ); ?>;
		max-height: <?php echo esc_attr( $media_height ); ?>;
		}
		.rtmedia-container .rtmedia-list  .rtmedia-list-item {
		width: <?php echo esc_attr( $media_width ); ?>;
		}
		<?php
	}

	/**
	 * Add custom style for gallery image size masonry.
	 */
	public function custom_style_for_gallery_image_size_masonry() {
		if ( intval( $this->options['defaultSizes_photo_thumbnail_height'] ) > 0 ) {
			?>
			.rtmedia-container .rtmedia-list  .rtmedia-list-item .rtmedia-item-thumbnail {
			max-height: <?php echo intval( $this->options['defaultSizes_photo_thumbnail_height'] ); ?>px;
			}
			<?php
		}
		if ( intval( $this->options['defaultSizes_photo_thumbnail_width'] ) > 0 ) {
			?>
			.rtmedia-container .rtmedia-list  .rtmedia-list-item .rtmedia-item-thumbnail {
			max-width: <?php echo intval( $this->options['defaultSizes_photo_thumbnail_width'] ); ?>px;
			}
			<?php
		}
	}

	/**
	 *  Default allowed media types array
	 */
	public function set_allowed_types() {
		$allowed_types = array(
			'photo' => array(
				'name'                => 'photo',
				'plural'              => 'photos',
				'label'               => esc_html__( 'Photo', 'buddypress-media' ),
				'plural_label'        => esc_html__( 'Photos', 'buddypress-media' ),
				'extn'                => array( 'jpg', 'jpeg', 'png', 'gif' ),
				'thumbnail'           => RTMEDIA_URL . 'app/assets/admin/img/image_thumb.png',
				'settings_visibility' => true,
			),
			'video' => array(
				'name'                => 'video',
				'plural'              => 'videos',
				'label'               => esc_html__( 'Video', 'buddypress-media' ),
				'plural_label'        => esc_html__( 'Videos', 'buddypress-media' ),
				'extn'                => array( 'mp4' ),
				'thumbnail'           => RTMEDIA_URL . 'app/assets/admin/img/video_thumb.png',
				'settings_visibility' => true,
			),
			'music' => array(
				'name'                => 'music',
				'plural'              => 'music',
				'label'               => esc_html__( 'Music', 'buddypress-media' ),
				'plural_label'        => esc_html__( 'Music', 'buddypress-media' ),
				'extn'                => array( 'mp3' ),
				'thumbnail'           => RTMEDIA_URL . 'app/assets/admin/img/audio_thumb.png',
				'settings_visibility' => true,
			),
		);

		// filter for hooking additional media types.
		$allowed_types = apply_filters( 'rtmedia_allowed_types', $allowed_types );

		// sanitize all the types.
		$allowed_types = $this->sanitize_allowed_types( $allowed_types );

		// set the allowed types property.
		$this->allowed_types = $allowed_types;
	}

	/**
	 * Sanitize all media sizes after hooking custom media types
	 *
	 * @param array $allowed_types allowed media types after hooking custom types.
	 *
	 * @return array $allowed_types sanitized media types
	 */
	public function sanitize_allowed_types( $allowed_types ) {
		// check if the array is formatted properly.
		if ( ! is_array( $allowed_types ) && count( $allowed_types ) < 1 ) {
			return;
		}

		// loop through each type.
		foreach ( $allowed_types as $key => &$type ) {

			if ( ! isset( $type['name'] ) || // check if a name is set.
				empty( $type['name'] ) ||
				// commented this section for playlist // !isset($type['extn']) || // check if file extensions are set.
				// commented this section for playlist  // empty($type['extn']) ||.
				strstr( $type['name'], ' ' ) || strstr( $type['name'], '_' )
			) {
				unset( $allowed_types[ $key ] ); // if not unset this type.
				continue;
			}
			$slug = strtoupper( $type['name'] );
			if ( defined( 'RTMEDIA_' . $slug . '_LABEL' ) ) {
				$type['label'] = constant( 'RTMEDIA_' . $slug . '_LABEL' );
			}
			if ( defined( 'RTMEDIA_' . $slug . '_PLURAL_LABEL' ) ) {
				$type['plural_label'] = constant( 'RTMEDIA_' . $slug . '_PLURAL_LABEL' );
			}
			// if thumbnail is not supplied, use the default thumbnail.
			if ( ! isset( $type['thumbnail'] ) || empty( $type['thumbnail'] ) ) {
				$type['thumbnail'] = $this->default_thumbnail;
			}
		}

		return $allowed_types;
	}

	/**
	 * Set the default sizes
	 */
	public function set_default_sizes() {
		$this->default_sizes = array(
			'photo'    => array(
				'thumbnail' => array(
					'width'  => 150,
					'height' => 150,
					'crop'   => 1,
				),
				'medium'    => array(
					'width'  => 320,
					'height' => 240,
					'crop'   => 1,
				),
				'large'     => array(
					'width'  => 800,
					'height' => 0,
					'crop'   => 1,
				),
			),
			'video'    => array(
				'activityPlayer' => array(
					'width'  => 320,
					'height' => 240,
				),
				'singlePlayer'   => array(
					'width'  => 640,
					'height' => 480,
				),
			),
			'music'    => array(
				'activityPlayer' => array( 'width' => 320 ),
				'singlePlayer'   => array( 'width' => 640 ),
			),
			'featured' => array(
				'default' => array(
					'width'  => 100,
					'height' => 100,
					'crop'   => 1,
				),
			),
		);

		$this->default_sizes = apply_filters( 'rtmedia_allowed_sizes', $this->default_sizes );
	}

	/**
	 * Set privacy options
	 */
	public function set_privacy() {

		$this->privacy_settings = array(
			'levels' => array(
				60 => esc_html__( 'Private - Visible only to the user', 'buddypress-media' ),
				40 => esc_html__( 'Friends - Visible to user\'s friends', 'buddypress-media' ),
				20 => esc_html__( 'Logged in Users - Visible to registered users', 'buddypress-media' ),
				0  => esc_html__( 'Public - Visible to the world', 'buddypress-media' ),
			),
		);
		$this->privacy_settings = apply_filters( 'rtmedia_privacy_levels', $this->privacy_settings );

		if ( function_exists( 'bp_is_active' ) && ! bp_is_active( 'friends' ) ) {
			unset( $this->privacy_settings['levels'][40] );
		}
	}

	/**
	 * Load admin screens
	 *
	 * @global RTMediaAdmin $rtmedia_admin Class for loading admin screen
	 */
	public function admin_init() {
		global $rtmedia_admin;
		$rtmedia_admin = new RTMediaAdmin();
	}

	/**
	 * Media screen.
	 */
	public function media_screen() {
		// todo: check if function required.
		return '';
	}

	/**
	 * Function to get user posts link from given ID.
	 *
	 * @param int $user User id to get link.
	 *
	 * @return string
	 */
	public function get_user_link( $user ) {

		if ( function_exists( 'bp_core_get_user_domain' ) ) {
			$parent_link = bp_core_get_user_domain( $user );
		} else {
			$parent_link = get_author_posts_url( $user );
		}

		return $parent_link;
	}

	/**
	 * Generate buddypress options.
	 */
	public function init_buddypress_options() {
		/**
		 * BuddyPress Settings
		 */
		$bp_media_options = rtmedia_get_site_option( 'bp_media_options' );

		$group = 0;
		if ( isset( $bp_media_options['enable_on_group'] ) && ! empty( $bp_media_options['enable_on_group'] ) ) {
			$group = $bp_media_options['enable_on_group'];
		} elseif ( function_exists( 'bp_is_active' ) ) {
			$group = bp_is_active( 'groups' );
		}
		$this->options['buddypress_enableOnGroup'] = $group;

		$activity = 0;
		if ( isset( $bp_media_options['activity_upload'] ) && ! empty( $bp_media_options['activity_upload'] ) ) {
			$activity = $bp_media_options['activity_upload'];
		} elseif ( function_exists( 'bp_is_active' ) ) {
			$activity = bp_is_active( 'activity' );
		}
		$this->options['buddypress_enableOnActivity'] = $activity;

		$this->options['buddypress_enableOnProfile'] = 1;

		// Last settings updated in options. Update them in DB & after this no other option would be saved in db.
		rtmedia_update_site_option( 'rtmedia-options', $this->options );
	}

	/**
	 * Generate rtMedia site options.
	 */
	public function init_site_options() {

		$bp_media_options = rtmedia_get_site_option( 'bp_media_options' );

		$defaults = array(
			'general_enableAlbums'        => 1,
			'general_enableComments'      => 0,
			'general_downloadButton'      => ( isset( $bp_media_options['download_enabled'] ) ) ? $bp_media_options['download_enabled'] : 0,
			'general_enableLightbox'      => ( isset( $bp_media_options['enable_lightbox'] ) ) ? $bp_media_options['enable_lightbox'] : 1,
			'general_perPageMedia'        => ( isset( $bp_media_options['default_count'] ) ) ? $bp_media_options['default_count'] : 10,
			'general_enableMediaEndPoint' => 0,
			'general_showAdminMenu'       => ( isset( $bp_media_options['show_admin_menu'] ) ) ? $bp_media_options['show_admin_menu'] : 0,
			'general_videothumbs'         => 2,
			'general_jpeg_image_quality'  => 90,
			'general_AllowUserData'       => 0,
		);

		foreach ( $this->allowed_types as $type ) {
			// invalid keys handled in sanitize method.
			$defaults[ 'allowedTypes_' . $type['name'] . '_enabled' ]  = 1;
			$defaults[ 'allowedTypes_' . $type['name'] . '_featured' ] = 0;
		}

		// Previous Sizes values from buddypress is migrated.
		foreach ( $this->default_sizes as $type => $type_value ) {
			foreach ( $type_value as $size => $size_value ) {
				foreach ( $size_value as $dimension => $value ) {
					switch ( $type ) {
						case 'photo':
							if ( isset( $bp_media_options['sizes']['image'][ $size ][ $dimension ] ) && ! empty( $bp_media_options['sizes']['image'][ $size ][ $dimension ] ) ) {
								$value = $bp_media_options['sizes']['image'][ $size ][ $dimension ];
							}
							break;
						case 'video':
						case 'music':
							$old = ( 'video' === $type ) ? 'video' : ( ( 'music' === $type ) ? 'audio' : '' );
							switch ( $size ) {
								case 'activityPlayer':
									if ( isset( $bp_media_options['sizes'][ $old ]['medium'][ $dimension ] ) && ! empty( $bp_media_options['sizes'][ $old ]['medium'][ $dimension ] ) ) {
										$value = $bp_media_options['sizes'][ $old ]['medium'][ $dimension ];
									}
									break;
								case 'singlePlayer':
									if ( isset( $bp_media_options['sizes'][ $old ]['large'][ $dimension ] ) && ! empty( $bp_media_options['sizes'][ $old ]['large'][ $dimension ] ) ) {
										$value = $bp_media_options['sizes'][ $old ]['large'][ $dimension ];
									}
									break;
							}
							break;
					}
					$defaults[ 'defaultSizes_' . $type . '_' . $size . '_' . $dimension ] = $value;
				}
			}
		}

		// Privacy options.
		$defaults['privacy_enabled']      = ( isset( $bp_media_options['privacy_enabled'] ) ) ? $bp_media_options['privacy_enabled'] : 0;
		$defaults['privacy_default']      = ( isset( $bp_media_options['default_privacy_level'] ) ) ? $bp_media_options['default_privacy_level'] : 0;
		$defaults['privacy_userOverride'] = ( isset( $bp_media_options['privacy_override_enabled'] ) ) ? $bp_media_options['privacy_override_enabled'] : 0;

		$defaults['styles_custom']  = ( isset( $bp_media_options['styles_custom'] ) ) ? $bp_media_options['styles_custom'] : '';
		$defaults['styles_enabled'] = ( isset( $bp_media_options['styles_enabled'] ) ) ? $bp_media_options['styles_enabled'] : 1;

		$this->options = $defaults;

		$this->init_buddypress_options();
	}

	/**
	 * Defines all the constants if undefined. Can be overridden by
	 * defining them elsewhere, say wp-config.php
	 */
	public function constants() {

		// If the plugin is installed.
		if ( ! defined( 'RTMEDIA_IS_INSTALLED' ) ) {
			define( 'RTMEDIA_IS_INSTALLED', 1 );
		}

		// Required Version.
		if ( ! defined( 'RTMEDIA_REQUIRED_BP' ) ) {
			define( 'RTMEDIA_REQUIRED_BP', '1.7' );
		}

		/* Slug Constants for building urls */

		// Media slugs.
		if ( ! defined( 'RTMEDIA_MEDIA_SLUG' ) ) {
			define( 'RTMEDIA_MEDIA_SLUG', 'media' );
		}

		if ( ! defined( 'RTMEDIA_MEDIA_LABEL' ) ) {
			define( 'RTMEDIA_MEDIA_LABEL', esc_html__( 'Media', 'buddypress-media' ) );
		}

		if ( ! defined( 'RTMEDIA_ALL_SLUG' ) ) {
			define( 'RTMEDIA_ALL_SLUG', 'all' );
		}

		if ( ! defined( 'RTMEDIA_ALL_LABEL' ) ) {
			define( 'RTMEDIA_ALL_LABEL', esc_html__( 'All', 'buddypress-media' ) );
		}

		if ( ! defined( 'RTMEDIA_ALBUM_SLUG' ) ) {
			define( 'RTMEDIA_ALBUM_SLUG', 'album' );
		}

		if ( ! defined( 'RTMEDIA_ALBUM_PLURAL_SLUG' ) ) {
			define( 'RTMEDIA_ALBUM_PLURAL_SLUG', 'albums' );
		}

		if ( ! defined( 'RTMEDIA_ALBUM_LABEL' ) ) {
			define( 'RTMEDIA_ALBUM_LABEL', esc_html__( 'Album', 'buddypress-media' ) );
		}

		if ( ! defined( 'RTMEDIA_ALBUM_PLURAL_LABEL' ) ) {
			define( 'RTMEDIA_ALBUM_PLURAL_LABEL', esc_html__( 'Albums', 'buddypress-media' ) );
		}

		// Upload slug.
		if ( ! defined( 'RTMEDIA_UPLOAD_SLUG' ) ) {
			define( 'RTMEDIA_UPLOAD_SLUG', 'upload' );
		}

		// Upload slug.
		if ( ! defined( 'RTMEDIA_UPLOAD_LABEL' ) ) {
			define( 'RTMEDIA_UPLOAD_LABEL', esc_html__( 'Upload', 'buddypress-media' ) );
		}

		// Global Album/Wall Post.
		if ( ! defined( 'RTMEDIA_GLOBAL_ALBUM_LABEL' ) ) {
			define( 'RTMEDIA_GLOBAL_ALBUM_LABEL', esc_html__( 'Wall Post', 'buddypress-media' ) );
		}

		$this->define_type_constants();
	}

	/**
	 * Define rtMedia allowed media type constants.
	 */
	public function define_type_constants() {

		if ( ! isset( $this->allowed_types ) ) {
			return;
		}
		foreach ( $this->allowed_types as $type ) {

			if ( ! isset( $type['name'] ) || '' === $type['name'] ) {
				continue;
			}

			$name = $type['name'];

			if ( isset( $type['plural'] ) && '' !== $type['plural'] ) {
				$plural = $type['plural'];
			} else {
				$plural = $name . 's';
			}

			if ( isset( $type['label'] ) && '' !== $type['label'] ) {
				$label = $type['label'];
			} else {
				$label = ucfirst( $name );
			}

			if ( isset( $type['plural_label'] ) && '' !== $type['plural_label'] ) {
				$label_plural = $type['plural_label'];
			} else {
				$label_plural = ucfirst( $plural );
			}

			$slug = strtoupper( $name );

			if ( ! defined( 'RTMEDIA_' . $slug . '_SLUG' ) ) {
				define( 'RTMEDIA_' . $slug . '_SLUG', $name );
			}
			if ( ! defined( 'RTMEDIA_' . $slug . '_PLURAL_SLUG' ) ) {
				define( 'RTMEDIA_' . $slug . '_PLURAL_SLUG', $plural );
			}
			if ( ! defined( 'RTMEDIA_' . $slug . '_LABEL' ) ) {
				define( 'RTMEDIA_' . $slug . '_LABEL', $label );
			}
			if ( ! defined( 'RTMEDIA_' . $slug . '_PLURAL_LABEL' ) ) {
				define( 'RTMEDIA_' . $slug . '_PLURAL_LABEL', $label_plural );
			}
		}
	}

	/**
	 * Hooks the plugin into BuddyPress via 'bp_include' action.
	 * Initialises the plugin's functionality, options,
	 * loads media for Profiles and Groups.
	 * Creates Admin panels
	 * Loads accessory functions
	 *
	 * @global BPMediaAdmin $bp_media_admin
	 */
	public function init() {
		// set meta table in $wpdb.
		$this->set_rtmedia_meta_wpdbfix();

		// rtMedia db upgrade.
		add_action( 'rt_db_upgrade', array( $this, 'fix_parent_id' ) );
		add_action( 'rt_db_upgrade', array( $this, 'fix_privacy' ) );
		add_action( 'rt_db_upgrade', array( $this, 'fix_group_media_privacy' ) );
		add_action( 'rt_db_upgrade', array( $this, 'fix_db_collation' ) );
		$this->update_db();
		remove_action( 'rt_db_upgrade', array( $this, 'fix_parent_id' ) );
		remove_action( 'rt_db_upgrade', array( $this, 'fix_privacy' ) );
		remove_action( 'rt_db_upgrade', array( $this, 'fix_group_media_privacy' ) );
		remove_action( 'rt_db_upgrade', array( $this, 'fix_db_collation' ) );

		$this->set_allowed_types(); // Define allowed types.
		$this->constants(); // Define constants.
		$this->redirect_on_change_slug();
		$this->set_default_sizes(); // set default sizes.
		$this->set_privacy(); // set privacy.

		/**
		 * Load options/settings
		 */
		$this->set_site_options();

		/**
		 * BuddyPress - Media Navigation Tab Inject
		 */
		/**
		 * Load accessory functions
		 */
		$class_construct = array(
			'deprecated'          => true,
			'interaction'         => true,
			'upload_shortcode'    => false,
			'gallery_shortcode'   => false,
			'upload_endpoint'     => false,
			'privacy'             => false,
			'nav'                 => true,
			'like'                => false,
			'featured'            => false,
			'GroupFeatured'       => false,
			'ViewCount'           => false,
			'GalleryItemAction'   => false,
			'LoginPopup'          => false,
			'CommentNotification' => false,
			'LikeNotification'    => false,
		);
		global $rtmedia_nav;

		/** Legacy code for Add-ons * */
		$bp_class_construct = apply_filters( 'bpmedia_class_construct', array() );
		foreach ( $bp_class_construct as $classname => $global_scope ) {
			$class = 'BPMedia' . ucfirst( $classname );
			if ( class_exists( $class ) ) {
				if ( true === $global_scope ) {
					global ${'bp_media_' . $classname}; // phpcs:ignore PHPCompatibility.Variables.ForbiddenGlobalVariableVariable.NonBareVariableFound
					${'bp_media_' . $classname} = new $class();
				} else {
					new $class();
				}
			}
		}
		/** ------------------- * */
		$class_construct = apply_filters( 'rtmedia_class_construct', $class_construct );

		$class_construct['Group'] = false; // will be constructed after rtmedia pro class.

		foreach ( $class_construct as $key => $global_scope ) {
			$classname = '';
			$ck        = explode( '_', $key );

			foreach ( $ck as $cn ) {
				$classname .= ucfirst( $cn );
			}

			$class = 'RTMedia' . $classname;

			if ( class_exists( $class ) ) {
				if ( true === $global_scope ) {
					global ${'rtmedia_' . $key}; // phpcs:ignore PHPCompatibility.Variables.ForbiddenGlobalVariableVariable.NonBareVariableFound
					${'rtmedia_' . $key} = new $class();
				} else {
					new $class();
				}
			}
		}

		$this->set_allowed_types(); // Define allowed types.

		global $rtmedia_buddypress_activity;
		$rtmedia_buddypress_activity = new RTMediaBuddyPressActivity();
		$media                       = new RTMediaMedia();
		$media->delete_hook();

		global $rtmedia_ajax;
		$rtmedia_ajax = new RTMediaAJAX();
		// API Classes.
		global $rtmediajsonapi;
		$rtmediajsonapi = new RTMediaJsonApi();

		if ( function_exists( 'rtmedia_enable_comment_media_uplaod' ) ) {
			rtmedia_enable_comment_media_uplaod();
		}

		do_action( 'bp_media_init' ); // legacy For plugin using this actions.
		do_action( 'rtmedia_init' );
	}

	/**
	 * Set rtMedia meta as mediameta for wpdb query.
	 */
	public function set_rtmedia_meta_wpdbfix() {
		global $wpdb;
		$media_meta      = new RTMediaMeta();
		$wpdb->mediameta = $media_meta->model->table_name;
	}

	/**
	 * Update option for rtMedia slug change.
	 */
	public function redirect_on_change_slug() {
		$old_slugs     = rtmedia_get_site_option( 'rtmedia_old_media_slug', false, true );
		$current_slugs = rtmedia_get_site_option( 'rtmedia_current_media_slug', false, false );
		if ( false === $current_slugs ) {
			rtmedia_update_site_option( 'rtmedia_current_media_slug', RTMEDIA_MEDIA_SLUG );

			return;
		}
		if ( RTMEDIA_MEDIA_SLUG === $current_slugs ) {
			return;
		}
		if ( false === $old_slugs ) {
			$old_slugs = array();
		}
		$old_slugs[] = $current_slugs;
		rtmedia_update_site_option( 'rtmedia_current_media_slug', RTMEDIA_MEDIA_SLUG );
	}

	/**
	 * Loads translations.
	 */
	public static function load_translation() {
		load_plugin_textdomain( 'buddypress-media', false, basename( RTMEDIA_PATH ) . '/languages/' );
	}

	/**
	 * Check default global album,.
	 *
	 * @return bool
	 */
	public function check_global_album() {
		// todo: Nonce required.
		$album        = new RTMediaAlbum();
		$global_album = $album->get_default();

		$action = sanitize_text_field( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) );
		$mode   = sanitize_text_field( filter_input( INPUT_POST, 'mode', FILTER_SANITIZE_STRING ) );

		// Hack for plupload default name.
		if ( ! empty( $action ) && ! empty( $mode ) && 'file_upload' === $mode ) {
			unset( $_POST['name'] ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		}

		global $rtmedia_error;
		if ( isset( $rtmedia_error ) && true === $rtmedia_error ) {
			return false;
		}
		if ( ! $global_album ) {
			$global_album = $album->add_global( esc_html__( 'Wall Posts', 'buddypress-media' ) );
		}

		// fix multisite global album doesn't exist issue.
		if ( is_multisite() && ! rtmedia_get_site_option( 'rtmedia_fix_multisite_global_albums', false ) ) {
			$model         = new RTMediaModel();
			$global_albums = rtmedia_global_albums();
			$album_objects = $model->get_media( array( 'id' => ( $global_albums ) ), false, false );
			if ( empty( $album_objects ) ) {
				$global_album = $album->add_global( esc_html__( 'Wall Posts', 'buddypress-media' ) );
			}
			rtmedia_update_site_option( 'rtmedia_fix_multisite_global_albums', true );
		}
	}

	/**
	 * Get default post per page count.
	 *
	 * @return int
	 */
	public function default_count() {
		$count = $this->posts_per_page;
		if ( array_key_exists( 'default_count', $this->options ) ) {
			$count = $this->options['default_count'];
		}
		$count = ( ! is_int( $count ) ) ? 0 : $count;

		return ( ! $count ) ? 10 : $count;
	}

	/**
	 * Function to get plugin version.
	 *
	 * @param string $path Plugin path.
	 *
	 * @return mixed
	 */
	public static function plugin_get_version( $path = null ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$path           = ( $path ) ? $path : RTMEDIA_PATH . 'index.php';
		$plugin_data    = get_plugin_data( $path );
		$plugin_version = $plugin_data['Version'];

		return $plugin_version;
	}

	/**
	 * Function to update DB for rtMedia update.
	 */
	public function update_db() {
		$update = new RTDBUpdate( false, RTMEDIA_PATH . 'index.php', RTMEDIA_PATH . 'app/schema/', true );
		// Current Version.
		if ( ! defined( 'RTMEDIA_VERSION' ) ) {
			define( 'RTMEDIA_VERSION', $update->db_version );
		}
		if ( $update->check_upgrade() ) {
			$update->do_upgrade();
		}
		if ( ! $update->table_exists( $update->genrate_table_name( 'rtm_media' ) ) ) {
			delete_site_option( $update->get_db_version_option_name() );
			$update->install_db_version = '0';
			$update->do_upgrade();
		}
	}

	/**
	 * Display error notice for DB table creation failure.
	 */
	public function create_table_error_notice() {
		global $rtmedia_error;
		$rtmedia_error = true;
		echo "<div class='error'><p><strong>" . esc_html__( 'rtMedia', 'buddypress-media' ) . '</strong>' . esc_html__( ": Can't Create Database table. Please check create table permission.", 'buddypress-media' ) . '</p></div>';
	}

	/**
	 * Enqueue scripts and styles for rtMedia.
	 */
	public function enqueue_scripts_styles() {
		global $rtmedia, $bp, $rtmedia_interaction;

		$rtmedia_main     = array();
		$rtmedia_backbone = array();
		$rtmedia_bp_tpl   = array();

		$bp_template = get_option( '_bp_theme_package_id' );

		wp_enqueue_script( 'rt-mediaelement', RTMEDIA_URL . 'lib/media-element/mediaelement-and-player.min.js', '', RTMEDIA_VERSION, true );
		wp_enqueue_style( 'rt-mediaelement', RTMEDIA_URL . 'lib/media-element/mediaelementplayer-legacy.min.css', '', RTMEDIA_VERSION );
		wp_enqueue_style( 'rt-mediaelement-wp', RTMEDIA_URL . 'lib/media-element/wp-mediaelement.min.css', '', RTMEDIA_VERSION );
		wp_enqueue_script( 'rt-mediaelement-wp', RTMEDIA_URL . 'lib/media-element/wp-mediaelement.min.js', 'rt-mediaelement', RTMEDIA_VERSION, true );

		// Dashicons: Needs if not loaded by WP.
		wp_enqueue_style( 'dashicons' );

		// Dont enqueue rtmedia.min.css if default styles is checked false in rtmedia settings.
		$suffix = ( function_exists( 'rtm_get_script_style_suffix' ) ) ? rtm_get_script_style_suffix() : '.min';

		if ( ! ( isset( $rtmedia->options ) && isset( $rtmedia->options['styles_enabled'] ) && 0 === $rtmedia->options['styles_enabled'] ) ) {
			wp_enqueue_style( 'rtmedia-main', RTMEDIA_URL . 'app/assets/css/rtmedia' . $suffix . '.css', '', RTMEDIA_VERSION );
		}

		if ( '' === $suffix ) {
			wp_enqueue_script(
				'rtmedia-magnific-popup',
				RTMEDIA_URL . 'app/assets/js/vendors/magnific-popup.js',
				array(
					'jquery',
					'rt-mediaelement-wp',
				),
				RTMEDIA_VERSION,
				true
			);
			wp_enqueue_script(
				'rtmedia-admin-tabs',
				RTMEDIA_URL . 'app/assets/admin/js/vendors/tabs.js',
				array(
					'jquery',
					'rt-mediaelement-wp',
				),
				RTMEDIA_VERSION,
				true
			);
			wp_enqueue_script(
				'rtmedia-main',
				RTMEDIA_URL . 'app/assets/js/rtMedia.js',
				array(
					'jquery',
					'rt-mediaelement-wp',
				),
				RTMEDIA_VERSION,
				true
			);
		} else {
			wp_enqueue_script(
				'rtmedia-main',
				RTMEDIA_URL . 'app/assets/js/rtmedia.min.js',
				array(
					'jquery',
					'rt-mediaelement-wp',
				),
				RTMEDIA_VERSION,
				true
			);
			// localize for rtmedia js.
			wp_localize_script( 'rtmedia-main', 'rtmedia_bp', array( 'bp_template_pack' => $bp_template ) );
		}

		$media_delete_confirmation_msg = __( 'Are you sure you want to delete this media?', 'buddypress-media' );
		$media_delete_success_msg      = __( 'Media file deleted successfully.', 'buddypress-media' );

		/**
		 * Media deletion confirmation message.
		 *
		 * The added filter `rtmedia_delete_prompt_message` helps in modifying the user consent message before proceeding towards deleting the media.
		 *
		 * @param string $media_delete_confirmation_msg Holds the actual confirmation message.
		 */
		$media_delete_confirmation = apply_filters( 'rtmedia_delete_prompt_message', $media_delete_confirmation_msg );

		/**
		 * Media deletion success message.
		 *
		 * The added filter `rtmedia_media_delete_success_message` helps in modifying the success message that pops us after the media is deleted.
		 *
		 * @param string $media_delete_success_msg Holds the actual success message.
		 */
		$media_delete_success = apply_filters( 'rtmedia_media_delete_success_message', $media_delete_success_msg );

		wp_localize_script(
			'rtmedia-main',
			'RTMedia_Main_JS',
			array(
				'media_delete_confirmation' => $media_delete_confirmation,
				'rtmedia_ajaxurl'           => admin_url( 'admin-ajax.php' ),
				'media_delete_success'      => $media_delete_success,
			)
		);

		$rtmedia_main['rtmedia_ajax_url']         = admin_url( 'admin-ajax.php' );
		$rtmedia_main['rtmedia_media_slug']       = RTMEDIA_MEDIA_SLUG;
		$rtmedia_main['rtmedia_lightbox_enabled'] = strval( $this->options['general_enableLightbox'] );

		$direct_upload = ( isset( $this->options['general_direct_upload'] ) ? $this->options['general_direct_upload'] : '0' );

		$rtmedia_main['rtmedia_direct_upload_enabled'] = $direct_upload;

		// gallery reload after media upload, by default true.
		$rtmedia_main['rtmedia_gallery_reload_on_upload'] = '1';

		// javascript messages.
		wp_localize_script(
			'rtmedia-magnific',
			'rtmedia_magnific',
			array(
				'rtmedia_load_more' => __( 'Loading media', 'buddypress-media' ),
			)
		);

		$rtmedia_main['rtmedia_empty_activity_msg']                 = __( 'Please enter some content to post.', 'buddypress-media' );
		$rtmedia_main['rtmedia_empty_comment_msg']                  = __( 'Empty comment is not allowed.', 'buddypress-media' );
		$rtmedia_main['rtmedia_media_delete_confirmation']          = __( 'Are you sure you want to delete this media?', 'buddypress-media' );
		$rtmedia_main['rtmedia_media_comment_delete_confirmation']  = __( 'Are you sure you want to delete this comment?', 'buddypress-media' );
		$rtmedia_main['rtmedia_album_delete_confirmation']          = __( 'Are you sure you want to delete this Album?', 'buddypress-media' );
		$rtmedia_main['rtmedia_drop_media_msg']                     = __( 'Drop files here', 'buddypress-media' );
		$rtmedia_main['rtmedia_album_created_msg']                  = ' ' . __( 'album created successfully.', 'buddypress-media' );
		$rtmedia_main['rtmedia_something_wrong_msg']                = __( 'Something went wrong. Please try again.', 'buddypress-media' );
		$rtmedia_main['rtmedia_empty_album_name_msg']               = __( 'Enter an album name.', 'buddypress-media' );
		$rtmedia_main['rtmedia_max_file_msg']                       = __( 'Max file Size Limit: ', 'buddypress-media' );
		$rtmedia_main['rtmedia_allowed_file_formats']               = __( 'Allowed File Formats', 'buddypress-media' );
		$rtmedia_main['rtmedia_select_all_visible']                 = __( 'Select All Visible', 'buddypress-media' );
		$rtmedia_main['rtmedia_unselect_all_visible']               = __( 'Unselect All Visible', 'buddypress-media' );
		$rtmedia_main['rtmedia_no_media_selected']                  = __( 'Please select some media.', 'buddypress-media' );
		$rtmedia_main['rtmedia_selected_media_delete_confirmation'] = __( 'Are you sure you want to delete the selected media?', 'buddypress-media' );
		$rtmedia_main['rtmedia_selected_media_move_confirmation']   = __( 'Are you sure you want to move the selected media?', 'buddypress-media' );
		$rtmedia_main['rtmedia_waiting_msg']                        = __( 'Waiting', 'buddypress-media' );
		$rtmedia_main['rtmedia_uploaded_msg']                       = __( 'Uploaded', 'buddypress-media' );
		$rtmedia_main['rtmedia_uploading_msg']                      = __( 'Uploading', 'buddypress-media' );
		$rtmedia_main['rtmedia_upload_failed_msg']                  = __( 'Failed', 'buddypress-media' );
		$rtmedia_main['rtmedia_close']                              = __( 'Close', 'buddypress-media' );
		$rtmedia_main['rtmedia_edit']                               = __( 'Edit', 'buddypress-media' );
		$rtmedia_main['rtmedia_delete']                             = __( 'Delete', 'buddypress-media' );
		$rtmedia_main['rtmedia_edit_media']                         = __( 'Edit Media', 'buddypress-media' );
		$rtmedia_main['rtmedia_remove_from_queue']                  = __( 'Remove from queue', 'buddypress-media' );
		$rtmedia_main['rtmedia_add_more_files_msg']                 = __( 'Add more files', 'buddypress-media' );
		$rtmedia_main['rtmedia_file_extension_error_msg']           = __( 'File not supported', 'buddypress-media' );
		$rtmedia_main['rtmedia_more']                               = __( 'more', 'buddypress-media' );
		$rtmedia_main['rtmedia_less']                               = __( 'less', 'buddypress-media' );
		$rtmedia_main['rtmedia_read_more']                          = __( 'Read more', 'buddypress-media' );
		$rtmedia_main['rtmedia__show_less']                         = __( 'Show less', 'buddypress-media' );
		$rtmedia_main['rtmedia_activity_text_with_attachment']      = apply_filters( 'rtmedia_required_activity_text_with_attachment', 'disable' );
		$rtmedia_main['rtmedia_delete_uploaded_media']              = __( 'This media is uploaded. Are you sure you want to delete this media?', 'buddypress-media' );
		$rtmedia_main['rtm_wp_version']                             = get_bloginfo( 'version' );

		$rtmedia_backbone['rMedia_loading_media'] = RTMEDIA_URL . 'app/assets/admin/img/boxspinner.gif';

		$rtmedia_media_thumbs = array();
		foreach ( $this->allowed_types as $key_type => $value_type ) {
			$rtmedia_media_thumbs[ $key_type ] = $value_type['thumbnail'];
		}

		/**
		 * Filter to add docs and files thumbnails.
		 *
		 * This filter is used by only rtmedia-docs-files addon.
		 * */
		$rtmedia_media_thumbs = apply_filters( 'rtmedia_add_docs_thumbs', $rtmedia_media_thumbs );

		wp_localize_script( 'rtmedia-backbone', 'rtmedia_media_thumbs', $rtmedia_media_thumbs );
		$rtmedia_backbone['rtmedia_set_featured_image_msg']   = __( 'Featured media set successfully.', 'buddypress-media' );
		$rtmedia_backbone['rtmedia_unset_featured_image_msg'] = __( 'Featured media removed successfully.', 'buddypress-media' );

		wp_localize_script(
			'rtmedia-backbone',
			'rtmedia_edit_media_info_upload',
			array(
				'title'       => esc_html__( 'Title:', 'buddypress-media' ),
				'description' => esc_html__( 'Description:', 'buddypress-media' ),
			)
		);

		$rtmedia_backbone['rtmedia_no_media_found'] = __( 'Oops !! There\'s no media found for the request !!', 'buddypress-media' );

		// Localizing strings for rtMedia.backbone.js.
		$rtmedia_backbone_strings = array(
			'rtm_edit_file_name' => esc_html__( 'Edit File Name', 'buddypress-media' ),
		);

		// Localise fot rtmedia-backcone js.
		wp_localize_script( 'rtmedia-backbone', 'rtmedia_bp', array( 'bp_template_pack' => $bp_template ) );

		wp_localize_script( 'rtmedia-backbone', 'rtmedia_backbone_strings', $rtmedia_backbone_strings );

		// Localizing strings for rtMedia.js.
		$rtmedia_main_js_strings = array(
			'rtmedia_albums'         => esc_html__( 'Albums', 'buddypress-media' ),
			'privacy_update_success' => esc_html__( 'Privacy updated successfully.', 'buddypress-media' ),
			'privacy_update_error'   => esc_html__( 'Couldn\'t change privacy, please try again.', 'buddypress-media' ),
		);

		wp_localize_script( 'rtmedia-main', 'rtmedia_main_js_strings', $rtmedia_main_js_strings );

		// Enqueue touchswipe.
		wp_enqueue_script( 'rtmedia-touchswipe', RTMEDIA_URL . 'lib/touchswipe/jquery.touchSwipe.min.js', array( 'jquery' ), RTMEDIA_VERSION, true );

		if ( isset( $rtmedia->options ) && isset( $rtmedia->options['general_masonry_layout'] ) && 1 === intval( $rtmedia->options['general_masonry_layout'] ) ) {
			if ( wp_script_is( 'jquery-masonry', 'registered' ) ) {
				wp_enqueue_style( 'jquery-masonry' );
				wp_enqueue_script( 'jquery-masonry' );
				$rtmedia_main['rtmedia_masonry_layout'] = 'true';

				if ( isset( $rtmedia->options ) && isset( $rtmedia->options['general_masonry_layout_activity'] ) && 1 === intval( $rtmedia->options['general_masonry_layout_activity'] ) ) {
					$rt_media_main['rtmedia_masonry_layout_activity'] = 'true';
				} else {
					$rtmedia_main['rtmedia_masonry_layout_activity'] = 'false';

				}
			} else {
				$rtmedia_main['rtmedia_masonry_layout'] = 'false';
			}
		} else {
			$rtmedia_main['rtmedia_masonry_layout'] = 'false';
		}

		if ( ! empty( $rtmedia->options['general_display_media'] ) ) {
			$rtmedia_backbone['rtmedia_load_more_or_pagination'] = (string) $rtmedia->options['general_display_media'];
		} else {
			$rtmedia_backbone['rtmedia_load_more_or_pagination'] = 'load_more';
		}

		if ( ! empty( $rtmedia->options['buddypress_enableOnActivity'] ) ) {
			$rtmedia_backbone['rtmedia_bp_enable_activity'] = (string) $rtmedia->options['buddypress_enableOnActivity'];
		} else {
			$rtmedia_backbone['rtmedia_bp_enable_activity'] = '0';
		}

		$rtmedia_backbone['rtmedia_upload_progress_error_message'] = __( 'There are some uploads in progress. Do you want to cancel them?', 'buddypress-media' );
		// Added to display error message when all media types upload are disabled.
		$rtmedia_backbone['rtmedia_media_disabled_error_message'] = __( 'Media upload is disabled. Please Enable at least one media type to proceed.', 'buddypress-media' );

		// localise media size config.
		$media_size_config = array(
			'photo'    => array(
				'thumb'  => array(
					'width'  => $rtmedia->options['defaultSizes_photo_thumbnail_width'],
					'height' => $rtmedia->options['defaultSizes_photo_thumbnail_height'],
					'crop'   => $rtmedia->options['defaultSizes_photo_thumbnail_crop'],
				),
				'medium' => array(
					'width'  => $rtmedia->options['defaultSizes_photo_medium_width'],
					'height' => $rtmedia->options['defaultSizes_photo_medium_height'],
					'crop'   => $rtmedia->options['defaultSizes_photo_medium_crop'],
				),
				'large'  => array(
					'width'  => $rtmedia->options['defaultSizes_photo_large_width'],
					'height' => $rtmedia->options['defaultSizes_photo_large_height'],
					'crop'   => $rtmedia->options['defaultSizes_photo_large_crop'],
				),
			),
			'video'    => array(
				'activity_media' => array(
					'width'  => $rtmedia->options['defaultSizes_video_activityPlayer_width'],
					'height' => $rtmedia->options['defaultSizes_video_activityPlayer_height'],
				),
				'single_media'   => array(
					'width'  => $rtmedia->options['defaultSizes_video_singlePlayer_width'],
					'height' => $rtmedia->options['defaultSizes_video_singlePlayer_height'],
				),
			),
			'music'    => array(
				'activity_media' => array(
					'width' => $rtmedia->options['defaultSizes_music_activityPlayer_width'],
				),
				'single_media'   => array(
					'width' => $rtmedia->options['defaultSizes_music_singlePlayer_width'],
				),
			),
			'featured' => array(
				'default' => array(
					'width'  => $rtmedia->options['defaultSizes_featured_default_width'],
					'height' => $rtmedia->options['defaultSizes_featured_default_height'],
					'crop'   => $rtmedia->options['defaultSizes_featured_default_crop'],
				),
			),
		);
		wp_localize_script( 'rtmedia-main', 'rtmedia_media_size_config', $media_size_config );

		// rtMedia fot comment media script localize.
		$request_uri = rtm_get_server_var( 'REQUEST_URI', 'FILTER_SANITIZE_URL' );
		$url         = rtmedia_get_upload_url( $request_uri );

		$params = array(
			'url'                 => $url,
			'runtimes'            => 'html5,flash,html4',
			'browse_button'       => 'rtmedia-comment-media-upload',
			'container'           => 'rtmedia-comment-media-upload-container',
			'filters'             => apply_filters(
				'rtmedia_plupload_files_filter',
				array(
					array(
						'title'      => esc_html__( 'Media Files', 'buddypress-media' ),
						'extensions' => get_rtmedia_allowed_upload_type(),
					),
				)
			),
			'max_file_size'       => ( wp_max_upload_size() ) / ( 1024 * 1024 ) . 'M',
			'multipart'           => true,
			'urlstream_upload'    => true,
			'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
			'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
			'file_data_name'      => 'rtmedia_file', // key passed to $_FILE.
			'multi_selection'     => false,
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
			'max_file_size_msg'   => apply_filters(
				'rtmedia_plupload_file_size_msg',
				min(
					array(
						ini_get( 'upload_max_filesize' ),
						ini_get( 'post_max_size' ),
					)
				)
			),
		);

		$params = apply_filters( 'rtmedia_modify_upload_params', $params );

		global $rtmedia;
		$rtmedia_extns = array();
		foreach ( $rtmedia->allowed_types as $allowed_types_key => $allowed_types_value ) {
			$rtmedia_extns[ $allowed_types_key ] = $allowed_types_value['extn'];
		}

		$rtmedia_disable_media = '1';
		// if the  rtmedia option does have value pick from there.
		if ( isset( $rtmedia->options['rtmedia_disable_media_in_commented_media'] ) ) {
			$rtmedia_disable_media = $rtmedia->options['rtmedia_disable_media_in_commented_media'];
		}
		$rtmedia_main['rtmedia_disable_media_in_commented_media'] = $rtmedia_disable_media;

		$rtmedia_main['rtmedia_disable_media_in_commented_media_text'] = __( 'Adding media in Comments is not allowed', 'buddypress-media' );

		wp_localize_script( 'rtmedia-backbone', 'rtmedia_exteansions', $rtmedia_extns );
		wp_localize_script( 'rtmedia-backbone', 'rtMedia_update_plupload_comment', $params );

		$rtmedia_backbone['rMedia_loading_file'] = admin_url( '/images/loading.gif' );

		// Check if BuddyPress plugin is not activated.
		$is_buddypress_activate = rtm_is_buddypress_activate();
		if ( empty( $is_buddypress_activate ) ) {
			$rtmedia_main['ajaxurl'] = admin_url( 'admin-ajax.php', is_ssl() ? 'admin' : 'http' );
		}

		$options = $rtmedia->options;
		// Previously done with rtmedia_custom_css() method on wp_head hook.
		if ( ! empty( $options['styles_custom'] ) ) {
			wp_register_style( 'rtmedia-custom-css', false );
			wp_enqueue_style( 'rtmedia-custom-css' );
			$css = stripslashes( wp_filter_nohtml_kses( $options['styles_custom'] ) );
			wp_add_inline_style( 'rtmedia-custom-css', $css );
		}

		if ( is_plugin_active( 'rtmedia-direct-download-link/index.php' ) ) {
			if ( array_key_exists( 'allowedTypes_video_download', $this->options ) ) {
				$rtmedia_main['rtmedia_direct_download_link'] = $this->options['allowedTypes_video_download'];
			}
		}

		wp_localize_script( 'rtmedia-main', 'rtmedia_main', $rtmedia_main );
		wp_localize_script( 'rtmedia-backbone', 'rtmedia_backbone', $rtmedia_backbone );

	}

	/**
	 * Function to remove BuddyPress account menu from admin bar.
	 */
	public function set_bp_bar() {
		remove_action( 'bp_adminbar_menus', 'bp_adminbar_account_menu', 4 );
	}

	/**
	 * Function to set friends Object.
	 */
	public function set_friends_object() {
		// todo: This function should return value or pass argument.
		if ( is_user_logged_in() ) {
			$user    = get_current_user_id();
			$friends = friends_get_friend_user_ids( $user );
		} else {
			$user = 0;
		}
	}

	/**
	 * Function to change image size details array.
	 *
	 * @param array $sizes Image sizes array.
	 *
	 * @return array|mixed
	 */
	public function filter_image_sizes_details( $sizes ) {

		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
		$id      = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

		if ( isset( $post_id ) ) {
			$sizes = $this->unset_bp_media_image_sizes_details( $sizes );
		} elseif ( isset( $id ) ) {

			// For Regenerate Thumbnails Plugin.
			$model  = new RTMediaModel();
			$result = $model->get( array( 'media_id' => intval( wp_unslash( $id ) ) ) );

			if ( ! empty( $result ) ) {
				$bp_media_sizes = $this->image_sizes();
				$sizes          = array(
					'rt_media_thumbnail'      => $bp_media_sizes['thumbnail'],
					'rt_media_activity_image' => $bp_media_sizes['activity'],
					'rt_media_single_image'   => $bp_media_sizes['single'],
					'rt_media_featured_image' => $bp_media_sizes['featured'],
				);
			} else {
				$sizes = $this->unset_bp_media_image_sizes_details( $sizes );
			}
		}

		return $sizes;
	}

	/**
	 * Add custom image sizes for size choose options.
	 *
	 * @param array $sizes Image sizes array.
	 *
	 * @return array
	 */
	public function rtmedia_custom_image_sizes_choose( $sizes ) {
		$custom_sizes = array(
			'rt_media_thumbnail'      => 'rtMedia Thumbnail',
			'rt_media_activity_image' => 'rtMedia Activity Image',
			'rt_media_single_image'   => 'rtMedia Single Image',
			'rt_media_featured_image' => 'rtMedia Fetured Image',
		);

		return array_merge( $sizes, $custom_sizes );
	}

	/**
	 * Filter BuddyPress media image sizes to unset sizes.
	 *
	 * @param array $sizes Image sizes array.
	 *
	 * @return array|mixed
	 */
	public function filter_image_sizes( $sizes ) {
		$post_id = filter_input( INPUT_POST, 'postid', FILTER_VALIDATE_INT );

		// For Regenerate Thumbnails Plugin.
		if ( ! empty( $post_id ) ) {

			$parent_id = get_post_field( 'post_parent', $post_id );
			if ( ! empty( $parent_id ) ) {
				$post_type = get_post_field( 'post_type', $parent_id );

				if ( 'rtmedia_album' === $post_type ) {
					$sizes = array(
						'rt_media_thumbnail',
						'rt_media_activity_image',
						'rt_media_single_image',
						'rt_media_featured_image',
					);
				} else {
					$sizes = $this->unset_bp_media_image_sizes( $sizes );
				}
			} else {
				$sizes = $this->unset_bp_media_image_sizes( $sizes );
			}
		}

		return $sizes;
	}

	/**
	 * Unset BuddyPress media image sizes details.
	 *
	 * @param array $sizes Image sizes array to unset.
	 *
	 * @return mixed
	 */
	public function unset_bp_media_image_sizes_details( $sizes ) {
		if ( isset( $sizes['rt_media_thumbnail'] ) ) {
			unset( $sizes['rt_media_thumbnail'] );
		}
		if ( isset( $sizes['rt_media_activity_image'] ) ) {
			unset( $sizes['rt_media_activity_image'] );
		}
		if ( isset( $sizes['rt_media_single_image'] ) ) {
			unset( $sizes['rt_media_single_image'] );
		}
		if ( isset( $sizes['rt_media_featured_image'] ) ) {
			unset( $sizes['rt_media_featured_image'] );
		}

		return $sizes;
	}

	/**
	 * Unset BuddyPress media image sizes.
	 *
	 * @param array $sizes Image sizes array to unset.
	 *
	 * @return mixed
	 */
	public function unset_bp_media_image_sizes( $sizes ) {

		$rt_media_thumbnail_key = array_search( 'rt_media_thumbnail', $sizes, true );
		if ( false !== $rt_media_thumbnail_key ) {
			unset( $sizes[ $rt_media_thumbnail_key ] );
		}

		$rt_media_activity_image_key = array_search( 'rt_media_activity_image', $sizes, true );
		if ( false !== $rt_media_activity_image_key ) {
			unset( $sizes[ $rt_media_activity_image_key ] );
		}

		$rt_media_single_image_key = array_search( 'rt_media_single_image', $sizes, true );
		if ( false !== $rt_media_single_image_key ) {
			unset( $sizes[ $rt_media_single_image_key ] );
		}

		$rt_media_featured_image_key = array_search( 'rt_media_featured_image', $sizes, true );
		if ( false !== $rt_media_featured_image_key ) {
			unset( $sizes[ $rt_media_featured_image_key ] );
		}

		return $sizes;
	}

	/**
	 * Function to expand allowed html in wp_kses_allowed_html.
	 *
	 * @return array
	 */
	public static function expanded_allowed_tags() {
		$new_allowed = wp_kses_allowed_html( 'post' );

		// Iframe.
		$new_allowed['iframe'] = array(
			'src'   => array(),
			'class' => array(),
			'id'    => array(),
		);

		// Script, for social sync plugin.
		$new_allowed['script'] = array(
			'type'  => array(),
			'class' => array(),
			'id'    => array(),
			'src'   => array(),
		);

		// form input.
		$new_allowed['form'] = array(
			'action' => array(),
			'id'     => array(),
			'name'   => array(),
			'class'  => array(),
			'method' => array(),
		);

		// form fields - input.
		$new_allowed['input'] = array(
			'class'       => array(),
			'id'          => array(),
			'name'        => array(),
			'value'       => array(),
			'type'        => array(),
			'onclick'     => array(),
			'min'         => array(),
			'max'         => array(),
			'step'        => array(),
			'style'       => array(),
			'placeholder' => array(),
			'checked'     => array(),
			'data-*'      => true,
		);

		// select.
		$new_allowed['select'] = array(
			'class' => array(),
			'id'    => array(),
			'name'  => array(),
			'value' => array(),
			'type'  => array(),
		);

		// select options.
		$new_allowed['option'] = array(
			'selected' => array(),
			'value'    => array(),
			'class'    => array(),
			'id'       => array(),
			'name'     => array(),
		);

		// select optgroup.
		$new_allowed['optgroup'] = array(
			'label' => array(),
			'value' => array(),
		);

		// audio.
		$new_allowed['audio'] = array_merge(
			$new_allowed['audio'],
			array(
				'width'  => array(),
				'height' => array(),
				'type'   => array(),
			)
		);

		// video.
		$new_allowed['video'] = array_merge(
			$new_allowed['video'],
			array(
				'type' => array(),
			)
		);

		// h2.
		$new_allowed['h2'] = array_merge(
			$new_allowed['h2'],
			array(
				'name' => array(),
			)
		);

		// div.
		$new_allowed['div'] = array_merge(
			$new_allowed['div'],
			array(
				'name' => array(),
			)
		);

		return $new_allowed;
	}

	/**
	 * Add extra inline allowed style.
	 *
	 * @param array $styles Allowed styles.
	 *
	 * @return array
	 */
	public static function allow_display_in_style( $styles ) {
		$styles[] = 'display';

		return $styles;
	}
}

/**
 * Function to get parent link of global album.
 *
 * @param int $id Id to get permalink.
 *
 * @return string
 */
function parentlink_global_album( $id ) {
	$global_albums = RTMediaAlbum::get_globals();
	$parent_link   = '';

	if ( is_array( $global_albums ) && '' !== $global_albums ) {
		if ( in_array( $id, $global_albums, false ) && function_exists( 'bp_displayed_user_id' ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.FoundNonStrictFalse -- This option sometimes comes from buddypress or normal options, so can't be sure.
			$disp_user = bp_displayed_user_id();
			$curr_user = get_current_user_id();
			if ( $disp_user === $curr_user ) {
				$parent_link = get_rtmedia_user_link( $curr_user );
			} else {
				$parent_link = get_rtmedia_user_link( $disp_user );
			}
			global $rtmedia_query;
			if ( isset( $rtmedia_query->is_gallery_shortcode ) && true === $rtmedia_query->is_gallery_shortcode ) {
				$parent_link = get_rtmedia_user_link( get_current_user_id() );
			}
		}
	}

	return $parent_link;
}

/**
 * Get media permalink.
 *
 * @param int $id Id to get permalink.
 *
 * @return mixed|void
 */
function get_rtmedia_permalink( $id ) {

	$media_model = new RTMediaModel();
	$media       = $media_model->get( array( 'id' => intval( $id ) ) );
	global $rtmedia_query;

	// Adding filter to get permalink for current blog.
	add_filter( 'bp_get_root_domain', 'rtmedia_get_current_blog_url' );

	if ( ! empty( $media ) && is_object( $media[0] ) && ! isset( $media[0]->context ) ) {
		if ( function_exists( 'bp_get_groups_root_slug' ) && isset( $rtmedia_query->query ) && isset( $rtmedia_query->query['context'] ) && 'group' === $rtmedia_query->query['context'] ) {
			$parent_link = get_rtmedia_group_link( $rtmedia_query->query['context_id'] );
		} else {
			// check for global album.
			$parent_link = parentlink_global_album( $id );
			if ( '' === $parent_link ) {
				$parent_link = get_rtmedia_user_link( $media[0]->media_author );
			}
		}
	} else {
		if ( isset( $media[0]->context ) && function_exists( 'bp_get_groups_root_slug' ) && 'group' === $media[0]->context ) {
			$parent_link = get_rtmedia_group_link( $media[0]->context_id );
		} else {
			// check for global album.
			$parent_link = parentlink_global_album( $id );
			if ( '' === $parent_link && isset( $media[0]->media_author ) ) {
							$parent_link = get_rtmedia_user_link( $media[0]->media_author );
			}
		}
	}

	$parent_link = trailingslashit( $parent_link );

	// Removing filter so that doesn't affect other calls to this function.
	remove_filter( 'bp_get_root_domain', 'rtmedia_get_current_blog_url' );

	$permalink = trailingslashit( $parent_link . RTMEDIA_MEDIA_SLUG . '/' . $id );

	/**
	 * Filters the rtmedia permalink for a media list.
	 *
	 * @since 4.2.2
	 *
	 * @param string  $permalink The rtmedia's permalink.
	 * @param object  $media     The media in question.
	 * @param int     $id        ID of the media.
	 */

	return apply_filters( 'get_rtmedia_permalink', $permalink, $media, $id );
}

/**
 * Function to get user posts link from given ID.
 *
 * @param int $id User id to get link.
 *
 * @return string
 */
function get_rtmedia_user_link( $id ) {
	if ( function_exists( 'bp_core_get_user_domain' ) ) {
		$parent_link = bp_core_get_user_domain( $id );
	} else {
		$parent_link = get_author_posts_url( $id );
	}

	return $parent_link;
}

/**
 * Function to update site option based on site type.
 *
 * @param string $option_name Option name to update.
 * @param mixed  $option_value Option value to update option with.
 *
 * @return bool
 */
function rtmedia_update_site_option( $option_name, $option_value ) {
	if ( is_multisite() ) {
		return update_option( $option_name, $option_value );
	} else {
		return update_site_option( $option_name, $option_value );
	}
}

/**
 * Function to get permalink for specified group id.
 *
 * @param int $group_id Group to id to get link.
 *
 * @return mixed|void
 */
function get_rtmedia_group_link( $group_id ) {
	$group = groups_get_group( array( 'group_id' => $group_id ) );

	return apply_filters( 'rtmedia_get_group_link', bp_get_group_permalink( $group ) );
}

/**
 * Function to get options value for rtMedia.
 *
 * @param string      $option_name Name of option to retrieve.
 * @param bool|string $default Default value to return if the option does not exist.
 *
 * @return bool|mixed|void
 */
function rtmedia_get_site_option( $option_name, $default = false ) {
	if ( is_multisite() ) {
		$return_val = get_option( $option_name, $default );
		if ( false === $return_val ) {
			$return_val = get_site_option( $option_name, $default );
			if ( false === $return_val ) {
				if ( function_exists( 'bp_get_option' ) ) {
					$return_val = bp_get_option( $option_name, $default );
				}
			}
			rtmedia_update_site_option( $option_name, $return_val );
		}
	} else {
		$return_val = get_site_option( $option_name, $default );
		if ( false === $return_val ) {
			if ( function_exists( 'bp_get_option' ) ) {
				$return_val = bp_get_option( $option_name, $default );
				rtmedia_update_site_option( $option_name, $return_val );
			}
		}
	}
	if ( false !== $default && false === $return_val ) {
		$return_val = $default;
	}

	return $return_val;
}

/**
 * Function to show privacy message provided from rtMedia settings in front end.
 */
function rtm_privacy_message_on_website() {
	global $rtmedia;
	$options = $rtmedia->options;

	$rtm_privacy_message_options = array(
		'background-color' => 'rgba(0,0,0,0.95)',
		'color'            => '#fff',
		'position'         => 'bottom',
	);

	$rtm_privacy_message_options = apply_filters( 'rtm_privacy_bar_position', $rtm_privacy_message_options );

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! is_plugin_active( 'rtmedia-upload-terms/index.php' ) ) {
		if ( ! empty( $options['general_upload_terms_show_pricacy_message'] ) && '1' === $options['general_upload_terms_show_pricacy_message'] && empty( $_COOKIE['rtm_show_privacy_message'] ) ) {
			$rtm_privacy_allowed_position = array( 'top', 'bottom' );
			$rtm_privacy_message_position = ! empty( $rtm_privacy_message_options['position'] ) && ( in_array( $rtm_privacy_message_options['background-color'], $rtm_privacy_allowed_position, true ) ) ? $rtm_privacy_message_options['position'] . ':0' : 'bottom: 0';
			$rtm_privacy_message_bgcolor  = ! empty( $rtm_privacy_message_options['background-color'] ) ? 'background-color: ' . $rtm_privacy_message_options['background-color'] : 'background-color: rgba(0,0,0,0.95)';
			$rtm_privacy_message_color    = ! empty( $rtm_privacy_message_options['color'] ) ? 'color: ' . $rtm_privacy_message_options['color'] : 'color: #fff';

			$rtm_privacy_style = $rtm_privacy_message_position . '; ' . $rtm_privacy_message_bgcolor . '; ' . $rtm_privacy_message_color . ';';

			?>
			<div class='privacy_message_wrapper' style='<?php echo esc_attr( $rtm_privacy_style ); ?>' >
				<p><?php echo wp_kses_post( $options['general_upload_terms_privacy_message'] ); ?></p>
				<span class='dashicons dashicons-no' id='close_rtm_privacy_message'></span>
			</div>
			<?php
		}
	}
}
add_action( 'wp_footer', 'rtm_privacy_message_on_website' );

/**
 * Function to add privacy policy information in WordPress policy section.
 */
function rtm_plugin_privacy_information() {

	if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
		ob_start();
		?>
		<p>We collect your information during the checkout process on your purchase. The information collected from you may include, but is not limited to, your name, billing address, shipping address, email address, phone number, credit card/payment details and any other details that might be requested from you for the purpose of processing.</p>
		<h2>Handling this data will also allow us to:</h2>
		<p>- Send you important service information.<br/>
		- Respond to your queries or complaints.<br/>
		- Set up and administer your account, provide technical and/or customer support, and to verify your identity.</p>
		<h2>Additionally we may also collect the following information:</h2>
		<p>- Your comments and product reviews if you choose to leave them on our website.
		- Account email/password to allow you to access your account, if you have one.
		- If you choose to create an account with us, your name, address, and email address, which will be used to populate the checkout for future orders.</p>
		<?php
		$policy = ob_get_clean();
		wp_add_privacy_policy_content(
			__( 'rtMedia', 'buddypress-media' ),
			wp_kses_post( $policy )
		);
	}
}

add_action( 'admin_init', 'rtm_plugin_privacy_information' );

/**
 * This wraps up the main rtMedia class. Three important notes:
 *
 * 1. All the constants can be overridden.
 *    So, you could use, 'portfolio' instead of 'media'
 * 2. The default thumbnail and display sizes can be filtered
 *    using 'bpmedia_media_sizes' hook
 * 3. The excerpts and string sizes can be filtered
 *    using 'bpmedia_excerpt_lengths' hook
 */
