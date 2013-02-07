<?php

/**
 * Description of BPMediaUploadScreen
 *
 * @package BuddyPressMedia
 * @subpackage Profile
 *
 * @author saurabh
 */
class BPMediaUploadScreen extends BPMediaScreen {

    /**
     * 
     * @param type $media_type
     * @param type $slug
     */
    public function __construct($media_type, $slug) {
        parent::__construct($media_type, $slug);
    }

    function upload_screen() {
        if (bp_is_my_profile() || BPMediaGroup::can_upload()) {
            add_action('wp_enqueue_scripts', array($this, 'upload_enqueue'));
            add_action('bp_template_title', array($this, 'upload_screen_title'));
            add_action('bp_template_content', array($this, 'upload_screen_content'));
            $this->template->loader();
        } else
            bp_core_redirect(trailingslashit(bp_displayed_user_domain() . constant('BP_MEDIA_SLUG')));
    }

    function upload_screen_title() {
        _e('Upload Media', BP_MEDIA_TXT_DOMAIN);
    }

    function upload_screen_content() {
        $this->hook_before();

        $this->template->upload_form_multiple();

        $this->hook_after();
    }

    function upload_enqueue() {
        $params = array(
            'url' => BP_MEDIA_URL . 'app/main/includes/bp-media-upload-handler.php',
            'runtimes' => 'gears,html5,flash,silverlight,browserplus',
            'browse_button' => 'bp-media-upload-browse-button',
            'container' => 'bp-media-upload-ui',
            'drop_element' => 'drag-drop-area',
            'filters' => apply_filters('bp_media_plupload_files_filter', array(array('title' => "Media Files", 'extensions' => "mp4,jpg,png,jpeg,gif,mp3"))),
            'max_file_size' => min(array(ini_get('upload_max_filesize'), ini_get('post_max_size'))),
            'multipart' => true,
            'urlstream_upload' => true,
            'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
            'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
            'file_data_name' => 'bp_media_file', // key passed to $_FILE.
            'multi_selection' => true,
            'multipart_params' => apply_filters('bp_media_multipart_params_filter', array('action' => 'wp_handle_upload'))
        );
        wp_enqueue_script('bp-media-uploader', BP_MEDIA_URL . 'app/assets/js/bp-media-uploader.js', array('plupload', 'plupload-html5', 'plupload-flash', 'plupload-silverlight', 'plupload-html4', 'plupload-handlers'));
        wp_localize_script('bp-media-uploader', 'bp_media_uploader_params', $params);
        wp_enqueue_style('bp-media-default', BP_MEDIA_URL . 'app/assets/css/bp-media-style.css');
        //wp_enqueue_style("wp-jquery-ui-dialog"); //Its not styling the Dialog box as it should so using different styling
        //wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
    }

    function upload_handler() {
        ignore_user_abort(true);

        require_once(BP_MEDIA_PATH . 'lib/bootstrap.php');

        // Check for rights
        if (!is_user_logged_in())
            wp_die(__("You are not allowed to be here", BP_MEDIA_TXT_DOMAIN));
    }

    /**
     * 
     * @global type $bp
     * @global type $bp_media_options
     * @return type
     */
    function upload_media() {
        global $bp, $bp_media_options;
        $bp_media_options = get_site_option('bp_media_options', array(
            'videos_enabled' => true,
            'audio_enabled' => true,
            'images_enabled' => true,
                )
        );
        if (isset($_POST['action']) && $_POST['action'] == 'wp_handle_upload') {
            /** This section can help in the group activity handling */
            if (isset($_POST['bp_media_group_id']) && intval($_POST['bp_media_group_id'])) {
                remove_action('bp_media_after_add_media', 'BPMediaActions::activity_create_after_add_media', 10, 2);
                add_action('bp_media_after_add_media', 'BPMediaGroupAction::bp_media_groups_activity_create_after_add_media', 10, 2);
                add_filter('bp_media_force_hide_activity', 'BPMediaGroupAction::bp_media_groups_force_hide_activity');
            }
            /* @var $bp_media_entry BPMediaHostWordpress */
            if (isset($_FILES) && is_array($_FILES) && array_key_exists('bp_media_file', $_FILES) && $_FILES['bp_media_file']['name'] != '') {
                if (!preg_match('/audio|video|image/i', $_FILES['bp_media_file']['type'], $result) || !isset($result[0])) {
                    $bp->{BP_MEDIA_SLUG}->messages['error'][] = __('File uploaded is not supported');
                    return;
                }
                $type = $result[0];
                switch ($result[0]) {
                    case 'image' :
                        if ($bp_media_options['images_enabled'] == false) {
                            $bp->{BP_MEDIA_SLUG}->messages['error'][] = __('Image uploads are disabled', BP_MEDIA_TXT_DOMAIN);
                            return;
                        }
                        break;
                    case 'video' :
                        if ($bp_media_options['videos_enabled'] == false) {
                            $bp->{BP_MEDIA_SLUG}->messages['error'][] = __('Video uploads are disabled', BP_MEDIA_TXT_DOMAIN);
                            return;
                        }
                        break;
                    case 'audio' :
                        if ($bp_media_options['audio_enabled'] == false) {
                            $bp->{BP_MEDIA_SLUG}->messages['error'][] = __('Audio uploads are disabled', BP_MEDIA_TXT_DOMAIN);
                            return;
                        }
                        break;
                    default :
                        $bp->{BP_MEDIA_SLUG}->messages['error'][] = __('File uploaded is not supported', BP_MEDIA_TXT_DOMAIN);
                        return;
                }
                $class_name = apply_filters('bp_media_transcoder', 'BPMediaHostWordpress', $type);
                $bp_media_entry = new $class_name();
                try {
                    $title = isset($_POST['bp_media_title']) ? ($_POST['bp_media_title'] != "") ? $_POST['bp_media_title'] : pathinfo($_FILES['bp_media_file']['name'], PATHINFO_FILENAME)  : pathinfo($_FILES['bp_media_file']['name'], PATHINFO_FILENAME);
                    $album_id = isset($_POST['bp_media_album_id']) ? intval($_POST['bp_media_album_id']) : 0;
                    $is_multiple = isset($_POST['is_multiple_upload']) ? ($_POST['is_multiple_upload'] == 'true' ? true : false) : false;
                    $description = isset($_POST['bp_media_description']) ? $_POST['bp_media_description'] : '';
                    $group_id = isset($_POST['bp_media_group_id']) ? intval($_POST['bp_media_group_id']) : 0;
                    $entry = $bp_media_entry->add_media($title, $description, $album_id, $group_id, $is_multiple);
                    if (!isset($bp->{BP_MEDIA_SLUG}->messages['updated'][0]))
                        $bp->{BP_MEDIA_SLUG}->messages['updated'][0] = __('Upload Successful', BP_MEDIA_TXT_DOMAIN);
                } catch (Exception $e) {
                    $bp->{BP_MEDIA_SLUG}->messages['error'][] = $e->getMessage();
                }
            } else {
                $bp->{BP_MEDIA_SLUG}->messages['error'][] = __('You did not specified a file to upload', BP_MEDIA_TXT_DOMAIN);
            }
        }
    }

}

?>
