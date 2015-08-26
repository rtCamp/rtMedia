<?php
/* * **************************************
 * Main.php
 *
 * The main template file, for lightbox and WordPress pages
 * *************************************** */
// by default it is not an ajax request
global $rt_ajax_request ;
?>
<div id="buddypress">
	<?php
		if ( ! $rt_ajax_request ) {
			?>
				<div id="item-body">
			<?php
		}
		rtmedia_load_template();
	    if ( ! $rt_ajax_request ) {
			?>
				</div><!--#item-body-->
			<?php
	    }
	?>
</div><!--#buddypress-->

        