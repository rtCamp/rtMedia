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
function rt_media(){
	global $rt_media_query;

	return $rt_media_query->rt_media();
}

/**
 * echo the title of the media
 * @global type $rt_media_media
 */
function rt_media_title(){
	global $rt_media_media;
	return $rt_media_media->post_title;
}

function rt_media_id() {
	global $rt_media_media;
	return $rt_media_media->id;
}

/**
 * echo parmalink of the media
 * @global type $rt_media_media
 */
function rt_media_permalink(){
	global $rt_media_query;
	echo $rt_media_query->permalink();
}

/*
 * echo http url of the media
 */
function rt_media_image($size = 'thumbnail',$return='src') {
	global $rt_media_media;
	list($src,$width,$height) = wp_get_attachment_image_src($rt_media_media->media_id,$size);

	if($return == "src")
		echo $src;
	if($return == "width")
		echo $width;
	if($return == "height")
		echo $height;
}

function rt_media_delete_allowed() {
	global $rt_media_media;

	$flag = $rt_media_media->media_author == get_current_user_id();

	$flag = apply_filters('rt_media_media_delete_priv', $flag);

	return $flag;
}

function rt_media_edit_allowed() {

	global $rt_media_media;

	$flag = $rt_media_media->media_author == get_current_user_id();

	$flag = apply_filters('rt_media_media_edit_priv', $flag);

	return $flag;
}

function rt_media_request_action() {
	global $rt_media_query;
	return $rt_media_query->action_query->action;
}

function rt_media_title_input() {
	global $rt_media_media;

	$name = 'media_title';
	$value = $rt_media_media->media_title;

	$html = '<label for="'. $name .'"> Title : ';

	if(rt_media_request_action() == 'edit')
		$html .= '<input type="text" name="' . $name . '" id="' . $name . '" value="' . $value . '">';
	else
		$html .= '<span name="' . $name . '" id="' . $name . '">' . $value . '</span>';

	$html .= '</label>';

	return $html;
}

function rt_media_description_input() {
	global $rt_media_media;

	$name = 'description';
	$value = $rt_media_media->post_content;

	$html = '<label for="'. $name .'"> Description : ';

	if(rt_media_request_action() == 'edit')
		$html .= '<textarea name="' . $name . '" id="' . $name . '">' . $value . '</textarea>';
	else
		$html .= '<span name="' . $name . '" id="' . $name . '">' . $value . '</span>';

	$html .= '</label>';

	return $html;
}

/**
 *
 */
function rt_media_content(){

	$html = '<form method="post">';
		$html .= rt_media_title_input() . '<br>';
		$html .= rt_media_description_input();
		if(rt_media_request_action() == "edit") {
			ob_start();
			RTMediaMedia::media_nonce_generator();
			$html .= ob_get_clean();
			$html .= '<input type="submit" value="Save">';
			$html .= '<a href="' . rt_media_url() . '"><input type="button" value="Back"></a>';
		}
	$html .= '</form>';

	echo $html;
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

	return ($rt_media_query->action_query->page-1)*$rt_media_query->action_query->per_page_media;
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
function rt_media_page() {
	global $rt_media_query;

	return $rt_media_query->action_query->page;
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
 *	rendering comments section
 */
function rt_media_comments(){

	$html = '<ul>';

	global $wpdb, $rt_media_media;

	$comments = $wpdb->get_results("SELECT * FROM wp_comments WHERE comment_post_ID = '". $rt_media_media->id ."'",ARRAY_A);

	foreach ($comments as $comment) {
		$html .= '<li class="rt-media-comment">';
			$html .= '<span class="rt-media-comment-content">' . $comment['comment_content'] . '</span>';
			$html .= ' by <span class ="rt-media-comment-author">' . $comment['comment_author'] . '</span>';
			$html .= ' at <span class ="rt-media-comment-author">' . $comment['comment_date_gmt'] . '</span>';
			$html .= '<a href></a>';
		$html .= '</li>';
	}

	$html .= '</ul>';

	echo $html;
}

function rt_media_url() {

	global $rt_media_media;

	$post = get_post($rt_media_media->post_parent);

	$link = get_site_url() . '/' . $post->post_name . '/media/' . $rt_media_media->id;

	return $link;
}

function rt_media_comments_enabled() {
	global $rt_media;
	return $rt_media->get_option('comments_enabled');
}

/**
 *
 * @return boolean
 */
function is_rt_media_gallery(){
	global $rt_media_query;
	return $rt_media_query->is_gallery();
}

/**
 *
 * @return boolean
 */
function is_rt_media_single(){
	global $rt_media_query;
	return $rt_media_query->is_single();
}

/**
 *
 * @param type $attr
 */
function rt_media_uploader($attr) {
	echo RTMediaUploadShortcode::pre_render($attr);
}

function rt_media_gallery($attr) {
	echo RTMediaGalleryShortcode::render($attr);
}

?>