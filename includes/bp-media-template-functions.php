<?php
function bp_media_show_upload_form() {
	global $bp,$bp_media_default_excerpts;	
	?>
	<form method="post" enctype="multipart/form-data" class="standard-form" id="bp-media-upload-form">
		<label for="bp-media-upload-input-title"><?php _e('Media Title', 'bp-media'); ?></label><input id="bp-media-upload-input-title" type="text" name="bp_media_title" class="settings-input" maxlength="<?php echo max(array($bp_media_default_excerpts['single_entry_title'],$bp_media_default_excerpts['activity_entry_title'])) ?>" />
		<label for="bp-media-upload-input-description"><?php _e('Media Description', 'bp-media'); ?></label><input id="bp-media-upload-input-description" type="text" name="bp_media_description" class="settings-input" maxlength="<?php echo max(array($bp_media_default_excerpts['single_entry_description'],$bp_media_default_excerpts['activity_entry_description'])) ?>" />
		<label for="bp-media-upload-file"><?php _e('Select Media File', 'bp-media') ?> (Max File Size:<?php echo min(array(ini_get('upload_max_filesize'),ini_get('post_max_size')));  ?>)</label><input type="file" name="bp_media_file" id="bp-media-upload-file" />
		<input type="hidden" name="action" value="wp_handle_upload" />
		<div class="submit"><input type="submit" class="auto" value="Upload" /></div>
	</form>
	<?php
}

function bp_media_show_pagination($type = 'top') {
	global $bp, $bp_media_paginated_links, $bp_media_query;
	switch ($bp->current_action) {
		case BP_MEDIA_IMAGES_SLUG :
			$current = $bp_media_query->found_posts > 1 ? BP_MEDIA_IMAGES_LABEL : BP_MEDIA_IMAGES_LABEL_SINGULAR;
			$current_single = BP_MEDIA_IMAGES_LABEL_SINGULAR;
			break;
		case BP_MEDIA_VIDEOS_SLUG :
			$current = $bp_media_query->found_posts > 1 ? BP_MEDIA_VIDEOS_LABEL : BP_MEDIA_VIDEOS_LABEL_SINGULAR;
			$current_single = BP_MEDIA_VIDEOS_LABEL_SINGULAR;
			break;
		case BP_MEDIA_AUDIO_SLUG :
			$current = BP_MEDIA_AUDIO_LABEL;
			$current_single = BP_MEDIA_AUDIO_LABEL_SINGULAR;
			break;
		default :
			$current = BP_MEDIA_LABEL;
			$current_single = BP_MEDIA_LABEL_SINGULAR;
	}
	$args = array(
		'base' => trailingslashit(bp_displayed_user_domain() . $bp->current_action . '/') . '%_%',
		'format' => 'page/%#%',
		'total' => $bp_media_query->max_num_pages,
		'current' => $bp_media_query->query_vars['paged'],
		'type' => 'array',
		'prev_text' => '&larr;',
		'next_text' => '&rarr;',
	);
	$start_num = intval($bp_media_query->query_vars['posts_per_page'] * ($bp_media_query->query_vars['paged'] - 1)) + 1;
	$from_num = $start_num;
	$to_num = $start_num + $bp_media_query->post_count - 1;
	$total = $bp_media_query->found_posts;
	$bp_media_paginated_links = paginate_links($args);
	?>
	<div id="pag-<?php echo $type; ?>" class="pagination no-ajax">
		<div class="pag-count">
			Viewing <?php echo $current_single ?> <?php echo $from_num ?> to <?php echo $to_num ?> (of <?php echo $total; ?> <?php echo $current ?>)
		</div>
		<div class="pagination-links">
			<?php if(is_array($bp_media_paginated_links)) : foreach ($bp_media_paginated_links as $link) : ?>
				<?php echo $link; ?>
			<?php endforeach; endif; ?>
		</div>
	</div>
	<?php
}

function bp_media_get_permalink($id = 0) {
	if (is_object($id))
		$media = $id;
	else
		$media = &get_post($id);
	if (empty($media->ID))
		return false;
	if (!$media->post_type == 'bp_media')
		return false;
	switch (get_post_meta($media->ID, 'bp_media_type', true)) {
		case 'video' :
			return trailingslashit(bp_displayed_user_domain() . BP_MEDIA_VIDEOS_SLUG . '/watch/' . $media->ID);
			break;
		case 'audio' :
			return trailingslashit(bp_displayed_user_domain() . BP_MEDIA_AUDIO_SLUG . '/listen/' . $media->ID);
			break;
		case 'image' :
			return trailingslashit(bp_displayed_user_domain() . BP_MEDIA_IMAGES_SLUG . '/view/' . $media->ID);
			break;
		default :
			return false;
	}
}

function bp_media_the_permalink() {
	echo apply_filters('bp_media_the_permalink', bp_media_get_permalink());
}

function bp_media_the_content($id = 0) {
	if (is_object($id))
		$media = $id;
	else
		$media = &get_post($id);
	if (empty($media->ID))
		return false;
	if (!$media->post_type == 'bp_media')
		return false;
	$media = new BP_Media_Host_Wordpress($media->ID);
	echo $media->get_media_gallery_content();
}
?>