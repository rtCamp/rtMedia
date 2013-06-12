<?php

/**
 * Checks at any point of time any media is left to be processed in the db pool
 * @global type $rt_media_query
 * @return type
 */
function have_rt_media() {
	global $rt_media_query;

	return $rt_media_query->have_media();
}

/**
 * Rewinds the db pool of media album and resets it to begining
 * @global type $rt_media_query
 * @return type
 */
function rewind_rt_media() {

	global $rt_media_query;

	return $rt_media_query->rewind_media();
}

/**
 * moves ahead in the loop of media within the album
 * @global type $rt_media_query
 * @return type
 */
function rt_album(){
	global $rt_media_query;

	return $rt_media_query->rt_album();
}

/**
 * returns the current media object in the album
 * @global type $rt_media_query
 * @return type
 */
function rt_media() {
	global $rt_media_query;
	
	return $rt_media_query->rt_media();
}

/**
 * echo the title of the media
 * @global type $rt_media_media
 */
function rt_media_title(){
	global $rt_media_media;
	echo $rt_media_media->post_title;

}

/**
 * echo parmalink of the media
 * @global type $rt_media_media
 */
function rt_media_permalink(){
	global $rt_media_media;
	echo 'media/' . $rt_media_media->media_id;
}

/*
 * echo http url of the media
 */
function rt_media_thumbnail(){
	global $rt_media_media;
	echo $rt_media_media->guid;
}

/**
 * 
 */
function rt_media_content(){

}

/**
 * echo media description
 * @global type $rt_media_media
 */
function rt_media_description(){
	global $rt_media_media;
	echo $rt_media_media->post_content;
}

/**
 * returns total media count in the album
 * @global type $rt_media_query
 * @return type
 */
function rt_media_count() {
	global $rt_media_query;
	
	return $rt_media_query->media_count;
}

/**
 * returns the page offset for the media pool
 * @global type $rt_media_query
 * @return type
 */
function rt_media_offset() {
	global $rt_media_query;
	
	return $rt_media_query->action_query->offset;
}

/**
 * returns number of media per page to be displayed
 * @global type $rt_media_query
 * @return type
 */
function rt_media_per_page_media() {
	global $rt_media_query;
	
	return $rt_media_query->action_query->per_page_media;
}

/**
 * returns the page number of media album in the pagination
 * @global type $rt_media_query
 * @return type
 */
function rt_media_paged() {
	global $rt_media_query;
	
	return $rt_media_query->action_query->paged;
}

/**
 * returns the current media number in the album pool
 * @global type $rt_media_query
 * @return type
 */
function rt_media_current_media() {
	global $rt_media_query;
	
	return $rt_media_query->current_media;
}

/**
 * 
 */
function rt_media_actions(){

}

/**
 * 
 */
function rt_media_comments(){

}

/**
 * 
 * @return boolean
 */
function is_rt_media_gallery(){
	return false;
}

/**
 * 
 * @return boolean
 */
function is_rt_media_single(){
	return true;
}

/**
 * 
 * @param type $attr
 */
function rtmedia_uploader($attr){
	echo RTMediaUploadShortcode::render($attr);
}


?>
