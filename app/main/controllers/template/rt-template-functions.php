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
	global $rt_media;
	echo $rt_media->post_title;

}

function rt_media_permalink(){

}

function rt_media_thumbnail(){

}

function rt_media_content(){

}

function rt_media_description(){
	global $rt_media;
	echo $rt_media->post_content;
}

function rt_media_actions(){

}

function rt_media_comments(){

}

function is_rt_media_gallery(){
	return true;
}

function is_rt_media_single(){
	return true;
}

function rtmedia_uploader($attr){
	echo RTMediaUploadShortcode::render($attr);
}


?>
