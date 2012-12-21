<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaUploadScreen
 *
 * @author saurabh
 */
class BPMediaUploadScreen extends BPMediaScreen {

	function __construct( $title, $slug ) {
		parent::__construct( $title, $slug );
	}

	function ui() {
		add_action( 'wp_enqueue_scripts', array( $this, 'upload_enqueue' ) );
		add_action( 'bp_template_title', array( $this, 'screen_title' ) );
		add_action( 'bp_template_content', array( $this, 'screen' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	function generate_ui() {
		$this->upload_form_multiple();
	}

	function upload_form_multiple() {
		global $bp;
		?>
		<div id="bp-media-album-prompt" title="Select Album"><select id="bp-media-selected-album"><?php
		if ( bp_is_current_component( 'groups' ) ) {
			$albums = new WP_Query( array(
						'post_type' => 'bp_media_album',
						'posts_per_page' => -1,
						'meta_key' => 'bp-media-key',
						'meta_value' => -bp_get_current_group_id(),
						'meta_compare' => '='
							) );
		} else {
			$albums = new WP_Query( array(
						'post_type' => 'bp_media_album',
						'posts_per_page' => -1,
						'author' => get_current_user_id()
							) );
		}
		if ( isset( $albums->posts ) && is_array( $albums->posts ) && count( $albums->posts ) > 0 ) {
			foreach ( $albums->posts as $album ) {
				if ( $album->post_title == 'Wall Posts' )
					echo '<option value="' . $album->ID . '" selected="selected">' . $album->post_title . '</option>';
				else
					echo '<option value="' . $album->ID . '">' . $album->post_title . '</option>';
			};
		}else {
			$album = new BP_Media_Album();
			if ( bp_is_current_component( 'groups' ) ) {
				$current_group = new BP_Groups_Group( bp_get_current_group_id() );
				$album->add_album( 'Wall Posts', $current_group->creator_id, bp_get_current_group_id() );
			} else {
				$album->add_album( 'Wall Posts', bp_loggedin_user_id() );
			}
			echo '<option value="' . $album->get_id() . '" selected="selected">' . $album->get_title() . '</option>';
		}
		?></select></div>
		<div id="bp-media-album-new" title="Create New Album"><label for="bp_media_album_name">Album Name</label><input id="bp_media_album_name" type="text" name="bp_media_album_name" /></div>
		<div id="bp-media-upload-ui" class="hide-if-no-js drag-drop">
			<div id="drag-drop-area">
				<div class="drag-drop-inside">
					<p class="drag-drop-info">Drop files here</p>
					<p>or</p>
					<p class="drag-drop-buttons"><input id="bp-media-upload-browse-button" type="button" value="Select Files" class="button" /></p>
				</div>
			</div>
		</div>
		<div id="bp-media-uploaded-files"></div>
		<?php
	}

	function upload_enqueue() {
		$params = array(
			'url' => plugins_url( 'includes/bp-media-upload-handler.php', __FILE__ ),
			'runtimes' => 'gears,html5,flash,silverlight,browserplus',
			'browse_button' => 'bp-media-upload-browse-button',
			'container' => 'bp-media-upload-ui',
			'drop_element' => 'drag-drop-area',
			'filters' => apply_filters( 'bp_media_plupload_files_filter', array( array( 'title' => "Media Files", 'extensions' => "mp4,jpg,png,jpeg,gif,mp3" ) ) ),
			'max_file_size' => min( array( ini_get( 'upload_max_filesize' ), ini_get( 'post_max_size' ) ) ),
			'multipart' => true,
			'urlstream_upload' => true,
			'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
			'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
			'file_data_name' => 'bp_media_file', // key passed to $_FILE.
			'multi_selection' => true,
			'multipart_params' => apply_filters( 'bp_media_multipart_params_filter', array( 'action' => 'wp_handle_upload' ) )
		);
		wp_enqueue_script( 'bp-media-uploader', plugins_url( 'js/bp-media-uploader.js', __FILE__ ), array( 'plupload', 'plupload-html5', 'plupload-flash', 'plupload-silverlight', 'plupload-html4', 'plupload-handlers', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-dialog' ) );
		wp_localize_script( 'bp-media-uploader', 'bp_media_uploader_params', $params );
		wp_enqueue_style( 'bp-media-default', plugins_url( 'css/bp-media-style.css', __FILE__ ) );
//	wp_enqueue_style("wp-jquery-ui-dialog"); //Its not styling the Dialog box as it should so using different styling
		wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
	}

}
?>
