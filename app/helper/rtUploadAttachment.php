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
				if ( ! empty( $_FILES ) ) {
					$error = false;
					$files = array();
					// Get WordPress's uploads directory paths and urls.
					$wpuploaddir = wp_upload_dir();
					// Folder for uploading temporary debug attachment. i.e SITE_ROOT/wp-content/uploads/rtMedia/tmp.
					$uploaddir = $wpuploaddir['basedir'] . '/rtMedia/tmp/';

					// If folder is not there, then create it.
					if ( ! is_dir( $uploaddir ) ) {
						if ( ! mkdir( $uploaddir, 0777, true ) ) {
							die( 'Failed to create folders...' );
						}
					}
					$allowd_type = array( 'jpg', 'jpeg', 'png', 'gif', 'zip', 'doc', 'docx', 'pdf', 'txt' );

					// Code to check whether the uploaded file is settings json file.
					$import_export         = false;
					$import_export_control = sanitize_text_field( filter_input( INPUT_POST, 'import_export_control' ) );
					if ( 'rtFileInput' === $import_export_control ) {
						$import_export = true;
					}

					// Move file to target folder.
					foreach ( $_FILES as $name => $file ) {
						if ( $file['size'] <= 2000000 ) {
							$ext = pathinfo( basename( $file['name'] ), PATHINFO_EXTENSION );

							if ( $import_export ) {
								if ( 'json' === strtolower( $ext ) && move_uploaded_file( $file['tmp_name'], $uploaddir . basename( $file['name'] ) ) ) {
									$uploaded_file = $uploaddir . $file['name'];

									$rtadmin = new RTMediaAdmin();
									$rtadmin->import_settings( $uploaded_file );
								} else {
									$error = true;
								}
							} elseif ( in_array( strtolower( $ext ), $allowd_type, true ) && move_uploaded_file( $file['tmp_name'], $uploaddir . basename( $file['name'] ) ) ) {
								$files[] = $uploaddir . $file['name'];
							} else {
								$error = true;
							}
						} else {
							$size_error = array( 'exceed_size_msg' => esc_html__( 'You can not upload more than 2 MB.', 'buddypress-media' ) );
							echo wp_json_encode( $size_error );
							exit();
						}
					}

					$data = ( $error ) ? array( 'error' => esc_html__( 'There was an error uploading your files', 'buddypress-media' ) ) : array( 'debug_attachmanet' => $files );

				} else {
					$data = array(
						'success'  => esc_html__( 'Form was submitted', 'buddypress-media' ),
						'formData' => $_POST,
					);
				}

				// Send response as json format.
				echo wp_json_encode( $data );
				die();
			}
		}
	}
}
