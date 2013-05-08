<?php

/**
 * Don't load this file directly!
 */
if (!defined('ABSPATH'))
    exit;

/**
 * BuddyPress Media
 *
 * The main BuddyPress Media Class. This is where everything starts.
 *
 * @package BuddyPressMedia
 * @subpackage Main
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class BuddyPressMedia {

    /**
     *
     * @var string The text domain for loading translations
     */
    public $text_domain = 'buddypress-media';

    /**
     *
     * @var array BuddyPress Media settings
     */
    public $options = array();

    /**
     *
     * @var string Email address the admin support form should send to
     */
    public $support_email = 'support@rtcamp.com';

    /**
     *
     * @var string Support forum url
     */
    public $support_url = 'http://rtcamp.com/support/forum/buddypress-media/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media';

    /**
     *
     * @var object/array The query that fetches media (photos, video and audio)
     */
    public $query;

    /**
     *
     * @var object/array The query that fetches albums
     */
    public $albums_query;

    /**
     *
     * @var int Count
     */
    public $count = null;

    /**
     *
     * @var int Number of media items to show in one view.
     */
    public $posts_per_page = 10;

    /**
     *
     * @var array The types of activity BuddyPress Media creates
     */
    public $activity_types = array(
        'media_upload',
        'album_updated',
        'album_created'
    );

    /**
     *
     * @var array A cache for activities that are hidden by BuddyPress Media
     */
    public $hidden_activity_cache = array();

    /**
     *
     * @var type
     */
    public $loader;

    /**
     *
     * @var type
     */
    public $group_loader;

    /**
     * Constructs the class
     * Defines constants and excerpt lengths, initiates admin notices,
     * loads and initiates the plugin, loads translations.
     * Initialises media counter
     *
     * @global int $bp_media_counter Media counter
     */
    public function __construct() {
        /**
         * Define constants
         */
        $this->constants();
        /**
         * Define excerpt lengths
         */
        $this->excerpt_lengths();
        /**
         * Add admin notice for BuddyPress dependance
         */
        add_action('admin_notices', array($this, 'bp_exists'));
        /**
         * Activate the plugin!
         */
        register_activation_hook(__FILE__, array($this, 'activate'));

        /**
         * Hook it to BuddyPress
         */
        add_action('bp_include', array($this, 'init'));

		/**
         * Hook admin to wp init
         */
        add_action('bp_init', array($this, 'admin_init'));
        /**
         * Add the widget
         */
        add_action('widgets_init', array($this, 'widgets_init'), 1);
        /**
         * Load translations
         */
        add_action('plugins_loaded', array($this, 'load_translation'));
        /**
         * Initialise media counter
         */
        global $bp_media_counter;
        $bp_media_counter = 0;
    }

    /**
     * Checks if BuddyPress is installed!
     */
    public function bp_exists() {
        if (!class_exists('BuddyPress')) {
            $plugins = get_plugins();
            if (array_key_exists('buddypress/bp-loader.php', $plugins)) {
                $install_link = wp_nonce_url(admin_url('plugins.php?action=activate&amp;plugin=buddypress%2Fbp-loader.php'), 'activate-plugin_buddypress/bp-loader.php');
            } else {
                include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
                $info = plugins_api('plugin_information', array('slug' => 'buddypress'));
                $install_link = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=buddypress'), 'install-plugin_buddypress');
            }
            echo '<div class="error">
       <p><strong>' . __('BuddyPress is not installed.', $this->text_domain) . '</strong></p>
       <p>'
            . __('To use BuddyPress Media, BuddyPress must be installed first.', $this->text_domain) .
            ' ' . sprintf(__('<a href="%s">Install BuddyPress now</a>', $this->text_domain), $install_link)
            . '</p>
    </div>';
        }
    }

    /**
     * Populates $options with saved settings
     */
    public function get_option() {
        $options = bp_get_option('bp_media_options', false);
        if (!$options) {
            $options = array(
                'enable_on_group' => 1,
                'enable_lightbox' => 1,
                'sizes' => array(
                    'image' => array(
                        'thumbnail' => array('width' => 150, 'height' => 150, 'crop' => 1),
                        'medium' => array('width' => 320, 'height' => 240, 'crop' => 1),
                        'large' => array('width' => 800, 'height' => 0, 'crop' => 1)
                    ),
                    'video' => array(
                        'medium' => array('width' => 320, 'height' => 240),
                        'large' => array('width' => 640, 'height' => 480)
                    ),
                    'audio' => array(
                        'medium' => array('width' => 320),
                        'large' => array('width' => 640)
                    ),
                    'media' => array(
                        'featured' => array('width' => 100,'height'=>100,'crop'=>1)
                    )
                ),
				'featured_image' => 0,
				'featured_video' => 0,
				'featured_audio' => 0,
                'videos_enabled' => 1,
                'audio_enabled' => 1,
                'images_enabled' => 1,
                'download_enabled' => 1,
                'show_admin_menu' => 1
            );
            bp_update_option('bp_media_options', $options);
        } elseif (!isset($options['sizes'])) {
            $options['sizes'] = array(
                'image' => array(
                    'thumbnail' => array('width' => 150, 'height' => 150, 'crop' => 1),
                    'medium' => array('width' => 320, 'height' => 240, 'crop' => 1),
                    'large' => array('width' => 800, 'height' => 0, 'crop' => 1)
                ),
                'video' => array(
                    'medium' => array('width' => 320, 'height' => 240),
                    'large' => array('width' => 640, 'height' => 480)
                ),
                'audio' => array(
                    'medium' => array('width' => 320),
                    'large' => array('width' => 640)
                    ),
                'media' => array(
                        'featured' => array('width' => 100,'height'=>100,'crop'=>1)
                    ));
            bp_update_option('bp_media_options', $options);
        } elseif (!isset($options['sizes']['media'])) {
            $options['sizes']['media'] = array(
                        'featured' => array('width' => 100,'height'=>100,'crop'=>1)
                    );
            bp_update_option('bp_media_options', $options);
        }

        $this->options = $options;
    }

    /**
     * Defines all the constants if undefined. Can be overridden by
     * defining them elsewhere, say wp-config.php
     */
    public function constants() {

        /* If the plugin is installed. */
        if (!defined('BP_MEDIA_IS_INSTALLED'))
            define('BP_MEDIA_IS_INSTALLED', 1);

        /* Current Version. */
        if (!defined('BP_MEDIA_VERSION'))
            define('BP_MEDIA_VERSION', BuddyPressMedia::plugin_get_version());

        /* Required Version  */
        if (!defined('BP_MEDIA_REQUIRED_BP'))
            define('BP_MEDIA_REQUIRED_BP', '1.6.2');

        /* Database Version */
        if (!defined('BP_MEDIA_DB_VERSION'))
            define('BP_MEDIA_DB_VERSION', '2.1');

        /* Slug Constants for building urls */

        /* Media slug */
        if (!defined('BP_MEDIA_SLUG'))
            define('BP_MEDIA_SLUG', 'media');

        /* Upload slug */
        if (!defined('BP_MEDIA_UPLOAD_SLUG'))
            define('BP_MEDIA_UPLOAD_SLUG', 'upload');

        /* Delete slug */
        if (!defined('BP_MEDIA_DELETE_SLUG'))
            define('BP_MEDIA_DELETE_SLUG', 'delete');

        /* Photos slug */
        if (!defined('BP_MEDIA_IMAGES_SLUG'))
            define('BP_MEDIA_IMAGES_SLUG', 'photos');

        if (!defined('BP_MEDIA_IMAGES_VIEW_SLUG'))
            define('BP_MEDIA_IMAGES_VIEW_SLUG', 'view');

        if (!defined('BP_MEDIA_IMAGES_EDIT_SLUG'))
            define('BP_MEDIA_IMAGES_EDIT_SLUG', 'edit');

        /* Videos slug */
        if (!defined('BP_MEDIA_VIDEOS_SLUG'))
            define('BP_MEDIA_VIDEOS_SLUG', 'videos');

        if (!defined('BP_MEDIA_VIDEOS_VIEW_SLUG'))
            define('BP_MEDIA_VIDEOS_VIEW_SLUG', 'watch');

        if (!defined('BP_MEDIA_VIDEOS_EDIT_SLUG'))
            define('BP_MEDIA_VIDEOS_EDIT_SLUG', 'edit');

        /* Audio slug */
        if (!defined('BP_MEDIA_AUDIO_SLUG'))
            define('BP_MEDIA_AUDIO_SLUG', 'music');

        if (!defined('BP_MEDIA_AUDIO_VIEW_SLUG'))
            define('BP_MEDIA_AUDIO_VIEW_SLUG', 'listen');

        if (!defined('BP_MEDIA_AUDIO_EDIT_SLUG'))
            define('BP_MEDIA_AUDIO_EDIT_SLUG', 'edit');

        /* Albums slug */
        if (!defined('BP_MEDIA_ALBUMS_SLUG'))
            define('BP_MEDIA_ALBUMS_SLUG', 'albums');

        if (!defined('BP_MEDIA_ALBUMS_VIEW_SLUG'))
            define('BP_MEDIA_ALBUMS_VIEW_SLUG', 'list');

        if (!defined('BP_MEDIA_ALBUMS_EDIT_SLUG'))
            define('BP_MEDIA_ALBUMS_EDIT_SLUG', 'edit');

        /* Settings slug */
        if (!defined('BP_MEDIA_USER_SETTINGS_SLUG'))
            define('BP_MEDIA_USER_SETTINGS_SLUG', 'privacy');

        /* UI Labels loaded via text domain, can be translated */
        if (!defined('BP_MEDIA_LABEL'))
            define('BP_MEDIA_LABEL', __('Media', $this->text_domain));

        if (!defined('BP_MEDIA_LABEL_SINGULAR'))
            define('BP_MEDIA_LABEL_SINGULAR', __('Media', $this->text_domain));
        if (!defined('BP_MEDIA_USER_SETTINGS_LABEL'))
            define('BP_MEDIA_USER_SETTINGS_LABEL', __('Privacy', $this->text_domain));

        if (!defined('BP_MEDIA_IMAGES_LABEL'))
            define('BP_MEDIA_IMAGES_LABEL', __('Photos', $this->text_domain));

        if (!defined('BP_MEDIA_IMAGES_LABEL_SINGULAR'))
            define('BP_MEDIA_IMAGES_LABEL_SINGULAR', __('Photo', $this->text_domain));

        if (!defined('BP_MEDIA_VIDEOS_LABEL'))
            define('BP_MEDIA_VIDEOS_LABEL', __('Videos', $this->text_domain));

        if (!defined('BP_MEDIA_VIDEOS_LABEL_SINGULAR'))
            define('BP_MEDIA_VIDEOS_LABEL_SINGULAR', __('Video', $this->text_domain));

        if (!defined('BP_MEDIA_AUDIO_LABEL'))
            define('BP_MEDIA_AUDIO_LABEL', __('Music', $this->text_domain));

        if (!defined('BP_MEDIA_AUDIO_LABEL_SINGULAR'))
            define('BP_MEDIA_AUDIO_LABEL_SINGULAR', __('Music', $this->text_domain));

        if (!defined('BP_MEDIA_ALBUMS_LABEL'))
            define('BP_MEDIA_ALBUMS_LABEL', __('Albums', $this->text_domain));

        if (!defined('BP_MEDIA_ALBUMS_LABEL_SINGULAR'))
            define('BP_MEDIA_ALBUMS_LABEL_SINGULAR', __('Album', $this->text_domain));

        if (!defined('BP_MEDIAUPLOAD_LABEL'))
            define('BP_MEDIA_UPLOAD_LABEL', __('Upload', $this->text_domain));

        /* Support Email constant */
        if (!defined('BP_MEDIA_SUPPORT_EMAIL'))
            define('BP_MEDIA_SUPPORT_EMAIL', $this->support_email);
    }

    /**
     * Hooks the plugin into BuddyPress via 'bp_include' action.
     * Initialises the plugin's functionalities, options,
     * loads media for Profiles and Groups.
     * Creates Admin panels
     * Loads accessory functions
     *
     * @global BPMediaAdmin $bp_media_admin
     */
    function init() {

        /**
         * Load options/settings
         */
        $this->get_option();

        if (defined('BP_VERSION') &&
                version_compare(BP_VERSION, BP_MEDIA_REQUIRED_BP, '>=')) {
            /**
             * Add a settings link to the Plugin list screen
             */
            add_filter('plugin_action_links', array($this, 'settings_link'), 10, 2);
            /**
             * Load BuddyPress Media for profiles
             */
            $this->loader = new BPMediaLoader();
            /**
             * Load BuddyPress Media for groups
             */
            if (array_key_exists('enable_on_group', $this->options)) {
                if ($this->options['enable_on_group']) {
                    $this->group_loader = new BPMediaGroupLoader();
                }
            }


            /**
             * Load accessory functions
             */
//			new BPMediaActivity();
            $class_construct = array(
                'activity' => false,
                'filters' => false,
                'actions' => false,
                'function' => false,
                'privacy' => false,
                'download' => false,
                'albumimporter' => false,
                'image' => false,
				'featured' => false
            );
            $class_construct = apply_filters('bpmedia_class_construct', $class_construct);

            foreach ($class_construct as $classname => $global_scope) {
                $class = 'BPMedia' . ucfirst($classname);
                if (class_exists($class)) {
                    if ($global_scope == true) {
                        global ${'bp_media_' . $classname};
                        ${'bp_media_' . $classname} = new $class();
                    } else {
                        new $class();
                    }
                }
            }
        }

        /**
         * Add admin notices
         */
        add_action('admin_notices', array($this, 'admin_notice'));
    }

	function admin_init(){
		       /**
         * Initialise Admin Panels
         */
        global $bp_media_admin;
        $bp_media_admin = new BPMediaAdmin();
	}

    /**
     * Loads translations
     */
    static function load_translation() {
        load_plugin_textdomain('buddypress-media', false, basename(BP_MEDIA_PATH) . '/languages/');
    }

    /**
     * Add a settings link to the BuddyPress Media entry
     * in the list of active plugins screen
     *
     * @param array $links
     * @param string $file
     * @return array
     */
    function settings_link($links, $file) {
        /* create link */
        $plugin_name = plugin_basename(BP_MEDIA_PATH . 'index.php');
        $admin_link = $this->get_admin_url(
                add_query_arg(
                        array(
                    'page' => 'bp-media-settings'), 'admin.php'
                )
        );
        if ($file == $plugin_name) {
            array_unshift(
                    $links, sprintf(
                            '<a href="%s">%s</a>', $admin_link, __('Settings', $this->text_domain)
                    )
            );
        }
        return $links;
    }

    /**
     * Default media sizes
     *
     * @return array
     */
    function media_sizes() {
        $options = $this->options;
        $def_sizes = array(

            //legacy array
            'single_video' => array(
                'width' => 640,
                'height' => 480
            ),
            'single_audio' => array(
                'width' => 640,
            ),

            'image' => array(
                'thumbnail' => array(
                    'width' => $options['sizes']['image']['thumbnail']['width'],
                    'height' => $options['sizes']['image']['thumbnail']['height'],
                    'crop' => $options['sizes']['image']['thumbnail']['crop']
                ),
                'medium' => array(
                    'width' => $options['sizes']['image']['medium']['width'],
                    'height' => $options['sizes']['image']['medium']['height'],
                    'crop' => $options['sizes']['image']['medium']['crop'],
                ),
                'large' => array(
                    'width' => $options['sizes']['image']['large']['width'],
                    'height' => $options['sizes']['image']['large']['height'],
                    'crop' => $options['sizes']['image']['large']['crop'],
                )
            ),
            'video' => array(
                'medium' => array(
                    'width' => $options['sizes']['video']['medium']['width'],
                    'height' => $options['sizes']['video']['medium']['height'],
                ),
                'large' => array(
                    'width' => $options['sizes']['video']['large']['width'],
                    'height' => $options['sizes']['video']['large']['height'],
                )
            ),
            'audio' => array(
                'medium' => array(
                    'width' => $options['sizes']['audio']['medium']['width'],
                ),
                'large' => array(
                    'width' => $options['sizes']['audio']['large']['width'],
                )
            )
        );

        return $def_sizes;
    }

    /**
     * Defines default length of strings and excerpts displayed in activities
     * and media tabs
     *
     * @global array $bp_media_default_excerpts
     */
    function excerpt_lengths() {
        global $bp_media_default_excerpts;
        $def_excerpt = array(
            'single_entry_title' => 100,
            'single_entry_description' => 500,
            'activity_entry_title' => 50,
            'activity_entry_description' => 500
        );

        $bp_media_default_excerpts = apply_filters(
                'bpmedia_excerpt_lengths', $def_excerpt
        );
    }

    /**
     * Admin notices for dependencies and compatibility
     *
     * @global object/array $current_user
     */
    public function admin_notice() {
        global $current_user;
        $user_id = $current_user->ID;
        if (isset($_GET['bp_media_nag_ignore'])
                && '0' == $_GET['bp_media_nag_ignore']) {
            add_user_meta($user_id, 'bp_media_ignore_notice', 'true', true);
        }
        /* Check that the user hasn't already clicked to ignore the message */
        if (!get_user_meta($user_id, 'bp_media_ignore_notice')) {
            if (defined('BP_VERSION')) {
                if (version_compare(BP_VERSION, BP_MEDIA_REQUIRED_BP, '<')) {
                    echo '<div class="error"><p>';
                    printf(
                            __(
                                    'The BuddyPress version installed is an
										older version and is not supported,
										please update BuddyPress to use
										BuddyPress Media Plugin.
										<a class="alignright" href="%1$s">X</a>', $this->text_domain
                            ), '?bp_media_nag_ignore=0'
                    );
                    echo "</p></div>";
                }
            } else {
                echo '<div class="error"><p>';
                printf(
                        __(
                                'You have not installed BuddyPress.
									Please install latest version of BuddyPress
									to use BuddyPress Media plugin.
									<a class="alignright" href="%1$s">X</a>', $this->text_domain
                        ), '?bp_media_nag_ignore=0'
                );
                echo "</p></div>";
            }
        }
    }

    /**
     * Plugin activation, checks for old database and updates it.
     *
     */
    public function activate() {
        $bpmquery = new WP_Query(
                        array(
                            'post_type' => 'bp_media',
                            'posts_per_page' => 1
                        )
        );
        if ($bpmquery->found_posts > 0) {
            update_site_option('bp_media_db_version', '1.0');
        } else {
            switch (get_site_option('bp_media_db_version', false, false)) {
                case '2.0':
                    break;
                default:
                    update_site_option(
                            'bp_media_db_version', BP_MEDIA_DB_VERSION
                    );
            }
        }

        BPMediaPrivacy::install();
    }

    /**
     * Provides the right admin url to work with
     *
     * @param string $path
     * @param string $scheme
     * @return string The proper admin url for single/multisite installs
     */
    function get_admin_url($path = '', $scheme = 'admin') {

        // Links belong in network admin
        if (is_multisite())
            $url = network_admin_url($path, $scheme);

        // Links belong in site admin
        else
            $url = admin_url($path, $scheme);

        return $url;
    }

    /**
     * Registers and activates the BuddyPress Media Widgets
     */
    function widgets_init() {
        register_widget('BPMediaWidget');
    }

    /**
     *
     */
    function enabled() {
        $options = $this->options;
        $enabled = array(
            'image' => false,
            'video' => false,
            'audio' => false,
            'album' => true,
            'upload' => true
        );
        if (array_key_exists('images_enabled', $options)) {
            if ($options['images_enabled'] == 1) {
                $enabled['image'] = true;
            }
        }
        if (array_key_exists('videos_enabled', $options)) {
            if ($options['videos_enabled'] == 1) {
                $enabled['video'] = true;
            }
        }
        if (array_key_exists('audio_enabled', $options)) {
            if ($options['audio_enabled'] == 1) {
                $enabled['audio'] = true;
            }
        }

        return $enabled;
    }

    function default_count() {
        $count = $this->posts_per_page;
        if (array_key_exists('default_count', $this->options)) {
            $count = $this->options['default_count'];
        }
        $count = (!is_int($count)) ? 0 : $count;
        return (!$count) ? 10 : $count;
    }

    function default_tab() {
        $enabled = $this->enabled();
        unset($enabled['upload']);
        unset($enabled['album']);
        foreach ($enabled as $tab => $value) {
            if ($value == true) {
                return $tab;
            }
        }
    }

    function defaults_tab() {
        $defaults_tab = $this->default_tab();
        if ($defaults_tab != 'audio') {
            $defaults_tab .= 's';
        }
        return $defaults_tab;
    }

    /*
      static function get_wall_album( $group_id = false ) {
      global $wpdb;
      $group_id = ( ! $group_id) ? '1' : $group_id;
      $album_name = __( 'Wall Posts', BP_MEDclaIA_TXT_DOMAIN );
      $query = "SELECT ID FROM {$wpdb->prefix}posts ps LEFT JOIN
      {$wpdb->prefix}postmeta pm ON ps.ID= pm.post_id WHERE ps.post_title
      LIKE '{$album_name}' AND ps.post_type='bp_media_album' AND
      pm.meta_key='bp-media-key' AND pm.meta_value ='{$group_id}'";
      $wall_albums = $wpdb->get_results( $query, ARRAY_A );
      if ( count( $wall_albums ) > 1 ) {
      return BuddyPressMedia::merge_duplicate_wall_albums( $wall_albums );
      } elseif($wall_albums) {
      return $wall_albums[ 0 ][ 'ID' ];
      }
      }
     *
     */

    static function merge_duplicate_wall_albums($wall_albums) {
        global $wpdb;
        $album_id = $wall_albums[0]['ID'];
        unset($wall_albums[0]);
        foreach ($wall_albums as $album) {
            $query = "SELECT ID FROM {$wpdb->prefix}posts WHERE
				post_parent={$album['ID']} AND post_type='attachment'";
            $media = $wpdb->get_results($query, ARRAY_A);
            foreach ($media as $file) {
                $wpdb->update(
                        $wpdb->prefix . 'posts', array(
                    '		post_parent' => $album_id
                        ), array('ID' => $file['ID']), array('%d'), array('%d')
                );
            }

            wp_delete_post($album['ID'], true);
        }
    }

    static function plugin_get_version($path = NULL) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $path = ($path) ? $path : BP_MEDIA_PATH . 'index.php';
        $plugin_data = get_plugin_data($path);
        $plugin_version = $plugin_data['Version'];
        return $plugin_version;
    }

    static function get_current_user_default_album() {
        if (is_user_logged_in()) {
            $current_user_id = get_current_user_id();
            $album_id = get_user_meta($current_user_id, 'bp-media-default-album', true);
            return $album_id;
        }
        return false;
    }

}

/**
 * This wraps up the main BuddyPress Media class. Three important notes:
 *
 * 1. All the constants can be overridden.
 *    So, you could use, 'portfolio' instead of 'media'
 * 2. The default thumbnail and display sizes can be filtered
 *    using 'bpmedia_media_sizes' hook
 * 3. The excerpts and string sizes can be filtered
 *    using 'bpmedia_excerpt_lengths' hook
 *
 */
?>
