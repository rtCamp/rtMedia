<?php

function have_rt_media(){
	global $rt_media_query;

	return $rt_media_query->have_media();
}
function rewind_rt_media() {

	global $rt_media_query;

	return $rt_media_query->rewind_media();
}

function rt_album(){
	global $rt_media_query;

	return $rt_media_query->rt_album();
}

function rt_media() {
	global $rt_media_query;
	
	return $rt_media_query->rt_media();
}

function rt_media_title(){
	global $rt_media_media;
	echo $rt_media_media->post_title;

}

function rt_media_permalink(){
	global $rt_media_media;
	echo 'media/' . $rt_media_media->media_id;
}

function rt_media_thumbnail(){
	global $rt_media_media;
	echo $rt_media_media->guid;
}

function rt_media_content(){

}

function rt_media_description(){
	global $rt_media_media;
	echo $rt_media_media->post_content;
}

function rt_media_count() {
	global $rt_media_query;
	
	return $rt_media_query->media_count;
}

function rt_media_offset() {
	global $rt_media_query;
	
	return $rt_media_query->action_query->offset;
}

function rt_media_per_page_media() {
	global $rt_media_query;
	
	return $rt_media_query->action_query->per_page_media;
}

function rt_media_page() {
	global $rt_media_query;
	
	return $rt_media_query->action_query->paged;
}

function rt_media_current_media() {
	global $rt_media_query;
	
	return $rt_media_query->current_media;
}

function rt_media_actions(){

}

function rt_media_comments(){

}

function is_rt_media_gallery(){
	return false;
}

function is_rt_media_single(){
	return true;
}

function rtmedia_uploader($attr){
	echo RTMediaUploadShortcode::render($attr);
}


?>
