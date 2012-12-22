<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of BPMediaScreen
 *
 * @author saurabh
 */
class BPMediaScreen {

	public $slug = NULL;
	public $media_type = '';
	public $media_const = '';
	public $medias_type = '';

	public function __construct( $media_type, $slug ) {
		$this->slug = $slug;
		$this->media_constant( $media_type );
	}

	public function media( $media_type ) {
		$this->media_type = $media_type;
	}

	public function medias( $media_type ) {
		$this->media( $media_type );
		$media = strtoupper( $this->media_type );
		if ( $media != 'audio' ) {
			$media = $media . 's';
		}
		$this->medias_type = $media;
	}

	public function media_constant( $media_type ) {
		$this->medias( $media_type );
		$this->media_const = strtoupper( $this->medias_type );
	}

	public function hook_before() {
		do_action( 'bp_media_before_content' );
		do_action( 'bp_media_before_' . $this->slug );
	}

	public function hook_after() {
		do_action( 'bp_media_after_' . $this->slug );
		do_action( 'bp_media_after_content' );
	}

	public function page_not_exist() {
		global $bp_media;
		@setcookie( 'bp-message', __( 'The requested url does not exist', $bp_media->text_domain ), time() + 60 * 60 * 24, COOKIEPATH );
		@setcookie( 'bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH );
		$this->template_redirect();
		exit;
	}

	public function screen_title() {
		global $bp_media;
		printf( __( '%s List Page', $bp_media->text_domain ), $this->slug );
	}

	public function screen() {

		global $bp;

		$editslug = 'BP_MEDIA_' . $this->media_const . '_EDIT_SLUG';
		$entryslug = 'BP_MEDIA_' . $this->media_const . '_ENTRY_SLUG';

		echo $editslug. '<br>';
		echo $entryslug. '<br>';
		remove_filter( 'bp_activity_get_user_join_filter', 'bp_media_activity_query_filter', 10 );
		if ( isset( $bp->action_variables[ 0 ] ) ) {
			switch ( $bp->action_variables[ 0 ] ) {
				case constant( $editslug ) :
					$this->edit_screen();
					break;
				case constant( $entryslug ) :
					$this->entry_screen();
					break;
				case BP_MEDIA_DELETE_SLUG :
					if ( ! isset( $bp->action_variables[ 1 ] ) ) {
						$this->page_not_exist();
					}
					$this->entry_delete();
					break;
				default:
					bp_media_set_query();
					add_action( 'bp_template_content', array( $this, 'screen_content' ) );
			}
		} else {
			bp_media_set_query();
			add_action( 'bp_template_content', array( $this, 'screen_content' ) );
		}
		$this->template_loader();
	}

	function screen_content() {
		global $bp_media_query, $bp_media;
		$this->hook_before();
		if ( $bp_media_query && $bp_media_query->have_posts() ):

			echo '<ul id="bp-media-list" class="bp-media-gallery item-list">';
			while ( $bp_media_query->have_posts() ) : $bp_media_query->the_post();
				bp_media_the_content();
			endwhile;
			echo '</ul>';
			bp_media_display_show_more();
		else:
			bp_media_show_formatted_error_message( sprintf( __( 'Sorry, no %s were found.', $bp_media->text_domain ), $this->slug ), 'info' );
		endif;
		$this->hook_after();
	}

	function entry_screen() {
		$this->template_actions( 'entry_screen' );
		$this->template_loader();
	}

	function entry_screen_title() {
		global $bp_media_current_entry;
		/** @var $bp_media_current_entry BP_Media_Host_Wordpress */
		if ( is_object( $bp_media_current_entry ) )
			echo $bp_media_current_entry->get_media_single_title();
	}

	function entry_screen_content() {
		global $bp, $bp_media, $bp_media_current_entry;
		if ( ! isset( $bp->action_variables[ 1 ] ) ) {
			$this->page_not_exist();
		}
		try {
			$bp_media_current_entry = new BP_Media_Host_Wordpress( $bp->action_variables[ 1 ] );
			if ( $bp_media_current_entry->get_author() != bp_displayed_user_id() )
				throw new Exception( __( 'Sorry, the requested media does not belong to the user', $bp_media->text_domain ) );
		} catch ( Exception $e ) {
			/* Send the values to the cookie for page reload display */
			if ( isset( $_COOKIE[ 'bp-message' ] ) && $_COOKIE[ 'bp-message' ] != '' ) {
				@setcookie( 'bp-message', $_COOKIE[ 'bp-message' ], time() + 60 * 60 * 24, COOKIEPATH );
				@setcookie( 'bp-message-type', $_COOKIE[ 'bp-message-type' ], time() + 60 * 60 * 24, COOKIEPATH );
			} else {
				@setcookie( 'bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH );
				@setcookie( 'bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH );
			}
			$this->template_redirect();
			exit;
		}
		$this->template_actions( 'entry_screen' );
	}

	function edit_screen() {
		global $bp_media_current_entry, $bp;
		if ( ! isset( $bp->action_variables[ 1 ] ) ) {
			$this->page_not_exist();
		}
		//Creating global bp_media_current_entry for later use
		try {
			$bp_media_current_entry = new BP_Media_Host_Wordpress( $bp->action_variables[ 1 ] );
		} catch ( Exception $e ) {
			/* Send the values to the cookie for page reload display */
			@setcookie( 'bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH );
			@setcookie( 'bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH );
			$this->template_redirect();
			exit;
		}
		bp_media_check_user();

		//For saving the data if the form is submitted
		if ( array_key_exists( 'bp_media_title', $_POST ) ) {
			bp_media_update_media();
		}
		$this->template_actions( 'edit_screen' );
		$this->template_loader();
	}

	function edit_screen_title() {
		global $bp_media;
		printf( __( 'Edit %s', $bp_media->text_domain ), $this->slug );
	}

	function edit_screen_content() {
		global $bp, $bp_media_current_entry, $bp_media_default_excerpts, $bp_media;
		?>
		<form method="post" class="standard-form" id="bp-media-upload-form">
			<label for="bp-media-upload-input-title">
				<?php printf( __( '%s Title', $bp_media->text_domain ), ucfirst( $this->media_type ) ); ?>
			</label>
			<input id="bp-media-upload-input-title" type="text" name="bp_media_title" class="settings-input"
				   maxlength="<?php echo max( array( $bp_media_default_excerpts[ 'single_entry_title' ], $bp_media_default_excerpts[ 'activity_entry_title' ] ) ) ?>"
				   value="<?php echo $bp_media_current_entry->get_title(); ?>" />
			<label for="bp-media-upload-input-description">
				<?php printf( __( '%s Description', $bp_media->text_domain ), ucfirst( $this->media_type ) ); ?>
			</label>
			<input id="bp-media-upload-input-description" type="text" name="bp_media_description" class="settings-input"
				   maxlength="<?php echo max( array( $bp_media_default_excerpts[ 'single_entry_description' ], $bp_media_default_excerpts[ 'activity_entry_description' ] ) ) ?>"
				   value="<?php echo $bp_media_current_entry->get_content(); ?>" />
			<div class="submit">
				<input type="submit" class="auto" value="<?php _e( 'Update', $bp_media->text_domain ); ?>" />
				<a href="<?php echo $bp_media_current_entry->get_url(); ?>" class="button" title="<?php _e( 'Back to Media File', $bp_media->text_domain ); ?>">
					<?php _e( 'Back to Media', $bp_media->text_domain ); ?>
				</a>
			</div>
		</form>
		<?php
	}

	function entry_delete() {
		global $bp, $bp_media;
		if ( bp_loggedin_user_id() != bp_displayed_user_id() ) {
			bp_core_no_access( array(
				'message' => __( 'You do not have access to this page.', $bp_media->text_domain ),
				'root' => bp_displayed_user_domain(),
				'redirect' => false
			) );
			exit;
		}
		if ( ! isset( $bp->action_variables[ 1 ] ) ) {
			@setcookie( 'bp-message',  __( 'The requested url does not exist', $bp_media->text_domain ), time() + 60 * 60 * 24, COOKIEPATH );
			@setcookie( 'bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH );
			$this->template_redirect();
			exit;
		}
		global $bp_media_current_entry;
		try {
			$bp_media_current_entry = new BP_Media_Host_Wordpress( $bp->action_variables[ 1 ] );
		} catch ( Exception $e ) {
			/* Send the values to the cookie for page reload display */
			@setcookie( 'bp-message', $e->getMessage(), time() + 60 * 60 * 24, COOKIEPATH );
			@setcookie( 'bp-message-type', 'error', time() + 60 * 60 * 24, COOKIEPATH );
			$this->template_redirect();
			exit;
		}
		$post_id = $bp_media_current_entry->get_id();
		$activity_id = get_post_meta( $post_id, 'bp_media_child_activity', true );

		bp_activity_delete_by_activity_id( $activity_id );
		$bp_media_current_entry->delete_media();

		@setcookie( 'bp-message', __( 'Media deleted successfully', $bp_media->text_domain ), time() + 60 * 60 * 24, COOKIEPATH );
		@setcookie( 'bp-message-type', 'success', time() + 60 * 60 * 24, COOKIEPATH );
		$this->template_redirect();
		exit;
	}

	function template_actions( $action ) {
		add_action( 'bp_template_title', array( $this, $action . '_title' ) );
		add_action( 'bp_template_content', array( $this, $action . '_content' ) );
	}

	function template_redirect() {
		wp_redirect( trailingslashit( bp_displayed_user_domain() . constant( 'BP_MEDIA_' . $this->media_const . '_SLUG' ) ) );
	}

	function template_loader() {
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	function upload_screen() {
		add_action( 'wp_enqueue_scripts', array( $this, 'upload_enqueue' ) );
		add_action( 'bp_template_title', array( $this, 'upload_screen_title' ) );
		add_action( 'bp_template_content', array( $this, 'upload_screen_content' ) );
		$this->template_loader();
	}

	function upload_screen_title() {
		global $bp_media;
		_e( 'Upload Media', $bp_media->text_domain );
	}

	function upload_screen_content() {
		$this->hook_before();

		$this->upload_form_multiple();

		$this->hook_after();
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
