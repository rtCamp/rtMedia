<?php

/**
 * 
 */

function bp_media_show_upload_form() {
	global $bp;
	?>
	<form method="post" enctype="multipart/form-data">
		<input type="file" name="bp_media_file" />
		<input type="hidden" name="action" value="wp_handle_upload"/>
		<input type="submit" />
	</form>
	<?php
}
?>