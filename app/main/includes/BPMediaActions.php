<?php

/**
 * Description of BPMediaActions
 *
 * @author faishal
 */
class BPMediaActions {

    /**
     *
     * @global type $bp_media_options
     */
    function __construct() {
        add_action('bp_media_before_content', 'BPMediaActions::show_messages');
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'), 11);
        add_action('bp_before_activity_delete', 'BPMediaActions::delete_activity_handler');
        add_action('wp_enqueue_scripts', array($this, 'upload_enqueue'));
        add_action('init', 'BPMediaActions::init_count');
        add_action('init', array($this, 'default_user_album'));
        add_action('bp_init', array($this, 'default_group_album'));
        add_action('bp_activity_entry_meta', array($this, 'action_buttons'));
        add_action('bp_media_before_delete_media', 'BPMediaActions::delete_media_handler');
        add_action('bp_media_after_add_album', array($this, 'album_create_activity'));
        add_action('bp_media_after_add_album', array($this, 'update_count'), 999);
        add_action('bp_media_album_updated', 'BPMediaActions::album_activity_update');
        add_action('bp_media_album_updated', array($this, 'update_count'), 999);
        add_action('bp_media_after_edit_album', array($this, 'update_count'), 999);
        add_action('bp_media_after_delete_album', array($this, 'update_count'), 999);
        add_action('bp_media_after_delete_media', array($this, 'album_activity_sync'));
        add_action('bp_media_after_add_media', 'BPMediaActions::activity_create_after_add_media', 10, 4);
        add_action('wp_ajax_bp_media_load_more', array($this, 'load_more'));
        add_action('wp_ajax_nopriv_bp_media_load_more', array($this, 'load_more'));
        add_action('wp_ajax_bp_media_set_album_cover', array($this, 'set_album_cover'));
        add_action('delete_attachment', array($this, 'delete_attachment_handler'));
        add_action('wp_ajax_bp_media_add_album', array($this, 'add_album'));
        add_action('bp_media_after_privacy_install', array($this, 'update_count'), 999);
        add_action('bp_media_after_add_media', array($this, 'update_count'), 999);
        add_action('bp_media_after_update_media', array($this, 'update_count'), 999);
        add_action('bp_media_after_delete_media', array($this, 'update_count'), 999);
        add_action('bp_before_group_settings_creation_step', array($this, 'group_create_default_album'));
        $linkback = bp_get_option('bp_media_add_linkback', false);
        if ($linkback)
            add_action('bp_footer', array($this, 'footer'));
    }

    /**
     * Handles the uploads and creates respective posts for the upload
     *
     * @since BuddyPress Media 2.0
     */

    /**
     *
     * @global type $bp
     * @global type $bp_media_options
     * @return type
     */
    static function handle_uploads() {
        global $bp, $bp_media;
        $bp_media_options = $bp_media->options;
        if (isset($_POST['action']) && $_POST['action'] == 'wp_handle_upload') {
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
                    $is_activity = isset($_POST['is_activity']) ? ($_POST['is_activity'] == 'true' ? true : false) : false;
                    $description = isset($_POST['bp_media_description']) ? $_POST['bp_media_description'] : '';
                    $group_id = isset($_POST['bp_media_group_id']) ? intval($_POST['bp_media_group_id']) : 0;
                    $entry = $bp_media_entry->add_media($title, $description, $album_id, $group_id, $is_multiple, $is_activity);
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

    /**
     *
     * @global type $bp
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

    /**
     *
     * @global type $bp
     */
    function enqueue_scripts_styles() {
        global $bp_media, $bp;
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('bp-media-mejs', BP_MEDIA_URL . 'lib/media-element/mediaelement-and-player.min.js', '', BP_MEDIA_VERSION);
        wp_enqueue_script('bp-media-default', BP_MEDIA_URL . 'app/assets/js/main.js', '', BP_MEDIA_VERSION);
        $lightbox = isset($bp_media->options['enable_lightbox']) ? $bp_media->options['enable_lightbox'] : 0;
        if ($lightbox)
            wp_enqueue_script('bp-media-modal', BP_MEDIA_URL . 'lib/simplemodal/jquery.simplemodal-1.4.4.js', '', BP_MEDIA_VERSION);
        $cur_group_id = NULL;
        if (bp_is_active("groups"))
            $cur_group_id = bp_get_current_group_id();
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
            $schema = 'https';
        } else {
            $schema = 'http';
        }
        $bp_media_vars = array(
            'ajaxurl' => admin_url('admin-ajax.php', $schema),
            'page' => 1,
            'current_action' => $cur_group_id ? (empty($bp->action_variables) ? BP_MEDIA_IMAGES_SLUG : $bp->action_variables[0]) : (isset($bp->current_action) ? $bp->current_action : false),
            'action_variables' => isset($bp->action_variables) ? (empty($bp->action_variables) ? array(BP_MEDIA_IMAGES_SLUG) : $bp->action_variables) : array(BP_MEDIA_IMAGES_SLUG),
            'displayed_user' => bp_displayed_user_id(),
            'loggedin_user' => bp_loggedin_user_id(),
            'current_group' => $cur_group_id,
            'lightbox' => $lightbox,
        );

        wp_localize_script('bp-media-default', 'bp_media_vars', $bp_media_vars);
        wp_enqueue_style('bp-media-mecss', BP_MEDIA_URL . 'lib/media-element/mediaelementplayer.min.css', '', BP_MEDIA_VERSION);
        wp_enqueue_style('bp-media-default', BP_MEDIA_URL . 'app/assets/css/main.css', '', BP_MEDIA_VERSION);
    }

    /**
     *
     * @global integer $bp_media_count
     * @global object $wpdb
     * @param array $args
     * @return boolean
     */
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

    /**
     *
     * @param type $media_id
     * @return boolean
     */
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

    /**
     *
     * @global type $bp
     * @global WP_Query $bp_media_query
     * @global type $bp_media_posts_per_page
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

    /**
     *
     * @global type $bp_media_current_entry
     * @global type $bp_media_options
     * @return boolean
     */
    function action_buttons() {
        if (!in_array('bp_media_current_entry', $GLOBALS))
            return false;
        global $bp_media_current_entry, $bp_media;

        $action_buttons = array();
        if ($bp_media_current_entry != NULL) {

            if (isset($bp_media->options['download_enabled']))
                $action_buttons[] = '<a href="' . admin_url('admin-ajax.php') . '?action=bp_media_download&file=' . $bp_media_current_entry->get_attachment_url()
                        . '" target="_blank" class="button item-button bp-secondary-action bp-media-download" title="'
                        . __('Download', BP_MEDIA_TXT_DOMAIN) . '">' . __('Download', BP_MEDIA_TXT_DOMAIN) . '</a>';

            if ((bp_displayed_user_id() == bp_loggedin_user_id()) && ($bp_media_current_entry->get_type() == 'image')) {
                if (get_post_thumbnail_id($bp_media_current_entry->get_album_id()) != $bp_media_current_entry->get_id())
                    $action_buttons[] = '<a href="#" data-album-id="' . $bp_media_current_entry->get_album_id()
                            . '"  data-post-id="' . $bp_media_current_entry->get_id()
                            . '" class="button item-button bp-secondary-action bp-media-featured" title="'
                            . __('Set as Album Cover', BP_MEDIA_TXT_DOMAIN) . '">' . __('Set as Album Cover', BP_MEDIA_TXT_DOMAIN) . '</a>';
                else
                    $action_buttons[] = '<a href="#" data-album-id="'
                            . $bp_media_current_entry->get_album_id() . '" data-post-id="' . $bp_media_current_entry->get_id()
                            . '" class="button item-button bp-secondary-action bp-media-featured" title="'
                            . __('Unset as Album Cover', BP_MEDIA_TXT_DOMAIN) . '">' . __('Unset as Album Cover', BP_MEDIA_TXT_DOMAIN) . '</a>';
            }

            if (bp_displayed_user_id() == bp_loggedin_user_id())
                $action_buttons[] = '<a href="' . $bp_media_current_entry->get_edit_url()
                        . '" class="button item-button bp-secondary-action bp-media-edit" title="'
                        . __('Edit Media', BP_MEDIA_TXT_DOMAIN) . '">' . __('Edit', BP_MEDIA_TXT_DOMAIN) . '</a>';
        }
        $action_buttons = apply_filters('bp_media_action_buttons', $action_buttons);
        foreach ($action_buttons as $action_button) {
            echo $action_button;
        }
    }

    /* Should be used with Content Disposition Type for media files set to attachment */

    /**
     * Shows the media count of a user in the tabs
     *
     * @since BuddyPress Media 2.0
     */

    /**
     *
     * @global type $bp_media_count
     * @param type $user
     * @return boolean
     */
    static function init_count($user = null) {
        global $bp_media_count, $bp_media;
        $enabled = $bp_media->enabled();
        $current_access = BPMediaPrivacy::current_access();
        if (!$user)
            $user = bp_displayed_user_id();
        if ($user < 1) {
            $bp_media_count = null;
            return false;
        }
        $count = bp_get_user_meta($user, 'bp_media_count', true);
        if (!$count) {
            $bp_media_count = array(
                0 => array('images' => 0, 'videos' => 0, 'audio' => 0, 'albums' => 0),
                2 => array('images' => 0, 'videos' => 0, 'audio' => 0, 'albums' => 0),
                4 => array('images' => 0, 'videos' => 0, 'audio' => 0, 'albums' => 0),
                6 => array('images' => 0, 'videos' => 0, 'audio' => 0, 'albums' => 0),
            );
            bp_update_user_meta($user, 'bp_media_count', $bp_media_count);
        } else {
            $total = array(
                'images' => 0,
                'videos' => 0,
                'audio' => 0,
                'albums' => 0,
                'total' => 0
            );
            $total_count = 0;
            if (isset($count) && is_array($count) && count($count) > 0) {
                foreach ($count as $level => $counts) {
                    if ($level <= $current_access) {
                        if (isset($counts) && is_array($counts) && count($counts) > 0) {
                            foreach ($counts as $media => $number) {
                                if (array_key_exists($media, $enabled) || array_key_exists($media . 's', $enabled)) {
                                    if ($enabled[$media]) {
                                        $medias = $media;
                                        if ($media != 'audio')
                                            $medias .='s';
                                        $total[$medias] = $total[$medias] + $number;
                                        if ($media != 'album') {
                                            $total_count = $total_count + $total[$medias];
                                        }
                                    }
                                }
                            }
                        }
                        $total['total'] = $total_count;
                    }
                }
            }

            $bp_media_count = $total;
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

    public function update_count($object) {

        global $bp;
        $user_id = $bp->loggedin_user->id;
        global $wpdb;
        $formatted = array();
        $query =
                "SELECT
		SUM(CASE WHEN post_mime_type LIKE 'image%' THEN 1 ELSE 0 END) as Images,
		SUM(CASE WHEN post_mime_type LIKE 'audio%' THEN 1 ELSE 0 END) as Audio,
		SUM(CASE WHEN post_mime_type LIKE 'video%' THEN 1 ELSE 0 END) as Videos,
		SUM(CASE WHEN post_type LIKE 'bp_media_album' THEN 1 ELSE 0 END) as Albums
	FROM
		$wpdb->posts p inner join $wpdb->postmeta  pm on pm.post_id = p.id INNER JOIN $wpdb->postmeta pmp
		on pmp.post_id = p.id  WHERE
		p.post_author = $user_id AND
		pm.meta_key = 'bp-media-key' AND
		pm.meta_value > 0 AND
		pmp.meta_key = 'bp_media_privacy' AND
		( post_mime_type LIKE 'image%' OR post_mime_type LIKE 'audio%' OR post_mime_type LIKE 'video%' OR post_type LIKE 'bp_media_album')
	GROUP BY pmp.meta_value";
        $result = $wpdb->get_results($query);
        if (!is_array($result))
            return false;
        foreach ($result as $level => $obj) {
            $formatted[$level * 2] = array(
                'image' => $obj->Images,
                'video' => $obj->Videos,
                'audio' => $obj->Audio,
                'album' => $obj->Albums,
            );
        }
        bp_update_user_meta($user_id, 'bp_media_count', $formatted);
        return true;
    }

    /**
     * Displays the footer of the BuddyPress Media Plugin if enabled through the dashboard options page
     *
     * @since BuddyPress Media 2.0
     */
    function footer() {
        ?>
        <div id="bp-media-footer"><p>Using <a title="BuddyPress Media adds photos, video and audio upload/management feature" href="http://rtcamp.com/buddypress-media/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media">BuddyPress Media</a>.</p></div>
        <?php
    }

    function upload_enqueue() {
        if (is_user_logged_in()) {
            if (bp_is_activity_component() || bp_is_group_home()) {
                $params = array(
                    'url' => BP_MEDIA_URL . 'app/main/includes/bp-media-upload-handler.php',
                    'runtimes' => 'gears,html5,flash,silverlight,browserplus',
                    'browse_button' => 'bp-media-activity-upload-browse-button',
                    'container' => 'bp-media-activity-upload-ui',
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
                wp_enqueue_script('bp-media-activity-uploader', BP_MEDIA_URL . 'app/assets/js/bp-media-activity-uploader.js', array('plupload', 'plupload-html5', 'plupload-flash', 'plupload-silverlight', 'plupload-html4', 'plupload-handlers'), BP_MEDIA_VERSION, true);
                wp_localize_script('bp-media-activity-uploader', 'bp_media_uploader_params', $params);
                wp_localize_script('bp-media-activity-uploader', 'activity_ajax_url', admin_url('admin-ajax.php'));
                if (bp_is_active('groups') && bp_get_current_group_id())
                    $default_album = (string) $this->default_group_album();
                else
                    $default_album = (string) $this->default_user_album();
                wp_localize_script('bp-media-activity-uploader', 'default_album', $default_album ? $default_album : '0');
            } elseif (in_array(bp_current_action(), array(BP_MEDIA_IMAGES_SLUG, BP_MEDIA_VIDEOS_SLUG, BP_MEDIA_AUDIO_SLUG, BP_MEDIA_SLUG, BP_MEDIA_ALBUMS_SLUG))) {
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
                wp_enqueue_script('bp-media-uploader', BP_MEDIA_URL . 'app/assets/js/bp-media-uploader.js', array('plupload', 'plupload-html5', 'plupload-flash', 'plupload-silverlight', 'plupload-html4', 'plupload-handlers'), BP_MEDIA_VERSION, true);
                wp_localize_script('bp-media-uploader', 'bp_media_uploader_params', $params);
            }
        }
        wp_enqueue_style('bp-media-default', BP_MEDIA_URL . 'app/assets/css/main.css', '', BP_MEDIA_VERSION);
    }

//This is used only on the uploads page so its added as action in the screens function of upload page.

    /**
     * Called on bp_init by screen functions
     *
     * @uses global $bp, $bp_media_albums_query
     *
     * @since BuddyPress Media 2.2
     */

    /**
     *
     * @global type $bp
     * @global WP_Query $bp_media_albums_query
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

    function filter_entries() {
        global $bp_media;
        $enabled = $bp_media->enabled();
        if (isset($enabled['upload']))
            unset($enabled['upload']);
        if (isset($enabled['album']))
            unset($enabled['album']);
        foreach ($enabled as $type => $active) {
            if ($active == true) {
                $filters[] = $type;
            }
        }

        if (count($filters) == 1)
            $filters = $filters[0];
        return $filters;
    }

    /**
     * Function to return the media for the ajax requests
     */

    /**
     *
     * @global type $bp
     * @global WP_Query $bp_media_query
     * @global type $bp_media_posts_per_page
     */
    function load_more() {
        global $bp, $bp_media_query, $bp_media, $bp_media_albums_query;
        $page = isset($_GET['page']) ? $_GET['page'] : die();
        $current_action = isset($_GET['current_action']) ? $_GET['current_action'] : null;
        $action_variables = isset($_GET['action_variables']) ? $_GET['action_variables'] : null;
        $displayed_user = isset($_GET['displayed_user']) ? $_GET['displayed_user'] : null;
        $loggedin_user = isset($_GET['loggedin_user']) ? $_GET['loggedin_user'] : null;
        $current_group = isset($_GET['current_group']) ? $_GET['current_group'] : null;
        $album_id = isset($_GET['album_id']) ? $_GET['album_id'] : false;
        if ($current_group && isset($action_variables[1])) {
            $type_var = 'list';
        } elseif ((isset($action_variables[0]) && $action_variables[0])) {
            $type_var = $action_variables[0];
        } else {
            $type_var = $current_action;
        }

        if ($current_action == 'albums') {
            if (isset($action_variables[1])) {
                $album_id = $action_variables[1];
            }
        }

        if ((!$displayed_user || intval($displayed_user) == 0) && (!$current_group || intval($current_group) == 0)) {
            die();
        }
        switch ($type_var) {
            case BP_MEDIA_IMAGES_SLUG:
                $type = 'image';
                break;
            case BP_MEDIA_AUDIO_SLUG:
                $type = 'audio';
                break;
            case BP_MEDIA_VIDEOS_SLUG:
                $type = 'video';
                break;
            case BP_MEDIA_ALBUMS_SLUG:
                $type = 'album';
                break;
            default :
                $type = null;
        }

        $query = new BPMediaQuery();
        $args = $query->init($type, $album_id, false, $page);
        if ($type == 'album') {
            $bp_media_albums_query = new WP_Query($args);
            if (isset($bp_media_albums_query->posts) && is_array($bp_media_albums_query->posts) && count($bp_media_albums_query->posts)) {
                foreach ($bp_media_albums_query->posts as $attachment) {
                    try {
                        $media = new BPMediaAlbum($attachment->ID);
                        echo $media->get_album_gallery_content();
                    } catch (exception $e) {
                        die();
                    }
                }
            }
        } else {
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
        }
        die();
    }

    /**
     *
     * @global type $bp_media_count
     * @param type $attachment_id
     * @return boolean
     */
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
                if (BPMediaGroupLoader::user_can_create_album($group_id, get_current_user_id())) {
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

    /**
     *
     * @param type $album
     */
    function album_create_activity($album) {
        /* @var $album BP_Media_Album */
        global $bp;
        $album_info = new BPMediaHostWordpress($album->get_id());
        if ($album_info->get_group_id() > 0 && bp_is_active('groups')) {
            $component = $bp->groups->id;
            $item_id = $album_info->get_group_id();
        } else {
            $component = $bp->activity->id;
            $item_id = 0;
        }

        $args = array(
            'action' => apply_filters('bp_media_album_created', sprintf(__('%1$s created an album %2$s', BP_MEDIA_TXT_DOMAIN), bp_core_get_userlink($album->get_owner()), '<a href="' . $album->get_url() . '">' . $album->get_title() . '</a>')),
            'component' => $component,
            'type' => 'activity_update',
            'primary_link' => $album->get_url(),
            'user_id' => $album->get_owner(),
            'item_id' => $item_id
        );
        $activity_id = BPMediaFunction::record_activity($args);
        update_post_meta($album->get_id(), 'bp_media_child_activity', $activity_id);
    }

    /**
     *
     * @param type $album_id
     */
    function album_activity_update($album_id) {
        BPMediaFunction::update_album_activity($album_id);
    }

    /**
     *
     * @param type $media_id
     */
    function album_activity_sync($media_id) {
        $album_id = wp_get_post_parent_id($media_id);
        BPMediaFunction::update_album_activity($album_id, false, $media_id);
    }

    /**
     *
     * @param BPMediaHostWordpress $media
     * @param type $hidden
     * @return boolean
     */
    static function activity_create_after_add_media($media, $hidden = false, $activity = false, $group = false) {
        global $bp;
        if (function_exists('bp_activity_add')) {
            $update_activity_id = false;
            if (!is_object($media)) {
                try {
                    $media = new BPMediaHostWordpress($media);
                } catch (exception $e) {
                    return false;
                }
            }
            $activity_content = $media->get_media_activity_content();
            $args = array(
                'action' => apply_filters('bp_media_added_media', sprintf(__('%1$s added a %2$s', BP_MEDIA_TXT_DOMAIN), bp_core_get_userlink($media->get_author()), '<a href="' . $media->get_url() . '">' . $media->get_media_activity_type() . '</a>')),
                'content' => $activity_content,
                'primary_link' => $media->get_url(),
                'item_id' => $media->get_id(),
                'type' => 'activity_update',
                'user_id' => $media->get_author()
            );

            $hidden = apply_filters('bp_media_force_hide_activity', $hidden);

            if ($activity || $hidden) {
                $args['secondary_item_id'] = -999;
            } else {
                $update_activity_id = get_post_meta($media->get_id(), 'bp_media_child_activity', true);
                if ($update_activity_id) {
                    $args['id'] = $update_activity_id;
                    $args['secondary_item_id'] = false;
                }
            }

            if ($hidden && !$activity) {
                do_action('bp_media_album_updated', $media->get_album_id());
            }

            if ($group) {
                $group_info = groups_get_group(array('group_id' => $group));
                $args['component'] = $bp->groups->id;
                $args['item_id'] = $group;
                if ('public' != $group_info->status) {
                    $args['hide_sitewide'] = 1;
                }
            }

            $activity_id = BPMediaFunction::record_activity($args);

            if ($group)
                bp_activity_update_meta($activity_id, 'group_id', $group);

            if (!$update_activity_id)
                add_post_meta($media->get_id(), 'bp_media_child_activity', $activity_id);
        }
    }

    public function set_album_cover() {
        $id = $_GET['post_id'];
        $album_id = $_GET['album_id'];
        $album_cover = get_post_thumbnail_id($album_id);
        $text = NULL;
        if ($album_cover && ($album_cover == $id)) {
            delete_post_thumbnail($album_id);
            $text = __('Set as Album Cover', BP_MEDIA_TXT_DOMAIN);
        } else {
            set_post_thumbnail($album_id, $id);
            $text = __('Unset as Album Cover', BP_MEDIA_TXT_DOMAIN);
        }
        echo $text;
        die;
    }

    public function get_thumbnail() {
        $id = $_POST['media_id'];
        $content = '';
        $media = new BPMediaHostWordpress($id);
        switch ($media->get_type()) {
            case 'video' :
                if ($media->get_thumbnail_id()) {
                    $image_array = image_downsize($media->get_thumbnail_id(), 'thumbnail');
                    $content.=apply_filters('bp_media_ajax_thumbnail_filter', '<video poster="' . $image_array[0] . '" src="' . wp_get_attachment_url($media->get_id()) . '" width="' . $default_sizes['single_video']['width'] . '" height="' . ($default_sizes['single_video']['height'] == 0 ? 'auto' : $default_sizes['single_video']['height']) . '" type="video/mp4" id="bp_media_video_' . $media->get_id() . '" controls="controls" preload="none"></video><script>bp_media_create_element("bp_media_video_' . $media->get_id() . '");</script>', $media);
                } else {
                    $content.=apply_filters('bp_media_ajax_thumbnail_filter', '<video src="' . wp_get_attachment_url($media->get_id()) . '" width="' . $default_sizes['single_video']['width'] . '" height="' . ($default_sizes['single_video']['height'] == 0 ? 'auto' : $default_sizes['single_video']['height']) . '" type="video/mp4" id="bp_media_video_' . $media->get_id() . '" controls="controls" preload="none"></video><script>bp_media_create_element("bp_media_video_' . $media->get_id() . '");</script>', $media);
                }
                break;
            case 'audio' :
                $content.=apply_filters('bp_media_ajax_thumbnail_filter', '<audio src="' . wp_get_attachment_url($media->get_id()) . '" width="' . $default_sizes['single_audio']['width'] . '" type="audio/mp3" id="bp_media_audio_' . $media->get_id() . '" controls="controls" preload="none" ></audio><script>bp_media_create_element("bp_media_audio_' . $media->get_id() . '");</script>', $media);
                break;
            case 'image' :
                $image_array = image_downsize($media->get_id(), 'thumbnail');
                $content.=apply_filters('bp_media_ajax_thumbnail_filter', '<img src="' . $image_array[0] . '" id="bp_media_image_' . $media->get_id() . '" />', $media);
                break;
            default :
                return false;
        }

        echo $content;
        die();
    }

    public function default_user_album() {
        $album_id = 0;
        if (is_user_logged_in()) {
            $current_user_id = get_current_user_id();
            $album_id = get_user_meta($current_user_id, 'bp-media-default-album', true);
            if (!$album_id) {
                $query = new WP_Query(array('post_type' => 'bp_media_album', 'author' => $current_user_id, 'order' => 'ASC', 'posts_per_page' => 1));
                wp_reset_postdata();
                if (isset($query->posts) && isset($query->posts[0])) {
                    $album_id = $query->posts[0]->ID;
                }
                if ($album_id) {
                    update_user_meta($current_user_id, 'bp-media-default-album', $album_id);
                }
            } else {
                global $wpdb;
                $exists = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE ID = $album_id");
                if (!$exists) {
                    $bpm_host_wp = new BPMediaHostWordpress();
                    $album_id = $bpm_host_wp->check_and_create_album(0, 0, $current_user_id);
                    update_user_meta($current_user_id, 'bp-media-default-album', $album_id);
                }
            }
            return $album_id;
        }
    }

    public function default_group_album() {
        $album_id = 0;
        if (bp_is_active('groups')) {
            if ($group_id = bp_get_current_group_id()) {
                $album_id = groups_get_groupmeta($group_id, 'bp_media_default_album');
                if (!$album_id) {
                    $args = array(
                        'post_type' => 'bp_media_album',
                        'posts_per_page' => 1,
                        'meta_key' => 'bp-media-key',
                        'meta_value' => -$group_id,
                        'meta_compare' => '=',
                        'order' => 'ASC'
                    );
                    $query = new WP_Query($args);
                    wp_reset_postdata();
                    if (isset($query->posts) && isset($query->posts[0])) {
                        $album_id = $query->posts[0]->ID;
                    }
                    if ($album_id) {
                        groups_update_groupmeta($group_id, 'bp_media_default_album', $album_id);
                    }
                } else {
                    global $wpdb;
                    $exists = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE ID = $album_id");
                    if (!$exists) {
                        $bp_album = new BPMediaHostWordpress();
                        $album_id = $bp_album->check_and_create_album(0, bp_get_current_group_id());
                        groups_update_groupmeta($group_id, 'bp_media_default_album', $album_id);
                    }
                }
            }
        }
        return $album_id;
    }

    function group_create_default_album() {
        $bp_album = new BPMediaHostWordpress();
        $bp_album->check_and_create_album(0, bp_get_new_group_id());
    }

}
?>
