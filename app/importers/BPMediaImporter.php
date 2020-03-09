<?php
/**
 * Handles BuddyPress media import
 *
 * @package    rtMedia
 */

/**
 * Class for BuddyPress media import
 *
 * @author saurabh
 */
class BPMediaImporter {

	/**
	 * Active.
	 *
	 * @var $active
	 */
	public $active;

	/**
	 * Import steps.
	 *
	 * @var $import_steps
	 */
	public $import_steps;

	/**
	 * BPMediaImporter constructor.
	 */
	public function __construct() {

	}

	/**
	 * Check if table exists.
	 *
	 * @param string $table Table name.
	 *
	 * @return bool
	 */
	public static function table_exists( $table ) {
		global $wpdb;

		if ( 1 === intval( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Function to check if plugin is active.
	 *
	 * @param string $path Plugin path.
	 *
	 * @return int
	 */
	public static function _active( $path ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		if ( ! function_exists( 'is_plugin_inactive' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}
		if ( is_plugin_active( $path ) ) {
			return 1;
		}
		$plugins = get_plugins();
		if ( array_key_exists( $path, $plugins ) ) {
			return 0;
		} else {
			return - 1;
		}
	}

	/**
	 * Function get file information.
	 *
	 * @param string $filepath Filepath.
	 *
	 * @return mixed
	 */
	public static function file_array( $filepath ) {

		$path_info        = pathinfo( $filepath );
		$filetype         = wp_check_filetype( $filepath );
		$file['error']    = '';
		$file['name']     = $path_info['basename'];
		$file['type']     = $filetype['type'];
		$file['tmp_name'] = $filepath;
		$file['size']     = filesize( $filepath );

		return $file;
	}

	/**
	 * Function to make copy of file.
	 *
	 * @param string $filepath Filepath.
	 *
	 * @return int|mixed
	 */
	public static function make_copy( $filepath ) {

		$upload_dir = wp_upload_dir();
		$path_info  = pathinfo( $filepath );
		$tmp_dir    = trailingslashit( $upload_dir['basedir'] ) . 'bp-album-importer';
		$newpath    = trailingslashit( $tmp_dir ) . $path_info['basename'];

		if ( ! is_dir( $tmp_dir ) ) {
			wp_mkdir_p( $tmp_dir );
		}

		if ( file_exists( $filepath ) ) {
			if ( copy( $filepath, $newpath ) ) {
				return self::file_array( $newpath );
			}
		}

		return 0;
	}

	/**
	 * Create Album.
	 *
	 * @param string $album_name Album name.
	 * @param int    $author_id Author id.
	 *
	 * @return mixed
	 */
	public function create_album( $album_name = '', $author_id = 1 ) {

		global $bp_media;

		if ( array_key_exists( 'bp_album_import_name', $bp_media->options ) ) {
			if ( '' !== $bp_media->options['bp_album_import_name'] ) {
				$album_name = $bp_media->options['bp_album_import_name'];
			}
		}
		$found_album = BuddyPressMedia::get_wall_album();

		if ( count( $found_album ) < 1 ) {
			$album = new BPMediaAlbum();
			$album->add_album( $album_name, $author_id );
			$album_id = $album->get_id();
		} else {
			$album_id = $found_album[0]->ID;
		}

		return $album_id;
	}

	/**
	 * Add media into album.
	 *
	 * @param int    $album_id Album media.
	 * @param string $title Media title.
	 * @param string $description Media description.
	 * @param string $filepath File path.
	 * @param int    $privacy Privacy.
	 * @param bool   $author_id Author id.
	 * @param bool   $album_name Album name.
	 *
	 * @return int
	 */
	public static function add_media( $album_id, $title = '', $description = '', $filepath = '', $privacy = 0, $author_id = false, $album_name = false ) {

		$files = self::make_copy( $filepath );
		if ( $files ) {
			$bp_imported_media = new BPMediaHostWordpress();
			$imported_media_id = $bp_imported_media->insertmedia( $title, $description, $album_id, 0, false, false, $files, $author_id, $album_name );

			$args = array(
				'ID'          => $imported_media_id,
				'post_author' => $author_id,
			);

			wp_update_post( $args );

			$bp_album_privacy = $privacy;
			if ( 10 === intval( $bp_album_privacy ) ) {
				$bp_album_privacy = 6;
			}

			$privacy = new BPMediaPrivacy();
			$privacy->save( $bp_album_privacy, $imported_media_id );

			return $imported_media_id;
		}

		return 0;
	}

	/**
	 * Function to do cleanup(Delete tables and directories).
	 *
	 * @param string $table Table.
	 * @param string $directory Directory.
	 */
	public static function cleanup( $table, $directory ) {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS $table" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->base_prefix}bp_activity WHERE component = %s", 'album' ) );
		if ( is_dir( $directory ) ) {
			self::delete( $directory );
		}
	}

	/**
	 * Delete Directory.
	 *
	 * @param string $path Path.
	 *
	 * @return bool
	 */
	public static function delete( $path ) {
		if ( true === is_dir( $path ) ) {
			$files = array_diff( scandir( $path ), array( '.', '..' ) );

			foreach ( $files as $file ) {
				self::delete( realpath( $path ) . '/' . $file );
			}

			return rmdir( $path );
		} else {
			if ( true === is_file( $path ) ) {
				return unlink( $path );
			}
		}

		return false;
	}
}
