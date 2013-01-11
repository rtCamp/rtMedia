<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaTemplate
 *
 * @author saurabh
 */
class BPMediaTemplate {

	function upload_form_multiple() {
		?>
		<div id="bp-media-album-prompt" title="Select Album">
                    <div class="bp-media-album-title">
                        <span><?php _e( 'Select Album', BP_MEDIA_TXT_DOMAIN ); ?></span>
                        <span id="bp-media-close"><?php _e( 'x', BP_MEDIA_TXT_DOMAIN ); ?></span>
                    </div>
                    <div class="bp-media-album-content">
                        <select id="bp-media-selected-album"><?php
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
			$album = new BPMediaAlbum();
			if ( bp_is_current_component( 'groups' ) ) {
				$current_group = new BP_Groups_Group( bp_get_current_group_id() );
				$album->add_album( 'Wall Posts', $current_group->creator_id, bp_get_current_group_id() );
			} else {
				$album->add_album( 'Wall Posts', bp_loggedin_user_id() );
			}
			echo '<option value="' . $album->get_id() . '" selected="selected">' . $album->get_title() . '</option>';
		}
		?></select>
                    </div>
                    <div class="select-btn-div">
                        <input id="selected-btn" type="button" class="btn" value="Select" />
                        <input id="create-btn" type="button" class="btn" value="Create Album" />
                        <div style="clear: both;"></div>
                    </div>
                </div>
		<div id="bp-media-album-new" title="Create New Album">
                    <div class="bp-media-album-title">
                        <span><?php _e( 'Create Album', BP_MEDIA_TXT_DOMAIN ); ?></span>
                        <span id="bp-media-create-album-close"><?php _e( 'x', BP_MEDIA_TXT_DOMAIN ); ?></span>
                    </div>
                    <div class="bp-media-album-content">
                        <label for="bp_media_album_name"><?php _e( 'Album Name', BP_MEDIA_TXT_DOMAIN ); ?></label>
                        <input id="bp_media_album_name" type="text" name="bp_media_album_name" />
                    </div>
                    <div class="select-btn-div">
                        <input id="create-album" type="button" class="btn" value="Create" />
                    </div>
                </div>
		<div id="bp-media-upload-ui" class="hide-if-no-js drag-drop">
			<div id="drag-drop-area">
				<div class="drag-drop-inside">
					<p class="drag-drop-info"><?php _e( 'Drop files here', BP_MEDIA_TXT_DOMAIN ); ?></p>
					<p><?php _e( ' or ', BP_MEDIA_TXT_DOMAIN ); ?></p>
					<p class="drag-drop-buttons"><input id="bp-media-upload-browse-button" type="button" value="<?php _e( 'Select Files', BP_MEDIA_TXT_DOMAIN ); ?>" class="button" /></p>
				</div>
			</div>
		</div>
		<div id="bp-media-uploaded-files"></div>
		<?php
	}

	function get_permalink( $id = 0 ) {
		if ( is_object( $id ) )
			$media = $id;
		else
			$media = &get_post( $id );
		if ( empty( $media->ID ) )
			return false;
		if ( ! $media->post_type == 'bp_media' )
			return false;
		switch ( get_post_meta( $media->ID, 'bp_media_type', true ) ) {
			case 'video' :
				return trailingslashit( bp_displayed_user_domain() . BP_MEDIA_VIDEOS_SLUG . '/' . BP_MEDIA_VIDEOS_ENTRY_SLUG . '/' . $media->ID );
				break;
			case 'audio' :
				return trailingslashit( bp_displayed_user_domain() . BP_MEDIA_AUDIO_SLUG . '/' . BP_MEDIA_AUDIO_ENTRY_SLUG . '/' . $media->ID );
				break;
			case 'image' :
				return trailingslashit( bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG . '/' . BP_MEDIA_IMAGES_ENTRY_SLUG . '/' . $media->ID );
				break;
			default :
				return false;
		}
	}

	function the_permalink() {
		echo apply_filters( 'the_permalink', array( $this, 'get_permalink' ) );
	}

	function the_content( $id = 0 ) {
		if ( is_object( $id ) )
			$media = $id;
		else
			$media = &get_post( $id );
		if ( empty( $media->ID ) )
			return false;
		if ( $media->post_type != 'attachment' )
			return false;
		try {
			$media = new BPMediaHostWordpress( $media->ID );
			echo $media->get_media_gallery_content();
		} catch ( Exception $e ) {
			echo '';
		}
	}

	function the_album_content( $id = 0 ) {
		if ( is_object( $id ) )
			$album = $id;
		else
			$album = &get_post( $id );
		if ( empty( $album->ID ) )
			return false;
		if ( ! $album->post_type == 'bp_media_album' )
			return false;
		try {
			$album = new BPMediaAlbum( $album->ID );
			echo $album->get_album_gallery_content();
		} catch ( Exception $e ) {
			echo '';
		}
	}

	function show_more( $type = 'media' ) {
		$showmore = false;
		switch ( $type ) {
			case 'media':
				global $bp_media_query;
				//found_posts
				if ( isset( $bp_media_query->found_posts ) && $bp_media_query->found_posts > 10 )
					$showmore = true;
				break;
			case 'albums':
				global $bp_media_albums_query;
				if ( isset( $bp_media_query->found_posts ) && $bp_media_query->found_posts > 10 )
					$showmore = true;
				break;
		}
		if ( $showmore ) {
			echo '<div class="bp-media-actions"><a href="#" class="button" id="bp-media-show-more">Show More</a></div>';
		}
	}

	/**
	 *
	 */
	function redirect( $mediaconst ) {
		bp_core_redirect( trailingslashit( bp_displayed_user_domain() . constant( 'BP_MEDIA_' . $mediaconst . '_SLUG' ) ) );
	}

	/**
	 *
	 */
	function loader() {
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	function upload_form_multiple_activity() {
		global $bp, $bp_media_default_excerpts;
		if ( $bp->current_component != 'activity' )
			return;
		?>
		<div id="bp-media-album-prompt" title="Select Album"><select id="bp-media-selected-album"><?php
		$albums = new WP_Query( array(
					'post_type' => 'bp_media_album',
					'posts_per_page' => -1,
					'author' => get_current_user_id()
						) );
		if ( isset( $albums->posts ) && is_array( $albums->posts ) && count( $albums->posts ) > 0 ) {
			foreach ( $albums->posts as $album ) {
				if ( $album->post_title == 'Wall Posts' )
					echo '<option value="' . $album->ID . '" selected="selected">' . $album->post_title . '</option>';
				else
					echo '<option value="' . $album->ID . '">' . $album->post_title . '</option>';
			};
		}
		?></select></div>
		<div id="bp-media-album-new" title="Create New Album"><label for="bp_media_album_name">Album Name</label><input id="bp_media_album_name" type="text" name="bp_media_album_name" /></div>
		<div id="bp-media-upload-ui" class="hide-if-no-js drag-drop activity-component">
			<p class="drag-drop-buttons"><input id="bp-media-upload-browse-button" type="button" value="Add Media" class="button" /></p>
		</div>
		<div id="bp-media-uploaded-files"></div>
		<?php
	}

}
?>
