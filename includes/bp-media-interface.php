<?php

/**
 * 
 */

interface BP_Media {
	/**
	 * Handles the uploads and creates a post.
	 */
	function add_media($title,$description);
	
	/**
	 * Removes the posts as well as associated media with it.
	 */
	function remove_media();
	
	/**
	 * Upadates the meta data of the media.
	 */
	function update_media();
	
	/**
	 * Displays the media file
	 */
	function display_media();
}
?>