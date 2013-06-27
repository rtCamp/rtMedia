<?php

/**
 * Description of RTMediaTemplate
 *
 * Template to display rtMedia Gallery.
 * A stand alone template that renders the gallery/uploader on the page.
 *
 * @author saurabh
 */
class RTMediaTemplate {

    public $media_args;

    function __construct() {

        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('init', array($this, 'enqueue_image_editor_scripts'));
    }

    /**
     * Enqueues required scripts on the page
     */
    function enqueue_scripts() {
        wp_enqueue_script('rtmedia-backbone');
    }

    function enqueue_image_editor_scripts() {
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_script('wp-ajax-response');
        wp_enqueue_script('rt-media-image-edit', admin_url("js/image-edit$suffix.js"), array('jquery', 'json2', 'imgareaselect'), false, 1);
        wp_enqueue_style('rt-media-image-edit', RTMEDIA_URL . 'app/assets/css/image-edit.css');
    }

    /**
     * redirects to the template according to the page request
     * Pass on the shortcode attributes to the template so that the shortcode can berendered accordingly.
     *
     * Also handles the json request coming from the AJAX calls for the media
     *
     * @global type $rt_media_query
     * @global type $rt_media_interaction
     * @param type $template
     * @param type $shortcode_attr
     * @return type
     */
    function set_template($template, $shortcode_attr = false) {

        global $rt_media_query, $rt_media_interaction, $rt_media_media;

        do_action('rtmedia_pre_template');

        do_action('rtmedia_pre_action_' . $rt_media_query->action_query->action);

        if (in_array($rt_media_interaction->context->type, array("profile", "group"))) {
            if ($rt_media_query->format == 'json') {
                $media_array = array();
                if ($rt_media_query->media) {
                    foreach ($rt_media_query->media as $key => $media) {
                        $media_array[$key] = $media;
                        list($src, $width, $height) = wp_get_attachment_image_src($media->media_id, 'thumbnail');
                        $media_array[$key]->guid = $src;
                        $media_array[$key]->rt_permalink = get_rt_media_permalink($media->id);
                    }
                }
                $return_array['data'] = $media_array;
                $return_array['prev'] = rt_media_page() - 1;
                $return_array['next'] = (rt_media_offset() + rt_media_per_page_media() < rt_media_count()) ? (rt_media_page() + 1) : -1;
                echo json_encode($return_array);
                die;
            } else if ($rt_media_query->action_query->action == 'edit' && count($_POST)) {
                /**
                 * /media/id/edit [POST]
                 * save details of media
                 *
                 */
                if (is_rt_media_single()) {
                    $nonce = $_REQUEST['rt_media_media_nonce'];
                    if (wp_verify_nonce($nonce, 'rt_media_' . $rt_media_query->action_query->id)) {
                        $data = $_POST;
                        unset($data['rt_media_media_nonce']);
                        unset($data['_wp_http_referer']);
                        $media = new RTMediaMedia();
                        $media->update($rt_media_query->action_query->id, $data, $rt_media_query->media[0]->media_id);
                        $rt_media_query->query(false);
                    } else {
                        echo __("Ooops !!! Invalid access. No nonce was found !!", "rt-media");
                    }
                } elseif (is_rt_media_album()) {
                    $nonce = $_REQUEST['rt_media_media_nonce'];
                    if (wp_verify_nonce($nonce, 'rt_media_' . $rt_media_query->media_query['album_id'])) {
                        $media = new RTMediaMedia();
                        $model = new RTMediaModel();
                        if (isset($_POST['submit'])) {
                            $data = $_POST;
                            unset($data['rt_media_media_nonce']);
                            unset($data['_wp_http_referer']);
                            unset($data['submit']);
                            $album = $model->get_media(array('id' => $rt_media_query->media_query['album_id']), false, false);
                            $media->update($album[0]->id, $data, $album[0]->media_id);
                        } elseif (isset($_POST['move-selected'])) {
//                            print_r($_POST);die;
                            $album_move = $_POST['album'];
                            $selected_ids = NULL;

                            if (isset($_POST['selected'])) {
                                $selected_ids = $_POST['selected'];
                                unset($_POST['selected']);
                            }
                            if (!empty($selected_ids) && is_array($selected_ids)) {
                                $album_move_details = $model->get_media(array('id' => $album_move), false, false);
                                foreach ($selected_ids as $media_id) {
                                    $media_details = $model->get_media(array('id' => $media_id), false, false);
                                    $post_array['ID'] = $media_details[0]->media_id;
                                    $post_array['post_parent'] = $album_move_details[0]->media_id;
                                    wp_update_post($post_array);
                                    $media->update($media_details[0]->id, array('album_id' => $album_move_details[0]->id), $media_details[0]->media_id);
                                }
                            }
                        }
                        wp_safe_redirect(get_rt_media_permalink($rt_media_query->media_query['album_id']) . 'edit/');
                    } else {
                        echo __("Ooops !!! Invalid access. No nonce was found !!", "rt-media");
                    }
                }
                return $this->get_default_template();
            } elseif ($rt_media_query->action_query->action == 'delete' && isset($rt_media_query->action_query->default) && $rt_media_query->action_query->default == 'delete' && count($_POST)) {
                $nonce = $_REQUEST['rt_media_bulk_delete_nonce'];

                $media = new RTMediaMedia();
                if (wp_verify_nonce($nonce, 'rt_media_bulk_delete_nonce') && isset($_POST['selected'])) {
                    $ids = $_POST['selected'];
                    foreach ($ids as $id) {
                        $media->delete($id);
                    }
                }
                wp_safe_redirect($_POST['_wp_http_referer']);
            } else if ($rt_media_query->action_query->action == 'delete') {

                /**
                 * /media/id/delete [POST]
                 */
                if (is_rt_media_single()) {

                    $nonce = $_REQUEST['rt_media_media_nonce'];
                    if (wp_verify_nonce($nonce, 'rt_media_' . $rt_media_query->media[0]->id)) {
                        $id = $_POST;
                        unset($id['rt_media_media_nonce']);
                        unset($id['_wp_http_referer']);
                        $media = new RTMediaMedia();
                        $media->delete($rt_media_query->media[0]->id);

                        $post = get_post($rt_media_query->media[0]);

                        $parent_link = '';
                        if (function_exists('bp_core_get_user_domain')) {
                            $parent_link = bp_core_get_user_domain($post->media_author);
                        } else {
                            $parent_link = get_author_posts_url($post->media_author);
                        }

                        wp_redirect($parent_link);
                    } else {
                        echo __("Ooops !!! Invalid access. No nonce was found !!", "rt-media");
                    }
                } elseif (is_rt_media_album() && count($_POST)) {

                    $nonce = $_REQUEST['rt_media_delete_album_nonce'];
                    if (wp_verify_nonce($nonce, 'rt_media_delete_album_' . $rt_media_query->media_query['album_id'])) {
                        $media = new RTMediaMedia();
                        $model = new RTMediaModel();
                        $album_contents = $model->get(array('album_id' => $rt_media_query->media_query['album_id']), false, false);
                        foreach ($album_contents as $album_media) {
                            $media->delete($album_media->id);
                        }
                        $media->delete($rt_media_query->media_query['album_id']);
                    }
                    wp_safe_redirect(get_rt_media_user_link(get_current_user_id()) . 'media/album/');
                    exit;
                }
                return $this->get_default_template();
            } elseif ($rt_media_query->action_query->action == 'merge') {
                $nonce = $_REQUEST['rt_media_merge_album_nonce'];
                if (wp_verify_nonce($nonce, 'rt_media_merge_album_' . $rt_media_query->media_query['album_id'])) {
                    $media = new RTMediaMedia();
                    $model = new RTMediaModel();
                    $album_contents = $model->get(array('album_id' => $rt_media_query->media_query['album_id']), false, false);
//                    print_r($album_contents); die;
                    $album_move_details = $model->get_media(array('id' => $_POST['album']), false, false);
                    foreach ($album_contents as $album_media) {
                        
                            $media_details = $model->get_media(array('id' => $album_media->id), false, false);
                            $post_array['ID'] = $album_media->media_id;
                            $post_array['post_parent'] = $album_move_details[0]->media_id;
                            wp_update_post($post_array);
                            $media->update($album_media->id, array('album_id' => $album_move_details[0]->id), $album_media->media_id);
                    }
                    $media->delete($rt_media_query->media_query['album_id']);
                }
                wp_safe_redirect(get_rt_media_user_link(get_current_user_id()) . 'media/album/');
                exit;
            } else if ($rt_media_query->action_query->action == 'comments') {

                if (isset($rt_media_query->action_query->media_type) && !count($_POST)) {
                    /**
                     * /media/comments [GET]
                     *
                     */
                    $media_array = array();
                    if ($rt_media_query->media) {
                        foreach ($rt_media_query->media as $media) {
                            $media_array[] = $media;
                        }
                    }
                    echo json_encode($media_array);
                    die;
                } else if (isset($rt_media_query->action_query->id) && count($_POST)) {
                    /**
                     * /media/comments [POST]
                     * Post a comment to the album by post id
                     */
                    $nonce = $_REQUEST['rt_media_comment_nonce'];
                    if (wp_verify_nonce($nonce, 'rt_media_comment_nonce')) {
                        $comment = new RTMediaComment();
                        $attr = $_POST;
                        if (!isset($attr['comment_post_ID']))
                            $attr['comment_post_ID'] = $rt_media_query->action_query->id;
                        $comment->add($attr);
                    }
                    else {
                        echo "Ooops !!! Invalid access. No nonce was found !!";
                    }
                }
                return $this->get_default_template();
            } else
                return $this->get_default_template();
        } else if ($rt_media_interaction->context->type == "activity") {
            echo 'Activity Handling';
        } else if ($rt_media_query->action_query->action == 'upload') {
            $upload = new RTMediaUploadEndpoint();
            $upload->template_redirect();
        } else if ($rt_media_query->format == 'json') {

            $media_array = array();
            if ($rt_media_query->media) {
                foreach ($rt_media_query->media as $key => $media) {
                    $media_array[$key] = $media;
                    list($src, $width, $height) = wp_get_attachment_image_src($media->media_id, 'thumbnail');
                    $media_array[$key]->guid = $src;
                    $media_array[$key]->rt_permalink = get_rt_media_permalink($media->id);
                }
            }
            $return_array['data'] = $media_array;
            $return_array['prev'] = rt_media_page() - 1;
            $return_array['next'] = (rt_media_offset() + rt_media_per_page_media() < rt_media_count()) ? (rt_media_page() + 1) : -1;

            echo json_encode($return_array);
            die;
        } else if (!$shortcode_attr)
            return $this->get_default_template();
        else if ($shortcode_attr['name'] == 'gallery') {
            $valid = $this->sanitize_gallery_attributes($shortcode_attr['attr']);

            if ($valid) {
                if (is_array($shortcode_attr['attr']))
                    $this->update_global_query($shortcode_attr['attr']);
                include $this->locate_template($template);
            } else {
                echo 'Invalid attribute passed for rtmedia_gallery shortcode.';
                return false;
            }
        }
    }

    /**
     * Helper method to fetch allowed media types from each section
     *
     * @param type $allowed_type
     * @return type
     */
    function get_allowed_type_name($allowed_type) {
        return $allowed_type['name'];
    }

    /**
     * Validates all the attributes for gallery shortcode
     *
     * @global type $rt_media
     * @param string $attr
     * @return type
     */
    function sanitize_gallery_attributes(&$attr) {
        global $rt_media;

        $flag = true;

        if (isset($attr['media_type'])) {
            $allowed_type_names = array_map(array($this, 'get_allowed_type_name'), $rt_media->allowed_types);

            if (strtolower($attr['media_type']) == 'all') {
                $flag = $flag && true;
                unset($attr['media_type']);
            } else
                $flag = $flag && in_array($attr['media_type'], $allowed_type_names);
        }

        if (isset($attr['order_by'])) {

            $allowed_columns = array('date', 'views', 'downloads', 'ratings', 'likes', 'dislikes');
            $allowed_columns = apply_filters('filter_allowed_sorting_columns', $allowed_columns);

            $flag = $flag && in_array($attr['order_by'], $allowed_columns);

            if (strtolower($attr['order_by']) == 'date')
                $attr['order_by'] = 'media_id';
        }

        if (isset($attr['order'])) {
            $flag = $flag && strtolower($attr['order']) == 'asc' || strtolower($attr['order']) == 'desc';
        }

        return $flag;
    }

    function update_global_query($attr) {

        global $rt_media_query;

        $rt_media_query->query($attr);
    }

    /**
     * filter to change the template path independent of the plugin
     *
     * @return type
     */
    function get_default_template() {

        return apply_filters('rt_media_media_template_include', RTMEDIA_PATH . 'app/main/controllers/template/template.php');
    }

    /**
     * Template Locator
     *
     * @param type $template
     * @return string
     */
    static function locate_template($template, $context = false) {
        $located = '';
        if (!$template)
            return;

        $template_name = $template . '.php';

        if (!$context)
            $context = 'rt-media';

        $path = '/' . $context . '/';
        $ogpath = 'templates/media/';


        if (file_exists(STYLESHEETPATH . $path . $template_name)) {
            $located = STYLESHEETPATH . $path . $template_name;
        } else if (file_exists(TEMPLATEPATH . $path . $template_name)) {
            $located = TEMPLATEPATH . $path . $template_name;
        } else {
            $located = RTMEDIA_PATH . $ogpath . $template_name;
        }

        return $located;
    }

}

?>
