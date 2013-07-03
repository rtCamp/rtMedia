<?php

/**
 * Checks at any point of time any media is left to be processed in the db pool
 * @global type $rtmedia_query
 * @return type
 */
function have_rtmedia() {
    global $rtmedia_query;

    return $rtmedia_query->have_media();
}

/**
 * Rewinds the db pool of media album and resets it to begining
 * @global type $rtmedia_query
 * @return type
 */
function rewind_rtmedia() {

    global $rtmedia_query;

    return $rtmedia_query->rewind_media();
}

/**
 * moves ahead in the loop of media within the album
 * @global type $rtmedia_query
 * @return type
 */
function rtmedia() {
    global $rtmedia_query;

    return $rtmedia_query->rtmedia();
}

/**
 * echo the title of the media
 * @global type $rtmedia_media
 */
function rtmedia_title() {

    global $rtmedia_backbone;
    if ($rtmedia_backbone['backbone']) {
        echo '<%= media_title %>';
    } else {
        global $rtmedia_media;
        return $rtmedia_media->post_title;
    }
}

function rtmedia_id($media_id=false) {
    if ($media_id) {
        $model = new RTMediaModel();
        $media = $model->get_media(array('media_id' => $media_id), 0, 1);
        return $media[0]->id;
    } else {
        global $rtmedia_media;
        return $rtmedia_media->id;
    }
}

function rtmedia_media_id($id = false) {
    if ( $id ){
        $model = new RTMediaModel();
        $media = $model->get_media(array('id' => $id), 0, 1);
        return $media[0]->media_id;
    } else {
        global $rtmedia_media;
        return $rtmedia_media->media_id;
    }
}

function rtmedia_activity_id($id = false) {
    if ( $id ){
        $model = new RTMediaModel();
        $media = $model->get_media(array('id' => $id), 0, 1);
        return $media[0]->activity_id;
    } else {
        global $rtmedia_media;
        return $rtmedia_media->activity_id;
    }
}

function rtmedia_type($id = false) {
    if ( $id ){
        $model = new RTMediaModel();
        $media = $model->get_media(array('id' => $id), 0, 1);
        return $media[0]->media_type;
    } else {
        global $rtmedia_media;
        return $rtmedia_media->media_type;
    }
}

/**
 * echo parmalink of the media
 * @global type $rtmedia_media
 */
function rtmedia_permalink() {

    global $rtmedia_backbone;

    if ($rtmedia_backbone['backbone']) {
        echo '<%= rt_permalink %>';
    } else {
        echo get_rtmedia_permalink(rtmedia_id());
    }
}

function rtmedia_media() {
    global $rtmedia_media;
    if (isset($rtmedia_media->media_type)) {
        if ($rtmedia_media->media_type == 'photo') {
            echo wp_get_attachment_image($rtmedia_media->media_id, 'large');
        } elseif ($rtmedia_media->media_type == 'video') {
            echo '<video src="' . wp_get_attachment_url($rtmedia_media->media_id) . '" width="800" height="600" type="video/mp4" class="wp-video-shortcode" id="bp_media_video_' . $rtmedia_media->id . '" controls="controls" preload="none"></video>';
        } elseif ($rtmedia_media->media_type == 'music') {
            echo '<audio src="' . wp_get_attachment_url($rtmedia_media->media_id) . '" width="600" height="0" type="audio/mp3" class="wp-audio-shortcode" id="bp_media_audio_' . $rtmedia_media->id . '" controls="controls" preload="none"></audio>';
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/*
 * echo http url of the media
 */

function rtmedia_image($size = 'thumbnail', $id = false) {
    global $rtmedia_backbone;

    if ($rtmedia_backbone['backbone']) {
        echo '<%= guid %>';
        return;
    }

    if ($id) {
        $model = new RTMediaModel();
        $media = $model->get_media(array('id' => $id), false, false);
        if (isset($media[0]))
            $media_object = $media[0];
        else
            return false;
    } else {
        global $rtmedia_media;
        $media_object = $rtmedia_media;
    }

    $thumbnail_id = 0;
    if (isset($media_object->media_type)) {
        if ($media_object->media_type == 'album' ||
                $media_object->media_type != 'photo') {
            $thumbnail_id = isset($media_object->cover_art) ? $media_object->cover_art : false;
        } elseif ($media_object->media_type == 'photo') {
            $thumbnail_id = $media_object->media_id;
        } else {
            $thumbnail_id = false;
        }
    } else {
        return false;
    }

    if (!$thumbnail_id) {
        global $rtmedia;
        if (isset($rtmedia->allowed_types[$media_object->media_type])
                && isset($rtmedia->allowed_types[$media_object->media_type]['thumbnail'])) {
            echo $rtmedia->allowed_types[$media_object->media_type]['thumbnail'];
            return;
        } elseif ($media_object->media_type == 'album') {
            rtmedia_album_image($size);
            return;
        } else {
            return false;
        }
    }

//    if ()
    list($src, $width, $height) = wp_get_attachment_image_src($thumbnail_id, $size);

    echo $src;
}

function rtmedia_album_image($size = 'thumbnail') {
    global $rtmedia_media;
    $model = new RTMediaModel();
    $media = $model->get_media(array('album_id' => $rtmedia_media->id, 'media_type' => 'photo'), 0, 1);
    if ($media) {
        rtmedia_image($size, $media[0]->id);
    } else {
        global $rtmedia;
        echo $rtmedia->allowed_types['photo']['thumbnail'];
    }
}

function rtmedia_sanitize_object($data,$exceptions = array()){
    foreach($data as $key => $value) {
        if (!in_array($key, array_merge(RTMediaMedia::$default_object,$exceptions)) )
            unset($data[$key]);
    }
    return $data;
}

function rtmedia_delete_allowed() {
    global $rtmedia_media;

    $flag = $rtmedia_media->media_author == get_current_user_id();

    $flag = apply_filters('rtmedia_media_delete_priv', $flag);

    return $flag;
}

function rtmedia_edit_allowed() {

    global $rtmedia_media;

    $flag = $rtmedia_media->media_author == get_current_user_id();

    $flag = apply_filters('rtmedia_media_edit_priv', $flag);

    return $flag;
}

function rtmedia_request_action() {
    global $rtmedia_query;
    return $rtmedia_query->action_query->action;
}

function rtmedia_title_input() {
    global $rtmedia_media;

    $name = 'media_title';
    $value = $rtmedia_media->media_title;

    $html = '';

    if (rtmedia_request_action() == 'edit')
        $html .= '<input type="text" name="' . $name . '" id="' . $name . '" value="' . $value . '">';
    else
        $html .= '<h2 name="' . $name . '" id="' . $name . '">' . $value . '</h2>';

    $html .= '';

    return $html;
}

function rtmedia_description_input() {
    global $rtmedia_media;

    $name = 'description';
    $value = $rtmedia_media->post_content;

    $html = '';

    if (rtmedia_request_action() == 'edit')
        $html .= wp_editor($value, $name, array('media_buttons' => false));
    else
        $html .= '<div name="' . $name . '" id="' . $name . '">' . $value . '</div>';

    $html .= '';

    return $html;
}

/**
 * echo media description
 * @global type $rtmedia_media
 */
function rtmedia_description() {
    global $rtmedia_media;
    echo $rtmedia_media->post_content;
}

/**
 * returns total media count in the album
 * @global type $rtmedia_query
 * @return type
 */
function rtmedia_count() {
    global $rtmedia_query;

    return $rtmedia_query->media_count;
}

/**
 * returns the page offset for the media pool
 * @global type $rtmedia_query
 * @return type
 */
function rtmedia_offset() {
    global $rtmedia_query;

    return ($rtmedia_query->action_query->page - 1) * $rtmedia_query->action_query->per_page_media;
}

/**
 * returns number of media per page to be displayed
 * @global type $rtmedia_query
 * @return type
 */
function rtmedia_per_page_media() {
    global $rtmedia_query;

    return $rtmedia_query->action_query->per_page_media;
}

/**
 * returns the page number of media album in the pagination
 * @global type $rtmedia_query
 * @return type
 */
function rtmedia_page() {
    global $rtmedia_query;

    return $rtmedia_query->action_query->page;
}

/**
 * returns the current media number in the album pool
 * @global type $rtmedia_query
 * @return type
 */
function rtmedia_current_media() {
    global $rtmedia_query;

    return $rtmedia_query->current_media;
}

/**
 *
 */
function rtmedia_actions() {
    global $rtmedia_query;
    $actions = $rtmedia_query->actions;

    unset($actions['edit']);
    unset($actions['comment']);
    unset($actions['delete']);
    unset($actions['merge']);


//        if (!is_rtmedia_album())
//            unset($actions['merge']);
    //render edit button here
    if (is_rtmedia_album()) {
        $id = $rtmedia_query->media_query['album_id'];
    } else {
        $id = $rtmedia_query->action_query->id;
    }

    foreach ($actions as $action => $details) {
        $button = '';
        if ($details[1] != false) {
            $button .= '<form action="' . get_rtmedia_permalink($rtmedia_query->action_query->id) . $action . '/" method="post">';
            $button .= wp_nonce_field($rtmedia_query->action_query->id, 'rtmedia_user_action_' . $action . '_nonce', true, false);
            $button .= '<input type="submit" class="rtmedia-' . $action . '" value="' . $details[0] . '">';
            $button .= '</form>';
        }
        echo $button;
    }

    // render delete button here
}

/**
 * 	rendering comments section
 */
function rtmedia_comments() {

    $html = '<ul>';

    global $wpdb, $rtmedia_media;

    $comments = $wpdb->get_results("SELECT * FROM wp_comments WHERE comment_post_ID = '" . $rtmedia_media->id . "'", ARRAY_A);

    foreach ($comments as $comment) {
        $html .= '<li class="rtmedia-comment">';
        $html .= '<div class ="rtmedia-comment-author">' . (($comment['comment_author']) ? $comment['comment_author'] : 'Annonymous') . '  said : </div>';
        $html .= '<div class="rtmedia-comment-content">' . $comment['comment_content'] . '</div>';
        $html .= '<div class ="rtmedia-comment-date"> on ' . $comment['comment_date_gmt'] . '</div>';
//			$html .= '<a href></a>';
        $html .= '</li>';
    }

    $html .= '</ul>';

    echo $html;
}

function rtmedia_pagination_prev_link() {

    global $rtmedia_media, $rtmedia_interaction, $rtmedia_query;

    $page_url = ((rtmedia_page() - 1) == 1) ? "" : "pg/" . (rtmedia_page() - 1);
	$site_url = (is_multisite()) ? trailingslashit(get_site_url(get_current_blog_id())) : trailingslashit(get_site_url());
	$author_name = get_query_var('author_name');
	$link = '';

	if ($rtmedia_interaction->context->type == "profile") {
        if (function_exists("bp_core_get_user_domain"))
            $link .= trailingslashit(bp_core_get_user_domain($rtmedia_media->media_author));
        else
            $link = $site_url . 'author/' . $author_name . '/';
    } else if ($rtmedia_interaction->context->type == 'group') {
        if (function_exists("bp_get_current_group_slug"))
            $link .= $site_url . 'groups/' . bp_get_current_group_slug() . '/';
    } else {
        $post = get_post($rtmedia_media->post_parent);

        $link .= $site_url . $post->post_name . '/';
    }

	$link .= 'media/';

	if(isset($rtmedia_query->action_query->media_type)) {
		if(in_array($rtmedia_query->action_query->media_type, array("photo","music","video","album")))
			$link .= $rtmedia_query->action_query->media_type . '/';
	}
    return $link . $page_url;
}

function rtmedia_pagination_next_link() {

    global $rtmedia_media, $rtmedia_interaction, $rtmedia_query;

	$page_url = 'pg/'. (rtmedia_page() + 1);
	$site_url = (is_multisite()) ? trailingslashit(get_site_url(get_current_blog_id())) : trailingslashit(get_site_url());
	$author_name = get_query_var('author_name');
	$link = '';

	if ($rtmedia_interaction->context->type == "profile") {
        if (function_exists("bp_core_get_user_domain"))
            $link .= trailingslashit(bp_core_get_user_domain($rtmedia_media->media_author));
        else
            $link .= $site_url . 'author/' . $author_name . '/';
    } else if ($rtmedia_interaction->context->type == 'group') {
        if (function_exists("bp_get_current_group_slug"))
            $link .=  $site_url . 'groups/' . bp_get_current_group_slug() . '/';
    } else {
        $post = get_post($rtmedia_media->post_parent);

        $link .= $site_url . $post->post_name . '/';
    }
	$link .= 'media/';
	if(isset($rtmedia_query->action_query->media_type)) {
		if(in_array($rtmedia_query->action_query->media_type, array("photo","music","video","album")))
			$link .= $rtmedia_query->action_query->media_type . '/';
	}
	return $link . $page_url;
}

function rtmedia_comments_enabled() {
	global $rtmedia;
    return $rtmedia->options['general_enableComments'] && is_user_logged_in();
}

/**
 *
 * @return boolean
 */
function is_rtmedia_gallery() {
    global $rtmedia_query;
    return $rtmedia_query->is_gallery();
}

/**
 *
 * @return boolean
 */
function is_rtmedia_album_gallery() {
    global $rtmedia_query;
    return $rtmedia_query->is_album_gallery();
}

/**
 *
 * @return boolean
 */
function is_rtmedia_single() {
    global $rtmedia_query;
    return $rtmedia_query->is_single();
}

/**
 *
 * @return boolean
 */
function is_rtmedia_album() {
    global $rtmedia_query;
    return $rtmedia_query->is_album();
}

add_action('rtmedia_add_edit_fields','rtmedia_image_editor');
function rtmedia_image_editor() {
    global $rtmedia_query;
    if ($rtmedia_query->media[0]->media_type == 'photo') {
        RTMediaTemplate::enqueue_image_editor_scripts();
        $media_id = $rtmedia_query->media[0]->media_id;
        $id = $rtmedia_query->media[0]->id;
        //$editor = wp_get_image_editor(get_attached_file($id));
        include_once( ABSPATH . 'wp-admin/includes/image-edit.php' );
        echo '<div class="rtmedia-image-editor-cotnainer">';
        echo '<div class="rtmedia-image-editor" id="image-editor-' . $media_id . '"></div>';
        $thumb_url = wp_get_attachment_image_src($media_id, 'thumbnail', true);
        $nonce = wp_create_nonce("image_editor-$media_id");
        echo '<div id="imgedit-response-' . $media_id . '"></div>';
        echo '<div class="wp_attachment_image" id="media-head-' . $media_id . '">
				<p id="thumbnail-head-' . $id . '"><img class="thumbnail" src="' . set_url_scheme($thumb_url[0]) . '" alt="" /></p>
	<p><input type="button" class="rtmedia-image-edit" id="imgedit-open-btn-' . $media_id . '" onclick="imageEdit.open( \'' . $media_id . '\', \'' . $nonce . '\' )" class="button" value="Modifiy Image"> <span class="spinner"></span></p></div>';
        echo '</div>';
    }
}

function rtmedia_comment_form() {

    $html = '<form method="post" action="' . get_rtmedia_permalink(rtmedia_id()) . 'comment/" style="width: 400px;">';
    $html .= '<textarea rows="4" name="comment_content" id="comment_content"></textarea>';
    $html .= '<input type="submit" value="'.__('Comment','rtmedia').'">';
    echo $html;
    RTMediaComment::comment_nonce_generator();
    echo '</form>';
}

function rtmedia_delete_form() {

    $html = '<form method="post" acction="' . get_rtmedia_permalink(rtmedia_id()) . 'delete/">';
    $html .= '<input type="hidden" name="id" id="id" value="' . rtmedia_id() . '">';
    $html .= '<input type="hidden" name="request_action" id="request_action" value="delete">';
    echo $html;
    RTMediaMedia::media_nonce_generator(rtmedia_id(), true);
    echo '<input type="submit" value="'.__('Delete','rtmedia').'"></form>';
}

/**
 *
 * @param type $attr
 */
function rtmedia_uploader($attr = '') {

    if (function_exists('bp_is_blog_page') && !bp_is_blog_page()) {
        if (function_exists('bp_is_user') && bp_is_user() && function_exists('bp_displayed_user_id') && bp_displayed_user_id() == get_current_user_id())
            echo RTMediaUploadShortcode::pre_render($attr);
        else if (function_exists('bp_is_group') && bp_is_group() && function_exists('bp_group_is_member') && bp_group_is_member())
            echo RTMediaUploadShortcode::pre_render($attr);
    }
}

function rtmedia_gallery($attr = '') {
    echo RTMediaGalleryShortcode::render($attr);
}

function get_rtmedia_meta($id = false, $key = false) {
    $rtmediameta = new RTMediaMeta();
    return $rtmediameta->get_meta($id, $key);
}

function add_rtmedia_meta($id = false, $key = false, $value = false, $duplicate = false) {
    $rtmediameta = new RTMediaMeta($id, $key, $value, $duplicate);
    return $rtmediameta->add_meta($id, $key, $value, $duplicate);
}

function update_rtmedia_meta($id = false, $key = false, $value = false, $duplicate = false) {
    $rtmediameta = new RTMediaMeta();
    return $rtmediameta->update_meta($id, $key, $value, $duplicate);
}

function delete_rtmedia_meta($id = false, $key = false) {
    $rtmediameta = new RTMediaMeta();
    return $rtmediameta->delete_meta($id, $key);
}

function rtmedia_user_album_list() {
    global $rtmedia_query;
    $global_albums = RTMediaAlbum::get_globals();	//get_site_option('rtmedia-global-albums');

    $option = NULL;
    foreach ($global_albums as $album) {
        $model = new RTMediaModel();
        $album_object = $model->get_media(array('id' => $album), false, false);
        $global_album_ids = array();
        if ( isset($album_object[0]) ) {
            $global_album_ids[] = $album_object[0]->id;
            if ((isset($rtmedia_query->media_query['album_id']) && ($album_object[0]->id != $rtmedia_query->media_query['album_id'])) || !isset($rtmedia_query->media_query['album_id']))
                $option .= '<option value="' . $album_object[0]->id . '">' . $album_object[0]->media_title . '</option>';
        }
    }
    $album_objects = $model->get_media(array('media_author' => get_current_user_id(), 'media_type' => 'album'), false, false);
    if ($album_objects) {
        foreach ($album_objects as $album) {
            if (!in_array($album->id, $global_album_ids) && (( isset($rtmedia_query->media_query['album_id']) && ($album->id != $rtmedia_query->media_query['album_id'])) || !isset($rtmedia_query->media_query['album_id']) ))
                $option .= '<option value="' . $album->id . '">' . $album->media_title . '</option>';
        }
    }

    if ($option)
        return $option;
    else
        return false;
}

add_action('rtmedia_before_media_gallery', 'rtmedia_create_album');

function rtmedia_create_album() {
    global $rtmedia_query;

    if (function_exists('bp_displayed_user_id') && bp_displayed_user_id() == get_current_user_id()) {
        if (isset($rtmedia_query->query['context']) && !isset($rtmedia_query->media_query['album_id']) && in_array($rtmedia_query->query['context'], array('profile', 'group'))) {
            ?>
            <input type=button class="button rtmedia-create-new-album-button" value="Create New Album" />
            <div class="rtmedia-create-new-album-container">
                <input type="text" class="rtmedia-new-album-name" value="" />
                <input type="submit" class="rtmedia-create-new-album" value="Create Album" />
            </div><?php
        }
    }
}

add_action('rtmedia_before_media_gallery', 'rtmedia_album_edit');

function rtmedia_album_edit() {

    if (!is_rtmedia_album() || !is_user_logged_in())
        return;

    global $rtmedia_query;
    if (isset($rtmedia_query->media_query) && get_current_user_id() == $rtmedia_query->media_query['media_author'] && !in_arraY($rtmedia_query->media_query['album_id'], get_site_option('rtmedia-global-albums'))) {
        ?>
        <a class="alignleft" href="edit/"><input type="button" class="button rtmedia-edit" value="<?php _e('Edit', 'rtmedia'); ?>" /></a>
        <form method="post" class="album-delete-form alignleft" action="delete/">
        <?php wp_nonce_field('rtmedia_delete_album_' . $rtmedia_query->media_query['album_id'], 'rtmedia_delete_album_nonce'); ?>
            <input type="submit" name="album-delete" value="<?php _e('Delete', 'rtmedia'); ?>" />
        </form>
        <?php if ($album_list = rtmedia_user_album_list()) { ?>
            <input type="button" class="button rtmedia-merge" value="<?php _e('Merge', 'rtmedia'); ?>" />
            <div class="rtmedia-merge-container">
            <?php _e('Merge to', 'rtmedia'); ?>
                <form method="post" class="album-merge-form" action="merge/">
                <?php echo '<select name="album" class="rtmedia-merge-user-album-list">' . $album_list . '</select>'; ?>
                    <?php wp_nonce_field('rtmedia_merge_album_' . $rtmedia_query->media_query['album_id'], 'rtmedia_merge_album_nonce'); ?>
                    <input type="submit" class="rtmedia-move-selected" name="merge-album" value="<?php _e('Merge Album', 'rtmedia'); ?>" />
                </form>
            </div>
            <?php
        }
    }
}

add_action('rtmedia_before_item', 'rtmedia_item_select');

function rtmedia_item_select() {
    global $rtmedia_query, $rtmedia_backbone;
	if ($rtmedia_backbone['backbone']) {
		if( $rtmedia_backbone['is_album'] && $rtmedia_backbone['is_edit_allowed'])
			echo '<input type="checkbox" name="move[]" value="<%= id %>" />';
    } else if (is_rtmedia_album() && isset($rtmedia_query->media_query) && get_current_user_id() == $rtmedia_query->media_query['media_author'] && $rtmedia_query->action_query->action == 'edit') {
        echo '<input type="checkbox" name="selected[]" value="' . rtmedia_id() . '" />';
    }
}

add_action('rtmedia_query_actions', 'rtmedia_album_merge_action');

function rtmedia_album_merge_action($actions) {
    $actions['merge'] = __('Merge', 'rtmedia');
    return $actions;
}

function rtmedia_sub_nav(){
	RTMediaNav::sub_nav();
}
?>
