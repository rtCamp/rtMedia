<?php

/**
 * Description of RTMediaUploadFile
 * Class responsible for uploading a file to the website.
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class RTMediaUploadFile {

	var $files;
	var $fake = false;
	var $uploaded = false;

	function __construct( $uploaded ) {
		$this->uploaded = $uploaded;
	}

	/**
	 * Initialize the upload process
	 *
	 * @param type $files
	 *
	 * @return type
	 */
	function init( $files ) {

		$this->set_file( $files );
		$this->unset_invalid_files();
		$uploaded_file = $this->process();

		return $uploaded_file;
	}

	/**
	 * core process of upload
	 */
	function process() {
		// hook for before file upload process
		do_action( 'rtmedia_before_file_upload_process' );

		include_once( ABSPATH . 'wp-admin/includes/file.php' );
		include_once( ABSPATH . 'wp-admin/includes/image.php' );

		$upload_type = $this->fake ? 'wp_handle_sideload' : 'wp_handle_upload';

		//todo why use $rt_set_filter_uplaod_dir global variable if we can remove filter for upload_dir after upload finish
		global $rt_set_filter_uplaod_dir;
		if ( ! isset( $rt_set_filter_uplaod_dir ) ) {
			add_filter( 'upload_dir', array( $this, 'upload_dir' ) );
			$rt_set_filter_uplaod_dir = true;
		}
		if ( isset( $this->files ) && count( $this->files ) > 0 ) {
			foreach ( $this->files as $key => $file ) {

				$uploaded_file[] = $upload_type ( $file, array( 'test_form' => false ) );
				try {
					if ( isset( $uploaded_file[ $key ]['error'] ) || is_null( $uploaded_file[ $key ] ) ) {
						array_pop( $uploaded_file );

						throw new RTMediaUploadException( 0, esc_html__( 'Error Uploading File', 'buddypress-media' ) );
					}
					$uploaded_file[ $key ]['name'] = $file['name'];
				} catch ( RTMediaUploadException $e ) {
					return new WP_Error( 'upload_error', $e->getMessage() );
				}

				if ( strpos( $file['type'], 'image' ) !== false ) {
					if ( function_exists( 'read_exif_data' ) ) {
						$file = $this->exif( $uploaded_file[ $key ] );
					}
				}
			}

			return $uploaded_file;
		}

		return false;
	}

	function upload_dir( $upload_dir ) {
		global $rtmedia_interaction;
		if ( isset( $this->uploaded['context'] ) && isset( $this->uploaded['context_id'] ) ) {
			if ( 'group' !== $this->uploaded['context'] ) {
				$rtmedia_upload_prefix = 'users/';
				$id                    = apply_filters( 'rtmedia_current_user', get_current_user_id() );
			} else {
				$rtmedia_upload_prefix = 'groups/';
				$id                    = $this->uploaded['context_id'];
			}
		} else {
			if ( 'group' !== $rtmedia_interaction->context->type ) {
				$rtmedia_upload_prefix = 'users/';
				$id                    = apply_filters( 'rtmedia_current_user', get_current_user_id() );
			} else {
				$rtmedia_upload_prefix = 'groups/';
				$id                    = $rtmedia_interaction->context->id;
			}
		}

		$rtmedia_folder_name = apply_filters( 'rtmedia_upload_folder_name', 'rtMedia' );

		if ( strpos( $upload_dir['path'], $rtmedia_folder_name . '/' . $rtmedia_upload_prefix ) === false ) {
			$upload_dir['path'] = trailingslashit( str_replace( $upload_dir['subdir'], '', $upload_dir['path'] ) ) . $rtmedia_folder_name . '/' . $rtmedia_upload_prefix . $id . $upload_dir['subdir'];
			$upload_dir['url']  = trailingslashit( str_replace( $upload_dir['subdir'], '', $upload_dir['url'] ) ) . $rtmedia_folder_name . '/' . $rtmedia_upload_prefix . $id . $upload_dir['subdir'];
		}

		// set dir as per the upload date
		if ( isset( $this->uploaded['date'] ) ) {
			$str_date   = strtotime( $this->uploaded['date'] );
			$year_month = date( 'Y/m', $str_date );

			$upload_dir['path'] = trailingslashit( str_replace( $upload_dir['subdir'], '', $upload_dir['path'] ) ) . $year_month;
			$upload_dir['url']  = trailingslashit( str_replace( $upload_dir['subdir'], '', $upload_dir['url'] ) ) . $year_month;
		}

		$upload_dir = apply_filters( 'rtmedia_filter_upload_dir', $upload_dir, $this->uploaded );

		return $upload_dir;
	}

	function set_file( $files ) {
		/**
		 * if files parameter is provided then take th file details from that object
		 */
		if ( $files ) {
			$this->fake = true;
			$this->populate_file_array( (array) $this->uploaded['files'] );
			/**
			 * otherwise check for $_FILES global object from the form submitted
			 */
		} elseif ( isset( $_FILES['rtmedia_file'] ) ) {
			$this->populate_file_array( $_FILES['rtmedia_file'] );
		} else {
			/**
			 * No files could be found to upload
			 */
			try {
				throw new RTMediaUploadException( UPLOAD_ERR_NO_FILE );
			} catch ( Exception $e ) {
				error_log( $e->getMessage() );
			}
		}
	}

	/**
	 * gather the file information for upload process
	 *
	 * @param type $file_array
	 */
	function populate_file_array( $file_array ) {
		$this->files[] = array(
			'name'     => isset( $file_array['name'] ) ? $file_array['name'] = str_replace( '%', '-', $file_array['name'] ) : '',
			'type'     => isset( $file_array['type'] ) ? $file_array['type'] : '',
			'tmp_name' => isset( $file_array['tmp_name'] ) ? $file_array['tmp_name'] : '',
			'error'    => isset( $file_array['error'] ) ? $file_array['error'] : '',
			'size'     => isset( $file_array['size'] ) ? $file_array['size'] : 0,
		);
	}

	/**
	 * Check for valid file types for rtMedia
	 *
	 * @global type $rtmedia
	 *
	 * @param type $file
	 *
	 * @return boolean
	 * @throws RTMediaUploadException
	 */
	function is_valid_type( $file ) {
		try {
			global $rtmedia;
			$allowed_types          = array();
			$rtmedia->allowed_types = apply_filters( 'rtmedia_allowed_types', $rtmedia->allowed_types );
			foreach ( $rtmedia->allowed_types as $type ) {
				if ( '' !== $type['extn'] && call_user_func( 'is_rtmedia_upload_' . $type['name'] . '_enabled' ) ) {
					foreach ( $type['extn'] as $extn ) {
						$allowed_types[] = $extn;
					}
				}
			}
			$file_data     = wp_check_filetype( $file['name'] );
			$allowed_types = apply_filters( 'rtmedia_plupload_files_filter', array(
				array(
					'title'      => 'Media Files',
					'extensions' => implode( ',', $allowed_types ),
				),
			) );
			$allowed_types = explode( ',', $allowed_types[0]['extensions'] );
			if ( false === in_array( strtolower( $file_data['ext'] ), $allowed_types, true ) ) {
				if ( ! preg_match( '/' . implode( '|', $allowed_types ) . '/i', $file['type'], $result ) || ! isset( $result[0] ) ) {
					throw new RTMediaUploadException( UPLOAD_ERR_EXTENSION );
				}
			}
			//            $is_valid = $this->id3_validate_type($file);
		} catch ( RTMediaUploadException $e ) {
			echo esc_html( $e->getMessage() );

			return false;
		}

		return true;
	}

	/**
	 * Remove invalid files
	 */
	function unset_invalid_files() {
		$temp_array  = $this->files;
		$this->files = null;
		foreach ( $temp_array as $key => $file ) {
			if ( apply_filters( 'rtmedia_valid_type_check', $this->is_valid_type( $file ), $file ) ) {
				$this->files[] = $file;
			}
		}
	}

	function id3_validate_type( $file ) {
		$file_type = explode( '/', $file['type'] );
		$type      = $file_type[0];
		switch ( $type ) {
			case 'video' :
				if ( ! class_exists( 'getID3', true ) ) {
					include_once( trailingslashit( RTMEDIA_PATH ) . 'lib/getid3/getid3.php' );
				}
				try {
					$get_id3  = new getID3;
					$vid_info = $get_id3->analyze( $file['tmp_name'] );
				} catch ( Exception $e ) {
					$this->safe_unlink( $file['tmp_name'] );
					$activity_content = false;
					throw new RTMediaUploadException( 0, esc_html__( 'MP4 file you have uploaded is corrupt.', 'buddypress-media' ) );
				}
				if ( is_array( $vid_info ) ) {
					if ( ! array_key_exists( 'error', $vid_info ) && array_key_exists( 'fileformat', $vid_info ) && array_key_exists( 'video', $vid_info ) && array_key_exists( 'fourcc', $vid_info['video'] ) ) {
						if ( ! ( 'mp4' === $vid_info['fileformat'] && 'avc1' === $vid_info['video']['fourcc'] ) ) {
							$this->safe_unlink( $file['tmp_name'] );
							$activity_content = false;
							throw new RTMediaUploadException( 0, esc_html__( 'The MP4 file you have uploaded is using an unsupported video codec. Supported video codec is H.264.', 'buddypress-media' ) );
						}
					} else {
						$this->safe_unlink( $file['tmp_name'] );
						$activity_content = false;
						throw new RTMediaUploadException( 0, esc_html__( 'The MP4 file you have uploaded is using an unsupported video codec. Supported video codec is H.264.', 'buddypress-media' ) );
					}
				} else {
					$this->safe_unlink( $file['tmp_name'] );
					$activity_content = false;
					throw new RTMediaUploadException( 0, esc_html__( 'The MP4 file you have uploaded is not a video file.', 'buddypress-media' ) );
				}
				break;
			case 'audio' :
				if ( ! class_exists( 'getID3' ) ) {
					include_once( trailingslashit( RTMEDIA_PATH ) . 'lib/getid3/getid3.php' );
				}
				try {
					$get_id3   = new getID3;
					$file_info = $get_id3->analyze( $file['tmp_name'] );
				} catch ( Exception $e ) {
					$this->safe_unlink( $file['tmp_name'] );
					$activity_content = false;
					throw new RTMediaUploadException( 0, esc_html__( 'MP3 file you have uploaded is currupt.', 'buddypress-media' ) );
				}
				if ( is_array( $file_info ) ) {
					if ( ! array_key_exists( 'error', $file_info ) && array_key_exists( 'fileformat', $file_info ) && array_key_exists( 'audio', $file_info ) && array_key_exists( 'dataformat', $file_info['audio'] ) ) {
						if ( ! ( 'mp3' === $file_info['fileformat'] && 'mp3' === $file_info['audio']['dataformat'] ) ) {
							$this->safe_unlink( $file['tmp_name'] );
							$activity_content = false;
							throw new RTMediaUploadException( 0, esc_html__( 'The MP3 file you have uploaded is using an unsupported audio format. Supported audio format is MP3.', 'buddypress-media' ) );
						}
					} else {
						$this->safe_unlink( $file['tmp_name'] );
						$activity_content = false;
						throw new RTMediaUploadException( 0, esc_html__( 'The MP3 file you have uploaded is using an unsupported audio format. Supported audio format is MP3.', 'buddypress-media' ) );
					}
				} else {
					$this->safe_unlink( $file['tmp_name'] );
					$activity_content = false;
					throw new RTMediaUploadException( 0, esc_html__( 'The MP3 file you have uploaded is not an audio file.', 'buddypress-media' ) );
				}
				break;
			case 'image' :
				break;
			default :
				$this->safe_unlink( $file['tmp_name'] );
				$activity_content = false;
				throw new RTMediaUploadException( 0, esc_html__( 'Media File you have tried to upload is not supported. Supported media files are .jpg, .png, .gif, .mp3, .mov and .mp4.', 'buddypress-media' ) );
		}

		return true;
	}

	function safe_unlink( $file_path ) {
		if ( file_exists( $file_path ) ) {
			if ( function_exists( 'wp_delete_file' ) ) {  // wp_delete_file is introduced in WordPress 4.2
				wp_delete_file( $file_path );
			} else {
				unlink( $file_path ); // @codingStandardsIgnoreLine
			}
		}
	}

	function exif( $file ) {
		$file_parts = pathinfo( $file['file'] );
		if ( in_array( strtolower( $file_parts['extension'] ), array( 'jpg', 'jpeg', 'tiff' ) ) ) {
			$exif        = @read_exif_data( $file['file'] );
			$exif_orient = isset( $exif['Orientation'] ) ? $exif['Orientation'] : 0;
			$rotate_image = 0;

			$exif_orient = intval( $exif_orient );
			if ( 6 === intval( $exif_orient ) ) {
				$rotate_image      = 90;
				$image_orientation = 1;
			} elseif ( 3 === $exif_orient ) {
				$rotate_image      = 180;
				$image_orientation = 1;
			} elseif ( 8 === $exif_orient ) {
				$rotate_image      = 270;
				$image_orientation = 1;
			}

			if ( $rotate_image ) {
				if ( class_exists( 'Imagick' ) ) {
					$imagick = new Imagick();
					$imagick->readImage( $file['file'] );
					$imagick->rotateImage( new ImagickPixel(), $rotate_image );
					$imagick->setImageOrientation( $image_orientation );
					$imagick->writeImage( $file['file'] );
					$imagick->clear();
					$imagick->destroy();
				} else {
					$rotate_image = - $rotate_image;

					switch ( $file['type'] ) {
						case 'image/jpeg':
							$source = imagecreatefromjpeg( $file['file'] );
							$rotate = imagerotate( $source, $rotate_image, 0 );
							imagejpeg( $rotate, $file['file'] );
							break;
						case 'image/png':
							$source = imagecreatefrompng( $file['file'] );
							$rotate = imagerotate( $source, $rotate_image, 0 );
							imagepng( $rotate, $file['file'] );
							break;
						case 'image/gif':
							$source = imagecreatefromgif( $file['file'] );
							$rotate = imagerotate( $source, $rotate_image, 0 );
							imagegif( $rotate, $file['file'] );
							break;
						default:
							break;
					}
				}
			}
		}

		return $file;
	}

	function arrayify( $files ) {
		if ( isset( $files['name'] ) && ! is_array( $files['name'] ) ) {
			$updated_files[0] = $files;
		} else {
			foreach ( $files as $key => $array ) {
				foreach ( $array as $index => $value ) {
					$updated_files[ $index ][ $key ] = $value;
				}
			}
		}

		return $updated_files;
	}
}
