<?php

/**
 * Description of BPMediaEncoding
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class BPMediaEncoding {
    
    protected $api_url = 'http://192.168.0.66:3000/';

    public function __construct() {
        if (is_admin()) {
            add_action(bp_core_admin_hook(), array($this, 'menu'));
            add_action('admin_init', array($this, 'encoding_settings'));
            add_filter('bp_media_add_sub_tabs', array($this,'encoding_tab'), '', 2);
        }
        add_filter('bp_media_add_admin_bar_item', array($this,'admin_bar_menu'));
    }

    public function menu() {
        add_submenu_page('bp-media-settings', __('BuddyPress Media Encoding Service', 'buddypress-media'), __('Encoding Service', 'buddypress-media'), 'manage_options', 'bp-media-encoding', array($this, 'encoding_page'));
    }

    /**
     * Render the BuddyPress Media Encoding page
     */
    public function encoding_page() {
        global $bp_media_admin;
        $bp_media_admin->render_page('bp-media-encoding');
    }

    public function encoding_settings() {
        add_settings_section('bpm-encoding', __('Encoding Service', 'buddypress-media'), array($this, 'encoding_service'), 'bp-media-encoding');
    }

    public function encoding_tab($tabs, $tab) {
        $idle_class = 'nav-tab';
        $active_class = 'nav-tab nav-tab-active';
        /* BuddyPress Media */
        $tabs[] = array(
            'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-encoding'), 'admin.php')),
            'title' => __('BuddyPress Media Encoding Service', 'buddypress-media'),
            'name' => __('Encoding', 'buddypress-media'),
            'class' => ($tab == 'bp-media-encoding') ? $active_class : $idle_class . ' last_tab'
        );

        return $tabs;
    }

    public function admin_bar_menu($bp_media_admin_nav) {
        // Encoding Service
        $bp_media_admin_nav[] = array(
            'parent' => 'bp-media-menu',
            'id' => 'bp-media-encoding',
            'title' => __('Encoding Service', 'buddypress-media'),
            'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-encoding'), 'admin.php'))
        );
        return $bp_media_admin_nav;
    }
    
    public function is_valid_key($key){
        $validate_url = trailingslashit($this->api_url).'validate/'.$key;
        $validation_page = wp_remote_get($validate_url);
        $validation_info = json_decode($validation_page['body']);
        return $validation_info['status'];
    }
    
    public function encoding_service(){
        $api_key = bp_get_option('bp-media-encoding-api-key');
//        if ( $api_key )
//            echo '<input type="text">';
    }

}

?>
