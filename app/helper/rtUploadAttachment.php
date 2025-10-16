<?php
/**
 * Uploader for admin settings.
 *
 * @author dharmin
 *
 * @package buddypress-media
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_rtmedia_admin_upload', 'rtmedia_admin_upload' );

if ( ! function_exists( 'rtmedia_admin_upload' ) ) {

	/**
	 * Uploader for admin settings.
	 *
	 * @return void.
	 */
	function rtmedia_admin_upload() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			$nonce = filter_input( INPUT_POST, 'rtmedia_admin_upload_nonce' );
			if ( wp_verify_nonce( $nonce, 'rtmedia-admin-upload' ) ) {

				// Check if user has capability to upload file.
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( array( 'message' => esc_html__( 'You are not allowed to upload file.', 'buddypress-media' ) ) );
				}

				if ( ! empty( $_FILES ) ) {

					$error = false;
					$files = array();

					// Get WordPress's uploads directory paths and urls.
					$wpuploaddir = wp_upload_dir();

					// Folder for uploading temporary debug attachment. i.e SITE_ROOT/wp-content/uploads/rtMedia/tmp.
					$uploaddir = $wpuploaddir['basedir'] . '/rtMedia/tmp/';

					// If folder is not there, then create it.
					if ( ! function_exists( 'WP_Filesystem' ) ) {
						require_once ABSPATH . 'wp-admin/includes/file.php';
					}

					global $wp_filesystem;

					if ( ! $wp_filesystem ) {
						WP_Filesystem();
					}

					if ( ! $wp_filesystem->is_dir( $uploaddir ) ) {
						if ( ! $wp_filesystem->mkdir( $uploaddir, FS_CHMOD_DIR ) ) {
							die( 'Failed to create folders...' );
						}
					}

					$allowed_type = array( 'jpg', 'jpeg', 'png', 'gif', 'zip', 'doc', 'docx', 'pdf', 'txt' );

					// Code to check whether the uploaded file is settings json file.
					$import_export         = false;
					$import_export_control = sanitize_text_field( filter_input( INPUT_POST, 'import_export_control' ) );

					if ( 'rtFileInput' === $import_export_control ) {
						$import_export = true;
					}

					// Move file to target folder.
					foreach ( $_FILES as $name => $file ) {
						$safe_key  = sanitize_key( $name );
						$safe_name = isset( $file['name'] ) ? sanitize_file_name( $file['name'] ) : '';
						$file_size = isset( $file['size'] ) ? intval( $file['size'] ) : 0;
						$tmp_name  = isset( $file['tmp_name'] ) ? $file['tmp_name'] : '';
						$ext       = pathinfo( $safe_name, PATHINFO_EXTENSION );

						if ( $file_size > 2000000 ) {
							$size_error = array( 'exceed_size_msg' => esc_html__( 'You can not upload more than 2 MB.', 'buddypress-media' ) );
							echo wp_json_encode( $size_error );
							exit();
						}

						if ( ! is_uploaded_file( $tmp_name ) ) {
							$error = true;
							continue;
						}

						if ( $import_export ) {
							if ( 'json' === strtolower( $ext ) && $wp_filesystem->move( $tmp_name, $uploaddir . $safe_name, true ) ) {
								$uploaded_file = $uploaddir . $safe_name;
								$rtadmin       = new RTMediaAdmin();

								$rtadmin->import_settings( $uploaded_file );
							} else {
								$error = true;
							}
						} elseif ( in_array( strtolower( $ext ), $allowed_type, true ) && $wp_filesystem->move( $tmp_name, $uploaddir . $safe_name, true ) ) {
							$files[] = $uploaddir . $safe_name;
						} else {
							$error = true;
						}
					}

					$data = ( $error ) ? array( 'error' => esc_html__( 'There was an error uploading your files', 'buddypress-media' ) ) : array( 'debug_attachmanet' => $files );

				} else {
					$data = array(
						'success'  => esc_html__( 'Form was submitted', 'buddypress-media' ),
						'formData' => rtmedia_deep_sanitize_post( $_POST ),
					);
				}

				// Send response as json format.
				echo wp_json_encode( $data );
				die();
			}
		}
	}

	/**
	 * Deep sanitize post data.
	 *
	 * @param array $data Data array.
	 *
	 * @return array
	 */
	function rtmedia_deep_sanitize_post( $data ) {
		$sanitized = array();

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$sanitized[ $key ] = rtmedia_deep_sanitize_post( $value );
			} elseif ( is_numeric( $value ) ) {
				$sanitized[ $key ] = absint( $value );
			} elseif ( false !== filter_var( $value, FILTER_VALIDATE_URL ) ) {
				$sanitized[ $key ] = esc_url_raw( $value );
			} else {
				$sanitized[ $key ] = sanitize_text_field( $value );
			}
		}

		return $sanitized;
	}
}
