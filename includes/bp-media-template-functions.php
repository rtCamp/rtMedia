<?php

/**
 * 
 */

function bp_media_show_upload_form() {
	global $bp;
	?>
	<form method="post" enctype="multipart/form-data" class="standard-form" id="bp-media-upload-form">
		<label for="bp-media-upload-input-title"><?php _e('Media Title','bp-media'); ?></label><input id="bp-media-upload-input-title" type="text" name="bp_media_title" class="settings-input" />
		<label for="bp-media-upload-input-description"><?php _e('Media Description','bp-media'); ?></label><input id="bp-media-upload-input-description" type="text" name="bp_media_description" class="settings-input" />
		<label for="bp-media-upload-file"><?php _e('Select Media File','bp-media') ?></label><input type="file" name="bp_media_file" id="bp-media-upload-file" />
		<input type="hidden" name="action" value="wp_handle_upload" />
		<div class="submit"><input type="submit" class="auto" value="Upload" /></div>
	</form>
	<?php
}
?>