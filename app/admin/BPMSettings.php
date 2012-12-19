<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMSettings
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class BPMSettings {

    public function __construct() {
        add_action('admin_init', array(&$this, 'settings'));
    }

    /**
     * Register Settings
     * 
     * @global string $bp_media->text_domain
     */
    public function settings() {
        global $bp_media;
        add_settings_section('bpm-settings', __('BuddyPress Media Settings', $bp_media->text_domain), array($this, 'section'), 'bp-media-settings');
        add_settings_field('bpm-video', __('Video', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array( 'option' => 'videos_enabled', 'desc' => __('Check to enable video upload functionality', $bp_media->text_domain) ) );
        register_setting('bp_media', 'bp_media_options');
    }

    public function section() {
        do_settings_fields('', $section);
    }

    /**
     * Output a checkbox
     * 
     * @global type $bp_media
     * @param array $args
     */
    public function checkbox($args) {
        global $bp_media;
        $options = $bp_media->options;
        $defaults = array(
            'option' => '',
            'desc' => '',
        );
        $args = wp_parse_args($args, $defaults);
        extract($args);
        if (empty($option)) {
            trigger_error('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the follwoing format array( \'option\' => \'option_name\' ) ');
            return;
        }
        ?>
        <label for="<?php echo $option; ?>">
            <input<?php checked($options[$option]); ?> name="bp_media_options[<?php echo $option; ?>]" id="<?php echo $option; ?>" type="checkbox" />
            <?php echo $desc; ?>
        </label><?php
    }

    /**
     * Load the metaboxes
     * 
     * @global string $bp_media->text_domain
     */
    public function metaboxes() {
        global $bp_media;
        /* Javascripts loaded to allow drag/drop, expand/collapse and hide/show of boxes. */
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');

// Check to see which tab we are on
        $tab = isset($_GET['page']) ? $_GET['page'] : "bp-media-settings";

        switch ($tab) {
            case 'bp-media-addons' :
// All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                add_meta_box('bp_media_addons_list_metabox', __('BuddyPress Media Addons for Audio/Video Conversion', $bp_media->text_domain), 'bp_media_addons_list', 'bp-media-settings', 'normal', 'core');
                break;
            case 'bp-media-support' :
// All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                add_meta_box('bp_media_support_metabox', __('BuddyPress Media Support', $bp_media->text_domain), 'bp_media_support', 'bp-media-settings', 'normal', 'core');
                add_meta_box('bp_media_form_report_metabox', __('Submit a request form', $bp_media->text_domain), 'bp_media_send_request', 'bp-media-settings', 'normal', 'core');
                break;
            case $tab :
// All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                add_meta_box('bp_media_settings_metabox', __('BuddyPress Media Settings', $bp_media->text_domain), 'bp_media_admin_menu', 'bp-media-settings', 'normal', 'core');
                add_meta_box('bp_media_options_metabox', __('Spread the word', $bp_media->text_domain), 'bp_media_settings_options', 'bp-media-settings', 'normal', 'core');
                add_meta_box('bp_media_other_options_metabox', __('BuddyPress Media Other options', $bp_media->text_domain), 'bp_media_settings_other_options', 'bp-media-settings', 'normal', 'core');
                break;
        }
    }

}
    ?>
