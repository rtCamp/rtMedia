<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaDownload
 *
 * @author saurabh
 */
class BPMediaDownload {

	/**
	 *
	 */
	function __construct() {
		add_action('wp_ajax_bp_media_download', array($this,'download_now'));
		add_action('wp_ajax_no_priv_bp_media_download', array($this,'download_now'));
	}

	function force_download($file) {
		if ( file_exists( $file ) ) {
				header( 'Content-Description: File Transfer' );
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename=' . basename( $file ) );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
				header( 'Pragma: public' );
				header( 'Content-Length: ' . filesize( $file ) );
				echo readfile( $file );
				exit;
			}
			return false;
	}

	function get_path_from_url($file){
		$upload_info = wp_upload_dir();
		if(empty($upload_info['error'])){

				$upload_base_path = $upload_info['basedir'];
				$upload_base_url = $upload_info['baseurl'];
				$file_path = str_replace($upload_base_url,$upload_base_path,$file);

				$path_info= pathinfo($file_path);
				if (in_array($path_info['extension'],array('gif','png','jpg','mp4','mp3'))){
					return $file_path;
				}


			}
			return false;
	}

	function download_now(){
		if ( isset( $_GET[ 'file' ] ) ) {
			$file = $_GET[ 'file' ];
			$file = $this->get_path_from_url($file);
			$this->force_download($file);
			die();
		}
	}

}

?>
