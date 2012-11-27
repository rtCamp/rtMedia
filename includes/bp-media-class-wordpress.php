<?php
class BP_Media_Host_Wordpress {

	/**
	 * Private variables not to be accessible outside this class' member functions
	 */
	private $id, //id of the entry
		$name, //Name of the entry
		$description, //Description of the entry
		$url, //URL of the entry
		$type, //Type of the entry (Video, Image or Audio)
		$owner,   //Owner of the entry
		$delete_url, //The delete url for the media
		$thumbnail_id, //The thumbnail's id
		$album_id, //The album id to which the media belongs
		$edit_url; //The edit page's url for the media

	/**
	 * Constructs a new BP_Media_Host_Wordpress element
	 *
	 * @param mixed $media_id optional Media ID of the element to be initialized if not defined, returns an empty element.
	 *
	 * @since BP Media 2.0
	 */
	function __construct($media_id = '') {
		if (!$media_id == '') {
			$this->init($media_id);
		}
	}

	/**
	 * Initializes the object with the variables from the post
	 *
	 * @param mixed $media_id Media ID of the element to be initialized. Can be the ID or the object of the Media
	 *
	 * @since BP Media 2.0
	 */
	function init($media_id = '') {
		if (is_object($media_id)) {
			$media = $media_id;
		} else {
			$media = &get_post($media_id);
		}
		if (empty($media->ID))
			throw new Exception(__('Sorry, the requested media does not exist.', 'bp-media'));
		$this->id = $media->ID;
		$this->description = $media->post_content;
		$this->name = $media->post_title;
		$this->owner = $media->post_author;
		$this->album_id = $media->post_parent;
		preg_match_all('/audio|video|image/i', $media->post_mime_type, $result);
		if(isset($result[0][0]))
			$this->type = $result[0][0];
		else
			return false;
		switch ($this->type) {
			case 'video' :
				$this->url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_VIDEOS_SLUG . '/' . $this->id);
				$this->edit_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_VIDEOS_SLUG . '/' . BP_MEDIA_VIDEOS_EDIT_SLUG . '/' . $this->id);
				$this->delete_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_VIDEOS_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id);
				$this->thumbnail_id = get_post_meta($this->id, 'bp_media_thumbnail',true);
				break;
			case 'audio' :
				$this->url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_AUDIO_SLUG . '/' . $this->id);
				$this->edit_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_AUDIO_SLUG . '/' . BP_MEDIA_AUDIO_EDIT_SLUG . '/' . $this->id);
				$this->delete_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_AUDIO_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id);
				$this->thumbnail_id = get_post_meta($this->id, 'bp_media_thumbnail',true);
				break;
			case 'image' :
				$this->url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_IMAGES_SLUG . '/' . $this->id);
				$this->edit_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_IMAGES_SLUG . '/' . BP_MEDIA_IMAGES_EDIT_SLUG . '/' . $this->id);
				$this->delete_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_IMAGES_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id);
				$image_array = image_downsize($this->id, 'bp_media_single_image');
				$this->thumbnail_id = $this->id;
				break;
			default :
				return false;
		}
	}

	/**
	 * Handles the uploaded media file and creates attachment post for the file.
	 *
	 * @since BP Media 2.0
	 */
	function add_media($name, $description, $album_id = 0, $group = 0, $is_multiple = false) {
		do_action('bp_media_before_add_media');
		global $bp, $wpdb, $bp_media_count;
		include_once(ABSPATH . 'wp-admin/includes/file.php');
		include_once(ABSPATH . 'wp-admin/includes/image.php');
		$create_new_album_flag = false;
		if($album_id!=0){
			$album = get_post($album_id);
			if($album->post_author!=  get_current_user_id()){
				$create_new_album_flag = true;
			}
			else{
				$post_id = $album->ID;
			}
		}
		else{
			$create_new_album_flag = true;
		}
		if($create_new_album_flag){
			$post_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = 'Wall Posts' AND post_author = '".  get_current_user_id()."' AND post_type='bp_media_album'");
			if($post_id==null){
				$album = new BP_Media_Album();
				$album->add_album('Wall Posts');
				$post_id = $album->get_id();
			}
		}
		$file = wp_handle_upload($_FILES['bp_media_file']);
		if (isset($file['error']) || $file === null) {
			throw new Exception(__('Error Uploading File', 'bp-media'));
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
		bp_media_init_count(bp_loggedin_user_id());
		switch ($type) {
			case 'video/mp4' :
			case 'video/quicktime' :
				$type = 'video';
				include_once(trailingslashit(BP_MEDIA_PLUGIN_DIR) . 'includes/lib/getid3/getid3.php');
				try {
					$getID3 = new getID3;
					$vid_info = $getID3->analyze($file);
				} catch (Exception $e) {
					unlink($file);
					$activity_content = false;
					throw new Exception(__('MP4 file you have uploaded is currupt.', 'bp-media'));
				}
				if (is_array($vid_info)) {
					if (!array_key_exists('error',$vid_info)&& array_key_exists('fileformat', $vid_info) && array_key_exists('video', $vid_info)&&array_key_exists('fourcc',$vid_info['video'])) {
						if (!($vid_info['fileformat']=='mp4'&&$vid_info['video']['fourcc']=='avc1')) {
							unlink($file);
							$activity_content = false;
							throw new Exception(__('The MP4 file you have uploaded is using an unsupported video codec. Supported video codec is H.264.', 'bp-media'));
						}
					} else {
						unlink($file);
						$activity_content = false;
						throw new Exception(__('The MP4 file you have uploaded is using an unsupported video codec. Supported video codec is H.264.', 'bp-media'));
					}
				} else {
					unlink($file);
					$activity_content = false;
					throw new Exception(__('The MP4 file you have uploaded is not a video file.', 'bp-media'));
				}
				$bp_media_count['videos'] = intval($bp_media_count['videos']) + 1;
				break;
			case 'audio/mpeg' :
				include_once(trailingslashit(BP_MEDIA_PLUGIN_DIR) . 'includes/lib/getid3/getid3.php');
				try {
					$getID3 = new getID3;
					$file_info = $getID3->analyze($file);
				} catch (Exception $e) {
					unlink($file);
					$activity_content = false;
					throw new Exception(__('MP3 file you have uploaded is currupt.', 'bp-media'));
				}
				if (is_array($file_info)) {
					if (!array_key_exists('error',$file_info)&& array_key_exists('fileformat', $file_info) && array_key_exists('audio', $file_info)&&array_key_exists('dataformat',$file_info['audio'])) {
						if (!($file_info['fileformat']=='mp3'&&$file_info['audio']['dataformat']=='mp3')) {
							unlink($file);
							$activity_content = false;
							throw new Exception(__('The MP3 file you have uploaded is using an unsupported audio format. Supported audio format is MP3.', 'bp-media'));
						}
					} else {
						unlink($file);
						$activity_content = false;
						throw new Exception(__('The MP3 file you have uploaded is using an unsupported audio format. Supported audio format is MP3.', 'bp-media'));
					}
				} else {
					unlink($file);
					$activity_content = false;
					throw new Exception(__('The MP3 file you have uploaded is not an audio file.', 'bp-media'));
				}
				$type = 'audio';
				$bp_media_count['audio'] = intval($bp_media_count['audio']) + 1;
				break;
			case 'image/gif' :
			case 'image/jpeg' :
			case 'image/png' :
				$type = 'image';
				$bp_media_count['images'] = intval($bp_media_count['images']) + 1;
				break;
			default :
				unlink($file);
				$activity_content = false;
				throw new Exception(__('Media File you have tried to upload is not supported. Supported media files are .jpg, .png, .gif, .mp3, .mov and .mp4.', 'bp-media'));
		}
		$attachment_id = wp_insert_attachment($attachment, $file, $post_id);
		if (!is_wp_error($attachment_id)) {
			wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file));
		} else {
			unlink($file);
			throw new Exception(__('Error creating attachment for the media file, please try again', 'bp-media'));
		}
		$this->id = $attachment_id;
		$this->name = $name;
		$this->description = $description;
		$this->type = $type;
		$this->owner = get_current_user_id();
		$this->album_id = $post_id;
		switch ($this->type) {
			case 'video' :
				$this->url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_VIDEOS_SLUG . '/' . $this->id);
				$this->edit_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_VIDEOS_SLUG . '/' . BP_MEDIA_VIDEOS_EDIT_SLUG . '/' . $this->id);
				$this->delete_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_VIDEOS_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id);
				$this->thumbnail_id = get_post_meta($this->id, 'bp_media_thumbnail',true);
				break;
			case 'audio' :
				$this->url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_AUDIO_SLUG . '/' . $this->id);
				$this->edit_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_AUDIO_SLUG . '/' . BP_MEDIA_AUDIO_EDIT_SLUG . '/' . $this->id);
				$this->delete_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_AUDIO_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id);
				$this->thumbnail_id = get_post_meta($this->id, 'bp_media_thumbnail',true);
				break;
			case 'image' :
				$this->url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_IMAGES_SLUG . '/' . $this->id);
				$this->edit_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_IMAGES_SLUG . '/' . BP_MEDIA_IMAGES_EDIT_SLUG . '/' . $this->id);
				$this->delete_url = trailingslashit(bp_core_get_user_domain($this->owner) . BP_MEDIA_IMAGES_SLUG . '/' . BP_MEDIA_DELETE_SLUG . '/' . $this->id);
				$image_array = image_downsize($this->id, 'bp_media_single_image');
				$this->thumbnail_id = $this->id;
				break;
			default :
				//return false;
		}
		if($group == 0)
			update_post_meta($attachment_id, 'bp-media-key', get_current_user_id());
		else
			update_post_meta($attachment_id, 'bp-media-key', (-$group));
		bp_update_user_meta(bp_loggedin_user_id(), 'bp_media_count', $bp_media_count);
		do_action('bp_media_after_add_media',$this,$is_multiple);
	}

	/**
	 * Fetches the content of the activity of media upload based on its type
	 *
	 */
	function get_media_activity_content() {
		global $bp_media_counter, $bp_media_default_excerpts;                
		$attachment_id = $this->id;
		$activity_content = '<div class="bp_media_title"><a href="' . $this->url . '" title="' . $this->description . '">' . wp_html_excerpt($this->name, $bp_media_default_excerpts['activity_entry_title']) . '</a></div>';
		$activity_content .='<div class="bp_media_content">';
		switch ($this->type) {
			case 'video' :
				if($this->thumbnail_id){
					$image_array = image_downsize($this->thumbnail_id, 'bp_media_activity_image');
					$activity_content.='<video poster="'.$image_array[0].'" src="' . wp_get_attachment_url($attachment_id) . '" width="320" height="240" type="video/mp4" id="bp_media_video_' . $this->id . '_' . $bp_media_counter . '" controls="controls" preload="none"></video></span><script>bp_media_create_element("bp_media_video_' . $this->id . '_' . $bp_media_counter . '");</script>';
				}
				else{
					$activity_content.='<video src="' . wp_get_attachment_url($attachment_id) . '" width="320" height="240" type="video/mp4" id="bp_media_video_' . $this->id . '_' . $bp_media_counter . '" controls="controls" preload="none"></video></span><script>bp_media_create_element("bp_media_video_' . $this->id . '_' . $bp_media_counter . '");</script>';
				}
				break;
			case 'audio' :
				$activity_content.='<audio src="' . wp_get_attachment_url($attachment_id) . '" width="320" type="audio/mp3" id="bp_media_audio_' . $this->id . '_' . $bp_media_counter . '" controls="controls" preload="none" ></audio></span><script>bp_media_create_element("bp_media_audio_' . $this->id . '_' . $bp_media_counter . '");</script>';
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
		$activity_content .= '</div>';
		$activity_content .= '<div class="bp_media_description">' . wp_html_excerpt($this->description, $bp_media_default_excerpts['activity_entry_description']) . '</div>';                                
		return $activity_content;
	}

	/**
	 * Returns the single media entry's URL
	 */
	function get_media_activity_url() {
		if (!bp_is_activity_component())
			return false;
		$activity_url = $this->url;
		return $activity_url;
	}

	/**
	 * Returns the media activity's action text
	 */
	function get_media_activity_action() {
		if (!bp_is_activity_component())
			return false;
		$activity_action = sprintf(__("%s uploaded a media."), bp_core_get_userlink($this->owner));
		return $activity_action;
	}

	/**
	 * Returns the HTML for content of the single entry page of the Media Entry
	 */
	function get_media_single_content() {
		global $bp_media_default_sizes, $bp_media_default_excerpts;
		$content = '<div class="bp_media_content">';
		switch ($this->type) {
			case 'video' :
				if($this->thumbnail_id){
					$image_array = image_downsize($this->thumbnail_id, 'bp_media_single_image');
					$content.=apply_filters('bp_media_single_content_filter', '<video poster="'.$image_array[0].'" src="' . wp_get_attachment_url($this->id) . '" width="' . $bp_media_default_sizes['single_video']['width'] . '" height="' . ($bp_media_default_sizes['single_video']['height'] == 0 ? 'auto' : $bp_media_default_sizes['single_video']['height']) . '" type="video/mp4" id="bp_media_video_' . $this->id . '" controls="controls" preload="none"></video><script>bp_media_create_element("bp_media_video_' . $this->id . '");</script>',$this);
				}
				else{
					$content.=apply_filters('bp_media_single_content_filter', '<video src="' . wp_get_attachment_url($this->id) . '" width="' . $bp_media_default_sizes['single_video']['width'] . '" height="' . ($bp_media_default_sizes['single_video']['height'] == 0 ? 'auto' : $bp_media_default_sizes['single_video']['height']) . '" type="video/mp4" id="bp_media_video_' . $this->id . '" controls="controls" preload="none"></video><script>bp_media_create_element("bp_media_video_' . $this->id . '");</script>',$this);
				}
				break;
			case 'audio' :
				$content.=apply_filters('bp_media_single_content_filter', '<audio src="' . wp_get_attachment_url($this->id) . '" width="' . $bp_media_default_sizes['single_audio']['width'] . '" type="audio/mp3" id="bp_media_audio_' . $this->id . '" controls="controls" preload="none" ></audio><script>bp_media_create_element("bp_media_audio_' . $this->id . '");</script>',$this);
				break;
			case 'image' :
				$image_array = image_downsize($this->id, 'bp_media_single_image');
				$content.=apply_filters('bp_media_single_content_filter', '<img src="' . $image_array[0] . '" id="bp_media_image_' . $this->id . '" />',$this);
				break;
			default :
				return false;
		}
		$content .= '</div>';
		$content .= '<div class="bp_media_description">' . wp_html_excerpt($this->description, $bp_media_default_excerpts['single_entry_description']) . '</div>';
		return $content;
	}

	/**
	 * Returns the HTML for title of the single entry page of the Media Entry
	 */
	function get_media_single_title(){
		global $bp_media_default_excerpts;
		$content = '<div class="bp_media_title">' . wp_html_excerpt($this->name, $bp_media_default_excerpts['single_entry_title']) . '</div>';
		return $content;
	}

	/**
	 * Returns the HTML for a media entry to be shown in the listing/gallery page
	 */
	function get_media_gallery_content() {
		$attachment = $this->id;
		switch ($this->type) {
			case 'video' :
				if($this->thumbnail_id){
					$medium_array = image_downsize($this->thumbnail_id, 'thumbnail');
					$thumb_url = $medium_array[0];
				}
				else{
					$thumb_url = plugins_url('img/video_thumb.png', __FILE__);
				}
				?>
				<li>
					<a href="<?php echo $this->url ?>" title="<?php echo $this->description ?>">
						<img src="<?php echo $thumb_url; ?>" />
					</a>
					<h3 title="<?php echo $this->name ?>"><a href="<?php echo $this->url ?>" title="<?php echo $this->description ?>"><?php echo $this->name ?></a></h3>
				</li>
				<?php
				break;
			case 'audio' :
				if($this->thumbnail_id){
					$medium_array = image_downsize($this->thumbnail_id, 'thumbnail');
					$thumb_url = $medium_array[0];
				}
				else{
					$thumb_url = plugins_url('img/audio_thumb.png', __FILE__);
				}
				?>
				<li>
					<a href="<?php echo $this->url ?>" title="<?php echo $this->description ?>">
						<img src="<?php echo $thumb_url ?>" />
					</a>
					<h3 title="<?php echo $this->name ?>"><a href="<?php echo $this->url ?>" title="<?php echo $this->description ?>"><?php echo $this->name ?></a></h3>
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
					<h3 title="<?php echo $this->name ?>"><a href="<?php echo $this->url ?>" title="<?php echo $this->description ?>"><?php echo $this->name ?></a></h3>
				</li>
				<?php
				break;
			default :
				return false;
		}
	}

	function show_comment_form_wordpress(){
		query_posts('attachment_id='.$this->id);
		while(have_posts()): the_post();
		add_action('comment_form','bp_media_wp_comment_form_mod');
		comments_template();
		endwhile;
	}

	/**
	 * Outputs the comments and comment form in the single media entry page
	 */
	function show_comment_form() {
		$activity_id = get_post_meta($this->id, 'bp_media_child_activity', true);
		if(!$activity_id || !function_exists('bp_has_activities'))
			return false;
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
									<div class="activity-meta no-ajax">
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
		else: ?>
			<div class="activity">
					<ul id="activity-stream" class="activity-list item-list">
						<li class="activity activity_update" id="activity-<?php echo $activity_id; ?>">
							<div class="activity-content">
								<?php do_action('bp_activity_entry_content'); ?>
								<?php if (is_user_logged_in()) : ?>
									<div class="activity-meta no-ajax">
										<a href="<?php echo $this->get_delete_url(); ?>" class="button item-button bp-secondary-action delete-activity-single confirm" rel="nofollow">Delete</a>
									</div>
								<?php endif; ?>
							</div>
						</li>
					</ul>
				</div>
			<?php
		endif;
	}

	/**
	 * Returns the URL of the single media entry page
	 */
	function get_url() {
		return $this->url;
	}

	/**
	 * Returns the URL of the attached media file
	 */
	function get_attachment_url(){
		return wp_get_attachment_url($this->id);
	}

	/**
	 * Updates the media entry
	 *
	 * @param array $args Array with the following keys:<br/>
	 * 'name' <br/>
	 * 'description'<br/>
	 * 'owner'
	 *
	 * @return bool True when the update is successful, False when the update fails
	 */
	function update_media($args=array()){                
		$defaults=array(
			'name'	=>	$this->name,
			'description'	=>	$this->description,
			'owner'			=>	$this->owner
		);
		$args = wp_parse_args( $args, $defaults );
		$post=get_post($this->id,ARRAY_A);
		$post['post_title']=esc_html($args['name']);
		$post['post_content']=esc_html($args['description']);
		$post['post_author']=$args['owner'];
		$result =  wp_update_post($post);
		$this->init($this->id);
		return $result;
	}
        
        /**	 
         * Updates activity content's title and description sync with the editing of Media
	 *	 
	 */
        function update_media_activity(){
            global $wpdb, $bp, $current_user;            
            $q = $wpdb->prepare( "SELECT id FROM {$bp->activity->table_name} WHERE type = %s AND item_id = %d", 'media_upload', $this->id);
            $activities = $wpdb->get_results($q);            
            if(isset($activities) && count($activities) > 0){                
                    $activities_template = new BP_Activity_Template(array(
                    'max' => TRUE,
                    'user_id' => $current_user,
                    'in' => $activities[0]->id
                ));
                foreach ($activities_template->activities as $activity){                
                        $args = array(
                                'content' => $this->get_media_activity_content(),
                                'id' => $activity->id,
                                'type' => 'media_upload',
                                'action' => apply_filters( 'bp_media_added_media', sprintf( __( '%1$s added a %2$s', 'bp-media'), bp_core_get_userlink( $this->get_author() ), '<a href="' . $this->get_url() . '">' . $this->get_media_activity_type() . '</a>' ) ),
                                'primary_link' => $this->get_url(),
                                'item_id' => $this->get_id(),
                                'recorded_time' => $activity->date_recorded,
                                'user_id' => $this->get_author()
                        );
                    $activity_id = bp_media_record_activity($args);
                }            
            }
        }

	/**
	 * Deletes the Media Entry
	 */
	function delete_media(){
		do_action('bp_media_before_delete_media',$this->id);
		global $bp_media_count;
		bp_media_init_count($this->owner);
		switch ($this->type) {
			case 'image':
				$bp_media_count['images'] = intval($bp_media_count['images']) - 1;
				break;
			case 'video':
				$bp_media_count['videos'] = intval($bp_media_count['videos']) - 1;
				break;
			case 'audio':
				$bp_media_count['audio'] = intval($bp_media_count['audio']) - 1;
				break;
		}
		wp_delete_attachment($this->id,true);
		bp_update_user_meta($this->owner, 'bp_media_count', $bp_media_count);
		do_action('bp_media_after_delete_media',$this->id);
	}

	/**
	 * Function to return the content to be placed in the activity of album
	 */
	function get_album_activity_content(){
		$attachment = $this->id;
		switch ($this->type) {
			case 'video' :
				if($this->thumbnail_id){
					$medium_array = image_downsize($this->thumbnail_id, 'thumbnail');
					$thumb_url = $medium_array[0];
				}
				else{
					$thumb_url = plugins_url('img/video_thumb.png', __FILE__);
				}
				break;
			case 'audio' :
				if($this->thumbnail_id){
					$medium_array = image_downsize($this->thumbnail_id, 'thumbnail');
					$thumb_url = $medium_array[0];
				}
				else{
					$thumb_url = plugins_url('img/audio_thumb.png', __FILE__);
				}
				break;
			case 'image' :
				$medium_array = image_downsize($attachment, 'thumbnail');
				$thumb_url = $medium_array[0];
				break;
			default :
				return false;
		}
		$content = '<li>';
		$content .= '<a href="'.$this->url.'" title="'.$this->name.'">';
		$content .= '<img src="'.$thumb_url.'" />';
		$content .= '</a>';
		$content .= '</li>';
		return $content;
	}

	/**
	 * Returns the description of the Media Entry
	 */
	function get_content() {
		return $this->description;
	}

	/**
	 * Returns the owner id of the Media Entry
	 */
	function get_author() {
		return $this->owner;
	}

	/**
	 * Returns the id of the Media Entry
	 */
	function get_id(){
		return $this->id;
	}

	/**
	 * Returns the edit url of the Media Entry
	 */
	function get_edit_url() {
		return $this->edit_url;
	}

	/**
	 * Returns the delete url of the Media Entry
	 */
	function get_delete_url() {
		return $this->delete_url;
	}

	/**
	 * Returns the type of activity
	 */
	function get_media_activity_type() {
		switch($this->type){
			case 'image':
				return BP_MEDIA_IMAGES_LABEL_SINGULAR;
			case 'video':
				return BP_MEDIA_VIDEOS_LABEL_SINGULAR;
			case 'audio':
				return BP_MEDIA_AUDIO_LABEL_SINGULAR;
			default:
				return 'Media';
		}
	}

	/**
	 * Returns the album id
	 */
	function get_album_id(){
		return $this->album_id;
	}

	/**
	 * Returns the title of the media
	 */
	function get_title(){
		return $this->name;
	}

	/**
	 * Returns the type of media
	 */
	function get_type(){
		return $this->type;
	}
} ?>
