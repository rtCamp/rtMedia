<?php
/**
 * Handles Media tags.
 *
 * @package rtMedia
 */

/**
 * Handles Media tags.
 */
class RTMediaTags {

	/**
	 * A new instance of the getid3 class
	 *
	 * @var object
	 */
	private static $_id3;

	/**
	 * File to analyze
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Holds a copy of the variable $_id3
	 *
	 * @var object
	 */
	private $id3;

	/**
	 * Key and value of analyzed file
	 *
	 * @var array
	 */
	private $data = null;

	/**
	 * Media duration.
	 *
	 * @var array
	 */
	private $duration_info = array( 'duration' );

	/**
	 * Media tags.
	 *
	 * @var array
	 */
	private $tags = array(
		'title',
		'artist',
		'album',
		'year',
		'genre',
		'comment',
		'track',
		'track_total',
		'attached_picture',
		'image',
	);

	/**
	 * Readonly tags.
	 *
	 * @var array
	 */
	private $readonly_tags = array( 'track_total', 'attached_picture', 'image' );

	/**
	 * RTMediaTags constructor.
	 *
	 * @param string $file File path.
	 *
	 * @throws getid3_exception Exception while initializing id3.
	 */
	public function __construct( $file ) {

		$this->file = $file;
		$this->id3  = self::id3();
	}

	/**
	 * Change file path.
	 *
	 * @param string $file File path.
	 */
	public function update_filepath( $file ) {

		$this->file = $file;
	}

	/**
	 * Writes data inside  the files after manipulation, mainly mp3 files.
	 *
	 * @return bool|WP_Error
	 * @throws Exception Exception while adding data.
	 */
	public function save() {

		include_once trailingslashit( RTMEDIA_PATH ) . 'lib/getid3/write.php';

		$tagwriter                    = new getid3_writetags();
		$tagwriter->filename          = $this->file;
		$tagwriter->tag_encoding      = 'UTF-8';
		$tagwriter->tagformats        = array( 'id3v2.3', 'id3v1' );
		$tagwriter->overwrite_tags    = true;
		$tagwriter->remove_other_tags = true;

		$tagwriter->tag_data = $this->data;

		// write tags.
		try {
			if ( $tagwriter->WriteTags() ) {
				return true;
			}
			throw new Exception( implode( ' : ', $tagwriter->errors ) );
		} catch ( Exception $e ) {
			return new WP_Error( 'tag_write_error', $e->getMessage() );
		}
	}

	/**
	 * Initialize the getid3 class
	 *
	 * @return getID3|object
	 * @throws getid3_exception Exception while initializing getID3.
	 */
	public static function id3() {

		include_once trailingslashit( RTMEDIA_PATH ) . 'lib/getid3/getid3.php';

		if ( ! self::$_id3 ) {
			self::$_id3 = new getID3();
		}

		return self::$_id3;
	}


	/**
	 * Sets cover art for mp3 files
	 *
	 * @param array  $data Image data.
	 * @param string $mime Image mime type.
	 * @param string $description Description of image.
	 */
	public function set_art( $data, $mime = 'jpeg', $description = 'Description' ) {

		if ( null === $this->data ) {
			$this->analyze();
		}

		$this->data['attached_picture'] = array();

		$this->data['attached_picture'][0]['data']          = $data;
		$this->data['attached_picture'][0]['picturetypeid'] = 0x03; // 'Cover (front)'.
		$this->data['attached_picture'][0]['description']   = $description;
		$this->data['attached_picture'][0]['mime']          = 'image/' . $mime;
	}

	/**
	 * Get tag value.
	 *
	 * @param string $key Tag key.
	 *
	 * @return array|mixed|null
	 * @throws Exception Unknown tag for class.
	 */
	public function __get( $key ) {

		if ( ! in_array( $key, $this->tags, true ) && ! in_array( $key, $this->duration_info, true ) && ! isset( $this->duration_info[ $key ] ) ) {
			throw new Exception( "Unknown property '$key' for class '" . __class__ . "'" );
		}

		if ( null === $this->data ) {
			$this->analyze();
		}

		if ( 'image' === $key ) {
			return isset( $this->data['attached_picture'] ) ? array(
				'data' => $this->data['attached_picture'][0]['data'],
				'mime' => $this->data['attached_picture'][0]['mime'],
			) : null;
		} else {
			if ( isset( $this->duration_info[ $key ] ) ) {
				return $this->duration_info[ $key ];
			} else {
				if ( ! empty( $this->data[ $key ] ) && ! empty( $this->data[ $key ][0] ) ) {
					return $this->data[ $key ][0];
				}
				return null;
			}
		}
	}

	/**
	 * Set tag to album.
	 *
	 * @param string $key Tag string.
	 * @param string $value Tag value.
	 *
	 * @throws Exception Exception for read only tags.
	 */
	public function __set( $key, $value ) {

		if ( ! in_array( $key, $this->tags, true ) ) {
			throw new Exception( "Unknown property '$key' for class '" . __class__ . "'" );
		}
		if ( in_array( $key, $this->readonly_tags, true ) ) {
			throw new Exception( "Tying to set readonly property '$key' for class '" . __class__ . "'" );
		}

		if ( null === $this->data ) {
			$this->analyze();
		}

		$this->data[ $key ] = array( $value );
	}


	/**
	 * Analyze file
	 */
	private function analyze() {

		$array_ext               = array( 'ogg', 'm4a', 'mp4', 'webm' );
		$path_parts              = pathinfo( $this->file );
		$path_parts['extension'] = $path_parts['extension'] ? $path_parts['extension'] : false;

		$data = $this->id3->analyze( $this->file );

		$this->duration_info = array( 'duration' => isset( $data['playtime_string'] ) ? ( $data['playtime_string'] ) : '-:--' );

		if ( ! in_array( $path_parts['extension'], $array_ext, true ) && ! empty( $data['tags']['id3v2'] ) ) {
			$this->data = isset( $data['tags'] ) ? array_intersect_key( $data['tags']['id3v2'], array_flip( $this->tags ) ) : array();
		}

		if ( isset( $data['id3v2']['APIC'] ) ) {
			$this->data['attached_picture'] = array( $data['id3v2']['APIC'][0] );
		}

		if ( isset( $data['tags']['id3v2']['track_number'] ) ) {
			$track = $data['tags']['id3v2']['track_number'][0];
		} else {
			if ( isset( $data['tags']['id3v1']['track'] ) ) {
				$track = $data['tags']['id3v1']['track'][0];
			} else {
				$track = null;
			}
		}

		if ( strstr( $track, '/' ) ) {
			list( $track, $track_total ) = explode( '/', $track );
			$this->data['track_total']   = array( $track_total );
		}

		$this->data['track'] = array( $track );

	}
}
