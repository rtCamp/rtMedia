<?php

/**
 * Description of BPMAdmin
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class BPMAdmin {

    public function __construct() {
        if (is_admin()) {
            if (version_compare(BP_MEDIA_DB_VERSION, get_site_option('bp_media_db_version', '1.0'), '>')) {
                add_action('admin_notices', array($this, 'upgrade_db'));
            }
            add_action('wp_loaded', array($this, 'upgrade'));
            add_action('admin_enqueue_scripts', array($this, 'ui'));
            add_action(bp_core_admin_hook(), array($this, 'menu'));
            add_action('admin_init', array($this, 'metaboxes'));
            add_action('admin_init', array(&$this, 'settings'));
        }
    }

    /**
     * Displays admin notice to upgrade BuddyPress Media Database
     */
    private function upgrade_db() {
        ?>
        <div class="error"><p>
                Please click upgrade to upgrade the database of BuddyPress Media <a class="button" id="refresh_media_count" href ="<?php echo bp_media_get_admin_url(add_query_arg(array('page' => 'bp-media-settings', 'bp_media_upgrade_db' => 1, 'wp_nonce' => wp_create_nonce('bp_media_upgrade_db')), 'admin.php')) ?>" class="button" title="<?php printf(__('It will migrate your BuddyPress Media\'s earlier database to new database.')); ?>">Upgrade</a>
            </p></div>
        <?php
    }

    /**
     * Upgrade Script
     */
    private function upgrade() {
        if (isset($_GET['bp_media_upgrade_db']) && empty($_REQUEST['settings-updated'])) {
            check_admin_referer('bp_media_upgrade_db', 'wp_nonce');
            require_once('bp-media-upgrade-script.php');
            $current_version = get_site_option('bp_media_db_version', '1.0');
            if ($current_version == '2.0')
                $this->upgrade_2_0_to_2_1();
            else
                $this->upgrade_1_0_to_2_1();
            remove_action('admin_notices', 'upgrade_db');
        }
    }

    /**
     * Upgrade from BuddyPress Media 1.0 to 2.1
     * @global wpdb $wpdb
     */
    private function upgrade_1_0_to_2_1() {
        global $wpdb;
        remove_filter('bp_activity_get_user_join_filter', 'bp_media_activity_query_filter', 10);
        /* @var $wpdb wpdb */
        $wall_posts_album_ids = array();
        do {
            $media_files = new WP_Query(array(
                        'post_type' => 'bp_media',
                        'posts_per_page' => 10
                    ));
            $media_files = isset($media_files->posts) ? $media_files->posts : null;
            if (is_array($media_files) && count($media_files)) {
                foreach ($media_files as $media_file) {
                    $attachment_id = get_post_meta($media_file->ID, 'bp_media_child_attachment', true);
                    $child_activity = get_post_meta($media_file->ID, 'bp_media_child_activity', true);
                    update_post_meta($attachment_id, 'bp_media_child_activity', $child_activity);
                    $attachment = get_post($attachment_id, ARRAY_A);
                    if (isset($wall_posts_album_ids[$media_file->post_author])) {
                        $wall_posts_id = $wall_posts_album_ids[$media_file->post_author];
                    } else {
                        $wall_posts_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = 'Wall Posts' AND post_author = '" . $media_file->post_author . "' AND post_type='bp_media_album'");
                        if ($wall_posts_id == null) {
                            $album = new BP_Media_Album();
                            $album->add_album('Wall Posts', $media_file->post_author);
                            $wall_posts_id = $album->get_id();
                        }
                        if (!$wall_posts_id) {
                            continue; //This condition should never be encountered
                        }
                        $wall_posts_album_ids[$media_file->post_author] = $wall_posts_id;
                    }
                    $attachment['post_parent'] = $wall_posts_id;
                    wp_update_post($attachment);
                    update_post_meta($attachment_id, 'bp-media-key', $media_file->post_author);
                    $activity = bp_activity_get(array('in' => intval($child_activity)));
                    if (isset($activity['activities'][0]->id))
                        $activity = $activity['activities'][0];
                    try {
                        $bp_media = new BP_Media_Host_Wordpress($attachment_id);
                    } catch (exception $e) {
                        continue;
                    }
                    $args = array(
                        'content' => $bp_media->get_media_activity_content(),
                        'id' => $child_activity,
                        'type' => 'media_upload',
                        'action' => apply_filters('bp_media_added_media', sprintf(__('%1$s added a %2$s', 'bp-media'), bp_core_get_userlink($media_file->post_author), '<a href="' . $bp_media->get_url() . '">' . $bp_media->get_media_activity_type() . '</a>')),
                        'primary_link' => $bp_media->get_url(),
                        'item_id' => $attachment_id,
                        'recorded_time' => $activity->date_recorded,
                        'user_id' => $bp_media->get_author()
                    );
                    $act_id = bp_media_record_activity($args);
                    bp_activity_delete_meta($child_activity, 'bp_media_parent_post');
                    wp_delete_post($media_file->ID);
                }
            } else {
                break;
            }
        } while (1);
        update_site_option('bp_media_db_version', BP_MEDIA_DB_VERSION);
        add_action('admin_notices', 'bp_media_database_updated_notice');
        wp_cache_flush();
    }

    /**
     * Upgrade from BuddyPress Media 2.0 to 2.1
     */
    private function upgrade_2_0_to_2_1() {
        $page = 0;
        while ($media_entries = bp_media_return_query_posts(array(
    'post_type' => 'attachment',
    'post_status' => 'any',
    'meta_key' => 'bp-media-key',
    'meta_value' => 0,
    'meta_compare' => '>',
    'paged' => ++$page,
    'postsperpage' => 10
        ))) {
            foreach ($media_entries as $media) {
                try {
                    $bp_media = new BP_Media_Host_Wordpress($media->ID);
                } catch (exception $e) {
                    continue;
                }
                $child_activity = get_post_meta($media->ID, 'bp_media_child_activity', true);
                if ($child_activity) {
                    $activity = bp_activity_get(array('in' => intval($child_activity)));
                    if (isset($activity['activities'][0]->id))
                        $activity = $activity['activities'][0];
                    else
                        continue;
                    $args = array(
                        'content' => $bp_media->get_media_activity_content(),
                        'id' => $child_activity,
                        'type' => 'media_upload',
                        'action' => apply_filters('bp_media_added_media', sprintf(__('%1$s added a %2$s', 'bp-media'), bp_core_get_userlink($bp_media->get_author()), '<a href="' . $bp_media->get_url() . '">' . $bp_media->get_media_activity_type() . '</a>')),
                        'primary_link' => $bp_media->get_url(),
                        'item_id' => $activity->item_id,
                        'recorded_time' => $activity->date_recorded,
                        'user_id' => $bp_media->get_author()
                    );
                    bp_media_record_activity($args);
                }
            }
        }
        update_site_option('bp_media_db_version', BP_MEDIA_DB_VERSION);
        add_action('admin_notices', 'bp_media_database_updated_notice');
        wp_cache_flush();
    }

    /**
     * Generates the Admin UI
     * 
     * @param string $hook
     */
    private function ui($hook) {
        $admin_js = trailingslashit(site_url()) . '?bp_media_get_feeds=1';
        wp_enqueue_script('bp-media-js', plugins_url('includes/js/bp-media.js', dirname(__FILE__)));
        wp_localize_script('bp-media-js', 'bp_media_news_url', $admin_js);
        wp_enqueue_style('bp-media-admin-style', plugins_url('includes/css/bp-media-style.css', dirname(__FILE__)));
    }

    /**
     * Admin Menu
     * 
     * @global string $bp_text_domain
     */
    private function menu() {
        global $bp_text_domain;
        add_menu_page(__('Buddypress Media Component', $bp_text_domain), __('BP Media', $bp_text_domain), 'manage_options', 'bp-media-settings', array($this, 'render_settings'));
        add_submenu_page('bp-media-settings', __('Buddypress Media Settings', $bp_text_domain), __('Settings', $bp_text_domain), 'manage_options', 'bp-media-settings', array($this, 'redener_settings'));
        add_submenu_page('bp-media-settings', __('Buddypress Media Addons', $bp_text_domain), __('Addons', $bp_text_domain), 'manage_options', 'bp-media-addons', array($this, 'redener_settings'));
        add_submenu_page('bp-media-settings', __('Buddypress Media Support', $bp_text_domain), __('Support ', $bp_text_domain), 'manage_options', 'bp-media-support', array($this, 'redener_settings'));
    }

    private function settings() {
        
    }

    /**
     * Render BPMedia Settings
     */
    private function render_settings() {
        $tab = isset($_GET['page']) ? $_GET['page'] : "bp-media-settings";
        ?>

        <div class="wrap bp-media-admin">
            <?php //screen_icon( 'buddypress' );    ?>
            <div id="icon-buddypress" class="icon32"><br></div>
            <h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs(__('Media', 'bp-media')); ?></h2>
            <div class="metabox-holder columns-2">
                <div class="bp-media-settings-tabs"><?php
        // Check to see which tab we are on
        if (current_user_can('manage_options')) {
            $tabs_html = '';
            $idle_class = 'media-nav-tab';
            $active_class = 'media-nav-tab media-nav-tab-active';
            $tabs = array();

            // Check to see which tab we are on
            $tab = isset($_GET['page']) ? $_GET['page'] : "bp-media-settings";
            /* BP Media */
            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-settings'), 'admin.php')),
                'title' => __('Buddypress Media Settings', 'bp-media'),
                'name' => __('Settings', 'bp-media'),
                'class' => ($tab == 'bp-media-settings') ? $active_class : $idle_class . ' first_tab'
            );

            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-addons'), 'admin.php')),
                'title' => __('Buddypress Media Addons', 'bp-media'),
                'name' => __('Addons', 'bp-media'),
                'class' => ($tab == 'bp-media-addons') ? $active_class : $idle_class
            );

            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-support'), 'admin.php')),
                'title' => __('Buddypress Media Support', 'bp-media'),
                'name' => __('Support', 'bp-media'),
                'class' => ($tab == 'bp-media-support') ? $active_class : $idle_class . ' last_tab'
            );

            $pipe = '|';
            $i = '1';
            foreach ($tabs as $tab) {
                if ($i != 1)
                    $tabs_html.=$pipe;
                $tabs_html.= '<a title=""' . $tab['title'] . '" " href="' . $tab['href'] . '" class="' . $tab['class'] . '">' . $tab['name'] . '</a>';
                $i++;
            }
            echo $tabs_html;
        }
            ?>
                </div>

                <div id="bp-media-settings-boxes">

                    <form id="bp_media_settings_form" name="bp_media_settings_form" action="" method="post" enctype="multipart/form-data"><?php
            settings_fields('bp_media_options_settings');
            do_settings_fields('bp_media_options_settings', '');
            echo '<div class="bp-media-metabox-holder">';

            if (isset($_REQUEST['request_type'])) {
                bp_media_bug_report_form($_REQUEST['request_type']);
            } else {
                do_meta_boxes('bp-media-settings', 'normal', '');
            }

            echo '</div>';
            ?>

                        <script type="text/javascript">
                            //<![CDATA[
                            jQuery(document).ready( function($) {
                                // close postboxes that should be closed
                                $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                                // postboxes setup
                                postboxes.add_postbox_toggles('bp-media-settings');
                            });
                            //]]>
                        </script>

                    </form>
                </div><!-- .bp-media-settings-boxes -->
                <div class="metabox-fixed metabox-holder alignright bp-media-metabox-holder">
                    <?php bp_media_default_admin_sidebar(); ?>
                </div>
            </div><!-- .metabox-holder -->
        </div><!-- .bp-media-admin --><?php
        }

        /**
         * Load the metaboxes
         */
        private function metaboxes() {
            /* Javascripts loaded to allow drag/drop, expand/collapse and hide/show of boxes. */
            wp_enqueue_script('common');
            wp_enqueue_script('wp-lists');
            wp_enqueue_script('postbox');

// Check to see which tab we are on
            $tab = isset($_GET['page']) ? $_GET['page'] : "bp-media-settings";

            switch ($tab) {
                case 'bp-media-addons' :
// All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                    add_meta_box('bp_media_addons_list_metabox', __('BuddyPress Media Addons for Audio/Video Conversion', 'bp-media'), 'bp_media_addons_list', 'bp-media-settings', 'normal', 'core');
                    break;
                case 'bp-media-support' :
// All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                    add_meta_box('bp_media_support_metabox', __('BuddyPress Media Support', 'rtPanel'), 'bp_media_support', 'bp-media-settings', 'normal', 'core');
                    add_meta_box('bp_media_form_report_metabox', __('Submit a request form', 'rtPanel'), 'bp_media_send_request', 'bp-media-settings', 'normal', 'core');
                    break;
                case $tab :
// All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                    add_meta_box('bp_media_settings_metabox', __('BuddyPress Media Settings', 'rtPanel'), 'bp_media_admin_menu', 'bp-media-settings', 'normal', 'core');
                    add_meta_box('bp_media_options_metabox', __('Spread the word', 'rtPanel'), 'bp_media_settings_options', 'bp-media-settings', 'normal', 'core');
                    add_meta_box('bp_media_other_options_metabox', __('BuddyPress Media Other options', 'rtPanel'), 'bp_media_settings_other_options', 'bp-media-settings', 'normal', 'core');
                    break;
            }
        }

    }
            ?>
