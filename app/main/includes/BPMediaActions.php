<?php

/**
 * Description of BPMediaActions
 *
 * @author faishal
 */
class BPMediaActions {

    function __construct() {
        add_action('bp_media_before_content', 'BPMediaActions::show_messages');
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'), 11);
        add_action('bp_before_activity_delete', 'BPMediaActions::delete_activity_handler');
        add_action('wp_enqueue_scripts', array($this, 'upload_enqueue'));
        add_action('init', 'BPMediaActions::init_count');
        add_action('bp_activity_entry_meta', array($this, 'action_buttons'));
        add_action('bp_media_before_delete_media', 'BPMediaActions::delete_media_handler');
        add_action('bp_media_after_add_album', array($this, 'album_create_activity'));
        add_action('bp_media_album_updated', array($this, 'album_activity_update'));
        add_action('bp_media_after_delete_media', array($this, 'album_activity_sync'));
        add_action('bp_media_after_add_media', 'BPMediaActions::activity_create_after_add_media', 10, 2);
        add_action('wp_ajax_bp_media_load_more', array($this, 'load_more'));
        add_action('wp_ajax_nopriv_bp_media_load_more', array($this, 'load_more'));
        add_action('delete_attachment', array($this, 'delete_attachment_handler'));
        add_action('wp_ajax_bp_media_add_album', array($this, 'add_album'));
        add_action('wp_ajax_nopriv_bp_media_add_album', array($this, 'add_album'));
        global $bp_media_options;
        if (isset($bp_media_options['remove_linkback']) && $bp_media_options['remove_linkback'] != '1')
            add_action('bp_footer', array($this, 'footer'));
    }

    /**
     * Handles the uploads and creates respective posts for the upload
     *
     * @since BuddyPress Media 2.0
     */
    static function handle_uploads() {
        global $bp, $bp_media_options;
        $bp_media_options = get_site_option('bp_media_options', array(
            'videos_enabled' => true,
            'audio_enabled' => true,
            'images_enabled' => true,
                ));
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
                            $bp->{BP_MEDIA_SLUG}->messages['error'][] = __('Image uploads are disabled');
                            return;
                        }
                        break;
                    case 'video' :
                        if ($bp_media_options['videos_enabled'] == false) {
                            $bp->{BP_MEDIA_SLUG}->messages['error'][] = __('Video uploads are disabled');
                            return;
                        }
                        break;
                    case 'audio' :
                        if ($bp_media_options['audio_enabled'] == false) {
                            $bp->{BP_MEDIA_SLUG}->messages['error'][] = __('Audio uploads are disabled');
                            return;
                        }
                        break;
                    default :
                        $bp->{BP_MEDIA_SLUG}->messages['error'][] = __('File uploaded is not supported');
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

//add_action('bp_init', 'handle_uploads');

    /**
     * Displays the messages that other functions/methods creates according to the BuddyPress' formating
     *
     * @since BuddyPress Media 2.0
     */
    static function show_messages() {
        global $bp;
        if (is_array($bp->{BP_MEDIA_SLUG}->messages)) {
            $types = array('error', 'updated', 'info');
            foreach ($types as $type) {
                if (count($bp->{BP_MEDIA_SLUG}->messages[$type]) > 0) {
                    BPMediaFunction::show_formatted_error_message($bp->{BP_MEDIA_SLUG}->messages[$type], $type);
                }
            }
        }
    }

    /**
     * Enqueues all the required scripts and stylesheets for the proper working of BuddyPress Media.
     *
     * @since BuddyPress Media 2.0
     */
    function enqueue_scripts_styles() {

        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('bp-media-mejs', BP_MEDIA_URL . 'lib/media-element/mediaelement-and-player.min.js');
        wp_enqueue_script('bp-media-default', BP_MEDIA_URL . 'app/assets/js/main.js');

        global $bp;
        $cur_group_id = NULL;
        if (bp_is_active("groups"))
            $cur_group_id = bp_get_current_group_id();
        $bp_media_vars = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'page' => 1,
            'current_action' => $cur_group_id ? (empty($bp->action_variables) ? BP_MEDIA_IMAGES_SLUG : $bp->action_variables[0]) : (isset($bp->current_action) ? $bp->current_action : false),
            'action_variables' => isset($bp->action_variables) ? (empty($bp->action_variables) ? array(BP_MEDIA_IMAGES_SLUG) : $bp->action_variables) : array(BP_MEDIA_IMAGES_SLUG),
            'displayed_user' => bp_displayed_user_id(),
            'loggedin_user' => bp_loggedin_user_id(),
            'current_group' => $cur_group_id,
            'feature' => __('Featured', BP_MEDIA_TXT_DOMAIN),
            'removefeature' => __('Remove Featured', BP_MEDIA_TXT_DOMAIN)
        );

        wp_localize_script('bp-media-default', 'bp_media_vars', $bp_media_vars);
        wp_enqueue_style('bp-media-mecss', BP_MEDIA_URL . 'lib/media-element/mediaelementplayer.min.css');
        wp_enqueue_style('bp-media-default', BP_MEDIA_URL . 'app/assets/css/main.css');
    }

    static function delete_activity_handler($args) {
        remove_action('bp_media_before_delete_media', 'BPMediaActions::delete_media_handler');
        global $bp_media_count, $wpdb;
        if (!array_key_exists('id', $args))
            return;

        $activity_id = $args['id'];
        if (intval($activity_id)) {
            $query = "SELECT post_id from $wpdb->postmeta WHERE meta_key='bp_media_child_activity' AND meta_value={$activity_id}";
            $result = $wpdb->get_results($query);
            if (!(is_array($result) && count($result) == 1 ))
                return;
            $post_id = $result[0]->post_id;
            try {
                $post = get_post($post_id);
                if (!isset($post->post_type))
                    return false;
                switch ($post->post_type) {
                    case 'attachment':
                        $media = new BPMediaHostWordpress($post_id);
                        $media->delete_media();
                        break;
                    case 'bp_media_album':
                        $album = new BPMediaAlbum($post_id);
                        $album->delete_album();
                        break;
                    default:
                        wp_delete_post($post_id);
                }
            } catch (Exception $e) {
                error_log('Media tried to delete was already deleted');
            }
        }
    }

    static function delete_media_handler($media_id) {
        /* @var $media BPMediaHostWordpress */
        remove_action('bp_before_activity_delete', 'BPMediaActions::delete_activity_handler');
        $activity_id = get_post_meta($media_id, 'bp_media_child_activity', true);
        if ($activity_id == NULL)
            return false;
        bp_activity_delete_by_activity_id($activity_id);
    }

    /**
     * Called on bp_init by screen functions
     *
     * @uses global $bp, $bp_media_query
     *
     * @since BuddyPress Media 2.0
     */
    function set_query() {
        global $bp, $bp_media_query, $bp_media_posts_per_page;
        switch ($bp->current_action) {
            case BP_MEDIA_IMAGES_SLUG:
                $type = 'image';
                break;
            case BP_MEDIA_AUDIO_SLUG:
                $type = 'audio';
                break;
            case BP_MEDIA_VIDEOS_SLUG:
                $type = 'video';
                break;
            default :
                $type = null;
        }
        if (isset($bp->action_variables) && is_array($bp->action_variables) && isset($bp->action_variables[0]) && $bp->action_variables[0] == 'page' && isset($bp->action_variables[1]) && is_numeric($bp->action_variables[1])) {
            $paged = $bp->action_variables[1];
        } else {
            $paged = 1;
        }
        if ($type) {
            $args = array(
                'post_type' => 'attachment',
                'post_status' => 'any',
                'post_mime_type' => $type,
                'author' => $bp->displayed_user->id,
                'meta_key' => 'bp-media-key',
                'meta_value' => $bp->displayed_user->id,
                'meta_compare' => '=',
                'paged' => $paged,
                'posts_per_page' => $bp_media_posts_per_page
            );
            $bp_media_query = new WP_Query($args);
        }
    }

    /**
     * Adds a download button and edit button on single entry pages of media files.
     *
     * @uses $bp_media_options Global variable
     *
     * @since BuddyPress Media 2.0
     */
    function action_buttons() {
        if (!in_array('bp_media_current_entry', $GLOBALS))
            return false;
        global $bp_media_current_entry, $bp_media_options;

        if ($bp_media_current_entry != NULL) {
            $featured_post = get_post_meta($bp_media_current_entry->get_id(), 'featured', true);

            if (bp_displayed_user_id() == bp_loggedin_user_id())
                echo '<a href="' . $bp_media_current_entry->get_edit_url()
                . '" class="button item-button bp-secondary-action bp-media-edit" title="'
                . __('Edit Media', BP_MEDIA_TXT_DOMAIN) . '">' . __('Edit', BP_MEDIA_TXT_DOMAIN) . '</a>';

            if ($bp_media_options['download_enabled'] == true)
                echo '<a href="' . $bp_media_current_entry->get_attachment_url()
                . '" class="button item-button bp-secondary-action bp-media-download" title="'
                . __('Download', BP_MEDIA_TXT_DOMAIN) . '">' . __('Download', BP_MEDIA_TXT_DOMAIN) . '</a>';

            if (bp_displayed_user_id() == bp_loggedin_user_id() && $featured_post == '')
                echo '<a href="' . $bp_media_current_entry->get_album_id()
                . '" rel="" data-album-id="' . $bp_media_current_entry->get_album_id()
                . '"  data-post-id="' . $bp_media_current_entry->get_id()
                . '" class="button item-button bp-secondary-action bp-media-featured" title="'
                . __('Featured Media', BP_MEDIA_TXT_DOMAIN) . '">' . __('Featured', BP_MEDIA_TXT_DOMAIN) . '</a>';
            else
                echo '<a href="' . $bp_media_current_entry->get_album_id() . '" rel="" data-remove-featured="1"   data-album-id="'
                . $bp_media_current_entry->get_album_id() . '" data-post-id="' . $bp_media_current_entry->get_id()
                . '" class="button item-button bp-secondary-action bp-media-featured" title="'
                . __('Featured Media', BP_MEDIA_TXT_DOMAIN) . '">' . __('Remove Featured', BP_MEDIA_TXT_DOMAIN) . '</a>';
        }
    }

    /* Should be used with Content Disposition Type for media files set to attachment */

    /**
     * Shows the media count of a user in the tabs
     *
     * @since BuddyPress Media 2.0
     */
    static function init_count($user = null) {
        global $bp_media_count;
        if (!$user)
            $user = bp_displayed_user_id();
        if ($user < 1) {
            $bp_media_count = null;
            return false;
        }
        $count = bp_get_user_meta($user, 'bp_media_count', true);
        if (!$count) {
            $bp_media_count = array('images' => 0, 'videos' => 0, 'audio' => 0, 'albums' => 0);
            bp_update_user_meta($user, 'bp_media_count', $bp_media_count);
        } else {
            $bp_media_count = $count;
        }
        add_filter('bp_get_displayed_user_nav_' . BP_MEDIA_SLUG, 'BPMediaFilters::items_count_filter', 10, 2);

        if (bp_current_component() == BP_MEDIA_SLUG) {
            add_filter('bp_get_options_nav_' . BP_MEDIA_IMAGES_SLUG, 'BPMediaFilters::items_count_filter', 10, 2);
            add_filter('bp_get_options_nav_' . BP_MEDIA_VIDEOS_SLUG, 'BPMediaFilters::items_count_filter', 10, 2);
            add_filter('bp_get_options_nav_' . BP_MEDIA_AUDIO_SLUG, 'BPMediaFilters::items_count_filter', 10, 2);
            add_filter('bp_get_options_nav_' . BP_MEDIA_ALBUMS_SLUG, 'BPMediaFilters::items_count_filter', 10, 2);
        }
        return true;
    }

    /**
     * Displays the footer of the BuddyPress Media Plugin if enabled through the dashboard options page
     *
     * @since BuddyPress Media 2.0
     */
    function footer() {
        ?>
        <div id="bp-media-footer"><p>Using <a title="BuddyPress Media adds photos, video and audio upload/management feature" href="http://rtcamp.com/buddypress-media/">BuddyPress Media</a>.</p></div>
        <?php
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
        wp_enqueue_style('bp-media-default', BP_MEDIA_URL . 'app/assets/css/main.css');
//	wp_enqueue_style("wp-jquery-ui-dialog"); //Its not styling the Dialog box as it should so using different styling
        //wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
    }

//This is used only on the uploads page so its added as action in the screens function of upload page.

    /**
     * Called on bp_init by screen functions
     *
     * @uses global $bp, $bp_media_albums_query
     *
     * @since BuddyPress Media 2.2
     */
    function albums_set_query() {
        global $bp, $bp_media_albums_query;
        if (isset($bp->action_variables) && is_array($bp->action_variables) && isset($bp->action_variables[0]) && $bp->action_variables[0] == 'page' && isset($bp->action_variables[1]) && is_numeric($bp->action_variables[1])) {
            $paged = $bp->action_variables[1];
        } else {
            $paged = 1;
        }
        if ($bp->current_action == BP_MEDIA_ALBUMS_SLUG) {
            $args = array(
                'post_type' => 'bp_media_album',
                'author' => $bp->displayed_user->id,
                'paged' => $paged,
                'meta_key' => 'bp-media-key',
                'meta_value' => $bp->displayed_user->id,
                'meta_compare' => '='
            );
            $bp_media_albums_query = new WP_Query($args);
        }
    }

    /**
     * Function to return the media for the ajax requests
     */
    function load_more() {

        global $bp, $bp_media_query, $bp_media_posts_per_page;
        $page = isset($_POST['page']) ? $_POST['page'] : die();
        $current_action = isset($_POST['current_action']) ? $_POST['current_action'] : null;
        $action_variables = isset($_POST['action_variables']) ? $_POST['action_variables'] : null;
        $displayed_user = isset($_POST['displayed_user']) ? $_POST['displayed_user'] : null;
        $loggedin_user = isset($_POST['loggedin_user']) ? $_POST['loggedin_user'] : null;
        $current_group = isset($_POST['current_group']) ? $_POST['current_group'] : null;
        if ((!$displayed_user || intval($displayed_user) == 0) && (!$current_group || intval($current_group) == 0)) {
            die();
        }
        switch ($current_action) {
            case BP_MEDIA_IMAGES_SLUG:
                $args = array(
                    'post_type' => 'attachment',
                    'post_status' => 'any',
                    'post_mime_type' => 'image',
                    'meta_key' => 'bp-media-key',
                    'meta_value' => $current_group > 0 ? -$current_group : $bp->displayed_user->id,
                    'meta_compare' => '=',
                    'paged' => $page,
                    'posts_per_page' => $bp_media_posts_per_page
                );
                break;
            case BP_MEDIA_AUDIO_SLUG:
                $args = array(
                    'post_type' => 'attachment',
                    'post_status' => 'any',
                    'post_mime_type' => 'audio',
                    'author' => $bp->displayed_user->id,
                    'meta_key' => 'bp-media-key',
                    'meta_value' => $current_group > 0 ? -$current_group : $bp->displayed_user->id,
                    'meta_compare' => '=',
                    'paged' => $page,
                    'posts_per_page' => $bp_media_posts_per_page
                );
                break;
            case BP_MEDIA_VIDEOS_SLUG:
                $args = array(
                    'post_type' => 'attachment',
                    'post_status' => 'any',
                    'post_mime_type' => 'video',
                    'author' => $bp->displayed_user->id,
                    'meta_key' => 'bp-media-key',
                    'meta_value' => $current_group > 0 ? -$current_group : $bp->displayed_user->id,
                    'meta_compare' => '=',
                    'paged' => $page,
                    'posts_per_page' => $bp_media_posts_per_page
                );
                break;
            case BP_MEDIA_ALBUMS_SLUG:
                if (isset($action_variables) && is_array($action_variables) && isset($action_variables[0]) && isset($action_variables[1])) {
                    $args = array(
                        'post_type' => 'attachment',
                        'post_status' => 'any',
                        'author' => $displayed_user,
                        'post_parent' => $action_variables[1],
                        'paged' => $page,
                        'posts_per_page' => $bp_media_posts_per_page
                    );
                } else {
                    $args = array(
                        'post_type' => 'bp_media_album',
                        'author' => $displayed_user,
                        'paged' => $page,
                        'posts_per_page' => $bp_media_posts_per_page
                    );
                }
                break;
            default:
                die();
        }
        $bp_media_query = new WP_Query($args);
        if (isset($bp_media_query->posts) && is_array($bp_media_query->posts) && count($bp_media_query->posts)) {
            foreach ($bp_media_query->posts as $attachment) {
                try {
                    $media = new BPMediaHostWordpress($attachment->ID);
                    echo $media->get_media_gallery_content();
                } catch (exception $e) {
                    die();
                }
            }
        }
        die();
    }

    function delete_attachment_handler($attachment_id) {
        if (get_post_meta($attachment_id, 'bp-media-key')) {
            do_action('bp_media_before_delete_media', $attachment_id);
            global $bp_media_count;
            $attachment = get_post($attachment_id);
            preg_match_all('/audio|video|image/i', $attachment->post_mime_type, $result);
            if (isset($result[0][0]))
                $type = $result[0][0];
            else
                return false;
            BPMediaActions::init_count($attachment->post_author);
            switch ($type) {
                case 'image':
                    $images = intval($bp_media_count['images']) ? intval($bp_media_count['images']) : 0;
                    $bp_media_count['images'] = $images - 1;
                    break;
                case 'audio':
                    $bp_media_count['audio'] = intval($bp_media_count['audio']) - 1;
                    break;
                case 'video':
                    $bp_media_count['videos'] = intval($bp_media_count['videos']) - 1;
                    break;
                default:
                    return false;
            }
            bp_update_user_meta($attachment->post_author, 'bp_media_count', $bp_media_count);
            do_action('bp_media_after_delete_media', $attachment_id);
            return true;
        }
    }

    /**
     * Function to create new album called via ajax request
     */
    function add_album() {
        if (isset($_POST['bp_media_album_name']) && $_POST['bp_media_album_name'] != '') {
            $album = new BPMediaAlbum();
            if (isset($_POST['bp_media_group_id']) && intval($_POST['bp_media_group_id']) > 0) {
                $group_id = intval($_POST['bp_media_group_id']);
                if (BPMediaGroup::user_can_create_album($group_id, get_current_user_id())) {
                    try {
                        $album->add_album($_POST['bp_media_album_name'], 0, $group_id);
                        echo $album->get_id();
                    } catch (exception $e) {
                        echo '0';
                    }
                } else {
                    echo '0';
                }
            } else {
                try {
                    $album->add_album($_POST['bp_media_album_name']);
                    echo $album->get_id();
                } catch (exception $e) {
                    echo '0';
                }
            }
        } else {
            echo '0';
        }
        die();
    }

    function add_new_from_activity() {
        BPMediaTemplateFunctions::show_upload_form_multiple_activity();
    }

//add_action('bp_after_activity_post_form','add_new_from_activity');


    function album_create_activity($album) {
        /* @var $album BP_Media_Album */
        $args = array(
            'action' => apply_filters('bp_media_album_created', sprintf(__('%1$s created an album %2$s', BP_MEDIA_TXT_DOMAIN), bp_core_get_userlink($album->get_owner()), '<a href="' . $album->get_url() . '">' . $album->get_title() . '</a>')),
            'component' => BP_MEDIA_SLUG,
            'type' => 'album_created',
            'primary_link' => $album->get_url(),
            'user_id' => $album->get_owner(),
            'item_id' => $album->get_id()
        );
        $activity_id = BPMediaFunction::record_activity($args);
        update_post_meta($album->get_id(), 'bp_media_child_activity', $activity_id);
    }

    function album_activity_update($album_id) {
        BPMediaFunction::update_album_activity($album_id);
    }

    function album_activity_sync($media_id) {
        $album_id = wp_get_post_parent_id($media_id);
        BPMediaFunction::update_album_activity($album_id, false, $media_id);
    }

    static function activity_create_after_add_media($media, $hidden = false) {
        if (function_exists('bp_activity_add')) {
            if (!is_object($media)) {
                try {
                    $media = new BPMediaHostWordpress($media);
                } catch (exception $e) {
                    return false;
                }
            }
			$activity_content = $media->get_media_activity_content();
			new BPMediaLog($activity_content);
            $args = array(
                'action' => apply_filters('bp_media_added_media', sprintf(__('%1$s added a %2$s', BP_MEDIA_TXT_DOMAIN), bp_core_get_userlink($media->get_author()), '<a href="' . $media->get_url() . '">' . $media->get_media_activity_type() . '</a>')),
                'content' => $activity_content,
                'primary_link' => $media->get_url(),
                'item_id' => $media->get_id(),
                'type' => 'media_upload',
                'user_id' => $media->get_author()
            );
            $hidden = apply_filters('bp_media_force_hide_activity', $hidden);
            if ($hidden) {
                $args['secondary_item_id'] = -999;
                do_action('bp_media_album_updated', $media->get_album_id());
            }
            $activity_id = BPMediaFunction::record_activity($args);
            add_post_meta($media->get_id(), 'bp_media_child_activity', $activity_id);
        }
    }

}
?>
