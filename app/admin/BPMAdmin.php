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
            add_action('admin_enqueue_scripts', array($this, 'ui'));
            add_action(bp_core_admin_hook(), array($this, 'menu'));
            add_action('admin_init', array($this, 'metaboxes'));
            add_action( 'admin_init', array( &$this, 'settings' ) );
        }
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
     * @global string $bpm_text_domain
     */
    private function menu() {
        global $bpm_text_domain;
        add_menu_page(__('Buddypress Media Component', $bpm_text_domain), __('BP Media', $bpm_text_domain), 'manage_options', 'bp-media-settings', array($this, 'render_settings'));
        add_submenu_page('bp-media-settings', __('Buddypress Media Settings', $bpm_text_domain), __('Settings', $bpm_text_domain), 'manage_options', 'bp-media-settings', array($this, 'redener_settings'));
        add_submenu_page('bp-media-settings', __('Buddypress Media Addons', $bpm_text_domain), __('Addons', $bpm_text_domain), 'manage_options', 'bp-media-addons', array($this, 'redener_settings'));
        add_submenu_page('bp-media-settings', __('Buddypress Media Support', $bpm_text_domain), __('Support ', $bpm_text_domain), 'manage_options', 'bp-media-support', array($this, 'redener_settings'));
    }
    
    /**
     * Register Settings
     * 
     * @global string $bpm_text_domain
     */    
    private function settings(){
        global $bpm_text_domain;
        add_settings_section( 'bpm-settings', __( 'BuddyPress Media Settings', $bpm_text_domain ), array( $this, 'section' ), 'bp-media-settings' );
 	add_settings_field( 'bpm-video', __( 'Video', $bpm_text_domain ), array( $this, 'checkbox' ), 'bp-media-settings', 'bpm-settings' );
        register_setting( 'bp_media', 'bp_media_options' );
    }

    /**
     * Render BPMedia Settings
     * 
     * @global string $bpm_text_domain
     */
    private function render_settings() {
        global $bpm_text_domain;
        $tab = isset($_GET['page']) ? $_GET['page'] : "bp-media-settings";
        ?>

        <div class="wrap bp-media-admin">
            <?php //screen_icon( 'buddypress' );    ?>
            <div id="icon-buddypress" class="icon32"><br></div>
            <h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs(__('Media', $bpm_text_domain)); ?></h2>
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
                'title' => __('Buddypress Media Settings', $bpm_text_domain),
                'name' => __('Settings', $bpm_text_domain),
                'class' => ($tab == 'bp-media-settings') ? $active_class : $idle_class . ' first_tab'
            );

            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-addons'), 'admin.php')),
                'title' => __('Buddypress Media Addons', $bpm_text_domain),
                'name' => __('Addons', $bpm_text_domain),
                'class' => ($tab == 'bp-media-addons') ? $active_class : $idle_class
            );

            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-support'), 'admin.php')),
                'title' => __('Buddypress Media Support', $bpm_text_domain),
                'name' => __('Support', $bpm_text_domain),
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
         * 
         * @global string $bpm_text_domain
         */
        private function metaboxes() {
            global $bpm_text_domain;
            /* Javascripts loaded to allow drag/drop, expand/collapse and hide/show of boxes. */
            wp_enqueue_script('common');
            wp_enqueue_script('wp-lists');
            wp_enqueue_script('postbox');

// Check to see which tab we are on
            $tab = isset($_GET['page']) ? $_GET['page'] : "bp-media-settings";

            switch ($tab) {
                case 'bp-media-addons' :
// All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                    add_meta_box('bp_media_addons_list_metabox', __('BuddyPress Media Addons for Audio/Video Conversion', $bpm_text_domain), 'bp_media_addons_list', 'bp-media-settings', 'normal', 'core');
                    break;
                case 'bp-media-support' :
// All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                    add_meta_box('bp_media_support_metabox', __('BuddyPress Media Support', $bpm_text_domain), 'bp_media_support', 'bp-media-settings', 'normal', 'core');
                    add_meta_box('bp_media_form_report_metabox', __('Submit a request form', $bpm_text_domain), 'bp_media_send_request', 'bp-media-settings', 'normal', 'core');
                    break;
                case $tab :
// All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                    add_meta_box('bp_media_settings_metabox', __('BuddyPress Media Settings', $bpm_text_domain), 'bp_media_admin_menu', 'bp-media-settings', 'normal', 'core');
                    add_meta_box('bp_media_options_metabox', __('Spread the word', $bpm_text_domain), 'bp_media_settings_options', 'bp-media-settings', 'normal', 'core');
                    add_meta_box('bp_media_other_options_metabox', __('BuddyPress Media Other options', $bpm_text_domain), 'bp_media_settings_other_options', 'bp-media-settings', 'normal', 'core');
                    break;
            }
        }

    }
            ?>
