<?php

/**
 * 
 */
class BP_Media_Host_Wordpress implements BP_Media_Host {

	private $id, //id of the entry
		$name, //Name of the entry
		$description, //Description of the entry
		$url, //URL of the entry
		$type, //Type of the entry (Video, Image or Audio)
		$owner;   //Owner of the entry

	function __construct($media_id = '') {
		if (!$media_id == '') {
			$this->init($media_id);
		}
	}

	function init($media_id = '') {
		if (is_object($media_id)) {
			$media = $media_id;
		} else {
			$media = &get_post($media_id);
		}
		if (empty($media->ID))
			throw new Exception('Sorry, the requested entry does not exist.');
		$this->id = $media->ID;
		$this->description = $media->post_content;
		$this->name = $media->post_title;
		$this->owner = $media->post_author;
		$this->type = get_post_meta($media->ID, 'bp_media_type', true);
		switch ($this->type) {
			case 'video' :
				$this->url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_VIDEOS_SLUG . '/' . BP_MEDIA_VIDEOS_ENTRY_SLUG . '/' . $this->id);
				break;
			case 'audio' :
				$this->url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_AUDIO_SLUG . '/' . BP_MEDIA_AUDIO_ENTRY_SLUG . '/' . $this->id);
				break;
			case 'image' :
				$this->url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_IMAGES_SLUG . '/' . BP_MEDIA_IMAGES_ENTRY_SLUG . '/' . $this->id);
				break;
			default :
				return false;
		}
	}

	function add_media($name, $description) {
		global $bp, $wpdb;
		include_once(ABSPATH . 'wp-admin/includes/file.php');
		include_once(ABSPATH . 'wp-admin/includes/image.php');
		//media_handle_upload('async-upload', $_REQUEST['post_id']);
		$postarr = array(
			'post_status' => 'draft',
			'post_type' => 'bp_media',
			'post_content' => $description,
			'post_title' => $name
		);
		$post_id = wp_insert_post($postarr);


		$file = wp_handle_upload($_FILES['bp_media_file']);
		if (isset($file['error']) || $file === null) {
			wp_delete_post($post_id, true);
			return false;
		}

		$attachment = array();
		$url = $file['url'];
		$type = $file['type'];
		$file = $file['file'];
		$title = $name;
		$content = $description;
		$attachment = array(
			'post_mime_type' => $type,
			'guid' => $url,
			'post_title' => $title,
			'post_content' => $content,
			'post_parent' => $post_id,
		);
		switch ($type) {
			case 'video/mp4' :
				$type = 'video';
				break;
			case 'audio/mpeg' :
				$type = 'audio';
				break;
			case 'image/gif' :
			case 'image/jpeg' :
			case 'image/png' :
				$type = 'image';
				break;
			default : unlink($file);
				wp_delete_post($post_id, true);
				unlink($file);
				$activity_content = false;
				return false;
		}
		$activity_content = '[bp_media_content id="' . $post_id . '"]';
		$attachment_id = wp_insert_attachment($attachment, $file, $post_id);
		if (!is_wp_error($attachment_id)) {
			wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file));
		} else {
			wp_delete_post($post_id, true);
			unlink($file);
			return false;
		}
		$postarr['ID'] = $post_id;
		$postarr['post_mime_type'] = $type;
		$postarr['post_status'] = 'publish';
		//$wpdb->update( $wpdb->posts, $postarr, array( 'ID' => $post_id ) );
		wp_insert_post($postarr);
		$activity_id = bp_media_record_activity(array(
			'action' => '[bp_media_action id="' . $post_id . '"]',
			'content' => $activity_content,
			'primary_link' => '[bp_media_url id="' . $post_id . '"]',
			'type' => 'media_upload'
			));
		bp_activity_update_meta($activity_id, 'bp_media_parent_post', $post_id);
		update_post_meta($post_id, 'bp_media_child_activity', $activity_id);
		update_post_meta($post_id, 'bp_media_child_attachment', $attachment_id);
		update_post_meta($post_id, 'bp_media_type', $type);
		update_post_meta($post_id, 'bp_media_hosting', 'wordpress');
		$this->id = $post_id;
		$this->name = $name;
		$this->description = $description;
		$this->owner = bp_loggedin_user_id();
		$this->type = $type;
		$this->url = $url;
	}

	function remove_media() {
		
	}

	function update_media() {
		
	}

	function display_media() {
		
	}

	function get_media_activity_content() {
		if (!bp_is_activity_component()) {
			return false;
		}
		global $bp_media_counter;
		$attachment_id = get_post_meta($this->id, 'bp_media_child_attachment', true);
		$activity_content = '<span class="bp_media_title"><a href="' . $this->url . '" title="' . $this->name . '">' . $this->name . '</a></span><span class="bp_media_description">' . $this->description . '</span><span class="bp_media_content">';
		switch ($this->type) {
			case 'video' :
				$activity_content.='<video src="' . wp_get_attachment_url($attachment_id) . '" width="320" height="240" type="video/mp4" id="bp_media_video_' . $this->id . '_' . $bp_media_counter . '" controls="controls" preload="none"></video><script>jQuery("#bp_media_video_' . $this->id . '_' . $bp_media_counter++ . '").mediaelementplayer();</script></span>';
				break;
			case 'audio' :
				$activity_content.='<audio src="' . wp_get_attachment_url($attachment_id) . '" width="320" type="audio/mp3" id="bp_media_audio_' . $this->id . '_' . $bp_media_counter . '" controls="controls" preload="none" ></audio><script>jQuery("#bp_media_audio_' . $this->id . '_' . $bp_media_counter++ . '").mediaelementplayer();</script>';
				$type = 'audio';
				break;
			case 'image' :
				$image_array = image_downsize($attachment_id, 'bp_media_activity_image');
				$activity_content.='<a href="' . $this->url . '" title="' . $this->name . '"><img src="' . $image_array[0] . '" id="bp_media_image_' . $this->id . '_' . $bp_media_counter++ . '" alt="' . $this->name . '" /></a>';
				$type = 'image';
				break;
			default :
				return false;
		}
		$activity_content .= '</span>';
		return $activity_content;
	}

	function get_media_activity_url() {
		if (!bp_is_activity_component())
			return false;
		$activity_url = $this->url;
		return $activity_url;
	}

	function get_media_activity_action() {
		if (!bp_is_activity_component())
			return false;
		$activity_action = sprintf(__("%s uploaded a media."), bp_core_get_userlink($this->owner));
		return $activity_action;
	}

	function get_media_single_content() {
		global $bp_media_default_sizes;
		$attachment_id = get_post_meta($this->id, 'bp_media_child_attachment', true);
		$content = '<span class="bp_media_title">' . $this->name . '</span><span class="bp_media_description">' . $this->description . '</span><span class="bp_media_content">';
		switch ($this->type) {
			case 'video' :
				$content.='<video src="' . wp_get_attachment_url($attachment_id) . '" width="' . $bp_media_default_sizes['single_video']['width'] . '" height="' . ($bp_media_default_sizes['single_video']['height'] == 0 ? 'auto' : $bp_media_default_sizes['single_video']['height']) . '" type="video/mp4" id="bp_media_video_' . $this->id . '" controls="controls" preload="none"></video><script>jQuery("#bp_media_video_' . $this->id . '").mediaelementplayer();</script></span>';
				break;
			case 'audio' :
				$content.='<audio src="' . wp_get_attachment_url($attachment_id) . '" width="' . $bp_media_default_sizes['single_audio']['width'] . '" type="audio/mp3" id="bp_media_audio_' . $this->id . '" controls="controls" preload="none" ></audio><script>jQuery("#bp_media_audio_' . $this->id . '").mediaelementplayer();</script>';
				$type = 'audio';
				break;
			case 'image' :
				$image_array = image_downsize($attachment_id, 'bp_media_single_image');
				$content.='<img src="' . $image_array[0] . '" id="bp_media_image_' . $this->id . '" />';
				$type = 'image';
				break;
			default :
				return false;
		}
		$content .= '</span>';
		return $content;
	}

	function get_media_gallery_content() {
		$attachment = get_post_meta($this->id, 'bp_media_child_attachment', true);
		switch ($this->type) {
			case 'video' :
				?>
				<li>
					<a href="<?php echo $this->url ?>" title="<?php echo $this->description ?>">
						<img src="<?php echo plugins_url('css/video_thumb.jpg', __FILE__) ?>" />
					</a>
					<h3>
						<a href="<?php echo $this->url ?>" title="<?php echo $this->description ?>"><?php echo $this->name ?></a>
					</h3>
				</li>
				<?php
				break;
			case 'audio' :
				?>
				<li>
					<a href="<?php echo $this->url ?>" title="<?php echo $this->description ?>">
						<img src="<?php echo plugins_url('css/audio_thumb.jpg', __FILE__) ?>" />
					</a>
					<h3>
						<a href="<?php echo $this->url ?>" title="<?php echo $this->description ?>"><?php echo $this->name ?></a>
					</h3>
				</li>
				<?php
				break;
			case 'image' :
				$medium_array = image_downsize($attachment, 'thumbnail');
				$medium_path = $medium_array[0];
				?>
				<li>
					<a href="<?php echo $this->url ?>" title="<?php echo $this->description ?>">
						<img src="<?php echo $medium_path ?>" />
					</a>
					<h3>
						<a href="<?php echo $this->url ?>" title="<?php echo $this->description ?>"><?php echo $this->name ?></a>
					</h3>
				</li>
				<?php
				break;
			default :
				return false;
		}
	}

	function show_comment_form() {
		$activity_id = get_post_meta($this->id, 'bp_media_child_activity', true);
		if (bp_has_activities(array(
				'display_comments' => 'stream',
				'include' => $activity_id,
				'max' => 1
			))) :
			while (bp_activities()) : bp_the_activity();
				do_action('bp_before_activity_entry');
				?>
				<div class="activity">
					<ul id="activity-stream" class="activity-list item-list">
						<li class="activity activity_update" id="activity-<?php echo $activity_id; ?>">
							<div class="activity-content">

								<?php do_action('bp_activity_entry_content'); ?>

								<?php if (is_user_logged_in()) : ?>

									<div class="activity-meta">

										<?php if (bp_activity_can_comment()) : ?>

											<a href="<?php bp_get_activity_comment_link(); ?>" class="button acomment-reply bp-primary-action" id="acomment-comment-<?php bp_activity_id(); ?>"><?php printf(__('Comment <span>%s</span>', 'buddypress'), bp_activity_get_comment_count()); ?></a>

										<?php endif; ?>

										<?php if (bp_activity_can_favorite()) : ?>

											<?php if (!bp_get_activity_is_favorite()) : ?>

												<a href="<?php bp_activity_favorite_link(); ?>" class="button fav bp-secondary-action" title="<?php esc_attr_e('Mark as Favorite', 'buddypress'); ?>"><?php _e('Favorite', 'buddypress') ?></a>

											<?php else : ?>

												<a href="<?php bp_activity_unfavorite_link(); ?>" class="button unfav bp-secondary-action" title="<?php esc_attr_e('Remove Favorite', 'buddypress'); ?>"><?php _e('Remove Favorite', 'buddypress') ?></a>

											<?php endif; ?>

										<?php endif; ?>

										<?php if (bp_activity_user_can_delete()) bp_activity_delete_link(); ?>

										<?php do_action('bp_activity_entry_meta'); ?>

									</div>

								<?php endif; ?>

							</div>

							<?php do_action('bp_before_activity_entry_comments'); ?>

							<?php if (( is_user_logged_in() && bp_activity_can_comment() ) || bp_activity_get_comment_count()) : ?>

								<div class="activity-comments">

									<?php bp_activity_comments(); ?>

									<?php if (is_user_logged_in()) : ?>

										<form action="<?php bp_activity_comment_form_action(); ?>" method="post" id="ac-form-<?php bp_activity_id(); ?>" class="ac-form"<?php bp_activity_comment_form_nojs_display(); ?>>
											<div class="ac-reply-avatar"><?php bp_loggedin_user_avatar('width=' . BP_AVATAR_THUMB_WIDTH . '&height=' . BP_AVATAR_THUMB_HEIGHT); ?></div>
											<div class="ac-reply-content">
												<div class="ac-textarea">
													<textarea id="ac-input-<?php bp_activity_id(); ?>" class="ac-input" name="ac_input_<?php bp_activity_id(); ?>"></textarea>
												</div>
												<input type="submit" name="ac_form_submit" value="<?php _e('Post', 'buddypress'); ?>" /> &nbsp; <?php _e('or press esc to cancel.', 'buddypress'); ?>
												<input type="hidden" name="comment_form_id" value="<?php bp_activity_id(); ?>" />
											</div>

											<?php do_action('bp_activity_entry_comments'); ?>

											<?php wp_nonce_field('new_activity_comment', '_wpnonce_new_activity_comment'); ?>

										</form>

									<?php endif; ?>

								</div>

							<?php endif; ?>

							<?php do_action('bp_after_activity_entry_comments'); ?>
						</li>
					</ul>
				</div>
				<?php
			endwhile;
		endif;
	}

	function get_url() {
		return $this->url;
	}

}
?>