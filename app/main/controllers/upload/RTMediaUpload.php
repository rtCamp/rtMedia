<?php
/**
 * Handles media upload.
 *
 * @package rtMedia
 * @author joshua
 */

/**
 * Controller class to upload the media
 */
class RTMediaUpload {

	/**
	 * Media upload modes.
	 *
	 * @var array $default_modes
	 */
	private $default_modes = array( 'file_upload', 'link_input' );

	/**
	 * Uploaded file object.
	 *
	 * @var RTMediaUploadFile|null
	 */
	public $file = null;

	/**
	 * Media object.
	 *
	 * @var RTMediaMedia|null
	 */
	public $media = null;

	/**
	 * Media url.
	 *
	 * @var RTMediaUploadUrl|null
	 */
	public $url = null;

	/**
	 * Uploaded media ids.
	 *
	 * @var array|null
	 */
	public $media_ids = null;

	/**
	 * Static array to hold the allowed tags to be use in wp_kses
	 *
	 * @var array
	 */
	public static $wp_kses_allowed_tags = array(
		'a' => array(
			'id'     => array(),
			'href'   => array(),
			'target' => array(),
		),
		'p' => array(),
	);

	/**
	 * RTMediaUpload constructor.
	 *
	 * @param array $uploaded Uploaded media details.
	 */
	public function __construct( $uploaded ) {
		/**
		 * Prepare to upload a file
		 */
		$this->file = new RTMediaUploadFile( $uploaded );

		/**
		 * Prepare to upload a url
		 */
		$this->url = new RTMediaUploadUrl();

		/**
		 * Prepare media object to populate the album
		 */
		$this->media = new RTMediaMedia();

		/**
		 * Upload the entity according to the mode of request
		 * either file_upload or link_input
		 */
		$file_object = $this->upload( $uploaded );

		/**
		 * If upload successful then populate the rtMedia database and insert the media into album
		 */
		if ( ! is_wp_error( $file_object ) && $file_object && $uploaded ) {

			$this->media_ids = $this->media->add( $uploaded, $file_object );
			do_action( 'rtemdia_after_file_upload_before_activity', $file_object, $this );

			if ( $this->media_ids ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Upload a file or a link input
	 *
	 * @param array $uploaded Uploaded media details.
	 *
	 * @return mixed
	 */
	public function upload( $uploaded ) {

		switch ( $uploaded['mode'] ) {
			case 'file_upload':
				if ( isset( $uploaded['files'] ) ) {
					return $this->file->init( $uploaded['files'] );
				}
				break;
			case 'link_input':
				return $this->url->init( $uploaded );
			default:
				do_action( 'rtmedia_upload_' . $uploaded['mode'], $uploaded );
		}
	}
}
